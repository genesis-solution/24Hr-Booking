<?php

namespace MPHB\Admin\Fields;

abstract class AbstractComplexField extends InputField {

	const TYPE = 'complex';

	protected static $counter = 0;

	protected $default = array();
	protected $fields  = array();
	protected $names   = array();
	protected $addLabel;
	protected $deleteLabel;
	protected $prototypeFields = array();
	protected $uniqid          = '';

	public function __construct( $name, $details, $values = array() ) {
		parent::__construct( $name, $details, $values );

		self::$counter++;

		$this->addLabel    = isset( $details['add_label'] ) ? $details['add_label'] : __( 'Add', 'motopress-hotel-booking' );
		$this->deleteLabel = isset( $details['delete_label'] ) ? $details['delete_label'] : __( 'Delete', 'motopress-hotel-booking' );
		// Without $counter uniqid() may generate uqual string for multiple instances
		$this->uniqid = uniqid( self::$counter );

		if ( is_array( $details['fields'] ) ) {
			foreach ( $details['fields'] as $field ) {
				if ( is_a( $field, '\MPHB\Admin\Fields\InputField' ) ) {
					$this->fields[] = $field;
					$this->names[]  = $field->getName();
				}
			}
		}
	}

	protected function getCtrlAtts() {
		$atts  = parent::getCtrlAtts();
		$atts .= ' data-group="' . esc_attr( $this->getName() ) . '"';
		return $atts;
	}

	protected function renderAddItemButton( $attrs = '', $classes = '' ) {
		return '<button type="button" class="button mphb-complex-add-item ' . $classes . '" data-id="' . $this->uniqid . '" ' . $attrs . '>' . esc_html( $this->addLabel ) . '</button>';
	}

	protected function renderDeleteItemButton( $attrs = '', $classes = '' ) {
		return '<button type="button" class="button mphb-complex-delete-item ' . $classes . '" data-id="' . $this->uniqid . '" ' . $attrs . '>' . esc_html( $this->deleteLabel ) . '</button>';
	}

	abstract protected function generateItem( $key, $value, $isPrototype = false );

	protected function fixDependencies( $field, $rowIndex, $rowValues ) {
		// Change dependency input name and the list or variants (only if the
		// dependency input is also in this complex field)
		if ( $field instanceof DependentField ) { // "dynamic-select", "amount"
			$dependencyInput = $field->getDependencyInput();

			if ( in_array( $dependencyInput, $this->names ) ) {
				$field->setDependencyInput( $this->getName() . '[' . $rowIndex . '][' . $dependencyInput . ']' );

				if ( isset( $rowValues[ $dependencyInput ] ) ) {
					$field->updateDependency( $rowValues[ $dependencyInput ] );
				}
			}
		}
	}

}
