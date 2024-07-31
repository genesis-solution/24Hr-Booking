<?php

namespace MPHB\Admin\Fields;

class SelectField extends InputField {

	const TYPE = 'select';

	protected $list = array();

	public function __construct( $name, $details, $value = '' ) {
		parent::__construct( $name, $details, $value );
		$this->list = isset( $details['list'] ) ? $details['list'] : $this->list;
	}

	protected function renderInput() {

		$result = '<select name="' . esc_attr( $this->getName() ) . '" id="' . MPHB()->addPrefix( $this->getName() ) . '" ' . $this->generateAttrs() . '>';
		foreach ( $this->list as $key => $label ) {
			$result .= '<option value="' . esc_attr( $key ) . '"' . selected( $this->getValue(), $key, false ) . '>' . esc_html( $label ) . '</option>';
		}
		$result .= '</select>';
		return $result;
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

		return $list[ $value ];
	}
}
