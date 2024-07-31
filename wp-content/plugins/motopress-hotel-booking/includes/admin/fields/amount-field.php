<?php

namespace MPHB\Admin\Fields;

class AmountField extends InputField implements DependentField {

	const TYPE = 'amount';

	protected $PHP_INT_MIN; // Constant PHP_INT_MIN available only since PHP 7

	protected $min     = 0;
	protected $max     = PHP_INT_MAX;
	protected $size    = 'regular';
	protected $default = 0;

	protected $dependencyInput  = '';
	protected $singleTriggers   = array();
	protected $multipleTriggers = array();

	protected $renderType = 'price';

	public function __construct( $name, $details, $value = '' ) {
		parent::__construct( $name, $details, $value );

		$this->PHP_INT_MIN = ~PHP_INT_MAX;

		$this->min  = isset( $details['min'] ) ? $details['min'] : $this->min;
		$this->max  = isset( $details['max'] ) ? $details['max'] : $this->max;
		$this->size = isset( $details['size'] ) ? $details['size'] : $this->size;

		if ( isset( $details['dependency'] ) ) {
			$dependency             = $details['dependency'];
			$this->dependencyInput  = isset( $dependency['input'] ) ? $dependency['input'] : $this->dependencyInput;
			$this->singleTriggers   = isset( $dependency['single_input_on'] ) ? $dependency['single_input_on'] : $this->singleTriggers;
			$this->multipleTriggers = isset( $dependency['multiple_inputs_on'] ) ? $dependency['multiple_inputs_on'] : $this->doubleTriggers;
		}

		if ( isset( $details['default_render_type'] )
			&& ( $details['default_render_type'] == 'price' || $details['default_render_type'] == 'percent' ) ) {
			$this->renderType = $details['default_render_type'];
		}
	}

	protected function generateAttrs() {
		$atts  = parent::generateAttrs();
		$atts .= $this->min != $this->PHP_INT_MIN ? ' min="' . esc_attr( $this->min ) . '"' : '';
		$atts .= $this->max != PHP_INT_MAX ? ' max="' . esc_attr( $this->max ) . '"' : '';
		$atts .= ' step="any"'; // Increase by 1, but allow 0.0001
		return $atts;
	}

	protected function renderInput() {
		$isSingleValue    = ! is_array( $this->value );
		$isMultipleValues = ! $isSingleValue;

		$commonValue   = $isSingleValue ? $this->value : $this->value[0];
		$adultsValue   = $isMultipleValues ? $this->value[0] : $this->value;
		$childrenValue = $isMultipleValues ? $this->value[1] : $this->value;

		$wrapperAtts  = ' data-dependency="' . esc_attr( $this->dependencyInput ) . '"';
		$wrapperAtts .= ' data-single-triggers="' . esc_attr( implode( ',', $this->singleTriggers ) ) . '"';
		$wrapperAtts .= ' data-multiple-triggers="' . esc_attr( implode( ',', $this->multipleTriggers ) ) . '"';
		$wrapperAtts .= ' data-render-type="' . esc_attr( $this->renderType ) . '"';

		$result = '';

		$result .= '<div class="mphb-amount-inputs"' . $wrapperAtts . '>';

		$result .= '<div class="' . $this->getSingleGroupClasses( $isMultipleValues ) . '">';
		$result .= $this->renderNumber( 'common', $commonValue, $isMultipleValues );
		$result .= '</div>';

		$result .= '<div class="' . $this->getMultipleGroupClasses( $isSingleValue ) . '">';
		$result .= '<label>' . __( 'Per adult:', 'motopress-hotel-booking' ) . '<br />';
		$result .= $this->renderNumber( 'adults', $adultsValue, $isSingleValue );
		$result .= '</label>';
		$result .= '<label>' . __( 'Per child:', 'motopress-hotel-booking' ) . '<br />';
		$result .= $this->renderNumber( 'children', $childrenValue, $isSingleValue );
		$result .= '</label>';
		$result .= '</div>';

		$result .= '</div>';

		return $result;
	}

	protected function renderNumber( $subname, $value, $isDisabled ) {
		$name  = $this->getName() . '[' . $subname . ']';
		$id    = MPHB()->addPrefix( $this->getName() ) . '-' . $subname;
		$class = $this->getSizeClass() . ' mphb-amount-' . $subname . '-input';
		$atts  = $this->generateAttrs();

		if ( $isDisabled ) {
			$class .= ' mphb-keep-disabled';

			if ( ! $this->disabled ) {
				// Don't duplicate attr "disabled", check $this->disabled first
				$atts .= ' disabled="disabled"';
			}
		}

		$input = '<input type="number" name="' . esc_attr( $name ) . '" value="' . esc_attr( $value ) . '" id="' . $id . '" class="' . $class . '"' . $atts . ' />';

		return $input;
	}

	public function getSizeClass() {
		$class = 'regular-text';

		switch ( $this->size ) {
			case 'small':
			case 'regular':
			case 'large':
				$class = $this->size . '-text';
				break;

			case 'medium':
				$class = 'all-options';
				break;

			case 'price':
			case 'long-price':
			case 'wide':
			default:
				$class = 'mphb-' . $this->size . '-text';
				break;
		}

		return $class;
	}

	protected function getSingleGroupClasses( $isDisabled ) {
		$classes = 'mphb-amount-single-input-group';
		if ( is_array( $this->value ) ) {
			$classes .= ' mphb-hide';
		}
		return $classes;
	}

	protected function getMultipleGroupClasses( $isDisabled ) {
		$classes = 'mphb-amount-multiple-inputs-group';
		if ( ! is_array( $this->value ) ) {
			$classes .= ' mphb-hide';
		}
		return $classes;
	}

	public function getDependencyInput() {
		return $this->dependencyInput;
	}

	public function setDependencyInput( $dependencyInput ) {
		$this->dependencyInput = $dependencyInput;
	}

	public function updateDependency( $dependencyValue ) {
		if ( strpos( $dependencyValue, 'percent' ) !== false ) {
			$this->renderType = 'percentage';
		} else {
			$this->renderType = 'price';
		}
	}

	public function getRenderType() {
		return $this->renderType;
	}

	public function sanitize( $value ) {
		if ( isset( $value['common'] ) ) {
			return $this->sanitizeNumber( $value['common'] );
		} else {
			$values   = array();
			$values[] = $this->sanitizeNumber( $value['adults'] );
			$values[] = $this->sanitizeNumber( $value['children'] );
			return $values;
		}
	}

	protected function sanitizeNumber( $value ) {
		$value = is_numeric( $value ) ? (float) $value : $this->default;
		$value = round( $value, 4 ); // [MB-435] Accuracy in 4 digits
		$value = max( $this->min, min( $value, $this->max ) ); // min <= value <= max
		return $value;
	}

	public static function renderValue( self $field ) {
		$value          = $field->getValue();
		$renderType     = $field->getRenderType();
		$formatFunction = $renderType == 'price' ? 'mphb_format_price' : 'mphb_format_percentage';
		$atts           = array(
			'trim_zeros' => false,
			'decimals'   => 4,
		);

		if ( ! is_array( $value ) ) {
			return $formatFunction( $value, $atts );
		} else {
			$result  = __( 'Per adult: ', 'motopress-hotel-booking' );
			$result .= $formatFunction( $value[0], $atts );
			$result .= '<br />' . __( 'Per child: ', 'motopress-hotel-booking' );
			$result .= $formatFunction( $value[1], $atts );
			return $result;
		}
	}

}
