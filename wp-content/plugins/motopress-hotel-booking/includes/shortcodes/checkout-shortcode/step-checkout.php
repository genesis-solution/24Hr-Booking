<?php

namespace MPHB\Shortcodes\CheckoutShortcode;

use \MPHB\Entities;
use \MPHB\UsersAndRoles\Customers;

class StepCheckout extends Step {

	/**
	 *
	 * @var Entities\Booking
	 */
	protected $booking;

	/**
	 *
	 * @var Entities\ReservedRoom[]
	 */
	protected $reservedRooms;

	/**
	 *
	 * @var boolean
	 */
	protected $alreadyBooked = false;

	/**
	 *
	 * @var array
	 */
	protected $roomDetails = array();

	protected $customer = null;

	private $isCorrectBookingData = true;


	public function __construct() {

		add_action( 'init', array( $this, 'addInitActions' ) );
		add_action( 'wp_login_failed', array( $this, 'redirectOnFailedLogin' ) );
		add_action( 'wp_logout', array( $this, 'redirectAfterLogout' ) );
	}

	public function addInitActions() {

		add_action( 'mphb_sc_checkout_before_errors', array( $this, 'showErrorsMessage' ) );

		add_action( 'mphb_sc_checkout_before_form', array( '\MPHB\Views\Shortcodes\CheckoutView', 'renderCustomerErrors' ), 10 );

		add_action( 'mphb_sc_checkout_before_form', array( '\MPHB\Views\Shortcodes\CheckoutView', 'renderLoginForm' ), 10 );

		// templates hooks
		add_action( 'mphb_sc_checkout_form', array( '\MPHB\Views\Shortcodes\CheckoutView', 'renderBookingDetails' ), 10, 2 );

		add_action( 'mphb_sc_checkout_room_details', array( '\MPHB\Views\Shortcodes\CheckoutView', 'renderRoomTypeTitle' ), 10, 3 );
		add_action( 'mphb_sc_checkout_room_details', array( '\MPHB\Views\Shortcodes\CheckoutView', 'renderGuestsChooser' ), 20, 4 );
		add_action( 'mphb_sc_checkout_room_details', array( '\MPHB\Views\Shortcodes\CheckoutView', 'renderRateChooser' ), 30, 5 );
		add_action( 'mphb_sc_checkout_room_details', array( '\MPHB\Views\Shortcodes\CheckoutView', 'renderServiceChooser' ), 40, 4 );

		if ( MPHB()->settings()->main()->isCouponsEnabled() ) {
			add_action( 'mphb_sc_checkout_form', array( '\MPHB\Views\Shortcodes\CheckoutView', 'renderCoupon' ), 20 );
		}
		add_action( 'mphb_sc_checkout_form', array( '\MPHB\Views\Shortcodes\CheckoutView', 'renderPriceBreakdown' ), 30 );
		add_action( 'mphb_sc_checkout_form', array( '\MPHB\Views\Shortcodes\CheckoutView', 'renderCheckoutText' ), 35 );
		add_action( 'mphb_sc_checkout_form', array( '\MPHB\Views\Shortcodes\CheckoutView', 'renderCustomerDetails' ), 40, 3 );

		if ( MPHB()->settings()->main()->getConfirmationMode() === 'payment' ) {
			$gateways = MPHB()->gatewayManager()->getListActive();

			// Filter used in mphb-woocommerce to hide WooCommerce gateway, if it is the only one
			if ( count( $gateways ) == 1 && apply_filters( 'mphb_sc_checkout_single_gateway_hide_billing_details', false, reset( $gateways ) ) ) {
				add_action( 'mphb_sc_checkout_form', array( '\MPHB\Views\Shortcodes\CheckoutView', 'renderBillingDetailsHidden' ), 5 );
			} else {
				add_action( 'mphb_sc_checkout_form', array( '\MPHB\Views\Shortcodes\CheckoutView', 'renderBillingDetails' ), 45 );
			}
		}

		add_action( 'mphb_sc_checkout_form', array( '\MPHB\Views\Shortcodes\CheckoutView', 'renderTotalPrice' ), 50 );
		add_action( 'mphb_sc_checkout_form', array( '\MPHB\Views\Shortcodes\CheckoutView', 'renderTermsAndConditions' ), 60 );

		// Booking Details
		add_action( 'mphb_sc_checkout_form_booking_details', array( '\MPHB\Views\Shortcodes\CheckoutView', 'renderCheckInDate' ) );
		add_action( 'mphb_sc_checkout_form_booking_details', array( '\MPHB\Views\Shortcodes\CheckoutView', 'renderCheckOutDate' ) );
		add_action( 'mphb_sc_checkout_form_booking_details', array( '\MPHB\Views\Shortcodes\CheckoutView', 'renderBookingDetailsInner' ), 10, 2 );

		add_action( 'wp_enqueue_scripts', array( $this, 'enqueueScripts' ) );
	}

	/**
	 * @since 3.7.0 added new filter - "mphb_sc_checkout_step_checkout_booking_object".
	 */
	public function setup() {

		$this->isCorrectBookingData = $this->parseBookingData();

		$this->parseCustomerData();

		if ( $this->isCorrectBookingData ) {
			$bookingAtts = array(
				'check_in_date'  => $this->checkInDate,
				'check_out_date' => $this->checkOutDate,
				'reserved_rooms' => $this->reservedRooms,
			);

			MPHB()->reservationRequest()->setupParameter( 'pricing_strategy', 'base-price' );
			$this->booking = apply_filters( 'mphb_sc_checkout_step_checkout_booking_object', Entities\Booking::create( $bookingAtts ) );
			MPHB()->reservationRequest()->resetDefaults( array( 'pricing_strategy' ) );

			$this->stepValid();

			if ( MPHB()->settings()->main()->isDirectRoomBooking() ) {
				// We skiped the search page, so save the search parameters here
				MPHB()->searchParametersStorage()->save(
					array(
						'mphb_check_in_date'  => $this->checkInDate->format( MPHB()->settings()->dateTime()->getDateTransferFormat() ),
						'mphb_check_out_date' => $this->checkOutDate->format( MPHB()->settings()->dateTime()->getDateTransferFormat() ),
						'mphb_adults'         => MPHB()->settings()->main()->getMinAdults(),
						'mphb_children'       => MPHB()->settings()->main()->getMinChildren(),
					)
				);
			}

			mphb_set_cookie( 'mphb_checkout_step', \MPHB\Shortcodes\CheckoutShortcode::STEP_CHECKOUT );
		}
	}

	/**
	 * @return bool
	 *
	 * @since 3.7.0 added new filter - "mphb_sc_checkout_step_checkout_selected_rooms".
	 * @since 3.7.0 added new filter - "mphb_sc_checkout_step_checkout_room_to_reserve".
	 * @since 3.7.0 added new filter - "mphb_sc_checkout_step_checkout_rooms_details".
	 * @since 3.7.0 added new filter - "mphb_sc_checkout_step_checkout_reserved_rooms".
	 */
	protected function parseBookingData() {

		$isCorrectCheckInDate  = $this->parseCheckInDate();
		$isCorrectCheckOutDate = $this->parseCheckOutDate();

		if ( ! $isCorrectCheckInDate || ! $isCorrectCheckOutDate ) {
			return false;
		}

		if ( ! empty( $_POST['mphb_rooms_details'] ) && is_array( $_POST['mphb_rooms_details'] ) ) {
			$roomDetails = (array) wp_unslash( $_POST['mphb_rooms_details'] );
		} elseif ( ! empty( mphb_get_cookie( 'mphb_rooms_details' ) ) ) {
			$roomDetails = maybe_unserialize( mphb_get_cookie( 'mphb_rooms_details' ) );
		}

		mphb_set_cookie( 'mphb_rooms_details', maybe_serialize( $roomDetails ) );

		if ( empty( $roomDetails ) || ! is_array( $roomDetails ) ) {
			$this->errors[] = __( 'There are no accommodations selected for reservation.', 'motopress-hotel-booking' );
			return false;
		}

		// phpcs:ignore WordPress.Security.ValidatedSanitizedInput

		$selectedRooms = ! empty( $roomDetails ) && is_array( $roomDetails ) ? $roomDetails : array();

		$selectedRooms = apply_filters( 'mphb_sc_checkout_step_checkout_selected_rooms', $selectedRooms );

		$this->reservedRooms = $reservedRooms = array();
		$this->roomDetails   = $roomDetails = array();

		foreach ( $selectedRooms as $roomTypeId => $roomsCount ) {

			$roomTypeId = filter_var( $roomTypeId, FILTER_VALIDATE_INT );
			if ( ! $roomTypeId ) {
				$this->errors[] = __( 'Accommodation Type is not valid.', 'motopress-hotel-booking' );
				continue;
			}

			$roomsCount = filter_var( $roomsCount, FILTER_VALIDATE_INT );
			if ( ! $roomsCount ) {
				$this->errors[] = __( 'Accommodation count is not valid.', 'motopress-hotel-booking' );
				continue;
			}

			$roomType = MPHB()->getRoomTypeRepository()->findById( $roomTypeId );
			if ( ! $roomType ) {
				$this->errors[] = __( 'Accommodation Type is not valid.', 'motopress-hotel-booking' );
				continue;
			}

			$unavailableRooms = MPHB()->getRulesChecker()->customRules()->getUnavailableRooms( $this->checkInDate, $this->checkOutDate, $roomType->getOriginalId() );

			$roomsExist = MPHB()->getRoomPersistence()->isExistsRooms(
				$this->checkInDate,
				$this->checkOutDate,
				array(
					'count'             => $roomsCount,
					'room_type_id'      => $roomType->getOriginalId(),
					'exclude_rooms'     => $unavailableRooms,
					'skip_buffer_rules' => false,
				)
			);

			if ( ! $roomsExist ) {
				$this->alreadyBooked = true;
				break;
			}

			$rateArgs = array(
				'check_in_date'  => $this->checkInDate,
				'check_out_date' => $this->checkOutDate,
			// 'mphb_language'  => 'original'
			);

			$allowedRates = MPHB()->getRateRepository()->findAllActiveByRoomType( $roomType->getId(), $rateArgs );
			if ( empty( $allowedRates ) ) {
				$this->errors[] = __( 'There are no rates for requested dates.', 'motopress-hotel-booking' );
				continue;
			}

			if ( ! MPHB()->getRulesChecker()->verify( $this->checkInDate, $this->checkOutDate, $roomTypeId ) ) {
				$this->errors[] = sprintf( __( 'Selected dates do not meet booking rules for type %s', 'motopress-hotel-booking' ), $roomType->getTitle() );
				continue;
			}

			$defaultRate = reset( $allowedRates );

			$reservedRoomAtts = array(
				'rate_id'  => $defaultRate->getOriginalId(),
				'adults'   => $roomType->getAdultsCapacity(),
				'children' => $roomType->getChildrenCapacity(),
			);
			$reservedRoomAtts = apply_filters( 'mphb_sc_checkout_step_checkout_room_to_reserve', $reservedRoomAtts, $roomTypeId );

			for ( $i = 1; $i <= $roomsCount; $i++ ) {

				$reservedRoom = Entities\ReservedRoom::create( $reservedRoomAtts );

				$reservedRooms[] = $reservedRoom;
				$roomDetails[]   = array(
					'room_type_id'  => $roomTypeId,
					'rate_id'       => $defaultRate->getOriginalId(),
					'allowed_rates' => $allowedRates,
					'adults'        => $roomType->getAdultsCapacity(),
					'children'      => $roomType->getChildrenCapacity(),
				);
			}
		}

		$this->roomDetails   = apply_filters( 'mphb_sc_checkout_step_checkout_rooms_details', $roomDetails, $selectedRooms );
		$this->reservedRooms = apply_filters( 'mphb_sc_checkout_step_checkout_reserved_rooms', $reservedRooms, $roomDetails, $selectedRooms );

		if ( empty( $this->reservedRooms ) ) {
			return false;
		}

		return true;
	}

	protected function parseCustomerData() {
		$userId = get_current_user_id();

		if ( ! $userId ) {
			// User is not logged in
			return;
		}

		$customer = MPHB()->customers()->findBy( 'user_id', $userId );

		if ( null == $customer ) {
			// TODO case if user is not a customer
			return;
		}

		$this->customer = $customer;
	}

	/**
	 * @since 3.7.0 added new action - "mphb_sc_checkout_before_errors".
	 */
	public function render() {

		if ( ! $this->isCorrectBookingData ) {
			do_action( 'mphb_sc_checkout_before_errors' );
			return;
		}

		if ( $this->alreadyBooked ) {
			$this->showAlreadyBookedMessage();
			return;
		}

		MPHB()->getSession()->set( 'mphb_checkout_step', \MPHB\Shortcodes\CheckoutShortcode::STEP_CHECKOUT );

		do_action( 'mphb_sc_checkout_before_form' );

		\MPHB\Views\Shortcodes\CheckoutView::renderCheckoutForm( $this->booking, $this->roomDetails, $this->customer );

		do_action( 'mphb_sc_checkout_after_form' );
	}

	/**
	 * @since 3.7.2 added new action - "mphb_enqueue_checkout_scripts".
	 */
	public function enqueueScripts() {

		if ( ! $this->isValidStep ) {
			return;
		}

		$checkoutData = array(
			'min_adults'   => MPHB()->settings()->main()->getMinAdults(),
			'min_children' => MPHB()->settings()->main()->getMinChildren(),
		);

		if ( MPHB()->settings()->main()->getConfirmationMode() === 'payment' ) {
			$checkoutData['deposit_amount'] = $this->booking->calcDepositAmount();
		}

		$checkoutData['total'] = $this->booking->calcPrice();

		MPHB()->getPublicScriptManager()->addCheckoutData( $checkoutData );

		foreach ( MPHB()->gatewayManager()->getListActive() as $gateway ) {
			MPHB()->getPublicScriptManager()->addGatewayData( $gateway->getId(), $gateway->getCheckoutData( $this->booking ) );
		}

		wp_enqueue_script( 'mphb-jquery-serialize-json' );
		MPHB()->getPublicScriptManager()->enqueue();

		do_action( 'mphb_enqueue_checkout_scripts' );
	}

	/**
	 *
	 * @since 4.2.1
	 */
	public function redirectOnFailedLogin() {

		$referrer = wp_get_referer();

		if ( false === $referrer ) {
			return;
		}

		$checkoutPageId = MPHB()->settings()->pages()->getCheckoutPageId();
		$page           = get_post( $checkoutPageId );
		$slug           = $page->post_name;

		if ( strstr( $referrer, $slug ) ) {

			$redirectTo = add_query_arg( 'login_failed', 'error', $referrer );
			wp_safe_redirect( $redirectTo );
			exit;
		}
	}

	/**
	 *
	 * @since 4.2.1
	 */
	public function redirectAfterLogout() {

		$referrer = wp_get_referer();

		if ( false === $referrer ) {
			return;
		}

		$checkoutPageId = MPHB()->settings()->pages()->getCheckoutPageId();
		$page           = get_post( $checkoutPageId );
		$slug           = $page->post_name;

		if ( strstr( $referrer, $slug ) ) {

			wp_safe_redirect( $referrer );
			exit;
		}
	}

}
