<?php

namespace MPHB\BookingRules\Buffer;

use MPHB\BookingRules\AbstractRulesList;

/**
 * @since 3.9
 */
class BufferRulesList extends AbstractRulesList {

	/**
	 * @param BufferDaysRule[] $rules
	 * @return BufferDaysRule|null
	 *
	 * @since 3.9
	 */
	protected function combineRules( $rules ) {
		$minDays = min(
			array_map(
				function ( $rule ) {
					return $rule->getBufferDays();
				},
				$rules
			)
		);

		return new BufferDaysRule(
			array(
				'buffer_days'   => $minDays,
				'season_ids'    => array( 0 ),
				'room_type_ids' => array( 0 ),
			)
		);
	}

	/**
	 * @return bool
	 *
	 * @since 3.9
	 */
	public function hasRules() {
		return count( $this->rules ) > 1; // Don't count a backfill rule
	}

	/**
	 * @param array $rules
	 * @return static
	 *
	 * @since 3.9
	 */
	public static function create( $rules ) {
		// Always must be at least one fallback rule to guarantee that
		// findActualRule() will always find a rule
		array_push(
			$rules,
			array(
				'buffer_days'   => 0,
				'season_ids'    => array( 0 ),
				'room_type_ids' => array( 0 ),
			)
		);

		$bufferDays = array_map(
			function ( $rule ) {
				return new BufferDaysRule( $rule );
			},
			$rules
		);

		return new static( $bufferDays );
	}
}
