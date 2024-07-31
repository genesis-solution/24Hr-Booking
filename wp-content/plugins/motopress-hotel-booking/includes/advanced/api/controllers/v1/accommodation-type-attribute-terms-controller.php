<?php
/**
 * @package MPHB\Advanced\Api
 * @since 4.1.0
 */

namespace MPHB\Advanced\Api\Controllers\V1;

use MPHB\Advanced\Api\Controllers\AbstractRestTermsController;
use WP_REST_Server;

class AccommodationTypeAttributeTermsController extends AbstractRestTermsController {


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
	protected $rest_base = 'accommodation_types/attributes/(?P<attribute_id>[\d]+)/terms';

	/**
	 * Register the routes for terms.
	 */
	public function register_routes() {
		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base,
			array(
				'args'   => array(
					'attribute_id' => array(
						'description' => 'Unique identifier for the attribute of the terms.',
						'type'        => 'integer',
					),
				),
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
							'name' => array(
								'type'        => 'string',
								'description' => 'Name for the resource.',
								'required'    => true,
							),
						)
					),
				),
				'schema' => array( $this, 'get_public_item_schema' ),
			)
		);

		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base . '/(?P<id>[\d]+)',
			array(
				'args'   => array(
					'id'           => array(
						'description' => 'Unique identifier for the resource.',
						'type'        => 'integer',
					),
					'attribute_id' => array(
						'description' => 'Unique identifier for the attribute of the terms.',
						'type'        => 'integer',
					),
				),
				array(
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => array( $this, 'get_item' ),
					'permission_callback' => array( $this, 'get_item_permissions_check' ),
					'args'                => array(
						'context' => $this->get_context_param( array( 'default' => 'view' ) ),
					),
				),
				array(
					'methods'             => WP_REST_Server::EDITABLE,
					'callback'            => array( $this, 'update_item' ),
					'permission_callback' => array( $this, 'update_item_permissions_check' ),
					'args'                => $this->get_endpoint_args_for_item_schema( WP_REST_Server::EDITABLE ),
				),
				array(
					'methods'             => WP_REST_Server::DELETABLE,
					'callback'            => array( $this, 'delete_item' ),
					'permission_callback' => array( $this, 'delete_item_permissions_check' ),
					'args'                => array(
						'force' => array(
							'default'     => false,
							'type'        => 'boolean',
							'description' => 'Required to be true, as resource does not support trashing.',
						),
					),
				),
				'schema' => array( $this, 'get_public_item_schema' ),
			)
		);

		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base . '/batch',
			array(
				'args'   => array(
					'attribute_id' => array(
						'description' => 'Unique identifier for the attribute of the terms.',
						'type'        => 'integer',
					),
				),
				array(
					'methods'             => WP_REST_Server::EDITABLE,
					'callback'            => array( $this, 'batch_items' ),
					'permission_callback' => array( $this, 'batch_items_permissions_check' ),
					'args'                => $this->get_endpoint_args_for_item_schema( WP_REST_Server::EDITABLE ),
				),
				'schema' => array( $this, 'get_public_batch_schema' ),
			)
		);
	}

	private function defineTaxonomy( $request ) {
		$attributeId    = $request['attribute_id'];
		$attributePost  = get_post( $attributeId );
		$attributeName  = mphb_sanitize_attribute_name( $attributePost->post_name );
		$this->taxonomy = mphb_attribute_taxonomy_name( $attributeName );
	}

	public function check_permissions( $request, $context = 'read' ) {
		$this->defineTaxonomy( $request );

		return parent::check_permissions( $request, $context );
	}

	public function get_items( $request ) {
		$this->defineTaxonomy( $request );

		return parent::get_items( $request );
	}

	public function create_item( $request ) {
		$this->defineTaxonomy( $request );

		return parent::create_item( $request );
	}

	public function get_item( $request ) {
		$this->defineTaxonomy( $request );

		return parent::get_item( $request );
	}

	public function update_item( $request ) {
		$this->defineTaxonomy( $request );

		return parent::update_item( $request );
	}

	public function delete_item( $request ) {
		$this->defineTaxonomy( $request );

		return parent::delete_item( $request );
	}
}
