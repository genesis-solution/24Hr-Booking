<?php

namespace MPHB\Admin\MenuPages\EditBooking;

use MPHB\Entities\Booking;
use MPHB\Entities\ReservedRoom;
use MPHB\Entities\RoomType;
use MPHB\Entities\Service;
use MPHB\Utils\BookingDetailsUtil;
use MPHB\Utils\ParseUtils;
use RuntimeException as Error;

/**
 * @since 3.8
 */
class CheckoutControl extends StepControl {

	/**
	 * @var DateTime
	 */
	protected $checkInDate = null;

	/**
	 * @var DateTime
	 */
	protected $checkOutDate = null;

	/**
	 * @var array Array of [room_id, room_type_id, rate_id, ...].
	 */
	protected $roomDetails = array();

	public function setup() {
		if ( $this->editBooking->isImported() ) {
			throw new Error( __( 'You cannot edit the imported booking. Please update the source booking and resync your calendars.', 'motopress-hotel-booking' ) );
		}

		// Parse dates
		if ( ! isset( $_POST['check_in_date'] ) ) {
			throw new Error( __( 'Check-in date is not set.', 'motopress-hotel-booking' ) );
		} elseif ( ! isset( $_POST['check_out_date'] ) ) {
			throw new Error( __( 'Check-out date is not set.', 'motopress-hotel-booking' ) );
		}

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

		// Parse mapped rooms
		$mapRooms = $this->parseRooms(); // [Room ID => Reserved room ID]
		$roomsMap = mphb_array_flip_duplicates( $mapRooms ); // [Reserved room ID => Room ID or IDs]

		if ( empty( $mapRooms ) ) {
			throw new Error( __( 'There are no accommodations selected for reservation.', 'motopress-hotel-booking' ) );
		}

		// Build checkout room details
		$roomsUtil = BookingDetailsUtil::createFromRooms( array_keys( $mapRooms ) );
		$roomsUtil->addCapacities()->addRates( $this->checkInDate, $this->checkOutDate )->addPresets( $this->editBooking, $roomsMap );

		// Use room IDs as keys to simplify the search in filter functions. But
		// don't forget that CheckoutView will only work with default indexes
		// and fail on custom ones
		$this->roomDetails = $roomsUtil->getValues();

		// Add "allowed_rate_ids"
		foreach ( $this->roomDetails as $roomId => $room ) {
			$rateIds = array_map(
				function ( $rate ) {
					return $rate->getId();
				},
				$room['allowed_rates']
			);
			$this->roomDetails[ $roomId ]['allowed_rate_ids'] = $rateIds;
		}

		parent::setup();

		// Booked info
		add_action( 'mphb_edit_booking_original_checkout', array( '\MPHB\Views\EditBooking\CheckoutView', 'renderBookedDetails' ), 10, 2 );

		add_action( 'mphb_edit_booking_checkout_booked_details', array( '\MPHB\Views\Shortcodes\CheckoutView', 'renderCheckInDate' ), 10, 1 );
		add_action( 'mphb_edit_booking_checkout_booked_details', array( '\MPHB\Views\Shortcodes\CheckoutView', 'renderCheckOutDate' ), 20, 1 );
		add_action( 'mphb_edit_booking_checkout_booked_details', array( '\MPHB\Views\EditBooking\CheckoutView', 'renderReservations' ), 30, 2 );

		add_action( 'mphb_edit_booking_checkout_booked_room_details', array( '\MPHB\Views\Shortcodes\CheckoutView', 'renderRoomTypeTitle' ), 10, 3 );
		add_action( 'mphb_edit_booking_checkout_booked_room_details', array( '\MPHB\Views\EditBooking\CheckoutView', 'renderRoomTitle' ), 20, 1 );
		add_action( 'mphb_edit_booking_checkout_booked_room_details', array( '\MPHB\Views\EditBooking\CheckoutView', 'renderGuests' ), 30, 3 );
		add_action( 'mphb_edit_booking_checkout_booked_room_details', array( '\MPHB\Views\EditBooking\CheckoutView', 'renderRate' ), 40, 4 );
		add_action( 'mphb_edit_booking_checkout_booked_room_details', array( '\MPHB\Views\EditBooking\CheckoutView', 'renderServices' ), 50, 1 );

		// Checkout form
		add_action( 'mphb_edit_booking_checkout_form', array( '\MPHB\Views\EditBooking\CheckoutView', 'renderBookingDetails' ), 10, 2 );

		add_action( 'mphb_edit_booking_checkout_booking_details', array( '\MPHB\Views\Shortcodes\CheckoutView', 'renderCheckInDate' ), 10, 1 );
		add_action( 'mphb_edit_booking_checkout_booking_details', array( '\MPHB\Views\Shortcodes\CheckoutView', 'renderCheckOutDate' ), 20, 1 );
		add_action( 'mphb_edit_booking_checkout_booking_details', array( '\MPHB\Views\EditBooking\CheckoutView', 'renderAccommodations' ), 30, 2 );

		add_action( 'mphb_edit_booking_checkout_room_details', array( '\MPHB\Views\Shortcodes\CheckoutView', 'renderRoomTypeTitle' ), 10, 3 );
		add_action( 'mphb_edit_booking_checkout_room_details', array( '\MPHB\Views\EditBooking\CheckoutView', 'renderRoomTitle' ), 20, 1 );
		add_action( 'mphb_edit_booking_checkout_room_details', array( '\MPHB\Views\Shortcodes\CheckoutView', 'renderGuestsChooser' ), 30, 4 );
		add_action( 'mphb_edit_booking_checkout_room_details', array( '\MPHB\Views\Shortcodes\CheckoutView', 'renderRateChooser' ), 40, 5 );
		add_action( 'mphb_edit_booking_checkout_room_details', array( '\MPHB\Views\Shortcodes\CheckoutView', 'renderServiceChooser' ), 50, 4 );

		// Don't display coupon field, price breakdown, checkout text, customer
		// details and total price - we don't need them on Edit Booking page

		// Add filters to preset data
		add_filter( 'mphb_sc_checkout_preset_adults', array( $this, 'presetAdults' ), 10, 3 );
		add_filter( 'mphb_sc_checkout_preset_children', array( $this, 'presetChildren' ), 10, 3 );
		add_filter( 'mphb_sc_checkout_preset_guest_name', array( $this, 'presetGuestName' ), 10, 2 );
		add_filter( 'mphb_sc_checkout_preset_rate_id', array( $this, 'presetRate' ), 10, 2 );
		add_filter( 'mphb_sc_checkout_is_selected_service', array( $this, 'presetSelectedService' ), 10, 3 );
		add_filter( 'mphb_sc_checkout_preset_service_adults', array( $this, 'presetServiceAdults' ), 10, 3 );
		add_filter( 'mphb_sc_checkout_preset_service_quantity', array( $this, 'presetServiceQuantity' ), 10, 3 );
	}

	/**
	 * @return array [Room ID => Reserved room ID]
	 */
	protected function parseRooms() {
		$bookedRooms = $this->editBooking->getRoomIds();
		$mapRooms    = array();

		if ( isset( $_POST['map_rooms'] ) && is_array( $_POST['map_rooms'] ) ) {

            // phpcs:ignore WordPress.Security.ValidatedSanitizedInput
			foreach ( $_POST['map_rooms'] as $mapInfo ) {

				if ( ! isset( $mapInfo['room_id'], $mapInfo['reserved_room_id'] ) ) {
					continue;
				}

				$roomId         = mphb_posint( $mapInfo['room_id'] );
				$reservedRoomId = mphb_posint( $mapInfo['reserved_room_id'] );

				if ( ! in_array( $reservedRoomId, $bookedRooms ) ) {
					$reservedRoomId = 0;
				}

				if ( $roomId > 0 ) {
					$mapRooms[ $roomId ] = $reservedRoomId;
				}
			}
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
		// Show booked data
		$bookedRooms = BookingDetailsUtil::createFromBooking( $editBooking );
		$bookedRooms->addFields(
			array(
				'from_date' => $editBooking->getCheckInDate(),
				'to_date'   => $editBooking->getCheckOutDate(),
				'booking'   => $editBooking,
			)
		);

		mphb_get_template_part(
			'edit-booking/last-checkout',
			array(
				'booking' => $editBooking,
				'rooms'   => $bookedRooms->mapForCheckout(),
			)
		);

		// Show checkout form
		$newBooking = BookingDetailsUtil::createBooking( $this->checkInDate, $this->checkOutDate, $this->roomDetails );
		$dateFormat = MPHB()->settings()->dateTime()->getDateFormat();

		mphb_get_template_part(
			'edit-booking/checkout-form',
			array(
				'actionUrl'    => $settings['action_url'],
				'nextStep'     => $settings['next_step'],
				'checkInDate'  => $this->checkInDate->format( $dateFormat ),
				'checkOutDate' => $this->checkOutDate->format( $dateFormat ),
				'booking'      => $newBooking,
				'rooms'        => array_values( $this->roomDetails ), // Reset indexes
			)
		);
	}

	/**
	 * @param int          $adults
	 * @param RoomType     $roomType
	 * @param ReservedRoom $reservedRoom
	 * @return int
	 */
	public function presetAdults( $adults, $roomType, $reservedRoom ) {
		$roomId = $reservedRoom->getRoomId();

		if ( isset( $this->roomDetails[ $roomId ]['presets']['adults'] ) ) {
			$adults = $this->roomDetails[ $roomId ]['presets']['adults'];
		}

		return $adults;
	}

	/**
	 * @param int          $children
	 * @param RoomType     $roomType
	 * @param ReservedRoom $reservedRoom
	 * @return int
	 */
	public function presetChildren( $children, $roomType, $reservedRoom ) {
		$roomId = $reservedRoom->getRoomId();

		if ( isset( $this->roomDetails[ $roomId ]['presets']['children'] ) ) {
			$children = $this->roomDetails[ $roomId ]['presets']['children'];
		}

		return $children;
	}

	/**
	 * @param string       $guestName
	 * @param ReservedRoom $reservedRoom
	 * @return string
	 */
	public function presetGuestName( $guestName, $reservedRoom ) {
		$roomId = $reservedRoom->getRoomId();

		if ( isset( $this->roomDetails[ $roomId ]['presets']['guest_name'] ) ) {
			$guestName = $this->roomDetails[ $roomId ]['presets']['guest_name'];
		}

		return $guestName;
	}

	public function presetRate( $rateId, $reservedRoom ) {
		$roomId = $reservedRoom->getRoomId();

		if ( isset( $this->roomDetails[ $roomId ]['presets']['rate_id'] ) ) {
			$presetId = $this->roomDetails[ $roomId ]['presets']['rate_id'];

			// Don't set the unallowed rate
			if ( in_array( $presetId, $this->roomDetails[ $roomId ]['allowed_rate_ids'] ) ) {
				$rateId = $presetId;
			}
		}

		return $rateId;
	}

	/**
	 * @param bool         $isSelected
	 * @param Service      $service
	 * @param ReservedRoom $reservedRoom
	 * @return bool
	 */
	public function presetSelectedService( $isSelected, $service, $reservedRoom ) {
		$serviceId = $service->getOriginalId();
		$roomId    = $reservedRoom->getRoomId();

		if ( isset( $this->roomDetails[ $roomId ]['presets']['services'][ $serviceId ] ) ) {
			$isSelected = true;
		}

		return $isSelected;
	}

	/**
	 * @param int          $adults
	 * @param Service      $service
	 * @param ReservedRoom $reservedRoom
	 * @return int
	 */
	public function presetServiceAdults( $adults, $service, $reservedRoom ) {
		$serviceId = $service->getOriginalId();
		$roomId    = $reservedRoom->getRoomId();

		if ( isset( $this->roomDetails[ $roomId ]['presets']['services'][ $serviceId ] ) ) {
			$presetAdults   = $this->roomDetails[ $roomId ]['presets']['services'][ $serviceId ]['adults'];
			$adultsCapacity = $this->roomDetails[ $roomId ]['adults'];

			// Adults capacity equal to min adults in 3 cases:
			// 1) capacity is actually so low (don't need the preset);
			// 2) we did not added the capacities via addCapacities() (don't preset
			// the min adults - the default behaviour is to set max default value);
			// 3) we failed in reading of room type data (don't preset the min
			// adults - same as previous).
			if ( $adultsCapacity != mphb_get_min_adults() ) {
				$adults = min( $presetAdults, $adultsCapacity );
			}
		}

		return $adults;
	}

	/**
	 * @param int          $quantity
	 * @param Service      $service
	 * @param ReservedRoom $reservedRoom
	 * @return int
	 */
	public function presetServiceQuantity( $quantity, $service, $reservedRoom ) {
		$serviceId = $service->getOriginalId();
		$roomId    = $reservedRoom->getRoomId();

		if ( isset( $this->roomDetails[ $roomId ]['presets']['services'][ $serviceId ] ) ) {
			$quantity = $this->roomDetails[ $roomId ]['presets']['services'][ $serviceId ]['quantity'];
		}

		return $quantity;
	}
}
