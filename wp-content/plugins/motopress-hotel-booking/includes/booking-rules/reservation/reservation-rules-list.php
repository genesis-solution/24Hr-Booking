<?php

namespace MPHB\BookingRules\Reservation;

use MPHB\BookingRules\AbstractRule;
use MPHB\BookingRules\AbstractRulesList;

/**
 * @since 3.9 renamed from RulesHolder.
 */
class ReservationRulesList extends AbstractRulesList {

	/**
	 * @var string
	 */
	protected $type = '';

	/**
	 * @param AbstractRule[] $rules
	 * @param string         $type Rule type (see constants of the ReservationRules class).
	 */
	public function __construct( $rules, $type = '' ) {
		parent::__construct( $rules );

		$this->type = $type;
	}

	/**
	 * @param AbstractRule[] $rules
	 * @return AbstractRule|null
	 */
	protected function combineRules( $rules ) {
		switch ( $this->type ) {
			case ReservationRules::RULE_CHECK_IN:
				return $this->combineCheckInRules( $rules );
			break;
			case ReservationRules::RULE_CHECK_OUT:
				return $this->combineCheckOutRules( $rules );
			break;
			case ReservationRules::RULE_MIN_STAY:
				return $this->combineMinStayRules( $rules );
			break;
			case ReservationRules::RULE_MAX_STAY:
				return $this->combineMaxStayRules( $rules );
			break;
			case ReservationRules::RULE_MIN_ADVANCE:
				return $this->combineMinAdvanceRules( $rules );
			break;
			case ReservationRules::RULE_MAX_ADVANCE:
				return $this->combineMaxAdvanceRules( $rules );
			break;
		}

		return null;
	}

	/**
	 * @param CheckInRule[] $rules
	 * @return CheckInRule
	 */
	protected function combineCheckInRules( $rules ) {
		$days = array();

		foreach ( $rules as $rule ) {
			$days = array_merge( $days, $rule->getDays() );
		}

		$days = array_unique( $days );

		return new CheckInRule(
			array(
				'check_in_days' => $days,
				'season_ids'    => array( 0 ),
				'room_type_ids' => array( 0 ),
			)
		);
	}

	/**
	 * @param CheckOutRule[] $rules
	 * @return CheckOutRule
	 */
	protected function combineCheckOutRules( $rules ) {
		$days = array();

		foreach ( $rules as $rule ) {
			$days = array_merge( $days, $rule->getDays() );
		}

		$days = array_unique( $days );

		return new CheckOutRule(
			array(
				'check_out_days' => $days,
				'season_ids'     => array( 0 ),
				'room_type_ids'  => array( 0 ),
			)
		);
	}

	/**
	 * @param MinDaysRule[] $rules
	 * @return MinDaysRule
	 */
	protected function combineMinStayRules( $rules ) {
		$minStay = min(
			array_map(
				function ( $rule ) {
					return $rule->getMinDays();
				},
				$rules
			)
		);

		return new MinDaysRule(
			array(
				'min_stay_length' => $minStay,
				'season_ids'      => array( 0 ),
				'room_type_ids'   => array( 0 ),
			)
		);
	}

	/**
	 * @param MaxDaysRule[] $rules
	 * @return MaxDaysRule
	 */
	protected function combineMaxStayRules( $rules ) {
		$maxStay = max(
			array_map(
				function ( $rule ) {
					// Check if maxDays value is 0 and make it equal to a huge number
					return $rule->getMaxDays() == 0 ? 999999 : $rule->getMaxDays();
				},
				$rules
			)
		);

		return new MaxDaysRule(
			array(
				'max_stay_length' => $maxStay,
				'season_ids'      => array( 0 ),
				'room_type_ids'   => array( 0 ),
			)
		);
	}

	/**
	 * @param MinAdvanceDaysRule[] $rules
	 * @return MinAdvanceDaysRule
	 *
	 * @since 3.9
	 */
	protected function combineMinAdvanceRules( $rules ) {
		$minAdvance = min(
			array_map(
				function ( $rule ) {
					return $rule->getMinAdvanceDays();
				},
				$rules
			)
		);

		return new MinAdvanceDaysRule(
			array(
				'min_advance_reservation' => $minAdvance,
				'season_ids'              => array( 0 ),
				'room_type_ids'           => array( 0 ),
			)
		);
	}

	/**
	 * @param MaxAdvanceDaysRule[] $rules
	 * @return MaxAdvanceDaysRule
	 *
	 * @since 3.9
	 */
	protected function combineMaxAdvanceRules( $maxAdvanceDaysRules ) {

		$maxAdvance = 0;

		foreach ( $maxAdvanceDaysRules as $rule ) {

			if ( $maxAdvance < $rule->getMaxAdvanceDays() ) {
				$maxAdvance = $rule->getMaxAdvanceDays();
			}
		}

		return new MaxAdvanceDaysRule(
			array(
				'max_advance_reservation' => $maxAdvance,
				'season_ids'              => array( 0 ),
				'room_type_ids'           => array( 0 ),
			)
		);
	}
}
