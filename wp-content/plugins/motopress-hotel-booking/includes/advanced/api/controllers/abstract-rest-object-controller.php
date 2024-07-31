<?php
/**
 * Abstract Rest Object Controller
 * Class for creating API for Entity objects from \MPHB\Entities.
 *
 * @class AbstractRestObjectController
 * @extends  AbstractRestController
 * @package MPHB\Advanced\Api
 * @since 4.1.0
 */

namespace MPHB\Advanced\Api\Controllers;

use MPHB\Advanced\Api\ApiHelper;
use MPHB\Advanced\Api\Data\AbstractPostData;
use MPHB\Advanced\Api\Data\DataFactory;
use WP_Error;
use WP_Post;
use WP_Query;
use WP_REST_Request;
use WP_REST_Response;
use WP_REST_Server;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

abstract class AbstractRestObjectController extends AbstractRestController {

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
	 * Post type.
	 *
	 * @var string
	 */
	protected $post_type = '';

	/**
	 * Controls visibility on frontend.
	 *
	 * @var string
	 */
	protected $public = false;

	/**
	 * @var AbstractPostData
	 */
	protected $data;

	public function __construct() {
		$this->data = DataFactory::create( $this->rest_base );
	}

	/**
	 * Register the routes.
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
					'args'                => $this->get_endpoint_args_for_item_schema( WP_REST_Server::CREATABLE ),
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
							'description' => 'Whether to bypass trash and force deletion.',
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
	 * Get the item schema, conforming to JSON Schema of endpoint.
	 *
	 * @return array
	 */
	public function get_item_schema() {
		return $this->add_additional_fields_schema( $this->data::getSchema( $this->rest_base ) );
	}

	/**
	 * Check if a given request has access to read items.
	 *
	 * @param  WP_REST_Request $request Full details about the request.
	 * @return WP_Error|boolean
	 */
	public function get_items_permissions_check( $request ) {
		if ( ! ApiHelper::checkPostPermissions( $this->post_type, 'read' ) ) {
			return new WP_Error( 'mphb_rest_cannot_view', 'Sorry, you cannot list resources.', array( 'status' => rest_authorization_required_code() ) );
		}

		return true;
	}

	/**
	 * Check if a given request has access to create an item.
	 *
	 * @param  WP_REST_Request $request Full details about the request.
	 * @return WP_Error|boolean
	 */
	public function create_item_permissions_check( $request ) {
		if ( ! ApiHelper::checkPostPermissions( $this->post_type, 'create' ) ) {
			return new WP_Error( 'mphb_rest_cannot_create', 'Sorry, you are not allowed to create resources.', array( 'status' => rest_authorization_required_code() ) );
		}

		return true;
	}

	/**
	 * Check if a given request has access to read an item.
	 *
	 * @param  WP_REST_Request $request Full details about the request.
	 * @return WP_Error|boolean
	 */
	public function get_item_permissions_check( $request ) {
		$post = get_post( (int) $request['id'] );

		if ( $post && ! ApiHelper::checkPostPermissions( $this->post_type, 'read', $post->ID ) ) {
			return new WP_Error( 'mphb_rest_cannot_view', 'Sorry, you cannot view this resource.', array( 'status' => rest_authorization_required_code() ) );
		}

		return true;
	}

	/**
	 * Check if a given request has access to update an item.
	 *
	 * @param  WP_REST_Request $request Full details about the request.
	 * @return WP_Error|boolean
	 */
	public function update_item_permissions_check( $request ) {
		$post = get_post( (int) $request['id'] );

		if ( $post && ! ApiHelper::checkPostPermissions( $this->post_type, 'edit', $post->ID ) ) {
			return new WP_Error( 'mphb_rest_cannot_edit', 'Sorry, you are not allowed to edit this resource.', array( 'status' => rest_authorization_required_code() ) );
		}

		return true;
	}

	/**
	 * Check if a given request has access to delete an item.
	 *
	 * @param  WP_REST_Request $request Full details about the request.
	 * @return bool|WP_Error
	 */
	public function delete_item_permissions_check( $request ) {
		$post = get_post( (int) $request['id'] );

		if ( $post && ! ApiHelper::checkPostPermissions( $this->post_type, 'delete', $post->ID ) ) {
			return new WP_Error( 'mphb_rest_cannot_delete', 'Sorry, you are not allowed to delete this resource.', array( 'status' => rest_authorization_required_code() ) );
		}

		return true;
	}

	/**
	 * Check if a given request has access batch create, update and delete items.
	 *
	 * @param  WP_REST_Request $request Full details about the request.
	 *
	 * @return boolean|WP_Error
	 */
	public function batch_items_permissions_check( $request ) {
		if ( ! ApiHelper::checkPostPermissions( $this->post_type, 'batch' ) ) {
			return new WP_Error( 'mphb_rest_cannot_batch', 'Sorry, you are not allowed to batch manipulate this resource.', array( 'status' => rest_authorization_required_code() ) );
		}

		return true;
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

		$writableArgs = $this->data::getWritableFieldKeys();
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
	 * @param  mixed           $object
	 * @param  WP_REST_Request $request
	 *
	 * @return WP_Error|WP_REST_Response
	 */
	public function prepare_item_for_response( $object, $request ) {
		$context  = ! empty( $request['context'] ) ? $request['context'] : 'view';
		$data     = $object->getData();
		$data     = $this->add_additional_fields_to_object( $data, $request );
		$data     = $this->filter_response_by_context( $data, $context );
		$response = rest_ensure_response( $data );
		$response->add_links( $this->prepare_links( $object, $request ) );

		/**
		 * Filter the data for a response.
		 *
		 * The dynamic portion of the hook name, $this->post_type, refers to post_type of the post being
		 * prepared for the response.
		 *
		 * @param  WP_REST_Response  $response  The response object.
		 * @param  mixed  $object  Entity object.
		 * @param  WP_REST_Request  $request  Request object.
		 */
		return apply_filters( "mphb_rest_prepare_{$this->post_type}", $response, $object, $request );
	}

	/**
	 * Get a single item.
	 *
	 * @param  WP_REST_Request $request  Full details about the request.
	 *
	 * @return WP_Error|WP_REST_Response
	 */
	public function get_item( $request ) {
		$id = (int) $request['id'];

		$object = $this->data::findById( $id );
		if ( is_null( $object ) ) {
			return new WP_Error(
				"mphb_rest_invalid_{$this->post_type}_id",
				'Invalid ID.',
				array( 'status' => 404 )
			);
		}

		$data     = $this->prepare_item_for_response( $object, $request );
		$response = rest_ensure_response( $data );

		if ( $this->public ) {
			$response->link_header( 'alternate', get_permalink( $id ), array( 'type' => 'text/html' ) );
		}

		return $response;
	}

	/**
	 * Create a single item.
	 *
	 * @param  WP_REST_Request $request  Full details about the request.
	 *
	 * @return WP_Error|WP_REST_Response
	 */
	public function create_item( $request ) {
		if ( ! empty( $request['id'] ) ) {
			return new WP_Error(
				"mphb_rest_{$this->post_type}_exists",
				sprintf( 'Cannot create existing %s.', $this->post_type ),
				array( 'status' => 400 )
			);
		}

		$object = $this->prepare_item_for_database( $request );

		if ( is_wp_error( $object ) ) {
			return $object;
		}

		try {
			$this->data->save();
		} catch ( \Exception $e ) {
			return new WP_Error( 'mphb_rest_save_error', $e->getMessage(), array( 'status' => 400 ) );
		}

		/**
		 * Fires after a single item is created or updated via the REST API.
		 *
		 * @param  object  $object  object.
		 * @param  WP_REST_Request  $request  Request object.
		 * @param  boolean  $creating  True when creating item, false when updating.
		 */
		do_action( "mphb_rest_insert_{$this->post_type}", $object, $request, true );

		$request->set_param( 'context', 'edit' );
		$response = $this->prepare_item_for_response( $object, $request );
		$response = rest_ensure_response( $response );
		$response->set_status( 201 );
		$response->header(
			'Location',
			rest_url( sprintf( '/%s/%s/%d', $this->namespace, $this->rest_base, $object->id ) )
		);

		return $response;
	}

	/**
	 * Delete post.
	 *
	 * @param WP_Post $post Post object.
	 */
	protected function delete_post( $post ) {
		wp_delete_post( $post->ID, true );
	}

	/**
	 * Update a single object.
	 *
	 * @param  WP_REST_Request $request  Full details about the request.
	 *
	 * @return WP_Error|WP_REST_Response
	 */
	public function update_item( $request ) {
		try {
			$post_id = (int) $request['id'];

			if ( empty( $post_id ) || get_post_type( $post_id ) !== $this->post_type ) {
				return new WP_Error(
					"mphb_rest_{$this->post_type}_invalid_id",
					'ID is invalid.',
					array( 'status' => 400 )
				);
			}
			$object = $this->prepare_item_for_database( $request );

			if ( is_wp_error( $object ) ) {
				return $object;
			}

			try {
				$this->data->save();
			} catch ( \Exception $e ) {
				return new WP_Error(
					'mphb_rest_save_error',
					$e->getMessage(),
					array( 'status' => 400 )
				);
			}

			/**
			 * Fires after a single item is created or updated via the REST API.
			 *
			 * @param  mixed  $object  Entity object.
			 * @param  WP_REST_Request  $request  Request object.
			 * @param  boolean  $creating  True when creating item, false when updating.
			 */
			do_action( "mphb_rest_insert_{$this->post_type}", $object, $request, false );
			$request->set_param( 'context', 'edit' );
			$response = $this->prepare_item_for_response( $object, $request );

			return rest_ensure_response( $response );

		} catch ( \Exception $e ) {
			return new WP_Error( 'mphb_rest_error', $e->getMessage(), array( 'status' => 400 ) );
		}
	}

	/**
	 * Prepare objects query.
	 *
	 * @param  WP_REST_Request $request  Full details about the request.
	 *
	 * @return array
	 * @since  4.1.1
	 */
	protected function prepareQuery( $request ) {
		/**
		 * Map WordPress to API query keys
		 */
		$queryArgsMap = array(
			'offset'              => 'offset',
			'order'               => 'order',
			'orderby'             => 'orderby',
			'paged'               => 'page',
			'post__in'            => 'include',
			'post__not_in'        => 'exclude',
			'posts_per_page'      => 'per_page',
			'name'                => 'slug',
			'post_parent__in'     => 'parent',
			'post_parent__not_in' => 'parent_exclude',
			's'                   => 'search',
		);

		$args = array();
		foreach ( $queryArgsMap as $queryWPArg => $queryAPIArg ) {
			if ( isset( $request[ $queryAPIArg ] ) && $request[ $queryAPIArg ] ) {
				$args[ $queryWPArg ] = $request[ $queryAPIArg ];
			}
		}

		$args['date_query'] = array();
		// Set before into date query. Date query must be specified as an array of an array.
		if ( isset( $request['before'] ) ) {
			$args['date_query'][0]['before'] = $request['before'];
		}

		// Set after into date query. Date query must be specified as an array of an array.
		if ( isset( $request['after'] ) ) {
			$args['date_query'][0]['after'] = $request['after'];
		}

		if ( is_array( $request['filter'] ) ) {
			$args = array_merge( $args, $request['filter'] );
			unset( $args['filter'] );
		}

		// Force the post_type argument, since it's not a user input variable.
		$args['post_type'] = $this->post_type;

		$args['fields'] = 'ids';

		/**
		 * Filter the query arguments for a request.
		 *
		 * Enables adding extra arguments or setting defaults for a post
		 * collection request.
		 *
		 * @param  array  $args  Key value array of query var to query value.
		 * @param  WP_REST_Request  $request  The request used.
		 */
		$args = apply_filters( "mphb_rest_{$this->post_type}_query", $args, $request );

		return $this->prepare_items_query( $args, $request );
	}


	/**
	 * Get a collection of posts.
	 *
	 * @param  WP_REST_Request $request  Full details about the request.
	 *
	 * @return WP_Error|WP_REST_Response
	 */
	public function get_items( $request ) {
		$queryArgs = $this->prepareQuery( $request );

		$postsQuery = new WP_Query();
		$postIds    = $postsQuery->query( $queryArgs );

		$posts = array();
		foreach ( $postIds as $postId ) {
			if ( ! ApiHelper::checkPostPermissions( $this->post_type, 'read', $postId ) ) {
				continue;
			}

			$object = $this->data::findById( $postId );

			if ( is_null( $object ) ) {
				return new WP_Error(
					"mphb_rest_invalid_{$this->post_type}_id",
					'Invalid ID.',
					array( 'status' => 404 )
				);
			}

			$data    = $this->prepare_item_for_response( $object, $request );
			$posts[] = $this->prepare_response_for_collection( $data );
		}

		$page        = (int) $queryArgs['paged'];
		$total_posts = $postsQuery->found_posts;

		if ( $total_posts < 1 ) {
			// Out-of-bounds, run the query again without LIMIT for total count.
			unset( $queryArgs['paged'] );
			$count_query = new WP_Query();
			$count_query->query( $queryArgs );
			$total_posts = $count_query->found_posts;
		}

		$max_pages = ceil( $total_posts / (int) $queryArgs['posts_per_page'] );

		$response = rest_ensure_response( $posts );
		$response->header( 'X-WP-Total', (int) $total_posts );
		$response->header( 'X-WP-TotalPages', (int) $max_pages );

		$request_params = $request->get_query_params();
		if ( ! empty( $request_params['filter'] ) ) {
			// Normalize the pagination params.
			unset( $request_params['filter']['posts_per_page'] );
			unset( $request_params['filter']['paged'] );
		}
		$base = add_query_arg( $request_params, rest_url( sprintf( '/%s/%s', $this->namespace, $this->rest_base ) ) );

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
	 * Delete a single item.
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 * @return WP_REST_Response|WP_Error
	 */
	public function delete_item( $request ) {
		$id    = (int) $request['id'];
		$force = (bool) $request['force'];
		$post  = get_post( $id );

		if ( empty( $id ) || empty( $post->ID ) || $post->post_type !== $this->post_type ) {
			return new WP_Error( "mphb_rest_{$this->post_type}_invalid_id", 'ID is invalid.', array( 'status' => 404 ) );
		}

		$supports_trash = EMPTY_TRASH_DAYS > 0;

		/**
		 * Filter whether an item is trashable.
		 *
		 * Return false to disable trash support for the item.
		 *
		 * @param boolean $supports_trash Whether the item type support trashing.
		 * @param WP_Post $post           The Post object being considered for trashing support.
		 */
		$supports_trash = apply_filters( "mphb_rest_{$this->post_type}_trashable", $supports_trash, $post );

		if ( ! ApiHelper::checkPostPermissions( $this->post_type, 'delete', $post->ID ) ) {
			return new WP_Error( "mphb_rest_user_cannot_delete_{$this->post_type}", sprintf( 'Sorry, you are not allowed to delete %s.', $this->post_type ), array( 'status' => rest_authorization_required_code() ) );
		}

		$request->set_param( 'context', 'edit' );

		$object = $this->data::findById( $post->ID );
		if ( is_null( $object ) ) {
			return new WP_Error(
				"mphb_rest_invalid_{$this->post_type}_id",
				'Invalid ID.',
				array( 'status' => 404 )
			);
		}

		$response = $this->prepare_item_for_response( $object, $request );

		// If we're forcing, then delete permanently.
		if ( $force ) {
			$result = wp_delete_post( $id, true );
		} else {
			// If we don't support trashing for this type, error out.
			if ( ! $supports_trash ) {
				return new WP_Error( 'mphb_rest_trash_not_supported', sprintf( 'The %s does not support trashing.', $this->post_type ), array( 'status' => 501 ) );
			}

			// Otherwise, only trash if we haven't already.
			if ( 'trash' === $post->post_status ) {
				return new WP_Error( 'mphb_rest_already_trashed', sprintf( 'The %s has already been deleted.', $this->post_type ), array( 'status' => 410 ) );
			}

			// (Note that internally this falls through to `wp_delete_post` if
			// the trash is disabled.)
			$result = wp_trash_post( $id );
		}

		if ( ! $result ) {
			return new WP_Error( 'mphb_rest_cannot_delete', sprintf( 'The %s cannot be deleted.', $this->post_type ), array( 'status' => 500 ) );
		}

		/**
		 * Fires after a single item is deleted or trashed via the REST API.
		 *
		 * @param object           $post     The deleted or trashed item.
		 * @param WP_REST_Response $response The response data.
		 * @param WP_REST_Request  $request  The request sent to the API.
		 */
		do_action( "mphb_rest_delete_{$this->post_type}", $post, $response, $request );

		return $response;
	}

	/**
	 * Prepare links for the request.
	 *
	 * @param  object          $object
	 * @param  WP_REST_Request $request  Request object.
	 *
	 * @return array
	 */
	protected function prepare_links( $object, $request ) {
		$links = array(
			'self'       => array(
				'href' => rest_url( sprintf( '/%s/%s/%d', $this->namespace, $this->rest_base, $object->id ) ),
			),
			'collection' => array(
				'href' => rest_url( sprintf( '/%s/%s', $this->namespace, $this->rest_base ) ),
			),
		);

		return $links;
	}

	/**
	 * Determine the allowed query_vars for a get_items() response and
	 * prepare for WP_Query.
	 *
	 * @param array           $prepared_args Prepared arguments.
	 * @param WP_REST_Request $request Request object.
	 * @return array          $query_args
	 */
	protected function prepare_items_query( $prepared_args = array(), $request = null ) {

		$valid_vars = array_flip( $this->get_allowed_query_vars() );
		$query_args = array();
		foreach ( $valid_vars as $var => $index ) {
			if ( isset( $prepared_args[ $var ] ) ) {
				/**
				 * Filter the query_vars used in `get_items` for the constructed query.
				 *
				 * The dynamic portion of the hook name, $var, refers to the query_var key.
				 *
				 * @param mixed $prepared_args[ $var ] The query_var value.
				 */
				$query_args[ $var ] = apply_filters( "mphb_rest_query_var-{$var}", $prepared_args[ $var ] );
			}
		}

		$query_args['ignore_sticky_posts'] = true;

		if ( 'include' === $query_args['orderby'] ) {
			$query_args['orderby'] = 'post__in';
		} elseif ( 'id' === $query_args['orderby'] ) {
			$query_args['orderby'] = 'ID'; // ID must be capitalized.
		} elseif ( 'slug' === $query_args['orderby'] ) {
			$query_args['orderby'] = 'name';
		}

		return $query_args;
	}

	/**
	 * Get all the WP Query vars that are allowed for the API request.
	 *
	 * @return array
	 */
	protected function get_allowed_query_vars() {
		global $wp;

		/**
		 * Filter the publicly allowed query vars.
		 *
		 * Allows adjusting of the default query vars that are made public.
		 *
		 * @param array  Array of allowed WP_Query query vars.
		 */
		$valid_vars = apply_filters( 'query_vars', $wp->public_query_vars );

		$post_type_obj = get_post_type_object( $this->post_type );
		if ( current_user_can( $post_type_obj->cap->edit_posts ) ) {
			/**
			 * Filter the allowed 'private' query vars for authorized users.
			 *
			 * If the user has the `edit_posts` capability, we also allow use of
			 * private query parameters, which are only undesirable on the
			 * frontend, but are safe for use in query strings.
			 *
			 * To disable anyway, use
			 * `add_filter( 'mphb_rest_private_query_vars', '__return_empty_array' );`
			 *
			 * @param array $private_query_vars Array of allowed query vars for authorized users.
			 * }
			 */
			$private    = apply_filters( 'mphb_rest_private_query_vars', $wp->private_query_vars );
			$valid_vars = array_merge( $valid_vars, $private );
		}
		// Define our own in addition to WP's normal vars.
		$rest_valid = array(
			'date_query',
			'ignore_sticky_posts',
			'offset',
			'post__in',
			'post__not_in',
			'post_parent',
			'post_parent__in',
			'post_parent__not_in',
			'posts_per_page',
			'meta_query',
			'tax_query',
			'meta_key',
			'meta_value',
			'meta_compare',
			'meta_value_num',
		);
		$valid_vars = array_merge( $valid_vars, $rest_valid );

		/**
		 * Filter allowed query vars for the REST API.
		 *
		 * This filter allows you to add or remove query vars from the final allowed
		 * list for all requests, including unauthenticated ones. To alter the
		 * vars for editors only.
		 *
		 * @param array {
		 *    Array of allowed WP_Query query vars.
		 *
		 *    @param string $allowed_query_var The query var to allow.
		 * }
		 */
		$valid_vars = apply_filters( 'mphb_rest_query_vars', $valid_vars );

		return $valid_vars;
	}

	/**
	 * Get the query params for collections of attachments.
	 *
	 * @return array
	 */
	public function get_collection_params() {
		$params = parent::get_collection_params();

		$params['context']['default'] = 'view';

		$params['after']   = array(
			'description'       => 'Limit response to resources published after a given ISO8601 compliant date.',
			'type'              => 'string',
			'format'            => 'date',
			'validate_callback' => 'rest_validate_request_arg',
		);
		$params['before']  = array(
			'description'       => 'Limit response to resources published before a given ISO8601 compliant date.',
			'type'              => 'string',
			'format'            => 'date',
			'validate_callback' => 'rest_validate_request_arg',
		);
		$params['exclude'] = array(
			'description'       => 'Ensure result set excludes specific IDs.',
			'type'              => 'array',
			'items'             => array(
				'type' => 'integer',
			),
			'default'           => array(),
			'sanitize_callback' => 'wp_parse_id_list',
		);
		$params['include'] = array(
			'description'       => 'Limit result set to specific ids.',
			'type'              => 'array',
			'items'             => array(
				'type' => 'integer',
			),
			'default'           => array(),
			'sanitize_callback' => 'wp_parse_id_list',
		);
		$params['offset']  = array(
			'description'       => 'Offset the result set by a specific number of items.',
			'type'              => 'integer',
			'sanitize_callback' => 'absint',
			'validate_callback' => 'rest_validate_request_arg',
		);
		$params['order']   = array(
			'description'       => 'Order sort attribute ascending or descending.',
			'type'              => 'string',
			'default'           => 'desc',
			'enum'              => array( 'asc', 'desc' ),
			'validate_callback' => 'rest_validate_request_arg',
		);
		$params['orderby'] = array(
			'description'       => 'Sort collection by object attribute.',
			'type'              => 'string',
			'default'           => 'date',
			'enum'              => array(
				'date',
				'id',
				'include',
				'title',
				'slug',
				'modified',
			),
			'validate_callback' => 'rest_validate_request_arg',
		);

		$post_type_obj = get_post_type_object( $this->post_type );

		if ( isset( $post_type_obj->hierarchical ) && $post_type_obj->hierarchical ) {
			$params['parent']         = array(
				'description'       => 'Limit result set to those of particular parent IDs.',
				'type'              => 'array',
				'items'             => array(
					'type' => 'integer',
				),
				'sanitize_callback' => 'wp_parse_id_list',
				'default'           => array(),
			);
			$params['parent_exclude'] = array(
				'description'       => 'Limit result set to all items except those of a particular parent ID.',
				'type'              => 'array',
				'items'             => array(
					'type' => 'integer',
				),
				'sanitize_callback' => 'wp_parse_id_list',
				'default'           => array(),
			);
		}

		$params['filter'] = array(
			'type'        => 'object',
			'description' => 'Use WP Query arguments to modify the response; private query vars require appropriate authorization.',
		);

		return $params;
	}
}
