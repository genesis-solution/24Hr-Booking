<?php

namespace MPHB\TaxesAndFees;

/**
 *
 * @since 3.9.8
 */
class TaxesAndFees {

	/**
	 * @var int
	 */
	public $roomTypeId;

	/**
	 * @var \MPHB\Entities\RoomType
	 */
	public $roomType;

	/**
	 * @var float
	 */
	public $roomPrice;

	/**
	 * @var float
	 */
	public $taxExcludedTotal;

	/**
	 * @var float
	 */
	public $taxIncludedTotal;

	/**
	 * @var array
	 */
	public $taxes;

	/**
	 * @var array
	 */
	public $fees;

	/**
	 * @var array
	 */
	public $feeTaxes;

	private $searchParameters = array();

	private $atts = array();


	public function __construct() {

		$this->roomTypeId       = null;
		$this->roomType         = null;
		$this->roomPrice        = 0;
		$this->taxExcludedTotal = 0;
		$this->taxIncludedTotal = 0;
		$this->taxes            = array();
		$this->fees             = array();
		$this->feeTaxes         = array();
	}

	/**
	 * @param \MPHB\Entities\RoomType $roomType Optional.
	 */
	public function setRoomType( $roomType = null ) {
		if ( is_null( $roomType ) ) {
			$roomType = MPHB()->getCurrentRoomType();
		} elseif ( is_int( $roomType ) ) {
			$roomType = mphb_get_room_type( $roomType );
		}
		$this->roomType = $roomType;

		$this->roomTypeId = $roomType->getOriginalId();

		$this->afterSetRoomType();
	}

	private function afterSetRoomType() {
		$this->setTaxesAndFees();
	}

	private function setTaxesAndFees() {
		$this->taxes    = MPHB()->settings()->taxesAndFees()->getAccommodationTaxes( $this->roomTypeId );
		$this->fees     = MPHB()->settings()->taxesAndFees()->getFees( $this->roomTypeId );
		$this->feeTaxes = MPHB()->settings()->taxesAndFees()->getFeeTaxes();
	}

	/**
	 * @param float $roomPrice
	 */
	public function setRoomPrice( $roomPrice ) {
		$this->roomPrice = $roomPrice;
	}

	/**
	 * @param array $atts Optional. Array of attributes to calculate taxes and fees.
	 */
	public function setupParams( $atts = array() ) {

		$this->searchParameters = MPHB()->getSession()->get( 'mphb_search_parameters' );

		$this->setAtts( $atts );

		$this->afterSetupParams();
	}

	/**
	 * @param array $atts Optional.
	 */
	private function setAtts( $atts = array() ) {

		$this->atts['defined']               = isset( $atts['defined'] ) ? (bool) $atts['defined'] : true;
		$this->atts['period_nights']         = isset( $atts['period_nights'] ) ? (int) $atts['period_nights'] : 1;
		$this->atts['accommodations_amount'] = isset( $atts['accommodations_amount'] ) ? (int) $atts['accommodations_amount'] : 1;

		$this->atts['adults_amount']   = isset( $this->searchParameters['mphb_adults'] ) && $this->searchParameters['mphb_adults'] > 0
					? (int) $this->searchParameters['mphb_adults'] : 1;
		$this->atts['children_amount'] = isset( $this->searchParameters['mphb_children'] ) && $this->searchParameters['mphb_children'] > 0
					? (int) $this->searchParameters['mphb_children'] : 0;

		if ( isset( $atts['adults_amount'] ) && $atts['adults_amount'] > 0 ) {
			$this->atts['adults_amount'] = (int) $atts['adults_amount'];
		} elseif ( isset( $atts['guests_amount'] ) && $atts['guests_amount'] > 0 ) {
			$this->atts['adults_amount'] = (int) $atts['guests_amount'];
		} elseif ( $this->roomType->getAdultsCapacity() > 0 ) {
			$this->atts['adults_amount'] = min( $this->atts['adults_amount'], (int) $this->roomType->getAdultsCapacity() * $this->atts['accommodations_amount'] );
		} elseif ( $this->roomType->getTotalCapacity() > 0 ) {
			$this->atts['adults_amount'] = min( $this->atts['adults_amount'], (int) $this->roomType->getTotalCapacity() * $this->atts['accommodations_amount'] );
		}

		if ( isset( $atts['children_amount'] ) && $atts['children_amount'] > 0 ) {
			$this->atts['children_amount'] = (int) $atts['children_amount'];
		} elseif ( $this->roomType->getChildrenCapacity() > 0 ) {
			$this->atts['children_amount'] = min( $this->atts['children_amount'], (int) $this->roomType->getChildrenCapacity() * $this->atts['accommodations_amount'] );
		}
	}

	/**
	 * @param bool $defined Optional.
	 */
	public function setDefined( $defined = true ) {
		$this->atts['defined'] = $defined;
	}

	private function afterSetupParams() {
		if ( $this->areTaxesAndFeesDefined() ) {
			$this->calcTaxes();
			$this->calcFees();
		}
	}

	private function calcTaxes() {
		if ( ! empty( $this->taxes ) ) {
			foreach ( $this->taxes as $tax ) {
				$taxPrice = 0;

				switch ( $tax['type'] ) {
					case 'per_guest_per_day':
						$taxPrice = $this->atts['adults_amount'] * $tax['amount']['adults'] + $this->atts['children_amount'] * $tax['amount']['children'];
						if ( $tax['limit'] == 0 ) {
							$taxPrice *= $this->atts['period_nights'];
						} else {
							$taxPrice *= min( $tax['limit'], $this->atts['period_nights'] );
						}
						break;

					case 'per_room_per_day':
						$taxPrice = $this->atts['accommodations_amount'] * $tax['amount'];
						if ( $tax['limit'] == 0 ) {
							$taxPrice *= $this->atts['period_nights'];
						} else {
							$taxPrice *= min( $tax['limit'], $this->atts['period_nights'] );
						}
						break;

					case 'per_room_percentage':
						$taxPrice = $this->roomPrice / 100 * $tax['amount'];
						break;
				}
				if ( $tax['included'] ) {
					$this->taxIncludedTotal += $taxPrice;
				} else {
					$this->taxExcludedTotal += $taxPrice;
				}
			}
		}
	}

	private function calcFees() {

		if ( ! empty( $this->fees ) ) {
			foreach ( $this->fees as $fee ) {

				$feePrice = 0;

				switch ( $fee['type'] ) {

					case 'per_guest_per_day':
						$feePrice = $this->atts['adults_amount'] * $fee['amount']['adults'] + $this->atts['children_amount'] * $fee['amount']['children'];

						if ( $fee['limit'] == 0 ) {
							$feePrice *= $this->atts['period_nights'];
						} else {
							$feePrice *= min( $fee['limit'], $this->atts['period_nights'] );
						}
						break;

					case 'per_room_per_day':
						$feePrice = $this->atts['accommodations_amount'] * $fee['amount'];

						if ( $fee['limit'] == 0 ) {
							$feePrice *= $this->atts['period_nights'];
						} else {
							$feePrice *= min( $fee['limit'], $this->atts['period_nights'] );
						}
						break;

					case 'per_room_percentage':
						$feePrice = $this->roomPrice / 100 * $fee['amount'];
						break;
				}
				if ( ! empty( $this->feeTaxes ) ) {
					foreach ( $this->feeTaxes as $feeTax ) {
						$feePrice += $feePrice / 100 * $feeTax['amount'];
					}
				}
				if ( $fee['included'] ) {
					$this->taxIncludedTotal += $feePrice;
				} else {
					$this->taxExcludedTotal += $feePrice;
				}
			}
		}
	}

	/**
	 * @return bool
	 */
	public function areTaxesAndFeesDefined() {
		return (bool) null !== $this->roomType
			&& $this->roomPrice
			&& $this->atts['defined'];
	}

	/**
	 * @return float
	 */
	public function getIncludedTaxesAndFees() {
		return $this->taxIncludedTotal;
	}

	/**
	 * @return float
	 */
	public function getExcludedTaxesAndFees() {
		return $this->taxExcludedTotal;
	}

	/**
	 * @return float
	 */
	public function getPriceWithTaxesAndFees() {
		return $this->roomPrice + $this->taxIncludedTotal;
	}

	/**
	 * @return bool
	 */
	public function hasTaxesAndFees() {
		return (bool) ! empty( $this->taxes ) || ! empty( $this->fees );
	}

	/**
	 * @return bool
	 */
	public function hasIncludedTaxesAndFees() {
		return (bool) $this->taxIncludedTotal > 0;
	}

	/**
	 * @return bool
	 */
	public function hasExcludedTaxesAndFees() {
		return (bool) $this->taxExcludedTotal > 0;
	}

	/**
	 * @return array
	 */
	public function getAllTaxesForRoom() {
		return $this->taxes;
	}

	/**
	 * @return array
	 */
	public function getAllFeesForRoom() {
		return $this->fees;
	}

	/**
	 * @return array
	 */
	public function getAllFeeTaxesForRoom() {
		return $this->feeTaxes;
	}
}


