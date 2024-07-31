<?php

namespace MPHB\Payments\Gateways;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class CashGateway extends Gateway {

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
		return 'cash';
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
		$this->adminTitle = __( 'Pay on Arrival', 'motopress-hotel-booking' );
	}

	protected function initDefaultOptions() {

		return array_merge(
			parent::initDefaultOptions(),
			array(
				'title'       => __( 'Pay on Arrival', 'motopress-hotel-booking' ),
				'description' => __( 'Pay with cash on arrival.', 'motopress-hotel-booking' ),
				'enabled'     => false,
			)
		);
	}


	public function processPayment( \MPHB\Entities\Booking $booking, \MPHB\Entities\Payment $payment ) {

		$isHolded    = $this->paymentOnHold( $payment );
		$redirectUrl = $isHolded ? MPHB()->settings()->pages()->getReservationReceivedPageUrl( $payment ) : MPHB()->settings()->pages()->getPaymentFailedPageUrl( $payment );
		wp_redirect( $redirectUrl );
		exit;
	}
}
