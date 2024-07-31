<?php

namespace MPHB\iCal;

use \MPHB\Libraries\iCalendar\ZCiCal;
use \MPHB\Libraries\iCalendar\ZCiCalNode;
use \MPHB\Libraries\iCalendar\ZCiCalDataNode;
use \MPHB\PostTypes\BookingCPT\Statuses as BookingStatuses;
use \MPHB\Utils\DateUtils;

class Exporter {

	public function export( $roomId ) {
		$bookings = $this->pullBookings( $roomId );
		$blocks   = $this->pullBlocks( $roomId );

		// Time when calendar was created. Format: "Ymd\THis\Z"
		$datestamp = ZCiCal::fromUnixDateTime() . 'Z';

		// Create calendar
		$calendar = new iCal();
		$calendar->removeMethodProperty(); // Remove property METHOD

		// Change default PRODID
		$prodid = '-//' . mphb_current_domain() . '//Hotel Booking ' . MPHB()->getVersion();
		$calendar->setProdid( $prodid );

		// Fill the calendar with events
		$this->addBookings( $calendar, $datestamp, $bookings, $roomId );
		$this->addBlocks( $calendar, $datestamp, $blocks );

		$postName = get_post_field( 'post_name', $roomId, 'raw' );
		// %domain%-%name%-%date%.ics - booking.dev-comfort-triple-room-1-20170710.ics
		$filename = mphb_current_domain() . '-' . $postName . '-' . date( 'Ymd' ) . '.ics';

		header( 'Content-type: text/calendar; charset=utf-8' );
		header( 'Content-Disposition: inline; filename=' . $filename );
        // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo $calendar->export();
	}

	protected function pullBookings( $roomId ) {
		$bookings = MPHB()->getBookingRepository()->findAll(
			array(
				'fields'      => 'all',
				'post_status' => array(
					BookingStatuses::STATUS_CONFIRMED,
					BookingStatuses::STATUS_PENDING,
					BookingStatuses::STATUS_PENDING_PAYMENT,
				),
				'date_from'   => date( 'Y-m-d H:i:s', 0 ),
				'date_to'     => '2036-01-01 00:00:01', // Max year for 32 bit systems
				'rooms'       => array( $roomId ),
			)
		);

		return $bookings;
	}

	protected function addBookings( $calendar, $datestamp, $bookings, $roomId ) {
		$exportImports = MPHB()->settings()->main()->exportImportedBookings();

		foreach ( $bookings as $booking ) {
			// Don't export imported bookings
			if ( $booking->isImported() && ! $exportImports ) {
				continue;
			}

			$summary     = $this->createSummary( $booking );
			$description = $this->createDescription( $booking, $roomId );

			$reservedRooms = $booking->getReservedRooms();

			foreach ( $reservedRooms as $reservedRoom ) {
				if ( $reservedRoom->getRoomId() == $roomId ) {
					$event = new ZCiCalNode( 'VEVENT', $calendar->curnode );

					// If UID = null, then it did not exist on import
					if ( ! is_null( $reservedRoom->getUid() ) ) {
						$event->addNode( new ZCiCalDataNode( 'UID:' . $reservedRoom->getUid() ) );
					}

					$event->addNode( new ZCiCalDataNode( 'DTSTART;VALUE=DATE:' . ZCiCal::fromSqlDateTime( $booking->getCheckInDate()->format( 'Y-m-d' ) ) ) );
					$event->addNode( new ZCiCalDataNode( 'DTEND;VALUE=DATE:' . ZCiCal::fromSqlDateTime( $booking->getCheckOutDate()->format( 'Y-m-d' ) ) ) );
					$event->addNode( new ZCiCalDataNode( 'DTSTAMP:' . $datestamp ) );
					$event->addNode( new ZCiCalDataNode( 'SUMMARY:' . $summary ) );

					// ZCiCal library can limit DESCRIPTION by 80 characters, so
					// some of the content can be pushed on the next line
					$event->addNode( new ZCiCalDataNode( 'DESCRIPTION:' . $description ) );
				}
			} // For each reserved room
		} // For each booking
	}

	protected function pullBlocks( $roomId ) {
		if ( ! MPHB()->settings()->main()->exportBlockedAccommodations() ) {
			return array();
		}

		$roomTypeId = (int) get_post_meta( $roomId, 'mphb_room_type_id', true );

		$blocks = MPHB()->getRulesChecker()->customRules()->filter(
			array(
				'roomTypeId'  => $roomTypeId,
				'roomId'      => $roomId,
				'restriction' => 'blocked',
			)
		);

		return $blocks;
	}

	protected function addBlocks( $calendar, $datestamp, $blocks ) {
		foreach ( $blocks as $block ) {
			$checkIn    = $block['startDate']->format( 'Y-m-d' );
			$checkOut   = $block['endDate']->format( 'Y-m-d' );
			$roomTypeId = $block['roomTypeId'];
			$roomId     = $block['roomId'];

			// Generate UID using all values, except for "comment"
			$uid = md5( "{$checkIn}/{$checkOut}/{$roomTypeId}/{$roomId}" ) . '@' . mphb_current_domain();

			$event = new ZCiCalNode( 'VEVENT', $calendar->curnode );

			$event->addNode( new ZCiCalDataNode( 'UID:' . $uid ) );
			$event->addNode( new ZCiCalDataNode( 'DTSTART;VALUE=DATE:' . ZCiCal::fromSqlDateTime( $checkIn ) ) );
			$event->addNode( new ZCiCalDataNode( 'DTEND;VALUE=DATE:' . ZCiCal::fromSqlDateTime( $checkOut ) ) );
			$event->addNode( new ZCiCalDataNode( 'SUMMARY:BLOCKED' ) );
			$event->addNode( new ZCiCalDataNode( 'DESCRIPTION:' . $block['comment'] ) );
		}
	}

	/**
	 * @param \MPHB\Entities\Booking $booking
	 * @return string
	 */
	protected function createSummary( $booking ) {
		$summary = $booking->getICalSummary();

		if ( ! empty( $summary ) ) {
			// Remove "", added on import
			$summary = substr( $summary, 1, -1 );
		} else {
			// Generate summary using customer information
			$customer = $booking->getCustomer();
			$name     = $customer ? $customer->getName() : '';
			$summary  = trim( sprintf( '%s (%d)', $name, $booking->getId() ) );
		}

		return $summary;
	}

	/**
	 * @param \MPHB\Entities\Booking $booking
	 * @param int                    $roomId
	 * @return string
	 */
	protected function createDescription( $booking, $roomId ) {
		$description = $booking->getICalDescription();

		if ( ! empty( $description ) ) {
			$description = substr( $description, 1, -1 ); // Remove "", added on import
			$description = str_replace( PHP_EOL, '\n', $description );
		} else {
			$checkIn  = $booking->getCheckInDate()->format( 'Y-m-d' );
			$checkOut = $booking->getCheckOutDate()->format( 'Y-m-d' );
			$nights   = DateUtils::calcNights( $booking->getCheckInDate(), $booking->getCheckOutDate() );

			$description = sprintf( 'CHECKIN: %s\nCHECKOUT: %s\nNIGHTS: %d\n', $checkIn, $checkOut, $nights );

			$propertyName = get_the_title( $roomId );
			if ( ! empty( $propertyName ) ) {
				$description .= sprintf( 'PROPERTY: %s\n', $propertyName );
			}
		}

		return $description;
	}
}
