<?php

namespace MPHB\Payments\Gateways;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ManualGateway extends Gateway {

	protected function setupProperties() {
		parent::setupProperties();
		$this->showOptions = false;
		$this->adminTitle  = __( 'Manual Payment', 'motopress-hotel-booking' );
	}

	protected function initDefaultOptions() {
		$defaults = array(
			'title'       => __( 'Manual Payment', 'motopress-hotel-booking' ),
			'description' => '',
			'enabled'     => false,
		);
		return array_merge( parent::initDefaultOptions(), $defaults );
	}

	protected function initId() {
		return 'manual';
	}

	public function processPayment( \MPHB\Entities\Booking $booking, \MPHB\Entities\Payment $payment ) {
		$isComplete = $this->paymentCompleted( $payment );
		return $isComplete;
	}

}
