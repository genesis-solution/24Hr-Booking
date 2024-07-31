<?php

namespace MPHB\Reports\Data;

abstract class AbstractReportsData {

	/**
	 * @var array
	 */
	public $atts;

	/**
	 * @var string|array
	 */
	public $range;

	/**
	 *
	 * @param array $atts
	 */
	public function __construct( $atts = array() ) {
		$this->atts  = $atts;
		$this->range = isset( $atts['range'] ) ? $atts['range'] : '';
	}

	/**
	 *
	 * @param string       $key
	 * @param string|array $value
	 */
	public function setAttr( $key, $value ) {
		$this->atts[ $key ] = $value;
	}

	/**
	 *
	 * @return array
	 */
	public function getAtts() {
		return $this->atts;
	}

	/**
	 *
	 * @param string $range
	 */
	public function setRange( $range ) {
		$this->range = $range;
	}

	/**
	 *
	 * @return string|array
	 */
	public function getRange() {
		return $this->range;
	}
}


