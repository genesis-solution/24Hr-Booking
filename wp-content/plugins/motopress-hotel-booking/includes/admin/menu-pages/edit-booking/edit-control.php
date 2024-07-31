<?php

namespace MPHB\Admin\MenuPages\EditBooking;

use MPHB\Entities\Booking;
use MPHB\Utils\BookingDetailsUtil;
use MPHB\Utils\ParseUtils;
use DateTime;
use RuntimeException as Error;

/**
 * @since 3.8
 */
class EditControl extends StepControl {

	/**
	 * @var DateTime
	 */
	protected $checkInDate = null;

	/**
	 * @var DateTime
	 */
	protected $checkOutDate = null;

	/**
	 * @var array [Room ID => [room_id, room_title, room_type_id, room_type_title, adults, children, status]]
	 *     (all IDs are original, titles - translated).
	 */
	protected $reservedRooms = array();

	/**
	 * All available rooms for current dates, including the rooms booked in the
	 * editing booking.
	 *
	 * @var array [Room ID => [room_id, room_title, room_type_id, room_type_title]]
	 *     (all IDs are original, titles - translated).
	 */
	protected $availableRooms = array();

	/**
	 * @param Booking $editBooking
	 */
	public function __construct( $editBooking ) {
		parent::__construct( $editBooking );

		$this->checkInDate  = $editBooking->getCheckInDate();
		$this->checkOutDate = $editBooking->getCheckOutDate();
	}

	/**
	 * @throws Error
	 */
	public function setup() {
		if ( $this->editBooking->isImported() ) {
			throw new Error( __( 'You cannot edit the imported booking. Please update the source booking and resync your calendars.', 'motopress-hotel-booking' ) );
		}

		// Parse dates
        // phpcs:ignore WordPress.Security.ValidatedSanitizedInput
		$this->checkInDate = isset( $_POST['check_in_date'] ) ? ParseUtils::parseCheckInDate( $_POST['check_in_date'], array( 'allow_past_dates' => true ) ) : $this->checkInDate;
        // phpcs:ignore WordPress.Security.ValidatedSanitizedInput
		$this->checkOutDate = isset( $_POST['check_out_date'] ) ? ParseUtils::parseCheckOutDate(
			$_POST['check_out_date'],
			array(
				'check_booking_rules' => false,
				'check_in_date'       => $this->checkInDate,
			)
		) : $this->checkOutDate;

		// Get available rooms list
		$availableRooms = mphb_get_available_rooms( $this->checkInDate, $this->checkOutDate, array( 'exclude_bookings' => $this->editBooking->getId() ) );
		$roomsUtil      = BookingDetailsUtil::createFromAvailableRooms( $availableRooms );

		$this->availableRooms = $roomsUtil->addTitles()->getValues();

		// Prepare reserved rooms list
		$roomsUtil = BookingDetailsUtil::createFromBooking( $this->editBooking );

		$this->reservedRooms = $roomsUtil->addTitles()->addCapacities()->getValues();

		foreach ( array_keys( $this->reservedRooms ) as $roomsId ) {
			// Add status "available"|"unavailable"
			$isAvailable                               = isset( $this->availableRooms[ $roomsId ] );
			$this->reservedRooms[ $roomsId ]['status'] = $isAvailable ? 'available' : 'unavailable';
		}

		parent::setup();

		add_filter( 'mphb_admin_js_data', array( $this, 'addTranslations' ) );
	}

	public function addTranslations( $jsData ) {
		$jsData['_data']['translations']['available'] = __( 'Available', 'motopress-hotel-booking' );
		$jsData['_data']['translations']['remove']    = __( 'Remove', 'motopress-hotel-booking' );
		$jsData['_data']['translations']['replace']   = __( 'Replace', 'motopress-hotel-booking' );

		return $jsData;
	}

	/**
	 * @param Booking $editBooking Completely similar to the booking in the constructor.
	 * @param array   $settings
	 *
	 * @see \MPHB\Admin\MenuPages\EditBookingMenuPage2::renderValid()
	 */
	public function display( $editBooking, $settings ) {
		$dateFormat = MPHB()->settings()->dateTime()->getDateFormat();

		// Show search form for check-in/check-out dates
		mphb_get_template_part(
			'edit-booking/edit-dates',
			array(
				'actionUrl'    => $settings['action_url'],
				'nextStep'     => $settings['current_step'],
				'checkInDate'  => $this->checkInDate->format( $dateFormat ),
				'checkOutDate' => $this->checkOutDate->format( $dateFormat ),
			)
		);

		// Show reserved rooms table
		mphb_get_template_part(
			'edit-booking/edit-reserved-rooms',
			array(
				'actionUrl'     => $settings['action_url'],
				'nextStep'      => $settings['next_step'],
				'checkInDate'   => $this->checkInDate->format( $dateFormat ),
				'checkOutDate'  => $this->checkOutDate->format( $dateFormat ),
				'reservedRooms' => $this->reservedRooms,
			)
		);

		// Add popup
		$availableRoomTypes = array();

		foreach ( $this->availableRooms as $room ) {
			$availableRoomTypes[ $room['room_type_id'] ] = $room['room_type_title'];
		}

		mphb_get_template_part(
			'edit-booking/add-room-popup',
			array(
				'availableRooms'     => $this->availableRooms,
				'availableRoomTypes' => $availableRoomTypes,
			)
		);
	}
}
