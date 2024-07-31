<?php

namespace MPHB\BookingRules\Reservation;

use MPHB\BookingRules\AbstractRule;
use MPHB\Utils\DateUtils;

class MaxDaysRule extends AbstractRule {

	/**
	 * @var int
	 */
	private $maxDays;

	public function __construct( $atts ) {
		parent::__construct( $atts );
		$this->maxDays = $atts['max_stay_length'];
	}

	/**
	 * @param \DateTime $checkInDate
	 * @param \DateTime $checkOutDate
	 * @param int       $roomTypeId
	 *
	 * @return mixed
	 */
	public function verify( \DateTime $checkInDate, \DateTime $checkOutDate, $roomTypeId = 0 ) {
		// Max days unlimited
		if ( $this->maxDays == 0 ) {
			return true;
		}

		$nightCount = DateUtils::calcNights( $checkInDate, $checkOutDate );

		return $nightCount <= $this->maxDays;
	}

	/**
	 * @return array
	 */
	public function toArray() {
		return array_merge(
			parent::toArray(),
			array(
				'max_stay_length' => $this->maxDays,
			)
		);
	}

	/**
	 * @return int
	 */
	public function getMaxDays() {
		return $this->maxDays;
	}
}
