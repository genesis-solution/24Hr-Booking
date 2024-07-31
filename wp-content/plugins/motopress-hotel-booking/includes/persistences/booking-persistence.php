<?php

namespace MPHB\Persistences;

class BookingPersistence extends CPTPersistence {

	/**
	 * @param array  $atts Optional.
	 * @param bool   $atts['room_locked'] Optional. Whether get only bookings that locked room.
	 * @param string $atts['date_from'] Optional. Date in 'Y-m-d' format. Retrieve only bookings that consist dates from period begins at this date.
	 * @param string $atts['date_to'] Optional. Date in 'Y-m-d' format. Retrieve only bookings that consist dates from period ends at this date.
	 * @param bool   $atts['period_edge_overlap'] Optional. Whether the edge days of period are overlapping.
	 * @param array  $atts['rooms'] Optional. Room Ids.
	 *
	 * @return WP_Post[]|int[] List of posts.
	 */
	public function getPosts( $atts = array() ) {

		return parent::getPosts( $atts );
	}

	/**
	 * @param string    $where
	 * @param \WP_Query $wp_query
	 */
	public function _customizeGetPostsWhere( $where, $wp_query ) {

		$where = parent::_customizeGetPostsWhere( $where, $wp_query );

		$rooms = $wp_query->get( 'mphb_rooms' );
		if ( ! empty( $rooms ) ) {
			$rooms  = "'" . join( "','", $rooms ) . "'";
			$where .= " AND mphb_reserved_room_room_id.meta_key = '_mphb_room_id'
							AND mphb_reserved_room_room_id.meta_value IN ( $rooms )";
		}
		return $where;
	}

	/**
	 * @global \WPDB $wpdb
	 * @param string    $where
	 * @param \WP_Query $wp_query
	 * @return string
	 */
	public function _customizeGetPostsJoin( $join, $wp_query ) {

		$join = parent::_customizeGetPostsJoin( $join, $wp_query );

		$rooms = $wp_query->get( 'mphb_rooms' );

		if ( ! empty( $rooms ) ) {

			global $wpdb;

			$join .= " INNER JOIN $wpdb->posts AS mphb_reserved_rooms
						ON mphb_reserved_rooms.post_parent = $wpdb->posts.ID ";
			$join .= " INNER JOIN $wpdb->postmeta AS mphb_reserved_room_room_id
						ON mphb_reserved_rooms.ID = mphb_reserved_room_room_id.post_id ";
		}
		return $join;
	}

	/**
	 * @param string    $distinct
	 * @param \WP_Query $wp_query
	 * @return string
	 */
	public function _customizeGetPostsDistinct( $distinct, $wp_query ) {

		$distinct = parent::_customizeGetPostsDistinct( $distinct, $wp_query );

		$rooms = $wp_query->get( 'mphb_rooms' );
		if ( ! empty( $rooms ) ) {
			$distinct = 'DISTINCT';
		}
		return $distinct;
	}

	/**
	 * @param array $customAtts Optional. Empty array by default.
	 * @return array
	 *
	 * @since 3.7.0 added optional parameter $customAtts.
	 */
	protected function getDefaultQueryAtts( $customAtts = array() ) {

		$atts = array_merge(
			array(
				'post_status' => array_keys( MPHB()->postTypes()->booking()->statuses()->getStatuses() ),
			),
			$customAtts
		);

		return parent::getDefaultQueryAtts( $atts );
	}

	protected function modifyQueryAtts( $atts ) {

		$atts = $this->_addRoomLockedCriteria( $atts );
		$atts = $this->_addPendingUserExpiredCriteria( $atts );
		$atts = $this->_addPendingPaymentExpiredCriteria( $atts );
		$atts = $this->_addPeriodCriteria( $atts );
		$atts = $this->_addRoomsCriteria( $atts );
		$atts = parent::modifyQueryAtts( $atts );

		return $atts;
	}

	private function _addRoomLockedCriteria( $atts ) {

		if ( isset( $atts['room_locked'] ) && $atts['room_locked'] ) {

			$atts['post_status'] = MPHB()->postTypes()->booking()->statuses()->getLockedRoomStatuses();
			unset( $atts['room_locked'] );
		}
		return $atts;
	}

	private function _addPendingUserExpiredCriteria( $atts ) {

		if ( isset( $atts['pending_user_expired'] ) && $atts['pending_user_expired'] ) {

			$atts['post_status'] = array( \MPHB\PostTypes\BookingCPT\Statuses::STATUS_PENDING_USER );

			$queryPart = array(
				'key'     => 'mphb_pending_user_expired',
				'value'   => time(),
				'type'    => 'NUMERIC',
				'compare' => '<=',
			);

			$atts['meta_query'] = mphb_add_to_meta_query( $queryPart, isset( $atts['meta_query'] ) ? $atts['meta_query'] : null );

			unset( $atts['pending_user_expired'] );
		}
		return $atts;
	}

	private function _addPendingPaymentExpiredCriteria( $atts ) {

		if ( isset( $atts['pending_payment_expired'] ) && $atts['pending_payment_expired'] ) {

			$atts['post_status'] = array( \MPHB\PostTypes\BookingCPT\Statuses::STATUS_PENDING_PAYMENT );

			$queryPart = array(
				'key'     => 'mphb_pending_payment_expired',
				'value'   => time(),
				'type'    => 'NUMERIC',
				'compare' => '<=',
			);

			$atts['meta_query'] = mphb_add_to_meta_query( $queryPart, isset( $atts['meta_query'] ) ? $atts['meta_query'] : null );

			unset( $atts['pending_payment_expired'] );
		}
		return $atts;
	}

	private function _addPeriodCriteria( $atts ) {

		if ( isset( $atts['date_from'], $atts['date_to'] ) ) {

			$isEdgeOverlap = isset( $atts['period_edge_overlap'] ) ? (bool) $atts['period_edge_overlap'] : false;

			$queryPart = array(
				'relation' => 'AND',
				array(
					'key'     => 'mphb_check_in_date',
					'value'   => $atts['date_to'],
					'compare' => $isEdgeOverlap ? '<=' : '<',
				),
				array(
					'key'     => 'mphb_check_out_date',
					'value'   => $atts['date_from'],
					'compare' => $isEdgeOverlap ? '>=' : '>',
				),
			);

			$atts['meta_query'] = mphb_add_to_meta_query( $queryPart, isset( $atts['meta_query'] ) ? $atts['meta_query'] : null );
			unset( $atts['date_from'], $atts['date_to'] );
		}
		return $atts;
	}

	public function _addRoomsCriteria( $atts ) {

		if ( ! empty( $atts['rooms'] ) ) {

			$atts['mphb_rooms'] = (array) $atts['rooms'];
			unset( $atts['rooms'] );
		}
		return $atts;
	}

	public function create( \MPHB\Entities\WPPostData $postData ) {

		if ( $postData->getStatus() !== 'auto-draft' ) {

			$postStatus = $postData->getStatus();

			$postData->setStatus( 'auto-draft' );

			$postId = parent::create( $postData );

			do_action( 'mphb_booking_create_before_set_status', $postId );

			return $postId ? $this->updateStatus( $postId, $postStatus ) : $postId;

		} else {

			return parent::create( $postData );
		}
	}

	protected function updateStatus( $postId, $status ) {

		$postAtts = array(
			'ID'          => $postId,
			'post_status' => $status,
		);
		return wp_update_post( $postAtts );
	}
}
