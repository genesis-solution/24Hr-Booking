<?php

namespace MPHB\Settings;

class PaymentSettings {

	private $defaultAmountType       = 'full';
	private $defaultDepositType      = 'percent';
	private $defaultDepositAmount    = 10;
	private $defaultForceCheckoutSSL = false;

	/**
	 * Retrieve type of payment. Possible values: full, deposit
	 *
	 * @return string
	 */
	public function getAmountType() {
		return get_option( 'mphb_payment_amount_type', $this->getDefaultAmountType() );
	}

	/**
	 *
	 * @return string
	 */
	public function getDefaultAmountType() {
		return $this->defaultAmountType;
	}

	/**
	 * Retrieve type of deposit. Possible values: percent, fixed.
	 *
	 * @return string
	 */
	public function getDepositType() {
		return get_option( 'mphb_payment_deposit_type', $this->getDefaultDepositType() );
	}

	/**
	 *
	 * @return string
	 */
	public function getDefaultDepositType() {
		return $this->defaultDepositType;
	}

	/**
	 *
	 * @return float
	 */
	public function getDepositAmount() {
		return (float) get_option( 'mphb_payment_deposit_amount', $this->getDefaultDepositAmount() );
	}

	/**
	 *
	 * @return float
	 */
	public function getDefaultDepositAmount() {
		return $this->defaultDepositAmount;
	}

	/**
	 * @return int|false
	 *
	 * @since 3.8.3
	 */
	public function getDepositTimeFrame() {
		$timeFrame = get_option( 'mphb_payment_deposit_time_frame', '' );

		if ( $timeFrame === '' ) {
			return false;
		} else {
			return absint( $timeFrame );
		}
	}

	/**
	 *
	 * @return string
	 */
	public function getDefaultGateway() {
		return get_option( 'mphb_payment_default_gateway', '' );
	}

	/**
	 *
	 * @return int Minutes
	 */
	public function getPendingTime() {
		return (int) get_option( 'mphb_payment_pending_time', $this->getDefaultPendingTime() );
	}

	/**
	 *
	 * @return int Minutes
	 */
	public function getDefaultPendingTime() {
		return 60; // 1 Hour
	}

	/**
	 *
	 * @return bool
	 */
	public function isForceCheckoutSSL() {
		return (bool) get_option( 'mphb_payment_force_checkout_ssl', $this->getDefaultForceCheckoutSSL() );
	}

	/**
	 *
	 * @return bool
	 */
	public function getDefaultForceCheckoutSSL() {
		return $this->defaultForceCheckoutSSL;
	}

}
