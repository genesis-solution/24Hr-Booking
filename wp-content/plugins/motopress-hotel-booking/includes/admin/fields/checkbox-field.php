<?php

namespace MPHB\Admin\Fields;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class CheckboxField extends InputField {

	const TYPE = 'checkbox';

	protected function renderInput() {

		// TODO: maybe we need to remove this hidden field?
		$result  = '<input name="' . esc_attr( $this->getName() ) . '" value="0" id="' . MPHB()->addPrefix( $this->getName() ) . '-hidden" ' . $this->generateAttrs() . ' type="hidden"/>';
		$result .= '<input name="' . esc_attr( $this->getName() ) . '" value="1" id="' . MPHB()->addPrefix( $this->getName() ) . '" ' . $this->generateAttrs() . ' type="checkbox" ' . checked( '1', $this->value, false ) . ' style="margin-top: 0;" />';

		return $result;
	}

	public function sanitize( $value ) {

		return sanitize_text_field( $value );
	}
}
