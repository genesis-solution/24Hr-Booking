<?php

namespace MPHB\Reports\Data;

use MPHB\Utils\DateUtils;
use MPHB\Reports\ReportFilters;

class ReportByDatesData extends AbstractReportsData {

	/**
	 * @var \DatePeriod
	 */
	protected $datesPeriod;

	/**
	 * @var string
	 */
	protected $dateFrom;

	/**
	 * @var string
	 */
	protected $dateTo;

	/**
	 *
	 * @param array $atts
	 */
	public function __construct( $atts ) {
		parent::__construct( $atts );

		if ( empty( $this->range ) ) {
			$this->range = ReportFilters::DEFAULT_DATES_RANGE_TYPE;
		}

		$this->datesPeriod = $this->getDatesPeriod();

		list( $periodStartDate, $periodEndDate ) = $this->getStartEndDates( $this->datesPeriod );
		$this->setDateFrom( $periodStartDate->format( 'Y-m-d' ) );
		$this->setDateTo( $periodEndDate->format( 'Y-m-d' ) );
	}

	/**
	 *
	 * @return \DatePeriod
	 */
	public function getDatesPeriod() {
		$today = new \DateTime( 'now', new \DateTimeZone( wp_timezone_string() ) );
		$range = $this->getRange();

		switch ( $range ) {
			case 'today':
				$period = $this->getDayPeriod( $today );
				break;
			case 'yesterday':
				$period = $this->getDayPeriod( DateUtils::cloneModify( $today, '-1 day' ) );
				break;
			case 'this_week':
				$period = $this->getWeekPeriod( $today );
				break;
			case 'last_week':
				$period = $this->getWeekPeriod( DateUtils::cloneModify( $today, '-7 days' ) );
				break;
			case 'last_thirty_days':
				$period = $this->getThirtyDaysPeriod( $today );
				break;
			case 'this_month':
				$period = $this->getMonthPeriod( $today );
				break;
			case 'last_month':
				$period = $this->getMonthPeriod( DateUtils::cloneModify( $today, 'last day of last month' ) );
				break;
			case 'this_quarter':
				$period = $this->getThisQuarterPeriod( $today );
				break;
			case 'last_quarter':
				$period = $this->getLastQuarterPeriod( $today );
				break;
			case 'this_year':
				$period = $this->getThisYearPeriod( $today );
				break;
			case 'last_year':
				$period = $this->getLastYearPeriod( $today );
				break;
			case 'custom':
				$period = $this->getCustomPeriod();
				break;
		}

		return $period;
	}

	/**
	 * @param \DatePeriod $period
	 *
	 * @return array [\DateTime StartDate, \DateTime EndDate]
	 */
	protected function getStartEndDates( \DatePeriod $period ) {
		$dates[] = $period->getStartDate();
		$dates[] = $period->getEndDate();

		return $dates;
	}

	/**
	 * @param \DateTime $baseDate
	 *
	 * @return \DatePeriod
	 */
	protected function getDayPeriod( \DateTime $baseDate ) {
		return DateUtils::createDayPeriod( $baseDate );
	}

	/**
	 * @param \DateTime $baseDate
	 *
	 * @return \DatePeriod
	 */
	protected function getWeekPeriod( \DateTime $baseDate ) {
		return DateUtils::createWeekPeriod( $baseDate );
	}

	protected function getThirtyDaysPeriod( \DateTime $baseDate ) {
		return DateUtils::createThirtyDaysPeriod( $baseDate );
	}

	protected function getMonthPeriod( \DateTime $baseDate ) {
		return DateUtils::createMonthPeriod( $baseDate );
	}

	protected function getThisQuarterPeriod( \DateTime $baseDate ) {
		$period = DateUtils::createQuarterPeriod( 0, $baseDate );

		return $period;
	}

	protected function getLastQuarterPeriod( \DateTime $baseDate ) {
		$datesArray = DateUtils::extractDayMonthYear( $baseDate );

		$lastQuarterBaseMonth = $datesArray[1] - 3;
		$lastQuarterBaseYear  = $datesArray[2];

		if ( $lastQuarterBaseMonth <= 0 ) {
			$lastQuarterBaseMonth = 12;
			$lastQuarterBaseYear  = $datesArray[2] - 1;
		}

		$lastQuarterBaseDate = new \DateTime( '1-' . $lastQuarterBaseMonth . '-' . $lastQuarterBaseYear );

		$period = DateUtils::createQuarterPeriod( 0, $lastQuarterBaseDate );

		return $period;
	}

	protected function getThisYearPeriod( \DateTime $baseDate ) {
		return DateUtils::createYearPeriod( $baseDate );
	}

	protected function getLastYearPeriod( \DateTime $baseDate ) {
		$datesArray = DateUtils::extractDayMonthYear( $baseDate );
		$lastYear   = $datesArray[2] - 1;

		$lastYearBaseDate = new \DateTime( '1-1-' . $lastYear );

		return DateUtils::createYearPeriod( $lastYearBaseDate );
	}

	protected function getCustomPeriod() {
		if ( ! empty( $this->atts['date_to'] ) && strtotime( $this->atts['date_to'] ) ) {
			$endDate = new \DateTime( $this->atts['date_to'] );
		} else {
			$endDate = new \DateTime( date( 'Y-m-d' ) );
		}

		if ( ! empty( $this->atts['date_from'] ) && strtotime( $this->atts['date_from'] ) ) {
			$startDate = new \DateTime( $this->atts['date_from'] );
		} else {
			$startDate = DateUtils::cloneModify( $endDate, '-45 days' );
		}

		$dateInterval = new \DateInterval( 'P1D' );

		$daysDiff = DateUtils::calcNights( $startDate, $endDate );

		if ( $daysDiff <= 1 ) { // Not less than 1 day
			$endDate = DateUtils::cloneModify( $startDate, '+2 days' );
		}

		return new \DatePeriod( $startDate, $dateInterval, $endDate );
	}

	/**
	 *
	 * @return array
	 */
	public function getDatesArray() {
		$datesArray                  = array();
		$range                       = $this->getRange();
		$period                      = $this->getDatesPeriod();
		list( $startDate, $endDate ) = $this->getStartEndDates( $period );

		foreach ( $period as $ind => $date ) {
			if ( in_array( $range, array( 'this_year', 'last_year' ) ) ) {
				$date = $date->format( 'Y-m' );
			} else {
				$date = DateUtils::formatDateTimeDB( $date );
			}
			array_push( $datesArray, $date );
		}

		if ( in_array( $range, array( 'this_year', 'last_year' ) ) ) {
			$dateTo = date( 'Y-m', strtotime( $this->getDateTo() ) );
		} else {
			$dateTo = $this->getDateTo();
		}

		array_push( $datesArray, $dateTo );

		return $datesArray;
	}

	/**
	 *
	 * @param string $dateFrom Y-m-d
	 */
	public function setDateFrom( $dateFrom ) {
		if ( strtotime( $dateFrom ) ) {
			$this->dateFrom = date( 'Y-m-d', strtotime( $dateFrom ) );
		}
	}

	/**
	 *
	 * @param string $dateTo Y-m-d
	 */
	public function setDateTo( $dateTo ) {
		if ( strtotime( $dateTo ) ) {
			$this->dateTo = date( 'Y-m-d', strtotime( $dateTo ) );
		}
	}

	/**
	 *
	 * @return string
	 */
	public function getDateFrom() {
		return $this->dateFrom;
	}

	/**
	 *
	 * @return string
	 */
	public function getDateTo() {
		return $this->dateTo;
	}
}


