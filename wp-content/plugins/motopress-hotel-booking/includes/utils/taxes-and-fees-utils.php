<?php

namespace MPHB\Utils;

/**
 *
 * @since 3.9.8
 */
class TaxesAndFeesUtils {

	/**
	 * @param bool $paragraph Optional. Whether use <p> or <span> tag.
	 */
	public static function textTaxesAndFeesUndefined( $paragraph = true ) {
		$output = '';

		$output .= $paragraph ? '<p class="mphb-tax-information taxes-included">' : '<span class="mphb-tax-information taxes-included">';
		/**
		 * @since 3.9.8
		 *
		 * @param string Text about taxes and fees
		 * @param string ( before text
		 * @param string ) after text
		 */
		$output .= apply_filters(
			'mphb_text_taxes_and_fees_undefined',
			_x( ' (+taxes and fees)', 'Text about taxes and fees below the price.', 'motopress-hotel-booking' ),
			'(',
			')'
		);
		$output .= $paragraph ? '</p>' : '</span>';

		return $output;
	}

	/**
	 * @param float $taxesExcluded
	 * @param bool  $paragraph Optional.
	 */
	public static function textTaxesAndFeesExcluded( $taxesExcluded, $paragraph = true ) {
		$output = '';

		$output .= $paragraph ? '<p class="mphb-tax-information taxes-excluded">' : '<span class="mphb-tax-information taxes-excluded">';
		/**
		 * @since 3.9.8
		 *
		 * @param string Texe about taxes and fees
		 * @param float $taxesExcluded
		 * @param string ( before text
		 * @param string ) after text
		 */
		$output .= apply_filters(
			'mphb_text_taxes_and_fees_excluded',
			// translators: %s is a tax value
			sprintf( _x( ' (+%s taxes and fees)', 'Text about taxes and fees below the price.', 'motopress-hotel-booking' ), mphb_format_price( $taxesExcluded ) ),
			$taxesExcluded,
			'(',
			')'
		);
		$output .= $paragraph ? '</p>' : '</span>';

		return $output;
	}

	/**
	 * @param bool $paragraph Optional.
	 */
	public static function textTaxesAndFeesIncluded( $paragraph = true ) {
		$output = '';

		$output .= $paragraph ? '<p class="mphb-tax-information taxes-included">' : '<span class="mphb-tax-information taxes-included">';
		/**
		 * @since 3.9.8
		 *
		 * @param string Texe about taxes and fees
		 * @param string ( before text
		 * @param string ) after text
		 */
		$output .= apply_filters(
			'mphb_text_taxes_and_fees_included',
			_x( ' (includes taxes and fees)', 'Text about taxes and fees below the price.', 'motopress-hotel-booking' ),
			'(',
			')'
		);
		$output .= $paragraph ? '</p>' : '</span>';

		return $output;
	}

}


