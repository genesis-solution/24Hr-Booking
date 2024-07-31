<?php

namespace MPHB\Repositories;

use \MPHB\Entities;
use \MPHB\Persistences;

class ReservedRoomRepository extends AbstractPostRepository {

	protected $type = 'reserved_room';

	/**
	 *
	 * @var array
	 */
	protected $bookingReservedRooms = array();

	public function __construct( Persistences\CPTPersistence $persistence ) {
		parent::__construct( $persistence );
		add_action( 'mphb_booking_repository_before_mapping_posts', array( $this, 'fillBookingReservedRooms' ) );
	}

	/**
	 *
	 * @param int  $id
	 * @param bool $force Optional. Default false.
	 * @return Entities\ReservedRoom
	 */
	public function findById( $id, $force = false ) {
		return parent::findById( $id, $force );
	}

	/**
	 *
	 * @param array     $atts
	 * @param int|int[] $atts['booking_id']
	 * @param int|int[] $atts['room_id']
	 */
	public function findAll( $atts = array() ) {
		return parent::findAll( $atts );
	}

	/**
	 *
	 * @param int   $bookingId
	 * @param array $atts
	 * @return Entities\ReservedRoom[]
	 */
	public function findAllByBooking( $bookingId, $force = false ) {

		if ( ! isset( $this->bookingReservedRooms[ $bookingId ] ) || $force ) {

			$atts = array(
				'booking_id' => $bookingId,
				// Important always to get reserved rooms in the same order
				// from first created to the last one to be able to map
				// them to the last price breakdown rooms in booking meta!
				'orderby'    => 'ID',
				'order'      => 'ASC',
			);

			$reservedRooms = $this->findAll( $atts );

			$this->bookingReservedRooms[ $bookingId ] = $reservedRooms;
		}

		return $this->bookingReservedRooms[ $bookingId ];
	}

	/**
	 * IMPORTANT: $bookingsPosts can by a single WP_Post when just one booking was found
	 * get_posts() returns an array with it but when fillBookingReservedRooms() is called
	 * through wp hook then this array converted to its content (Wp_Post) in call_user_func in wp core
	 *
	 * @param int[]|\WP_Post[]|WP_Post $bookingsPosts
	 */
	public function fillBookingReservedRooms( $bookingsPosts ) {

		if ( empty( $bookingsPosts ) ) {
			return;
		}

		$bookingsIds = $bookingsPosts;

		if ( $bookingsPosts instanceof \WP_Post ) {

			$bookingsIds = $bookingsPosts->ID;

		} elseif ( 0 < count( $bookingsPosts ) && reset( $bookingsPosts ) instanceof \WP_Post ) {

			$bookingsIds = wp_list_pluck( $bookingsPosts, 'ID' );
		}

		$atts = array(
			'booking_id' => $bookingsIds,
			'fields'     => 'all',
		);

		$reservedRooms   = $this->findAll( $atts );
		$roomsByBookings = array();

		foreach ( $reservedRooms as $reservedRoom ) {

			$bookingId = $reservedRoom->getBookingId();

			if ( ! isset( $roomsByBookings[ $bookingId ] ) ) {

				$roomsByBookings[ $bookingId ] = array();
			}

			$roomsByBookings[ $bookingId ][ $reservedRoom->getId() ] = $reservedRoom;
		}

		$this->bookingReservedRooms += $roomsByBookings;
	}

	/**
	 *
	 * @param \WP_Post|int $post
	 * @return Entities\ReservedRoom
	 */
	public function mapPostToEntity( $post ) {

		$id = ( is_a( $post, '\WP_Post' ) ) ? $post->ID : $post;

		$atts = array(
			'id'         => $id,
			'room_id'    => get_post_meta( $id, '_mphb_room_id', true ),
			'rate_id'    => get_post_meta( $id, '_mphb_rate_id', true ),
			'booking_id' => wp_get_post_parent_id( $id ),
			'adults'     => get_post_meta( $id, '_mphb_adults', true ),
			'children'   => get_post_meta( $id, '_mphb_children', true ),
			'guest_name' => get_post_meta( $id, '_mphb_guest_name', true ),
			'uid'        => get_post_meta( $id, '_mphb_uid', true ),
			'status'     => get_post_status( $id ),
		);

		$services = get_post_meta( $id, '_mphb_services', true );
		if ( ! empty( $services ) && is_array( $services ) ) {
			$atts['reserved_services'] = array_filter( array_map( array( '\MPHB\Entities\ReservedService', 'create' ), $services ) );
		}

		return Entities\ReservedRoom::create( $atts );
	}

	/**
	 *
	 * @param Entities\ReservedRoom $entity
	 * @return \MPHB\Entities\WPPostData
	 */
	public function mapEntityToPostData( $entity ) {

		$postAtts = array(
			'ID'          => $entity->getId(),
			'post_metas'  => array(),
			'post_status' => $entity->getStatus(),
			'post_type'   => MPHB()->postTypes()->reservedRoom()->getPostType(),
			'post_parent' => $entity->getBookingId(),
		);

		$services = array();
		foreach ( $entity->getReservedServices() as $reservedService ) {
			$servicesDetails = array(
				'id'       => $reservedService->getOriginalId(),
				'adults'   => $reservedService->getAdults(),
				'quantity' => $reservedService->getQuantity(),
			);
			$services[]      = $servicesDetails;
		}

		$postAtts['post_metas'] = array(
			'_mphb_room_id'    => $entity->getRoomId(),
			'_mphb_rate_id'    => $entity->getRateId(),
			'_mphb_adults'     => $entity->getAdults(),
			'_mphb_children'   => $entity->getChildren(),
			'_mphb_services'   => $services,
			'_mphb_guest_name' => $entity->getGuestName(),
			'_mphb_uid'        => $entity->getUid(),
		);

		return new Entities\WPPostData( $postAtts );
	}

}
