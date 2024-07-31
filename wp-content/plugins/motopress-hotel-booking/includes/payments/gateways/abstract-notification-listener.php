<?php

namespace MPHB\Payments\Gateways;

abstract class AbstractNotificationListener {

	/**
	 *
	 * @var bool
	 */
	protected $isSandbox = false;

	/**
	 *
	 * @var string
	 */
	protected $urlKey = 'mphb-listener';

	/**
	 *
	 * @var string
	 */
	protected $urlValue = '';

	/**
	 *
	 * @var string
	 */
	protected $gatewayId = '';

	/**
	 *
	 * @var array
	 */
	protected $input;

	/**
	 *
	 * @var \MPHB\Entities\Payment
	 */
	protected $payment;

	/**
	 *
	 * @param array $atts
	 */
	public function __construct( $atts = array() ) {

		$this->gatewayId = $atts['gatewayId'];
		$this->isSandbox = $atts['sandbox'];

		add_action( 'init', array( $this, 'checkRequest' ) );

		$this->urlValue = $this->initUrlIdentificationValue();
	}

	/**
	 * @sinse 3.6.0 all "exit;" was replaced with fireExit().
	 */
	public function checkRequest() {

		if (
			empty( $_GET[ $this->urlKey ] ) ||
			$_GET[ $this->urlKey ] !== $this->urlValue
		) {
			return;
		}

		$input = $this->parseInput();

		if ( empty( $input ) ) {
			$this->fireExit( false );
		}

		if ( ! $this->validate( $input ) ) {
			$this->fireExit( false );
		}

		$this->input = $input;

		$payment = $this->retrievePayment();

		if ( ! $payment || $payment->getGatewayId() !== $this->gatewayId ) {
			$this->fireExit( false );
		}
		$this->payment = $payment;

		$this->process();

		$this->fireExit( true );
	}

	/**
	 * @param bool $succeed
	 *
	 * @since 3.6.0
	 */
	public function fireExit( $succeed ) {
		exit;
	}

	/**
	 *
	 * @return array
	 */
	protected function parseInput() {
		return empty( $_POST ) ? array() : wp_unslash( $_POST );
	}

	/**
	 *
	 * @return string
	 */
	public function getNotifyUrl() {
		$notifyUrl = add_query_arg( $this->urlKey, $this->urlValue, home_url( 'index.php' ) );

		if ( is_ssl() || MPHB()->settings()->payment()->isForceCheckoutSSL() ) {
			$notifyUrl = preg_replace( '|^http://|', 'https://', $notifyUrl );
		}

		return $notifyUrl;
	}

	/**
	 * @return string
	 */
	abstract protected function initUrlIdentificationValue();

	abstract protected function process();

	/**
	 *
	 * @param array $input
	 * @return boolean
	 */
	abstract protected function validate( $input );

	/**
	 *
	 * @return \MPHB\Entities\Payment
	 */
	abstract protected function retrievePayment();

	abstract protected function paymentCompleted( $log );

	abstract protected function paymentFailed( $log );

	abstract protected function paymentRefunded( $log );

	abstract protected function paymentOnHold( $log );
}
