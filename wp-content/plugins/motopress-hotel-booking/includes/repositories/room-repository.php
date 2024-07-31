<?php

namespace MPHB\Repositories;

use \MPHB\Entities;

class RoomRepository extends AbstractPostRepository {

	protected $type = 'room';

	/**
	 *
	 * @param int  $id
	 * @param bool $force
	 * @return Entities\Room
	 */
	public function findById( $id, $force = false ) {
		return parent::findById( $id, $force );
	}

	/**
	 * @since 3.7.1
	 */
	public function getIdTitleList( $atts = array() ) {
		$defaults = array(
			'fields'      => 'all',
			'orderby'     => 'ID',
			'order'       => 'ASC',
			'post_status' => array( 'publish', 'pending', 'draft', 'future', 'private' ),
		);

		$atts  = array_merge( $defaults, $atts );
		$posts = $this->persistence->getPosts( $atts );

		$list = array();

		foreach ( $posts as $post ) {
			$list[ $post->ID ] = $post->post_title;
		}

		return $list;
	}

	function mapPostToEntity( $post ) {
		$id = ( is_a( $post, '\WP_Post' ) ) ? $post->ID : $post;

		$atts = array(
			'id'           => $id,
			'status'       => get_post_status( $id ),
			'title'        => get_the_title( $id ),
			'description'  => get_the_excerpt( $id ),
			'room_type_id' => get_post_meta( $id, 'mphb_room_type_id', true ),
		);

		return new Entities\Room( $atts );
	}

	/**
	 *
	 * @param Entities\Room $entity
	 * @return \MPHB\Entities\WPPostData
	 */
	public function mapEntityToPostData( $entity ) {
		$post_metas = array(
			'mphb_room_type_id' => $entity->getRoomTypeId(),
		);
		$postAtts   = array(
			'ID'           => $entity->getId(),
			'post_metas'   => $post_metas,
			'post_status'  => $entity->getStatus(),
			'post_title'   => $entity->getTitle(),
			'post_excerpt' => $entity->getDescription(),
			'post_type'    => MPHB()->postTypes()->room()->getPostType(),
		);

		return new Entities\WPPostData( $postAtts );
	}

	/**
	 *
	 * @param Entities\RoomType $roomType
	 * @param int               $count Optional. Number of rooms to generate. Default 1.
	 * @param string            $customPrefix Optional. Default ''
	 * @return bool
	 */
	public function generateRooms( $roomType, $count = 1, $customPrefix = '' ) {
		$titlePrefix = '';

		if ( ! $roomType ) {
			return false;
		}

		if ( $count < 1 ) {
			return false;
		}

		if ( empty( $customPrefix ) ) {
			$titlePrefix = $roomType->getTitle() . ' ';
		} else {
			$titlePrefix = $customPrefix . ' ';
		}

		for ( $i = 1; $i <= $count; $i++ ) {
			$postMetaAtts = array(
				'mphb_room_type_id' => $roomType->getId(),
			);
			$postDataAtts = array(
				'post_metas'  => $postMetaAtts,
				'post_title'  => $titlePrefix . $i,
				'post_type'   => MPHB()->postTypes()->room()->getPostType(),
				'post_status' => 'publish',
			);

			$postData = new Entities\WPPostData( $postDataAtts );

			$created = $this->persistence->create( $postData );
		}

		return true;
	}

	public function findAllByRoomType( $roomTypeId, $atts = array() ) {
		$atts['room_type_id'] = $roomTypeId;
		return $this->findAll( $atts );
	}

	/**
	 * @param \DateTime $checkIn
	 * @param \DateTime $checkOut
	 * @param int       $roomTypeId
	 * @param array     $atts Optional.
	 *     @param int|int[] $atts['exclude_bookings']
	 * @return int[] IDs of locked rooms. Will always return original IDs because
	 *     of direct query to the DB.
	 *
	 * @since 3.8 added new argument $atts and parameter "exclude_bookings".
	 */
	public function getLockedRooms( \DateTime $checkInDate, \DateTime $checkOutDate, $roomTypeId = 0, $atts = array() ) {

		/**
		 * @since 3.9
		 */
		$bookingPeriod = array( $checkInDate, $checkOutDate );
		$searchAtts    = array_merge(
			array(
				'availability' => 'locked',
				'from_date'    => $checkInDate,
				'to_date'      => $checkOutDate,
			),
			$atts
		);

		if ( $roomTypeId ) {
			$searchAtts['room_type_id'] = $roomTypeId;
		}

		$lockedRooms = MPHB()->getRoomPersistence()->searchRooms( $searchAtts );
		$lockedRooms = array_map( 'intval', $lockedRooms );

		return $lockedRooms;
	}

	/**
	 *
	 * @param int $requestedRoomTypeId if 0 then we search among all rooms
	 *
	 * @return array Keys are dates in format Y-m-d, values are room counts
	 */
	public function getBookedDays( $requestedRoomTypeId ) {

		$bookingAtts = array(
			'room_locked' => true,
			'fields'      => 'all',
			// Search starting from today
			'meta_query'  => array(
				array(
					'key'     => 'mphb_check_out_date',
					'value'   => mphb_current_time( 'Y-m-d' ),
					'compare' => '>=',
					'type'    => 'DATE',
				),
			),
		);

		$roomIdsAtts = array(
			'fields'      => 'ids',
			'post_status' => 'publish',
		);

		if ( 0 < $requestedRoomTypeId ) {
			$roomIdsAtts['room_type_id'] = $requestedRoomTypeId;
		}

		$allRoomIdsByRequestedRoomType = $this->persistence->getPosts( $roomIdsAtts );

		$bookingAtts['rooms'] = $allRoomIdsByRequestedRoomType;

		$bookings = MPHB()->getBookingRepository()->findAll( $bookingAtts );

		// [ Y-m-d => [roomId, roomId, ... ], ... ]
		$bookedRoomIdsPerDates = array();

		foreach ( $bookings as $booking ) {

			$bookedRoomIds              = array();
			$bookedRoomIdsPerRoomTypeId = array();

			foreach ( $booking->getReservedRooms() as $reservedRoom ) {

				// check in rooms list to avoide add deleted rooms from bookings to the
				// availability check
				if ( in_array( $reservedRoom->getRoomId(), $allRoomIdsByRequestedRoomType, true ) ) {

					$bookedRoomIds[] = $reservedRoom->getRoomId();
					$bookedRoomIdsPerRoomTypeId[ $reservedRoom->getRoomTypeId() ][] = $reservedRoom->getRoomId();
				}
			}

			if ( empty( $bookedRoomIds ) ) {
				continue;
			}

			foreach ( $booking->getDates( true ) as $dateYmd => $date ) {

				if ( ! isset( $bookedRoomIdsPerDates[ $dateYmd ] ) ) {
					$bookedRoomIdsPerDates[ $dateYmd ] = array();
				}

				$bookedRoomIdsPerDates[ $dateYmd ] = array_merge( $bookedRoomIdsPerDates[ $dateYmd ], $bookedRoomIds );
			}

			// add booked room ids to buffer days
			foreach ( $bookedRoomIdsPerRoomTypeId as $bookedRoomTypeId => $roomIds ) {

				$actualRule = MPHB()->getRulesChecker()->bufferRules()->findActualRule(
					$booking->getCheckInDate(),
					$bookedRoomTypeId
				);

				$bookingBufferDays = $actualRule->getBufferDays();

				if ( 0 < $bookingBufferDays ) {

					$bufferDatesForRoom = mphb_modify_booking_buffer_period( $booking, $bookingBufferDays );

					if ( ! empty( $bufferDatesForRoom ) ) {

						foreach ( $bufferDatesForRoom as $date => $dateObj ) {

							$bufferDateYmd = $dateObj->format( 'Y-m-d' );

							if ( ! isset( $bookedRoomIdsPerDates[ $bufferDateYmd ] ) ) {
								$bookedRoomIdsPerDates[ $bufferDateYmd ] = array();
							}

							$bookedRoomIdsPerDates[ $bufferDateYmd ] = array_merge( $bookedRoomIdsPerDates[ $bufferDateYmd ], $roomIds );
						}
					}
				}
			}
		}

		$bookedRoomIdsPerDates    = array_map( 'array_unique', $bookedRoomIdsPerDates );
		$bookedRoomCountsPerDates = array_map( 'count', $bookedRoomIdsPerDates );
		ksort( $bookedRoomCountsPerDates );

		$checkInCountsPerDates  = array();
		$checkOutCountsPerDates = array();

		if ( ! empty( $bookedRoomCountsPerDates ) ) {

			$roomsTotal = count( $allRoomIdsByRequestedRoomType );

			foreach ( $bookedRoomCountsPerDates as $bookedDateYmd => $roomsCount ) {

				if ( $roomsCount >= $roomsTotal ) {

					$beforeBookedDateYmd = \DateTime::createFromFormat( 'Y-m-d', $bookedDateYmd )->modify( '-1 day' )->format( 'Y-m-d' );
					$afterBookedDateYmd  = \DateTime::createFromFormat( 'Y-m-d', $bookedDateYmd )->modify( '+1 day' )->format( 'Y-m-d' );

					if ( empty( $checkInCountsPerDates ) ) {

						$checkInCountsPerDates[ $bookedDateYmd ]       = $roomsCount;
						$checkOutCountsPerDates[ $afterBookedDateYmd ] = $roomsCount;

					} elseif ( ! isset( $bookedRoomCountsPerDates[ $beforeBookedDateYmd ] ) || 
						$bookedRoomCountsPerDates[ $beforeBookedDateYmd ] < $roomsCount
					) {
						$checkInCountsPerDates[ $bookedDateYmd ]       = $roomsCount;
						$checkOutCountsPerDates[ $afterBookedDateYmd ] = $roomsCount;

					} elseif ( ! empty( $checkOutCountsPerDates ) ) {

						$lastCheckOutDate = array_keys( $checkOutCountsPerDates )[ count( $checkOutCountsPerDates ) - 1 ];
						unset( $checkOutCountsPerDates[ $lastCheckOutDate ] );
						$checkOutCountsPerDates[ $afterBookedDateYmd ] = $roomsCount;
					}
				}
			}
		}

		return array(
			'booked'     => $bookedRoomCountsPerDates,
			'check-ins'  => $checkInCountsPerDates,
			'check-outs' => $checkOutCountsPerDates,
		);
	}

	/**
	 * @param \DateTime $checkInDate
	 * @param \DateTime $checkOutDate
	 * @param type      $roomTypeId Optional. 0 by default.
	 * @return array [%Room type ID% => [%Rooms IDs%]] Will always return original
	 *     IDs because of direct query to the DB.
	 *
	 * @global \wpdb $wpdb
	 *
	 * @since 3.8 added new argument $atts and parameter "exclude_bookings".
	 */
	public function getAvailableRooms( \DateTime $checkInDate, \DateTime $checkOutDate, $roomTypeId = 0, $atts = array() ) {
		global $wpdb;

		$lockedRooms = $this->getLockedRooms( $checkInDate, $checkOutDate, $roomTypeId, $atts );

		$query = 'SELECT room_type_id.meta_value AS type_id, rooms.ID AS room_id '
			. "FROM $wpdb->posts AS rooms "

			. "INNER JOIN $wpdb->postmeta AS room_type_id "
			. 'ON rooms.ID = room_type_id.post_id '
			. "INNER JOIN $wpdb->posts AS room_types "
			. 'ON room_type_id.meta_value = room_types.ID '

			. "WHERE rooms.post_type = '" . MPHB()->postTypes()->room()->getPostType() . "' "
			. "AND rooms.post_status = 'publish' "
			. "AND room_type_id.meta_key = 'mphb_room_type_id' "
			. "AND room_types.post_status = 'publish' "
			. "AND room_types.post_type = '" . MPHB()->postTypes()->roomType()->getPostType() . "' ";

		if ( ! empty( $lockedRooms ) ) {
			$query .= 'AND rooms.ID NOT IN (' . join( ',', $lockedRooms ) . ') ';
		}

		if ( $roomTypeId > 0 ) {
			$query .= "AND room_type_id.meta_value = '$roomTypeId' ";
		} else {
			$query .= 'AND room_type_id.meta_value IS NOT NULL '
				. "AND room_type_id.meta_value <> '' ";
		}

		/**
		 * @var array [["type_id", "room_id"], ...]
		 */
		$results = $wpdb->get_results( $query, ARRAY_A );

		$availableRooms = array();

		foreach ( $results as $row ) {
			$typeId = intval( $row['type_id'] );
			$roomId = intval( $row['room_id'] );

			if ( ! isset( $availableRooms[ $typeId ] ) ) {
				$availableRooms[ $typeId ] = array();
			}

			$availableRooms[ $typeId ][] = $roomId;
		}

		return $availableRooms;
	}

}
