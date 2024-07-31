<?php
/**
 * Abstract Rest Options Controller
 * Class for creating API for WordPress options.
 *
 * @package MPHB\Advanced\Api
 * @since 4.1.0
 */

namespace MPHB\Advanced\Api\Controllers;

use MPHB\Advanced\Api\ApiHelper;
use MPHB\Advanced\Api\Data\OptionsSchema;
use WP_Error;
use WP_REST_Request;
use WP_REST_Server;

abstract class AbstractRestOptionsController extends AbstractRestController {

	/**
	 * $dbFormatKey => $endpointFormatKey
	 */
	const ENDPOINT_REPLACEMENT_RULES = array();

	/**
	 * Endpoint namespace.
	 *
	 * @var string
	 */
	protected $namespace = 'mphb/v1';

	/**
	 * Route base.
	 *
	 * @var string
	 */
	protected $rest_base = '';

	/**
	 * @var OptionsSchema
	 */
	protected $options;

	public function __construct() {
		$this->options = $this->initOptions();
	}

	public function register_routes() {

		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base,
			array(
				array(
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => array( $this, 'get_item' ),
					'args'                => array(),
					'permission_callback' => array( $this, 'get_item_permissions_check' ),
				),
				array(
					'methods'             => WP_REST_Server::EDITABLE,
					'callback'            => array( $this, 'update_item' ),
					'args'                => $this->get_endpoint_args_for_item_schema( WP_REST_Server::EDITABLE ),
					'permission_callback' => array( $this, 'get_item_permissions_check' ),
				),
				'schema' => array( $this, 'get_public_item_schema' ),
			)
		);
	}


	/**
	 * Checks if a given request has access to read and manage settings.
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 * @return bool True if the request has read access for the item, otherwise false.
	 */
	public function get_item_permissions_check( $request ) {
		return current_user_can( 'manage_options' );
	}

	/**
	 * Preparing a value from the database for the response view.
	 * - Renaming according to the rules of renaming. (if renaming rules exist)
	 * - Reformatting the value if the prepareResponse {$ OptionName} () method exists in the endpoint class
	 * - Sanitize values according to the scheme (type conversion and removal of values missing in the scheme)
	 */
	protected function prepareResponse( $option, $optionName, $schema ) {
		if ( count( static::ENDPOINT_REPLACEMENT_RULES ) ) {
			$option = $this->replaceAllOccurrencesKeysAndValues( $option, static::ENDPOINT_REPLACEMENT_RULES );
		}

		$prepareResponseCallback = 'prepareResponse' . ApiHelper::convertSnakeToCamelString( $optionName );
		if ( method_exists( $this, $prepareResponseCallback ) ) {
			$option = $this->{$prepareResponseCallback}( $option );
		}

		return rest_sanitize_value_from_schema( $option, $schema );
	}

	/**
	 * Retrieves the options.
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 * @return array|WP_Error Array on success, or WP_Error object on failure.
	 */
	public function get_item( $request ) {
		$options  = $this->options->getOptionsSchema();
		$response = array();

		foreach ( $options as $name => $args ) {
			$option            = get_option( $args['option_name'], $args['schema']['default'] );
			$response[ $name ] = $this->prepareResponse( $option, $name, $args['schema'] );
		}

		return $response;
	}

	/**
	 * Updates settings for the settings object.
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 * @return array|WP_Error Array on success, or error object on failure.
	 */
	public function update_item( $request ) {
		$preparedRequest = $this->prepareRequest( $request );
		if ( is_wp_error( $preparedRequest ) ) {
			return $preparedRequest;
		}

		foreach ( $preparedRequest as $option => $value ) {
			/*
			 * A null value for an option would have the same effect as
			 * deleting the option from the database, and relying on the
			 * default value.
			 */
			if ( is_null( $value ) ) {
				delete_option( $option );
			} else {
				update_option( $option, $value );
			}
		}

		return $this->get_item( $request );
	}

	public function get_item_schema() {
		$schema = $this->options->getSchema();

		return $this->add_additional_fields_schema( $schema );
	}

	/**
	 * @return OptionsSchema
	 */
	abstract protected function initOptions();

	/**
	 * Replace all array keys and values matching the replacement rules.
	 *
	 * @param  array $replaceableArray
	 * @param  array $replacementRules  [ $replaceKeyFrom => $replaceKeyTo ]
	 *
	 * @return mixed|null
	 */
	private function replaceAllOccurrencesKeysAndValues( array $replaceableArray, array $replacementRules ) {
		$replaceableKeysFrom = array_map(
			function ( $key ) {
				return '"' . $key . '"';
			},
			array_keys( $replacementRules )
		);
		$replaceableKeysTo   = array_map(
			function ( $key ) {
				return '"' . $key . '"';
			},
			$replacementRules
		);

		return json_decode( str_replace( $replaceableKeysFrom, $replaceableKeysTo, json_encode( $replaceableArray ) ), true );
	}

	/**
	 * @param $option
	 * @param $optionName
	 *
	 * @return mixed|null
	 * @throws \Exception
	 */
	protected function prepareRequestItem( $option, $optionName ) {
		$prepareRequestCallback = 'prepareRequest' . ApiHelper::convertSnakeToCamelString( $optionName );
		if ( method_exists( $this, $prepareRequestCallback ) ) {
			$option = $this->{$prepareRequestCallback}( $option );
		}

		if ( count( static::ENDPOINT_REPLACEMENT_RULES ) ) {
			$option = $this->replaceAllOccurrencesKeysAndValues( $option, array_flip( static::ENDPOINT_REPLACEMENT_RULES ) );
		}

		return $option;
	}

	private function prepareRequest( WP_REST_Request $request ) {
		$preparedRequest = array();
		$options         = $this->options->getOptionsSchema();

		$params = $request->get_params();

		foreach ( $options as $name => $args ) {
			if ( ! array_key_exists( $name, $params ) ) {
				continue;
			}

			if ( is_null( $request[ $name ] ) ) {
				/*
				 * A null value is returned in the response for any option
				 * that has a non-scalar value.
				 *
				 * To protect clients from accidentally including the null
				 * values from a response object in a request, we do not allow
				 * options with values that don't pass validation to be updated to null.
				 * Without this added protection a client could mistakenly
				 * delete all options that have invalid values from the
				 * database.
				 */
				if ( is_wp_error( rest_validate_value_from_schema( get_option( $args['option_name'], false ), $args['schema'] ) ) ) {
					return new WP_Error(
						'rest_invalid_stored_value',
						sprintf( __( 'The %s property has an invalid stored value, and cannot be updated to null.' ), $name ),
						array( 'status' => 500 )
					);
				}
			}

			try {
				$preparedRequest[ $args['option_name'] ] = $this->prepareRequestItem( $request[ $name ], $name );
			} catch ( \Exception $e ) {
				return new WP_Error(
					'mphb_rest_invalid_' . $name,
					$e->getMessage(),
					array( 'status' => 400 )
				);
			}
		}

		return $preparedRequest;
	}
}
