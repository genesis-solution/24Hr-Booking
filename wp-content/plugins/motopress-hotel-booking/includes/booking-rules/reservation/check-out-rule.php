<?php

namespace MPHB\BookingRules\Reservation;

use MPHB\BookingRules\AbstractRule;

class CheckOutRule extends AbstractRule {

	/**
	 * @var int[]
	 */
	private $checkOutDays;

	public function __construct( $atts ) {
		parent::__construct( $atts );
		$this->checkOutDays = $atts['check_out_days'];
	}

	/**
	 * @param \DateTime $checkInDate
	 * @param \DateTime $checkOutDate
	 * @param int       $roomTypeId
	 *
	 * @return boolean
	 */
	public function verify( \DateTime $checkInDate, \DateTime $checkOutDate, $roomTypeId = 0 ) {
		$checkOutDay = (int) $checkOutDate->format( 'w' );

		return in_array( $checkOutDay, $this->checkOutDays );
	}

	/**
	 * @return array
	 */
	public function toArray() {
		return array_merge(
			parent::toArray(),
			array(
				'check_out_days' => $this->checkOutDays,
			)
		);
	}

	/**
	 * @return int[]
	 */
	public function getDays() {
		return $this->checkOutDays;
	}

}
