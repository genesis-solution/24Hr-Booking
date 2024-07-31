<?php

namespace MPHB\BookingRules\Reservation;

use MPHB\BookingRules\RuleVerifyInterface;

class ReservationRules implements RuleVerifyInterface {

	const RULE_CHECK_IN    = 'check_in_days';
	const RULE_CHECK_OUT   = 'check_out_days';
	const RULE_MIN_STAY    = 'min_stay_length';
	const RULE_MAX_STAY    = 'max_stay_length';
	const RULE_MIN_ADVANCE = 'min_advance_reservation';
	const RULE_MAX_ADVANCE = 'max_advance_reservation';

	/**
	 * @var ReservationRulesList[]
	 */
	private $rules = array(
		self::RULE_CHECK_IN    => null,
		self::RULE_CHECK_OUT   => null,
		self::RULE_MIN_STAY    => null,
		self::RULE_MAX_STAY    => null,
		self::RULE_MIN_ADVANCE => null,
		self::RULE_MAX_ADVANCE => null,
	);

	/**
	 *
	 * ReservationRules constructor.
	 *
	 * @param array $rules
	 *
	 * @since 3.9.9 - reset rules to default values if using for admin is disabled
	 */
	public function __construct( array $rules ) {

		if ( MPHB()->settings()->main()->isBookingRulesForAdminDisabled() ) {

			foreach ( $this->rules as $type => $value ) { // Reset rules
				$rules[ $type ] = array();
			}
		}

		$this->cleanEmptyRules( $rules );
		$this->fillFallbackRules( $rules );

		$this->rules['check_in_days'] = new ReservationRulesList(
			array_map(
				function ( $data ) {
					return new CheckInRule( $data );
				},
				$rules['check_in_days']
			),
			self::RULE_CHECK_IN
		);

		$this->rules['check_out_days'] = new ReservationRulesList(
			array_map(
				function ( $data ) {
					return new CheckOutRule( $data );
				},
				$rules['check_out_days']
			),
			self::RULE_CHECK_OUT
		);

		$this->rules['min_stay_length'] = new ReservationRulesList(
			array_map(
				function ( $data ) {
					return new MinDaysRule( $data );
				},
				$rules['min_stay_length']
			),
			self::RULE_MIN_STAY
		);

		$this->rules['max_stay_length'] = new ReservationRulesList(
			array_map(
				function ( $data ) {
					return new MaxDaysRule( $data );
				},
				$rules['max_stay_length']
			),
			self::RULE_MAX_STAY
		);

		$this->rules['min_advance_reservation'] = new ReservationRulesList(
			array_map(
				function ( $data ) {
					return new MinAdvanceDaysRule( $data );
				},
				$rules['min_advance_reservation']
			),
			self::RULE_MIN_ADVANCE
		);

		$this->rules['max_advance_reservation'] = new ReservationRulesList(
			array_map(
				function ( $data ) {
					return new MaxAdvanceDaysRule( $data );
				},
				$rules['max_advance_reservation']
			),
			self::RULE_MAX_ADVANCE
		);
	}

	/**
	 * Delete rules that not applied at least to one season and at least to one room.
	 *
	 * @param $rules
	 */
	private function cleanEmptyRules( &$rules ) {
		foreach ( $rules as $type => &$typeRules ) {
			$typeRules = array_filter(
				$typeRules,
				function ( $rule ) {
					return ! empty( $rule['season_ids'] ) && ! empty( $rule['room_type_ids'] );
				}
			);
		}
	}

	/**
	 * Fill fallback default rules
	 *
	 * @param array $rules
	 */
	private function fillFallbackRules( &$rules ) {

		array_push(
			$rules[ self::RULE_CHECK_IN ],
			array(
				'check_in_days' => range( 0, 6 ),
				'season_ids'    => array( 0 ),
				'room_type_ids' => array( 0 ),
			)
		);
		array_push(
			$rules[ self::RULE_CHECK_OUT ],
			array(
				'check_out_days' => range( 0, 6 ),
				'season_ids'     => array( 0 ),
				'room_type_ids'  => array( 0 ),
			)
		);
		array_push(
			$rules[ self::RULE_MIN_STAY ],
			array(
				'min_stay_length' => 1,
				'season_ids'      => array( 0 ),
				'room_type_ids'   => array( 0 ),
			)
		);
		array_push(
			$rules[ self::RULE_MAX_STAY ],
			array(
				'max_stay_length' => 0, // unlimited
				'season_ids'      => array( 0 ),
				'room_type_ids'   => array( 0 ),
			)
		);
		array_push(
			$rules[ self::RULE_MIN_ADVANCE ],
			array(
				'min_advance_reservation' => 0, // can be booked today
				'season_ids'              => array( 0 ),
				'room_type_ids'           => array( 0 ),
			)
		);
		array_push(
			$rules[ self::RULE_MAX_ADVANCE ],
			array(
				'max_advance_reservation' => 0, // no limit
				'season_ids'              => array( 0 ),
				'room_type_ids'           => array( 0 ),
			)
		);
	}

	/**
	 *
	 * @param \DateTime $checkInDate
	 * @param \DateTime $checkOutDate
	 * @param int       $roomTypeId
	 *
	 * @return bool
	 */
	public function verify( \DateTime $checkInDate, \DateTime $checkOutDate, $roomTypeId = 0 ) {
		$verified = true;

		foreach ( $this->rules as $typeRules ) {

			if ( $roomTypeId ) {
				$actualRule = $typeRules->findActualRule( $checkInDate, $roomTypeId );
			} else {
				$actualRule = $typeRules->findActualCombinedRule( $checkInDate );
			}

			if ( ! $actualRule->verify( $checkInDate, $checkOutDate ) ) {
				$verified = false;
				break;
			}
		}

		return $verified;
	}

	/**
	 * @param $ruleType one of constant in this class
	 */
	private function verifyRule( string $ruleType, \DateTime $checkInDate, \DateTime $checkOutDate, $roomTypeId = 0 ) {

		$actualRule = null;

		if ( $roomTypeId ) {
			$actualRule = $this->rules[ $ruleType ]->findActualRule( $checkInDate, $roomTypeId );
		} else {
			$actualRule = $this->rules[ $ruleType ]->findActualCombinedRule( $checkInDate );
		}

		return null != $actualRule ? $actualRule->verify( $checkInDate, $checkOutDate ) : true;
	}

	public function verifyMinStayLengthReservationRule( \DateTime $checkInDate, \DateTime $checkOutDate, $roomTypeId = 0 ) {
		return $this->verifyRule( self::RULE_MIN_STAY, $checkInDate, $checkOutDate, $roomTypeId );
	}

	public function getMinStayLengthReservationDaysCount( \DateTime $checkInDate, $roomTypeId = 0 ) {

		$actualRule = null;

		if ( $roomTypeId ) {
			$actualRule = $this->rules[ self::RULE_MIN_STAY ]->findActualRule( $checkInDate, $roomTypeId );
		} else {
			$actualRule = $this->rules[ self::RULE_MIN_STAY ]->findActualCombinedRule( $checkInDate );
		}

		$minStayDaysCount = null;

		if ( null != $actualRule ) {
			$minStayDaysCount = $actualRule->getMinDays();
		}

		return $minStayDaysCount;
	}

	public function getMaxStayLengthReservationDaysCount( \DateTime $checkInDate, $roomTypeId = 0 ) {

		$actualRule = null;

		if ( $roomTypeId ) {
			$actualRule = $this->rules[ self::RULE_MAX_STAY ]->findActualRule( $checkInDate, $roomTypeId );
		} else {
			$actualRule = $this->rules[ self::RULE_MAX_STAY ]->findActualCombinedRule( $checkInDate );
		}

		$maxStayDaysCount = null;

		if ( null != $actualRule ) {
			$maxStayDaysCount = $actualRule->getMaxDays();
		}

		return $maxStayDaysCount;
	}

	public function verifyMaxStayLengthReservationRule( \DateTime $checkInDate, \DateTime $checkOutDate, $roomTypeId = 0 ) {
		return $this->verifyRule( self::RULE_MAX_STAY, $checkInDate, $checkOutDate, $roomTypeId );
	}

	public function verifyCheckInDaysReservationRule( \DateTime $checkInDate, \DateTime $checkOutDate, $roomTypeId = 0 ) {
		return $this->verifyRule( self::RULE_CHECK_IN, $checkInDate, $checkOutDate, $roomTypeId );
	}

	public function verifyCheckOutDaysReservationRule( \DateTime $checkInDate, \DateTime $checkOutDate, $roomTypeId = 0 ) {
		return $this->verifyRule( self::RULE_CHECK_OUT, $checkInDate, $checkOutDate, $roomTypeId );
	}

	public function verifyMinAdvanceReservationRule( \DateTime $checkInDate, \DateTime $checkOutDate, $roomTypeId = 0 ) {
		return $this->verifyRule( self::RULE_MIN_ADVANCE, $checkInDate, $checkOutDate, $roomTypeId );
	}

	public function verifyMaxAdvanceReservationRule( \DateTime $checkInDate, \DateTime $checkOutDate, $roomTypeId = 0 ) {
		return $this->verifyRule( self::RULE_MAX_ADVANCE, $checkInDate, $checkOutDate, $roomTypeId );
	}

	/**
	 *
	 * @return array
	 */
	public function getData() {
		return array_map(
			function ( ReservationRulesList $ruleHolder ) {
				return $ruleHolder->toArray();
			},
			$this->rules
		);
	}

	/**
	 * @param $roomTypeId
	 *
	 * @return int
	 */
	public function getMinDaysAllSeason( $roomTypeId ) {
		return $this->rules[ self::RULE_MIN_STAY ]->findAllSeasonsRule( $roomTypeId )->getMinDays();
	}

}
