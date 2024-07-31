<?php
/**
 * @package MPHB\Advanced\Api
 * @since 4.1.0
 */

namespace MPHB\Advanced\Api\Controllers\V1;

use MPHB\Advanced\Api\ApiHelper;
use MPHB\Advanced\Api\Controllers\AbstractRestController;
use WP_Error;
use WP_REST_Request;
use WP_REST_Response;
use WP_REST_Server;

class BookingAvailabilityController extends AbstractRestController {

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
	protected $rest_base = 'bookings/availability';

	/**
	 * Route base.
	 *
	 * @var string
	 */
	protected $post_type = 'mphb_booking';

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
					'callback'            => array( $this, 'get_item' ),
					'permission_callback' => array( $this, 'get_item_permissions_check' ),
					'args'                => $this->get_endpoint_args_for_item_schema(),
				),
				'schema' => array( $this, 'get_public_item_schema' ),
			)
		);
	}

	/**
	 * Get the item schema, conforming to JSON Schema of endpoint.
	 *
	 * @return array
	 */
	public function get_item_schema() {
		$schema = array(
			'$schema'    => 'http://json-schema.org/draft-04/schema#',
			'title'      => 'booking_availability',
			'type'       => 'object',
			'properties' => array(
				'check_in_date'      => array(
					'description' => sprintf( 'Check in date as %s.', MPHB()->settings()->dateTime()->getDateTransferFormat() ),
					'type'        => 'string',
					'format'      => 'date',
					'context'     => array( 'view' ),
					'required'    => true,
				),
				'check_out_date'     => array(
					'description' => sprintf( 'Check out date as %s.', MPHB()->settings()->dateTime()->getDateTransferFormat() ),
					'type'        => 'string',
					'format'      => 'date',
					'context'     => array( 'view' ),
					'required'    => true,
				),
				'accommodation_type' => array(
					'description' => 'Accommodation Type id. Enter 0 to select all.',
					'type'        => 'integer',
					'minimum'     => 0,
					'default'     => 0,
					'context'     => array( 'view' ),
				),
				'adults'             => array(
					'description' => 'Count of adults.',
					'type'        => 'integer',
					'minimum'     => 0,
					'default'     => 1,
					'context'     => array( 'view' ),
				),
				'children'           => array(
					'description' => 'Count of children.',
					'type'        => 'integer',
					'minimum'     => 0,
					'default'     => 0,
					'context'     => array( 'view' ),
				),
				'availability'       => array(
					'type'    => 'array',
					'context' => array( 'view' ),
					'items'   => array(
						'type'       => 'object',
						'title'      => 'Accommodations',
						'properties' => array(
							'accommodation_type' => array(
								'description' => 'Accommodation Type id.',
								'type'        => 'integer',
							),
							'title'              => array(
								'description' => 'Title.',
								'type'        => 'string',
							),
							'base_price'         => array(
								'description' => 'Base price.',
								'type'        => 'number',
							),
							'accommodations'     => array(
								'type'  => 'array',
								'items' => array(
									'type'       => 'object',
									'title'      => 'Accommodations',
									'properties' => array(
										'id'    => array(
											'description' => 'Accommodation id.',
											'type'        => 'integer',
										),
										'title' => array(
											'description' => 'Title.',
											'type'        => 'string',
										),
									),
								),
							),
						),
					),
				),
			),
		);

		return $this->add_additional_fields_schema( $schema );
	}

	/**
	 * Check if a given request has access to read items.
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 *
	 * @return WP_Error|boolean
	 */
	public function get_item_permissions_check( $request ) {
		if ( ! ApiHelper::checkPostPermissions( $this->post_type, 'read' ) ) {
			return new WP_Error(
				'mphb_rest_cannot_view',
				'Sorry, you cannot list resources.',
				array( 'status' => rest_authorization_required_code() )
			);
		}

		return true;
	}

	/**
	 * @param array           $data
	 * @param WP_REST_Request $request
	 *
	 * @return WP_Error|WP_REST_Response
	 */
	public function prepare_item_for_response( $data, $request ) {
		$context = ! empty( $request['context'] ) ? $request['context'] : 'view';

		$data     = $this->add_additional_fields_to_object( $data, $request );
		$data     = $this->filter_response_by_context( $data, $context );
		$response = rest_ensure_response( $data );
		$response->add_links( $this->prepare_links( $data['availability'], $request ) );

		/**
		 * Filter the data for a response.
		 *
		 * The dynamic portion of the hook name, $this->post_type, refers to post_type of the post being
		 * prepared for the response.
		 *
		 * @param WP_REST_Response $response The response object.
		 * @param mixed $post Entity object.
		 * @param WP_REST_Request $request Request object.
		 */
		return apply_filters( "mphb_rest_prepare_{$this->post_type}", $response, $data, $request );
	}

	/**
	 * Get a single item.
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 *
	 * @return WP_Error|WP_REST_Response
	 */
	public function get_item( $request ) {
		$checkInDate         = ApiHelper::prepareDateRequest( $request['check_in_date'] );
		$checkOutDate        = ApiHelper::prepareDateRequest( $request['check_out_date'] );
		$accommodationTypeId = $request['accommodation_type'];
		$adults              = $request['adults'];
		$children            = $request['children'];

		if ( $accommodationTypeId && is_null( MPHB()->getRoomTypePersistence()->getPost( $accommodationTypeId ) ) ) {
			return new WP_Error(
				'mphb_rest_invalid_accommodation_type',
				'Invalid ID.',
				array( 'status' => 400 )
			);
		}

		$data = array(
			'check_in_date'      => ApiHelper::prepareDateResponse( $checkInDate ),
			'check_out_date'     => ApiHelper::prepareDateResponse( $checkOutDate ),
			'accommodation_type' => $accommodationTypeId,
			'adults'             => $adults,
			'children'           => $children,
			'availability'       => $this->getAvailability( $checkInDate, $checkOutDate, $accommodationTypeId, $adults, $children ),
		);

		$data     = $this->prepare_item_for_response( $data, $request );
		$response = rest_ensure_response( $data );

		return $response;
	}

	/**
	 * @param $checkInDate \DateTime
	 * @param $checkOutDate \DateTime
	 * @param $accommodationTypeId integer
	 *
	 * @return array[][
	 *  'type_id' => '0'
	 *  'type_title' => '',
	 *  'accommodation_id' = '0',
	 *  'accommodation_title' => ''
	 * ]
	 */
	protected function getAllUnlockedAccommodations(
		\DateTime $checkInDate,
		\DateTime $checkOutDate,
		int $accommodationTypeId = 0
	) {
		global $wpdb;

		$lockedAccommodation = MPHB()->getRoomRepository()->getLockedRooms(
			$checkInDate,
			$checkOutDate,
			$accommodationTypeId,
			array( 'skip_buffer_rules' => false )
		);

		$query = 'SELECT accommodation_type_id.meta_value AS type_id, accommodation_types.post_title AS type_title, accommodations.ID AS accommodation_id, accommodations.post_title AS accommodation_title '
				 . "FROM $wpdb->posts AS accommodations "

				 . "INNER JOIN $wpdb->postmeta AS accommodation_type_id "
				 . 'ON accommodations.ID = accommodation_type_id.post_id '
				 . "INNER JOIN $wpdb->posts AS accommodation_types "
				 . 'ON accommodation_type_id.meta_value = accommodation_types.ID '

				 . "WHERE accommodations.post_type = '" . MPHB()->postTypes()->room()->getPostType() . "' "
				 . "AND accommodations.post_status = 'publish' "
				 . "AND accommodation_type_id.meta_key = 'mphb_room_type_id' "
				 . "AND accommodation_types.post_status = 'publish' "
				 . "AND accommodation_types.post_type = '" . MPHB()->postTypes()->roomType()->getPostType() . "' ";

		if ( ! empty( $lockedAccommodation ) ) {
			$query .= 'AND accommodations.ID NOT IN (' . join( ',', $lockedAccommodation ) . ') ';
		}

		if ( $accommodationTypeId > 0 ) {
			$query .= "AND accommodation_type_id.meta_value = '$accommodationTypeId' ";
		} else {
			$query .= 'AND accommodation_type_id.meta_value IS NOT NULL '
					  . "AND accommodation_type_id.meta_value <> '' ";
		}

		return $wpdb->get_results( $query, ARRAY_A );
	}

	/**
	 * @param $checkInDate \DateTime
	 * @param $checkOutDate \DateTime
	 * @param $accommodationTypeId integer
	 * @param int                         $adults
	 * @param int                         $children
	 *
	 * @return array[]{
	 *  'accommodation_type': int,
	 *  'title': string,
	 *  'base_price': float,
	 *  'accommodations': array[]{'id': int, 'title': string}
	 *
	 * Will always return original
	 *     IDs because of direct query to the DB.
	 *
	 * @global \wpdb $wpdb
	 */
	protected function getAvailability(
		\DateTime $checkInDate,
		\DateTime $checkOutDate,
		int $accommodationTypeId = 0,
		int $adults = 1,
		int $children = 0
	) {

		$unlockedAccommodations  = $this->getAllUnlockedAccommodations( $checkInDate, $checkOutDate, $accommodationTypeId );
		$availableAccommodations = array();

		foreach ( $unlockedAccommodations as $unlockedAccommodation ) {
			$accommodationTypeId = intval( $unlockedAccommodation['type_id'] );
			$accommodationId     = intval( $unlockedAccommodation['accommodation_id'] );

			// skip accommodations which don't match to booking rules

			if ( ! isset( $availableAccommodations[ $accommodationTypeId ] ) &&
				 ( ! $this->isAvailableAccommodationTypeByRates( $checkInDate, $checkOutDate, $accommodationTypeId ) ||
				   ! $this->isAvailableAccommodationTypeByCapacity( $accommodationTypeId, $adults, $children ) ||
				   ! $this->isAvailableAccommodationTypeByBookingRules( $checkInDate, $checkOutDate, $accommodationTypeId ) )
			) {
				continue;
			}

			if ( ! $this->isAvailableAccommodationByBlockedRules( $checkInDate, $checkOutDate, $accommodationTypeId, $accommodationId ) ) {
				continue;
			}

			if ( ! isset( $availableAccommodations[ $accommodationTypeId ] ) ) {
				$availableAccommodations[ $accommodationTypeId ] = array(
					'accommodation_type' => $accommodationTypeId,
					'title'              => $unlockedAccommodation['type_title'],
					'base_price'         => mphb_get_room_type_period_price( $checkInDate, $checkOutDate, $accommodationTypeId ),
				);
			}

			$availableAccommodations[ $accommodationTypeId ]['accommodations'][] = array(
				'id'    => $accommodationId,
				'title' => $unlockedAccommodation['accommodation_title'],
			);
		}

		return array_values( $availableAccommodations );
	}

	/**
	 * Prepare links for the request.
	 *
	 * @param array $availability
	 *
	 * @return array
	 */
	protected function prepare_links( $availability ) {
		$links = array(
			'self' => array(
				'href' => rest_url( sprintf( '/%s/%s', $this->namespace, $this->rest_base ) ),
			),
		);
		if ( ! count( $availability ) ) {
			return $links;
		}
		foreach ( $availability as $available ) {
			$links['accommodation_types'][] = array(
				'href'       => rest_url(
					sprintf(
						'/%s/%s/%d',
						$this->namespace,
						'accommodation_types',
						$available['accommodation_type']
					)
				),
				'embeddable' => true,
			);
			foreach ( $available['accommodations'] as $accommodation ) {
				$links['accommodations'][] = array(
					'href'       => rest_url(
						sprintf(
							'/%s/%s/%d',
							$this->namespace,
							'accommodations',
							$accommodation['id']
						)
					),
					'embeddable' => true,
				);
			}
		}

		return $links;
	}

	/**
	 * @param $checkInDate \DateTime
	 * @param $checkOutDate \DateTime
	 * @param $accommodationTypeId integer
	 *
	 * @return bool
	 */
	protected function isAvailableAccommodationTypeByRates(
		\DateTime $checkInDate,
		\DateTime $checkOutDate,
		int $accommodationTypeId = 0
	) {
		$rateSearchAtts = array(
			'check_in_date'  => $checkInDate,
			'check_out_date' => $checkOutDate,
		);

		if ( ! MPHB()->getRateRepository()->isExistsForRoomType( $accommodationTypeId, $rateSearchAtts ) ) {
			return false;
		}

		return true;
	}

	/**
	 * @param $accommodationTypeId integer
	 * @param $adults integer
	 * @param $children integer
	 *
	 * @return bool
	 */
	protected function isAvailableAccommodationTypeByCapacity(
		int $accommodationTypeId,
		int $adults,
		int $children
	) {
		$accommodationType = MPHB()->getRoomTypeRepository()->findById( $accommodationTypeId );

		if ( is_null( $accommodationType ) || $accommodationType->getAdultsCapacity() < $adults || $accommodationType->getChildrenCapacity() < $children ) {
			return false;
		}

		return true;
	}

	/**
	 * @param $checkInDate \DateTime
	 * @param $checkOutDate \DateTime
	 * @param $accommodationTypeId integer
	 *
	 * @return bool
	 */
	protected function isAvailableAccommodationTypeByBookingRules(
		\DateTime $checkInDate,
		\DateTime $checkOutDate,
		$accommodationTypeId = 0
	) {
		$rules = MPHB()->getRulesChecker();

		if ( ! $rules->verify( $checkInDate, $checkOutDate, $accommodationTypeId ) ) {
			return false;
		}

		return true;
	}

	/**
	 * @param $checkInDate \DateTime
	 * @param $checkOutDate \DateTime
	 * @param $accommodationTypeId integer
	 * @param $accommodationId integer
	 *
	 * @return bool
	 */
	protected function isAvailableAccommodationByBlockedRules(
		\DateTime $checkInDate,
		\DateTime $checkOutDate,
		int $accommodationTypeId,
		int $accommodationId
	) {
		$unavailableAccommodations = MPHB()->getRulesChecker()->customRules()->getUnavailableRooms( $checkInDate, $checkOutDate, $accommodationTypeId );

		if ( in_array( $accommodationId, $unavailableAccommodations ) ) {
			return false;
		}

		return true;
	}
}
