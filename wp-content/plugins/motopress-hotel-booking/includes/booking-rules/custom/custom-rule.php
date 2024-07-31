<?php

namespace MPHB\BookingRules\Custom;

use MPHB\BookingRules\RuleVerifyInterface;
use MPHB\Utils\DateUtils;

class CustomRule implements RuleVerifyInterface {

	protected $roomTypeId = 0;
	protected $roomId     = 0;

	/**
	 *
	 * @var \DateTime
	 */
	protected $dateFrom;

	/**
	 *
	 * @var \DateTime
	 */
	protected $dateTo;

	/**
	 *
	 * @var bool
	 */
	protected $notCheckIn = false;

	/**
	 *
	 * @var bool
	 */
	protected $notCheckOut = false;

	/**
	 *
	 * @var bool
	 */
	protected $notStayIn = false;

	/**
	 *
	 * @var string
	 */
	protected $comment = '';

	/**
	 *
	 * @param array     $atts
	 * @param \DateTime $atts['date_from']
	 * @param \DateTime $atts['date_to']
	 * @param array     $atts['restrictions']
	 */
	protected function __construct( $atts ) {
		$this->roomTypeId  = (int) $atts['room_type_id'];
		$this->roomId      = (int) $atts['room_id'];
		$this->dateFrom    = $atts['date_from'];
		$this->dateTo      = $atts['date_to'];
		$this->notCheckIn  = in_array( 'check-in', $atts['restrictions'] );
		$this->notCheckOut = in_array( 'check-out', $atts['restrictions'] );
		$this->notStayIn   = in_array( 'stay-in', $atts['restrictions'] );
		$this->comment     = $atts['comment'];
	}

	public function getRestrictions() {
		return array(
			'not_check_in'  => $this->notCheckIn,
			'not_check_out' => $this->notCheckOut,
			'not_stay_in'   => $this->notStayIn,
		);
	}

	/**
	 *
	 * @return array ["2017-01-01" => ["not_check_in" => true,
	 * "not_check_out" => true, "not_stay_in" => true], "2017-01-02" => ...]
	 */
	public function getRestrictionsByDays() {
		$dateFormat   = MPHB()->settings()->dateTime()->getDateTransferFormat();
		$restrictions = $this->getRestrictions();

		$dates = array();

		foreach ( $this->getPeriodDates() as $date ) {
			$date           = $date->format( $dateFormat );
			$dates[ $date ] = $restrictions;
		}

		return $dates;
	}

	public function getBlockedDates() {
		$restrictions = $this->getRestrictions();

		if ( ! $restrictions['not_stay_in'] ) {
			return array();
		}

		$dates = $this->getPeriodDates();

		// Format all dates
		$dateFormat = MPHB()->settings()->dateTime()->getDateTransferFormat();
		foreach ( $dates as &$date ) {
			$date = $date->format( $dateFormat );
		}
		unset( $date );

		return $dates;
	}

	public function getPeriodDates() {
		$period = DateUtils::createDatePeriod( $this->dateFrom, $this->dateTo, true );
		return iterator_to_array( $period );
	}

	public function getComment() {
		return $this->comment;
	}

	/**
	 *
	 * @return bool
	 */
	public function isBlocked() {
		return $this->notStayIn;
	}

	public function verify( \DateTime $checkInDate, \DateTime $checkOutDate, $roomTypeId = 0 ) {
		if ( $this->noCheckIn( $checkInDate ) || $this->noCheckOut( $checkOutDate ) || $this->noStayIn( $checkInDate, $checkOutDate ) ) {
			return false;
		} else {
			return true;
		}
	}

	/**
	 * @param \DateTime|null $checkInDate Optional. If not set - don't check the
	 *                                    dates.
	 * @return bool
	 */
	public function noCheckIn( \DateTime $checkInDate = null ) {
		if ( is_null( $checkInDate ) ) {
			return $this->notCheckIn;
		} else {
			return $this->notCheckIn
				&& $this->compareDates( $checkInDate, '>=', $this->dateFrom )
				&& $this->compareDates( $checkInDate, '<=', $this->dateTo );
		}
	}

	/**
	 * @param \DateTime|null $checkOutDate Optional. If not set - don't check
	 *                                     the dates.
	 * @return bool
	 */
	public function noCheckOut( \DateTime $checkOutDate = null ) {
		if ( is_null( $checkOutDate ) ) {
			return $this->notCheckOut;
		} else {
			return $this->notCheckOut
				&& $this->compareDates( $checkOutDate, '>=', $this->dateFrom )
				&& $this->compareDates( $checkOutDate, '<=', $this->dateTo );
		}
	}

	/**
	 * @param \DateTime|null $checkInDate
	 * @param \DateTime|null $checkOutDate
	 * @return bool
	 */
	public function noStayIn( \DateTime $checkInDate = null, \DateTime $checkOutDate = null ) {
		if ( ! $this->notStayIn ) {
			return false;
		}

		if ( ! is_null( $checkInDate ) && $this->compareDates( $this->dateTo, '<', $checkInDate ) ) {
			return false;
		}

		if ( ! is_null( $checkOutDate ) ) {
			$beforeCheckOut = clone $checkOutDate;
			$beforeCheckOut->modify( '-1 day' );

			if ( $this->compareDates( $this->dateFrom, '>', $beforeCheckOut ) ) {
				return false;
			}
		}

		return true;
	}

	protected function compareDates( \DateTime $date1, $operator, \DateTime $date2 ) {
		$date1 = $date1->format( 'Ymd' );
		$date2 = $date2->format( 'Ymd' );

		switch ( $operator ) {
			case '>':
				return $date1 > $date2;
			break;
			case '>=':
				return $date1 >= $date2;
			break;
			case '<':
				return $date1 < $date2;
			break;
			case '<=':
				return $date1 <= $date2;
			break;
		}

		return false;
	}

	/**
	 *
	 * @param array  $atts
	 * @param string $atts['date_from']
	 * @param string $atts['date_to']
	 * @param array  $atts['restrictions']
	 *
	 * @return \MPHB\BookingRules\Custom\CustomRule
	 */
	public static function create( $atts ) {
		$dateFormat = MPHB()->settings()->dateTime()->getDateTransferFormat(); // Y-m-d

		$atts['date_from'] = \DateTime::createFromFormat( $dateFormat, $atts['date_from'] );
		$atts['date_to']   = \DateTime::createFromFormat( $dateFormat, $atts['date_to'] );

		if ( ! $atts['date_from'] || ! $atts['date_to'] ) {
			return null;
		}

		if ( DateUtils::calcNights( $atts['date_from'], $atts['date_to'] ) < 0 ) {
			return null;
		}

		if ( ! isset( $atts['comment'] ) ) {
			$atts['comment'] = '';
		}

		return new self( $atts );
	}

	public function getRoomTypeId() {
		return $this->roomTypeId;
	}

	public function getRoomId() {
		return $this->roomId;
	}

	public function getStartDate() {
		return $this->dateFrom;
	}

	public function getEndDate() {
		return $this->dateTo;
	}

}
