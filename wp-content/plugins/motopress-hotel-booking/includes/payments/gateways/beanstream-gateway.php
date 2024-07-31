<?php

namespace MPHB\Payments\Gateways;

use \MPHB\Admin\Groups;
use \MPHB\Admin\Fields;

class BeanstreamGateway extends Gateway {

	/**
	 *
	 * @var string
	 */
	private $merchantId;

	/**
	 *
	 * @var string
	 */
	private $apiKey;

	/**
	 *
	 * @var string
	 */
	private $singleUseToken;

	public function __construct() {
		add_filter( 'mphb_gateway_has_instructions', array( $this, 'hideInstructions' ), 10, 2 );
		parent::__construct();
	}

	protected function initId() {
		return 'beanstream';
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

	protected function setupProperties() {
		parent::setupProperties();

		$this->adminTitle = __( 'Beanstream/Bambora', 'motopress-hotel-booking' );
		$this->merchantId = $this->getOption( 'merchant_id' );
		$this->apiKey     = $this->getOption( 'api_key' );

		if ( $this->isSandbox ) {
			$this->description .= ' ' . sprintf( __( 'Use the card number %1$s with CVC %2$s and a valid expiration date to test a payment.', 'motopress-hotel-booking' ), '4030000010001234', '123' );
			$this->description  = trim( $this->description );
		}
	}

	protected function initDefaultOptions() {
		$defaults = array(
			'title'       => __( 'Pay by Card (Beanstream)', 'motopress-hotel-booking' ),
			'description' => __( 'Pay with your credit card via Beanstream.', 'motopress-hotel-booking' ),
			'enabled'     => false,
			'is_sandbox'  => false,
			'merchant_id' => '',
			'public_key'  => '',
		);
		return array_merge( parent::initDefaultOptions(), $defaults );
	}

	public function registerOptionsFields( &$subTab ) {
		parent::registerOptionsFields( $subTab );

		// Show warning if the SSL not enabled
		if ( ! MPHB()->isSiteSSL() && ( ! MPHB()->settings()->payment()->isForceCheckoutSSL() && ! class_exists( 'WordPressHTTPS' ) ) ) {
			$enableField = $subTab->findField( "mphb_payment_gateway_{$this->id}_enable" );

			if ( ! is_null( $enableField ) ) {
				if ( $this->isActive() ) {
					$message = __( '%1$s is enabled, but the <a href="%2$s">Force Secure Checkout</a> option is disabled. Please enable SSL and ensure your server has a valid SSL certificate. Otherwise, %1$s will only work in Test Mode.', 'motopress-hotel-booking' );
				} else {
					$message = __( 'The <a href="%2$s">Force Secure Checkout</a> option is disabled. Please enable SSL and ensure your server has a valid SSL certificate. Otherwise, %1$s will only work in Test Mode.', 'motopress-hotel-booking' );
				}

				$message = sprintf( $message, __( 'Beanstream', 'motopress-hotel-booking' ), esc_url( MPHB()->getSettingsMenuPage()->getUrl( array( 'tab' => 'payments' ) ) ) );

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
					'description' => __( 'Your Merchant ID can be found in the top-right corner of the screen after logging in to the Beanstream Back Office', 'motopress-hotel-booking' ),
				)
			),
			Fields\FieldFactory::create(
				"mphb_payment_gateway_{$this->id}_api_key",
				array(
					'type'        => 'text',
					'label'       => __( 'Payments Passcode', 'motopress-hotel-booking' ),
					'default'     => $this->getDefaultOption( 'api_key' ),
					'description' => __( 'To generate the passcode, navigate to Administration > Account Settings > Order Settings in the sidebar, then scroll to Payment Gateway > Security/Authentication', 'motopress-hotel-booking' ),
				)
			),
		);

		$group->addFields( $groupFields );

		$subTab->addGroup( $group );
	}

	/**
	 *
	 * @param \MPHB\Entities\Booking $booking
	 * @param \MPHB\Entities\Payment $payment
	 * @return boolean
	 */
	public function processPayment( \MPHB\Entities\Booking $booking, \MPHB\Entities\Payment $payment ) {
		try {
			MPHB()->requireOnce( 'vendors/beanstream-sdk/src/Beanstream/Gateway.php' );

			$customer    = $booking->getCustomer();
			$name        = $customer->getName();
			$paymentData = array(
				'order_number' => 'orderNum' . $payment->getId(),
				'amount'       => $payment->getAmount(),
				'name'         => trim( $name ),
			);

			// See result details here: https://developer.beanstream.com/docs/references/merchant_API/v1-0-2/ (Operations -> Payments -> Make Payment -> Responses -> 200)
			$beanstream  = new \Beanstream\Gateway( $this->merchantId, $this->apiKey, 'www', 'v1' );
			$result      = $beanstream->payments()->makeLegatoTokenPayment( $this->singleUseToken, $paymentData, true );
			$fee         = $result['amount'] - $payment->getAmount();
			$paymentType = $result['payment_method'];

			if ( isset( $result['card'] ) && isset( $result['card']['card_type'] ) ) {
				$paymentType .= ' / ' . $result['card']['card_type'];
			}

			update_post_meta( $payment->getId(), '_mphb_payment_type', $paymentType );
			update_post_meta( $payment->getId(), '_mphb_fee', number_format( $fee, 2, '.', '' ) );

			// Re-get payment to prevent overriding directly updated meta
			$payment = MPHB()->getPaymentRepository()->findById( $payment->getId(), true );
			$payment->setTransactionId( $result['id'] );

			$this->paymentCompleted( $payment );
			wp_redirect( MPHB()->settings()->pages()->getReservationReceivedPageUrl( $payment ) );
			exit;
		} catch ( \Exception $e ) {
			$message = sprintf( __( 'Beanstream Payment Error: %s', 'motopress-hotel-booking' ), $e->getMessage() );

			$payment->addLog( $message );
			$this->paymentFailed( $payment );

			wp_redirect( MPHB()->settings()->pages()->getPaymentFailedPageUrl( $payment ) );
			exit;
		}
	}

	public function initPaymentFields() {
		return array(
			'singleUseToken' => array(
				'type'     => 'hidden',
				'required' => true,
			),
		);
	}

	/**
	 *
	 * @param \MPHB\Entities\Booking $booking
	 */
	public function getCheckoutData( $booking ) {
		$data = array(
			'scriptUrl' => MPHB()->getPluginUrl( 'vendors/beanstream-sdk/js/beanstream_payfields.js' ),
		);
		return array_merge( parent::getCheckoutData( $booking ), $data );
	}

	public function parsePaymentFields( $input, &$errors ) {
		$isParsed = parent::parsePaymentFields( $input, $errors );

		if ( $isParsed ) {
			if ( ! empty( $this->postedPaymentFields['singleUseToken'] ) ) {
				$this->singleUseToken = $this->postedPaymentFields['singleUseToken'];
				unset( $this->postedPaymentFields['singleUseToken'] );
			} else {
				$errorMessage                = __( 'Payment single use token is required.', 'motopress-hotel-booking' );
				$this->paymentFieldsErrors[] = $errorMessage;
				$errors[]                    = $errorMessage;
				$isParsed                    = false;
			}
		}

		return $isParsed;
	}

}
