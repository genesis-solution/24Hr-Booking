<?php

namespace MPHB\Admin\Fields;

class MultipleCheckboxField extends InputField {

	const TYPE = 'multiple-checkbox';

	protected $list              = array();
	protected $default           = array();
	protected $allowGroupActions = true;

	/**
	 * @var mixed The value of the item, that means "all values".
	 */
	protected $allValue = null;

	/**
	 * @var array
	 * @since 3.6.0
	 */
	protected $alwaysEnabled = array();

	/**
	 * @var mixed "All values" item was selected.
	 */
	protected $allSelected = false;

	public function __construct( $name, $details, $value = '' ) {

		parent::__construct( $name, $details, $value );

		$this->list              = ( isset( $details['list'] ) ? $details['list'] : $this->list );
		$this->allowGroupActions = ( isset( $details['allow_group_actions'] ) ? (bool) $details['allow_group_actions'] : $this->allowGroupActions );
		$this->allValue          = ( isset( $details['all_value'] ) ? $details['all_value'] : $this->allValue );
		$this->alwaysEnabled     = ( isset( $details['always_enabled'] ) ? $details['always_enabled'] : array() );

		if ( ! is_null( $this->allValue ) && ! array_key_exists( $this->allValue, $this->list ) ) {

			$this->allValue = null;
		}
	}

	protected function getCtrlClasses() {

		// "mphb-left" for complex tables to align checkboxes by the left side
		return parent::getCtrlClasses() . ' mphb-left';
	}

	protected function renderInput() {

		$result = '';

		// Input for empty value if nothing checked
		$result .= '<input type="hidden" name="' . esc_attr( $this->getName() ) . '" \>';

		$list = $this->list;

		$this->allSelected = ( ! is_null( $this->allValue ) && in_array( $this->allValue, $this->value ) );

		// Show "All" element on the top of the list
		if ( ! is_null( $this->allValue ) ) {

			$value   = $this->allValue;
			$result .= $this->renderListItem( $value, $list[ $value ], 'mphb-checkbox-all' );

			// Don't show this item anymore
			unset( $list[ $value ] );

		} elseif ( ! empty( $this->alwaysEnabled ) ) {

			foreach ( $this->alwaysEnabled as $value => $label ) {

				$result .= $this->renderFakeItem( $value, $label );
			}
		}

		// Show other items
		foreach ( $list as $value => $label ) {

			$result .= $this->renderListItem( $value, $label );
		}

		if ( $this->allowGroupActions ) {

			// If there no "All" checkbox - then add "Select all" button
			if ( is_null( $this->allValue ) ) {
				$result .= '<button class="button-link mphb-checkbox-select-all">' . __( 'Select all', 'motopress-hotel-booking' ) . '</button>';
				$result .= ' - ';
			}

			$result .= '<button class="button-link mphb-checkbox-unselect-all">' . __( 'Unselect all', 'motopress-hotel-booking' ) . '</button>';
		}

		return $result;
	}

	protected function renderListItem( $value, $label, $addClass = '' ) {

		$isSelected = ( $this->allSelected || in_array( $value, $this->value ) );

		$name = $this->getName();
		$atts = $this->generateAttrs();

		if ( $isSelected ) {
			$atts .= ' checked="checked"';
		}

		if ( $this->allSelected && $value != $this->allValue ) {

			$addClass = trim( $addClass . ' mphb-keep-disabled' );

			if ( ! $this->disabled ) { // Don't duplicate the attribute

				$atts .= ' disabled="disabled"';
			}
		}

		if ( ! empty( $addClass ) ) {
			$atts .= ' class="' . $addClass . '"';
		}

		return $this->renderCheckbox( $name, $value, $atts, $label );
	}

	/**
	 * @param string $value
	 * @param string $label
	 * @return string
	 *
	 * @since 3.6.0
	 */
	protected function renderFakeItem( $value, $label ) {

		$name = $this->getName();

		$atts  = $this->generateAttrs();
		$atts .= ' checked="checked"';
		$atts .= ' class="mphb-keep-disabled"';

		if ( ! $this->disabled ) { // Don't duplicate the attribute

			$atts .= ' disabled="disabled"';
		}

		return $this->renderCheckbox( $name, $value, $atts, $label );
	}

	/**
	 * @param string $name
	 * @param string $value
	 * @param string $atts
	 * @param string $label
	 * @return string
	 *
	 * @since 3.6.0
	 */
	protected function renderCheckbox( $name, $value, $atts, $label ) {

		$result  = '<label>';
		$result .= '<input name="' . esc_attr( $name ) . '[]" value="' . esc_attr( $value ) . '" type="checkbox" ' . $atts . ' />';
		$result .= $label;
		$result .= '</label>';
		$result .= '<br />';

		return $result;
	}

	public function getList() {
		return $this->list;
	}

	public function getAllValue() {
		return $this->allValue;
	}

	public function sanitize( $values ) {

		if ( ! is_array( $values ) ) {
			$values = $this->default;
		}

		foreach ( $values as $index => $value ) {

			if ( ! array_key_exists( $value, $this->list ) ) {

				unset( $values[ $index ] );
			}
		}

		return $values;
	}

	public static function renderValue( self $field ) {

		$values = $field->getValue();
		$list   = $field->getList();

		$labels = array();

		foreach ( $list as $key => $label ) {

			if ( in_array( $key, $values ) ) {

				$labels[] = $label;
			}
		}

		return implode( ', ', $labels );
	}
}
