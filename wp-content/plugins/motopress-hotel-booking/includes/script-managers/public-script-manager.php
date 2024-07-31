<?php

namespace MPHB\ScriptManagers;

class PublicScriptManager extends ScriptManager {

	/**
	 *
	 * @var array
	 */
	private $gatewaysData = array();
	private $checkoutData;

	public function __construct() {

		parent::__construct();

		add_action( 'init', array( $this, 'register' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue' ) );

		add_action( 'enqueue_block_editor_assets', array( $this, 'enqueueBlockEditor' ) );
		add_action( 'enqueue_block_editor_assets', array( $this, 'localize' ) );
	}

	/**
	 * @since 3.6.0 removed the script "mphb-vendor-stripe-checkout".
	 * @since 3.6.0 added new script - "mphb-vendor-stripe-library".
	 */
	public function register() {

		parent::register();

		wp_register_script(
			'mphb-fancybox',
			$this->scriptUrl( 'vendors/fancybox/dist/jquery.fancybox.min.js' ),
			array( 'jquery' ),
			'3.5.7',
			true
		);

		wp_register_script( 'mphb-flexslider', $this->scriptUrl( 'vendors/woothemes-FlexSlider/jquery.flexslider-min.js' ), array( 'jquery' ), MPHB()->getVersion(), true );
		wp_register_script( 'mphb-jquery-serialize-json', $this->scriptUrl( 'vendors/jquery.serializeJSON/jquery.serializejson.min.js' ), array( 'jquery' ), MPHB()->getVersion() );

		wp_register_script( 'mphb-vendor-stripe-library', 'https://js.stripe.com/v3/', null, '3.0', true );

		wp_register_script( 'mphb-vendor-braintree-client-sdk', 'https://js.braintreegateway.com/js/braintree-2.31.0.min.js', null, '2.31.0', true );

		wp_register_script( 'mphb', $this->scriptUrl( 'assets/js/public/mphb.min.js' ), $this->scriptDependencies, MPHB()->getVersion(), true );
	}

	protected function registerStyles() {

		parent::registerStyles();

		$this->registerDatepickTheme();

		wp_register_style(
			'mphb-fancybox-css',
			$this->scriptUrl( 'vendors/fancybox/dist/jquery.fancybox.min.css' ),
			null,
			'3.5.7'
		);

		$useFixedFlexslider = apply_filters( 'mphb_use_fixed_flexslider_css', true );
		if ( $useFixedFlexslider ) {
			wp_register_style( 'mphb-flexslider-css', $this->scriptUrl( 'assets/css/flexslider-fixed.css' ), null, MPHB()->getVersion() );
		} else {
			wp_register_style( 'mphb-flexslider-css', $this->scriptUrl( 'vendors/woothemes-FlexSlider/flexslider.css' ), null, MPHB()->getVersion() );
		}

		wp_register_style( 'mphb', $this->scriptUrl( 'assets/css/mphb.min.css' ), $this->styleDependencies, MPHB()->getVersion() );
	}

	protected function registerDatepickTheme() {

		$theme     = MPHB()->settings()->main()->getDatepickerCurrentTheme(); // Not getDatepickerTheme() because of Create New Booking page
		$themeFile = $this->locateDatepickFile( $theme );

		if ( $themeFile !== false ) {
			wp_register_style( 'mphb-kbwood-datepick-theme', $themeFile, array( 'mphb-kbwood-datepick-css' ), MPHB()->getVersion() );
			$this->addStyleDependency( 'mphb-kbwood-datepick-theme' );
		}
	}

	public function enqueueBlockEditor() {

		if ( ! in_array( 'mphb-flexslider', $this->scriptDependencies ) ) {
			wp_enqueue_script( 'mphb-flexslider' );
		}

		if ( ! in_array( 'mphb-flexslider-css', $this->styleDependencies ) ) {
			wp_enqueue_style( 'mphb-flexslider-css' );
		}

		$this->enqueue();
	}

	public function enqueue() {

		if ( ! wp_script_is( 'mphb' ) ) {
			// NextGEN Gallery buffers output and prints scripts with priority 1:
			// add_action('wp_print_footer_scripts', ..., 1);
			// We need to add localization earlier. Otherwise we'll add localization
			// for handle "mphb" when it will be marked as "done" and all scripts will
			// be printed
			add_action( 'wp_print_footer_scripts', array( $this, 'localize' ), 0 );
		}

		wp_enqueue_script( 'mphb' );
		$this->enqueueStyles();
	}

	private function enqueueStyles() {
		wp_enqueue_style( 'mphb' );
	}

	public function addRoomTypeData( $roomTypeId ) {
		// do nothing because calendars do not need such data anymore
		// TODO: remove this method when Hotel Booking & Elementor Integration
		// will require minimum Hotel Booking 4.7.5 and we remove call of this methos there
	}

	/**
	 *
	 * @param string $gatewayId
	 * @param array  $data
	 */
	public function addGatewayData( $gatewayId, $data ) {
		if ( ! isset( $this->gatewaysData[ $gatewayId ] ) ) {
			$this->gatewaysData[ $gatewayId ] = array();
		}
		$this->gatewaysData[ $gatewayId ] = array_merge( $this->gatewaysData[ $gatewayId ], $data );
	}

	/**
	 *
	 * @param array $data
	 */
	public function addCheckoutData( $data ) {
		if ( ! isset( $this->checkoutData ) ) {
			$this->checkoutData = array();
		}
		$this->checkoutData = $data;
	}

	public function localize() {
		wp_localize_script( 'mphb', 'MPHB', $this->getLocalizeData() );
	}

	/**
	 * @return array
	 *
	 * @since 3.7 added new filters: "mphb_custom_front_nonces"
	 * @since 3.8 added new filter - "mphb_public_js_data".
	 */
	public function getLocalizeData() {

		$jsDateFormat     = MPHB()->settings()->dateTime()->getDateTransferFormat();
		$currencySymbol   = MPHB()->settings()->currency()->getCurrencySymbol();
		$currencyPosition = MPHB()->settings()->currency()->getCurrencyPosition();

		$useBilling = ( MPHB()->settings()->main()->getConfirmationMode() === 'payment' && ! mphb_is_create_booking_page() );
		$useBilling = apply_filters( 'mphb_use_billing_on_page', $useBilling );

		$customNonces = apply_filters( 'mphb_custom_front_nonces', array() );

		$data = array(
			'_data' => array(
				'settings'       => array(
					'currency'                => array(
						'code'               => MPHB()->settings()->currency()->getCurrencyCode(),
						'price_format'       => MPHB()->settings()->currency()->getPriceFormat( $currencySymbol, $currencyPosition ),
						'decimals'           => MPHB()->settings()->currency()->getPriceDecimalsCount(),
						'decimal_separator'  => MPHB()->settings()->currency()->getPriceDecimalsSeparator(),
						'thousand_separator' => MPHB()->settings()->currency()->getPriceThousandSeparator(),
					),
					'siteName'                => get_bloginfo( 'name' ),
					'currentLanguage'         => MPHB()->translation()->getCurrentLanguage(),
					'firstDay'                => MPHB()->settings()->dateTime()->getFirstDay(),
					'numberOfMonthCalendar'   => 2,
					'numberOfMonthDatepicker' => 2,
					'dateFormat'              => MPHB()->settings()->dateTime()->getDateFormatJS(),
					'dateTransferFormat'      => MPHB()->settings()->dateTime()->getDateTransferFormatJS(),
					'useBilling'              => $useBilling,
					'useCoupons'              => MPHB()->settings()->main()->isCouponsEnabled(),
					'datepickerClass'         => MPHB()->settings()->main()->getDatepickerThemeClass(),
					'countryRequired'         => MPHB()->settings()->main()->isRequireCountry(),
					'fullAddressRequired'     => MPHB()->settings()->main()->isRequireFullAddress(),
				),
				'isAdmin'        => is_admin(),
				'today'          => mphb_current_time( $jsDateFormat ),
				'ajaxUrl'        => MPHB()->getAjaxUrl(),
				'nonces'         => array_merge( $customNonces, MPHB()->getAjax()->getFrontNonces(), \MPHB\AjaxApi\AjaxApiHandler::getAjaxActionWPNonces() ),
				'translations'   => array(
					'errorHasOccured'                  => __( 'An error has occurred, please try again later.', 'motopress-hotel-booking' ),
					'booked'                           => __( 'Booked', 'motopress-hotel-booking' ),
					'buffer'                           => __( 'Buffer time.', 'motopress-hotel-booking' ),
					'pending'                          => __( 'Pending', 'motopress-hotel-booking' ),
					'available'                        => __( 'Available', 'motopress-hotel-booking' ),
					'notAvailable'                     => __( 'Not available', 'motopress-hotel-booking' ),
					'earlierMinAdvance'                => __( 'This is earlier than allowed by our advance reservation rules.', 'motopress-hotel-booking' ),
					'laterMaxAdvance'                  => __( 'This is later than allowed by our advance reservation rules.', 'motopress-hotel-booking' ),
					'notStayIn'                        => __( 'Not stay-in', 'motopress-hotel-booking' ),
					'notCheckIn'                       => __( 'Not check-in', 'motopress-hotel-booking' ),
					'notCheckOut'                      => __( 'Not check-out', 'motopress-hotel-booking' ),
					'past'                             => __( 'Day in the past', 'motopress-hotel-booking' ),
					'checkInDate'                      => __( 'Check-in date', 'motopress-hotel-booking' ),
					'lessThanMinDaysStay'              => __( 'Less than min days stay', 'motopress-hotel-booking' ),
					'moreThanMaxDaysStay'              => __( 'More than max days stay', 'motopress-hotel-booking' ),
					// for dates between "not stay-in" (rules, existsing bookings) date and "max days stay" date
					'laterThanMaxDate'                 => __( 'Later than max date for current check-in date', 'motopress-hotel-booking' ),
					'rules'                            => __( 'Rules:', 'motopress-hotel-booking' ),
					'tokenizationFailure'              => __( 'Tokenisation failed: %s', 'motopress-hotel-booking' ),
					'roomsAddedToReservation_singular' => _n( '%1$d &times; &ldquo;%2$s&rdquo; has been added to your reservation.', '%1$d &times; &ldquo;%2$s&rdquo; have been added to your reservation.', 1, 'motopress-hotel-booking' ),
					'roomsAddedToReservation_plural'   => _n( '%1$d &times; &ldquo;%2$s&rdquo; has been added to your reservation.', '%1$d &times; &ldquo;%2$s&rdquo; have been added to your reservation.', 2, 'motopress-hotel-booking' ),
					'countRoomsSelected_singular'      => _n( '%s accommodation selected.', '%s accommodations selected.', 1, 'motopress-hotel-booking' ),
					'countRoomsSelected_plural'        => _n( '%s accommodation selected.', '%s accommodations selected.', 2, 'motopress-hotel-booking' ),
					'emptyCouponCode'                  => __( 'Coupon code is empty.', 'motopress-hotel-booking' ),
					'checkInNotValid'                  => __( 'Check-in date is not valid.', 'motopress-hotel-booking' ),
					'checkOutNotValid'                 => __( 'Check-out date is not valid.', 'motopress-hotel-booking' ),
					'selectDates'                      => __( 'Select dates', 'motopress-hotel-booking' ),
				),
				'page'           => array(
					'isCheckoutPage'       => mphb_is_checkout_page(),
					'isSingleRoomTypePage' => mphb_is_single_room_type_page(),
					'isSearchResultsPage'  => mphb_is_search_results_page(),
					'isCreateBookingPage'  => mphb_is_create_booking_page(),
				),
				'gateways'       => $this->gatewaysData,
				'roomTypeId'     => mphb_is_single_room_type_page() ? MPHB()->getCurrentRoomType()->getOriginalId() : 0,
			),
		);

		// Add "isDirectBooking" setting
		if ( mphb_is_search_results_page() ) {
			$data['_data']['settings']['isDirectBooking'] = MPHB()->settings()->main()->isDirectSearchResultsBooking();
		} else {
			// Add "isDirectBooking" for booking form shortcode (at least) on any front page (see MB-1124)
			$data['_data']['settings']['isDirectBooking'] = MPHB()->settings()->main()->isDirectRoomBooking();
		}

		if ( isset( $this->checkoutData ) ) {
			$data['_data']['checkout'] = $this->checkoutData;
		}

		$data = apply_filters( 'mphb_public_js_data', $data );

		return $data;
	}
}
