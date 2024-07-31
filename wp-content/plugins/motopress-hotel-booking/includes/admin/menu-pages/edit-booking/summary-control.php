<?php

namespace MPHB\Admin\MenuPages\EditBooking;

use MPHB\Entities\Booking;
use MPHB\Utils\ParseUtils;
use RuntimeException as Error;

/**
 * @since 3.8
 */
class SummaryControl extends StepControl {

	/**
	 * @var DateTime
	 */
	protected $checkInDate = null;

	/**
	 * @var DateTime
	 */
	protected $checkOutDate = null;

	/**
	 * @var array [Room ID => Reserved room ID]
	 */
	protected $mapRooms = array();

	public function setup() {
		if ( $this->editBooking->isImported() ) {
			throw new Error( __( 'You cannot edit the imported booking. Please update the source booking and resync your calendars.', 'motopress-hotel-booking' ) );
		} elseif ( ! isset( $_POST['check_in_date'] ) ) {
			throw new Error( __( 'Check-in date is not set.', 'motopress-hotel-booking' ) );
		} elseif ( ! isset( $_POST['check_out_date'] ) ) {
			throw new Error( __( 'Check-out date is not set.', 'motopress-hotel-booking' ) );
		}

		// Parse dates
        // phpcs:ignore WordPress.Security.ValidatedSanitizedInput
		$this->checkInDate = ParseUtils::parseCheckInDate( $_POST['check_in_date'], array( 'allow_past_dates' => true ) );
        // phpcs:ignore WordPress.Security.ValidatedSanitizedInput
		$this->checkOutDate = ParseUtils::parseCheckOutDate(
			$_POST['check_out_date'],
			array(
				'check_booking_rules' => false,
				'check_in_date'       => $this->checkInDate,
			)
		);

		$replaceRooms = $this->parseReplaceRooms();

		$addRooms = $this->parseAddRooms();
		$addRooms = array_diff( $addRooms, $replaceRooms ); // Don't allow to use a single room for multiple reservations

		if ( empty( $replaceRooms ) && empty( $addRooms ) ) {
			throw new Error( __( 'There are no accommodations selected for reservation.', 'motopress-hotel-booking' ) );
		}

		$this->mapRooms = $this->mapRooms( $replaceRooms, $addRooms );

		parent::setup();
	}

	/**
	 * @return int[] [Reserved room ID => Room ID]
	 */
	protected function parseReplaceRooms() {
		$replaceRooms = array();

		if ( isset( $_POST['replace_rooms'] ) && is_array( $_POST['replace_rooms'] ) ) {

            // phpcs:ignore WordPress.Security.ValidatedSanitizedInput
			foreach ( $_POST['replace_rooms'] as $reservedRoomId => $roomId ) {
				$reservedRoomId = mphb_posint( $reservedRoomId );
				$roomId         = mphb_posint( $roomId );

				if ( $reservedRoomId > 0 && $roomId > 0 ) {
					$replaceRooms[ $reservedRoomId ] = $roomId;
				}
			}
		}

		// Don't allow to use a single room for multiple reservations
		$replaceRooms = array_unique( $replaceRooms );

		return $replaceRooms;
	}

	/**
	 * @return int[] [Room (IDs) to add]
	 */
	protected function parseAddRooms() {
		$addRooms = array();

		if ( isset( $_POST['add_rooms'] ) ) {
            // phpcs:ignore WordPress.Security.ValidatedSanitizedInput
			$addRooms = array_map( 'wp_unslash', $_POST['add_rooms'] );
			$addRooms = array_map( 'mphb_posint', $addRooms );
			$addRooms = array_filter( $addRooms );
		}

		// Don't allow to use a single room for multiple reservations
		$addRooms = array_unique( $addRooms );

		return $addRooms;
	}

	/**
	 * @param int[] $replaceRooms
	 * @param int[] $addRooms
	 * @return array [Room ID => Reserved room ID]
	 */
	protected function mapRooms( $replaceRooms, $addRooms ) {
		$bookedRooms = $this->editBooking->getRoomIds();
		$mapRooms    = array();

		foreach ( $replaceRooms as $reservedRoomId => $roomId ) {
			// Allow to copy the data only from the booked rooms
			if ( in_array( $reservedRoomId, $bookedRooms ) ) {
				$mapRooms[ $roomId ] = $reservedRoomId;
			} else {
				$mapRooms[ $roomId ] = 0;
			}
		}

		foreach ( $addRooms as $roomId ) {
			$mapRooms[ $roomId ] = 0;
		}

		return $mapRooms;
	}

	/**
	 * @param Booking $editBooking Completely similar to the booking in the constructor.
	 * @param array   $settings
	 *
	 * @see \MPHB\Admin\MenuPages\EditBookingMenuPage2::renderValid()
	 */
	public function display( $editBooking, $settings ) {
		$bookedRooms = $editBooking->getRoomIds();

		$roomIds = array_merge( array_keys( $this->mapRooms ), $bookedRooms );
		$roomIds = array_unique( $roomIds );
		sort( $roomIds );

		$roomsList = array_combine( $roomIds, array_map( 'get_the_title', $roomIds ) );

		$copyFrom = array( 0 => __( '— Add new —', 'motopress-hotel-booking' ) );
		foreach ( $bookedRooms as $reservedRoomId ) {
			$copyFrom[ $reservedRoomId ] = $roomsList[ $reservedRoomId ];
		}

		// Show transitions table
		$dateFormat = MPHB()->settings()->dateTime()->getDateFormat();

		mphb_get_template_part(
			'edit-booking/summary-table',
			array(
				'actionUrl'    => $settings['action_url'],
				'nextStep'     => $settings['next_step'],
				'checkInDate'  => $this->checkInDate->format( $dateFormat ),
				'checkOutDate' => $this->checkOutDate->format( $dateFormat ),
				'mapRooms'     => $this->mapRooms,
				'copyFrom'     => $copyFrom,
				'roomsList'    => $roomsList,
			)
		);
	}
}
