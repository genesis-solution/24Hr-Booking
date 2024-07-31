<?php

namespace MPHB\Admin\MenuPages\EditBooking;

use MPHB\Entities\ReservedRoom;
use MPHB\Utils\ParseUtils;
use RuntimeException as Error;

/**
 * @since 3.8
 */
class BookingControl extends StepControl {

	public function setup() {

		if ( $this->editBooking->isImported() ) {

			throw new Error( __( 'You cannot edit the imported booking. Please update the source booking and resync your calendars.', 'motopress-hotel-booking' ) );

		} elseif ( ! isset( $_POST['checkout_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['checkout_nonce'] ) ), 'edit-booking' ) ) {

			throw new Error( __( 'Request does not pass security verification. Please refresh the page and try one more time.', 'motopress-hotel-booking' ) );

		} elseif ( ! isset( $_POST['check_in_date'] ) ) {

			throw new Error( __( 'Check-in date is not set.', 'motopress-hotel-booking' ) );

		} elseif ( ! isset( $_POST['check_out_date'] ) ) {

			throw new Error( __( 'Check-out date is not set.', 'motopress-hotel-booking' ) );

		} elseif ( ! isset( $_POST['mphb_room_details'] ) ) {

			throw new Error( __( 'There are no accommodations selected for reservation.', 'motopress-hotel-booking' ) );
		}

        // phpcs:ignore WordPress.Security.ValidatedSanitizedInput
		$checkInDate = ParseUtils::parseCheckInDate( $_POST['check_in_date'], array( 'allow_past_dates' => true ) );
        // phpcs:ignore WordPress.Security.ValidatedSanitizedInput
		$checkOutDate = ParseUtils::parseCheckOutDate(
			$_POST['check_out_date'],
			array(
				'check_booking_rules' => false,
				'check_in_date'       => $checkInDate,
			)
		);
        // phpcs:ignore WordPress.Security.ValidatedSanitizedInput
		$roomDetails = ParseUtils::parseRooms(
			$_POST['mphb_room_details'],
			array(
				'check_in_date'  => $checkInDate,
				'check_out_date' => $checkOutDate,
				'edit_booking'   => $this->editBooking,
			)
		);
		$booking     = $this->editBooking;

		$oldRooms = $booking->getReservedRooms();
		$newRooms = $this->mergeRooms( $roomDetails, $oldRooms );

		// Update booking with new data
		$booking->setDates( $checkInDate, $checkOutDate );
		$booking->setRooms( $newRooms );
		$booking->updateTotal();

		// Update booking
		$saved = MPHB()->getBookingRepository()->save( $booking );

		if ( $saved ) {
			MPHB()->getBookingRepository()->updateReservedRooms( $booking->getId() );
		} else {
			throw new Error( __( 'Unable to update booking. Please try again.', 'motopress-hotel-booking' ) );
		}

		$booking->addLog( __( 'Booking was edited.', 'motopress-hotel-booking' ) );

		// Reload booking after update. Refresh its data, such as reserved rooms
		// and their IDs
		$booking = mphb_get_booking( $booking->getId(), true );
		do_action( 'mphb_update_edited_booking', $booking, $oldRooms );

		// Redirect back to booking post page
		$redirectUrl = get_edit_post_link( $booking->getId(), 'raw' );
		$redirectUrl = add_query_arg( 'message', 1, $redirectUrl ); // Add "Post updated" message

		wp_safe_redirect( $redirectUrl );
		exit;

		// parent::setup(); - don't need this
	}

	/**
	 * @param array          $parsedRooms
	 * @param ReservedRoom[] $reservedRooms
	 * @return ReservedRoom[]
	 */
	protected function mergeRooms( $parsedRooms, $reservedRooms ) {
		// Use old UIDs for same rooms
		$uids = array();

		foreach ( $reservedRooms as $reservedRoom ) {
			$uids[ $reservedRoom->getRoomId() ] = $reservedRoom->getUid();
		}

		// Create new list of reserved rooms
		$rooms = array();

		foreach ( $parsedRooms as $room ) {
			$services = array_map( array( '\MPHB\Entities\ReservedService', 'create' ), $room['services'] );
			$services = array_filter( $services ); // Filter NULLs

			$uid = isset( $uids[ $room['room_id'] ] ) ? $uids[ $room['room_id'] ] : mphb_generate_uid();

			$rooms[] = new ReservedRoom(
				array(
					'room_id'           => $room['room_id'],
					'rate_id'           => $room['rate_id'],
					'adults'            => $room['adults'],
					'children'          => $room['children'],
					'guest_name'        => $room['guest_name'],
					'reserved_services' => $services,
					'uid'               => $uid,
				)
			);
		}

		return $rooms;
	}
}
