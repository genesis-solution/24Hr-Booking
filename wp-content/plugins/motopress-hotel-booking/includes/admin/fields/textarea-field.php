<?php

namespace MPHB\Admin\Fields;

class TextareaField extends InputField {

	const TYPE = 'textarea';

	protected $size        = 'large';
	protected $placeholder = '';
	protected $rows        = '';

	public function __construct( $name, $details, $value = '' ) {
		parent::__construct( $name, $details, $value );
		$this->size        = ( isset( $details['size'] ) ) ? $details['size'] : $this->size;
		$this->placeholder = ( isset( $details['placeholder'] ) ) ? $details['placeholder'] : $this->placeholder;
		$this->rows        = ( isset( $details['rows'] ) ) ? $details['rows'] : $this->rows;
	}

	protected function renderInput() {
		$result = '<textarea name="' . esc_attr( $this->getName() ) . '" id="' . MPHB()->addPrefix( $this->getName() ) . '" class="' . $this->generateSizeClasses() . '"' . $this->generateAttrs() . '>' . esc_textarea( $this->value ) . '</textarea>';
		return $result;
	}

	protected function generateAttrs() {
		$attrs  = parent::generateAttrs();
		$attrs .= ( ! empty( $this->placeholder ) ) ? ' placeholder="' . esc_attr( $this->placeholder ) . '"' : '';
		$attrs .= ( ! empty( $this->rows ) ) ? ' rows="' . esc_attr( $this->rows ) . '"' : '';
		return $attrs;
	}

	protected function generateSizeClasses() {
		$classes = '';
		switch ( $this->size ) {
			case 'small':
				$classes .= ' small-text';
				break;
			case 'regular':
				$classes .= ' regular-text';
				break;
			case 'large':
				$classes .= ' large-text';
				break;
			case 'medium':
				$classes .= ' all-options';
				break;
		}
		return $classes;
	}

	public function sanitize( $value ) {
		// TODO sanitize textarea
		return $value;
	}

	public static function renderValue( self $field ) {
		return $field->getValue();
	}

}
