<?php

namespace MPHB\BookingRules;

use MPHB\BookingRules\Buffer\BufferRulesList;
use MPHB\BookingRules\Custom\CustomRules;
use MPHB\BookingRules\Reservation\ReservationRules;

class RulesChecker implements RuleVerifyInterface {

	/**
	 *
	 * @var ReservationRules
	 */
	protected $reservationRules;

	/**
	 *
	 * @var CustomRules
	 */
	protected $customRules;

	/**
	 * @var BufferRulesList
	 *
	 * @since 3.9
	 */
	protected $bufferRules;

	/**
	 * @param ReservationRules $reservationRules
	 * @param CustomRules      $customRules
	 * @param BufferRulesList  $bufferRules
	 *
	 * @since 3.9 added new argument: $bufferRules.
	 */
	public function __construct( $reservationRules, $customRules, $bufferRules ) {
		$this->reservationRules = $reservationRules;
		$this->customRules      = $customRules;
		$this->bufferRules      = $bufferRules;
	}

	/**
	 *
	 * @param \DateTime $checkInDate
	 * @param \DateTime $checkOutDate
	 * @param int       $roomTypeId
	 * @return bool
	 */
	public function verify( \DateTime $checkInDate, \DateTime $checkOutDate, $roomTypeId = 0 ) {
		if ( $roomTypeId ) {
			$roomTypeId = MPHB()->translation()->getOriginalId( $roomTypeId, MPHB()->postTypes()->roomType()->getPostType() );
		}

		return $this->reservationRules->verify( $checkInDate, $checkOutDate, $roomTypeId )
			&& $this->customRules->verify( $checkInDate, $checkOutDate, $roomTypeId );
	}

	/**
	 *
	 * @return \MPHB\BookingRules\Reservation\ReservationRules
	 */
	public function reservationRules() {
		return $this->reservationRules;
	}

	/**
	 *
	 * @return \MPHB\BookingRules\Custom\CustomRules
	 */
	public function customRules() {
		return $this->customRules;
	}

	/**
	 * @return Buffer\BufferRulesList
	 *
	 * @since 3.9
	 */
	public function bufferRules() {
		return $this->bufferRules;
	}

	/**
	 *
	 * @return array
	 */
	public function getData() {
		return array(
			'reservationRules' => $this->reservationRules->getData(),
			'dates'            => $this->customRules->getGlobalRestrictions(),
			'blockedTypes'     => $this->customRules->getGlobalTypeRestrictions(),
			// Array of [buffer_days, season_ids, room_type_ids]
			'bufferRules'      => $this->bufferRules->toArray(),
		);
	}

}
