<?php

namespace MPHB\Payments\Gateways;

use \MPHB\Admin\Groups;
use \MPHB\Admin\Fields;
use \Braintree\ClientToken;
use \Braintree\Configuration;
use \Braintree\Transaction;

class BraintreeGateway extends Gateway {

	/**
	 *
	 * @var Braintree\WebhookListener
	 */
	protected $webhookListener;

	/**
	 *
	 * @var array
	 */
	private $supportedCurrencies = array();

	/**
	 *
	 * @var string
	 */
	private $merchantId;

	/**
	 *
	 * @var string
	 */
	private $publicKey;

	/**
	 *
	 * @var string
	 */
	private $privateKey;

	/**
	 *
	 * @var string
	 */
	private $accountId;

	/**
	 *
	 * @var string
	 */
	private $paymentMethodNonce;

	/**
	 *
	 * @var string
	 */
	private $apiIssues = '';

	public function __construct() {
		add_filter( 'mphb_gateway_has_instructions', array( $this, 'hideInstructions' ), 10, 2 );

		parent::__construct();

		$this->setupNotificationListener();
		$this->adminDescription = $this->generateAdminDescription();
		$this->setupSuppportedCurrencies();

		if ( $this->isActive() ) {
			$this->setupAPI();

			add_action( 'wp_enqueue_scripts', array( $this, 'enqueueScripts' ) );
		} else {
			$this->checkAPI();
		}
	}

	protected function initId() {
		return 'braintree';
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

	protected function setupNotificationListener() {
		$webhookListnerArgs    = array(
			'gatewayId' => $this->getId(),
			'sandbox'   => $this->isSandbox,
		);
		$this->webhookListener = new Braintree\WebhookListener( $webhookListnerArgs );
	}

	private function setupAPI() {
		try {
			$this->loadBraintreeApi();
			Configuration::environment( ( $this->isSandbox ? 'sandbox' : 'production' ) );
			Configuration::merchantId( $this->merchantId );
			Configuration::publicKey( $this->publicKey );
			Configuration::privateKey( $this->privateKey );
			// Configuration::sslVersion( 6 );
		} catch ( \Exception $e ) {
			$this->enabled   = false;
			$this->apiIssues = $e->getMessage();
		}
	}

	private function checkAPI() {
		try {
			// Is there some errors with PHP version or installed PHP extensions?
			$this->loadBraintreeApi();
		} catch ( \Exception $e ) {
			$this->apiIssues = $e->getMessage();
		}
	}

	private function loadBraintreeApi() {
		if ( ! function_exists( 'requireDependencies' ) ) {
			MPHB()->requireOnce( 'vendors/braintree-sdk/lib/Braintree.php' );
		}
	}

	protected function setupProperties() {
		parent::setupProperties();

		$this->adminTitle = __( 'Braintree', 'motopress-hotel-booking' );
		$this->merchantId = $this->getOption( 'merchant_id' );
		$this->publicKey  = $this->getOption( 'public_key' );
		$this->privateKey = $this->getOption( 'private_key' );
		$this->accountId  = $this->getOption( 'account_id' );

		if ( $this->isSandbox ) {
			$this->description .= ' ' . sprintf( __( 'Use the card number %1$s with CVC %2$s and a valid expiration date to test a payment.', 'motopress-hotel-booking' ), '4111111111111111', '123' );
			$this->description  = trim( $this->description );
		}
	}

	private function generateAdminDescription() {
		$description = sprintf( __( 'Webhooks Destination URL: %s', 'motopress-hotel-booking' ), '<code>' . esc_url( $this->webhookListener->getNotifyUrl() ) . '</code>' );

		return $description;
	}

	public function enqueueScripts() {
		if ( mphb_is_checkout_page() ) {
			wp_enqueue_script( 'mphb-vendor-braintree-client-sdk' );
		}
	}

	protected function initDefaultOptions() {
		$defaults = array(
			'title'       => __( 'Pay by Card (Braintree)', 'motopress-hotel-booking' ),
			'description' => __( 'Pay with your credit card via Braintree.', 'motopress-hotel-booking' ),
			'enabled'     => false,
			'is_sandbox'  => false,
			'merchant_id' => '',
			'public_key'  => '',
			'private_key' => '',
			'account_id'  => '',
		);
		return array_merge( parent::initDefaultOptions(), $defaults );
	}

	public function registerOptionsFields( &$subTab ) {
		parent::registerOptionsFields( $subTab );

		$enableField = $subTab->findField( "mphb_payment_gateway_{$this->id}_enable" );

		if ( ! is_null( $enableField ) ) {
			if ( ! empty( $this->apiIssues ) ) {
				// Disable checkbox "Enable Pay by Card (Braintree)" if there are some issues with SDK
				$enableField->setDisabled( true );
				$enableField->setDescription( sprintf( __( 'Braintree gateway cannot be enabled due to some problems: %s', 'motopress-hotel-booking' ), $this->apiIssues ) );

			} elseif ( ! MPHB()->isSiteSSL() && ( ! MPHB()->settings()->payment()->isForceCheckoutSSL() && ! class_exists( 'WordPressHTTPS' ) ) ) {
				// Show warning if the SSL not enabled
				if ( $this->isActive() ) {
					$message = __( '%1$s is enabled, but the <a href="%2$s">Force Secure Checkout</a> option is disabled. Please enable SSL and ensure your server has a valid SSL certificate. Otherwise, %1$s will only work in Test Mode.', 'motopress-hotel-booking' );
				} else {
					$message = __( 'The <a href="%2$s">Force Secure Checkout</a> option is disabled. Please enable SSL and ensure your server has a valid SSL certificate. Otherwise, %1$s will only work in Test Mode.', 'motopress-hotel-booking' );
				}

				$message = sprintf( $message, __( 'Braintree', 'motopress-hotel-booking' ), esc_url( MPHB()->getSettingsMenuPage()->getUrl( array( 'tab' => 'payments' ) ) ) );

				$enableField->setDescription( $message );
			}
		}

		$group = new Groups\SettingsGroup( "mphb_payments_{$this->id}_group1", '', $subTab->getOptionGroupName() );

		$groupFields = array(
			Fields\FieldFactory::create(
				"mphb_payment_gateway_{$this->id}_merchant_id",
				array(
					'type'        => 'text',
					'label'       => __( 'Merchant ID', 'motopress-hotel-booking' ),
					'default'     => $this->getDefaultOption( 'merchant_id' ),
					'description' => __( 'In your Braintree account select Account > My User > View Authorizations.', 'motopress-hotel-booking' ),
				)
			),
			Fields\FieldFactory::create(
				"mphb_payment_gateway_{$this->id}_public_key",
				array(
					'type'    => 'text',
					'label'   => __( 'Public Key', 'motopress-hotel-booking' ),
					'default' => $this->getDefaultOption( 'public_key' ),
				)
			),
			Fields\FieldFactory::create(
				"mphb_payment_gateway_{$this->id}_private_key",
				array(
					'type'    => 'text',
					'label'   => __( 'Private Key', 'motopress-hotel-booking' ),
					'default' => $this->getDefaultOption( 'private_key' ),
				)
			),
			Fields\FieldFactory::create(
				"mphb_payment_gateway_{$this->id}_account_id",
				array(
					'type'        => 'text',
					'label'       => __( 'Merchant Account ID', 'motopress-hotel-booking' ),
					'description' => sprintf( __( 'In case the site currency differs from default currency in your Braintree account, you can set specific merchant account to avoid <a href="%s">complications with currencty conversions</a>. Otherwise leave the field empty.', 'motopress-hotel-booking' ), esc_url( 'https://articles.braintreepayments.com/get-started/currencies#a-warning-on-currency-conversion' ) ),
					'default'     => $this->getDefaultOption( 'account_id' ),
				)
			),
		);

		$group->addFields( $groupFields );

		$subTab->addGroup( $group );
	}

	/**
	 * Generate the request for the payment.
	 *
	 * @param string                 $nonce
	 * @param string                 $accountId
	 * @param \MPHB\Entities\Payment $payment
	 * @return array
	 */
	protected function generatePaymentRequest( $nonce, $accountId, $payment ) {
		$post_data                       = array();
		$post_data['amount']             = number_format( $payment->getAmount(), 2, '.', '' );
		$post_data['paymentMethodNonce'] = $nonce;
		$post_data['options']            = array( 'submitForSettlement' => true );

		if ( ! empty( $accountId ) ) {
			$post_data['merchantAccountId'] = $accountId;
		}

		return $post_data;
	}

	/**
	 *
	 * @param \MPHB\Entities\Booking $booking
	 * @param \MPHB\Entities\Payment $payment
	 * @return boolean
	 */
	public function processPayment( \MPHB\Entities\Booking $booking, \MPHB\Entities\Payment $payment ) {
		$createSaleRequest = $this->generatePaymentRequest( $this->paymentMethodNonce, $this->accountId, $payment );
		$result            = Transaction::sale( $createSaleRequest );

		if ( $result->success ) {
			$paymentType = $result->transaction->paymentInstrumentType . ' / ' . $result->transaction->creditCardDetails->cardType;
			$fee         = floatval( $result->transaction->amount ) - $payment->getAmount();

			update_post_meta( $payment->getId(), '_mphb_payment_type', $paymentType );
			update_post_meta( $payment->getId(), '_mphb_fee', number_format( $fee, 2, '.', '' ) );

			// Re-get payment to prevent overriding directly updated meta
			$payment = MPHB()->getPaymentRepository()->findById( $payment->getId(), true );

			$transactionId = $result->transaction->id;
			$payment->setTransactionId( $transactionId );

			$message = sprintf( __( 'Braintree submitted for settlement (Transaction ID: %s)', 'motopress-hotel-booking' ), $transactionId );
			$payment->addLog( $message );

			$this->paymentCompleted( $payment );

			wp_redirect( MPHB()->settings()->pages()->getReservationReceivedPageUrl( $payment ) );
			exit;

		} else {
			$braintreeErrors = str_replace( "\n", ' ', $result->message );
			$message         = sprintf( __( 'Braintree Payment Error: %s', 'motopress-hotel-booking' ), $braintreeErrors );

			$payment->addLog( $message );
			$this->paymentFailed( $payment );

			wp_redirect( MPHB()->settings()->pages()->getPaymentFailedPageUrl( $payment ) );
			exit;
		}
	}

	public function initPaymentFields() {
		return array(
			'mphb_braintree_payment_nonce' => array(
				'type'     => 'hidden',
				'required' => true,
			),
		);
	}

	public function parsePaymentFields( $input, &$errors ) {
		$isParsed = parent::parsePaymentFields( $input, $errors );

		if ( $isParsed ) {
			if ( ! empty( $this->postedPaymentFields['mphb_braintree_payment_nonce'] ) ) {
				$this->paymentMethodNonce = $this->postedPaymentFields['mphb_braintree_payment_nonce'];
				unset( $this->postedPaymentFields['mphb_braintree_payment_nonce'] );
			} else {
				$errorMessage                = __( 'Payment method nonce is required.', 'motopress-hotel-booking' );
				$this->paymentFieldsErrors[] = $errorMessage;
				$errors[]                    = $errorMessage;
				$isParsed                    = false;
			}
		}

		return $isParsed;
	}

	/**
	 *
	 * @param \MPHB\Entities\Booking $booking
	 */
	public function getCheckoutData( $booking ) {
		$data = array();
		try {
			$data['clientToken'] = ClientToken::generate();
		} catch ( \Exception $e ) {
			$data['clientToken'] = '';
		}
		return array_merge( parent::getCheckoutData( $booking ), $data );
	}

	/**
	 *
	 * @return bool
	 */
	public function isActive() {
		return parent::isActive() && $this->isSupportCurrency( MPHB()->settings()->currency()->getCurrencyCode() );
	}

	/**
	 *
	 * @param string $currency Currency code.
	 * @return bool
	 */
	public function isSupportCurrency( $currency ) {
		return in_array( $currency, $this->supportedCurrencies );
	}

	public function setupSuppportedCurrencies() {
		$this->supportedCurrencies = include 'braintree/supported-currencies.php';
	}

}
