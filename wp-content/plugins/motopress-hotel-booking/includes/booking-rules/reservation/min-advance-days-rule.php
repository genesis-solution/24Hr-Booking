<?php

namespace MPHB\BookingRules\Reservation;

use MPHB\BookingRules\AbstractRule;
use MPHB\Utils\DateUtils;

class MinAdvanceDaysRule extends AbstractRule {

	/**
	 * @var int
	 */
	private $minAdvanceDays;

	public function __construct( $atts ) {
		parent::__construct( $atts );
		$this->minAdvanceDays = is_admin() && ! wp_doing_ajax() ? 0 : $atts['min_advance_reservation'];
	}

	/**
	 * @param \DateTime $checkInDate
	 * @param \DateTime $checkOutDate
	 * @param int       $roomTypeId
	 *
	 * @return mixed
	 */
	public function verify( \DateTime $checkInDate, \DateTime $checkOutDate = null, $roomTypeId = 0 ) {

		if ( is_admin() && ! wp_doing_ajax() ) { // Don't apply the rule if it's a booking from admin
			return true;
		}

		$nightsSinceToday = DateUtils::calcNightsSinceToday( $checkInDate );

		return $nightsSinceToday >= $this->minAdvanceDays;
	}

	/**
	 * @return array
	 */
	public function toArray() {
		return array_merge(
			parent::toArray(),
			array(
				'min_advance_reservation' => $this->minAdvanceDays,
			)
		);
	}

	/**
	 * @return int
	 */
	public function getMinAdvanceDays() {
		return $this->minAdvanceDays;
	}

}


