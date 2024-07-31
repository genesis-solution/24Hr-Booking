<?php

namespace MPHB\Shortcodes;

class AvailabilityCalendarShortcode extends AbstractShortcode {

	protected $name = 'mphb_availability_calendar';

	/**
	 * @param array  $atts
	 * @param null   $content
	 * @param string $name
	 *
	 * @return string
	 */
	public function render( $atts, $content, $shortcodeName ) {

		$defaultAtts = array(
			'id'               => get_the_ID(),
			'monthstoshow'     => '',
			'display_price'    => MPHB()->settings()->main()->isRoomTypeCalendarShowPrices(),
			'truncate_price'   => MPHB()->settings()->main()->isRoomTypeCalendarTruncatePrices(),
			'display_currency' => MPHB()->settings()->main()->isRoomTypeCalendarShowPricesCurrency(),
			'class'            => '',
		);

		$atts = shortcode_atts( $defaultAtts, $atts, $shortcodeName );

		$roomTypeId = absint( $atts['id'] );

		// It's not IDs, but also must be > 0
		$monthsToShow = \MPHB\Utils\ValidateUtils::validateCommaSeparatedIds( $atts['monthstoshow'] );

		if ( ! empty( $monthsToShow ) ) {

			// Must be only 1 or 2 numbers
			$monthsToShow = array_slice( $monthsToShow, 0, 2 );
			$monthsToShow = join( ',', $monthsToShow );

		} else {

			$monthsToShow = '';
		}

		ob_start();

		do_action( 'mphb_sc_before_availability_calendar' );

		mphb_tmpl_the_room_type_calendar(
			$roomTypeId,
			$monthsToShow,
			rest_sanitize_boolean( $atts['display_price'] ),
			rest_sanitize_boolean( $atts['truncate_price'] ),
			rest_sanitize_boolean( $atts['display_currency'] )
		);

		do_action( 'mphb_sc_after_availability_calendar' );

		$content = ob_get_clean();

		$wrapperClass = apply_filters( 'mphb_sc_availability_calendar_wrapper_classes', 'mphb_sc_availability_calendar-wrapper' );
		$wrapperClass = trim( $wrapperClass . ' ' . $atts['class'] );
		return '<div class="' . esc_attr( $wrapperClass ) . '">' . $content . '</div>';
	}
}
