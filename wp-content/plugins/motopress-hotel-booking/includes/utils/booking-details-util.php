<?php

namespace MPHB\Utils;

use MPHB\Entities\Booking;
use MPHB\Entities\ReservedRoom;
use MPHB\Entities\ReservedService;
use DateTime;

/**
 * Utility to get the maximum required information about the rooms starting from
 * their IDs.
 *
 * Extend IDs (minimum set of data)
 *     <pre>
 *         [
 *             Room ID => [
 *                 room_id,
 *                 room_type_id
 *             ]
 *         ]
 *     </pre>
 * with titles, capacities and rate data:
 *     <pre>
 *         [
 *             Room ID => [
 *                 room_id,
 *                 room_title,      // After addTitles()
 *                 room_type_id
 *                 room_type_title, // After addTitles()
 *                 rate_id          // After addRates() or addBooked()
 *                 rate_title,      // After addRates() or addBooked()
 *                 allowed_rates    // After addRates() or addBooked()
 *                 adults,          // After addCapacities()
 *                 children,        // After addCapacities()
 *                 presets => [     // After addPresets()
 *                     adults,
 *                     children,
 *                     guest_name,
 *                     services
 *                 ]
 *             ]
 *         ]
 *     </pre>
 *
 * @since 3.8
 */
class BookingDetailsUtil {

	/**
	 * @var array [Room ID => [room_id, room_type_id, ...]]
	 */
	protected $rooms = array();

	/**
	 * @param array $rooms [Room ID => [room_id, room_type_id]]
	 */
	public function __construct( $rooms ) {
		$this->rooms = $rooms;
	}

	/**
	 * @param array    $args Optional.
	 *     @param DateTime $args['from_date']
	 *     @param DateTime $args['to_date']
	 *     @param Booking  $args['booking']
	 *     @param array    $args['mapping']
	 * @return self
	 */
	public function addFields( $args = array() ) {
		$this->addTitles();
		$this->addCapacities();

		if ( isset( $args['from_date'], $args['to_date'] ) ) {
			$this->addRates( $args['from_date'], $args['to_date'] );
		} elseif ( isset( $args['booking'] ) ) {
			$mapping = isset( $args['mapping'] ) ? $args['mapping'] : array();
			$this->addBooked( $args['booking'], $mapping );
		}

		return $this;
	}

	/**
	 * @return self
	 */
	public function addTitles() {
		foreach ( $this->rooms as $roomId => &$room ) {
			// Get the ID of the translated room type
			$roomTypeId = apply_filters( '_mphb_translate_post_id', $room['room_type_id'] );

			$room['room_title']      = get_the_title( $roomId );
			$room['room_type_title'] = get_the_title( $roomTypeId );
		}

		unset( $room );

		return $this;
	}

	/**
	 * @return self
	 */
	public function addCapacities() {
		foreach ( $this->rooms as &$room ) {
			$roomType = mphb_get_room_type( $room['room_type_id'] );

			if ( ! is_null( $roomType ) ) {
				$room['adults']   = $roomType->getAdultsCapacity();
				$room['children'] = $roomType->getChildrenCapacity();
			} else {
				$room['adults']   = mphb_get_min_adults();
				$room['children'] = mphb_get_min_children();
			}
		}

		unset( $room );

		return $this;
	}

	/**
	 * @param DateTime Start search date. For example - check-in date.
	 * @param DateTime End search date. For example - check-out date.
	 * @return self
	 */
	public function addRates( $fromDate, $toDate ) {
		$searchArgs = array(
			'check_in_date'  => $fromDate,
			'check_out_date' => $toDate,
		);

		// Don't repeat the search for the same room types
		$foundRates = array(); // [Room type ID => [Allowed rates]]

		foreach ( $this->rooms as &$room ) {
			$roomTypeId = $room['room_type_id'];

			// Get/find allowed rates
			if ( isset( $foundRates[ $roomTypeId ] ) ) {
				$allowedRates = $foundRates[ $roomTypeId ];
			} else {
				$allowedRates              = MPHB()->getRateRepository()->findAllActiveByRoomType( $roomTypeId, $searchArgs );
				$foundRates[ $roomTypeId ] = $allowedRates;
			}

			// Add rates info
			if ( count( $allowedRates ) >= 1 ) {
				$defaultRate    = reset( $allowedRates );
				$translatedRate = apply_filters( '_mphb_translate_rate', $defaultRate );

				$room['rate_id']       = $defaultRate->getId();
				$room['rate_title']    = $translatedRate->getTitle();
				$room['allowed_rates'] = $allowedRates;
			} else {
				$room['rate_id']       = 0;
				$room['rate_title']    = '';
				$room['allowed_rates'] = array();
			}
		}

		unset( $room );

		return $this;
	}

	/**
	 * @param Booking $booking
	 * @param array   $mapping Optional. [Reserved room ID => Room ID(s) to copy data to].
	 * @return self
	 */
	public function addBooked( $booking, $mapping = array() ) {
		foreach ( $booking->getReservedRooms() as $reservedRoom ) {
			$rateId = $reservedRoom->getRateId();

			$rate           = MPHB()->getRateRepository()->findById( $rateId );
			$translatedRate = apply_filters( '_mphb_translate_rate', $rate );

			$rateData = array(
				'rate_id'       => $rateId,
				'rate_title'    => ! is_null( $translatedRate ) ? $translatedRate->getTitle() : '',
				'allowed_rates' => ! is_null( $rate ) ? array( $rate ) : array(),
			);

			// Add rate data to each mapped room
			$roomId = $reservedRoom->getRoomId();

			if ( isset( $mapping[ $roomId ] ) ) {
				$copyTo = (array) $mapping[ $roomId ];
			} else {
				$copyTo = (array) $roomId;
			}

			foreach ( $copyTo as $roomId ) {
				// Don't accidently add the new room (without some fields, titles for example)
				if ( isset( $this->rooms[ $roomId ] ) ) {
					$this->rooms[ $roomId ] = array_merge( $this->rooms[ $roomId ], $rateData );
				}
			}
		} // For each reserved room

		return $this;
	}

	/**
	 * Add presets from the existing booking.
	 *
	 * @param Booking $booking
	 * @param array   $mapping Optional. [Reserved room ID => Room ID(s) to copy data to].
	 * @return self
	 */
	public function addPresets( $booking, $mapping = array() ) {
		foreach ( $booking->getReservedRooms() as $reservedRoom ) {
			$presets = array(
				'adults'     => $reservedRoom->getAdults(),
				'children'   => $reservedRoom->getChildren(),
				'guest_name' => $reservedRoom->getGuestName(),
				'services'   => array(),
			);

			// Add reserved services
			foreach ( $reservedRoom->getReservedServices() as $reservedService ) {
				$presets['services'][ $reservedService->getId() ] = array(
					'adults'   => $reservedService->getAdults(),
					'quantity' => $reservedService->getQuantity(),
				);
			}

			// Add rate (only if it's not 0 (imported booking))
			$rateId = $reservedRoom->getRateId();

			if ( $rateId > 0 ) {
				$presets['rate_id'] = $rateId;
			}

			// Add presets to each mapped room
			$roomId = $reservedRoom->getRoomId();

			if ( isset( $mapping[ $roomId ] ) ) {
				$copyTo = (array) $mapping[ $roomId ];
			} else {
				$copyTo = (array) $roomId;
			}

			foreach ( $copyTo as $roomId ) {
				// Don't accidently add the new room (without some fields, titles for example)
				if ( isset( $this->rooms[ $roomId ] ) ) {
					$this->rooms[ $roomId ]['presets'] = $presets;
				}
			}
		} // For each reserved room

		return $this;
	}

	/**
	 * @return array
	 */
	public function getValues() {
		return $this->rooms;
	}

	/**
	 * @return array
	 */
	public function mapForCheckout() {
		// Views of the checkout form can only work with indexes [0, 1, 2, ...]
		// and fail on custom indexes like room IDs. So just reset them
		return array_values( $this->rooms );
	}

	/**
	 * @param array $availableRooms [Room type ID => [Room IDs]]
	 * @return static
	 */
	public static function createFromAvailableRooms( $availableRooms ) {
		$rooms = array();

		foreach ( $availableRooms as $roomTypeId => $roomIds ) {
			foreach ( $roomIds as $roomId ) {
				$rooms[ $roomId ] = array(
					'room_id'      => $roomId,
					'room_type_id' => $roomTypeId,
				);
			}
		}

		return new static( $rooms );
	}

	/**
	 * @param int[] $roomIds
	 * @return static
	 */
	public static function createFromRooms( $roomIds ) {
		$rooms = array();

		foreach ( $roomIds as $roomId ) {
			$room = MPHB()->getRoomRepository()->findById( $roomId );

			if ( ! is_null( $room ) ) {
				$roomTypeId = $room->getRoomTypeId();

				$rooms[ $roomId ] = array(
					'room_id'      => $roomId,
					'room_type_id' => $roomTypeId,
				);
			}
		}

		return new static( $rooms );
	}

	/**
	 * @param Booking $booking
	 * @return static
	 */
	public static function createFromBooking( $booking ) {
		return static::createFromRooms( $booking->getRoomIds() );
	}

	/**
	 * @param DateTime $checkInDate
	 * @param DateTime $checkOutDate
	 * @param array    $rooms
	 *     @param int      $rooms[]['room_id'] Required.
	 *     @param int      $rooms[]['rate_id'] Required.
	 *     @param int      $rooms[]['adults'] Required.
	 *     @param int      $rooms[]['children'] Required.
	 *     @param array    $rooms[]['presets'] Optional.
	 * @return Booking
	 *
	 * TODO Add customer data.
	 *
	 * @since 3.8
	 */
	public static function createBooking( $checkInDate, $checkOutDate, $rooms ) {
		$reservedRooms = array();

		foreach ( $rooms as $room ) {
			$presets   = isset( $room['presets'] ) ? $room['presets'] : array();
			$adults    = isset( $presets['adults'] ) ? $presets['adults'] : $room['adults'];
			$children  = isset( $presets['children'] ) ? $presets['children'] : $room['children'];
			$guestName = isset( $presets['guest_name'] ) ? $presets['guest_name'] : '';

			$reservedServices = array();

			if ( ! empty( $presets['services'] ) ) {
				$allowedServices = get_post_meta( $room['room_type_id'], 'mphb_services', true );

				if ( is_array( $allowedServices ) ) {
					$allowedServices = array_map( 'mphb_posint', $allowedServices );
				} else {
					$allowedServices = array();
				}

				foreach ( $presets['services'] as $serviceId => $serviceInfo ) {
					if ( ! in_array( $serviceId, $allowedServices ) ) {
						continue;
					}

					$reservedServices[] = ReservedService::create(
						array(
							'id'       => $serviceId,
							'adults'   => $serviceInfo['adults'],
							'quantity' => $serviceInfo['quantity'],
						)
					);
				}
			}

			$reservedRoom = new ReservedRoom(
				array(
					'room_id'           => $room['room_id'],
					'rate_id'           => $room['rate_id'],
					'adults'            => $adults,
					'children'          => $children,
					'reserved_services' => $reservedServices,
					'guest_name'        => $guestName,
				)
			);

			$reservedRooms[] = $reservedRoom;
		}

		// Create booking
		MPHB()->reservationRequest()->setupParameter( 'pricing_strategy', 'base-price' );

		$booking = new Booking(
			array(
				'check_in_date'  => $checkInDate,
				'check_out_date' => $checkOutDate,
				'reserved_rooms' => $reservedRooms,
			)
		);

		MPHB()->reservationRequest()->resetDefaults( array( 'pricing_strategy' ) );

		return $booking;
	}
}
