<?php
/**
 * @package MPHB\Advanced\Api
 * @since 4.1.0
 */

namespace MPHB\Advanced\Api\Data;

use WP_Error;
use WP_REST_Request;

class OptionsSchema {

	/**
	 * @var array
	 */
	protected $schema = array();

	/**
	 * @var array
	 */
	protected $optionsSchema = array();

	/**
	 * @return array
	 */
	public function getOptionsSchema() {
		return $this->optionsSchema;
	}

	/**
	 * Custom sanitize callback used for all options to allow the use of 'null'.
	 *
	 * By default, the schema of settings will throw an error if a value is set to
	 * `null` as it's not a valid value for something like "type => string". We
	 * provide a wrapper sanitizer to allow the use of `null`.
	 *
	 * @param mixed           $value   The value for the setting.
	 * @param WP_REST_Request $request The request object.
	 * @param string          $param   The parameter name.
	 * @return mixed|WP_Error
	 */
	public function sanitizeCallback( $value, $request, $param ) {
		if ( is_null( $value ) ) {
			return $value;
		}

		return rest_parse_request_arg( $value, $request, $param );
	}

	/**
	 * @return array
	 */
	public function getSchema() {
		if ( $this->schema ) {
			return $this->schema;
		}

		$schema = array(
			'$schema'    => 'http://json-schema.org/draft-04/schema#',
			'title'      => 'settings',
			'type'       => 'object',
			'properties' => array(),
		);

		if ( ! count( $this->optionsSchema ) ) {
			return $schema;
		}

		foreach ( $this->optionsSchema as $endpointOptionName => $option ) {
			$schema['properties'][ $endpointOptionName ] = $option['schema'];
			if ( isset( $option['schema']['arg_options']['sanitize_callback'] ) ) {
				$schema['properties'][ $endpointOptionName ]['arg_options']['sanitize_callback'] = array( $this, 'sanitizeCallback' );
			}
		}
		$this->schema = $schema;

		return $schema;
	}

	/**
	 * Recursively add additionalProperties = false to all objects in a schema.
	 *
	 * This is need to restrict properties of objects in settings values to only
	 * registered items, as the REST API will allow additional properties by
	 * default.
	 *
	 * @param array $schema The schema array.
	 * @return array
	 */
	protected function setAdditionalPropertiesToFalse( $schema ) {
		if ( isset( $schema['oneOf'] ) ) {
			foreach ( $schema['oneOf'] as $key => $child_schema ) {
				$schema['oneOf'][ $key ] = $this->setAdditionalPropertiesToFalse( $child_schema );
			}
		}

		if ( isset( $schema['type'] ) ) {
			switch ( $schema['type'] ) {
				case 'object':
					foreach ( $schema['properties'] as $key => $child_schema ) {
						$schema['properties'][ $key ] = $this->setAdditionalPropertiesToFalse( $child_schema );
					}
					$schema['additionalProperties'] = false;

					break;
				case 'array':
					$schema['items'] = $this->setAdditionalPropertiesToFalse( $schema['items'] );
					break;

			}
		}

		return $schema;
	}

	/**
	 * @param $schema
	 *
	 * @return array
	 */
	private function prepareOptionSchema( $schema ) {
		$schema = $this->setAdditionalPropertiesToFalse( $schema );

		switch ( $schema['type'] ) {
			case 'array':
				$schema['default'] = array();
				break;
			case 'integer':
			case 'number':
				$schema['default'] = 0;
				break;
			case 'string':
				$schema['default'] = '';
				break;
			case 'boolean':
				$schema['default'] = false;
				break;
		}

		return $schema;
	}

	/**
	 * @param string $dbOptionName
	 * @param string $endpointOptionName
	 * @param array  $schema
	 */
	public function addOption( string $dbOptionName, string $endpointOptionName, array $schema ) {
		$option                                     = array(
			'name'        => $endpointOptionName,
			'option_name' => $dbOptionName,
			'schema'      => $this->prepareOptionSchema( $schema ),
		);
		$this->optionsSchema[ $endpointOptionName ] = $option;
	}
}
