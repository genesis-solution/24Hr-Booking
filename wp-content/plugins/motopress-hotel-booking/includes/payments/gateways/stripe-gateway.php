<?php

namespace MPHB\Payments\Gateways;

use \MPHB\Admin\Fields;
use \MPHB\Admin\Groups;
use \MPHB\PostTypes\PaymentCPT\Statuses as PaymentStatuses;
use MPHB\Shortcodes\CheckoutShortcode;

/**
 * @since 3.6.0 the gateway has been completely rewritten.
 * @since 3.6.0 added new payment methods: Bancontact, Giropay, iDEAL, SEPA Direct Debit, and SOFORT.
 * @since 3.6.0 removed the method retrieveStripeAmount() (moved to StripeAPI6::convertToSmallestUnit()).
 * @since 3.6.0 removed the method checkMinimumAmount() (moved to StripeAPI6).
 * @since 3.6.0 removed the method getMinimumAmount() (moved to StripeAPI6).
 */
class StripeGateway extends Gateway {

	/**
	 * @var string
	 */
	protected $publicKey = '';

	/**
	 * @var string
	 */
	protected $secretKey = '';

	/**
	 * @var string
	 */
	protected $endpointSecret = '';

	/**
	 * @var string[] "card", "ideal", "sepa_debit" etc.
	 */
	protected $paymentMethods = array();

	/**
	 * @var string[] Equal to $paymentMethods if the currency is euro, ["card"]
	 * otherwise.
	 */
	protected $allowedMethods = array();

	/**
	 * @var string
	 */
	protected $locale = 'auto';

	/**
	 * @var \MPHB\Payments\Gateways\Stripe\StripeAPI6
	 */
	protected $api = null;

	/**
	 * @var \MPHB\Payments\Gateways\Stripe\WebhookListener
	 */
	protected $webhookListener = null;

	// See method parsePaymentFields()
	protected $paymentFields = array(
		'payment_method'    => 'card',
		'payment_intent_id' => '',
		'source_id'         => '',
		'redirect_url'      => '',
	);

	public function __construct() {
		add_filter( 'mphb_gateway_has_instructions', array( $this, 'hideInstructions' ), 10, 2 );

		parent::__construct();

		$this->api = new Stripe\StripeAPI6(
			array(
				'secret_key' => $this->secretKey,
			)
		);

		if ( $this->isActive() ) {
			$this->setupWebhooks();

			$this->adminDescription = sprintf( __( 'Webhooks Destination URL: %s', 'motopress-hotel-booking' ), '<code>' . esc_url( $this->webhookListener->getNotifyUrl() ) . '</code>' );

			add_action( 'wp_enqueue_scripts', array( $this, 'enqueueScripts' ) );
		}
	}

	protected function initId() {
		return 'stripe';
	}

	/**
	 * Whether is Gateway Eanbled and support current plugin settings (currency, etc.)
	 *
	 * @return boolean
	 */
	public function isActive() {
		return parent::isActive() &&
			! empty( $this->publicKey ) &&
			! empty( $this->secretKey );
	}

	/**
	 * @param bool   $show
	 * @param string $gatewayId
	 * @return bool
	 *
	 * @since 3.6.1
	 */
	public function hideInstructions( $show, $gatewayId ) {
		if ( $gatewayId == $this->id ) {
			$show = false;
		}

		return $show;
	}

	protected function setupWebhooks() {
		$args = array(
			'gatewayId'       => $this->getId(),
			'sandbox'         => $this->isSandbox,
			'secret_key'      => $this->secretKey,
			'endpoint_secret' => $this->endpointSecret,
		);

		$this->webhookListener = new Stripe\WebhookListener( $args );
	}

	protected function setupProperties() {
		parent::setupProperties();

		$this->adminTitle     = __( 'Stripe', 'motopress-hotel-booking' );
		$this->publicKey      = $this->getOption( 'public_key' );
		$this->secretKey      = $this->getOption( 'secret_key' );
		$this->endpointSecret = $this->getOption( 'endpoint_secret' );
		$this->paymentMethods = $this->getOption( 'payment_methods' );
		$this->locale         = $this->getOption( 'locale' );

		// Add "card" to payment methods
		if ( ! is_array( $this->paymentMethods ) ) {
			$this->paymentMethods = array( 'card' );
		} elseif ( ! in_array( 'card', $this->paymentMethods ) ) {
			$this->paymentMethods = array_merge( array( 'card' ), $this->paymentMethods );
		}

		// Filter unallowed methods
		if ( MPHB()->settings()->currency()->getCurrencyCode() == 'EUR' ) {
			$this->allowedMethods = $this->paymentMethods;
		} else {
			$this->allowedMethods = array( 'card' );
		}

		if ( $this->isSandbox ) {
			$this->description .= ' ' . sprintf( __( 'Use the card number %1$s with CVC %2$s, a valid expiration date and random 5-digit ZIP-code to test a payment.', 'motopress-hotel-booking' ), '4242424242424242', '123' );
			$this->description  = trim( $this->description );
		}
	}

	public function enqueueScripts() {
		if ( mphb_is_checkout_page() ) {
			wp_enqueue_script( 'mphb-vendor-stripe-library' );
		}
	}

	protected function initDefaultOptions() {
		$defaults = array(
			'title'           => __( 'Pay by Card (Stripe)', 'motopress-hotel-booking' ),
			'description'     => __( 'Pay with your credit card via Stripe.', 'motopress-hotel-booking' ),
			'enabled'         => false,
			'is_sandbox'      => false,
			'public_key'      => '',
			'secret_key'      => '',
			'endpoint_secret' => '',
			'payment_methods' => array(),
			'locale'          => 'auto',
		);

		return array_merge( parent::initDefaultOptions(), $defaults );
	}

	public function registerOptionsFields( &$subtab ) {
		parent::registerOptionsFields( $subtab );

		// Show warning if the SSL not enabled
		if ( ! MPHB()->isSiteSSL() && ( ! MPHB()->settings()->payment()->isForceCheckoutSSL() && ! class_exists( 'WordPressHTTPS' ) ) ) {
			$enableField = $subtab->findField( "mphb_payment_gateway_{$this->id}_enable" );

			if ( ! is_null( $enableField ) ) {
				if ( $this->isActive() ) {
					$message = __( '%1$s is enabled, but the <a href="%2$s">Force Secure Checkout</a> option is disabled. Please enable SSL and ensure your server has a valid SSL certificate. Otherwise, %1$s will only work in Test Mode.', 'motopress-hotel-booking' );
				} else {
					$message = __( 'The <a href="%2$s">Force Secure Checkout</a> option is disabled. Please enable SSL and ensure your server has a valid SSL certificate. Otherwise, %1$s will only work in Test Mode.', 'motopress-hotel-booking' );
				}

				$message = sprintf( $message, __( 'Stripe', 'motopress-hotel-booking' ), esc_url( MPHB()->getSettingsMenuPage()->getUrl( array( 'tab' => 'payments' ) ) ) );

				$enableField->setDescription( $message );
			}
		}

		$group = new Groups\SettingsGroup( "mphb_payments_{$this->id}_group1", '', $subtab->getOptionGroupName() );

		$paymentMethods = array(
			'bancontact' => __( 'Bancontact', 'motopress-hotel-booking' ),
			'ideal'      => __( 'iDEAL', 'motopress-hotel-booking' ),
			'giropay'    => __( 'Giropay', 'motopress-hotel-booking' ),
			'sepa_debit' => __( 'SEPA Direct Debit', 'motopress-hotel-booking' ),
			'sofort'     => __( 'SOFORT', 'motopress-hotel-booking' ),
		);

		$paymentsWarning = '';

		if ( count( $this->allowedMethods ) != count( $this->paymentMethods ) ) {
			$paymentsWarning = '<span class="notice notice-warning">' . __( 'Euro is the only acceptable currency for the selected payment methods. Change your currency to Euro in General settings.', 'motopress-hotel-booking' ) . '</span>';
		}

		$groupFields = array(
			Fields\FieldFactory::create(
				"mphb_payment_gateway_{$this->id}_public_key",
				array(
					'type'        => 'text',
					'label'       => __( 'Public Key', 'motopress-hotel-booking' ),
					'default'     => $this->getDefaultOption( 'public_key' ),
					'description' => '<a href="https://support.stripe.com/questions/locate-api-keys" target="_blank">Find API Keys</a>',
				)
			),
			Fields\FieldFactory::create(
				"mphb_payment_gateway_{$this->id}_secret_key",
				array(
					'type'    => 'text',
					'label'   => __( 'Secret Key', 'motopress-hotel-booking' ),
					'default' => $this->getDefaultOption( 'secret_key' ),
				)
			),
			Fields\FieldFactory::create(
				"mphb_payment_gateway_{$this->id}_endpoint_secret",
				array(
					'type'        => 'text',
					'label'       => __( 'Webhook Secret', 'motopress-hotel-booking' ),
					'description' => '<a href="https://stripe.com/docs/webhooks/setup#configure-webhook-settings" target="_blank">Setting Up Webhooks</a>',
					'default'     => $this->getDefaultOption( 'endpoint_secret' ),
				)
			),
			Fields\FieldFactory::create(
				"mphb_payment_gateway_{$this->id}_payment_methods",
				array(
					'type'                => 'multiple-checkbox',
					'label'               => __( 'Payment Methods', 'motopress-hotel-booking' ),
					'always_enabled'      => array( 'card' => __( 'Card Payments', 'motopress-hotel-booking' ) ),
					'list'                => $paymentMethods,
					'description'         => $paymentsWarning,
					'default'             => $this->getDefaultOption( 'payment_methods' ),
					'allow_group_actions' => false, // Disable "Select All" and "Unselect All"
				)
			),
			Fields\FieldFactory::create(
				"mphb_payment_gateway_{$this->id}_locale",
				array(
					'type'        => 'select',
					'label'       => __( 'Checkout Locale', 'motopress-hotel-booking' ),
					'list'        => $this->getAvailableLocales(),
					'default'     => $this->getDefaultOption( 'locale' ),
					'description' => __( 'Display Checkout in the user\'s preferred language, if available.', 'motopress-hotel-booking' ),
				)
			),
		);

		$group->addFields( $groupFields );

		$subtab->addGroup( $group );
	}

	public function initPaymentFields() {
		$fields = array(
			'mphb_stripe_payment_method'    => array(
				'type'     => 'hidden',
				'required' => true,
			),
			'mphb_stripe_payment_intent_id' => array(
				'type'     => 'hidden',
				'required' => false,
			),
			'mphb_stripe_source_id'         => array(
				'type'     => 'hidden',
				'required' => false,
			),
			'mphb_stripe_redirect_url'      => array(
				'type'     => 'hidden',
				'required' => false,
			),
		);

		return $fields;
	}

	public function parsePaymentFields( $input, &$errors ) {
		$isParsed = parent::parsePaymentFields( $input, $errors );

		if ( $isParsed ) {
			foreach ( array( 'payment_method', 'payment_intent_id', 'source_id', 'redirect_url' ) as $param ) {
				$field = 'mphb_stripe_' . $param;

				if ( isset( $this->postedPaymentFields[ $field ] ) ) {
					$this->paymentFields[ $param ] = $this->postedPaymentFields[ $field ];
					unset( $this->postedPaymentFields[ $field ] );
				}
			}
		}

		return $isParsed;
	}

	/**
	 * @param \MPHB\Entities\Booking $booking
	 * @param \MPHB\Entities\Payment $payment
	 */
	public function processPayment( \MPHB\Entities\Booking $booking, \MPHB\Entities\Payment $payment ) {
		$paymentMethod   = $this->paymentFields['payment_method'];
		$paymentIntentId = $this->paymentFields['payment_intent_id'];
		$sourceId        = $this->paymentFields['source_id'];
		$redirectUrl     = $this->paymentFields['redirect_url'];

		// Verify all values
		if ( empty( $paymentMethod ) ) {
			$payment->addLog( __( 'The payment method is not selected.', 'motopress-hotel-booking' ) );
			$this->paymentFailed( $payment );
		}

		if ( $paymentMethod == 'card' ) {
			if ( empty( $paymentIntentId ) ) {
				$payment->addLog( __( 'Payment intent ID is not set.', 'motopress-hotel-booking' ) );
				$this->paymentFailed( $payment );
			}
		} else {
			if ( empty( $sourceId ) ) {
				$payment->addLog( __( 'Source ID is not set.', 'motopress-hotel-booking' ) );
				$this->paymentFailed( $payment );
			}
		}

		// If verification failed - stop here
		if ( $payment->getStatus() == PaymentStatuses::STATUS_FAILED ) {
			wp_redirect( MPHB()->settings()->pages()->getPaymentFailedPageUrl( $payment ) );
			exit;
		}

		// Process payment
		update_post_meta( $payment->getId(), '_mphb_payment_type', $paymentMethod );

		if ( $paymentMethod == 'card' ) {
			$this->processCardPayment( $payment, $paymentIntentId, $booking );
		} else {
			$this->processSourcePayment( $payment, $sourceId, $redirectUrl );
		}
	}

	/**
	 * @param \MPHB\Entities\Payment $payment
	 * @param string                 $paymentIntentId
	 * @param \MPHB\Entities\Booking $booking
	 *
	 * @since 3.7.4 added new argument - $booking.
	 */
	public function processCardPayment( \MPHB\Entities\Payment $payment, $paymentIntentId, $booking ) {
		update_post_meta( $payment->getId(), '_mphb_transaction_id', $paymentIntentId );

		$payment->setTransactionId( $paymentIntentId );

		try {
			$paymentIntent = $this->api->retrievePaymentIntent( $paymentIntentId );
			$this->api->confirmPaymentIntent( $paymentIntent );

			$intentStatus = $paymentIntent->status;

			/*
			 * https://stripe.com/docs/payments/intents#intent-statuses
			 *
			 * Stripe has many statuses, but we are using only 2 of them:
			 * "succeeded" and "processing". "canceled" and other will not pass
			 * checks from stripe-gateway.js.
			 */
			if ( $intentStatus == 'succeeded' ) {
				// translators: %s - Stripe PaymentIntent ID
				$payment->addLog( sprintf( __( 'Payment for PaymentIntent %s succeeded.', 'motopress-hotel-booking' ), $paymentIntentId ) );
				$this->paymentCompleted( $payment );

			} else { // "processing"
				// translators: %s - Stripe PaymentIntent ID
				$payment->addLog( sprintf( __( 'Payment for PaymentIntent %s is processing.', 'motopress-hotel-booking' ), $paymentIntentId ) );
				$this->paymentOnHold( $payment );
			}

			// Set description to "Reservation #..." when we know the booking ID
			$description = $this->generateItemName( $booking );
			$this->getApi()->updateDescription( $paymentIntent, $description );

			wp_redirect( MPHB()->settings()->pages()->getReservationReceivedPageUrl( $payment ) );

		} catch ( \Exception $e ) {
			$payment->addLog( sprintf( __( 'Failed to process Card payment. %s', 'motopress-hotel-booking' ), $e->getMessage() ) );
			wp_redirect( MPHB()->settings()->pages()->getPaymentFailedPageUrl( $payment ) );
		}

		exit;
	}

	/**
	 * @param \MPHB\Entities\Payment $payment
	 * @param string                 $sourceId
	 * @param string                 $redirectUrl
	 *
	 * @since 3.9.6 - support for the SEPA_DEBIT payment's 'chargeable' status added.
	 */
	public function processSourcePayment( \MPHB\Entities\Payment $payment, $sourceId, $redirectUrl ) {
		$paymentStatus = 'success'; // "success", "failed", "redirect"

		try {
			$source     = $this->api->retrieveSource( $sourceId );
			$status     = $source->status;
			$sourceType = $source->type;

			update_post_meta( $payment->getId(), '_mphb_transaction_source_id', $sourceId );

			// Later we will use transaction_id meta field to save Charge's ID
			$payment->setTransactionId( $sourceId );

			// All source statuses: https://stripe.com/docs/api/sources/object#source_object-status
			// ("chargeable" is impossible, now we have processCardPayment() for card payments)
			if ( $status == 'pending' ) {
				// Bancontact, iDEAL, Giropay, SEPA Direct Debit, SOFORT
				if ( ! empty( $redirectUrl ) ) {
					// translators: %s - Stripe Source ID
					$message = sprintf( __( 'Payment source %s is waiting for customer confirmation.', 'motopress-hotel-booking' ), $sourceId );
					$payment->addLog( $message );

					$paymentStatus = 'redirect';
					$this->paymentOnHold( $payment );

				} else {
					// translators: %s - Stripe Source ID
					$message = sprintf( __( 'Pending source %s received, but the redirect URL is empty.', 'motopress-hotel-booking' ), $sourceId );
					$payment->addLog( $message );

					$paymentStatus = 'failed';
					$this->paymentFailed( $payment );
				}
			} elseif ( $sourceType == 'sepa_debit' && $status == 'chargeable' ) { // In case of SEPA Debit payment a source object can be charged immediatelly without customer's confirmation

				$this->paymentOnHold( $payment );

			} else {
				$paymentStatus = 'failed';

				switch ( status ) {
					case 'canceled':
						// translators: %s - Stripe Source ID
						$message = sprintf( __( 'Payment source %s was cancelled by customer.', 'motopress-hotel-booking' ), $sourceId );
						break;
					case 'failed':
						// translators: %s - Stripe Source ID
						$message = sprintf( __( "Payment source %s failed and couldn't be processed.", 'motopress-hotel-booking' ), $sourceId );
						break;
					default: // "consumed" (or "chargeable")
						// translators: %1$s - Stripe Source ID; %2$s - Stripe Source status
						$message = sprintf( __( 'Failed to process payment source %1$s: unsupported status - "%2$s".', 'motopress-hotel-booking' ), $sourceId, $status );
						break;
				}

				$payment->addLog( $message );
				$this->paymentFailed( $payment );
			}
		} catch ( \Exception $e ) {
			$paymentStatus = 'failed';

			// Leave payment status transition to the admin
			$payment->addLog( sprintf( __( 'Failed to process Source payment. %s', 'motopress-hotel-booking' ), $e->getMessage() ) );
		}

		switch ( $paymentStatus ) {
			case 'success':
				wp_redirect( MPHB()->settings()->pages()->getReservationReceivedPageUrl( $payment ) );
				break;
			case 'failed':
				wp_redirect( MPHB()->settings()->pages()->getPaymentFailedPageUrl( $payment ) );
				break;
			case 'redirect':
				wp_redirect( $redirectUrl );
				break; // Customer must confirm the source
		}

		exit;
	}

	/**
	 * @param \MPHB\Entities\Payment $payment
	 * @param \Stripe\Source         $source Source with status "chargeable".
	 *
	 * @see MPHB\Payments\Gateways\StripeGateway::processPayment()
	 * @see MPHB\Payments\Gateways\Stripe\WebhookListener::process()
	 * @see MPHB\ActionsHandler::chargeStripeSource()
	 */
	public function chargePayment( \MPHB\Entities\Payment $payment, \Stripe\Source $source ) {

		if ( ! in_array( $payment->getStatus(), array( PaymentStatuses::STATUS_PENDING, PaymentStatuses::STATUS_ON_HOLD ) ) ) {
			$message = __( "Can't charge the payment again: payment's flow already completed.", 'motopress-hotel-booking' );
			$payment->addLog( $message );

			return false;
		}

		try {
			// Generate description
			$booking     = MPHB()->getBookingRepository()->findById( $payment->getBookingId() );
			$description = ! is_null( $booking ) ? $this->generateItemName( $booking ) : '';

			// Create Charge object
			$charge = $this->api->chargeSource( $source->id, $payment->getAmount(), $description, $payment->getCurrency() );

			$payment->setTransactionId( $charge->id );

			// If paymentXXX() will not trigger any changes, then we must save
			// transaction ID manually
			update_post_meta( $payment->getId(), '_mphb_transaction_id', $charge->id );

			if ( $charge->status == 'succeeded' ) {
				// translators: %s - Stripe Charge ID
				$payment->addLog( sprintf( __( 'Charge %s succeeded.', 'motopress-hotel-booking' ), $charge->id ) );
				$this->paymentCompleted( $payment );

			} elseif ( $charge->status == 'pending' ) {
				$chargedPrice = mphb_format_price( $payment->getAmount(), array( 'currency_symbol' => MPHB()->settings()->currency()->getBundle()->getSymbol( $payment->getCurrency() ) ) );

				// translators: %1$s - Stripe Charge ID; %2$s - payment price
				$payment->addLog( sprintf( __( 'Charge %1$s for %2$s created.', 'motopress-hotel-booking' ), $charge->id, $chargedPrice ) );
				$this->paymentOnHold( $payment );

			} else { // failed
				// translators: %s - Stripe Charge ID
				$payment->addLog( sprintf( __( 'Charge %s failed.', 'motopress-hotel-booking' ), $charge->id ) );
				$this->paymentFailed( $payment );
			}

			return $charge->status != 'failed';

		} catch ( \Exception $e ) {
			$payment->addLog( sprintf( __( 'Charge error. %s', 'motopress-hotel-booking' ), $e->getMessage() ) );

			// Wait for webhooks
			$this->paymentOnHold( $payment );

			return false;
		}
	}

	public function getAvailableLocales() {
		// Available locales: https://stripe.com/docs/stripe-js/reference#locale
		return array(
			'auto' => __( 'Auto', 'motopress-hotel-booking' ),
			'ar'   => __( 'Argentinean', 'motopress-hotel-booking' ),
			'zh'   => __( 'Simplified Chinese', 'motopress-hotel-booking' ),
			'da'   => __( 'Danish', 'motopress-hotel-booking' ),
			'nl'   => __( 'Dutch', 'motopress-hotel-booking' ),
			'en'   => __( 'English', 'motopress-hotel-booking' ),
			'fi'   => __( 'Finnish', 'motopress-hotel-booking' ),
			'fr'   => __( 'French', 'motopress-hotel-booking' ),
			'de'   => __( 'German', 'motopress-hotel-booking' ),
			'it'   => __( 'Italian', 'motopress-hotel-booking' ),
			'ja'   => __( 'Japanese', 'motopress-hotel-booking' ),
			'no'   => __( 'Norwegian', 'motopress-hotel-booking' ),
			'pl'   => __( 'Polish', 'motopress-hotel-booking' ),
			'ru'   => __( 'Russian', 'motopress-hotel-booking' ),
			'es'   => __( 'Spanish', 'motopress-hotel-booking' ),
			'sv'   => __( 'Swedish', 'motopress-hotel-booking' ),
			// 'he' => what is "he"?
		);
	}

	/**
	 * @param \MPHB\Entities\Booking $booking
	 */
	public function getCheckoutData( $booking ) {
		$redirectUrl = add_query_arg(
			array(
				'mphb_action' => 'handle_stripe_errors',
				'mphb_nonce'  => wp_create_nonce( 'handle_stripe_errors' ),
			),
			MPHB()->settings()->pages()->getReservationReceivedPageUrl( null, array( 'mphb_payment_status' => 'auto' ) )
		);

		// Put some basic customer info required for the StripeGateway.js
		// (important only for Payment Request and its checkout page)
		$customer = $booking->getCustomer();

		if ( ! is_null( $customer ) ) {
			$customerData = array(
				'email'      => $customer->getEmail(),
				'name'       => $customer->getName(),
				'first_name' => $customer->getFirstName(),
				'last_name'  => $customer->getLastName(),
			);
		} else {
			$customerData = array();
		}

		$data = array(
			'publicKey'               => $this->publicKey,
			'locale'                  => $this->locale,
			'currency'                => MPHB()->settings()->currency()->getCurrencyCode(),
			'successUrl'              => $redirectUrl,
			'defaultCountry'          => MPHB()->settings()->main()->getDefaultCountry(),
			'statementDescriptor'     => substr( MPHB()->getName(), 0, 22 ), // 22 is max for some methods
			'paymentMethods'          => $this->allowedMethods,
			'idempotencyKeyFieldName' => CheckoutShortcode::BOOKING_CID_NAME,
			'amount'                  => $booking->calcDepositAmount(),
			'customer'                => $customerData,
			// Docs: https://stripe.com/docs/stripe-js/reference#element-options
			// Example: https://github.com/stripe/stripe-payments-demo/blob/master/public/javascripts/payments.js#L38
			'style'                   => apply_filters( 'mphb_stripe_elements_style', array( 'base' => array( 'fontSize' => '15px' ) ) ),
			'i18n'                    => array(
				// Payment methods (labels)
				'card'             => __( 'Card', 'motopress-hotel-booking' ),
				'bancontact'       => __( 'Bancontact', 'motopress-hotel-booking' ),
				'ideal'            => __( 'iDEAL', 'motopress-hotel-booking' ),
				'giropay'          => __( 'Giropay', 'motopress-hotel-booking' ),
				'sepa_debit'       => __( 'SEPA Direct Debit', 'motopress-hotel-booking' ),
				'sofort'           => __( 'SOFORT', 'motopress-hotel-booking' ),
				// Additional labels
				'card_description' => __( 'Credit or debit card', 'motopress-hotel-booking' ),
				'iban'             => __( 'IBAN', 'motopress-hotel-booking' ),
				'ideal_bank'       => __( 'Select iDEAL Bank', 'motopress-hotel-booking' ),
				// Messages
				'redirect_notice'  => __( 'You will be redirected to a secure page to complete the payment.', 'motopress-hotel-booking' ),
				'iban_policy'      => __( 'By providing your IBAN and confirming this payment, you are authorizing this merchant and Stripe, our payment service provider, to send instructions to your bank to debit your account and your bank to debit your account in accordance with those instructions. You are entitled to a refund from your bank under the terms and conditions of your agreement with your bank. A refund must be claimed within 8 weeks starting from the date on which your account was debited.', 'motopress-hotel-booking' ), // From https://stripe.com/docs/sources/sepa-debit#prerequisite
			),
		);

		return array_merge( parent::getCheckoutData( $booking ), $data );
	}

	/**
	 * @return \MPHB\Payments\Gateways\Stripe\StripeAPI6
	 */
	public function getApi() {
		return $this->api;
	}
}
