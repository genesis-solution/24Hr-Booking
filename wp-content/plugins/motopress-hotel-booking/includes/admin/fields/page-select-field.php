<?php

namespace MPHB\Admin\Fields;

class PageSelectField extends InputField {

	const TYPE = 'page-select';

	protected function renderInput() {

		$atts = array(
			'name'              => $this->getName(),
			'id'                => $this->getName(),
			'echo'              => 0,
			'selected'          => $this->getValue(),
			'show_option_none'  => esc_html__( '— Select —', 'motopress-hotel-booking' ),
			'option_none_value' => '',
		);

		$result = mphb_wp_dropdown_pages( $atts );

		return $result;
	}

	public function sanitize( $value ) {
		$value = sanitize_text_field( $value );
		return get_post( $value ) ? $value : $this->default;
	}
}
