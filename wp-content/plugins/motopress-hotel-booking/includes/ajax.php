<?php

namespace MPHB;

use \MPHB\Entities;
use \MPHB\Views;
use \MPHB\Utils\DateUtils;
use \MPHB\Utils\ThirdPartyPluginsUtils;
use \MPHB\Utils\ValidateUtils;

/**
 * TODO move each ajax controller to separate class
 *
 * @since 3.5.0 added new event - "export_bookings_csv".
 * @since 3.5.0 added new event - "check_bookings_csv".
 * @since 3.5.0 added new event - "cancel_bookings_csv".
 *
 * @deprecated put all ajax code to the \MPHB\AjaxApi\AjaxApiHandler and its actions
 * pay attansion to the #nolite comments
 */
class Ajax {

	protected $nonceName    = 'mphb_nonce';
	protected $actionPrefix = 'mphb_';
	protected $ajaxActions  = array(
		// Admin
		'export_bookings_csv'          => array( // Start export
			'method' => 'POST',
			'nopriv' => false,
		),
		'check_bookings_csv'           => array( // Get export progress
			'method' => 'GET',
			'nopriv' => false,
		),
		'cancel_bookings_csv'          => array( // Cancel export
			'method' => 'POST',
			'nopriv' => false,
		),
		'install_plugin'               => array(
			'method' => 'POST',
			'nopriv' => false,
		),
		'display_imported_bookings'    => array(
			'method' => 'POST',
			'nopriv' => false,
		),
		'recalculate_total'            => array(
			'method' => 'POST',
			'nopriv' => false,
		),
		'get_rates_for_room'           => array(
			'method' => 'GET',
			'nopriv' => false,
		),
		'dismiss_license_notice'       => array(
			'method' => 'POST',
			'nopriv' => false,
		),
		'attributes_custom_ordering'   => array(
			'method' => 'POST',
			'nopriv' => false,
		),
		// Frontend
		'create_stripe_payment_intent' => array(
			'method' => 'POST',
			'nopriv' => true,
		),
		'update_checkout_info'         => array(
			'method' => 'GET',
			'nopriv' => true,
		),
		'update_rate_prices'           => array(
			'method' => 'GET',
			'nopriv' => true,
		),
		'get_billing_fields'           => array(
			'method' => 'GET',
			'nopriv' => true,
		),
		'apply_coupon'                 => array(
			'method' => 'POST',
			'nopriv' => true,
		),
		'ical_sync_abort'              => array(
			'method' => 'POST',
		),
		'ical_sync_clear_all'          => array(
			'method' => 'POST',
		),
		'ical_sync_remove_item'        => array(
			'method' => 'POST',
		),
		'ical_sync_get_progress'       => array(
			'method' => 'POST',
		),
		'ical_upload_get_progress'     => array(
			'method' => 'GET',
		),
		'ical_upload_abort'            => array(
			'method' => 'POST',
		),
		'get_accommodations_list'      => array(
			'method' => 'GET',
		),
		'remove_customer'              => array(
			'method' => 'POST',
		),
	);

	public function __construct() {
		foreach ( $this->ajaxActions as $action => $details ) {
			$noPriv = isset( $details['nopriv'] ) ? $details['nopriv'] : false;
			$this->addAjaxAction( $action, $noPriv );
		}
	}

	/**
	 *
	 * @param string $action
	 * @param bool   $noPriv
	 */
	public function addAjaxAction( $action, $noPriv ) {

		add_action( 'wp_ajax_' . $this->actionPrefix . $action, array( $this, $action ) );

		if ( $noPriv ) {
			add_action( 'wp_ajax_nopriv_' . $this->actionPrefix . $action, array( $this, $action ) );
		}
	}

	/**
	 *
	 * @param string $action
	 * @return bool
	 */
	protected function checkNonce( $action ) {

		if ( ! isset( $this->ajaxActions[ $action ] ) ) {
			return false;
		}

		$input = $this->retrieveInput( $action );

		$nonce = isset( $input[ $this->nonceName ] ) ? $input[ $this->nonceName ] : '';

		return wp_verify_nonce( $nonce, $this->actionPrefix . $action );
	}

	/**
	 *
	 * @param string $action Name of AJAX action without wp prefix.
	 * @return array
	 */
	protected function retrieveInput( $action ) {

		$method = isset( $this->ajaxActions[ $action ]['method'] ) ? $this->ajaxActions[ $action ]['method'] : '';

		switch ( $method ) {
			case 'GET':
				$input = $_GET;
				break;
			case 'POST':
				$input = $_POST;
				break;
			default:
				$input = $_REQUEST;
		}

		if ( isset( $input['lang'] ) ) {
			MPHB()->translation()->switchLanguage( sanitize_text_field( $input['lang'] ) );
		}

		return $input;
	}

	/**
	 *
	 * @param string $action
	 */
	protected function verifyNonce( $action ) {
		if ( ! $this->checkNonce( $action ) ) {
			wp_send_json_error(
				array(
					'message' => __( 'Request does not pass security verification. Please refresh the page and try one more time.', 'motopress-hotel-booking' ),
				)
			);
		}
	}

	/**
	 *
	 * @return array
	 */
	public function getAdminNonces() {
		$nonces = array();
		foreach ( $this->ajaxActions as $actionName => $actionDetails ) {
			$nonces[ $this->actionPrefix . $actionName ] = wp_create_nonce( $this->actionPrefix . $actionName );
		}
		return $nonces;
	}

	/**
	 *
	 * @return array
	 */
	public function getFrontNonces() {
		$nonces = array();
		foreach ( $this->ajaxActions as $actionName => $actionDetails ) {
			if ( isset( $actionDetails['nopriv'] ) && $actionDetails['nopriv'] ) {
				$nonces[ 'mphb_' . $actionName ] = wp_create_nonce( 'mphb_' . $actionName );
			}
		}
		return $nonces;
	}

	public function export_bookings_csv() {
		 $this->verifyNonce( __FUNCTION__ );

		// Don't start another export when the previous one was not finished
		$exporter = MPHB()->getBookingsExporter();

		if ( $exporter->isInProgress() ) {
			wp_send_json_success();
		}

		// Prepare new export
		$input = $this->retrieveInput( __FUNCTION__ );
		$args  = isset( $input['args'] ) ? mphb_clean( $input['args'] ) : array();
		$query = new \MPHB\CSV\Bookings\BookingsQuery( $args );

		if ( $query->hasErrors() ) {
			wp_send_json_error( array( 'message' => $query->getErrorMessage() ) );
		} else {
			$args = $query->getInputs(); // Get validated inputs
		}

		// Save selected columns for next tries
		MPHB()->settings()->export()->setUserExportColumns( $args['columns'] );

		// Query bookings
		$ids = $query->query()->filterByRoomType( $args['room'] )->getIds();

		if ( empty( $ids ) ) {
			wp_send_json_error( array( 'message' => __( 'No bookings found for your request.', 'motopress-hotel-booking' ) ) );
		}

		// Try to create the file
		$exporter->setupOutput( $args );

		if ( ! file_exists( $exporter->pathToFile() ) ) {
			wp_send_json_error( array( 'message' => __( 'Uploads directory is not writable.', 'motopress-hotel-booking' ) ) );
		}

		// Start new export
		$exporter->data( $ids )->save();
		$exporter->dispatch();

		wp_send_json_success();
	}

	public function check_bookings_csv() {
		if ( ! current_user_can( \MPHB\UsersAndRoles\CapabilitiesAndRoles::EXPORT_REPORTS ) ) {
			wp_send_json_error();
		}

		$this->verifyNonce( __FUNCTION__ );

		$exporter   = MPHB()->getBookingsExporter();
		$isFinished = ! $exporter->isInProgress();

		if ( $isFinished ) {
			wp_send_json_success(
				array(
					'progress' => 100,
					'finished' => true,
					'file'     => $exporter->getDownloadLink(),
				)
			);
		} else {
			wp_send_json_success(
				array(
					'progress' => $exporter->getProgress(),
					'finished' => false,
				)
			);
		}
	}

	public function cancel_bookings_csv() {
		 $this->verifyNonce( __FUNCTION__ );

		$exporter  = MPHB()->getBookingsExporter();
		$isRunning = $exporter->isInProgress(); // Check before canceling the process

		if ( $isRunning && ! $exporter->isAborting() ) {
			$exporter->abort();
		}

		// Background process needs some time to cancel all tasks and stop completely
		wp_send_json_success( array( 'cancelled' => ! $isRunning ) );
	}

	/**
	 * @since 3.8.1
	 */
	public function install_plugin() {
		$this->verifyNonce( __FUNCTION__ );

		$input = $this->retrieveInput( __FUNCTION__ );

		if ( ! isset( $input['plugin_slug'], $input['plugin_zip'] ) ) {
			wp_send_json_error( array( 'message' => __( 'No enough data', 'motopress-hotel-booking' ) ) );
		}

		$pluginSlug = sanitize_text_field( $input['plugin_slug'] );
		$pluginZip  = sanitize_text_field( $input['plugin_zip'] );

		$installed = ThirdPartyPluginsUtils::isPluginInstalled( $pluginSlug );

		if ( ! $installed ) {
			$installed = ThirdPartyPluginsUtils::installPlugin( $pluginZip );
		}

		$activated = ThirdPartyPluginsUtils::isPluginActive( $pluginSlug )
			|| ( $installed && ThirdPartyPluginsUtils::activatePlugin( $pluginSlug ) );

		if ( $installed && $activated ) {
			wp_send_json_success();
		} else {
			wp_send_json_error( array( 'message' => __( 'An error has occurred', 'motopress-hotel-booking' ) ) ); // Very informative
		}
	}

	public function display_imported_bookings() {
		$this->verifyNonce( __FUNCTION__ );

		$input = $this->retrieveInput( __FUNCTION__ );

		if ( ! isset( $input['new_value'] ) || ! isset( $input['user_id'] ) ) {
			wp_send_json_error(
				array(
					'message' => __( 'Please complete all required fields and try again.', 'motopress-hotel-booking' ),
				)
			);
		}

		$newValue = Utils\ValidateUtils::validateBool( $input['new_value'] );
		$userId   = Utils\ValidateUtils::parseInt( $input['user_id'] );

		if ( $userId > 0 ) {
			MPHB()->settings()->main()->displayImportedBookings( $userId, $newValue );
		}

		wp_send_json_success();
	}

	public function recalculate_total() {

		$this->verifyNonce( __FUNCTION__ );

		$input = $this->retrieveInput( __FUNCTION__ );

		if (
			! isset( $input['formValues'] ) ||
			! is_array( $input['formValues'] ) ||
			! isset( $input['formValues']['post_ID'] )
		) {
			wp_send_json_error(
				array(
					'message' => __( 'An error has occurred, please try again later.', 'motopress-hotel-booking' ),
				)
			);
		}

		$bookingId = intval( $input['formValues']['post_ID'] );

		$atts = MPHB()->postTypes()->booking()->getEditPage()->getAttsFromRequest( $input['formValues'] );

		// Check Required Fields
		if (
			empty( $atts['mphb_check_in_date'] ) ||
			empty( $atts['mphb_check_out_date'] )
		) {
			wp_send_json_error(
				array(
					'message' => __( 'Please complete all required fields and try again.', 'motopress-hotel-booking' ),
				)
			);
		}

		$checkInDate  = \DateTime::createFromFormat( 'Y-m-d', $atts['mphb_check_in_date'] );
		$checkOutDate = \DateTime::createFromFormat( 'Y-m-d', $atts['mphb_check_out_date'] );

		$reservedRooms = MPHB()->getReservedRoomRepository()->findAllByBooking( $bookingId );

		$bookingAtts = array(
			'check_in_date'  => $checkInDate,
			'check_out_date' => $checkOutDate,
			'reserved_rooms' => $reservedRooms,
		);

		$booking = Entities\Booking::create( $bookingAtts );

		if ( MPHB()->settings()->main()->isCouponsEnabled() && ! empty( $input['formValues']['mphb_coupon_id'] ) ) {
			$coupon = MPHB()->getCouponRepository()->findById( intval( $input['formValues']['mphb_coupon_id'] ) );
			if ( $coupon ) {
				$booking->applyCoupon( $coupon );
			}
		}

		// array_walk_recursive() not required. wp_send_json_success() adds all
		// required slashes
		$priceBreakdown = $booking->getPriceBreakdown();

		wp_send_json_success(
			array(
				// [MB-684] Prevent excess number of digits
				'total'                => round( $booking->calcPrice(), MPHB()->settings()->currency()->getPriceDecimalsCount() ),
				'price_breakdown'      => json_encode( $priceBreakdown ),
				'price_breakdown_html' => \MPHB\Views\BookingView::generatePriceBreakdownArray( $priceBreakdown ),
			)
		);
	}

	/**
	 * @param string $input Date string.
	 *
	 * @return \DateTime
	 */
	protected function parseCheckInDate( $input ) {
		$checkInDate = \DateTime::createFromFormat( 'Y-m-d', $input );

		if ( ! $checkInDate ) {
			wp_send_json_error(
				array(
					'message' => __( 'Check-in date is not valid.', 'motopress-hotel-booking' ),
				)
			);
		}

		return $checkInDate;
	}

	/**
	 * @param string $input Date string.
	 *
	 * @return \DateTime
	 */
	protected function parseCheckOutDate( $input ) {
		$checkOutDate = \DateTime::createFromFormat( 'Y-m-d', $input );

		if ( ! $checkOutDate ) {
			wp_send_json_error(
				array(
					'message' => __( 'Check-out date is not valid.', 'motopress-hotel-booking' ),
				)
			);
		}

		return $checkOutDate;
	}

	protected function parseAdults( $input, $allowEmptyString = false ) {
		$adults = Utils\ValidateUtils::validateInt( $input, 1 );

		if ( $adults === false ) {
			if ( $allowEmptyString ) {
				return '';
			}

			$errorMessage = __( 'The number of adults is not valid.', 'motopress-hotel-booking' );

			if ( ! MPHB()->settings()->main()->isChildrenAllowed() ) {

				$errorMessage = __( 'The number of guests is not valid.', 'motopress-hotel-booking' );
			}

			wp_send_json_error(
				array(
					'message' => $errorMessage,
				)
			);
		}

		return $adults;
	}

	protected function parseChildren( $input, $allowEmptyString = false ) {
		$children = Utils\ValidateUtils::validateInt( $input, 0 );

		if ( $children === false ) {
			if ( $allowEmptyString ) {
				return '';
			}

			wp_send_json_error(
				array(
					'message' => __( 'Children number is not valid.', 'motopress-hotel-booking' ),
				)
			);
		}

		return $children;
	}

	public function update_rate_prices() {

		$this->verifyNonce( __FUNCTION__ );

		$input = $this->retrieveInput( __FUNCTION__ );

		if ( ! isset( $input['rates'], $input['adults'], $input['children'], $input['check_in_date'], $input['check_out_date'] ) ||
			! is_array( $input['rates'] )
		) {
			wp_send_json_error(
				array(
					'message' => __( 'An error has occurred. Please try again later.', 'motopress-hotel-booking' ),
				)
			);
		}

		$rates        = \MPHB\Utils\ValidateUtils::validateIds( $input['rates'] );
		$adults       = $this->parseAdults( $input['adults'], true );
		$children     = $this->parseChildren( $input['children'], true );
		$checkInDate  = $this->parseCheckInDate( $input['check_in_date'] );
		$checkOutDate = $this->parseCheckInDate( $input['check_out_date'] );

		MPHB()->reservationRequest()->setupParameters(
			array(
				'adults'         => $adults,
				'children'       => $children,
				'check_in_date'  => $checkInDate,
				'check_out_date' => $checkOutDate,
			)
		);

		$prices = array();

		foreach ( $rates as $rateId ) {
			$rate = MPHB()->getRateRepository()->findById( $rateId );

			if ( ! $rate ) {
				continue;
			}

			$price             = $rate->calcPrice( $checkInDate, $checkOutDate );
			$prices[ $rateId ] = mphb_format_price( $price );
		}

		wp_send_json_success( $prices );
	}

	/**
	 * Parse booking from checkout form values.
	 *
	 * @param array $input
	 * @return Entities\Booking
	 */
	protected function parseCheckoutFormBooking( $input ) {

		$isSetRequiredFields = isset( $input['formValues'] ) &&
			is_array( $input['formValues'] ) &&
			! empty( $input['formValues']['mphb_room_details'] ) &&
			is_array( $input['formValues']['mphb_room_details'] ) &&
			! empty( $input['formValues']['mphb_check_in_date'] ) &&
			! empty( $input['formValues']['mphb_check_out_date'] );

		if ( $isSetRequiredFields ) {
			foreach ( $input['formValues']['mphb_room_details'] as &$roomDetails ) {
				if (
					! is_array( $roomDetails ) ||
					empty( $roomDetails['room_type_id'] ) ||
					! isset( $roomDetails['adults'] ) ||
					empty( $roomDetails['rate_id'] )
				) {
					$isSetRequiredFields = false;
					break;
				}

				if ( ! isset( $roomDetails['children'] ) ) {
					$roomDetails['children'] = 0;
				}
			}
			unset( $roomDetails );
		}

		if ( ! $isSetRequiredFields ) {
			wp_send_json_error(
				array(
					'message' => __( 'An error has occurred. Please try again later.', 'motopress-hotel-booking' ),
				)
			);
		}

		$atts = $input['formValues'];

		$checkInDate  = $this->parseCheckInDate( $atts['mphb_check_in_date'] );
		$checkOutDate = $this->parseCheckOutDate( $atts['mphb_check_out_date'] );

		$reservedRooms = array();

		foreach ( $atts['mphb_room_details'] as $roomDetails ) {

			$roomTypeId = Utils\ValidateUtils::validateInt( $roomDetails['room_type_id'], 0 );
			$roomType   = $roomTypeId ? MPHB()->getRoomTypeRepository()->findById( $roomTypeId ) : null;
			if ( ! $roomType ) {
				wp_send_json_error(
					array(
						'message' => __( 'Accommodation Type is not valid.', 'motopress-hotel-booking' ),
					)
				);
			}

			$roomRateId = Utils\ValidateUtils::validateInt( $roomDetails['rate_id'], 0 );
			$roomRate   = $roomRateId ? MPHB()->getRateRepository()->findById( $roomRateId ) : null;
			if ( ! $roomRate ) {
				wp_send_json_error(
					array(
						'message' => __( 'Rate is not valid.', 'motopress-hotel-booking' ),
					)
				);
			}

			$adults   = $this->parseAdults( $roomDetails['adults'] );
			$children = $this->parseChildren( $roomDetails['children'] );

			if ( $roomType->hasLimitedTotalCapacity() && $adults + $children > $roomType->getTotalCapacity() ) {
				wp_send_json_error(
					array(
						'message' => __( 'The total number of guests is not valid.', 'motopress-hotel-booking' ),
					)
				);
			}

			$allowedServices = $roomType->getServices();

			$services = array();

			if ( ! empty( $roomDetails['services'] ) && is_array( $roomDetails['services'] ) ) {
				foreach ( $roomDetails['services'] as $serviceDetails ) {

					if ( empty( $serviceDetails['id'] ) || ! in_array( $serviceDetails['id'], $allowedServices ) ) {
						continue;
					}

					$serviceAdults = Utils\ValidateUtils::validateInt( $serviceDetails['adults'] );
					if ( $serviceAdults === false || $serviceAdults < 1 ) {
						continue;
					}

					$quantity = isset( $serviceDetails['quantity'] ) ? Utils\ValidateUtils::validateInt( $serviceDetails['quantity'] ) : 1;
					if ( isset( $serviceDetails['quantity'] ) && $quantity < 1 ) {
						continue;
					}

					$services[] = Entities\ReservedService::create(
						array(
							'id'       => (int) $serviceDetails['id'],
							'adults'   => $serviceAdults,
							'quantity' => $quantity,
						)
					);
				}
			}
			$services = array_filter( $services );

			$reservedRoomAtts = array(
				'room_type_id'      => $roomTypeId,
				'rate_id'           => $roomRateId,
				'adults'            => $adults,
				'children'          => $children,
				'reserved_services' => $services,
			);

			$reservedRooms[] = Entities\ReservedRoom::create( $reservedRoomAtts );
		}

		$bookingAtts = array(
			'check_in_date'  => $checkInDate,
			'check_out_date' => $checkOutDate,
			'reserved_rooms' => $reservedRooms,
		);

		$booking = Entities\Booking::create( $bookingAtts );

		if (
			MPHB()->settings()->main()->isCouponsEnabled() &&
			! empty( $input['formValues']['mphb_applied_coupon_code'] )
		) {
			$coupon = MPHB()->getCouponRepository()->findByCode( $input['formValues']['mphb_applied_coupon_code'] );
			if ( $coupon ) {
				$booking->applyCoupon( $coupon );
			}
		}

		return $booking;
	}

	/**
	 * @since 3.6.0
	 */
	public function create_stripe_payment_intent() {
		$this->verifyNonce( __FUNCTION__ );

		$input = $this->retrieveInput( __FUNCTION__ );

		if ( ! isset( $input['amount'] ) ) {
			wp_send_json_error(
				array(
					'message' => __( 'Please complete all required fields and try again.', 'motopress-hotel-booking' ),
				)
			);
		}

		$amount          = floatval( $input['amount'] );
		$description     = isset( $input['description'] ) ? mphb_clean( $input['description'] ) : '';
		$paymentMethodId = isset( $input['paymentMethodId'] ) ? $input['paymentMethodId'] : null;

		$currency  = MPHB()->settings()->currency()->getCurrencyCode();
		$stripeApi = MPHB()->gatewayManager()->getGateway( 'stripe' )->getApi();

		if ( ! $stripeApi->checkMinimumAmount( $amount, $currency ) ) {
			$minimumAmount = $stripeApi->getMinimumAmount( $currency );
			$minimumPrice  = mphb_format_price( $minimumAmount );

			wp_send_json_error(
				array(
					'message' => sprintf( __( 'Sorry, the minimum allowed payment amount is %s to use this payment method.', 'motopress-hotel-booking' ), $minimumPrice ),
				)
			);
		}

		$idempotencyKey = isset( $input['idempotencyKey'] ) ? $input['idempotencyKey'] : '';

		$response = $stripeApi->createPaymentIntent( $amount, $description, $currency, array( 'idempotency_key' => $idempotencyKey ), $paymentMethodId );

		if ( is_wp_error( $response ) ) {
			wp_send_json_error(
				array(
					'message' => $response->get_error_message(),
				)
			);
		}

		wp_send_json_success(
			array(
				'id'            => $response->id,
				'client_secret' => $response->client_secret,
			)
		);
	}

	public function update_checkout_info() {

		$this->verifyNonce( __FUNCTION__ );

		$input = $this->retrieveInput( __FUNCTION__ );

		$booking = $this->parseCheckoutFormBooking( $input );

		$total = $booking->calcPrice();

		$priceHtml = mphb_format_price( $total );

		$responseData = array(
			'newAmount'      => $total,
			'priceHtml'      => $priceHtml,
			'priceBreakdown' => Views\BookingView::generatePriceBreakdown( $booking ),
		);

		if ( MPHB()->settings()->main()->getConfirmationMode() === 'payment' ) {
			$responseData['depositAmount'] = $booking->calcDepositAmount();
			$responseData['depositPrice']  = mphb_format_price( $responseData['depositAmount'] );

			$responseData['gateways'] = array_map(
				function( $gateway ) use ( $booking ) {
					return $gateway->getCheckoutData( $booking );
				},
				MPHB()->gatewayManager()->getListActive()
			);

			$responseData['isFree'] = $total == 0;
		}

		wp_send_json_success( $responseData );
	}

	public function get_billing_fields() {

		$this->verifyNonce( __FUNCTION__ );

		$input = $this->retrieveInput( __FUNCTION__ );

		$gatewayId = ! empty( $input['mphb_gateway_id'] ) ? mphb_clean( $input['mphb_gateway_id'] ) : '';

		if ( ! array_key_exists( $gatewayId, MPHB()->gatewayManager()->getListActive() ) ) {
			wp_send_json_error(
				array(
					'message' => __( 'Chosen payment method is not available. Please refresh the page and try one more time.', 'motopress-hotel-booking' ),
				)
			);
		}

		$booking = $this->parseCheckoutFormBooking( $input );

		ob_start();
		MPHB()->gatewayManager()->getGateway( $gatewayId )->renderPaymentFields( $booking );
		$fields = ob_get_clean();

		wp_send_json_success(
			array(
				'fields'           => $fields,
				'hasVisibleFields' => MPHB()->gatewayManager()->getGateway( $gatewayId )->hasVisiblePaymentFields(),
			)
		);
	}

	public function get_rates_for_room() {

		$this->verifyNonce( __FUNCTION__ );

		$input = $this->retrieveInput( __FUNCTION__ );

		$titlesList = array();

		if (
			isset( $input['formValues'] ) &&
			is_array( $input['formValues'] ) &&
			! empty( $input['formValues']['mphb_room_id'] )
		) {
			$roomId = absint( $input['formValues']['mphb_room_id'] );
			$room   = MPHB()->getRoomRepository()->findById( $roomId );

			if ( ! $room ) {
				wp_send_json_success(
					array(
						'options' => array(),
					)
				);
			}

			foreach ( MPHB()->getRateRepository()->findAllActiveByRoomType( $room->getRoomTypeId() ) as $rate ) {
				$titlesList[ $rate->getId() ] = $rate->getTitle();
			}
		}

		wp_send_json_success(
			array(
				'options' => $titlesList,
			)
		);
	}

	public function apply_coupon() {

		$this->verifyNonce( __FUNCTION__ );

		$input = $this->retrieveInput( __FUNCTION__ );

		$booking = $this->parseCheckoutFormBooking( $input );

		$responseData = array();

		if ( MPHB()->settings()->main()->isCouponsEnabled() && isset( $input['mphb_coupon_code'] ) ) {

			$coupon = MPHB()->getCouponRepository()->findByCode( $input['mphb_coupon_code'] );

			if ( $coupon ) {
				$couponApplied = $booking->applyCoupon( $coupon );

				if ( is_wp_error( $couponApplied ) ) {
					$responseData['coupon'] = array(
						'applied_code' => '',
						'message'      => $couponApplied->get_error_message(),
					);
				} else {
					$responseData['coupon'] = array(
						'applied_code' => $booking->getCouponCode(),
						'message'      => __( 'Coupon applied successfully.', 'motopress-hotel-booking' ),
					);
				}
			} else {
				$responseData['coupon'] = array(
					'applied_code' => '',
					'message'      => __( 'Coupon is not valid.', 'motopress-hotel-booking' ),
				);
			}
		}

		$total = $booking->calcPrice();

		$responseData['newAmount']      = $total;
		$responseData['priceHtml']      = mphb_format_price( $total );
		$responseData['priceBreakdown'] = Views\BookingView::generatePriceBreakdown( $booking );

		if ( MPHB()->settings()->main()->getConfirmationMode() === 'payment' ) {
			$responseData['depositAmount'] = $booking->calcDepositAmount();
			$responseData['depositPrice']  = mphb_format_price( $responseData['depositAmount'] );

			$responseData['gateways'] = array_map(
				function( $gateway ) use ( $booking ) {
					return $gateway->getCheckoutData( $booking );
				},
				MPHB()->gatewayManager()->getListActive()
			);

			$responseData['isFree'] = $total == 0;
		}

		wp_send_json_success( $responseData );
	}

	public function dismiss_license_notice() {

		$this->verifyNonce( __FUNCTION__ );

		MPHB()->settings()->license()->setNeedHideNotice( true );

		wp_send_json_success();
	}

	public function attributes_custom_ordering() {

		$this->verifyNonce( __FUNCTION__ );

		$termId       = isset( $_POST['term_id'] ) ? absint( $_POST['term_id'] ) : 0;
		$nextTermId   = ( isset( $_POST['next_term_id'] ) && absint( $_POST['next_term_id'] ) ) ? absint( $_POST['next_term_id'] ) : null;
		$taxonomyName = isset( $_POST['taxonomy_name'] ) ? esc_attr( sanitize_text_field( wp_unslash( $_POST['taxonomy_name'] ) ) ) : null;

		if ( ! $termId || ! $taxonomyName ) {
			wp_send_json_error();
		}

		if ( ! term_exists( $termId, $taxonomyName ) ) {
			wp_send_json_error();
		}

		mphb_reorder_attributes( $termId, $nextTermId, $taxonomyName );

		wp_send_json_success();
	}

	public function ical_sync_abort() {
		$this->verifyNonce( __FUNCTION__ );

		MPHB()->getQueuedSynchronizer()->abortAll();

		wp_send_json_success();
	}

	public function ical_sync_clear_all() {
		$this->verifyNonce( __FUNCTION__ );

		MPHB()->getQueuedSynchronizer()->clearAll();

		wp_send_json_success();
	}

	public function ical_sync_remove_item() {
		$this->verifyNonce( __FUNCTION__ );

		// phpcs:ignore WordPress.Security.ValidatedSanitizedInput
		$roomKey = mphb_clean( wp_unslash( $_POST['mphb_room_key'] ) );

		MPHB()->getQueuedSynchronizer()->removeItem( $roomKey );

		wp_send_json_success();
	}

	public function ical_sync_get_progress() {
		$this->verifyNonce( __FUNCTION__ );

		// phpcs:ignore WordPress.Security.ValidatedSanitizedInput
		$items    = isset( $_POST['focus'] ) ? (array) $_POST['focus'] : array();
		$queue    = iCal\Queue::selectItems( $items );
		$queueIds = array_keys( $queue );
		$stats    = iCal\Stats::selectStats( $queueIds );

		$processedItems = array();

		foreach ( $queueIds as $queueId ) {
			$item = array();

			$queueName = $queue[ $queueId ]['queue'];

			$status      = $queue[ $queueId ]['status'];
			$statusClass = 'mphb-status-' . $status;

			switch ( $status ) {
				case iCal\Queue::STATUS_WAIT:
					$statusText = __( 'Waiting', 'motopress-hotel-booking' );
					break;
				case iCal\Queue::STATUS_IN_PROGRESS:
					$statusText = __( 'Processing', 'motopress-hotel-booking' );
					break;
				case iCal\Queue::STATUS_DONE:
					$statusText = __( 'Done', 'motopress-hotel-booking' );
					break;

				default:
					$statusText = ucfirst( str_replace( '-', ' ', $status ) );
					break;
			}

			$itemStats = $stats[ $queueId ];

			$processedItems[ $queueName ] = array(
				'status' => array(
					'code'  => $status,
					'class' => $statusClass,
					'text'  => $statusText,
				),
				'stats'  => $itemStats,
			);
		}

		wp_send_json_success(
			array(
				'items'      => $processedItems,
				'inProgress' => MPHB()->getQueuedSynchronizer()->isInProgress(),
			)
		);
	}

	public function ical_upload_get_progress() {
		$this->verifyNonce( __FUNCTION__ );

		$logsShown      = isset( $_GET['logsShown'] ) ? absint( $_GET['logsShown'] ) : 0;
		$logsHandler    = new \MPHB\iCal\LogsHandler();
		$uploader       = MPHB()->getICalUploader();
		$processDetails = $uploader->getDetails( $logsShown );
		$logs           = $processDetails['logs'];
		$stats          = $processDetails['stats'];
		$isFinished     = ! $uploader->isInProgress();
		$notice         = '';

		// Build notice
		if ( $isFinished ) {
			$notice = $logsHandler->buildNotice( $stats['succeed'], $stats['failed'] );
		}

		// Calculate new "logsShown"
		$logsShown += count( $logs );

		wp_send_json_success(
			array(
				'total'      => $stats['total'],
				'succeed'    => $stats['succeed'],
				'skipped'    => $stats['skipped'],
				'failed'     => $stats['failed'],
				'removed'    => $stats['removed'],
				'progress'   => $uploader->getProgress(),
				'logs'       => $logsHandler->logsToHtml( $logs ),
				'logsShown'  => $logsShown,
				'notice'     => $notice,
				'isFinished' => $isFinished,
			)
		);
	}

	public function ical_upload_abort() {
		$this->verifyNonce( __FUNCTION__ );

		MPHB()->getICalUploader()->abort();

		wp_send_json_success();
	}


	public function get_accommodations_list() {
		$this->verifyNonce( __FUNCTION__ );

		$input = $this->retrieveInput( __FUNCTION__ );

		$formValues = $input['formValues'];
		$typeId     = ( isset( $formValues['room_type_id'] ) ) ? (int) $formValues['room_type_id'] : 0;
		$roomsList  = mphb_get_rooms_select_list( $typeId );

		wp_send_json_success( array( 'options' => $roomsList ) );
	}


	public function remove_customer() {
		if ( ! isset( $_POST['itemId'] ) ) {
			wp_die();
		}

		$this->verifyNonce( __FUNCTION__ );

		if ( ! current_user_can( \MPHB\UsersAndRoles\CapabilitiesAndRoles::DELETE_CUSTOMER ) ) {
			wp_die( esc_html__( 'You do not have permission to do this action.', 'motopress-hotel-booking' ) );
		}

		$customerId = (int) $_POST['itemId'];

		$deleted = \MPHB\UsersAndRoles\Customers::delete( $customerId );

		echo wp_json_encode( $deleted );

		wp_die();
	}

}
