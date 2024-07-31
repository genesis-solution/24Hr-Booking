<?php

namespace MPHB\Notifier\Utils;

use MPHB\Entities\Booking;
use MPHB\Entities\Customer;
use MPHB\Entities\ReservedRoom;
use MPHB\PostTypes\BookingCPT\Statuses as BookingStatuses;

/**
 * @since 1.0
 */
class BookingUtils {

	/**
	 * @param \MPHB\Notifier\Entities\Notification $notification
	 * @return int[]
	 * @since 1.0
	 */
	public static function findByNotification( $notification ) {

		global $wpdb;

		// $period, $unit, $compare, $field
		extract( $notification->getTrigger() );

		// Get all booking IDs we need to trigger
		$bookingIds = static::findByTriggerDay( $period, $compare, $field );

		if ( ! empty( $bookingIds ) ) {

			// Exclude bookings that already sent this notification
			$excludeSql = $wpdb->prepare(
				"SELECT post_id FROM {$wpdb->postmeta} WHERE post_id IN (" . implode( ', ', $bookingIds ) .
				") AND meta_key = '_mphb_notification_sent' AND meta_value = %s GROUP BY post_id",
				$notification->getId()
			);
			$excludeIds = $wpdb->get_col( $excludeSql );

			$bookingIds = array_diff( $bookingIds, $excludeIds );

			// filter booking ids by accommodation types in notification
			$filteredBookingIds = array();

			foreach ( $bookingIds as $bookingId ) {

				$booking = MPHB()->getBookingRepository()->findById( $bookingId );

				if ( static::isNotificationFitsToGoForBookingReservedRooms( $notification, $booking ) ) {

					$filteredBookingIds[] = $bookingId;
				}
			}

			$bookingIds = $filteredBookingIds;
		}

		return $bookingIds;
	}

	/**
	 * @param \MPHB\Notifier\Entities\Notification $notification
	 * @param \MPHB\Entities\Booking               $booking
	 * @return bool
	 */
	public static function isNotificationFitsToGoForNewBooking( $notification, $booking ) {

		$daysToCheckIn = \MPHB\Utils\DateUtils::calcNights( new \DateTime(), $booking->getCheckInDate() );

		// We are interested only in notifications with trigger "X days before check-in"
		// (only "check-in" and only "before" - you can't book in the past dates)
		if ( 'after' == $notification->getTrigger()['compare'] ||
			'check-out' == $notification->getTrigger()['field']
		) {
			return false;
		}

		return static::isNotificationFitsToGoForBookingReservedRooms( $notification, $booking ) &&
			$daysToCheckIn <= $notification->getTrigger()['period'];
	}

	/**
	 * @param \MPHB\Notifier\Entities\Notification $notification
	 * @param \MPHB\Entities\Booking               $booking
	 * @return bool
	 */
	public static function isNotificationDisabledForReservationAfterTrigger( $notification, $booking ) {

		$result = false;

		if ( $notification->isDisabledForReservationAfterTrigger() &&
			'before' == $notification->getTrigger()['compare']
		) {

			$daysToTrigger = 0;

			if ( 'check-in' == $notification->getTrigger()['field'] ) {

				$daysToTrigger = \MPHB\Utils\DateUtils::calcNights( $booking->getDateTime(), $booking->getCheckInDate() );

			} elseif ( 'check-out' == $notification->getTrigger()['field'] ) {

				$daysToTrigger = \MPHB\Utils\DateUtils::calcNights( $booking->getDateTime(), $booking->getCheckOutDate() );
			}

			$result = $daysToTrigger < $notification->getTrigger()['period'];
		}

		return $result;
	}

	/**
	 * @param \MPHB\Notifier\Entities\Notification $notification
	 * @param \MPHB\Entities\Booking               $booking
	 * @return bool
	 */
	public static function isNotificationFitsToGoForBookingReservedRooms( $notification, $booking ) {

		$reservedRooms = $booking->getReservedRooms();

		$isNotificationAccommodationTypeFitsToBooking = false;

		foreach ( $reservedRooms as $room ) {

			// if notification is old it could not have accommodation type ids at all
			// we treat such notifications as for all accommodation types!
			if ( ! is_array( $notification->getAccommodationTypeIds() ) ||
				empty( $notification->getAccommodationTypeIds() ) ||
				in_array( 0, $notification->getAccommodationTypeIds() ) || // All accommodation types
				in_array( $room->getRoomTypeId(), $notification->getAccommodationTypeIds() )
			) {

				$isNotificationAccommodationTypeFitsToBooking = true;
				break;
			}
		}

		return $isNotificationAccommodationTypeFitsToBooking;
	}

	/**
	 * @param int    $period
	 * @param string $compare before|after
	 * @param string $field check-in|check-out
	 * @return int[]
	 *
	 * @since 1.0
	 */
	public static function findByTriggerDay( $period, $compare, $field ) {

		switch ( $compare ) {
			case 'before':
			case 'after':
				$triggerDates = mphb_notifier_get_trigger_dates( $period, $compare );

				$fromDate = $triggerDates['from'];
				$toDate   = $triggerDates['to'];

				break;

			default:
				return array();
				break;
		}

		switch ( $field ) {

			case 'check-in':
				$metaField = 'mphb_check_in_date';
				break;

			case 'check-out':
				$metaField = 'mphb_check_out_date';
				break;

			default:
				return array();
			break;
		}

		$ids = mphb()->getBookingPersistence()->getPosts(
			array(
				'fields'      => 'ids',
				'post_status' => BookingStatuses::STATUS_CONFIRMED,
				'meta_query'  => array(
					'relation' => 'AND',
					array(
						'key'     => $metaField,
						'value'   => $fromDate->format( 'Y-m-d' ),
						'compare' => '>=',
					),
					array(
						'key'     => $metaField,
						'value'   => $toDate->format( 'Y-m-d' ),
						'compare' => '<=',
					),
				),
			)
		);

		return $ids;
	}

	/**
	 * @return \MPHB\Entities\Booking
	 * @since 1.0
	 */
	public static function getTestBooking() {

		$booking = static::getRandomBooking();
		$booking = static::getFakeBooking( $booking );

		return $booking;
	}

	/**
	 * @return \MPHB\Entities\Booking|null Any booking with customer email set.
	 * @since 1.0
	 */
	public static function getRandomBooking() {

		return mphb()->getBookingRepository()->findOne(
			array(
				'orderby'    => 'rand',
				'meta_query' => array(
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
				),
			)
		);
	}

	/**
	 * @param \MPHB\Entities\Booking $booking Optional. Booking prototype. NULL by default.
	 * @return \MPHB\Entities\Booking
	 * @since 1.0
	 */
	public static function getFakeBooking( $booking = null ) {

		if ( is_null( $booking ) ) {

			return new Booking(
				array(
					'id'             => 0,
					'check_in_date'  => new \DateTime( '+0 days' ), // "new \DateTime('today')" will set time to 00:00:00
					'check_out_date' => new \DateTime( '+1 day' ),
					'reserved_rooms' => array(
						new ReservedRoom(
							array(
								'id'         => 0,
								'room_id'    => 0,
								'rate_id'    => 0,
								'adults'     => 1,
								'children'   => 0,
								'booking_id' => 0,
								'uid'        => mphb_generate_uid(),
							)
						),
					),
					'customer'       => new Customer(
						array(
							'email'      => mphb()->settings()->emails()->getHotelAdminEmail(),
							'first_name' => 'First',
							'last_name'  => 'Last',
							'phone'      => '+0123456789',
						)
					),
					'status'         => BookingStatuses::STATUS_CONFIRMED,
					'total_price'    => 100,
				)
			);

		} else {

			return new Booking(
				array(
					'id'             => $booking->getId(),
					'check_in_date'  => $booking->getCheckInDate(),
					'check_out_date' => $booking->getCheckOutDate(),
					'reserved_rooms' => $booking->getReservedRooms(),
					'customer'       => new Customer(
						array(
							'email'      => mphb()->settings()->emails()->getHotelAdminEmail(),
							'first_name' => $booking->getCustomer()->getFirstName(),
							'last_name'  => $booking->getCustomer()->getLastName(),
							'phone'      => $booking->getCustomer()->getPhone(),
						)
					),
					'status'         => $booking->getStatus(),
					'total_price'    => $booking->getTotalPrice(),
				)
			);
		}
	}
}
