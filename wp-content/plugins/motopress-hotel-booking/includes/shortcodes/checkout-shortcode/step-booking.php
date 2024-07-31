<?php

namespace MPHB\Shortcodes\CheckoutShortcode;

use \MPHB\Entities;
use \MPHB\Shortcodes\CheckoutShortcode;
use \MPHB\Utils\ParseUtils;
use \MPHB\Utils\ValidateUtils;
use \MPHB\UsersAndRoles\Customers;
use \MPHB\UsersAndRoles\Customer;
use \MPHB\UsersAndRoles\User;

class StepBooking extends Step {

	/**
	 *
	 * @var Entities\Customer
	 */
	protected $customer;

	/**
	 *
	 * @var string
	 */
	protected $gatewayId;

	/**
	 *
	 * @var boolean
	 */
	protected $isCorrectPaymentData = false;

	/**
	 *
	 * @var boolean
	 */
	protected $isCorrectData = false;

	/**
	 *
	 * @var boolean
	 */
	protected $isAlreadyBooked = false;

	/**
	 *
	 * @var boolean
	 */
	protected $unableToCreateBooking = false;

	/**
	 *
	 * @var Entities\ReservedRoom[]
	 */
	private $reservedRooms = array();

	/**
	 *
	 * @var Entities\Booking
	 */
	private $booking;

	/**
	 * Booked before clicking "Back" button in browser (reserved, but not
	 * completed). We can use it's reserved rooms.
	 *
	 * @var Entities\Booking|null
	 */
	private $unfinishedBooking = null;

	/**
	 * Rooms from $unfinishedBooking. Still available if the current checkout ID
	 * equals to $unfinishedBooking->checkoutId.
	 *
	 * @var array [%Room type ID% => [%Room ID%]]
	 */
	private $unfinishedRooms = array();

	/**
	 * Checkout ID (maybe checkout ID of $unfinishedBooking).
	 *
	 * @var string
	 */
	private $checkoutId = '';

	public function setup() {

		$this->isCorrectData = false;

		if ( ! $this->parseCheckoutId() || ! $this->parseUnfinishedBookingData() ) {
			return;
		}

		if ( ! $this->parseCheckInDate() || ! $this->parseCheckOutDate() ) {
			return;
		}

		if ( ! $this->parseCustomerData() || ! $this->parseBookingData() ) {
			if ( $this->isAlreadyBooked ) {
				$this->cleanUnfinished();
			}
			return;
		}

		if ( MPHB()->settings()->main()->getConfirmationMode() === 'payment' && ! $this->parsePaymentData() ) {
			return;
		}

		if ( apply_filters( 'mphb_block_booking', false ) ) {
			$this->errors[] = __( 'Booking is blocked due to maintenance reason. Please try again later.', 'motopress-hotel-booking' );
			return;
		}

		$this->isCorrectData = true;

		MPHB()->getSession()->set( 'mphb_checkout_step', \MPHB\Shortcodes\CheckoutShortcode::STEP_BOOKING );

		// First delete previously unfinished booking (to free rooms)
		$this->cleanUnfinished();

		// Generate price breakdown before save: save() will trigger some emails,
		// which require price breakdown in their text. See MB-1027 for more details
		$this->booking->getPriceBreakdown();

		$isCustomerCreated = $this->createCustomer();

		if ( ! is_wp_error( $isCustomerCreated ) ) {
			$this->customer->setCustomerId( $isCustomerCreated );
			$this->booking->setCustomer( $this->customer );
		}

		$isCreated = MPHB()->getBookingRepository()->save( $this->booking );

		if ( ! $isCreated ) {
			$this->unableToCreateBooking = true;
			return;
		}

		do_action( 'mphb_create_booking_by_user', $this->booking );

		MPHB()->getSession()->set( 'mphb_checkout_step', \MPHB\Shortcodes\CheckoutShortcode::STEP_COMPLETE );

		if ( MPHB()->settings()->main()->getConfirmationMode() === 'payment' ) {
			$payment = $this->createPayment( $this->booking );
			$this->booking->setExpectPayment( $payment->getId() );
			MPHB()->gatewayManager()->getGateway( $this->gatewayId )->processPayment( $this->booking, $payment );
		}

		$this->stepValid();
	}

	protected function parseCheckoutId() {
		if ( empty( $_POST[ CheckoutShortcode::BOOKING_CID_NAME ] ) ) {
			$this->errors[] = __( 'Checkout data is not valid.', 'motopress-hotel-booking' );
			return false;
		}

		// phpcs:ignore WordPress.Security.ValidatedSanitizedInput
		$this->checkoutId = mphb_clean( wp_unslash( $_POST[ CheckoutShortcode::BOOKING_CID_NAME ] ) );

		return true;
	}

	protected function parseUnfinishedBookingData() {
		$this->unfinishedBooking = MPHB()->getBookingRepository()->findByCheckoutId( $this->checkoutId );

		if ( ! is_null( $this->unfinishedBooking ) ) {
			foreach ( $this->unfinishedBooking->getReservedRooms() as $reservedRoom ) {
				$this->unfinishedRooms[ $reservedRoom->getRoomTypeId() ][] = $reservedRoom->getRoomId();
			}
		}

		return true;
	}

	/**
	 * @return bool
	 *
	 * @since 3.7.0 added new filter - "mphb_sc_checkout_step_booking_rooms_details".
	 * @since 3.7.0 added new filter - "mphb_search_available_rooms".
	 * @since 3.7.0 added new filter - "mphb_sc_checkout_step_booking_booking_details".
	 */
	protected function parseBookingData() {

		if ( empty( $_POST['mphb_room_details'] ) ) {
			$this->errors[] = __( 'There are no accommodations selected for reservation.', 'motopress-hotel-booking' );
			return false;
		}

		// phpcs:ignore WordPress.Security.ValidatedSanitizedInput
		$bookingDetails      = isset( $_POST['mphb_room_details'] ) && is_array( $_POST['mphb_room_details'] ) ? $_POST['mphb_room_details'] : array();
		$bookingDetails      = apply_filters( 'mphb_sc_checkout_step_booking_rooms_details', $bookingDetails );
		$bookingRoomsDetails = array();
		$errors              = array();

		foreach ( $bookingDetails as $index => $roomDetails ) {

			$roomTypeId = isset( $roomDetails['room_type_id'] ) ? \MPHB\Utils\ValidateUtils::validateInt( $roomDetails['room_type_id'] ) : null;
			if ( ! $roomTypeId ) {
				$errors[] = __( 'Accommodation Type is not valid.', 'motopress-hotel-booking' );
				break;
			}

			$roomType = MPHB()->getRoomTypeRepository()->findById( $roomTypeId );
			if ( ! $roomType || $roomType->getStatus() !== 'publish' ) {
				$errors[] = __( 'Accommodation Type is not valid.', 'motopress-hotel-booking' );
				break;
			}

			$rateId = isset( $roomDetails['rate_id'] ) ? \MPHB\Utils\ValidateUtils::validateInt( $roomDetails['rate_id'] ) : null;
			if ( ! $rateId ) {
				$errors[] = __( 'Rate is not valid.', 'motopress-hotel-booking' );
				break;
			}

			$rateArgs = array(
				'check_in_date'  => $this->checkInDate,
				'check_out_date' => $this->checkOutDate,
				'mphb_language'  => 'original',
			);

			$allowedRates    = MPHB()->getRateRepository()->findAllActiveByRoomType( $roomType->getOriginalId(), $rateArgs );
			$allowedRatesIds = array_map(
				function( Entities\Rate $rate ) {
					return $rate->getOriginalId();
				},
				$allowedRates
			);

			if ( ! in_array( $rateId, $allowedRatesIds ) ) {
				$errors[] = __( 'Rate is not valid.', 'motopress-hotel-booking' );
				break;
			}

			$adults = isset( $roomDetails['adults'] ) ? \MPHB\Utils\ValidateUtils::validateInt( $roomDetails['adults'] ) : 0;
			if ( $adults === false || $adults < MPHB()->settings()->main()->getMinAdults() || $adults > $roomType->getAdultsCapacity() ) {
				$errors[] = __( 'Adults number is not valid.', 'motopress-hotel-booking' );
				break;
			}

			$children = isset( $roomDetails['children'] ) ? \MPHB\Utils\ValidateUtils::validateInt( $roomDetails['children'] ) : 0;
			if ( $children === false || $children < MPHB()->settings()->main()->getMinChildren() || $children > $roomType->getChildrenCapacity() ) {
				$errors[] = __( 'Children number is not valid.', 'motopress-hotel-booking' );
				break;
			}

			if ( $roomType->hasLimitedTotalCapacity() && $adults + $children > $roomType->getTotalCapacity() ) {
				$errors[] = __( 'The total number of guests is not valid.', 'motopress-hotel-booking' );
				break;
			}

			if ( ! MPHB()->getRulesChecker()->verify( $this->checkInDate, $this->checkOutDate, $roomTypeId ) ) {
				$this->errors[] = sprintf( __( 'Selected dates do not meet booking rules for type %s', 'motopress-hotel-booking' ), $roomType->getTitle() );
				break;
			}

			$reservedServices = array();

			if ( ! empty( $roomDetails['services'] ) && is_array( $roomDetails['services'] ) ) {

				foreach ( $roomDetails['services'] as $serviceDetails ) {
					if ( ! isset( $serviceDetails['id'], $serviceDetails['adults'] ) ) {
						continue;
					}

					$serviceId     = \MPHB\Utils\ValidateUtils::validateInt( $serviceDetails['id'] );
					$serviceAdults = \MPHB\Utils\ValidateUtils::validateInt( $serviceDetails['adults'] );
					$quantity      = isset( $serviceDetails['quantity'] ) ? ValidateUtils::validateInt( $serviceDetails['quantity'] ) : 1;
					if ( $serviceId !== false && $serviceAdults !== false && in_array( $serviceId, $roomType->getServices() ) && $serviceAdults > 0 && ( ! isset( $serviceDetails['quantity'] ) || $quantity >= 1 ) ) {
						$reservedServiceAtts = array(
							'id'       => $serviceId,
							'adults'   => $serviceAdults,
							'quantity' => $quantity,
						);
						$reservedServices[]  = Entities\ReservedService::create( $reservedServiceAtts );
					}
				}
			}
			$reservedServices = array_filter( $reservedServices );

			$guestName = isset( $roomDetails['guest_name'] ) ? mphb_clean( $roomDetails['guest_name'] ) : '';

			$bookingRoomsDetails[ $index ] = array(
				'room_type_id'      => $roomTypeId,
				'rate_id'           => $rateId,
				'adults'            => $adults,
				'children'          => $children,
				'reserved_services' => $reservedServices,
				'guest_name'        => $guestName,
			);
		}

		if ( ! empty( $errors ) ) {
			$this->errors = array_merge( $this->errors, $errors );
			return false;
		}

		// Allocate rooms
		$availableRooms = $this->unfinishedRooms;
		$roomTypesCount = array_count_values( wp_list_pluck( $bookingRoomsDetails, 'room_type_id' ) );

		foreach ( $roomTypesCount as $roomTypeId => $roomsCount ) {

			$alreadyHave = empty( $availableRooms[ $roomTypeId ] ) ? 0 : count( $availableRooms[ $roomTypeId ] );

			if ( $alreadyHave < $roomsCount ) {
				$lockedRooms = MPHB()->getRulesChecker()->customRules()->getUnavailableRooms( $this->checkInDate, $this->checkOutDate, $roomTypeId );

				$searchAtts = apply_filters(
					'mphb_search_available_rooms',
					array(
						'availability'      => 'free',
						'from_date'         => $this->checkInDate,
						'to_date'           => $this->checkOutDate,
						'count'             => $roomsCount - $alreadyHave,
						'room_type_id'      => $roomTypeId,
						'exclude_rooms'     => $lockedRooms,
						'skip_buffer_rules' => false,
					)
				);

				$foundRooms = MPHB()->getRoomPersistence()->searchRooms( $searchAtts );

				if ( $alreadyHave == 0 ) {
					$availableRooms[ $roomTypeId ] = $foundRooms;
				} else {
					$availableRooms[ $roomTypeId ] = array_merge( $availableRooms[ $roomTypeId ], $foundRooms );
				}
			}

			if ( count( $availableRooms[ $roomTypeId ] ) < $roomsCount ) {
				$this->isAlreadyBooked = true;
				break;
			}
		}

		if ( $this->isAlreadyBooked ) {
			return false;
		}

		foreach ( $bookingRoomsDetails as &$roomDetails ) {
			$roomDetails['room_id'] = (int) array_shift( $availableRooms[ $roomDetails['room_type_id'] ] );
			// "room_type_id" field not required anymore, but leave it for the next filter
		}

		$bookingRoomsDetails = apply_filters( 'mphb_sc_checkout_step_booking_booking_details', $bookingRoomsDetails );

		$this->reservedRooms = array_filter( array_map( array( 'MPHB\Entities\ReservedRoom', 'create' ), $bookingRoomsDetails ) );

		if ( empty( $this->reservedRooms ) ) {
			$this->errors[] = __( 'There are no accommodations selected for reservation.', 'motopress-hotel-booking' );
			return false;
		}

		$note = ! empty( $_POST['mphb_note'] ) ? sanitize_textarea_field( wp_unslash( $_POST['mphb_note'] ) ) : '';

		$bookingAtts = array(
			'check_in_date'  => $this->checkInDate,
			'check_out_date' => $this->checkOutDate,
			'customer'       => $this->customer,
			'note'           => $note,
			'status'         => MPHB()->postTypes()->booking()->statuses()->getDefaultNewBookingStatus(),
			'reserved_rooms' => $this->reservedRooms,
			'checkout_id'    => $this->checkoutId,
		);

		$booking = Entities\Booking::create( $bookingAtts );

		// TODO check is coupon feature is enabled
		if ( ! empty( $_POST['mphb_applied_coupon_code'] ) ) {
			// phpcs:ignore WordPress.Security.ValidatedSanitizedInput
			$coupon = MPHB()->getCouponRepository()->findByCode( mphb_clean( wp_unslash( $_POST['mphb_applied_coupon_code'] ) ) );
			if ( $coupon ) {
				$booking->applyCoupon( $coupon );
			}
		}

		$this->booking = $booking;

		return true;
	}

	/**
	 *
	 * @return boolean
	 */
	public function parseRoomRate() {
		$roomRateId = filter_input( INPUT_POST, 'mphb_room_rate_id', FILTER_VALIDATE_INT );

		if ( ! $roomRateId ) {
			$this->errors[] = __( 'Rate is not valid.', 'motopress-hotel-booking' );
			return false;
		}

		$rate = null;
		foreach ( $this->allowedRates as $allowedRate ) {
			if ( $allowedRate->getId() === $roomRateId ) {
				$rate = $allowedRate;
			}
		}

		if ( is_null( $rate ) ) {
			$this->errors[] = __( 'Rate is not valid.', 'motopress-hotel-booking' );
			return false;
		}

		$this->roomRate = $rate;

		return true;
	}

	/**
	 * @return bool
	 *
	 * @since 3.7.0 added new filter - "mphb_sc_checkout_step_booking_customer_details".
	 * @since 3.7.2 the filter "mphb_sc_checkout_step_booking_customer_details" was removed.
	 */
	protected function parseCustomerData() {
		$input = $_POST;

		if ( ! empty( $_FILES ) ) {
			$input = array_merge( $input, $_FILES );
		}

		$customerData = ParseUtils::parseCustomer( $input, $errors );

		if ( $customerData !== false ) {
			$this->customer = new Entities\Customer( $customerData );
		} else {
			$this->errors  += $errors;
			$this->customer = null;
		}

		return ! is_null( $this->customer );
	}

	/**
	 *
	 * @return boolean
	 */
	protected function parsePaymentData() {
		$this->isCorrectPaymentData = $this->parseGatewayId() && $this->parsePaymentMethodFields();
		return $this->isCorrectPaymentData;
	}

	/**
	 *
	 * @return boolean
	 */
	protected function parseGatewayId() {

		if ( ! isset( $_POST['mphb_gateway_id'] ) ) {
			return false;
		}

		// phpcs:ignore WordPress.Security.ValidatedSanitizedInput
		$gatewayId = mphb_clean( wp_unslash( $_POST['mphb_gateway_id'] ) );

		if ( $this->booking->getTotalPrice() == 0 && $gatewayId == 'manual' ) {
			// avoid process payment gateways on free bookings
			$this->gatewayId = $gatewayId;
			return true;
		}

		if ( ! array_key_exists( $gatewayId, MPHB()->gatewayManager()->getListActive() ) ) {
			$this->errors[] = __( 'Payment method is not valid.', 'motopress-hotel-booking' );
			return false;
		}

		$this->gatewayId = $gatewayId;

		return true;
	}

	protected function parsePaymentMethodFields() {
		$errors = array();

		MPHB()->gatewayManager()->getGateway( $this->gatewayId )->parsePaymentFields( $_POST, $errors );

		if ( ! empty( $errors ) ) {
			$this->errors = array_merge( $this->errors, $errors );
			return false;
		}

		return true;
	}

	public function render() {

		if ( $this->isAlreadyBooked ) {
			$this->showAlreadyBookedMessage();
		} elseif ( ! $this->isCorrectData ) {
			$this->showErrorsMessage();
		} elseif ( $this->unableToCreateBooking ) {
			esc_html_e( 'Unable to create booking. Please try again.', 'motopress-hotel-booking' );
		} else {
			$this->showSuccessMessage();
		}
	}

	/**
	 *
	 * @param Entities\Booking $booking
	 * @return Entities\Payment|null
	 */
	protected function createPayment( $booking ) {

		$gateway = MPHB()->gatewayManager()->getGateway( $this->gatewayId );

		$paymentData = array(
			'gatewayId'   => $gateway->getId(),
			'gatewayMode' => $gateway->getMode(),
			'bookingId'   => $booking->getId(),
			'amount'      => $booking->calcDepositAmount(),
			'currency'    => MPHB()->settings()->currency()->getCurrencyCode(),
		);

		$payment   = Entities\Payment::create( $paymentData );
		$isCreated = MPHB()->getPaymentRepository()->save( $payment );

		if ( $isCreated ) {
			$gateway->storePaymentFields( $payment );
			// Re-get payment. Some gateways may update metadata without entity update.
			$payment = MPHB()->getPaymentRepository()->findById( $payment->getId(), true );
		}

		return $isCreated ? $payment : null;
	}

	protected function cleanUnfinished() {
		if ( is_null( $this->unfinishedBooking ) ) {
			return;
		}

		$paymentId = $this->unfinishedBooking->getExpectPaymentId();
		$payment   = $paymentId !== false ? MPHB()->getPaymentRepository()->findById( $paymentId ) : null;

		if ( ! is_null( $payment ) ) {
			MPHB()->getPaymentRepository()->delete( $payment );
		}

		MPHB()->getBookingRepository()->delete( $this->unfinishedBooking );
	}

	/**
	 *
	 * @since 4.2.0
	 *
	 * @return int|\WP_Error
	 */
	protected function createCustomer() {
		return MPHB()->customers()->createCustomerOnBooking( $this->booking, false );
	}
}
