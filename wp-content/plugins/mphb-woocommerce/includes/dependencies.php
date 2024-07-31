<?php

namespace MPHBW;

class Dependencies {

	const WOO_VERSION	 = '3.2.6';
	const MPHB_VERSION = '2.4.0';

	public function __construct(){
		add_action( 'admin_notices', array( $this, 'checkAndShowNotice' ) );
	}

	/**
	 * Check plugin dependencies. Don't use before plugins_loaded action
	 *
	 * @return boolean
	 */
	public function check(){
		return $this->checkMPHB() && $this->checkWoocommerce();
	}

	private function isMPHBActive(){
		return mphbw_is_plugin_active( 'motopress-hotel-booking/motopress-hotel-booking.php' ) ||
			mphbw_is_plugin_active( 'motopress-hotel-booking-lite/motopress-hotel-booking.php' );
	}

	private function isMPHBCorrectVersion(){
		if ( !function_exists( 'MPHB' ) ) {
			return false;
		}
		$mphb = MPHB();

		return method_exists( $mphb, 'getVersion' ) &&
			version_compare( $mphb->getVersion(), self::MPHB_VERSION, '>=' );
	}

	private function checkMPHB(){
		return $this->isMPHBActive() && $this->isMPHBCorrectVersion();
	}

	private function isWoocommerceActive(){
		return mphbw_is_plugin_active( 'woocommerce/woocommerce.php' );
	}

	private function isWoocommerceCorrectVersion(){
		if ( !function_exists( 'wc' ) ) {
			return false;
		}

		$woocommerce = wc();
		return $woocommerce && property_exists( $woocommerce, 'version' ) &&
			version_compare( $woocommerce->version, self::WOO_VERSION, '>=' );
	}

	private function checkWoocommerce(){
		return $this->isWoocommerceActive() && $this->isWoocommerceCorrectVersion();
	}

	/**
	 *
	 * @return bool
	 */
	public function checkCurrency(){
		$mphbCurrency	 = MPHB()->settings()->currency()->getCurrencyCode();
		$wooCurrency	 = get_woocommerce_currency();
		return $mphbCurrency == $wooCurrency;
	}

	function checkAndShowNotice(){
		if ( !$this->checkMPHB() ) {
			echo '<div class="error"><p>' . sprintf( __( 'Hotel Booking WooCommerce Payments plugin requires Hotel Booking plugin version %s or higher.', 'mphb-woocommerce' ), self::MPHB_VERSION ) . '</p></div>';
		}

		if ( !$this->checkWoocommerce() ) {
			echo '<div class="error"><p>' . sprintf( __( 'Hotel Booking WooCommerce Payments requires WooCommerce plugin version %s or higher.', 'mphb-woocommerce' ), self::WOO_VERSION ) . '</p></div>';
		}

		if ( $this->checkMPHB() && $this->checkWoocommerce() && !$this->checkCurrency() ) {
			echo '<div class="error"><p>' . sprintf( __( 'Hotel Booking WooCommerce Payments requires equal currencies set up in WooCommerce(%s) and Hotel Booking(%s).', 'mphb-woocommerce' ), get_woocommerce_currency(), MPHB()->settings()->currency()->getCurrencyCode() ) . '</p></div>';
		}
	}

}
