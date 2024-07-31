<?php
/**
 * Abstract Rest Terms Controller
 * Class for creating API for WordPress terms.
 *
 * @package MPHB\Advanced\Api
 * @since 4.1.0
 */

namespace MPHB\Advanced\Api\Controllers;

use MPHB\Advanced\Api\ApiHelper;
use WP_Error;
use WP_REST_Request;
use WP_REST_Response;
use WP_REST_Server;
use WP_Term;

abstract class AbstractRestTermsController extends AbstractRestController {

	/**
	 * Route base.
	 *
	 * @var string
	 */
	protected $rest_base = '';

	/**
	 * Taxonomy.
	 *
	 * @var string
	 */
	protected $taxonomy = '';

	/**
	 * Register the routes for terms.
	 */
	public function register_routes() {
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
					'id' => array(
						'description' => 'Unique identifier for the resource.',
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

	/**
	 * Check if a given request has access to read the terms.
	 *
	 * @param  WP_REST_Request $request  Full details about the request.
	 *
	 * @return WP_Error|boolean
	 */
	public function get_items_permissions_check( $request ) {
		$permissions = $this->check_permissions( $request, 'read' );
		if ( is_wp_error( $permissions ) ) {
			return $permissions;
		}

		if ( ! $permissions ) {
			return new WP_Error(
				'mphb_rest_cannot_view',
				'Sorry, you cannot list resources.',
				array( 'status' => rest_authorization_required_code() )
			);
		}

		return true;
	}

	/**
	 * Check if a given request has access to create a term.
	 *
	 * @param  WP_REST_Request $request  Full details about the request.
	 *
	 * @return WP_Error|boolean
	 */
	public function create_item_permissions_check( $request ) {
		$permissions = $this->check_permissions( $request, 'create' );
		if ( is_wp_error( $permissions ) ) {
			return $permissions;
		}

		if ( ! $permissions ) {
			return new WP_Error(
				'mphb_rest_cannot_create',
				'Sorry, you are not allowed to create resources.',
				array( 'status' => rest_authorization_required_code() )
			);
		}

		return true;
	}

	/**
	 * Check if a given request has access to read a term.
	 *
	 * @param  WP_REST_Request $request  Full details about the request.
	 *
	 * @return WP_Error|boolean
	 */
	public function get_item_permissions_check( $request ) {
		$permissions = $this->check_permissions( $request, 'read' );
		if ( is_wp_error( $permissions ) ) {
			return $permissions;
		}

		if ( ! $permissions ) {
			return new WP_Error(
				'mphb_rest_cannot_view',
				'Sorry, you cannot view this resource.',
				array( 'status' => rest_authorization_required_code() )
			);
		}

		return true;
	}

	/**
	 * Check if a given request has access to update a term.
	 *
	 * @param  WP_REST_Request $request  Full details about the request.
	 *
	 * @return WP_Error|boolean
	 */
	public function update_item_permissions_check( $request ) {
		$permissions = $this->check_permissions( $request, 'edit' );
		if ( is_wp_error( $permissions ) ) {
			return $permissions;
		}

		if ( ! $permissions ) {
			return new WP_Error(
				'mphb_rest_cannot_edit',
				'Sorry, you are not allowed to edit this resource.',
				array( 'status' => rest_authorization_required_code() )
			);
		}

		return true;
	}

	/**
	 * Check if a given request has access to delete a term.
	 *
	 * @param  WP_REST_Request $request  Full details about the request.
	 *
	 * @return WP_Error|boolean
	 */
	public function delete_item_permissions_check( $request ) {
		$permissions = $this->check_permissions( $request, 'delete' );
		if ( is_wp_error( $permissions ) ) {
			return $permissions;
		}

		if ( ! $permissions ) {
			return new WP_Error(
				'mphb_rest_cannot_delete',
				'Sorry, you are not allowed to delete this resource.',
				array( 'status' => rest_authorization_required_code() )
			);
		}

		return true;
	}

	/**
	 * Check if a given request has access batch create, update and delete items.
	 *
	 * @param  WP_REST_Request $request  Full details about the request.
	 *
	 * @return boolean|WP_Error
	 */
	public function batch_items_permissions_check( $request ) {
		$permissions = $this->check_permissions( $request, 'batch' );
		if ( is_wp_error( $permissions ) ) {
			return $permissions;
		}

		if ( ! $permissions ) {
			return new WP_Error(
				'mphb_rest_cannot_batch',
				'Sorry, you are not allowed to batch manipulate this resource.',
				array( 'status' => rest_authorization_required_code() )
			);
		}

		return true;
	}

	/**
	 * Check permissions.
	 *
	 * @param  WP_REST_Request $request  Full details about the request.
	 * @param  string          $context  Request context.
	 *
	 * @return bool|WP_Error
	 */
	protected function check_permissions( $request, $context = 'read' ) {
		if ( ! taxonomy_exists( $this->taxonomy ) ) {
			return new WP_Error(
				'mphb_rest_taxonomy_invalid',
				'Taxonomy does not exist.',
				array( 'status' => 404 )
			);
		}

		// Check permissions for a single term.
		$id = intval( $request['id'] );
		if ( $id ) {
			$term = get_term( $id, $this->taxonomy );

			if ( is_wp_error( $term ) || ! $term || $term->taxonomy !== $this->taxonomy ) {
				return new WP_Error(
					'mphb_rest_term_invalid',
					'Resource does not exist.',
					array( 'status' => 404 )
				);
			}

			return ApiHelper::checkTermPermissions( $this->taxonomy, $context, $term->term_id );
		}

		return ApiHelper::checkTermPermissions( $this->taxonomy, $context );
	}

	/**
	 * Get terms associated with a taxonomy.
	 *
	 * @param  WP_REST_Request $request  Full details about the request.
	 *
	 * @return WP_REST_Response|WP_Error
	 */
	public function get_items( $request ) {
		$prepared_args = array(
			'exclude'    => $request['exclude'],
			'include'    => $request['include'],
			'order'      => $request['order'],
			'orderby'    => $request['orderby'],
			'product'    => $request['product'],
			'hide_empty' => $request['hide_empty'],
			'number'     => $request['per_page'],
			'search'     => $request['search'],
			'slug'       => $request['slug'],
		);

		if ( ! empty( $request['offset'] ) ) {
			$prepared_args['offset'] = $request['offset'];
		} else {
			$prepared_args['offset'] = ( $request['page'] - 1 ) * $prepared_args['number'];
		}

		$taxonomy_obj = get_taxonomy( $this->taxonomy );

		if ( $taxonomy_obj->hierarchical && isset( $request['parent'] ) ) {
			if ( 0 === $request['parent'] ) {
				// Only query top-level terms.
				$prepared_args['parent'] = 0;
			} else {
				if ( $request['parent'] ) {
					$prepared_args['parent'] = $request['parent'];
				}
			}
		}

		/**
		 * Filter the query arguments, before passing them to `get_terms()`.
		 *
		 * Enables adding extra arguments or setting defaults for a terms
		 * collection request.
		 *
		 * @see https://developer.wordpress.org/reference/functions/get_terms/
		 *
		 * @param  array  $prepared_args  Array of arguments to be
		 *                                       passed to get_terms.
		 * @param  WP_REST_Request  $request  The current request.
		 */
		$prepared_args = apply_filters( "mphb_rest_{$this->taxonomy}_query", $prepared_args, $request );

		$query_result = get_terms( $this->taxonomy, $prepared_args );

		$count_args = $prepared_args;
		unset( $count_args['number'] );
		unset( $count_args['offset'] );
		$total_terms = wp_count_terms( $this->taxonomy, $count_args );

		// Ensure we don't return results when offset is out of bounds.
		// See https://core.trac.wordpress.org/ticket/35935.
		if ( $prepared_args['offset'] && $prepared_args['offset'] >= $total_terms ) {
			$query_result = array();
		}

		// wp_count_terms can return a falsy value when the term has no children.
		if ( ! $total_terms ) {
			$total_terms = 0;
		}

		$response = array();
		foreach ( $query_result as $term ) {
			$data       = $this->prepare_item_for_response( $term, $request );
			$response[] = $this->prepare_response_for_collection( $data );
		}

		$response = rest_ensure_response( $response );

		// Store pagination values for headers then unset for count query.
		$per_page = (int) $prepared_args['number'];
		$page     = ceil( ( ( (int) $prepared_args['offset'] ) / $per_page ) + 1 );

		$response->header( 'X-WP-Total', (int) $total_terms );
		$max_pages = ceil( $total_terms / $per_page );
		$response->header( 'X-WP-TotalPages', (int) $max_pages );

		$base = str_replace( '(?P<attribute_id>[\d]+)', $request['attribute_id'], $this->rest_base );
		$base = add_query_arg( $request->get_query_params(), rest_url( '/' . $this->namespace . '/' . $base ) );
		if ( $page > 1 ) {
			$prev_page = $page - 1;
			if ( $prev_page > $max_pages ) {
				$prev_page = $max_pages;
			}
			$prev_link = add_query_arg( 'page', $prev_page, $base );
			$response->link_header( 'prev', $prev_link );
		}
		if ( $max_pages > $page ) {
			$next_page = $page + 1;
			$next_link = add_query_arg( 'page', $next_page, $base );
			$response->link_header( 'next', $next_link );
		}

		return $response;
	}

	/**
	 * Create a single term for a taxonomy.
	 *
	 * @param  WP_REST_Request $request  Full details about the request.
	 *
	 * @return WP_REST_Request|WP_Error
	 */
	public function create_item( $request ) {
		$name   = $request['name'];
		$args   = array();
		$schema = $this->get_item_schema();

		if ( ! empty( $schema['properties']['description'] ) && isset( $request['description'] ) ) {
			$args['description'] = $request['description'];
		}
		if ( isset( $request['slug'] ) ) {
			$args['slug'] = $request['slug'];
		}
		if ( isset( $request['parent'] ) ) {
			if ( ! is_taxonomy_hierarchical( $this->taxonomy ) ) {
				return new WP_Error(
					'mphb_rest_taxonomy_not_hierarchical',
					'Can not set resource parent, taxonomy is not hierarchical.',
					array( 'status' => 400 )
				);
			}
			$args['parent'] = $request['parent'];
		}

		$term = wp_insert_term( $name, $this->taxonomy, $args );
		if ( is_wp_error( $term ) ) {
			$error_data = array( 'status' => 400 );

			// If we're going to inform the client that the term exists,
			// give them the identifier they can actually use.
			$term_id = $term->get_error_data( 'term_exists' );
			if ( $term_id ) {
				$error_data['resource_id'] = $term_id;
			}

			return new WP_Error( $term->get_error_code(), $term->get_error_message(), $error_data );
		}

		$term = get_term( $term['term_id'], $this->taxonomy );

		$this->update_additional_fields_for_object( $term, $request );

		// Add term data.
		$meta_fields = $this->update_term_meta_fields( $term, $request );
		if ( is_wp_error( $meta_fields ) ) {
			wp_delete_term( $term->term_id, $this->taxonomy );

			return $meta_fields;
		}

		/**
		 * Fires after a single term is created or updated via the REST API.
		 *
		 * @param  WP_Term  $term  Inserted Term object.
		 * @param  WP_REST_Request  $request  Request object.
		 * @param  boolean  $creating  True when creating term, false when updating.
		 */
		do_action( "mphb_rest_insert_{$this->taxonomy}", $term, $request, true );

		$request->set_param( 'context', 'edit' );
		$response = $this->prepare_item_for_response( $term, $request );
		$response = rest_ensure_response( $response );
		$response->set_status( 201 );

		$base = '/' . $this->namespace . '/' . $this->rest_base;
		if ( ! empty( $request['attribute_id'] ) ) {
			$base = str_replace( '(?P<attribute_id>[\d]+)', (int) $request['attribute_id'], $base );
		}

		$response->header( 'Location', rest_url( $base . '/' . $term->term_id ) );

		return $response;
	}

	/**
	 * Get a single term from a taxonomy.
	 *
	 * @param  WP_REST_Request $request  Full details about the request.
	 *
	 * @return WP_REST_Request|WP_Error
	 */
	public function get_item( $request ) {
		$term = get_term( (int) $request['id'], $this->taxonomy );

		if ( is_wp_error( $term ) ) {
			return $term;
		}

		$response = $this->prepare_item_for_response( $term, $request );

		return rest_ensure_response( $response );
	}

	/**
	 * Update a single term from a taxonomy.
	 *
	 * @param  WP_REST_Request $request  Full details about the request.
	 *
	 * @return WP_REST_Request|WP_Error
	 */
	public function update_item( $request ) {
		$term          = get_term( (int) $request['id'], $this->taxonomy );
		$schema        = $this->get_item_schema();
		$prepared_args = array();

		if ( isset( $request['name'] ) ) {
			$prepared_args['name'] = $request['name'];
		}
		if ( ! empty( $schema['properties']['description'] ) && isset( $request['description'] ) ) {
			$prepared_args['description'] = $request['description'];
		}
		if ( isset( $request['slug'] ) ) {
			$prepared_args['slug'] = $request['slug'];
		}
		if ( isset( $request['parent'] ) ) {
			if ( ! is_taxonomy_hierarchical( $this->taxonomy ) ) {
				return new WP_Error(
					'mphb_rest_taxonomy_not_hierarchical',
					'Can not set resource parent, taxonomy is not hierarchical.',
					array( 'status' => 400 )
				);
			}
			$prepared_args['parent'] = $request['parent'];
		}

		// Only update the term if we haz something to update.
		if ( ! empty( $prepared_args ) ) {
			$update = wp_update_term( $term->term_id, $term->taxonomy, $prepared_args );
			if ( is_wp_error( $update ) ) {
				return $update;
			}
		}

		$term = get_term( (int) $request['id'], $this->taxonomy );

		$this->update_additional_fields_for_object( $term, $request );

		// Update term data.
		$meta_fields = $this->update_term_meta_fields( $term, $request );
		if ( is_wp_error( $meta_fields ) ) {
			return $meta_fields;
		}

		/**
		 * Fires after a single term is created or updated via the REST API.
		 *
		 * @param  WP_Term  $term  Inserted Term object.
		 * @param  WP_REST_Request  $request  Request object.
		 * @param  boolean  $creating  True when creating term, false when updating.
		 */
		do_action( "mphb_rest_insert_{$this->taxonomy}", $term, $request, false );

		$request->set_param( 'context', 'edit' );
		$response = $this->prepare_item_for_response( $term, $request );

		return rest_ensure_response( $response );
	}

	/**
	 * Delete a single term from a taxonomy.
	 *
	 * @param  WP_REST_Request $request  Full details about the request.
	 *
	 * @return WP_REST_Response|WP_Error
	 */
	public function delete_item( $request ) {
		$force = isset( $request['force'] ) ? (bool) $request['force'] : false;

		// We don't support trashing for this type, error out.
		if ( ! $force ) {
			return new WP_Error(
				'mphb_rest_trash_not_supported',
				'Resource does not support trashing.',
				array( 'status' => 501 )
			);
		}

		$term = get_term( (int) $request['id'], $this->taxonomy );
		// Get default category id.
		$default_category_id = absint( get_option( 'default_product_cat', 0 ) );

		// Prevent deleting the default product category.
		if ( $default_category_id === (int) $request['id'] ) {
			return new WP_Error(
				'mphb_rest_cannot_delete',
				'Default product category cannot be deleted.',
				array( 'status' => 500 )
			);
		}

		$request->set_param( 'context', 'edit' );
		$response = $this->prepare_item_for_response( $term, $request );

		$retval = wp_delete_term( $term->term_id, $term->taxonomy );
		if ( ! $retval ) {
			return new WP_Error(
				'mphb_rest_cannot_delete',
				'The resource cannot be deleted.',
				array( 'status' => 500 )
			);
		}

		/**
		 * Fires after a single term is deleted via the REST API.
		 *
		 * @param  WP_Term  $term  The deleted term.
		 * @param  WP_REST_Response  $response  The response data.
		 * @param  WP_REST_Request  $request  The request sent to the API.
		 */
		do_action( "mphb_rest_delete_{$this->taxonomy}", $term, $response, $request );

		return $response;
	}

	/**
	 * Prepare links for the request.
	 *
	 * @param  object          $term  Term object.
	 * @param  WP_REST_Request $request  Full details about the request.
	 *
	 * @return array Links for the given term.
	 */
	protected function prepare_links( $term, $request ) {
		$base = '/' . $this->namespace . '/' . $this->rest_base;

		if ( ! empty( $request['attribute_id'] ) ) {
			$base = str_replace( '(?P<attribute_id>[\d]+)', (int) $request['attribute_id'], $base );
		}

		$links = array(
			'self'       => array(
				'href' => rest_url( trailingslashit( $base ) . $term->term_id ),
			),
			'collection' => array(
				'href' => rest_url( $base ),
			),
		);

		if ( $term->parent ) {
			$parent_term = get_term( (int) $term->parent, $term->taxonomy );
			if ( $parent_term ) {
				$links['up'] = array(
					'href' => rest_url( trailingslashit( $base ) . $parent_term->term_id ),
				);
			}
		}

		return $links;
	}

	/**
	 * Update term meta fields.
	 *
	 * @param  WP_Term         $term  Term object.
	 * @param  WP_REST_Request $request  Full details about the request.
	 *
	 * @return bool|WP_Error
	 */
	protected function update_term_meta_fields( $term, $request ) {
		return true;
	}

	/**
	 * Get the query params for collections
	 *
	 * @return array
	 */
	public function get_collection_params() {
		$params = parent::get_collection_params();

		$params['context']['default'] = 'view';

		$params['exclude']    = array(
			'description'       => 'Ensure result set excludes specific IDs.',
			'type'              => 'array',
			'items'             => array(
				'type' => 'integer',
			),
			'default'           => array(),
			'sanitize_callback' => 'wp_parse_id_list',
		);
		$params['include']    = array(
			'description'       => 'Limit result set to specific ids.',
			'type'              => 'array',
			'items'             => array(
				'type' => 'integer',
			),
			'default'           => array(),
			'sanitize_callback' => 'wp_parse_id_list',
		);
		$params['offset']     = array(
			'description'       => 'Offset the result set by a specific number of items. Applies to hierarchical taxonomies only.',
			'type'              => 'integer',
			'sanitize_callback' => 'absint',
			'validate_callback' => 'rest_validate_request_arg',
		);
		$params['order']      = array(
			'description'       => 'Order sort attribute ascending or descending.',
			'type'              => 'string',
			'sanitize_callback' => 'sanitize_key',
			'default'           => 'asc',
			'enum'              => array(
				'asc',
				'desc',
			),
			'validate_callback' => 'rest_validate_request_arg',
		);
		$params['orderby']    = array(
			'description'       => 'Sort collection by resource attribute.',
			'type'              => 'string',
			'sanitize_callback' => 'sanitize_key',
			'default'           => 'name',
			'enum'              => array(
				'id',
				'include',
				'name',
				'slug',
				'term_group',
				'description',
				'count',
			),
			'validate_callback' => 'rest_validate_request_arg',
		);
		$params['hide_empty'] = array(
			'description'       => 'Whether to hide resources not assigned to any products.',
			'type'              => 'boolean',
			'default'           => false,
			'validate_callback' => 'rest_validate_request_arg',
		);
		$params['parent']     = array(
			'description'       => 'Limit result set to resources assigned to a specific parent. Applies to hierarchical taxonomies only.',
			'type'              => 'integer',
			'sanitize_callback' => 'absint',
			'validate_callback' => 'rest_validate_request_arg',
		);
		$params['product']    = array(
			'description'       => 'Limit result set to resources assigned to a specific product.',
			'type'              => 'integer',
			'default'           => null,
			'validate_callback' => 'rest_validate_request_arg',
		);
		$params['slug']       = array(
			'description'       => 'Limit result set to resources with a specific slug.',
			'type'              => 'string',
			'validate_callback' => 'rest_validate_request_arg',
		);

		return $params;
	}


	/**
	 * Get a schema that matches the JSON schema.
	 *
	 * @return array
	 */
	public function get_item_schema() {
		$schema = array(
			'$schema'    => 'http://json-schema.org/draft-04/schema#',
			'title'      => sanitize_title( $this->rest_base ),
			'type'       => 'object',
			'properties' => array(
				'id'          => array(
					'description' => 'Unique identifier for the resource.',
					'type'        => 'integer',
					'context'     => array( 'embed', 'view', 'edit' ),
					'readonly'    => true,
				),
				'name'        => array(
					'description' => 'Name of term.',
					'type'        => 'string',
					'context'     => array( 'embed', 'view', 'edit' ),
					'arg_options' => array(
						'sanitize_callback' => 'sanitize_text_field',
					),
				),
				'slug'        => array(
					'description' => 'An alphanumeric identifier for the resource unique to its type.',
					'type'        => 'string',
					'context'     => array( 'embed', 'view', 'edit' ),
					'arg_options' => array(
						'sanitize_callback' => 'sanitize_title',
					),
				),
				'description' => array(
					'description' => 'HTML description of the resource.',
					'type'        => 'string',
					'context'     => array( 'embed', 'view', 'edit' ),
					'arg_options' => array(
						'sanitize_callback' => 'wp_filter_post_kses',
					),
				),
				'count'       => array(
					'description' => 'Number of published resources.',
					'type'        => 'integer',
					'context'     => array( 'embed', 'view', 'edit' ),
					'readonly'    => true,
				),
			),
		);

		if ( is_taxonomy_hierarchical( $this->taxonomy ) ) {
			$schema['properties']['parent'] = array(
				'description' => 'The ID for the parent of the resource.',
				'type'        => 'integer',
				'context'     => array( 'embed', 'view', 'edit' ),
			);
		}

		return $this->add_additional_fields_schema( $schema );
	}

	/**
	 * Prepare a single taxonomy output for response.
	 *
	 * @param  WP_Term         $item  Term object.
	 * @param  WP_REST_Request $request  Request instance.
	 *
	 * @return WP_REST_Response
	 */
	public function prepare_item_for_response( $item, $request ) {
		$data = array(
			'id'          => (int) $item->term_id,
			'name'        => $item->name,
			'slug'        => $item->slug,
			'description' => $item->description,
			'count'       => (int) $item->count,
		);

		$schema = $this->get_item_schema();
		if ( isset( $schema['properties']['parent'] ) ) {
			$data['parent'] = (int) $item->parent;
		}

		$context = ! empty( $request['context'] ) ? $request['context'] : 'view';
		$data    = $this->add_additional_fields_to_object( $data, $request );
		$data    = $this->filter_response_by_context( $data, $context );

		$response = rest_ensure_response( $data );

		$response->add_links( $this->prepare_links( $item, $request ) );

		/**
		 * Filter a term item returned from the API.
		 *
		 * Allows modification of the term data right before it is returned.
		 *
		 * @param  WP_REST_Response  $response  The response object.
		 * @param  object  $item  The original term object.
		 * @param  WP_REST_Request  $request  Request used to generate the response.
		 */
		return apply_filters( "mphb_rest_prepare_{$this->taxonomy}", $response, $item, $request );
	}
}
