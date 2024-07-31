<?php

namespace MPHB\iCal;

use \MPHB\Entities;
use \MPHB\iCal\Logger;
use \MPHB\PostTypes\BookingCPT\Statuses;

class Importer {

	/**
	 * @since 4.2.2
	 * @var Logger
	 */
	protected $logger = null;

	/**
	 * @var bool
	 */
	protected $isImporting = false;

	public function __construct( $logger ) {
		$this->logger = $logger;

		add_filter( 'mphb_prevent_handle_booking_status_transition', array( $this, 'preventStatusTransition' ) );
	}

	/**
	 * @param bool $prevent
	 * @return bool
	 */
	public function preventStatusTransition( $prevent ) {
		return $prevent || $this->isImporting;
	}

	public static function isBookingTooOldForImport( \DateTime $bookingCheckIn ) {

		$today = date( 'Y-m-d' );

		return $bookingCheckIn->format( 'Y-m-d' ) < $today;
	}

	protected function findIntersectingBookings( $event ) {
		return MPHB()->getBookingRepository()->findAll(
			array(
				'room_locked' => true,
				'rooms'       => array( $event['roomId'] ),
				'date_from'   => $event['checkIn'],
				'date_to'     => $event['checkOut'],
			)
		);
	}

	protected function isSamePeriod( $event, $booking ) {
		return $event['checkIn'] === $booking->getCheckInDate()->format( 'Y-m-d' )
			&& $event['checkOut'] === $booking->getCheckOutDate()->format( 'Y-m-d' );
	}

	protected function isOutdatedBooking( $booking, $syncId, $queueId ) {
		return $booking->getSyncId() === 'Outdated'
			|| ( $booking->getSyncId() === $syncId && $booking->getSyncQueueId() != $queueId );
	}

	protected function filterOutdatedBookings( $bookings, $syncId, $queueId ) {
		$outdatedBookings = array();

		foreach ( $bookings as $booking ) {
			if ( $this->isOutdatedBooking( $booking, $syncId, $queueId ) ) {
				$outdatedBookings[] = $booking;
			}
		}

		return $outdatedBookings;
	}

	protected function filterConflictingIds( $intersectingBookings, $outdatedBookings ) {
		$intersectionIds = array_map(
			function ( $booking ) {
				return $booking->getId();
			},
			$intersectingBookings
		);

		$outdatedIds = array_map(
			function ( $booking ) {
				return $booking->getId();
			},
			$outdatedBookings
		);

		return array_diff( $intersectionIds, $outdatedIds );
	}

	/**
	 * @param array  $event [roomId, prodid, uid, checkIn, checkOut, summary, description]
	 * @param string $syncId
	 * @param int    $queueId
	 * @return int Import status code. See \MPHB\iCal\ImportStatus.
	 */
	public function import( $event, $syncId, $queueId ) {
		// If the event is too old, then just skip it
		if ( static::isBookingTooOldForImport( \DateTime::createFromFormat( 'Y-m-d', $event['checkIn'] ) ) ) {
			$this->logger->info(
				sprintf( __( 'Skipped. Event from %1$s to %2$s has passed.', 'motopress-hotel-booking' ), $event['checkIn'], $event['checkOut'] )
			);

			return ImportStatus::SKIPPED;
		}

		// Check intersections with other bookings
		$intersectingBookings = $this->findIntersectingBookings( $event );
		$intersectionsCount   = count( $intersectingBookings );

		// Create new booking if no intersections found
		if ( $intersectionsCount == 0 ) {
			$wasCreated = $newId = $this->createBooking( $event, $syncId, $queueId );

			if ( $wasCreated ) {
				$this->logger->success(
					sprintf( __( 'New booking #%1$d. The dates from %2$s to %3$s are now blocked.', 'motopress-hotel-booking' ), $newId, $event['checkIn'], $event['checkOut'] )
				);

				return ImportStatus::SUCCESS;
			} else {
				return ImportStatus::FAILED;
			}
		}

		// If only one intersection with the same dates - skip the event (maybe
		// update summary, description etc.)
		if ( $intersectionsCount == 1 ) {
			$booking = reset( $intersectingBookings );

			if ( $this->isSamePeriod( $event, $booking ) ) {
				if ( $this->isOutdatedBooking( $booking, $syncId, $queueId ) ) {
					// Update outdated booking with new information
					$this->updateBooking( $event, $booking, $syncId, $queueId );

					$this->logger->info(
						sprintf( __( 'Success. Booking #%d updated with new data.', 'motopress-hotel-booking' ), $booking->getId() )
					);

					return ImportStatus::SUCCESS;

				} else {
					// Just inform that dates already blocked
					$this->logger->info(
						sprintf( __( 'Skipped. The dates from %1$s to %2$s are already blocked.', 'motopress-hotel-booking' ), $event['checkIn'], $event['checkOut'] )
					);

					return ImportStatus::SKIPPED;
				}
			}
		}

		// If all bookings - outdated, then update one and remove all other
		$outdatedBookings = $this->filterOutdatedBookings( $intersectingBookings, $syncId, $queueId );

		if ( count( $outdatedBookings ) == $intersectionsCount ) {
			$updatedId    = $this->updateOne( $event, $intersectingBookings, $syncId, $queueId );
			$removedCount = $intersectionsCount - 1;

			if ( $removedCount > 0 ) {
				$message = _n( 'Success. Booking #%1$d updated with new data. Removed %2$d outdated booking.', 'Success. Booking #%1$d updated with new data. Removed %2$d outdated bookings.', $removedCount, 'motopress-hotel-booking' );
			} else {
				$message = __( 'Success. Booking #%1$d updated with new data.', 'motopress-hotel-booking' );
			}

			$this->logger->info( sprintf( $message, $updatedId, $removedCount ) );

			return ImportStatus::SUCCESS;
		}

		// Cannot import the event
		$conflictIds = $this->filterConflictingIds( $intersectingBookings, $outdatedBookings );

		$message = _n( 'Cannot import new event. Dates from %1$s to %2$s are partially blocked by booking %3$s.', 'Cannot import new event. Dates from %1$s to %2$s are partially blocked by bookings %3$s.', count( $conflictIds ), 'motopress-hotel-booking' );

		$this->logger->error(
			sprintf( $message, $event['checkIn'], $event['checkOut'], '#' . implode( ', #', $conflictIds ) )
		);

		return ImportStatus::FAILED;
	}

	/**
	 * @param array  $event [roomId, prodid, uid, checkIn, checkOut, summary, description]
	 * @param string $syncId
	 * @param int    $queueId
	 * @return bool Was or was not created in the database.
	 */
	protected function createBooking( $event, $syncId, $queueId ) {
		$room     = MPHB()->getRoomRepository()->findById( $event['roomId'] );
		$roomType = $room ? MPHB()->getRoomTypeRepository()->findById( $room->getRoomTypeId() ) : null;
		$adults   = $roomType ? $roomType->getAdultsCapacity() : MPHB()->settings()->main()->getMinAdults();
		$children = $roomType ? $roomType->getChildrenCapacity() : MPHB()->settings()->main()->getMinChildren();

		// Add time to check-in/check-out dates
		$checkIn  = $event['checkIn'] . ' ' . MPHB()->settings()->dateTime()->getCheckInTime();
		$checkOut = $event['checkOut'] . ' ' . MPHB()->settings()->dateTime()->getCheckOutTime();

		$this->isImporting = true;

		$reservedRoom = Entities\ReservedRoom::create(
			array(
				'room_id'  => $event['roomId'],
				'rate_id'  => 0,
				'adults'   => $adults,
				'children' => $children,
				'uid'      => $event['uid'],
			)
		);

		$booking = Entities\Booking::create(
			array(
				'check_in_date'    => \DateTime::createFromFormat( 'Y-m-d H:i:s', $checkIn ),
				'check_out_date'   => \DateTime::createFromFormat( 'Y-m-d H:i:s', $checkOut ),
				'reserved_rooms'   => array( $reservedRoom ),
				'customer'         => new Entities\Customer(),
				'status'           => Statuses::STATUS_CONFIRMED,
				'total_price'      => 0, // Prevent calculation of the price
				'ical_prodid'      => $event['prodid'],
				'ical_summary'     => $event['summary'],
				'ical_description' => $event['description'],
				'sync_id'          => $syncId,
				'sync_queue_id'    => $queueId,
			)
		);

		$wasCreated = MPHB()->getBookingRepository()->save( $booking );

		if ( $wasCreated ) {
			$logMessage = __( 'Booking imported with UID %1$s.<br />Summary: %2$s.<br />Description: %3$s.<br />Source: %4$s.', 'motopress-hotel-booking' );
			$booking->addLog( sprintf( $logMessage, $event['uid'], $event['summary'], $event['description'], $event['prodid'] ) );

			do_action( 'mphb_create_booking_via_ical', $booking );
		}

		$this->isImporting = false;

		return $wasCreated ? $booking->getId() : false;
	}

	/**
	 * @param array                  $event [roomId, prodid, uid, checkIn, checkOut, summary, description]
	 * @param \MPHB\Entities\Booking $booking
	 * @param string                 $syncId
	 * @param int                    $queueId
	 */
	protected function updateBooking( $event, $booking, $syncId, $queueId ) {
		$bookingId = $booking->getId();

		update_post_meta( $bookingId, 'mphb_ical_prodid', $event['prodid'], $booking->getICalProdid() );
		update_post_meta( $bookingId, 'mphb_ical_summary', $event['summary'], $booking->getICalSummary() );
		update_post_meta( $bookingId, 'mphb_ical_description', $event['description'], $booking->getICalDescription() );
		// Update dates (it's for updateOne(): the outdated booking may have different dates)
		update_post_meta( $bookingId, 'mphb_check_in_date', $event['checkIn'], $booking->getCheckInDate()->format( 'Y-m-d' ) );
		update_post_meta( $bookingId, 'mphb_check_out_date', $event['checkOut'], $booking->getCheckOutDate()->format( 'Y-m-d' ) );
		// Update sync_id in the case if booking belonged to another calendar
		update_post_meta( $bookingId, '_mphb_sync_id', $syncId, $booking->getSyncId() );
		// Mark as actual/not outdated booking
		update_post_meta( $bookingId, '_mphb_sync_queue_id', $queueId, $booking->getSyncQueueId() );

		$reservedRooms  = $booking->getReservedRooms();
		$reservedRoom   = reset( $reservedRooms );
		$reservedRoomId = $reservedRoom->getId();

		update_post_meta( $reservedRoomId, '_mphb_uid', $event['uid'], $reservedRoom->getUid() );
	}

	/**
	 * @param array                    $event
	 * @param \MPHB\Entities\Booking[] $bookings
	 * @param string                   $syncId
	 * @param int                      $queueId
	 * @return int Updated booking ID.
	 */
	protected function updateOne( $event, $bookings, $syncId, $queueId ) {
		$updateBooking = array_shift( $bookings );

		// Delete all bookings except one
		foreach ( $bookings as $booking ) {
			$reservedRooms = $booking->getReservedRooms();

			foreach ( $reservedRooms as $reservedRoom ) {
				MPHB()->getReservedRoomRepository()->delete( $reservedRoom );
			}

			MPHB()->getBookingRepository()->delete( $booking );
		}

		// Update one booking
		$this->updateBooking( $event, $updateBooking, $syncId, $queueId );

		return $updateBooking->getId();
	}
}
