<?php

namespace MPHB\Entities;

/**
 *
 * @param array $atts
 * @param int $atts['id'] Id of season
 * @param string $atts['title'] Title of season
 * @param string $atts['description'] Description of season
 * @param DateTime $atts['start_date'] Start Date of season
 * @param DateTime $atts['end_date'] End Date of season
 * @param array $atts['days'] Days of season
 */
class Season {

	/**
	 *
	 * @var int
	 */
	private $id;

	/**
	 *
	 * @var string
	 */
	private $title;

	/**
	 *
	 * @var string
	 */
	private $description;

	/**
	 *
	 * @var \DateTime
	 */
	private $startDate;

	/**
	 *
	 * @var \DateTime
	 */
	private $endDate;

	/**
	 *
	 * @var array
	 */
	private $days = array();

	/**
	 *
	 * @var \DateTime[]
	 */
	private $dates = array();

	public function __construct( $atts ) {
		$this->id          = $atts['id'];
		$this->title       = $atts['title'];
		$this->description = $atts['description'];
		$this->startDate   = $atts['start_date'];
		$this->endDate     = $atts['end_date'];
		$this->days        = array_map( '\MPHB\Utils\CastUtils::toInt', $atts['days'] );
		$this->setupDates();
	}

	private function setupDates() {

		$dates = array();

		if ( ! is_null( $this->startDate ) && ! is_null( $this->endDate ) ) {
			$datePeriod = \MPHB\Utils\DateUtils::createDatePeriod( $this->startDate, $this->endDate, true );
			$dates      = iterator_to_array( $datePeriod );

			// remove not allowed week days from period
			$dates = array_filter( $dates, array( $this, 'isAllowedWeekDay' ) );
		}

		$this->dates = $dates;
	}

	/**
	 * @param \DateTime $date
	 *
	 * @return bool
	 */
	public function isDateInSeason( $date ) {
		return $date >= $this->startDate && $date <= $this->endDate && $this->isAllowedWeekDay( $date );
	}

	/**
	 *
	 * @param \DateTime $date
	 *
	 * @return bool
	 */
	public function isAllowedWeekDay( $date ) {
		$weekDay = $date->format( 'w' );
		return in_array( $weekDay, $this->days );
	}

	/**
	 *
	 * @return int
	 */
	function getId() {
		return $this->id;
	}

	/**
	 *
	 * @return string
	 */
	function getTitle() {
		return $this->title;
	}

	/**
	 *
	 * @return \DateTime
	 */
	function getDescription() {
		return $this->description;
	}

	/**
	 *
	 * @return \DateTime|null
	 */
	function getStartDate() {
		return $this->startDate;
	}

	/**
	 *
	 * @return \DateTime|null
	 */
	function getEndDate() {
		return $this->endDate;
	}

	/**
	 *
	 * @return array
	 */
	public function getDays() {
		return $this->days;
	}

	/**
	 *
	 * @return \DateTime[]
	 */
	function getDates() {
		return $this->dates;
	}

}
