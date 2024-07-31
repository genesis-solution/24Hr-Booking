<?php

namespace MPHB;

use MPHB\Utils\DateUtils;

/**
 * @since 3.5.0
 */
class ReservationRequest {

	protected $defaults = array(
		'adults'           => '',       // "" or integer number
		'children'         => '',       // "" or integer number
		'check_in_date'    => null,     // null or DateTime object
		'check_out_date'   => null,     // null or DateTime object
		'nights_count'     => -1,       // -1, 0 or natural number
		'pricing_strategy' => 'default', // default|base-price
	);

	protected $custom = array();

	public function setupParameter( $parameter, $value ) {
		$this->custom[ $parameter ] = $value;
	}

	/**
	 * @param array $parameters [%parameter name% => %value%]
	 */
	public function setupParameters( $parameters ) {
		$this->custom = array_merge( $this->custom, $parameters );

		if ( isset( $parameters['check_in_date'], $parameters['check_out_date'] ) && ! isset( $parameters['nights_count'] ) ) {
			$this->custom['nights_count'] = DateUtils::calcNights( $parameters['check_in_date'], $parameters['check_out_date'] );
		}
	}

	/**
	 * @return self
	 */
	public function setupSearchParameters() {
		// Set default values to override previous custom values
		$parameters = array(
			'adults'         => $this->defaults['adults'],
			'children'       => $this->defaults['children'],
			'check_in_date'  => $this->defaults['check_in_date'],
			'check_out_date' => $this->defaults['check_out_date'],
		);

		$search = MPHB()->searchParametersStorage()->get();

		if ( is_numeric( $search['mphb_adults'] ) ) {
			$parameters['adults'] = intval( $search['mphb_adults'] );
		}

		if ( is_numeric( $search['mphb_children'] ) ) {
			$parameters['children'] = intval( $search['mphb_children'] );
		}

		if ( ! empty( $search['mphb_check_in_date'] ) && ! empty( $search['mphb_check_out_date'] ) ) {
			$dateFormat = MPHB()->settings()->dateTime()->getDateTransferFormat();

			$parameters['check_in_date']  = \DateTime::createFromFormat( $dateFormat, $search['mphb_check_in_date'] );
			$parameters['check_out_date'] = \DateTime::createFromFormat( $dateFormat, $search['mphb_check_out_date'] );
		}

		$this->setupParameters( $parameters );

		return $this;
	}

	/**
	 * @param string[] $parameters The list of parameters to reset.
	 * @return self
	 */
	public function resetDefaults( $parameters = array() ) {
		if ( empty( $parameters ) ) {
			$this->custom = array();
		} else {
			$this->custom = array_diff_key( $this->custom, array_flip( $parameters ) );
		}

		return $this;
	}

	public function getParameters() {
		return array_merge( $this->defaults, $this->custom );
	}

	public function getParameter( $parameter ) {
		if ( isset( $this->custom[ $parameter ] ) ) {
			return $this->custom[ $parameter ];
		} else {
			return $this->defaults[ $parameter ];
		}
	}

	/**
	 * @return int|string Adults count or empty string "".
	 */
	public function getAdults() {
		return $this->getParameter( 'adults' );
	}

	/**
	 * @return int|string Children count or empty string "".
	 */
	public function getChildren() {
		return $this->getParameter( 'children' );
	}

	/**
	 * @return null|\DateTime
	 */
	public function getCheckInDate() {
		return $this->getParameter( 'check_in_date' );
	}

	/**
	 * @return null|\DateTime
	 */
	public function getCheckOutDate() {
		return $this->getParameter( 'check_out_date' );
	}

	/**
	 * @return int [-1; oo)
	 */
	public function getNightsCount() {
		return (int) $this->getParameter( 'nights_count' );
	}

	/**
	 * @return string "default"|"base-price"
	 */
	public function getPricingStrategy() {
		return $this->getParameter( 'pricing_strategy' );
	}
}
