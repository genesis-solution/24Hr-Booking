<?php

namespace MPHB\Payments\Gateways\Paypal;

use \MPHB\Payments\Gateways;

class IpnListener extends Gateways\AbstractNotificationListener {

	const SANDBOX_URL = 'https://www.sandbox.paypal.com/cgi-bin/webscr';
	const LIVE_URL    = 'https://www.paypal.com/cgi-bin/webscr';

	/**
	 *
	 * @var string
	 */
	private $businessEmail;

	/**
	 *
	 * @var bool
	 */
	private $verificationDisabled = false;

	public function __construct( $atts = array() ) {
		parent::__construct( $atts );
		$this->businessEmail        = $atts['businessEmail'];
		$this->verificationDisabled = $atts['verificationDisabled'];
	}

	protected function initUrlIdentificationValue() {
		return 'paypal-ipn';
	}

	/**
	 *
	 * @param array $input
	 * @return boolean
	 */
	protected function validate( $input ) {
		return $this->verificationDisabled || $this->verifyRequest( $input );
	}

	/**
	 *
	 * @return \MPHB\Entities\Payment|null
	 */
	protected function retrievePayment() {

		$payment = null;

		if ( ! empty( $this->input['parent_txn_id'] ) ) {
			$paymentAtts = array(
				'transaction_id' => $this->input['parent_txn_id'],
				'gateway'        => 'paypal',
			);

			$payment = MPHB()->getPaymentRepository()->findAll( $paymentAtts );
			$payment = ! empty( $payment ) ? reset( $payment ) : null;
		} elseif ( ! empty( $this->input['txn_id'] ) ) {
			$paymentAtts = array(
				'transaction_id' => $this->input['txn_id'],
				'gateway'        => 'paypal',
			);

			$payment = MPHB()->getPaymentRepository()->findAll( $paymentAtts );
			$payment = ! empty( $payment ) ? reset( $payment ) : null;
		}

		if ( empty( $payment ) ) {
			$payment = MPHB()->getPaymentRepository()->findById( absint( $this->input['custom'] ) );
		}

		return $payment;
	}

	/**
	 *
	 * @param array $input Requested Data
	 * @return boolean
	 */
	private function verifyRequest( $input ) {
		$validateIpn  = array( 'cmd' => '_notify-validate' );
		$validateIpn += $input;

		$params = array(
			'body'        => $validateIpn,
			'timeout'     => 60,
			'httpversion' => '1.1',
			'compress'    => false,
			'decompress'  => false,
			'user-agent'  => 'MPHB/' . MPHB()->getVersion(),
		);

		$response = wp_safe_remote_post( $this->isSandbox ? self::SANDBOX_URL : self::LIVE_URL, $params );

		if ( is_wp_error( $response ) ) {
			return false;
		}

		if ( ! ( $response['response']['code'] >= 200 && $response['response']['code'] < 300 ) ) {
			return false;
		}

		return strstr( $response['body'], 'VERIFIED' );
	}

	protected function process() {

		$txnType = $this->input['txn_type'];

		// Sandbox fix.
		if ( isset( $this->input['test_ipn'] ) && 1 == $this->input['test_ipn'] && 'Pending' == $this->input['payment_status'] ) {
			$this->input['payment_status'] = 'Completed';
		}

		if ( has_action( "mphb_paypal_{$txnType}" ) ) {
			// Allow PayPal IPN types to be processed separately
			do_action( "mphb_paypal_{$txnType}", $this->input, $this->payment->getId() );
		} else {
			// Fallback to web accept just in case the txn_type isn't present
			$this->processWebAccept();
		}
	}

	/**
	 * Process Web Accept
	 *
	 * @return boolean
	 */
	private function processWebAccept() {

		if (
			$this->input['txn_type'] !== 'web_accept' &&
			$this->input['payment_status'] !== 'Refunded'
		) {
			return false;
		}

		if ( ! isset( $this->input['invoice'], $this->input['mc_gross'], $this->input['payment_status'], $this->input['business'] ) ) {
			return;
		}

		if ( ! $this->checkBusinessEmail() || ! $this->checkCurrencyCode() ) {
			return;
		}

		$paymentStatus = strtolower( $this->input['payment_status'] );
		switch ( $paymentStatus ) {
			case 'refunded':
			case 'reversed':
				$this->paymentRefunded();
				break;
			case 'completed':
				$this->paymentCompleted();
				break;
			case 'pending':
				$onHoldLog = $this->retrievePendingReasonNote( $this->input );
				$this->paymentOnHold( $onHoldLog );
				break;
			case 'failed':
			case 'denied':
			case 'expired':
			case 'voided':
				$failedLog = sprintf( __( 'Payment %s via IPN.', 'motopress-hotel-booking' ), mphb_clean( $this->input['payment_status'] ) );
				$this->paymentFailed( $failedLog );
				break;
			// case 'canceled_reversal':
			// break;
			// case 'processed':
			// break;
		}
	}

	/**
	 * Verify payment recipient
	 *
	 * @return boolean
	 */
	private function checkBusinessEmail() {
		$businessEmail = isset( $this->input['business'] ) && is_email( $this->input['business'] ) ? trim( $this->input['business'] ) : trim( $this->input['receiver_email'] );

		if ( strcasecmp( $businessEmail, $this->businessEmail ) != 0 ) {
			$log = __( 'Payment failed due to invalid PayPal business email.', 'motopress-hotel-booking' );
			$this->paymentFailed( $log );
			return false;
		}

		return true;
	}

	/**
	 * Verify payment currency
	 *
	 * @return boolean
	 */
	private function checkCurrencyCode() {
		$currencyCode = strtolower( $this->input['mc_currency'] );

		if ( $currencyCode !== strtolower( $this->payment->getCurrency() ) ) {
			$log = __( 'Payment failed due to invalid currency in PayPal IPN.', 'motopress-hotel-booking' );
			$this->paymentFailed( $log );
			return false;
		}

		return true;
	}

	/**
	 *
	 * @return boolean
	 */
	private function checkPaymentAmount() {

		if ( number_format( (float) $this->input['mc_gross'], 2 ) < number_format( (float) $this->payment->getAmount(), 2 ) ) {
			$log = __( 'Payment failed due to invalid amount in PayPal IPN.', 'motopress-hotel-booking' );
			$this->paymentFailed( $log );
			return false;
		}

		return true;
	}

	/**
	 *
	 * @return boolean
	 */
	private function checkPaymentKey() {

		if ( $this->input['invoice'] !== $this->payment->getKey() ) {
			$log = __( 'Payment failed due to invalid purchase key in PayPal IPN.', 'motopress-hotel-booking' );
			$this->paymentFailed( $log );
			return false;
		}

		return true;
	}

	/**
	 *
	 * @param string $completeLog Optional.
	 * @return bool
	 */
	public function paymentCompleted( $completeLog = '' ) {

		if ( ! MPHB()->paymentManager()->canBeCompleted( $this->payment ) ) {
			return false;
		}

		if ( ! $this->checkPaymentAmount() || ! $this->checkPaymentKey() ) {
			return false;
		}

		if ( $completeLog ) {
			$this->payment->addLog( $completeLog );
		}

		$this->updatePaymentMeta();

		$this->payment->setTransactionId( mphb_clean( $this->input['txn_id'] ) );

		$completed = MPHB()->paymentManager()->completePayment( $this->payment, '', true );

		if ( ! empty( $this->input['mc_fee'] ) ) {
			update_post_meta( $this->payment->getId(), '_mphb_fee', mphb_clean( $this->input['mc_fee'] ) );
		}

		return $completed;
	}

	/**
	 *
	 * @param string $onHoldLog
	 * @return bool
	 */
	public function paymentOnHold( $onHoldLog = '' ) {

		if ( ! MPHB()->paymentManager()->canBeOnHold( $this->payment ) ) {
			return false;
		}

		if ( ! $this->checkPaymentAmount() || ! $this->checkPaymentKey() ) {
			return false;
		}

		if ( $onHoldLog ) {
			$this->payment->addLog( $onHoldLog );
		}

		return MPHB()->paymentManager()->holdPayment( $this->payment, '', true );
	}

	/**
	 *
	 * @return string
	 */
	private function retrievePendingReasonNote() {
		$note = '';

		switch ( strtolower( $this->input['pending_reason'] ) ) {

			case 'echeck':
				$note = __( 'Payment made via eCheck and will clear automatically in 5-8 days.', 'motopress-hotel-booking' );

				break;

			case 'address':
				$note = __( 'Payment requires a confirmed customer address and must be accepted manually through PayPal.', 'motopress-hotel-booking' );

				break;

			case 'intl':
				$note = __( 'Payment must be accepted manually through PayPal due to international account regulations.', 'motopress-hotel-booking' );

				break;

			case 'multi-currency':
				$note = __( 'Payment received in non-shop currency and must be accepted manually through PayPal.', 'motopress-hotel-booking' );

				break;

			case 'paymentreview':
			case 'regulatory_review':
				$note = __( 'Payment is being reviewed by PayPal staff as high-risk or in possible violation of government regulations.', 'motopress-hotel-booking' );

				break;

			case 'unilateral':
				$note = __( 'Payment was sent to unconfirmed or non-registered email address.', 'motopress-hotel-booking' );

				break;

			case 'upgrade':
				$note = __( 'PayPal account must be upgraded before this payment can be accepted.', 'motopress-hotel-booking' );

				break;

			case 'verify':
				$note = __( 'PayPal account is not verified. Verify account in order to accept this payment.', 'motopress-hotel-booking' );

				break;

			case 'other':
				$note = __( 'Payment is pending for unknown reasons. Contact PayPal support for assistance.', 'motopress-hotel-booking' );

				break;
		}
		return $note;
	}

	/**
	 *
	 * @param string $refundLog Optional.
	 */
	public function paymentRefunded( $refundLog = '' ) {

		if ( ! MPHB()->paymentManager()->canBeRefunded( $this->payment ) ) {
			return false;
		}

		$refundAmount = $this->input['mc_gross'] * -1;

		if ( number_format( $refundAmount, 2 ) < number_format( (float) $this->payment->getAmount(), 2 ) ) {
			$log = sprintf( __( 'Partial PayPal refund processed: %s', 'motopress-hotel-booking' ), empty( $this->input['parent_txn_id'] ) ? $this->input['parent_txn_id'] : '' );
			$this->payment->addLog( $log );

			return false;
		}

		if ( ! empty( $this->input['parent_txn_id'] ) && ! empty( $this->input['reason_code'] ) ) {
			$reasonLog = sprintf( __( 'PayPal Payment #%s Refunded for reason: %s', 'motopress-hotel-booking' ), $this->input['parent_txn_id'], $this->input['reason_code'] );
			$this->payment->addLog( $reasonLog );
		}

		$transactionLog = sprintf( __( 'PayPal Refund Transaction ID: %s', 'motopress-hotel-booking' ), $this->input['txn_id'] );
		$this->payment->addLog( $transactionLog );

		if ( $refundLog ) {
			$this->payment->addLog( $refundLog );
		}

		return MPHB()->paymentManager()->refundPayment( $this->payment, '', true );
	}

	/**
	 *
	 * @param string $failLog Optional.
	 * @return bool
	 */
	public function paymentFailed( $failLog = '' ) {

		if ( ! MPHB()->paymentManager()->canBeFailed( $this->payment ) ) {
			return false;
		}

		if ( $failLog ) {
			$this->payment->addLog( $failLog );
		}

		return MPHB()->paymentManager()->failPayment( $this->payment, '', true );
	}

	private function updatePaymentMeta() {

		$billingEmail     = mphb_clean( isset( $this->input['payer_email'] ) ? $this->input['payer_email'] : '' );
		$billingFirstName = mphb_clean( isset( $this->input['first_name'] ) ? $this->input['first_name'] : '' );
		$billingLastName  = mphb_clean( isset( $this->input['last_name'] ) ? $this->input['last_name'] : '' );
		$paymentType      = mphb_clean( isset( $this->input['payment_type'] ) ? $this->input['payment_type'] : '' );

		if ( ! empty( $billingEmail ) ) {
			update_post_meta( $this->payment->getId(), '_mphb_email', $billingEmail );
		}
		if ( ! empty( $billingFirstName ) ) {
			update_post_meta( $this->payment->getId(), '_mphb_first_name', $billingFirstName );
		}
		if ( ! empty( $billingLastName ) ) {
			update_post_meta( $this->payment->getId(), '_mphb_last_name', $billingLastName );
		}

		if ( ! empty( $paymentType ) ) {
			update_post_meta( $this->payment->getId(), '_mphb_payment_type', $paymentType );
		}

		// Re-get payment.
		$this->payment = MPHB()->getPaymentRepository()->findById( $this->payment->getId(), true );
	}

}
