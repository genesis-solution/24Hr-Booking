<?php
/**
 * @package MPHB\Advanced\Api
 * @since 4.1.0
 */

namespace MPHB\Advanced\Api\Controllers\V1;

use MPHB\Advanced\Api\Controllers\AbstractRestObjectController;
use MPHB\Advanced\Api\Data\BookingData;
use MPHB\Utils\DateUtils;
use WP_Error;
use WP_REST_Request;
use WP_REST_Response;

class BookingsController extends AbstractRestObjectController {


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
	protected $rest_base = 'bookings';

	/**
	 * Post type.
	 *
	 * @var string
	 */
	protected $post_type = 'mphb_booking';

	/**
	 * Get a collection of bookings.
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
	 * Replace the search in post_title, post_excerpt and post_content.
	 *
	 * @param  string    $where
	 * @param  \WP_Query $query
	 *
	 * @return string
	 *
	 * @global \WPDB $wpdb
	 */
	public function extendPostsSearch( $where, $query ) {
		global $wpdb;

		if ( ! $this->isBookingsQuery( $query ) ) {
			return $where;
		}

		$search = isset( $query->query['s'] ) ? trim( $query->query['s'] ) : '';

		// Apply search filter
		if ( $search !== '' ) {
			$query->set( 'mphb_join_booking_meta', true );

			$alternatives = array();

			// Search by ID and price
			if ( is_numeric( $search ) ) {
				$id = intval( $search );

				$price = mphb_format_price(
					floatval( $search ),
					array(
						'as_html'         => false,
						'currency_symbol' => '',
					)
				);
				$price = mphb_trim_decimal_zeros( $price );

				$alternatives[] = $wpdb->prepare( "{$wpdb->posts}.ID = %d", $id );
				$alternatives[] = $wpdb->prepare(
					"(mphb_bookmeta.meta_key = 'mphb_total_price' AND mphb_bookmeta.meta_value = %s)",
					$price
				);
			}

			// Search any other match
			$searchVariants = array( $search );

			if ( DateUtils::isDate( $search ) ) {
				$searchVariants[] = DateUtils::convertDateFormat(
					$search,
					MPHB()->settings()->dateTime()->getDateFormat(),
					MPHB()->settings()->dateTime()->getDateTransferFormat()
				);
			}

			$countryCode = MPHB()->settings()->main()->getCountriesBundle()->getCountryCode( $search );

			if ( $countryCode !== false ) {
				$searchVariants[] = $countryCode;
			}

			if ( count( $searchVariants ) == 1 ) {
				// The $search is neither date, nor country code
				$alternatives[] = $wpdb->prepare(
					"(mphb_bookmeta.meta_key LIKE 'mphb_%' AND mphb_bookmeta.meta_value = %s)",
					$search
				);
			} else {
				// The $search may be date, country code or both
				$searchVariants = esc_sql( $searchVariants );
				$searchValues   = "'" . implode( "', '", $searchVariants ) . "'";

				$alternatives[] = "(mphb_bookmeta.meta_key LIKE 'mphb_%' AND mphb_bookmeta.meta_value IN ({$searchValues}))";
			}

			// Add all alternatives to WHERE statement
			$where = ' AND (' . implode( ' OR ', $alternatives ) . ')';
		}

		// Apply accommodation filter
		if ( ! empty( $_GET['mphb_room_type_id'] ) ) {
			$query->set( 'mphb_join_reserved_rooms', true );

			$roomTypeId = absint( $_GET['mphb_room_type_id'] );
			$where     .= $wpdb->prepare( ' AND mphb_rooms_meta.meta_value = %s', $roomTypeId );
		}

		return $where;
	}

	public function isBookingsQuery( $query ) {
		return isset( $query->query['post_type'] )
			   && $query->query['post_type'] === MPHB()->postTypes()->booking()->getPostType();
	}

	/**
	 * @param  string    $join
	 * @param  \WP_Query $query
	 *
	 * @return string
	 *
	 * @global \WPDB $wpdb
	 */
	public function extendSearchPostsJoin( $join, $query ) {
		global $wpdb;

		if ( ! $this->isBookingsQuery( $query ) ) {
			return $join;
		}

		$search = isset( $query->query['s'] ) ? trim( $query->query['s'] ) : '';

		$joinBookingMeta   = (bool) $query->get( 'mphb_join_booking_meta', false );
		$joinReservedRooms = (bool) $query->get( 'mphb_join_reserved_rooms', false );

		// Add join for search
		if ( $search !== '' && $joinBookingMeta ) {
			$join .= " LEFT JOIN {$wpdb->postmeta} AS mphb_bookmeta ON {$wpdb->posts}.ID = mphb_bookmeta.post_id ";
		}

		// Add joins for accommodation filter
		if ( ! empty( $_GET['mphb_room_type_id'] ) && $joinReservedRooms ) {
			$join .= " INNER JOIN {$wpdb->posts} AS mphb_reserved_rooms ON {$wpdb->posts}.ID = mphb_reserved_rooms.post_parent"
					 . " INNER JOIN {$wpdb->postmeta} AS mphb_reserved_rooms_meta ON mphb_reserved_rooms.ID = mphb_reserved_rooms_meta.post_id AND mphb_reserved_rooms_meta.meta_key = '_mphb_room_id'"
					 . " INNER JOIN {$wpdb->posts} AS mphb_rooms ON mphb_reserved_rooms_meta.meta_value = mphb_rooms.ID"
					 . " INNER JOIN {$wpdb->postmeta} AS mphb_rooms_meta ON mphb_rooms.ID = mphb_rooms_meta.post_id AND mphb_rooms_meta.meta_key = 'mphb_room_type_id'";
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
	 * @param  BookingData     $bookingData  Booking data object.
	 * @param  WP_REST_Request $request  Request object.
	 *
	 * @return array Links for the given post.
	 */
	protected function prepare_links( $bookingData, $request ) {
		$links = parent::prepare_links( $bookingData, $request );

		$payments = $bookingData->payments;
		if ( count( $payments ) ) {
			$links['payments'] = array_map(
				function ( $id ) {
					return array(
						'href'       => rest_url( sprintf( '/%s/%s/%d', $this->namespace, 'payments', $id ) ),
						'embeddable' => true,
					);
				},
				wp_list_pluck( $payments, 'id' )
			);
		}

		if ( $bookingData->status !== \MPHB\PostTypes\BookingCPT\Statuses::STATUS_CONFIRMED && ! $bookingData->payments ) {
			$links['payments'] = array(
				'href' => rest_url( sprintf( '/%s/%s', $this->namespace, 'payments' ) ),
			);
		}

		$reservedAccommodations = $bookingData->reserved_accommodations;
		if ( count( $reservedAccommodations ) ) {
			$links['accommodation'] = array_map(
				function( $id ) {
					return array(
						'href'       => rest_url( sprintf( '/%s/%s/%d', $this->namespace, 'accommodations', $id ) ),
						'embeddable' => true,
					);
				},
				wp_list_pluck( $reservedAccommodations, 'accommodation' )
			);

			$links['accommodation_type'] = array_map(
				function( $id ) {
					return array(
						'href'       => rest_url( sprintf( '/%s/%s/%d', $this->namespace, 'accommodation_types', $id ) ),
						'embeddable' => true,
					);
				},
				wp_list_pluck( $reservedAccommodations, 'accommodation_type' )
			);

			$links['rate'] = array_map(
				function( $id ) {
					return array(
						'href'       => rest_url( sprintf( '/%s/%s/%d', $this->namespace, 'rates', $id ) ),
						'embeddable' => true,
					);
				},
				array_filter( wp_list_pluck( $reservedAccommodations, 'rate' ) )
			);

			$links['services'] = array_map(
				function( $id ) {
					return array(
						'href'       => rest_url( sprintf( '/%s/%s/%s/%d', $this->namespace, 'accommodation_types', 'services', $id ) ),
						'embeddable' => true,
					);
				},
				$this->getServiceIds( $reservedAccommodations )
			);
		}

		return $links;
	}

	private function getServiceIds( $reservedAccommodationsResponse ) {
		$services = array();
		foreach ( $reservedAccommodationsResponse as $reservedAccommodation ) {
			if ( isset( $reservedAccommodation['services'] ) ) {
				$services = array_merge( $services, wp_list_pluck( $reservedAccommodation['services'], 'id' ) );
			}
		}

		return array_unique( $services );
	}
}
