<?php

namespace MPHB\Admin\Fields;

class NumberField extends TextField implements DependentField {

	const TYPE = 'number';

	protected $min;
	protected $max;
	protected $step       = 1;
	protected $size       = 'small';
	protected $default    = 0;
	protected $allowEmpty = false;

	protected $dependencyInput = '';
	protected $disableOnValues = array();

	public function __construct( $name, $details, $value = '' ) {
		parent::__construct( $name, $details, $value );
		$this->min        = isset( $details['min'] ) ? $details['min'] : null;
		$this->max        = isset( $details['max'] ) ? $details['max'] : null;
		$this->step       = isset( $details['step'] ) ? $details['step'] : $this->step;
		$this->allowEmpty = isset( $details['allow_empty'] ) ? $details['allow_empty'] : $this->allowEmpty;

		if ( isset( $details['dependency'] ) ) {
			$this->dependencyInput = isset( $details['dependency']['input'] ) ? $details['dependency']['input'] : $this->dependencyInput;
			$this->disableOnValues = isset( $details['dependency']['disable_on'] ) ? $details['dependency']['disable_on'] : $this->disableOnValues;
		}
	}

	public function generateAttrs() {
		$attrs  = parent::generateAttrs();
		$attrs .= ( isset( $this->min ) ) ? ' min="' . esc_attr( $this->min ) . '"' : '';
		$attrs .= ( isset( $this->max ) ) ? ' max="' . esc_attr( $this->max ) . '"' : '';
		$attrs .= ( isset( $this->step ) ) ? ' step="' . esc_attr( $this->step ) . '"' : '';
		if ( $this->dependencyInput && ! empty( $this->disableOnValues ) ) {
			$attrs .= ' data-dependency="' . esc_attr( $this->dependencyInput ) . '"';
			$attrs .= ' data-disable-on="' . esc_attr( implode( ',', $this->disableOnValues ) ) . '"';
		}
		return $attrs;
	}

	public function getDependencyInput() {
		return $this->dependencyInput;
	}

	public function setDependencyInput( $dependencyInput ) {
		$this->dependencyInput = $dependencyInput;
	}

	public function updateDependency( $dependencyValue ) {
		if ( in_array( $dependencyValue, $this->disableOnValues ) ) {
			$this->disabled          = true;
			$this->additionalClasses = ltrim( $this->additionalClasses . ' mphb-keep-disabled' );
		}
	}

	public function sanitize( $value ) {
		$value = sanitize_text_field( $value );
		$value = is_numeric( $value ) ? $value : $this->default;

		if ( $value !== '' || ! $this->allowEmpty ) {
			$value = isset( $this->min ) && $value < $this->min ? $this->min : $value;
			$value = isset( $this->max ) && $value > $this->max ? $this->max : $value;
		}

		return $value;
	}

	public static function renderValue( TextField $field ) {
		// Add inner label to get output like "10 night", "5 days" etc.
		$value      = $field->getValue();
		$innerLabel = $field->getInnerLabel();

		return empty( $innerLabel ) ? $value : $value . ' ' . $innerLabel;
	}

}
