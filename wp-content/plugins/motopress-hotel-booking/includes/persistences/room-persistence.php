<?php

namespace MPHB\Persistences;

class RoomPersistence extends RoomTypeDependencedPersistence {

	/**
	 * @param array $customAtts Optional. Empty array by default.
	 * @return array
	 *
	 * @since 3.7.0 added optional parameter $customAtts.
	 */
	protected function getDefaultQueryAtts( $customAtts = array() ) {

		$atts = array_merge(
			array(
				'orderby' => 'menu_order',
				'order'   => 'ASC',
			),
			$customAtts
		);

		return parent::getDefaultQueryAtts( $atts );
	}

	/**
	 *
	 * @param array $atts
	 */
	protected function modifyQueryAtts( $atts ) {
		$atts = parent::modifyQueryAtts( $atts );
		if ( isset( $atts['post_status'] ) && $atts['post_status'] === 'all' ) {
			$atts['post_status'] = array(
				'publish',
				'pending',
				'draft',
				'future',
				'private',
			);
		}
		return $atts;
	}

	/**
	 * @param array     $atts Optional.
	 *     @param string    $atts['availability'] free|locked|booked|pending. 'free'
	 *            by default.
	 *            'free' - has no bookings with status complete or pending for this days.
	 *            'locked' - has bookings with status complete or pending for this days.
	 *            'booked' - has bookings with status complete for this days.
	 *            'pending' - has bookings with status pending for this days.
	 *     @param \DateTime $atts['from_date'] Today by default.
	 *     @param \DateTime $atts['to_date'] Tomorrow by default.
	 *     @param int       $atts['count'] The number of rooms to search. All by default.
	 *     @param int|int[] $atts['room_type_id'] Type of rooms to search. All
	 *         by default.
	 *     @param int|int[] $atts['exclude_bookings'] One or more booking IDs to
	 *         exclude from the search results.
	 *     @param int[]     $atts['exclude_rooms'] Room IDs to exclude from the
	 *             search results.
	 *     @param bool      $atts['skip_buffer_rules'] True by default.
	 *     @param int       $atts['exclude_booking'] Deprecated. Use "exclude_bookings"
	 *               instead.
	 * @return int[] Room IDs.
	 *
	 * @since 3.9
	 */
	public function searchRooms( $atts = array() ) {
		$defaults = array(
			'availability'      => 'free',
			'from_date'         => mphb_today(),
			'to_date'           => mphb_today( '+1 day' ),
			'count'             => 0, // Previously was null by default
			'room_type_id'      => 0, // Previously was null by default
			'exclude_bookings'  => array(),
			'exclude_rooms'     => array(),
			'skip_buffer_rules' => true, // Don't rewrite the old logic by default
		);

		// Get rid of deprecated parameters
		if ( isset( $atts['exclude_booking'] ) && ! isset( $atts['exclude_bookings'] ) ) {
			$atts['exclude_bookings'] = $atts['exclude_booking'];
		}

		/** @since 3.9 */
		$atts = apply_filters( 'mphb_search_rooms_atts', array_merge( $defaults, $atts ), $defaults );

		// Reset the "count" parameter if searching for free rooms - find all
		// locked rooms instead of min($count, %all%)
		$count = $atts['count'];

		if ( $atts['availability'] == 'free' ) {
			$atts['count'] = 0;
		}

		// Find locked rooms
		if ( $atts['skip_buffer_rules'] || ! mphb_has_buffer_days() ) {
			$roomIds = $this->findLockedRooms( $atts );

		} else {
			$roomIds     = array();
			$roomTypeIds = ! empty( $atts['room_type_id'] ) ? (array) $atts['room_type_id'] : mphb_get_room_type_ids( 'original' );

			// Search rooms for each room type separately (each room type may
			// have different buffer range)
			foreach ( $roomTypeIds as $roomTypeId ) {
				$modifiedAtts = $atts;

				// Force room type ID
				$modifiedAtts['room_type_id'] = $roomTypeId;

				// Expand searched period
				$bufferDays = mphb_get_buffer_days( $atts['from_date'], $roomTypeId );

				if ( $bufferDays > 0 ) {
					list($fromDate, $toDate) = mphb_modify_buffer_period( $atts['from_date'], $atts['to_date'], $bufferDays );

					$modifiedAtts['from_date'] = $fromDate;
					$modifiedAtts['to_date']   = $toDate;
				}

				// Find rooms
				$roomsPack = $this->findLockedRooms( $modifiedAtts );
				$roomIds   = array_merge( $roomIds, $roomsPack );
			}
		} // If search with buffer rules

		// Get the list of free room
		if ( $atts['availability'] == 'free' ) {
			// Restore the real count
			$atts['count'] = $count;

			// Find free rooms
			$roomIds = $this->findFreeRooms( $roomIds, $atts );
		}

		return $roomIds;
	}

	/**
	 * @param array $atts Optional.
	 * @return int[]
	 *
	 * @global \wpdb $wpdb
	 *
	 * @since 3.9
	 */
	protected function findLockedRooms( $atts ) {
		global $wpdb;

		switch ( $atts['availability'] ) {
			// For 'free' find locked rooms and then find all others (free)
			case 'free':
				$bookingStatuses = MPHB()->postTypes()->booking()->statuses()->getLockedRoomStatuses();
				break;
			case 'booked':
				$bookingStatuses = MPHB()->postTypes()->booking()->statuses()->getBookedRoomStatuses();
				break;
			case 'pending':
				$bookingStatuses = MPHB()->postTypes()->booking()->statuses()->getPendingRoomStatuses();
				break;
			case 'locked':
				$bookingStatuses = MPHB()->postTypes()->booking()->statuses()->getLockedRoomStatuses();
				break;
		}

		$bookingStatusesStr = "'" . implode( "', '", $bookingStatuses ) . "'";

		$sql = 'SELECT DISTINCT room_id.meta_value AS ID'
			. " FROM {$wpdb->posts} AS reserved_rooms"
			. " INNER JOIN {$wpdb->postmeta} AS room_id ON room_id.post_id = reserved_rooms.ID AND room_id.meta_key = '_mphb_room_id'"
			. " INNER JOIN {$wpdb->posts} AS bookings ON bookings.ID = reserved_rooms.post_parent"
			. " INNER JOIN {$wpdb->postmeta} AS check_in_date ON check_in_date.post_id = bookings.ID AND check_in_date.meta_key = 'mphb_check_in_date'"
			. " INNER JOIN {$wpdb->postmeta} AS check_out_date ON check_out_date.post_id = bookings.ID AND check_out_date.meta_key = 'mphb_check_out_date'"
			. ' WHERE reserved_rooms.post_type = %s'
				. " AND reserved_rooms.post_status = 'publish'"
				. " AND bookings.post_status IN ({$bookingStatusesStr})"
				. ' AND check_in_date.meta_value < %s'   // check_in_date  < $atts['to_date']
				. ' AND check_out_date.meta_value > %s'; // check_out_date > $atts['from_date']

		if ( ! empty( $atts['exclude_bookings'] ) ) {
			$bookingIds = implode( ', ', (array) $atts['exclude_bookings'] );

			$sql .= " AND bookings.ID NOT IN ({$bookingIds})";
		}

		if ( ! empty( $atts['room_type_id'] ) ) {
			$roomTypeIds = implode( ', ', (array) $atts['room_type_id'] );

			$sql .= " AND EXISTS(SELECT 1 FROM {$wpdb->postmeta} AS room_type_id WHERE room_type_id.post_id = room_id.meta_value AND room_type_id.meta_key = 'mphb_room_type_id' AND room_type_id.meta_value IN ({$roomTypeIds}) LIMIT 1)";
		}

		if ( ! empty( $atts['count'] ) ) {
			$sql .= ' LIMIT ' . absint( $atts['count'] );
		}

		// Prepare SQL
		$dateFormat = MPHB()->settings()->dateTime()->getDateTransferFormat();

		$sql = $wpdb->prepare(
			$sql,
			MPHB()->postTypes()->reservedRoom()->getPostType(),
			$atts['to_date']->format( $dateFormat ),
			$atts['from_date']->format( $dateFormat )
		);

		// Find rooms
		$roomIds = $wpdb->get_col( $sql );
		$roomIds = array_map( 'absint', $roomIds );

		return $roomIds;
	}

	/**
	 * @param int[] $lockedRooms Results of findLockedRooms().
	 * @param array $atts
	 * @return int[]
	 *
	 * @since 3.9
	 */
	protected function findFreeRooms( $lockedRooms, $atts ) {
		$postAtts = array(
			'fields' => 'ids',
		);

		if ( ! empty( $lockedRooms ) ) {
			$postAtts['post__not_in'] = $lockedRooms;
		}

		if ( ! empty( $atts['exclude_rooms'] ) ) {
			if ( isset( $postAtts['post__not_in'] ) ) {
				$postAtts['post__not_in'] = array_merge( $postAtts['post__not_in'], $atts['exclude_rooms'] );
			} else {
				$postAtts['post__not_in'] = $atts['exclude_rooms'];
			}
		}

		if ( ! empty( $atts['room_type_id'] ) ) {
			$postAtts['room_type_id'] = $atts['room_type_id'];
		}

		if ( ! empty( $atts['count'] ) ) {
			$postAtts['posts_per_page'] = $atts['count'];
		}

		/** @since 3.9 */
		$postAtts = apply_filters( 'mphb_search_free_rooms_atts', $postAtts, $atts );

		$roomIds = $this->getPosts( $postAtts );

		return $roomIds;
	}

	/**
	 * @param \DateTime $checkInDate
	 * @param \DateTime $checkOutDate
	 * @param array     $atts Optional. Additional attributes for searchRooms().
	 * @param int       $atts['count']
	 * @param int|int[] $atts['room_type_id']
	 * @param bool      $atts['skip_buffer_rules'] True by default.
	 * @return bool
	 *
	 * @since 3.7.0 added new filter - "mphb_is_rooms_exist_query_atts".
	 * @since 3.9 arguments $count and $roomTypeId was replaced with $atts.
	 */
	public function isExistsRooms( \DateTime $checkInDate, \DateTime $checkOutDate, $atts = array() ) {
		$searchAtts = array_merge(
			array(
				'availability' => 'free',
				'from_date'    => $checkInDate,
				'to_date'      => $checkOutDate,
				'count'        => 1,
			),
			$atts
		);

		$searchAtts = apply_filters( 'mphb_is_rooms_exist_query_atts', $searchAtts );

		$rooms = $this->searchRooms( $searchAtts );

		return count( $rooms ) >= $searchAtts['count'];
	}

	/**
	 * @param \DateTime $checkInDate
	 * @param \DateTime $checkOutDate
	 * @param array     $rooms Rooms to check.
	 * @param array     $args Optional.
	 *     @param int       $args['room_type_id']
	 *     @param int|int[] $args['exclude_bookings']
	 * @return bool
	 *
	 * @since 3.7 added new filter - "mphb_is_rooms_free_query_atts".
	 * @since 3.8 parameter $roomTypeId was replaced with $args. Added new arguments: "room_type_id" and "exclude_bookings".
	 */
	public function isRoomsFree( \DateTime $checkInDate, \DateTime $checkOutDate, $rooms, $args = array() ) {
		$searchAtts = array(
			'availability' => 'free',
			'from_date'    => $checkInDate,
			'to_date'      => $checkOutDate,
		);

		if ( isset( $args['room_type_id'] ) ) {
			$searchAtts['room_type_id'] = (int) $args['room_type_id'];
		}

		if ( isset( $args['exclude_bookings'] ) ) {
			$searchAtts['exclude_bookings'] = $args['exclude_bookings'];
		}

		$searchAtts = apply_filters( 'mphb_is_rooms_free_query_atts', $searchAtts );

		$freeRooms      = $this->searchRooms( $searchAtts );
		$availableRooms = array_intersect( $rooms, $freeRooms );

		return ( count( $rooms ) == count( $availableRooms ) );
	}

	/**
	 *
	 * @param int $typeId
	 * @return int[]
	 */
	public function findAllIdsByType( $typeId ) {
		$allRoomIds = $this->getPosts(
			array(
				'room_type_id'   => $typeId,
				'post_status'    => 'publish',
				'fields'         => 'ids',
				'posts_per_page' => -1,
			)
		);

		return $allRoomIds;
	}

}
