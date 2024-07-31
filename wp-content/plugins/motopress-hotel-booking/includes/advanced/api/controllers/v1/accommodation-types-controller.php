<?php
/**
 * @package MPHB\Advanced\Api
 * @since 4.1.0
 */

namespace MPHB\Advanced\Api\Controllers\V1;

use MPHB\Advanced\Api\Controllers\AbstractRestObjectController;
use MPHB\Advanced\Api\Data\AccommodationTypeData;

class AccommodationTypesController extends AbstractRestObjectController {


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
	protected $rest_base = 'accommodation_types';

	/**
	 * Post type.
	 *
	 * @var string
	 */
	protected $post_type = 'mphb_room_type';

	/**
	 * Prepare links for the request.
	 *
	 * @param  AccommodationTypeData $accommodationTypeData  Rate data object.
	 * @param  \WP_REST_Request      $request  Request object.
	 *
	 * @return array Links for the given post.
	 */
	protected function prepare_links( $accommodationTypeData, $request ) {
		$links = parent::prepare_links( $accommodationTypeData, $request );

		$embedEndpoints = array( 'services', 'amenities', 'categories', 'tags', 'images' );

		foreach ( $embedEndpoints as $embedEndpoint ) {
			$ids = wp_list_pluck( $accommodationTypeData->{$embedEndpoint}, 'id' );
			if ( count( $ids ) ) {
				foreach ( $ids as $id ) {
					$links[ $embedEndpoint ][] = array(
						'href' => rest_url(
							sprintf( '/%s/%s/%s/%d', $this->namespace, $this->rest_base, $embedEndpoint, $id )
						),
					);
				}
			}
		}

		return $links;
	}
}
