<?php

namespace MPHB\BookingRules\Custom;

use MPHB\BookingRules\RuleVerifyInterface;

class CustomRules implements RuleVerifyInterface {

	/**
	 *
	 * @var CustomRule[] [%Type ID% => CustomRule[]]
	 */
	private $globals = array();

	/**
	 * Also <b>contains</b> global rules.
	 *
	 * @var array [%Type ID% => ["room_id" => ..., "rule" => CustomRule]]
	 */
	private $rules = array();

	/**
	 * @var \MPHB\BookingRules\Custom\CustomRule[]
	 */
	protected $list = array();

	/**
	 *
	 * @since 3.9.9 - don't create custom rules object if rules use for admin is disabled.
	 */
	public function __construct( $customRules ) {

		if ( MPHB()->settings()->main()->isBookingRulesForAdminDisabled() ) {
			return;
		}

		foreach ( $customRules as $customRule ) {

			$ruleInstance = CustomRule::create( $customRule );

			if ( is_null( $ruleInstance ) ) {
				continue;
			}

			$this->list[] = $ruleInstance;

			$typeId = (int) $customRule['room_type_id'];
			$roomId = (int) $customRule['room_id'];

			if ( $roomId == 0 ) {
				if ( ! isset( $this->globals[ $typeId ] ) ) {
					$this->globals[ $typeId ] = array();
				}

				$this->globals[ $typeId ][] = $ruleInstance;
			}

			if ( $typeId != 0 ) {
				if ( ! isset( $this->rules[ $typeId ] ) ) {
					$this->rules[ $typeId ] = array();
				}

				$this->rules[ $typeId ][] = array(
					'room_id' => $roomId,
					'rule'    => $ruleInstance,
				);
			}
		} // For each custom rule
	}

	/**
	 *
	 * @param \DateTime $checkIn
	 * @param \DateTime $checkOut
	 * @param int       $typeId Optional. 0 by default. Already translated in RulesChecker.
	 * @return bool
	 */
	public function verify( \DateTime $checkIn, \DateTime $checkOut, $typeId = 0 ) {

		$verified = true;

		$verifyTypes = array( 0 );
		if ( $typeId != 0 ) {
			$verifyTypes[] = $typeId;
		}

		foreach ( $verifyTypes as $_typeId ) {
			if ( ! isset( $this->globals[ $_typeId ] ) ) {
				continue;
			}

			foreach ( $this->globals[ $_typeId ] as $rule ) {
				if ( ! $rule->verify( $checkIn, $checkOut ) ) {
					$verified = false;
					break 2;
				}
			}
		}

		return $verified;
	}

	private function getGlobalRulesForRoomTypeId( $roomTypeId = 0 ) {

		$rules = array();

		$verifyTypes = array( 0 );
		if ( 0 != $roomTypeId ) {
			$verifyTypes[] = $roomTypeId;
		}

		foreach ( $verifyTypes as $_typeId ) {

			if ( isset( $this->globals[ $_typeId ] ) ) {

				$rules = array_merge( $rules, $this->globals[ $_typeId ] );
			}
		}

		return $rules;
	}

	public function verifyNotCheckInRestriction( \DateTime $checkInDate, \DateTime $checkOutDate, $roomTypeId = 0 ) {

		$rules = $this->getGlobalRulesForRoomTypeId( $roomTypeId );

		foreach ( $rules as $rule ) {
			if ( $rule->noCheckIn( $checkInDate ) ) {
				return false;
			}
		}
		return true;
	}

	public function verifyNotCheckOutRestriction( \DateTime $checkInDate, \DateTime $checkOutDate, $roomTypeId = 0 ) {

		$rules = $this->getGlobalRulesForRoomTypeId( $roomTypeId );

		foreach ( $rules as $rule ) {
			if ( $rule->noCheckOut( $checkOutDate ) ) {
				return false;
			}
		}
		return true;
	}

	public function verifyNotStayInRestriction( \DateTime $checkInDate, \DateTime $checkOutDate, $roomTypeId = 0 ) {

		$checkOutDateForCheckingRules = clone $checkOutDate;

		if ( $checkInDate->format( 'Y-m-d' ) == $checkOutDateForCheckingRules->format( 'Y-m-d' ) ) {

			$checkOutDateForCheckingRules = $checkOutDateForCheckingRules->modify( '+1 day' );
		}

		$rules = $this->getGlobalRulesForRoomTypeId( $roomTypeId );

		foreach ( $rules as $rule ) {
			if ( $rule->noStayIn( $checkInDate, $checkOutDateForCheckingRules ) ) {
				return false;
			}
		}
		return true;
	}

	/**
	 *
	 * @param \DateTime $checkIn
	 * @param \DateTime $checkOut
	 * @param int       $typeId
	 * @return int[]
	 */
	public function getUnavailableRooms( \DateTime $checkIn, \DateTime $checkOut, $typeId ) {

		$typeId = MPHB()->translation()->getOriginalId( $typeId, MPHB()->postTypes()->roomType()->getPostType() );

		if ( ! isset( $this->rules[ $typeId ] ) ) {
			return array();
		} elseif ( ! $this->verify( $checkIn, $checkOut, $typeId ) ) {
			// All unavailable when global rule fails
			return MPHB()->getRoomPersistence()->findAllIdsByType( $typeId );
		}

		$unavailableRooms = array();

		foreach ( $this->rules[ $typeId ] as $ruleWrapper ) {
			$roomId     = $ruleWrapper['room_id'];
			$customRule = $ruleWrapper['rule'];

			if ( $roomId == 0 ) {
				continue; // Already verified globally for type
			}

			if ( ! $customRule->verify( $checkIn, $checkOut ) ) {
				$unavailableRooms[] = $roomId;
			}
		}

		$unavailableRooms = array_unique( $unavailableRooms );
		sort( $unavailableRooms ); // Will also reset keys after array_unique()

		return $unavailableRooms;
	}

	/**
	 *
	 * @param \DateTime $checkIn
	 * @param \DateTime $checkOut
	 * @param int       $typeId
	 * @return int
	 */
	public function getUnavailableRoomsCount( \DateTime $checkIn, \DateTime $checkOut, $typeId ) {

		$unavailableRooms = $this->getUnavailableRooms( $checkIn, $checkOut, $typeId );
		return count( $unavailableRooms );
	}

	public function getBlockedRoomsCounts( $roomTypeId ) {

		$roomTypeId = MPHB()->translation()->getOriginalId( $roomTypeId, MPHB()->postTypes()->roomType()->getPostType() );

		$counts = array();

		// process global rules
		$globalRules         = $this->getGlobalRulesForRoomTypeId( $roomTypeId );
		$allActiveRoomsCount = count( MPHB()->getRoomPersistence()->findAllIdsByType( $roomTypeId ) );

		foreach ( $globalRules as $globalRule ) {

			if ( ! $globalRule->isBlocked() ) {
				continue;
			}

			foreach ( $globalRule->getPeriodDates() as $date ) {

				$date            = $date->format( 'Y-m-d' );
				$counts[ $date ] = $allActiveRoomsCount;
			}
		}

		if ( ! isset( $this->rules[ $roomTypeId ] ) ) {
			return $counts;
		}

		// process rooms specific rules
		$foundRooms = array();

		foreach ( $this->rules[ $roomTypeId ] as $ruleWrapper ) {

			$roomId = $ruleWrapper['room_id'];
			$rule   = $ruleWrapper['rule'];

			if ( $roomId == 0 || ! $rule->isBlocked() ) {
				continue;
			}

			foreach ( $rule->getPeriodDates() as $date ) {

				$date                = $date->format( 'Y-m-d' );
				$foundRooms[ $date ] = isset( $foundRooms[ $date ] ) ? $foundRooms[ $date ] : array();
				$isNewRoom           = ! in_array( $roomId, $foundRooms[ $date ] );

				if ( isset( $counts[ $date ] ) ) {
					$counts[ $date ] += $isNewRoom ? 1 : 0;
				} else {
					$counts[ $date ] = 1;
				}

				if ( $isNewRoom ) {
					$foundRooms[ $date ][] = $roomId;
				}
			}
		}

		return $counts;
	}

	/**
	 *
	 * @return array ["2017-01-01" => ["not_check_in" => true,
	 * "not_check_out" => true, "not_stay_in" => true], ...]
	 */
	public function getGlobalRestrictions() {

		if ( ! isset( $this->globals[0] ) ) {
			return array();
		}

		$dates = array();

		foreach ( $this->globals[0] as $rule ) {
			$newDates = $rule->getRestrictionsByDays();
			$dates    = $this->mergeRestrictions( $dates, $newDates );
		}

		ksort( $dates );

		return $dates;
	}

	/**
	 *
	 * @return array ["2017-01-01" => ["not_check_in" => true,
	 * "not_check_out" => true, "not_stay_in" => true], ...]
	 */
	public function getGlobalTypeRestrictions() {

		$dates = array();

		foreach ( $this->globals as $typeId => $rules ) {
			if ( $typeId == 0 ) {
				continue;
			}

			$typeDates = array();

			foreach ( $rules as $rule ) {
				$newDates  = $rule->getRestrictionsByDays();
				$typeDates = $this->mergeRestrictions( $typeDates, $newDates );
			}

			$dates[ $typeId ] = $typeDates;
		}

		return $dates;
	}

	/**
	 *
	 * @param array $dates1 [%date% => %restrictions%]
	 * @param array $dates2 [%date% => %restrictions%]
	 *
	 * @return array [%date% => %merged restrictions%]
	 */
	private function mergeRestrictions( $dates1, $dates2 ) {

		$dates = $dates1;

		foreach ( $dates2 as $date => $restrictions ) {
			if ( ! isset( $dates[ $date ] ) ) {
				$dates[ $date ] = $restrictions;
			} else {
				foreach ( $restrictions as $param => $value ) {
					$dates[ $date ][ $param ] = $dates[ $date ][ $param ] || $value;
				}
			}
		}

		return $dates;
	}

	/**
	 * @param int   $typeId Booking Calendar queries only original types.
	 * @param array $roomIds
	 *
	 * @return array
	 */
	public function getCommentsByDates( $typeId, $roomIds ) {

		$globals = array();

		foreach ( $this->globals as $_typeId => $rules ) {
			if ( $_typeId != $typeId && $_typeId != 0 ) {
				continue;
			}

			foreach ( $rules as $rule ) {
				$dates   = $rule->getBlockedDates();
				$comment = $rule->getComment();

				foreach ( $dates as $dateYmd ) {
					if ( empty( $globals[ $dateYmd ] ) ) {
						$globals[ $dateYmd ] = $comment;
					} elseif ( ! empty( $comment ) ) {
						$globals[ $dateYmd ] .= ', ' . $comment;
					}
				}
			}
		}

		$rooms = array_fill_keys( $roomIds, $globals );

		if ( ! isset( $this->rules[ $typeId ] ) ) { // Also: if ( $typeId == 0 )
			return $rooms;
		}

		foreach ( $this->rules[ $typeId ] as $ruleWrapper ) {
			$roomId = $ruleWrapper['room_id'];
			$rule   = $ruleWrapper['rule'];

			if ( $roomId == 0 || ! in_array( $roomId, $roomIds ) ) {
				continue;
			}

			$dates   = $rule->getBlockedDates();
			$comment = $rule->getComment();

			foreach ( $dates as $dateYmd ) {
				if ( empty( $rooms[ $roomId ][ $dateYmd ] ) ) {
					$rooms[ $roomId ][ $dateYmd ] = $comment;
				} elseif ( ! empty( $comment ) ) {
					$rooms[ $roomId ][ $dateYmd ] .= ', ' . $comment;
				}
			}
		}

		return $rooms;
	}

	/**
	 * @param array  $atts
	 * @param int    $atts['roomTypeId'] Optional. 0 by default (get all).
	 * @param int    $atts['roomId'] Optional. 0 by default (get all).
	 * @param string $atts['restriction'] blocked|check-in|check-out|stay-in.
	 *                                    Optional. "blocked" by default.
	 * @return array [[roomTypeId, roomId, startDate, endDate, comment]]
	 *               (roomTypeId and roomId of the rule, not the attributes)
	 */
	public function filter( $atts ) {

		$roomTypeId  = isset( $atts['roomTypeId'] ) ? $atts['roomTypeId'] : 0;
		$roomId      = isset( $atts['roomId'] ) ? $atts['roomId'] : 0;
		$restriction = isset( $atts['restriction'] ) ? $atts['restriction'] : 'blocked';

		$results = array();

		foreach ( $this->list as $rule ) {
			$ruleTypeId = $rule->getRoomTypeId();
			$ruleRoomId = $rule->getRoomId();

			if ( $roomTypeId != 0 && $ruleTypeId != 0 && $roomTypeId != $ruleTypeId ) {
				continue;
			}

			if ( $roomId != 0 && $ruleRoomId != 0 && $roomId != $ruleRoomId ) {
				continue;
			}

			switch ( $restriction ) {
				case 'blocked':
					if ( ! $rule->isBlocked() ) {
						continue 2;
					}
					break;
				case 'check-in':
					if ( ! $rule->noCheckIn() ) {
						continue 2;
					}
					break;
				case 'check-out':
					if ( ! $rule->noCheckOut() ) {
						continue 2;
					}
					break;
				case 'stay-in':
					if ( ! $rule->noStayIn() ) {
						continue 2;
					}
					break;
			}

			// All filters done. Add the rule to results
			$results[] = array(
				'roomTypeId' => $ruleTypeId,
				'roomId'     => $ruleRoomId,
				'startDate'  => $rule->getStartDate(),
				'endDate'    => $rule->getEndDate(),
				'comment'    => $rule->getComment(),
			);
		}

		return $results;
	}
}
