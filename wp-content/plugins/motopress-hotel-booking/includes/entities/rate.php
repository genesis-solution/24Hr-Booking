<?php

namespace MPHB\Entities;

class Rate {

	/**
	 *
	 * @var int
	 */
	private $id;

	/**
	 *
	 * @var int
	 */
	private $originalId;

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
	 * @var int
	 */
	private $roomTypeId;

	/**
	 *
	 * @var SeasonPrice[]
	 */
	private $seasonPrices;

	/**
	 *
	 * @var bool
	 */
	private $active = false;

	/**
	 * Available dates (with base price as value).
	 *
	 * @var array
	 */
	private $dates = array();

	/**
	 *
	 * @param array         $atts Array of atts
	 * @param int           $atts['id'] Id of rate
	 * @param string        $atts['title'] Title of rate
	 * @param string        $atts['description'] Description of rate
	 * @param int           $atts['room_type_id'] Room Type ID
	 * @param SeasonPrice[] $atts['season_prices'] Array of Season Prices.
	 * @param bool          $atts['active'] Is rate available for user choosing.
	 */
	function __construct( $atts ) {
		$this->id           = $atts['id'];
		$this->originalId   = MPHB()->translation()->getOriginalId( $this->id, MPHB()->postTypes()->rate()->getPostType() );
		$this->title        = $atts['title'];
		$this->description  = $atts['description'];
		$this->roomTypeId   = $atts['room_type_id'];
		$this->seasonPrices = array_reverse( $atts['season_prices'] );
		$this->active       = $atts['active'];
		$this->dates        = $this->getDatePrices();
	}

	/**
	 *
	 * @return int Id of rate
	 */
	public function getId() {
		return $this->id;
	}

	/**
	 * @return int
	 */
	public function getOriginalId() {
		return $this->originalId;
	}

	/**
	 *
	 * @return string Title
	 */
	public function getTitle() {
		return $this->title;
	}

	/**
	 *
	 * @return string Description
	 */
	public function getDescription() {
		return $this->description;
	}

	/**
	 *
	 * @return SeasonPrice[] Array of season prices.
	 */
	public function getSeasonPrices() {
		return $this->seasonPrices;
	}

	/**
	 *
	 * @return int
	 */
	public function getRoomTypeId() {
		return $this->roomTypeId;
	}

	/**
	 *
	 * @param \DateTime $dateFrom
	 * @param \DateTime $dateTo
	 * @return bool
	 */
	public function isAvailableForDates( \DateTime $dateFrom, \DateTime $dateTo, $includeLastDate = false ) {

		$requestedPeriod = \MPHB\Utils\DateUtils::createDatePeriod( $dateFrom, $dateTo, $includeLastDate );

		$requestedDates   = array_map( array( '\MPHB\Utils\DateUtils', 'formatDateDB' ), iterator_to_array( $requestedPeriod ) );
		$availableDates   = array_keys( $this->dates );
		$unavailableDates = array_diff( $requestedDates, $availableDates );

		return empty( $unavailableDates );
	}

	/**
	 *
	 * @param \DateTime $fromDate
	 * @return bool
	 */
	public function isExistsFrom( $fromDate ) {
		$isExists = false;

		$fromDateFormatted = $fromDate->format( 'Y-m-d' );

		foreach ( array_keys( $this->dates ) as $date ) {
			if ( $date > $fromDateFormatted ) {
				$isExists = true;
				break;
			}
		}
		return $isExists;
	}

	/**
	 * @return array
	 *
	 * @since 3.5.0 removed optional parameter $occupancyParams.
	 */
	public function getDatePrices() {
		$datePrices = array();
		foreach ( $this->seasonPrices as $seasonPrice ) {
			$datePrices = array_merge( $datePrices, $seasonPrice->getDatePrices() );
		}
		return $datePrices;
	}

	/**
	 *
	 * @return bool
	 */
	public function isActive() {
		return $this->active;
	}

	/**
	 *
	 * @return Season[]
	 */
	public function getSeasons() {
		$seasons = array_map(
			function( SeasonPrice $seasonPrice ) {
				return $seasonPrice->getSeason();
			},
			$this->seasonPrices
		);
		return array_filter( $seasons );
	}

	/**
	 *
	 * @param \DateTime $fromDate
	 * @param \DateTime $toDate
	 * @return float
	 */
	public function getMinBasePrice( $fromDate = null, $toDate = null ) {
		$useFilter = false;

		if ( $fromDate && is_a( $fromDate, '\DateTime' ) ) {
			$useFilter = true;
			$fromDate  = $fromDate->format( 'Y-m-d' );
		}

		if ( $toDate && is_a( $toDate, '\DateTime' ) ) {
			$useFilter = true;
			$toDate    = $toDate->format( 'Y-m-d' );
		}

		if ( $useFilter ) {
			$datePrices = array();
			foreach ( $this->dates as $date => $price ) {
				if ( $fromDate && $date < $fromDate ) {
					continue;
				}
				if ( $toDate && $date > $toDate ) {
					continue;
				}
				$datePrices[ $date ] = $price;
			}
		} else {
			$datePrices = $this->dates;
		}

		return ! empty( $datePrices ) ? min( $datePrices ) : 0.0;
	}

	/**
	 * @param \DateTime $checkInDate
	 * @param \DateTime $checkOutDate
	 * @return float
	 *
	 * @since 3.5.0 removed optional parameter $occupancyParams.
	 */
	public function calcPrice( \DateTime $checkInDate, \DateTime $checkOutDate ) {
		return (float) array_sum( $this->getPriceBreakdown( $checkInDate, $checkOutDate ) );
	}

	/**
	 * @param string $checkInDate date in format 'Y-m-d'
	 * @param string $checkOutDate date in format 'Y-m-d'
	 * @return array Array where keys are dates and values are prices
	 *
	 * @since 3.5.0 removed optional parameter $occupancyParams.
	 */
	public function getPriceBreakdown( $checkInDate, $checkOutDate ) {

		$prices = array();

		$datePrices = $this->getDatePrices();

		foreach ( \MPHB\Utils\DateUtils::createDatePeriod( $checkInDate, $checkOutDate ) as $date ) {
			$dateDB = \MPHB\Utils\DateUtils::formatDateDB( $date );
			if ( array_key_exists( $dateDB, $datePrices ) ) {
				$prices[ $dateDB ] = $datePrices[ $dateDB ];
			}
		}

		return $prices;
	}

}
