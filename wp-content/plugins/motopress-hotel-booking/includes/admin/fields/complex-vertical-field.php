<?php

namespace MPHB\Admin\Fields;

class ComplexVerticalField extends AbstractComplexField {

	const TYPE = 'complex-vertical';

	protected $defaultLabel;
	protected $minItemsCount   = 0;
	protected $canChooseDefult = false;

	public function __construct( $name, $details, $values = array() ) {

		parent::__construct( $name, $details, $values );

		$this->defaultLabel    = isset( $details['deafault_label'] ) ? $details['default_label'] : __( 'Default', 'motopress-hotel-booking' );
		$this->minItemsCount   = isset( $details['min_items_count'] ) ? $details['min_items_count'] : $this->minItemsCount;
		$this->canChooseDefult = isset( $details['can_choose_default'] ) ? $details['can_choose_default'] : $this->canChooseDefult;
	}

	public function setValue( $value ) {
		if ( ! isset( $value['last_index'] ) ) {
			$value['last_index'] = $this->getLastIndex();
		}
		if ( ! isset( $value['items'] ) ) {
			$value['items'] = array();
		}
		if ( count( $value['items'] ) < $this->minItemsCount ) {
			$value['items']      = array_pad( $value['items'], $this->minItemsCount, array() );
			$value['last_index'] = $value['last_index'] + ( $this->minItemsCount - count( $value['items'] ) );
		}
		if ( $this->canChooseDefult && ! isset( $value['default'] ) ) {
			$value['default'] = key( $value['items'] );
		}
		parent::setValue( $value );
	}

	protected function renderInput() {
		$items = $this->value['items'];

		$result  = '<input type="hidden" name="' . esc_attr( $this->getName() ) . '" value="" />';
		$result .= '<table class="widefat striped" data-uniqid="' . $this->uniqid . '" data-min-items-count="' . esc_attr( $this->minItemsCount ) . '">';
		$result .= '<thead>';

		$result .= $this->generateItem( '%key_' . $this->uniqid . '%', array(), true );

		foreach ( $items as $key => $value ) {
			$result .= $this->generateItem( $key, $value );
		}

		$result .= '<tfoot>';
		$result .= '<td colspan="2" class="mphb-centered">';
		$result .= '<input type="hidden" value="' . esc_attr( $this->getLastIndex() ) . '" name="' . esc_attr( $this->getName() ) . '[last_index]" class="mphb-complex-last-index">';
		$result .= '<button type="button" class="button mphb-complex-add-item" data-id="' . $this->uniqid . '">' . esc_html( $this->addLabel ) . '</button>';
		$result .= '</td>';
		$result .= '</tfoot>';
		$result .= '</table>';

		return $result;
	}

	protected function getLastIndex() {
		return isset( $this->value['last_index'] ) ? $this->value['last_index'] : 0;
	}

	protected function incLastIndex() {
		return ++$this->value['last_index'];
	}

	protected function getDefaultItemIndex() {
		$default = 0;
		if ( isset( $this->value['default'] ) ) {
			$default = $this->value['default'];
		} elseif ( isset( $this->value['items'] ) && ! empty( $this->value['items'] ) ) {
			reset( $this->value['items'] );
			$default = key( $this->value['items'] );
		}
		return $default;
	}

	protected function generateItem( $key, $value, $isPrototype = false, $default = false ) {

		$itemClass = ( $isPrototype ) ? 'mphb-complex-item-prototype mphb-hide' : '';
		$result    = '<tbody class="' . $itemClass . '" data-id="' . $key . '">';
		foreach ( $this->fields as $field ) {
			$newField = clone $field;
			$newField->setName( $this->getName() . '[items]' . '[' . $key . ']' . '[' . $field->getName() . ']' );
			$newField->setValue( isset( $value[ $field->getName() ] ) ? $value[ $field->getName() ] : '' );

			if ( $isPrototype ) {
				$newField->setDisabled( true );
			}

			$this->fixDependencies( $newField, 'items][' . $key, $value );

			$result .= '<tr>';
			$result .= '<th class="row-title">' . $newField->getLabelTag() . '</th>';
			$result .= '<td>';
			$result .= $newField->render();
			$result .= '</td>';
			$result .= '</tr>';
		}

		$result .= '<tr class="mphb-complex-item-actions-holder">';
		$result .= '<td colspan="' . count( $this->fields ) . '" class="mphb-centered">';

		if ( $this->canChooseDefult ) {
			$result .= $this->generateDefaultInput( $key );
			$result .= '<br/>';
		}

		if ( count( $this->value['items'] ) <= $this->minItemsCount ) {
			$result .= $this->renderDeleteItemButton( 'disabled="disabled"', 'mphb-hide' );
		} else {
			$result .= $this->renderDeleteItemButton();
		}
		$result .= '</td>';
		$result .= '</tr>';
		$result .= '</tbody>';

		return $result;
	}

	protected function generateDefaultInput( $key ) {
		return '<label><input type="radio" name="' . esc_attr( $this->getName() ) . '[default]" value="' . $key . '" ' . checked( $this->getDefaultItemIndex(), $key, false ) . '> ' . $this->defaultLabel . '</label>';
	}

	public function sanitize( $values ) {
		if ( ! is_array( $values ) ) {
			$values = $this->default;
		} else {

			if ( isset( $values['items'] ) ) {
				foreach ( $values['items'] as $key => &$value ) {
					foreach ( $this->fields as $field ) {
						$value[ $field->getName() ] = $field->sanitize( isset( $value[ $field->getName() ] ) ? $value[ $field->getName() ] : $field->getDefault() );
					}
				}
			}

			if ( isset( $values['last_index'] ) ) {
				$values['last_index'] = absint( $values['last_index'] );
			}

			if ( isset( $values['default'] ) ) {
				$values['default'] = absint( $values['default'] );
				if ( ! key_exists( $values['default'], $values['items'] ) ) {
					reset( $values['items'] );
					$values['default'] = key( $values['items'] );
				}
			}
		}
		return $values;
	}

}
