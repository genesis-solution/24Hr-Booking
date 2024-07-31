<?php

namespace MPHB\Payments\Gateways;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class TestGateway extends Gateway {

	public function __construct() {

		parent::__construct();

		add_filter(
			'mphb_gateway_has_sandbox',
			function( bool $isShow, string $gatewayId ) {
				return $gatewayId === $this->getId() ? false : $isShow;
			},
			10,
			2
		);
	}

	protected function initId() {
		return 'test';
	}

	/**
	 * @return string
	 */
	public function getMode() {
		return static::MODE_LIVE;
	}

	/**
	 * @return bool
	 */
	public function isSandbox() {
		return false;
	}

	protected function setupProperties() {

		parent::setupProperties();
		$this->adminTitle = __( 'Test Payment', 'motopress-hotel-booking' );
	}

	protected function initDefaultOptions() {

		$defaults = array(
			'title'       => __( 'Test Payment', 'motopress-hotel-booking' ),
			'description' => '',
			'enabled'     => false,
		);
		return array_merge( parent::initDefaultOptions(), $defaults );
	}

	public function processPayment( \MPHB\Entities\Booking $booking, \MPHB\Entities\Payment $payment ) {

		$isComplete  = $this->paymentCompleted( $payment );
		$redirectUrl = $isComplete ? MPHB()->settings()->pages()->getReservationReceivedPageUrl( $payment ) : MPHB()->settings()->pages()->getPaymentFailedPageUrl( $payment );
		wp_redirect( $redirectUrl );
		exit;
	}
}
