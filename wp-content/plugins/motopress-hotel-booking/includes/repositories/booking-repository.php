<?php

namespace MPHB\Repositories;

use \MPHB\Entities;
use \MPHB\Persistences;

class BookingRepository extends AbstractPostRepository {

	protected $type = 'booking';

	private $reservedRooms = array();

	/**
	 *
	 * @param int  $id
	 * @param bool $force Optional. Default false.
	 * @return Entities\Booking
	 */
	public function findById( $id, $force = false ) {
		return parent::findById( $id, $force );
	}

	/**
	 *
	 * @param int   $customerId
	 * @param array $atts
	 * @param bool  $all
	 *
	 * @since 4.2.0
	 *
	 * @return array [ ID ] | [ \MPHB\Entity\Booking ]
	 */
	public function findAllByCustomer( $customerId, $atts = array(), $all = true ) {
		$requestAtts = array_merge(
			array(
				'meta_query' => array(
					array(
						'key'   => 'mphb_customer_id',
						'value' => $customerId,
					),
				),
			),
			$atts
		);

		$ids = $this->persistence->getPosts( $requestAtts );

		if ( ! $all ) {
			return $ids;
		}

		return $this->mapPostsToEntity( $ids );
	}

	/**
	 *
	 * @param \MPHB\Entities\Payment|int $payment Payment object or payment id
	 * @param bool                       $force Optional. Default false.
	 * @return Entities\Booking|null
	 */
	public function findByPayment( $payment, $force = false ) {
		if ( ! is_a( $payment, '\MPHB\Entities\Payment' ) ) {
			$payment = MPHB()->getPaymentRepository()->findById( $payment );
		}

		return $payment ? $this->findById( $payment->getBookingId(), $force ) : null;
	}

	/**
	 *
	 * @param string $checkoutId
	 *
	 * @return Entities\Booking|null
	 */
	public function findByCheckoutId( $checkoutId ) {
		return $this->findByMeta( '_mphb_checkout_id', $checkoutId );
	}

	/**
	 *
	 * @param array  $atts
	 * @param bool   $atts['room_locked'] Optional. Whether get only bookings that locked room.
	 * @param string $atts['date_from'] Optional. Date in 'Y-m-d' format. Retrieve only bookings that consist dates from period begins at this date.
	 * @param string $atts['date_to'] Optional. Date in 'Y-m-d' format. Retrieve only bookings that consist dates from period ends at this date.
	 * @param bool   $atts['period_edge_overlap'] Optional. Whether the edge days of period are overlapping. Default FALSE.
	 * @param array  $atts['rooms'] Optional. Room Ids.
	 *
	 * @return Entities\Booking[]
	 */
	public function findAll( $atts = array() ) {
		return parent::findAll( $atts );
	}

	/**
	 * @param string $syncId
	 * @param string $fields Optional. "ids" or "all". "ids" by default.
	 * @return int[]|\MPHB\Entities\Booking[]
	 */
	public function findAllByCalendar( $syncId, $fields = 'ids' ) {
		$ids = $this->persistence->getPosts(
			array(
				'meta_query' => array(
					array(
						'key'   => '_mphb_sync_id',
						'value' => esc_sql( $syncId ),
					),
				),
			)
		);

		if ( $fields == 'all' ) {
			return $this->mapPostsToEntity( $ids );
		} else {
			return $ids;
		}
	}

	/**
	 * @param array $args Optional.
	 * @param bool  $args['filter_empty_emails'] Filter all bookings without email.
	 *      TRUE by default.
	 * @return \MPHB\Entities\Booking|null
	 *
	 * @since 3.7.2
	 */
	public function findRandom( $args = array() ) {
		$filterByEmail = array_key_exists( 'filter_empty_emails', $args ) ? $args['filter_empty_emails'] : true;

		$query     = array( 'orderby' => 'rand' );
		$metaQuery = array();

		if ( $filterByEmail ) {
			$metaQuery = array(
				'relation' => 'AND',
				array(
					'key'     => 'mphb_email',
					'compare' => 'EXISTS',
				),
				array(
					'key'     => 'mphb_email',
					'value'   => '',
					'compare' => '!=',
				),
			);
		}

		if ( ! empty( $metaQuery ) ) {
			$query['meta_query'] = $metaQuery;
		}

		return $this->findOne( $query );
	}

	/**
	 *
	 * @param \WP_Post|int $post
	 * @return Entities\Booking
	 */
	public function mapPostToEntity( $post ) {

		$id = ( is_a( $post, '\WP_Post' ) ) ? $post->ID : $post;

		$bookingAtts = $this->retrieveBookingAtts( $id );
		return Entities\Booking::create( $bookingAtts );
	}

	/**
	 *
	 * @param int $postId
	 * @return array|false
	 */
	private function retrieveBookingAtts( $postId ) {

		if ( empty( $postId ) ) {
			return false;
		}

		$postDateTime = \MPHB\Utils\DateUtils::getPostDateTimeUTC( $postId );

		$bookingAtts = array(
			'id'       => $postId,
			'datetime' => $postDateTime,
		);

		$checkInDate = get_post_meta( $postId, 'mphb_check_in_date', true );
		if ( $checkInDate ) {
			$bookingAtts['check_in_date'] = \DateTime::createFromFormat( 'Y-m-d', $checkInDate );
		}
		$checkOutDate = get_post_meta( $postId, 'mphb_check_out_date', true );
		if ( $checkOutDate ) {
			$bookingAtts['check_out_date'] = \DateTime::createFromFormat( 'Y-m-d', $checkOutDate );
		}

		$bookingAtts['note'] = get_post_meta( $postId, 'mphb_note', true );

		$bookingAtts['reserved_rooms'] = MPHB()->getReservedRoomRepository()->findAllByBooking( $postId );

		$customerDetails = array(
			'customer_id' => get_post_meta( $postId, 'mphb_customer_id', true ),
			'email'       => get_post_meta( $postId, 'mphb_email', true ),
			'first_name'  => get_post_meta( $postId, 'mphb_first_name', true ),
			'last_name'   => get_post_meta( $postId, 'mphb_last_name', true ),
			'phone'       => get_post_meta( $postId, 'mphb_phone', true ),
			'country'     => get_post_meta( $postId, 'mphb_country', true ),
			'state'       => get_post_meta( $postId, 'mphb_state', true ),
			'city'        => get_post_meta( $postId, 'mphb_city', true ),
			'zip'         => get_post_meta( $postId, 'mphb_zip', true ),
			'address1'    => get_post_meta( $postId, 'mphb_address1', true ),
		);

		$customerCustoms = mphb_get_custom_customer_fields();

		foreach ( array_keys( $customerCustoms ) as $fieldName ) {
			$postMeta                      = MPHB()->addPrefix( $fieldName, '_' );
			$customerDetails[ $fieldName ] = get_post_meta( $postId, $postMeta, true );
		}

		$bookingAtts['customer'] = new Entities\Customer( $customerDetails );

		$bookingAtts['payment_status'] = get_post_meta( $postId, 'mphb_payment_status', true );

		$bookingAtts['status'] = get_post_status( $postId );

		$bookingAtts['total_price'] = abs( floatval( get_post_meta( $postId, 'mphb_total_price', true ) ) );

		$bookingAtts['coupon_id'] = get_post_meta( $postId, 'mphb_coupon_id', true );

		$bookingAtts['ical_prodid'] = get_post_meta( $postId, 'mphb_ical_prodid', true );

		$bookingAtts['ical_summary'] = get_post_meta( $postId, 'mphb_ical_summary', true );

		$bookingAtts['ical_description'] = get_post_meta( $postId, 'mphb_ical_description', true );

		$bookingAtts['language'] = mphb_clean( get_post_meta( $postId, 'mphb_language', true ) );

		$bookingAtts['checkout_id'] = get_post_meta( $postId, '_mphb_checkout_id', true );

		// If booking has such meta value and meta value is not empty - then the
		// booking is imported
		$bookingAtts['sync_id'] = get_post_meta( $postId, '_mphb_sync_id', true );

		$bookingAtts['sync_queue_id'] = get_post_meta( $postId, '_mphb_sync_queue_id', true );

		$bookingAtts['internal_notes'] = get_post_meta( $postId, '_mphb_booking_internal_notes', true );

		return $bookingAtts;
	}

	/**
	 *
	 * @param Entities\Booking $entity
	 * @return \MPHB\Entities\WPPostData
	 */
	public function mapEntityToPostData( $entity ) {

		$postAtts = array(
			'ID'          => $entity->getId(),
			'post_metas'  => array(),
			'post_status' => $entity->getStatus(),
			'post_type'   => MPHB()->postTypes()->booking()->getPostType(),
		);

		$postAtts['post_metas'] = array(
			'mphb_check_in_date'    => $entity->getCheckInDate()->format( 'Y-m-d' ),
			'mphb_check_out_date'   => $entity->getCheckOutDate()->format( 'Y-m-d' ),
			'mphb_note'             => $entity->getNote(),
			'mphb_customer_id'      => $entity->getCustomer()->getCustomerId(),
			'mphb_email'            => $entity->getCustomer()->getEmail(),
			'mphb_first_name'       => $entity->getCustomer()->getFirstName(),
			'mphb_last_name'        => $entity->getCustomer()->getLastName(),
			'mphb_phone'            => $entity->getCustomer()->getPhone(),
			'mphb_country'          => $entity->getCustomer()->getCountry(),
			'mphb_state'            => $entity->getCustomer()->getState(),
			'mphb_city'             => $entity->getCustomer()->getCity(),
			'mphb_zip'              => $entity->getCustomer()->getZip(),
			'mphb_address1'         => $entity->getCustomer()->getAddress1(),
			'mphb_total_price'      => $entity->getTotalPrice(),
			'mphb_coupon_id'        => $entity->getCouponId(),
			'mphb_ical_prodid'      => $entity->getICalProdid(),
			'mphb_ical_summary'     => $entity->getICalSummary(),
			'mphb_ical_description' => $entity->getICalDescription(),
			'mphb_language'         => $entity->getLanguage(),
			'_mphb_checkout_id'     => $entity->getCheckoutId(),
		);

		if ( $entity->isImported() ) {
			$postAtts['post_metas']['_mphb_sync_id']       = $entity->getSyncId();
			$postAtts['post_metas']['_mphb_sync_queue_id'] = $entity->getSyncQueueId();
		}

		// Add custom customer fields
		$customerCustoms = $entity->getCustomer()->getCustomFields();

		foreach ( $customerCustoms as $fieldName => $value ) {
			$postMeta                            = MPHB()->addPrefix( $fieldName, '_' );
			$postAtts['post_metas'][ $postMeta ] = $value;
		}

		// Save price breakdown here, otherwise some emails will not show price
		// breakdown details (see MB-1027):
		// Pending Booking Email
		// New Booking Email (Confirmation by Admin)
		// New Booking Email (Confirmation by User)
		$priceBreakdown = $entity->getLastPriceBreakdown( false );

		if ( ! empty( $priceBreakdown ) ) {
			$priceBreakdown = json_encode( $priceBreakdown );
			$priceBreakdown = wp_slash( $priceBreakdown );

			$postAtts['post_metas']['_mphb_booking_price_breakdown'] = $priceBreakdown;
		}

		return new Entities\WPPostData( $postAtts );
	}

	/**
	 *
	 * @param Entities\Booking $entity
	 * @return bool
	 */
	public function save( &$entity ) {

		// TODO temporary solution for save reserved rooms. Move reserved rooms saving from booking repository.
		$this->reservedRooms = $entity->getReservedRooms();

		// TODO now reservedRooms saved only on create new booking (with non-auto-draft status ). needs to allow save reserved rooms on update also
		add_action( 'mphb_booking_create_before_set_status', array( $this, 'updateReservedRooms' ) );

		$isSaved = parent::save( $entity );

		remove_action( 'mphb_booking_create_before_set_status', array( $this, 'updateReservedRooms' ) );

		return $isSaved;
	}

	/**
	 * @param int $bookingId
	 */
	public function updateReservedRooms( $bookingId ) {
		$savedRoomIds = array_filter(
			array_map(
				function ( $reservedRoom ) {
					return $reservedRoom->getId();
				},
				$this->reservedRooms
			)
		);

		$existingRooms = MPHB()->getReservedRoomRepository()->findAllByBooking( $bookingId );

		$roomsToDelete = array_filter(
			$existingRooms,
			function ( $existingRoom ) use ( $savedRoomIds ) {
				return ! in_array( $existingRoom->getId(), $savedRoomIds );
			}
		);

		foreach ( $roomsToDelete as $roomToDelete ) {
			MPHB()->getReservedRoomRepository()->delete( $roomToDelete );
		}

		foreach ( $this->reservedRooms as $reservedRoom ) {
			$reservedRoom->setBookingId( $bookingId );

			MPHB()->getReservedRoomRepository()->save( $reservedRoom );
		}

		// Refresh stored reserved rooms
		MPHB()->getReservedRoomRepository()->findAllByBooking( $bookingId, true );

		unset( $this->reservedRooms );
	}

	public function getImportedCount() {
		global $wpdb;

		$sql   = "SELECT COUNT(*) FROM {$wpdb->posts} AS posts INNER JOIN {$wpdb->postmeta} AS postmeta ON posts.ID = postmeta.post_id AND postmeta.meta_key = '_mphb_sync_id' WHERE posts.post_status != 'trash' AND postmeta.meta_value != ''";
		$count = $wpdb->get_var( $sql );

		return (int) $count;
	}

}
