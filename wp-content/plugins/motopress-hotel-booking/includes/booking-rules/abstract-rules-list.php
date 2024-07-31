<?php

namespace MPHB\BookingRules;

use DateTime;

/**
 * @since 3.9
 */
abstract class AbstractRulesList implements RuleVerifyInterface {

	/**
	 * @var AbstractRule[]
	 *
	 * @since 3.9
	 */
	protected $rules = array();

	/**
	 * @param AbstractRule[] $rules
	 *
	 * @since 3.9
	 */
	public function __construct( $rules ) {
		$this->rules = $rules;
	}

	/**
	 * @param DateTime $checkInDate
	 * @param DateTime $checkOutDate
	 * @param int      $roomTypeId Optional.
	 * @return bool
	 *
	 * @since 3.9
	 */
	public function verify( DateTime $checkInDate, DateTime $checkOutDate, $roomTypeId = 0 ) {
		$rule = $this->findActualRule( $checkInDate, $roomTypeId );

		return ! is_null( $rule ) ? $rule->verify( $checkInDate, $checkOutDate, $roomTypeId ) : true;
	}

	/**
	 * Find the rule that suits the date and room type ID.
	 *
	 * @param DateTime $date
	 * @param int      $roomTypeId Optional.
	 * @return AbstractRule|null
	 *
	 * @since 1.0
	 */
	public function findActualRule( $date, $roomTypeId = 0 ) {
		foreach ( $this->rules as $rule ) {
			if ( $rule->isFor( $date, $roomTypeId ) ) {
				return $rule;
			}
		}

		return null;
	}

	/**
	 * @param DateTime $date
	 * @return AbstractRule
	 *
	 * @since 3.9
	 */
	public function findActualCombinedRule( $date ) {
		// Get actual rule for each room type and combine them together
		$actualRules = array();

		$allRoomTypeIds = MPHB()->getCoreAPI()->getAllRoomTypeOriginalIds();

		$processedRoomTypeIds = array();

		foreach ( $this->rules as $rule ) {

			$roomTypeIds = array_diff( $rule->getRoomTypeIds(), $processedRoomTypeIds );

			if ( empty( $roomTypeIds ) || ! $rule->isForDate( $date ) ) {
				continue;
			}

			$actualRules[]        = $rule;
			$processedRoomTypeIds = array_merge( $processedRoomTypeIds, $rule->getRoomTypeIds() );

			if ( $rule->isForAllRoomTypes() ) {
				// Already found the most general rule
				break;
			}

			// All room types processed?
			$leftRoomTypeIds = array_diff( $allRoomTypeIds, $processedRoomTypeIds );

			if ( count( $leftRoomTypeIds ) == 0 ) {
				break;
			}
		}

		return $this->combineRules( $actualRules );
	}

	/**
	 * @param int $roomTypeId Optional.
	 * @return AbstractRule|null
	 *
	 * @since 3.9
	 */
	public function findAllSeasonsRule( $roomTypeId = 0 ) {
		foreach ( $this->rules as $rule ) {
			if ( $rule->isForRoomType( $roomTypeId ) && $rule->isForAllSeasons() ) {
				return $rule;
			}
		}

		return null;
	}

	/**
	 * @param AbstractRule[] $rules
	 * @return AbstractRule|null
	 *
	 * @since 3.9
	 */
	abstract protected function combineRules( $rules);

	/**
	 * @return array
	 *
	 * @since 3.9
	 */
	public function toArray() {
		return array_map(
			function ( $rule ) {
				return $rule->toArray();
			},
			$this->rules
		);
	}
}
