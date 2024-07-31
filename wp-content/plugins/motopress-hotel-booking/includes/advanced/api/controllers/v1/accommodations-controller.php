<?php
/**
 * @package MPHB\Advanced\Api
 * @since 4.1.0
 */

namespace MPHB\Advanced\Api\Controllers\V1;

use MPHB\Advanced\Api\Controllers\AbstractRestObjectController;
use MPHB\Advanced\Api\Data\AbstractPostData;
use MPHB\Advanced\Api\Data\AccommodationData;
use WP_Error;
use WP_REST_Request;
use WP_REST_Server;

class AccommodationsController extends AbstractRestObjectController {


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
	protected $rest_base = 'accommodations';

	/**
	 * Post type.
	 *
	 * @var string
	 */
	protected $post_type = 'mphb_room';

	/**
	 * Register the routes.
	 */
	public function register_routes() {

		parent::register_routes();

		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base,
			array(
				array(
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => array( $this, 'get_items' ),
					'permission_callback' => array( $this, 'get_items_permissions_check' ),
					'args'                => $this->get_collection_params(),
				),
				array(
					'methods'             => WP_REST_Server::CREATABLE,
					'callback'            => array( $this, 'create_item' ),
					'permission_callback' => array( $this, 'create_item_permissions_check' ),
					'args'                => array_merge(
						$this->get_endpoint_args_for_item_schema( WP_REST_Server::CREATABLE ),
						array(
							'accommodation_type_id' => array(
								'description' => 'Unique identifier for the accommodation type resource.',
								'type'        => 'integer',
								'context'     => array( 'embed', 'view', 'edit' ),
								'required'    => true,
							),
						)
					),
				),
				'schema' => array( $this, 'get_public_item_schema' ),
			),
			true
		);
	}

	protected function getEndpointWritableArgs( WP_REST_Request $request ) {
		$requestAttributes = $request->get_attributes();
		if ( isset( $requestAttributes['args']['id'] ) ) {
			unset( $requestAttributes['args']['id'] );
		}

		return array_keys( $requestAttributes['args'] );
	}


	/**
	 * Prepare a single item for create.
	 *
	 * @param  WP_REST_Request $request  Request object.
	 *
	 * @return WP_Error|AbstractPostData $data
	 */
	protected function prepare_item_for_database( $request ) {
		$id = isset( $request['id'] ) ? absint( $request['id'] ) : 0;

		if ( $id !== 0 ) {
			$data = $this->data::findById( $id );
			if ( is_null( $data ) ) {
				return new WP_Error(
					"mphb_rest_invalid_{$this->rest_base}_id",
					'ID is invalid.',
					array( 'status' => 400 )
				);
			}
			$this->data = $data;
		}

		$writableArgs = $this->getEndpointWritableArgs( $request );
		// Handle all writable props
		foreach ( $writableArgs as $arg ) {
			$value = $request[ $arg ];

			if ( ! is_null( $value ) ) {
				try {
					$this->data->{$arg} = $value;
				} catch ( \Exception $e ) {
					return new WP_Error( "mphb_rest_invalid_set_{$arg}", $e->getMessage(), array( 'status' => 400 ) );
				}
			}
		}

		/**
		 * Filter the data for the insert.
		 *
		 * @param  AbstractPostData  $data  data object
		 * @param  WP_REST_Request  $request  Request object.
		 */
		return apply_filters( "mphb_rest_pre_insert_{$this->post_type}", $this->data, $request );
	}

	/**
	 * Prepare links for the request.
	 *
	 * @param  AccommodationData $accommodationData  Accommodation data object.
	 * @param  WP_REST_Request   $request  Request object.
	 *
	 * @return array Links for the given post.
	 */
	protected function prepare_links( $accommodationData, $request ) {
		$links                          = parent::prepare_links( $accommodationData, $request );
		$links['accommodation_type_id'] = array(
			'href'       => rest_url(
				sprintf(
					'/%s/%s/%d',
					$this->namespace,
					'accommodation_types',
					$accommodationData->accommodation_type_id
				)
			),
			'embeddable' => true,
		);

		return $links;
	}
}
