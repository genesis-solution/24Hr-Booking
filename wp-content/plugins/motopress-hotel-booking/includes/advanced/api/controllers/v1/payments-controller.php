<?php
/**
 * @package MPHB\Advanced\Api
 * @since 4.1.0
 */

namespace MPHB\Advanced\Api\Controllers\V1;

use MPHB\Advanced\Api\Controllers\AbstractRestObjectController;
use MPHB\Advanced\Api\Data\PaymentData;
use WP_Error;
use WP_REST_Request;
use WP_REST_Response;

class PaymentsController extends AbstractRestObjectController {


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
	protected $rest_base = 'payments';

	/**
	 * Post type.
	 *
	 * @var string
	 */
	protected $post_type = 'mphb_payment';

	/**
	 * Get a collection of payments.
	 *
	 * @param  WP_REST_Request $request  Full details about the request.
	 *
	 * @return WP_Error|WP_REST_Response
	 */
	public function get_items( $request ) {
		if ( isset( $request['search'] ) ) {
			add_filter( 'posts_join', array( $this, 'extendSearchPostsJoin' ), 10, 2 );
			add_filter( 'posts_search', array( $this, 'extendPostsSearch' ), 10, 2 );
			add_filter( 'posts_distinct', array( $this, 'searchDistinct' ), 10 );
		}

		return parent::get_items( $request );
	}

	/**
	 *
	 * @param  string    $where
	 * @param  \WP_Query $wp_query
	 *
	 * @return string
	 * @global \WPDB $wpdb
	 */
	public function extendPostsSearch( $where, $wp_query ) {
		global $wpdb;

		if ( ! empty( $wp_query->query['s'] ) ) {

			$search = trim( $wp_query->query['s'] );

			$customWhere = '';

			if ( is_email( $search ) ) {
				$joinCount = $wp_query->get( '_mphb_join_meta', 0 ) + 1;
				$wp_query->set( '_mphb_join_meta', $joinCount );

				$customWhere = $wpdb->prepare(
					"( mphb_postmeta_{$joinCount}.meta_key = %s AND CAST( mphb_postmeta_{$joinCount}.meta_value as CHAR ) = %s )",
					'_mphb_email',
					$search
				);
			} elseif ( is_numeric( $search ) ) {
				if ( get_post_type( $search ) === MPHB()->postTypes()->booking()->getPostType() ) {
					$joinCount = $wp_query->get( '_mphb_join_meta', 0 ) + 1;
					$wp_query->set( '_mphb_join_meta', $joinCount );

					$customWhere = $wpdb->prepare(
						"( mphb_postmeta_{$joinCount}.meta_key = %s AND CAST( mphb_postmeta_{$joinCount}.meta_value as CHAR ) = %s )",
						'_mphb_booking_id',
						$search
					);
				} elseif ( get_post_type( $search ) === MPHB()->postTypes()->payment()->getPostType() ) {
					$customWhere = $wpdb->prepare( "($wpdb->posts.ID = %d)", (int) $search );
				}
			}

			if ( ! empty( $customWhere ) ) {
				$where = " AND ({$customWhere}) ";
			}
		}

		return $where;
	}

	/**
	 *
	 * @param  string    $join
	 * @param  \WP_Query $wp_query
	 *
	 * @return string
	 * @global \WPDB $wpdb
	 */
	public function extendSearchPostsJoin( $join, $wp_query ) {
		global $wpdb;

		if ( ! empty( $wp_query->query['s'] ) ) {
			$joinCount = (int) $wp_query->get( '_mphb_join_meta', 0 );
			for ( $i = 1; $i <= $joinCount; $i ++ ) {
				$join .= " LEFT JOIN $wpdb->postmeta AS mphb_postmeta_{$i} ON $wpdb->posts.ID = mphb_postmeta_{$i}.post_id ";
			}
		}

		return $join;
	}

	/**
	 * Prevent duplicates
	 *
	 * @return string
	 */
	function searchDistinct() {
		return 'DISTINCT';
	}

	/**
	 * Prepare links for the request.
	 *
	 * @param  PaymentData     $bookingData  Booking data object.
	 * @param  WP_REST_Request $request  Request object.
	 *
	 * @return array Links for the given post.
	 */
	protected function prepare_links( $paymentData, $request ) {
		$links = parent::prepare_links( $paymentData, $request );

		if ( $paymentData->booking_id ) {
			$links['booking_id'] = array(
				'href' => rest_url( sprintf( '/%s/%s/%d', $this->namespace, 'bookings', $paymentData->booking_id ) ),
			);
		}

		return $links;
	}
}
