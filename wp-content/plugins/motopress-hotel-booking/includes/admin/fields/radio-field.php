<?php

namespace MPHB\Admin\Fields;

class RadioField extends InputField {

	const TYPE = 'radio';

	protected $list = array();

	public function __construct( $name, $details, $value = '' ) {
		parent::__construct( $name, $details, $value );
		$this->list = isset( $details['list'] ) ? $details['list'] : $this->list;
	}

	protected function renderInput() {
		$checkedValue = $this->value;

		if ( ! empty( $this->list ) && ! array_key_exists( $checkedValue, $this->list ) ) {
			// This is for checking the first item instead of not checking anything
			reset( $this->list );
			$checkedValue = key( $this->list );
		}

		$output = '';

		$output .= '<fieldset>';

		foreach ( $this->list as $value => $label ) {
			$output     .= '<label>';
				$output .= '<input type="radio" name="' . esc_attr( $this->getName() ) . '" value="' . esc_attr( $value ) . '" ' . checked( $checkedValue, $value, false ) . '/>';
				$output .= '<span>' . esc_html( $label ) . '</span>';
			$output     .= '</label>';
			$output     .= '<br />';
		}

			// Remove last <br />
			$output = substr( $output, 0, -6 );

		$output .= '</fieldset>';

		return $output;
	}

	public function getList() {
		return $this->list;
	}

	public function sanitize( $value ) {
		return array_key_exists( $value, $this->list ) ? $value : $this->default;
	}

	public static function renderValue( self $field ) {
		$value = $field->getValue();
		$list  = $field->getList();

		return isset( $list[ $value ] ) ? $list[ $value ] : $list[ $field->getDefault() ];
	}

}
