<?php

namespace MPHB\Upgrades;

use \MPHB\Entities;

class BackgroundBookingUpgrader_2_0_0 extends \MPHB\BackgroundPausableProcess {

	const BATCH_SIZE = 500;

	/**
	 * @var string
	 */
	protected $action = '2_0_0';

	protected function task( $bookingId ) {

		$roomId = (int) get_post_meta( $bookingId, 'mphb_room_id', true );

		if ( ! $roomId ) {
			// Prevent create reserved room
			return false;
		}

		// mphb_error_log( 'Task booking #' . $bookingId );

		$rateId   = (int) get_post_meta( $bookingId, 'mphb_room_rate_id', true );
		$adults   = (int) get_post_meta( $bookingId, 'mphb_adults', true );
		$children = (int) get_post_meta( $bookingId, 'mphb_children', true );
		$services = get_post_meta( $bookingId, 'mphb_services', true );
		$services = is_array( $services ) ? $services : array();

		$reservedServices = array();
		foreach ( $services as $serviceDetails ) {
			if ( ! isset( $serviceDetails['id'], $serviceDetails['count'] ) ) {
				continue;
			}
			$reservedServiceParams = array(
				'id'     => $serviceDetails['id'],
				'adults' => $serviceDetails['count'],
			);

			$reservedServices[] = Entities\ReservedService::create( $reservedServiceParams );
		}
		$reservedServices = array_filter( $reservedServices );

		$reservedRoomParams = array(
			'room_id'           => $roomId,
			'rate_id'           => $rateId,
			'adults'            => $adults,
			'children'          => $children,
			'reserved_services' => $reservedServices,
			'booking_id'        => $bookingId,
		);

		$reservedRoom = Entities\ReservedRoom::create( $reservedRoomParams );

		$isSaved = $reservedRoom && MPHB()->getReservedRoomRepository()->save( $reservedRoom );

		if ( $isSaved ) {
			delete_post_meta( $bookingId, 'mphb_room_id' );
			delete_post_meta( $bookingId, 'mphb_room_rate_id' );
			delete_post_meta( $bookingId, 'mphb_adults' );
			delete_post_meta( $bookingId, 'mphb_children' );
			delete_post_meta( $bookingId, 'mphb_services' );
		}

		return false;
	}

	protected function get_batch() {
		$batch = parent::get_batch();

		if ( ! empty( $batch ) && property_exists( $batch, 'data' ) && ! empty( $batch->data ) ) {
			// Fill bookings meta
			update_postmeta_cache( $batch->data );
		}

		return $batch;
	}

}
