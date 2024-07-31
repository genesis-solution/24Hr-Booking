<?php

namespace MPHB\Admin\Fields;

class TextField extends InputField {

	const TYPE = 'text';

	protected $inputType;
	protected $size        = 'regular';
	protected $placeholder = '';
	protected $list        = array();

	/**
	 * @var string
	 * @since 3.7.3
	 */
	protected $pattern = '';

	/**
	 * @var string
	 *
	 * @since 3.9.9
	 */
	protected $inputMode = '';

	public function __construct( $name, $details, $value = '' ) {
		parent::__construct( $name, $details, $value );
		$this->size        = ( isset( $details['size'] ) ) ? $details['size'] : $this->size;
		$this->placeholder = ( isset( $details['placeholder'] ) ) ? $details['placeholder'] : $this->placeholder;
		if ( ! isset( $this->inputType ) ) {
			$this->inputType = static::TYPE;
		}
		$this->list    = ( isset( $details['list'] ) ) ? $details['list'] : $this->list;
		$this->pattern = ( isset( $details['pattern'] ) ) ? $details['pattern'] : $this->pattern;

		if ( ! empty( $details['inputMode'] ) && in_array( $details['inputMode'], array( 'none', 'text', 'decimal', 'numeric', 'tel', 'search', 'email', 'url' ) ) ) {
			$this->inputMode = $details['inputMode'];
		}
	}

	protected function renderInput() {
		$result = '<input name="' . esc_attr( $this->getName() ) . '" value="' . esc_attr( $this->value ) . '" id="' . MPHB()->addPrefix( $this->getName() ) . '" class="' . $this->generateSizeClasses() . '"' . $this->generateAttrs() . '/>';
		if ( ! empty( $this->list ) ) {
			$result .= '<datalist id="' . esc_attr( $this->name . '-datalist' ) . '">';
			foreach ( $this->list as $value => $label ) {
				$result .= '<option value="' . esc_attr( $value ) . '">' . esc_html( $label ) . '</option>';
			}
			$result .= '</datalist>';
		}
		return $result;
	}

	/**
	 *
	 * @since 3.9.9 - generates 'inputmode' attribute
	 */
	protected function generateAttrs() {
		$attrs  = parent::generateAttrs();
		$attrs .= ' type="' . esc_attr( $this->inputType ) . '"';
		$attrs .= ( ! empty( $this->placeholder ) ) ? ' placeholder="' . esc_attr( $this->placeholder ) . '"' : '';
		$attrs .= ( ! empty( $this->pattern ) ) ? ' pattern="' . esc_attr( $this->pattern ) . '"' : '';
		$attrs .= ( ! empty( $this->list ) ) ? ' list="' . esc_attr( $this->name . '-datalist' ) . '"' : '';
		$attrs .= ! empty( $this->inputMode ) ? ' inputmode="' . esc_attr( $this->inputMode ) . '"' : '';
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
			case 'price':
				$classes .= ' mphb-price-text';
				break;
			case 'long-price':
				$classes .= ' mphb-long-price-text';
				break;
			case 'wide':
				$classes .= ' mphb-wide-text';
				break;
		}
		return $classes;
	}

	public function sanitize( $value ) {
		return sanitize_text_field( $value );
	}

	public static function renderValue( self $field ) {
		return $field->getValue();
	}

}
