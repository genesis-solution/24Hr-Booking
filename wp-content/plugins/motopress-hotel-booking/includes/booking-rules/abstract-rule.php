<?php

namespace MPHB\BookingRules;

use DateTime;

/**
 * @since 3.9
 */
abstract class AbstractRule implements RuleVerifyInterface {

	/**
	 * @var int[]
	 *
	 * @since 3.9
	 */
	protected $seasonIds = array();

	/**
	 * @var int[]
	 *
	 * @since 3.9
	 */
	protected $roomTypeIds = array();

	/**
	 * @param array $atts
	 *     @param array $atts['season_ids']
	 *     @param array $atts['room_type_ids']
	 *
	 * @since 3.9
	 */
	public function __construct( $atts ) {
		$this->seasonIds   = array_map( 'intval', $atts['season_ids'] );
		$this->roomTypeIds = array_map( 'intval', $atts['room_type_ids'] );
	}

	/**
	 * @param DateTime $date
	 * @param int      $roomTypeId
	 * @return bool
	 *
	 * @since 3.9
	 */
	public function isFor( $date, $roomTypeId ) {
		return $this->isForDate( $date ) && $this->isForRoomType( $roomTypeId );
	}

	/**
	 * @param DateTime $date
	 * @return bool
	 *
	 * @since 3.9
	 */
	public function isForDate( $date ) {
		if ( $this->isForAllSeasons() ) {
			return true;
		}

		foreach ( $this->seasonIds as $seasonId ) {
			$season = mphb_get_season( $seasonId );

			if ( ! is_null( $season ) && $season->isDateInSeason( $date ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * @param int $roomTypeId
	 * @return bool
	 *
	 * @since 3.9
	 */
	public function isForRoomType( $roomTypeId ) {
		if ( $this->isForAllRoomTypes() ) {
			return true;
		}

		return in_array( $roomTypeId, $this->roomTypeIds );
	}

	/**
	 * @return bool
	 *
	 * @since 3.9
	 */
	public function isForAllSeasons() {
		return ( in_array( 0, $this->seasonIds ) );
	}

	/**
	 * @return bool
	 *
	 * @since 3.9
	 */
	public function isForAllRoomTypes() {
		return ( in_array( 0, $this->roomTypeIds ) );
	}

	/**
	 * @return array
	 *
	 * @since 3.9
	 */
	public function toArray() {
		return array(
			'season_ids'    => $this->seasonIds,
			'room_type_ids' => $this->roomTypeIds,
		);
	}

	/**
	 * @return int[]
	 *
	 * @since 3.9
	 */
	public function getSeasonIds() {
		return $this->seasonIds;
	}

	/**
	 * @return int[]
	 *
	 * @since 3.9
	 */
	public function getRoomTypeIds() {
		return $this->roomTypeIds;
	}
}
