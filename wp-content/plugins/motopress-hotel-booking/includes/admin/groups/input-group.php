<?php

namespace MPHB\Admin\Groups;

use \MPHB\Admin\Fields;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

abstract class InputGroup {

	/**
	 * @var \MPHB\Admin\Fields\InputField[]
	 */
	protected $fields = array();
	protected $name;
	protected $label;


	public function __construct( $name, $label ) {

		$this->name  = $name;
		$this->label = $label;
	}

	/**
	 * @param \MPHB\Admin\Fields\InputField $field
	 */
	public function addField( Fields\InputField $field ) {

		$this->fields[] = $field;
	}

	/**
	 * @param \MPHB\Admin\Fields\InputField[] $fields
	 */
	public function addFields( $fields ) {

		foreach ( $fields as $field ) {

			$this->addField( $field );
		}
	}

	/**
	 * @param \MPHB\Admin\Fields\InputField $field
	 * @param int                           $index
	 */
	public function insertField( Fields\InputField $field, $index ) {

		$this->fields[ $index ] = $field;
	}

	/**
	 * @param int|string $key Field index or name.
	 * @return boolean true if removed, false - otherwise
	 */
	public function removeField( $key ) {

		$index = is_numeric( $key ) ? intval( $key ) : $this->getIndexByName( $key );

		if ( isset( $this->fields[ $index ] ) ) {

			unset( $this->fields[ $index ] );

			// Reset the indexes so that there are no gaps
			$this->fields = array_values( $this->fields );

			return true;
		}

		return false;
	}

	/**
	 * @return \MPHB\Admin\Fields\InputField[]
	 */
	public function getFields() {

		return $this->fields;
	}

	/**
	 * @param string $name
	 * @return \MPHB\Admin\Fields\InputField|null Searched field or null if
	 * nothing found.
	 */
	public function getFieldByName( $name ) {

		$index = $this->getIndexByName( $name );

		return ( $index != -1 ) ? $this->fields[ $index ] : null;
	}

	/**
	 * @param string $name
	 * @return int Field index or -1 if nothing found.
	 */
	public function getIndexByName( $name ) {

		// Don't use for() here - don't rely on the absence of gaps
		// in $this->fields
		foreach ( $this->fields as $i => $field ) {

			if ( $field->getName() === $name ) {

				return $i;
			}
		}

		return -1;
	}

	/**
	 * @since 4.2.4
	 * @param string $name
	 * @return bool
	 */
	public function hasField( $name ) {

		return $this->getIndexByName( $name ) >= 0;
	}

	public function getName() {

		return $this->name;
	}

	public function getLabel() {

		return $this->label;
	}

	public function setName( $name ) {

		$this->name = $name;
	}

	abstract public function render();

	abstract public function save();

	public function getAttsFromRequest( $request = null, $allowReadonly = true ) {

		if ( is_null( $request ) ) {
			$request = $_REQUEST;
		}

		$atts = array();

		foreach ( $this->fields as $field ) {

			// Skip read-only fields
			if ( ! $allowReadonly && $field->isReadonly() ) {
				continue;
			}

			// Also skip the disabled ones
			if ( $field->isDisabled() ) {
				continue;
			}

			$fieldName = $field->getName();

			if ( isset( $request[ $fieldName ] ) ) {

				$value = $request[ $fieldName ];

				$atts[ $fieldName ] = $field->sanitize( $value );
			}
		}

		return $atts;
	}
}
