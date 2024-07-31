<?php
/**
 * @package MPHB\Advanced\Api
 * @since 4.1.0
 */

namespace MPHB\Advanced\Api\Data;

use MPHB\Advanced\Api\ApiHelper;

abstract class AbstractData {

	public $entity;
	protected $_entity_init_state;

	public function __construct( $entity ) {
		$this->entity             = $entity;
		$this->_entity_init_state = clone $entity;
	}

	/**
	 * @param $property
	 *
	 * @return mixed
	 * @throws \Exception
	 */
	public function __get( $property ) {
		$getterCallback = 'get' . ApiHelper::convertSnakeToCamelString( $property );
		if ( method_exists( $this, $getterCallback ) ) {
			return $this->{$getterCallback}();
		} elseif ( method_exists( $this->entity, $getterCallback ) ) {
			return $this->entity->{$getterCallback}();
		} else {
			throw new \Exception( sprintf( 'You need to implement method %s in class %s.', $getterCallback, static::class ) );
		}
	}


	/**
	 * @param $property
	 * @param $value
	 *
	 * @throws \Exception
	 */
	public function __set( $property, $value ) {
		$setterCallback = 'set' . ApiHelper::convertSnakeToCamelString( $property );
		if ( method_exists( $this, $setterCallback ) ) {
			$this->{$setterCallback}( $value );
			return;
		}
		if ( method_exists( $this->entity, $setterCallback ) ) {
			$this->entity->{$setterCallback}( $value );
			return;
		}
		$writableFields = static::getWritableFields();
		if ( ! isset( $writableFields[ $property ] ) && ! property_exists( $this, $property ) ) {
			throw new \Exception( sprintf( 'You cannot set readonly property: %s to class %s.', $property, static::class ) );
		}
		$this->$property = $value;
	}

	/**
	 * @return array
	 */
	abstract public static function getProperties();


	/**
	 * @return array
	 */
	public static function getFields() {
		return array_keys( static::getProperties() );
	}

	/**
	 * @return array
	 */
	public static function getRequiredFields() {
		return array_keys(
			array_filter(
				static::getProperties(),
				function ( $schema ) {
					return ! empty( $schema['required'] );
				}
			)
		);
	}

	/**
	 * @return array
	 */
	public static function getRequiredFieldKeys() {
		return array_keys( static::getRequiredFields() );
	}

	/**
	 * @return array
	 */
	public static function getWritableFields() {
		return array_filter(
			static::getProperties(),
			function ( $schema ) {
				return empty( $schema['readonly'] );
			}
		);
	}

	/**
	 * @return array
	 */
	public static function getWritableFieldKeys() {
		return array_keys( static::getWritableFields() );
	}

	/**
	 * @return array
	 * @throws \Exception
	 */
	public function getData() {
		$data   = array();
		$fields = static::getFields();
		foreach ( $fields as $field ) {
			$data[ $field ] = $this->{$field};
		}

		return $data;
	}


	public static function getSchema( $title ) {
		return array(
			'$schema'    => 'http://json-schema.org/draft-04/schema#',
			'title'      => sanitize_title( $title ),
			'type'       => 'object',
			'properties' => static::getProperties(),
		);
	}
}
