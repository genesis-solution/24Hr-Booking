<?php

namespace MPHB\Settings;

use \MPHB\Bundles;

class UnitsSettings {

	/**
	 *
	 * @var \MPHB\Bundles\UnitsBundle
	 */
	private $bundle;

	private $squareUnitDefault = 'm2';

	public function __construct() {
		$this->bundle = new Bundles\UnitsBundle();
	}

	/**
	 *
	 * @return string
	 */
	public function getSquareUnit() {
		$squareUnitKey = get_option( 'mphb_square_unit', $this->squareUnitDefault );
		return $this->bundle->getSymbol( $squareUnitKey );
	}

	/**
	 *
	 * @return \MPHB\Bundles\UnitsBundle
	 */
	public function getBundle() {
		return $this->bundle;
	}

}
