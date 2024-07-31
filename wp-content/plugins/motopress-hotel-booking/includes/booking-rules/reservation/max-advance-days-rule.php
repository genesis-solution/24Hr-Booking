<?php

namespace MPHB\BookingRules\Reservation;

use MPHB\BookingRules\AbstractRule;
use MPHB\Utils\DateUtils;

class MaxAdvanceDaysRule extends AbstractRule {

	/**
	 * @var int
	 */
	private $maxAdvanceDays;

	public function __construct( $atts ) {
		parent::__construct( $atts );
		$this->maxAdvanceDays = is_admin() && ! wp_doing_ajax() ? 0 : $atts['max_advance_reservation'];
	}

	/**
	 * @param \DateTime $checkInDate
	 * @param \DateTime $checkOutDate
	 * @param int       $roomTypeId
	 *
	 * @return mixed
	 */
	public function verify( \DateTime $checkInDate = null, \DateTime $checkOutDate, $roomTypeId = 0 ) {
		// Max advance days unlimited
		if ( $this->maxAdvanceDays == 0 || is_admin() && ! wp_doing_ajax() ) {
			return true;
		}

		$nightsSinceToday = DateUtils::calcNightsSinceToday( $checkInDate );

		return $nightsSinceToday <= $this->maxAdvanceDays;
	}

	/**
	 * @return array
	 */
	public function toArray() {
		return array_merge(
			parent::toArray(),
			array(
				'max_advance_reservation' => $this->maxAdvanceDays,
			)
		);
	}

	/**
	 * @return int
	 */
	public function getMaxAdvanceDays() {
		return $this->maxAdvanceDays;
	}

}


