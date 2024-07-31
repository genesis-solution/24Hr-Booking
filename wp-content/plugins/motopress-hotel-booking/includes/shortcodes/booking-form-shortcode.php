<?php

namespace MPHB\Shortcodes;

use \MPHB\Entities;

class BookingFormShortcode extends AbstractShortcode {

	protected $name = 'mphb_availability';
	private $roomTypeId;

	/**
	 *
	 * @param array  $atts
	 * @param null   $content
	 * @param string $shortcodeName
	 * @return string
	 */
	public function render( $atts, $content, $shortcodeName ) {

		$defaultAtts = array(
			'id'    => get_the_ID(),
			'class' => '',
		);

		$atts = shortcode_atts( $defaultAtts, $atts, $shortcodeName );

		$this->roomTypeId = absint( $atts['id'] );

		ob_start();

		do_action( 'mphb_sc_booking_form_before_form' );

		$this->renderMain();

		do_action( 'mphb_sc_booking_form_after_form' );

		$content = ob_get_clean();

		$wrapperClass  = apply_filters( 'mphb_sc_booking_form_wrapper_classes', 'mphb_sc_booking_form-wrapper' );
		$wrapperClass .= empty( $wrapperClass ) ? $atts['class'] : ' ' . $atts['class'];
		return '<div class="' . esc_attr( $wrapperClass ) . '">' . $content . '</div>';
	}

	private function renderMain() {

		mphb_tmpl_the_room_reservation_form( $this->roomTypeId );
	}

}
