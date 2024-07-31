<?php

namespace MPHB\Settings;

class CurrencySettings {

	private $defaultCurrency = 'USD';
	private $defaultPosition = 'before';

	public function getDefaultCurrency() {
		return $this->defaultCurrency;
	}

	public function getDefaultPosition() {
		return $this->defaultPosition;
	}

	/**
	 *
	 * @var \MPHB\Bundles\CurrencyBundle
	 */
	private $bundle;

	public function __construct() {
		$this->bundle = new \MPHB\Bundles\CurrencyBundle();
	}

	/**
	 * Retrieve ISO 4217 code of currency
	 *
	 * @return string
	 */
	public function getCurrencyCode() {
		$currencyCode = get_option( 'mphb_currency_symbol', $this->defaultCurrency );
		return $currencyCode;
	}

	/**
	 * Retrieve human readable currency symbol
	 *
	 * @return string
	 */
	public function getCurrencySymbol() {
		return $this->bundle->getSymbol( $this->getCurrencyCode() );
	}

	/**
	 *  Return currency position.
	 *
	 * @return string
	 */
	public function getCurrencyPosition() {
		$currencyPosition = get_option( 'mphb_currency_position', $this->defaultPosition );
		return $currencyPosition;
	}

	/**
	 *
	 * @param string $currencySymbol
	 * @param string $currencyPosition Possible values: after, before, after_space, before_space
	 * @param bool   $asHtml Generate HTML instead of plain text
	 * @return string
	 */
	public function getPriceFormat( $currencySymbol = null, $currencyPosition = null, $asHtml = true ) {

		if ( is_null( $currencySymbol ) ) {
			$currencySymbol = $this->getCurrencySymbol();
		}

		if ( is_null( $currencyPosition ) ) {
			$currencyPosition = $this->getCurrencyPosition();
		}

		$currencySpan = $asHtml ? '<span class="mphb-currency">' . $currencySymbol . '</span>' : $currencySymbol;

		switch ( $currencyPosition ) {
			case 'after':
				$format = '%s' . $currencySpan;
				break;
			case 'before_space':
				$format = $currencySpan . '&nbsp;%s';
				break;
			case 'after_space':
				$format = '%s&nbsp;' . $currencySpan;
				break;
			case 'before':
				$format = $currencySpan . '%s';
				break;
		}

		return $format;
	}

	/**
	 *
	 * @return string
	 */
	public function getPriceDecimalsSeparator() {
		$separator = get_option( 'mphb_decimals_separator', '.' );
		return $separator;
	}

	/**
	 *
	 * @return string
	 */
	public function getPriceThousandSeparator() {
		$separator = get_option( 'mphb_thousand_separator', ',' );
		return $separator;
	}

	/**
	 *
	 * @return int
	 */
	public function getPriceDecimalsCount() {
		$count = get_option( 'mphb_decimal_count', 2 );
		return intval( $count );
	}

	/**
	 *
	 * @return \MPHB\Bundles\CurrencyBundle
	 */
	public function getBundle() {
		return $this->bundle;
	}

}
