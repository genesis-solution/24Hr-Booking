<?php

class MPHB_Invoice_Dependencies {

	const MPHB_VERSION = '3.8.6';

	public function __construct() {

		add_action( 'admin_notices', array( $this, 'checkAndShowNotice' ) );
	}

	/**
	 * Check plugin dependencies. Don't use before plugins_loaded action
	 *
	 * @return boolean
	 */
	public function check() {

		return $this->checkMPHB();
	}

	private function isMPHBCorrectVersion() {

		if ( ! function_exists( 'MPHB' ) ) {
			return false;
		}

		$mphb = MPHB();

		return method_exists( $mphb, 'getVersion' ) &&
			version_compare( $mphb->getVersion(), self::MPHB_VERSION, '>=' );
	}

	private function checkMPHB() {

		return class_exists( 'HotelBookingPlugin' ) && $this->isMPHBCorrectVersion();
	}

	function checkAndShowNotice() {

		if ( ! $this->checkMPHB() ) {
			echo '<div class="error"><p>' . esc_html( sprintf( __( 'Hotel Booking PDF Invoice plugin requires activated Hotel Booking plugin version %s or higher.', 'mphb-invoices' ), static::MPHB_VERSION ) ) . '</p></div>';
		}
	}

}
