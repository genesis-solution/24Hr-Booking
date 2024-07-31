<?php

namespace MPHB\Admin\MenuPages\CreateBooking;

/**
 * Third step.
 */
class CheckoutStep extends Step {

	const NONCE_ACTION = 'mphb-booking';
	const NONCE_FIELD  = 'mphb-checkout-nonce';

	/**
	 * [int room_id, int room_type_id, int rate_id, \MPHB\Entities\Rate[] allowed_rates,
	 * int adults, int children]
	 *
	 * @var array
	 */
	protected $rooms = array();

	/**
	 * @var \MPHB\Entities\Booking|null
	 */
	protected $booking = null;

	public function __construct() {
		parent::__construct( 'checkout' );
	}

	public function setup() {
		parent::setup();

		/** @see templates/create-booking/checkout/checkout-form.php */
		add_action( 'mphb_cb_checkout_form_after_start', array( $this, 'printNonceFields' ), 10, 0 );
		add_action( 'mphb_cb_checkout_form_after_start', array( $this, 'printDateHiddenFields' ), 20, 0 );

		if ( ! $this->isValidStep ) {
			return;
		}

		/** @see templates/create-booking/checkout/checkout-form.php */
		add_action( 'mphb_cb_checkout_form', array( '\MPHB\Views\CreateBooking\CheckoutView', 'renderBookingDetails' ), 10, 2 );
			add_action( 'mphb_cb_checkout_booking_details', array( '\MPHB\Views\Shortcodes\CheckoutView', 'renderCheckInDate' ), 10 );
			add_action( 'mphb_cb_checkout_booking_details', array( '\MPHB\Views\Shortcodes\CheckoutView', 'renderCheckOutDate' ), 20 );
			add_action( 'mphb_cb_checkout_booking_details', array( '\MPHB\Views\CreateBooking\CheckoutView', 'renderBookingDetailsInner' ), 30, 2 );
				add_action( 'mphb_cb_checkout_room_details', array( '\MPHB\Views\Shortcodes\CheckoutView', 'renderRoomTypeTitle' ), 10, 3 );
				add_action( 'mphb_cb_checkout_room_details', array( '\MPHB\Views\Shortcodes\CheckoutView', 'renderGuestsChooser' ), 20, 4 );
				add_action( 'mphb_cb_checkout_room_details', array( '\MPHB\Views\Shortcodes\CheckoutView', 'renderRateChooser' ), 30, 5 );
				add_action( 'mphb_cb_checkout_room_details', array( '\MPHB\Views\Shortcodes\CheckoutView', 'renderServiceChooser' ), 40, 4 );
		add_action( 'mphb_cb_checkout_form', array( '\MPHB\Views\CreateBooking\CheckoutView', 'renderCoupon' ), 20 );
		add_action( 'mphb_cb_checkout_form', array( '\MPHB\Views\Shortcodes\CheckoutView', 'renderPriceBreakdown' ), 30 );
		add_action( 'mphb_cb_checkout_form', array( '\MPHB\Views\Shortcodes\CheckoutView', 'renderCheckoutText' ), 35 );
		add_action( 'mphb_cb_checkout_form', array( '\MPHB\Views\Shortcodes\CheckoutView', 'renderCustomerDetails' ), 40, 3 );
		add_action( 'mphb_cb_checkout_form', array( '\MPHB\Views\Shortcodes\CheckoutView', 'renderTotalPrice' ), 50 );
		// Billing details - skipped - the booking does not require the payment
		// Terms & conditions - skipped
		add_action(
			'mphb_cb_checkout_form_before_submit_button',
			function() {

				$bookingStatuses = MPHB()->postTypes()->booking()->statuses()->getLabels();

				echo '<p><label for="mphb_new_booking_status">' . esc_html__( 'Status', 'motopress-hotel-booking' ) . '</label><br>' .
					'<select name="mphb_new_booking_status">' .
					'<option value="' . esc_attr( \MPHB\PostTypes\BookingCPT\Statuses::STATUS_CONFIRMED ) . '" selected="true">' .
						esc_html( $bookingStatuses[ \MPHB\PostTypes\BookingCPT\Statuses::STATUS_CONFIRMED ] ) . '</option>' .
					'<option value="' . esc_attr( \MPHB\PostTypes\BookingCPT\Statuses::STATUS_PENDING ) . '">' .
						esc_html( $bookingStatuses[ \MPHB\PostTypes\BookingCPT\Statuses::STATUS_PENDING ] ) . '</option>' .
					'</select></p>';
			}
		);

		// Create reserved rooms
		$reservedRooms = array_map( array( '\MPHB\Entities\ReservedRoom', 'create' ), $this->rooms );

		// Create booking
		MPHB()->reservationRequest()->setupParameter( 'pricing_strategy', 'base-price' );

		$this->booking = new \MPHB\Entities\Booking(
			array(
				'check_in_date'  => $this->checkInDate,
				'check_out_date' => $this->checkOutDate,
				'reserved_rooms' => $reservedRooms,
			)
		);

		MPHB()->reservationRequest()->resetDefaults( array( 'pricing_strategy' ) );
	}

	protected function renderValid() {
		mphb_get_template_part(
			'create-booking/checkout/checkout-form',
			array(
				'actionUrl' => $this->nextUrl,
				'booking'   => $this->booking,
				'details'   => $this->rooms,
			)
		);
	}

	public function printNonceFields() {
		wp_nonce_field( self::NONCE_ACTION, self::NONCE_FIELD );
	}

	protected function parseFields() {
		$this->checkInDate  = $this->parseCheckInDate( INPUT_POST );
		$this->checkOutDate = $this->parseCheckOutDate( INPUT_POST );

		if ( $this->checkInDate && $this->checkOutDate ) {
			$this->rooms = $this->parseRooms( INPUT_POST );
		}
	}

	/**
	 * @param int $input INPUT_POST (0) or INPUT_GET (1)
	 *
	 * @return array
	 */
	protected function parseRooms( $input ) {

		/**
		 * @var string|false|null
		 */
		$request   = filter_input( $input, 'mphb_rooms', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY );
		$rooms     = array();
		$wasErrors = count( $this->parseErrors );

		if ( empty( $request ) ) {
			if ( is_null( $request ) ) {
				$this->parseError( __( 'There are no accommodations selected for reservation.', 'motopress-hotel-booking' ) );
			} elseif ( $request === false ) {
				$this->parseError( __( 'Selected accommodations are not valid.', 'motopress-hotel-booking' ) );
			}

			return array();
		}

		foreach ( $request as $roomTypeId => $roomIds ) {
			$roomTypeId   = filter_var( $roomTypeId, FILTER_VALIDATE_INT );
			$roomType     = ( $roomTypeId > 0 ) ? MPHB()->getRoomTypeRepository()->findById( $roomTypeId ) : null;
			$rateAtts     = array(
				'check_in_date'  => $this->checkInDate,
				'check_out_date' => $this->checkOutDate,
			);
			$allowedRates = ( $roomType ) ? MPHB()->getRateRepository()->findAllActiveByRoomType( $roomTypeId, $rateAtts ) : array();
			$defaultRate  = ( ! empty( $allowedRates ) ) ? reset( $allowedRates ) : null;

			if ( ! $roomType ) {
				$this->parseError( __( 'Accommodation Type is not valid.', 'motopress-hotel-booking' ) );
				continue;
			}

			if ( ! is_array( $roomIds ) ) {
				$this->parseError( __( 'Selected accommodations are not valid.', 'motopress-hotel-booking' ) );
				continue;
			}

			if ( empty( $allowedRates ) ) {
				$this->parseError( __( 'There are no rates for requested dates.', 'motopress-hotel-booking' ) );
				continue;
			}

			if ( ! MPHB()->getRulesChecker()->verify( $this->checkInDate, $this->checkOutDate, $roomTypeId ) ) {
				$this->parseError( sprintf( __( 'Selected dates do not meet booking rules for type %s', 'motopress-hotel-booking' ), $roomType->getTitle() ) );
				continue;
			}

			if ( ! MPHB()->getRoomPersistence()->isRoomsFree( $this->checkInDate, $this->checkOutDate, $roomIds, array( 'room_type_id' => $roomTypeId ) ) ) {
				$this->parseError( __( 'Accommodations are not available.', 'motopress-hotel-booking' ) );
				continue;
			}

			foreach ( $roomIds as $roomId ) {
				$roomId = absint( $roomId );

				if ( $roomId == 0 ) {
					$this->parseError( __( 'Selected accommodations are not valid.', 'motopress-hotel-booking' ) );
					break;
				}

				$rooms[] = array(
					'room_id'       => $roomId,
					'room_type_id'  => $roomType->getOriginalId(),
					'rate_id'       => $defaultRate->getOriginalId(),
					'allowed_rates' => $allowedRates,
					'adults'        => $roomType->getAdultsCapacity(),
					'children'      => $roomType->getChildrenCapacity(),
				);
			} // For each room ID
		} // For each room type

		return ( count( $this->parseErrors ) == $wasErrors ) ? $rooms : array();
	}

	/**
	 * @return \MPHB\Entities\Booking|null
	 */
	public function getBooking() {
		return $this->booking;
	}

}
