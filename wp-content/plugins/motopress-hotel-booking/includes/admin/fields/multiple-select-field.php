<?php

namespace MPHB\Admin\Fields;

class MultipleSelectField extends SelectField {

	protected $default = array();

	protected function renderInput() {
		$value   = is_array( $this->getValue() ) ? $this->getValue() : array();
		$result  = '<input type="hidden" name="' . esc_attr( $this->getName() ) . '" >';
		$result .= '<select name="' . esc_attr( $this->getName() . '[]' ) . '" id="' . MPHB()->addPrefix( $this->getName() ) . '" ' . $this->generateAttrs() . '>';
		foreach ( $this->list as $key => $label ) {
			$selectedAttr = ( in_array( $key, $value ) ) ? ' selected="selected"' : '';
			$result      .= '<option value="' . esc_attr( $key ) . '"' . $selectedAttr . '>' . esc_html( $label ) . '</option>';
		}
		$result .= '</select>';
		return $result;
	}

	public function generateAttrs() {
		$attrs  = parent::generateAttrs();
		$attrs .= ' multiple="multiple"';
		$attrs .= ' size="' . count( $this->list ) . '"';
		return $attrs;
	}

	public function sanitize( $values ) {
		if ( ! is_array( $values ) ) {
			$values = $this->default;
		}
		foreach ( $values as $key => $value ) {
			if ( ! array_key_exists( $value, $this->list ) ) {
				unset( $values[ $key ] );
			}
		}
		return $values;
	}

}
