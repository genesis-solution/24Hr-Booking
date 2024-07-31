<?php

namespace MPHB\Settings;

class BookingRulesSettings {

	private $defaultCheckInDays    = array( 0, 1, 2, 3, 4, 5, 6 );
	private $defaultCheckOutDays   = array( 0, 1, 2, 3, 4, 5, 6 );
	private $defaultMinStayLength  = 1;
	private $defaultMaxStayLength  = 0;
	private $defaultMinAdvanceDays = 0;
	private $defaultMaxAdvanceDays = 0;

	public function getDefaultCheckInDays() {
		return $this->defaultCheckInDays;
	}

	public function getDefaultCheckOutDays() {
		return $this->defaultCheckOutDays;
	}

	public function getDefaultMinStayLength() {
		return $this->defaultMinStayLength;
	}

	public function getDefaultMaxStayLength() {
		return $this->defaultMaxStayLength;
	}

	public function getDefaultMinAdvanceDays() {
		return $this->defaultMinAdvanceDays;
	}

	public function getDefaultMaxAdvanceDays() {
		return $this->defaultMaxAdvanceDays;
	}

	public function getReservationRules() {
		return array(
			'check_in_days'           => get_option( 'mphb_check_in_days', array() ),
			'check_out_days'          => get_option( 'mphb_check_out_days', array() ),
			'min_stay_length'         => get_option( 'mphb_min_stay_length', array() ),
			'max_stay_length'         => get_option( 'mphb_max_stay_length', array() ),
			'min_advance_reservation' => get_option( 'mphb_min_advance_reservation', array() ),
			'max_advance_reservation' => get_option( 'mphb_max_advance_reservation', array() ),
		);
	}

	/**
	 *
	 * @return array
	 */
	public function getDefaultReservationRule() {
		return array(
			'check_in_days'           => $this->getDefaultCheckInDays(),
			'check_out_days'          => $this->getDefaultCheckOutDays(),
			'min_stay_length'         => $this->getDefaultMinStayLength(),
			'max_stay_length'         => $this->getDefaultMaxStayLength(),
			'min_advance_reservation' => $this->getDefaultMinAdvanceDays(),
			'max_advance_reservation' => $this->getDefaultMaxAdvanceDays(),
		);
	}

	/**
	 *
	 * @return array
	 */
	public function getCustomRules() {
		return get_option( 'mphb_booking_rules_custom', array() );
	}

	/**
	 * @return array
	 *
	 * @since 3.9
	 */
	public function getBufferRules() {
		return get_option( 'mphb_buffer_days', array() );
	}

}
