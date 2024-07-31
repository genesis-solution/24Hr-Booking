<?php

namespace MPHB\BookingRules\Reservation;

use MPHB\BookingRules\AbstractRule;

class CheckInRule extends AbstractRule {

	private $checkInDays;

	public function __construct( $atts ) {
		parent::__construct( $atts );
		$this->checkInDays = $atts['check_in_days'];
	}

	/**
	 * @param \DateTime $checkInDate
	 * @param \DateTime $checkOutDate
	 * @param int       $roomTypeId
	 *
	 * @return mixed
	 */
	public function verify( \DateTime $checkInDate, \DateTime $checkOutDate, $roomTypeId = 0 ) {
		$checkInDay = (int) $checkInDate->format( 'w' );

		return in_array( $checkInDay, $this->checkInDays );
	}

	/**
	 * @return array
	 */
	public function toArray() {
		return array_merge(
			parent::toArray(),
			array(
				'check_in_days' => $this->checkInDays,
			)
		);
	}

	/**
	 * @return int[]
	 */
	public function getDays() {
		return $this->checkInDays;
	}

}
