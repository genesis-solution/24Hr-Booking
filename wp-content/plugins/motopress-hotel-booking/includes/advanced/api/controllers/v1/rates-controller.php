<?php
/**
 * @package MPHB\Advanced\Api
 * @since 4.1.0
 */

namespace MPHB\Advanced\Api\Controllers\V1;

use MPHB\Advanced\Api\Controllers\AbstractRestObjectController;
use MPHB\Advanced\Api\Data\RateData;

class RatesController extends AbstractRestObjectController {


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
	protected $rest_base = 'rates';

	/**
	 * Post type.
	 *
	 * @var string
	 */
	protected $post_type = 'mphb_rate';

	/**
	 * Prepare links for the request.
	 *
	 * @param  RateData         $rateData  Rate data object.
	 * @param  \WP_REST_Request $request  Request object.
	 *
	 * @return array Links for the given post.
	 */
	protected function prepare_links( $rateData, $request ) {
		$links = parent::prepare_links( $rateData, $request );

		$links['accommodation_type_id'] = array(
			'href'       => rest_url(
				sprintf(
					'/%s/%s/%d',
					$this->namespace,
					'accommodation_types',
					$rateData->accommodation_type_id
				)
			),
			'embeddable' => true,
		);

		$seasonIds = $rateData->getSeasonIds();
		if ( count( $seasonIds ) ) {
			$seasonIds = array_unique( $seasonIds );
			foreach ( $seasonIds as $seasonId ) {
				$links['season_id'][] = array(
					'href'       => rest_url( sprintf( '/%s/%s/%d', $this->namespace, 'seasons', $seasonId ) ),
					'embeddable' => true,
				);
			}
		}

		return $links;
	}
}
