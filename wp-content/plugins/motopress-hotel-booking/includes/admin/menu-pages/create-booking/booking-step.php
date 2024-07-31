<?php

namespace MPHB\Admin\MenuPages\CreateBooking;

use \MPHB\Utils\ParseUtils;
use \MPHB\Utils\ValidateUtils;

/**
 * Fourth step.
 */
class BookingStep extends Step {

	/**
	 * @var \MPHB\Entities\Customer|null
	 */
	protected $customer = null;

	/**
	 * @var \MPHB\Entities\Booking|null
	 */
	protected $booking = null;

	protected $allowRedirect = true;

	public function __construct() {
		parent::__construct( 'booking' );
	}

	public function setup() {
		parent::setup();

		if ( ! $this->isValidStep ) {
			return;
		}

		// Generate price breakdown before save: save() will trigger some emails,
		// which require price breakdown in their text. See MB-1027 for more details
		$this->booking->getPriceBreakdown();

		$isCustomerCreated = $this->createCustomer();

		if ( ! is_wp_error( $isCustomerCreated ) ) {
			$this->customer->setCustomerId( $isCustomerCreated );
			$this->booking->setCustomer( $this->customer );
		}

		$bookingSaved = MPHB()->getBookingRepository()->save( $this->booking );

		if ( ! $bookingSaved ) {
			$this->parseError( __( 'Unable to create booking. Please try again.', 'motopress-hotel-booking' ) );
			$this->isValidStep = false;
			return;
		}

		do_action( 'mphb_create_booking_by_user', $this->booking );

		// Redirect to "Edit Booking"
		if ( $this->allowRedirect ) {
			$redirectTo = get_edit_post_link( $this->booking->getId(), 'raw' );
			wp_redirect( $redirectTo );
			$this->_exit();
		}
	}

	protected function renderValid() {
		$booking = sprintf( __( 'Booking #%s', 'motopress-hotel-booking' ), $this->booking->getId() );
		$link    = get_edit_post_link( $this->booking->getId() );

		echo '<h2><a href="' . esc_url( $link ) . '">' . esc_html( $booking ) . '</a></h2>';
	}

	protected function parseFields() {
		if ( apply_filters( 'mphb_block_booking', false ) ) {
			$this->parseError( __( 'Booking is blocked due to maintenance reason. Please try again later.', 'motopress-hotel-booking' ) );
			return;
		}

		$this->checkInDate  = $this->parseCheckInDate( INPUT_POST );
		$this->checkOutDate = $this->parseCheckOutDate( INPUT_POST );
		$this->customer     = $this->parseCustomer( INPUT_POST );

		if ( $this->checkInDate && $this->checkOutDate && $this->customer ) {
			$this->booking = $this->parseBooking( INPUT_POST );
		}
	}

	/**
	 * @param int $inputType INPUT_POST (0) or INPUT_GET (1)
	 *
	 * @return \MPHB\Entities\Customer|null
	 */
	protected function parseCustomer( $inputType ) {
		$input = $inputType == INPUT_POST ? $_POST : $_GET;

		if ( ! empty( $_FILES ) ) {
			$input = array_merge( $input, $_FILES );
		}

		$customerData = ParseUtils::parseCustomer( $input, $this->parseErrors );

		if ( $customerData !== false ) {
			return new \MPHB\Entities\Customer( $customerData );
		} else {
			return null;
		}
	}

	/**
	 * @param int $input INPUT_POST (0) or INPUT_GET (1)
	 *
	 * @return \MPHB\Entities\Booking|null
	 */
	protected function parseBooking( $input ) {
		/**
		 * @var string|false|null
		 */
		$details = filter_input( $input, 'mphb_room_details', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY );

		if ( empty( $details ) ) {
			if ( is_null( $details ) ) {
				$this->parseError( __( 'There are no accommodations selected for reservation.', 'motopress-hotel-booking' ) );
			} elseif ( $details === false ) {
				$this->parseError( __( 'Selected accommodations are not valid.', 'motopress-hotel-booking' ) );
			}

			return null;
		}

		$rooms     = array();
		$roomIds   = array();
		$typeRates = array();
		$wasErrors = count( $this->parseErrors );

		foreach ( $details as $number => $roomDetails ) {
			$roomTypeId     = isset( $roomDetails['room_type_id'] ) ? ValidateUtils::validateInt( $roomDetails['room_type_id'] ) : 0;
			$roomType       = ( $roomTypeId > 0 ) ? MPHB()->getRoomTypeRepository()->findById( $roomTypeId ) : null;
			$originalTypeId = ( ! is_null( $roomType ) ) ? $roomType->getOriginalId() : $roomTypeId;
			$roomId         = isset( $roomDetails['room_id'] ) ? ValidateUtils::validateInt( $roomDetails['room_id'] ) : 0;
			$rateId         = isset( $roomDetails['rate_id'] ) ? ValidateUtils::validateInt( $roomDetails['rate_id'] ) : 0;
			$adults         = isset( $roomDetails['adults'] ) ? ValidateUtils::validateInt( $roomDetails['adults'] ) : 0;
			$children       = isset( $roomDetails['children'] ) ? ValidateUtils::validateInt( $roomDetails['children'] ) : 0;
			$minAdults      = MPHB()->settings()->main()->getMinAdults();
			$minChildren    = MPHB()->settings()->main()->getMinChildren();
			$guestName      = isset( $roomDetails['guest_name'] ) ? mphb_clean( $roomDetails['guest_name'] ) : '';

			if ( ! $roomType || $roomType->getStatus() != 'publish' ) {
				$this->parseError( __( 'Accommodation Type is not valid.', 'motopress-hotel-booking' ) );
				break;
			}

			if ( $roomId <= 0 ) {
				$this->parseError( __( 'Selected accommodations are not valid.', 'motopress-hotel-booking' ) );
				break;
			}

			if ( $rateId <= 0 ) {
				$this->parseError( __( 'Rate is not valid.', 'motopress-hotel-booking' ) );
				break;
			}

			// Search allowed rates (IDs)
			$allowedRateIds = array();
			if ( isset( $typeRates[ $originalTypeId ] ) ) {
				$allowedRateIds = $typeRates[ $originalTypeId ];
			} else {
				$allowedRates                 = MPHB()->getRateRepository()->findAllActiveByRoomType(
					$originalTypeId,
					array(
						'check_in_date'  => $this->checkInDate,
						'check_out_date' => $this->checkOutDate,
						'mphb_language'  => 'original',
					)
				);
				$allowedRateIds               = array_map(
					function( \MPHB\Entities\Rate $rate ) {
						return $rate->getOriginalId();
					},
					$allowedRates
				);
				$typeRates[ $originalTypeId ] = $allowedRateIds;
			}

			if ( ! in_array( $rateId, $allowedRateIds ) ) {
				$this->parseError( __( 'Rate is not valid.', 'motopress-hotel-booking' ) );
				break;
			}

			if ( $adults === false || $adults < $minAdults || $adults > $roomType->getAdultsCapacity() ) {
				$this->parseError( __( 'Adults number is not valid.', 'motopress-hotel-booking' ) );
				break;
			}

			if ( $children === false || $children < $minChildren || $children > $roomType->getChildrenCapacity() ) {
				$this->parseError( __( 'Children number is not valid.', 'motopress-hotel-booking' ) );
				break;
			}

			if ( $roomType->hasLimitedTotalCapacity() && $adults + $children > $roomType->getTotalCapacity() ) {
				$this->parseError( __( 'The total number of guests is not valid.', 'motopress-hotel-booking' ) );
				break;
			}

			if ( ! MPHB()->getRulesChecker()->verify( $this->checkInDate, $this->checkOutDate, $roomTypeId ) ) {
				$this->parseError( __( 'Selected dates do not meet booking rules for type %s', 'motopress-hotel-booking' ) );
				break;
			}

			$services = array();

			// Check isset() before is_array(); if there are no services,
			// in_array() will generate the notice "Undefined index"
			if ( isset( $roomDetails['services'] ) && is_array( $roomDetails['services'] ) ) {
				foreach ( $roomDetails['services'] as $serviceDetails ) {
					if ( ! isset( $serviceDetails['id'] ) || ! isset( $serviceDetails['adults'] ) ) {
						continue;
					}

					$serviceId       = ValidateUtils::validateInt( $serviceDetails['id'] );
					$serviceAdults   = ValidateUtils::validateInt( $serviceDetails['adults'] );
					$serviceQuantity = isset( $serviceDetails['quantity'] ) ? ValidateUtils::validateInt( $serviceDetails['quantity'] ) : 1;

					if ( $serviceId === false || $serviceAdults === false || ! in_array( $serviceId, $roomType->getServices() ) || $serviceAdults <= 0 || ( isset( $serviceDetails['quantity'] ) && $serviceQuantity < 1 ) ) {
						continue;
					}

					$service = \MPHB\Entities\ReservedService::create(
						array(
							'id'       => $serviceId,
							'adults'   => $serviceAdults,
							'quantity' => $serviceQuantity,
						)
					);

					if ( ! is_null( $service ) ) {
						$services[] = $service;
					}
				} // For each service details
			}

			$rooms[] = array(
				'room_id'           => $roomId,
				'rate_id'           => $rateId,
				'adults'            => $adults,
				'children'          => $children,
				'reserved_services' => $services,
				'guest_name'        => $guestName,
			);

			if ( ! isset( $roomIds[ $roomTypeId ] ) ) {
				$roomIds[ $roomTypeId ] = array();
			}
			$roomIds[ $roomTypeId ][] = $roomId;
		} // For each room details

		foreach ( $roomIds as $roomTypeId => $ids ) {
			if ( ! MPHB()->getRoomPersistence()->isRoomsFree( $this->checkInDate, $this->checkOutDate, $ids, array( 'room_type_id' => $roomTypeId ) ) ) {
				$this->parseError( __( 'Accommodations are not available.', 'motopress-hotel-booking' ) );
				break;
			}
		}

		if ( count( $this->parseErrors ) > $wasErrors ) {
			return null;
		}

		$reservedRooms = array_filter( array_map( array( '\MPHB\Entities\ReservedRoom', 'create' ), $rooms ) );

		if ( empty( $reservedRooms ) ) {
			$this->parseError( __( 'There are no accommodations selected for reservation.', 'motopress-hotel-booking' ) );
			return null;
		}

		$values = ( $input == INPUT_POST ) ? $_POST : $_GET;
		$note   = ! empty( $values['mphb_note'] ) ? sanitize_textarea_field( $values['mphb_note'] ) : '';

		$newBookingStatus = ! empty( $values['mphb_new_booking_status'] ) ? sanitize_textarea_field( wp_unslash( $values['mphb_new_booking_status'] ) ) : \MPHB\PostTypes\BookingCPT\Statuses::STATUS_CONFIRMED;

		$booking = \MPHB\Entities\Booking::create(
			array(
				'check_in_date'  => $this->checkInDate,
				'check_out_date' => $this->checkOutDate,
				'customer'       => $this->customer,
				'note'           => $note,
				'status'         => $newBookingStatus,
				'reserved_rooms' => $reservedRooms,
				'checkout_id'    => mphb_generate_uuid4(),
			)
		);

		if ( ! empty( $values['mphb_applied_coupon_code'] ) ) {
			$coupon = MPHB()->getCouponRepository()->findByCode( mphb_clean( $values['mphb_applied_coupon_code'] ) );
			if ( $coupon ) {
				$booking->applyCoupon( $coupon );
			}
		}

		return $booking;
	}

	public function disableRedirect() {
		$this->allowRedirect = false;
	}

	protected function _exit() {
		exit;
	}

	/**
	 *
	 * @since 4.2.0
	 *
	 * @return int|\WP_Error
	 */
	public function createCustomer() {
		return MPHB()->customers()->createCustomerOnBooking( $this->booking, true );
	}

}
