<?php
/**
 * @package MPHB\Advanced\Api
 * @since 4.1.0
 */

namespace MPHB\Advanced\Api\Controllers\V1;

use MPHB\Advanced\Api\Controllers\AbstractRestObjectController;
use MPHB\Advanced\Api\Data\CouponData;
use WP_REST_Request;

class CouponsController extends AbstractRestObjectController {


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
	protected $rest_base = 'coupons';

	/**
	 * Post type.
	 *
	 * @var string
	 */
	protected $post_type = 'mphb_coupon';

	/**
	 * Prepare links for the request.
	 *
	 * @param  CouponData      $couponData  Coupon data object.
	 * @param  WP_REST_Request $request  Request object.
	 *
	 * @return array Links for the given post.
	 */
	protected function prepare_links( $couponData, $request ) {
		$links = parent::prepare_links( $couponData, $request );

		$accommodationTypes = $couponData->accommodation_types;
		if ( count( $accommodationTypes ) ) {
			$accommodationTypes = array_unique( $accommodationTypes );
			foreach ( $accommodationTypes as $accommodationTypeId ) {
				$links['accommodation_types'][] = array(
					'href'       => rest_url(
						sprintf(
							'/%s/%s/%d',
							$this->namespace,
							'accommodation_types',
							$accommodationTypeId
						)
					),
					'embeddable' => true,
				);
			}
		}

		return $links;
	}
}
