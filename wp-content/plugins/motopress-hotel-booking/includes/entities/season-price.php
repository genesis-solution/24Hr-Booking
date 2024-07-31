<?php

namespace MPHB\Entities;

class SeasonPrice {

	/**
	 *
	 * @var int
	 */
	private $id;

	/**
	 *
	 * @var int
	 */
	private $seasonId;

	/**
	 *
	 * @var array
	 */
	private $stockPrices;

	/**
	 *
	 * @var float
	 */
	private $basePrice;

	/**
	 *
	 * @var array [%Period length% => %Price%], where price can be float number
	 * or empty string.
	 */
	private $basePrices;

	/**
	 *
	 * @var bool
	 */
	private $enableVariations;

	/**
	 *
	 * @var array
	 */
	private $variations;

	/**
	 *
	 * @param array $atts
	 * @param int   $atts['id']
	 * @param int   $atts['season_id']
	 * @param float $atts['price']
	 */
	protected function __construct( $atts = array() ) {
		$this->id               = $atts['id'];
		$this->seasonId         = $atts['season_id'];
		$this->stockPrices      = $atts['price'];
		$this->basePrice        = (float) reset( $atts['price']['prices'] );
		$this->basePrices       = $atts['price']['prices'];
		$this->enableVariations = $atts['price']['enable_variations'];
		$this->variations       = $atts['price']['variations'];

		// Combine periods with prices to make it easier to search the price by period
		$periods          = $this->stockPrices['periods'];
		$this->basePrices = array_combine( $periods, $this->basePrices );
		foreach ( $this->variations as &$variation ) {
			$variation['prices'] = array_combine( $periods, $variation['prices'] );
		}
		unset( $variation );
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
	 * @return int
	 */
	function getSeasonId() {
		return $this->seasonId;
	}

	/**
	 *
	 * @return \MPHB\Entities\Season|null
	 */
	function getSeason() {
		return MPHB()->getSeasonRepository()->findById( $this->seasonId );
	}

	/**
	 * @since 3.5.0
	 */
	protected function getVariation( $adults, $children ) {
		$prices = $this->basePrices;

		if ( ! $this->enableVariations
			|| MPHB()->reservationRequest()->getPricingStrategy() == 'base-price'
			|| ! MPHB()->settings()->main()->isAdultsAllowed() // Adults and children disabled
		) {
			return $prices;
		}

		foreach ( $this->variations as $variation ) {
			// Use == for comparisons for case 0 == ""
			if ( $adults == $variation['adults'] && $children == $variation['children'] ) {
				$prices = $variation['prices'];
				break;
			}
		}

		return $prices;
	}

	/**
	 * @param array $prices [1 => 20, 5 => 15, 14 => 10]
	 * @param int   $nightsCount [-1; oo).
	 * @return float
	 *
	 * @since 3.5.0
	 */
	protected function getPriceForPeriod( $prices, $nightsCount ) {
		$price = $this->basePrice;

		if ( $nightsCount > 0 ) {
			// Period length for base prices is 1, so we'll apply variation base
			// price on first iteration (and rewrite global base price)
			$appliedPeriod = 0;

			// Search for proper period
			foreach ( $prices as $periodLength => $periodPrice ) {
				if ( $nightsCount >= $periodLength && $periodLength > $appliedPeriod ) {
					if ( ! empty( $periodPrice ) ) { // "", "0", 0
						$price         = (float) $periodPrice;
						$appliedPeriod = $periodLength;
					}
				}
			}
		} else {
			// Try to get nightly price (1 night)
			if ( isset( $prices[1] ) && ! empty( $prices[1] ) ) {
				$price = (float) $prices[1];
			}
		}

		return $price;
	}

	/**
	 * @return float Base or variation price.
	 *
	 * @since 3.5.0 removed optional parameter $occupancyParams.
	 */
	function getPrice() {
		$price = $this->basePrice;

		$adults      = MPHB()->reservationRequest()->getAdults();
		$children    = MPHB()->reservationRequest()->getChildren();
		$nightsCount = MPHB()->reservationRequest()->getNightsCount();

		$prices = $this->getVariation( $adults, $children );
		$price  = $this->getPriceForPeriod( $prices, $nightsCount );

		return $price;
	}

	/**
	 *
	 * @return array
	 */
	function getPricesAndVariations() {
		return $this->stockPrices;
	}

	/**
	 * @return array
	 *
	 * @since 3.5.0 removed optional parameter $occupancyParams.
	 */
	function getDatePrices() {
		$season = $this->getSeason();
		if ( ! $season ) {
			return array();
		}

		$dates = $season->getDates();
		$dates = array_map( array( '\MPHB\Utils\DateUtils', 'formatDateDB' ), $dates );

		$price = $this->getPrice();

		$datePrices = array_fill_keys( $dates, $price );
		return $datePrices;
	}

	/**
	 *
	 * @param array $atts
	 * @param int   $atts['id']
	 * @param int   $atts['season_id']
	 * @param float $atts['price']
	 * @return SeasonPrice|null
	 */
	public static function create( $atts ) {

		if ( ! isset( $atts['id'], $atts['price'], $atts['season_id'] ) ) {
			return null;
		}

		$atts['id']        = (int) $atts['id'];
		$atts['season_id'] = (int) $atts['season_id'];
		$atts['price']     = mphb_normilize_season_price( $atts['price'] );

		if ( $atts['id'] < 0 ) {
			return null;
		}

		if ( count( $atts['price']['prices'] ) <= 0 || $atts['price']['prices'][0] < 0 ) {
			return null;
		}

		if ( ! MPHB()->getSeasonRepository()->findById( $atts['season_id'] ) ) {
			return null;
		}

		return new self( $atts );
	}

}
