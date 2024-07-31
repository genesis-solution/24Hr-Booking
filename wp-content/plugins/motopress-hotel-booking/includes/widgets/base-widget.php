<?php

namespace MPHB\Widgets;

class BaseWidget extends \WP_Widget {

	/**
	 *
	 * @param string|int $value
	 * @param int|false  $min
	 * @param int|false  $max
	 * @return string Empty string for uncorrect value
	 */
	protected function sanitizeInt( $value, $min = false, $max = false ) {
		$value = absint( $value );
		return ( $min === false || $value >= $min ) && ( $max === false || $value <= $max ) ? (string) $value : '';
	}

	/**
	 *
	 * @param string $date
	 * @return string
	 */
	protected function sanitizeDate( $date, $inFormat = false, $outFormat = false ) {
		if ( $inFormat === false ) {
			$inFormat = MPHB()->settings()->dateTime()->getDateFormat();
		}
		if ( $outFormat === false ) {
			$outFormat = $inFormat;
		}
		$dateObj = \DateTime::createFromFormat( $inFormat, $date );
		return $dateObj ? $dateObj->format( $outFormat ) : '';
	}

	protected function sanitizeText( $value ) {
		return sanitize_text_field( $value );
	}

	public static function register() {
		register_widget( get_called_class() );
	}

	public static function init() {
		add_action( 'widgets_init', array( get_called_class(), 'register' ) );
	}

}
