<?php

namespace MPHB\Utils;

use MPHB\Entities\Booking;
use MPHB\Entities\RoomType;
use DateTime;
use RuntimeException as Error;

/**
 * @since 3.7.2
 */
class ParseUtils {

	/**
	 * @param string $rawData Raw check-in date string.
	 * @param array  $args Optional.
	 *     @param bool   $args['allow_past_dates'] Optional. FALSE by default.
	 * @return DateTime
	 *
	 * @throws Error If check-in date is not valid or earlier than today (if past dates not allowed).
	 *
	 * @since 3.8
	 */
	public static function parseCheckInDate( $rawData, $args = array() ) {
		// Init settings
		$allowPastDates = isset( $args['allow_past_dates'] ) ? $args['allow_past_dates'] : false;
		$dateFormat     = MPHB()->settings()->dateTime()->getDateFormat();

		// Parse date
		$checkInString = sanitize_text_field( wp_unslash( $rawData ) );
		$checkInDate   = DateUtils::createCheckInDate( $dateFormat, $checkInString );

		$today = DateTime::createFromFormat( $dateFormat, 'today' );

		if ( ! $checkInDate ) {
			throw new Error( __( 'Check-in date is not valid.', 'motopress-hotel-booking' ) );
		} elseif ( ! $allowPastDates && DateUtils::calcNights( $today, $checkInDate ) < 0 ) {
			throw new Error( __( 'Check-in date cannot be earlier than today.', 'motopress-hotel-booking' ) );
		} else {
			return $checkInDate;
		}
	}

	/**
	 * @param string              $rawData Raw check-out date string.
	 * @param array               $args Optional.
	 *     @param bool                $args['check_booking_rules'] Optional. TRUE by default.
	 *     @param DateTime|null|false $args['check_in_date'] Optional. Check-in
	 *         date to verify the booking rules (only if "check_booking_rules"
	 *         is set). Not set by default (FALSE).
	 * @return DateTime
	 *
	 * @throws Error
	 *
	 * @since 3.8
	 */
	public static function parseCheckOutDate( $rawData, $args = array() ) {
		// Init settings
		$checkBookingRules = isset( $args['check_booking_rules'] ) ? $args['check_booking_rules'] : true;
		$checkInDate       = isset( $args['check_in_date'] ) ? $args['check_in_date'] : false;
		$dateFormat        = MPHB()->settings()->dateTime()->getDateFormat();

		// Parse date
		$checkOutString = sanitize_text_field( wp_unslash( $rawData ) );
		$checkOutDate   = DateUtils::createCheckOutDate( $dateFormat, $checkOutString );

		if ( ! $checkOutDate ) {
			throw new Error( __( 'Check-out date is not valid.', 'motopress-hotel-booking' ) );
		} elseif ( $checkBookingRules && $checkInDate && ! MPHB()->getRulesChecker()->verify( $checkInDate, $checkOutDate ) ) {
			throw new Error( __( 'Nothing found. Please try again with different search parameters.', 'motopress-hotel-booking' ) );
		} elseif ( $checkInDate && DateUtils::calcNights( $checkInDate, $checkOutDate ) < 0 ) {
			throw new Error( __( 'Check-out date cannot be earlier than check-in date.', 'motopress-hotel-booking' ) );
		} else {
			return $checkOutDate;
		}
	}

	/**
	 * @param array $rawData Raw adults string.
	 * @param array $args Optional. No args at the moment.
	 * @return int
	 *
	 * @throws Error
	 *
	 * @since 3.8
	 */
	public static function parseAdults( $rawData, $args = array() ) {
		$minAdults = mphb_get_min_adults();
		$maxAdults = mphb_get_max_adults();

		$adults = ValidateUtils::validateInt( $rawData, -1 );

		if ( $adults === false ) {
			throw new Error( __( 'Adults number is not valid', 'motopress-hotel-booking' ) );
		} elseif ( $adults == -1 || ( $adults >= $minAdults && $adults <= $maxAdults ) ) {
			return $adults;
		} else {
			throw new Error( __( 'Adults number is not valid.', 'motopress-hotel-booking' ) );
		}
	}

	/**
	 * @param array $rawData Raw children string.
	 * @param array $args Optional. No args at the moment.
	 * @return int
	 *
	 * @throws Error
	 *
	 * @since 3.8
	 */
	public static function parseChildren( $rawData, $args = array() ) {
		$minChildren = mphb_get_min_children();
		$maxChildren = mphb_get_max_children();

		$children = ValidateUtils::validateInt( $rawData, -1 );

		if ( $children === false ) {
			throw new Error( __( 'Children number is not valid', 'motopress-hotel-booking' ) );
		} elseif ( $children == -1 || ( $children >= $minChildren && $children <= $maxChildren ) ) {
			return $children;
		} else {
			throw new Error( __( 'Children number is not valid.', 'motopress-hotel-booking' ) );
		}
	}

	/**
	 * @param mixed     $rawData Raw [mphb_room_details => ...] data.
	 * @param array     $args
	 *     @param DateTime  $args['check_in_date'] Required if "edit_booking" is not set.
	 *     @param DateTime  $args['check_out_date'] Required if "edit_booking" is not set.
	 *     @param bool      $args['check_booking_rules'] Optional. TRUE by default. FALSE if
	 *              "edit_booking" is set.
	 *     @param int|int[] $args['exclude_bookings'] Optional.
	 *     @param Booking   $args['edit_booking'] Optional.
	 * @return array Array of [room_id, room_type_id, rate_id, adults, children,
	 *     guest_name, allowed_rates, services], where all IDs and objects -
	 *     original values (not translated).
	 *
	 * @throws Error
	 *
	 * @since 3.8
	 */
	public static function parseRooms( $rawData, $args ) {
		if ( ! is_array( $rawData ) ) {
			throw new Error( __( 'Selected accommodations are not valid.', 'motopress-hotel-booking' ) );
		}

		$defaultArgs = array(
			'check_in_date'       => null,
			'check_out_date'      => null,
			'check_booking_rules' => true,
			'exclude_bookings'    => array(),
		);

		if ( ! empty( $args['edit_booking'] ) ) {
			$editBooking = $args['edit_booking'];

			$defaultArgs['check_in_date']       = $editBooking->getCheckInDate();
			$defaultArgs['check_out_date']      = $editBooking->getCheckOutDate();
			$defaultArgs['check_booking_rules'] = false;
			$defaultArgs['exclude_bookings']    = $editBooking->getId();
		}

		$args = array_merge( $defaultArgs, $args );

		// Check check-in/check-out dates
		if ( ! $args['check_in_date'] ) {
			throw new Error( __( 'Check-in date is not set.', 'motopress-hotel-booking' ) );
		} elseif ( ! $args['check_out_date'] ) {
			throw new Error( __( 'Check-out date is not set.', 'motopress-hotel-booking' ) );
		}

		// Parse rooms
		$rooms       = array();
		$roomsByType = array(); // [Room type ID => [Room IDs]]

		foreach ( $rawData as $roomData ) {
			$room = static::parseRoom( $roomData, $args );

			$rooms[]                                = $room;
			$roomsByType[ $room['room_type_id'] ][] = $room['room_id'];
		}

		if ( empty( $rooms ) ) {
			throw new Error( __( 'There are no accommodations selected for reservation.', 'motopress-hotel-booking' ) );
		}

		// Check available rooms
		foreach ( $roomsByType as $roomTypeId => $roomIds ) {
			if ( ! MPHB()->getRoomPersistence()->isRoomsFree(
				$args['check_in_date'],
				$args['check_out_date'],
				$roomIds,
				array(
					'room_type_id'     => $roomTypeId,
					'exclude_bookings' => $args['exclude_bookings'],
				)
			) ) {
				throw new Error( __( 'Accommodations are not available.', 'motopress-hotel-booking' ) );
			}
		}

		return $rooms;
	}

	/**
	 * @param mixed    $roomData
	 * @param array    $args
	 *     @param DateTime $args['check_in_date']
	 *     @param DateTime $args['check_out_date']
	 *     @param bool     $args['check_booking_rules']
	 * @return array
	 *
	 * @throws Error
	 *
	 * @since 3.8
	 */
	protected static function parseRoom( $roomData, $args ) {
		if ( ! is_array( $roomData ) ) {
			throw new Error( __( 'Selected accommodations are not valid.', 'motopress-hotel-booking' ) );
		}

		$minAdults   = mphb_get_min_adults();
		$minChildren = mphb_get_min_children();

		$roomId     = isset( $roomData['room_id'] ) ? mphb_posint( $roomData['room_id'] ) : 0;
		$roomTypeId = isset( $roomData['room_type_id'] ) ? mphb_posint( $roomData['room_type_id'] ) : 0;
		$adults     = isset( $roomData['adults'] ) ? ValidateUtils::validateInt( $roomData['adults'], $minAdults ) : 0;
		$children   = isset( $roomData['children'] ) ? ValidateUtils::validateInt( $roomData['children'], $minChildren ) : 0;
		$guestName  = isset( $roomData['guest_name'] ) ? sanitize_text_field( $roomData['guest_name'] ) : '';
		$rateId     = isset( $roomData['rate_id'] ) ? mphb_posint( $roomData['rate_id'] ) : 0;

		$roomType = mphb_get_room_type( $roomTypeId );

		if ( is_null( $roomType ) || $roomType->getStatus() != 'publish' ) {
			throw new Error( __( 'Accommodation Type is not valid.', 'motopress-hotel-booking' ) );
		}

		if ( $roomId == 0 ) {
			throw new Error( __( 'Selected accommodations are not valid.', 'motopress-hotel-booking' ) );
		}

		$allowedRates = MPHB()->getRateRepository()->findAllActiveByRoomType(
			$roomTypeId,
			array(
				'check_in_date'  => $args['check_in_date'],
				'check_out_date' => $args['check_out_date'],
				'mphb_language'  => 'original',
			)
		);

		$rateIds = array_map(
			function ( $rate ) {
				return $rate->getId();
			},
			$allowedRates
		);

		if ( $rateId == 0 || ! in_array( $rateId, $rateIds ) ) {
			throw new Error( __( 'Rate is not valid.', 'motopress-hotel-booking' ) );
		}

		if ( $adults === false || $adults > $roomType->getAdultsCapacity() ) {
			throw new Error( __( 'Adults number is not valid.', 'motopress-hotel-booking' ) );
		}

		if ( $children === false || $children > $roomType->getChildrenCapacity() ) {
			throw new Error( __( 'Children number is not valid.', 'motopress-hotel-booking' ) );
		}

		if ( $roomType->hasLimitedTotalCapacity() && $adults + $children > $roomType->getTotalCapacity() ) {
			throw new Error( __( 'The total number of guests is not valid.', 'motopress-hotel-booking' ) );
		}

		if ( $args['check_booking_rules'] && ! MPHB()->getRulesChecker()->verify( $args['check_in_date'], $args['check_out_date'], $roomTypeId ) ) {
			throw new Error( sprintf( __( 'Selected dates do not meet booking rules for type %s', 'motopress-hotel-booking' ), $roomType->getTitle() ) );
		}

		if ( isset( $roomData['services'] ) ) {
			$services = static::parseServices( $roomData['services'], array( 'room_type' => $roomType ) );
		} else {
			$services = array();
		}

		return array(
			'room_id'       => $roomId,
			'room_type_id'  => $roomTypeId,
			'rate_id'       => $rateId,
			'adults'        => $adults,
			'children'      => $children,
			'guest_name'    => $guestName,
			'allowed_rates' => $rateIds,
			'services'      => $services,
		);
	}

	/**
	 * @param mixed    $servicesData
	 * @param array    $args
	 *     @param RoomType $args['room_type']
	 * @return array
	 *
	 * @since 3.8
	 */
	protected static function parseServices( $servicesData, $args ) {
		if ( ! is_array( $servicesData ) ) {
			return array();
		}

		$services = array();

		foreach ( $servicesData as $serviceData ) {
			if ( ! isset( $serviceData['id'], $serviceData['adults'] ) ) {
				continue;
			}

			$serviceId = mphb_posint( $serviceData['id'] );
			$adults    = ValidateUtils::validateInt( $serviceData['adults'], mphb_get_min_adults() );
			$quantity  = isset( $serviceData['quantity'] ) ? ValidateUtils::validateInt( $serviceData['quantity'], 1 ) : 1;

			if ( $serviceId > 0 && $adults !== false && $quantity !== false && in_array( $serviceId, $args['room_type']->getServices() ) ) {
				$services[ $serviceId ] = array(
					// Data enough/valid for ReservedService::create()
					'id'       => $serviceId,
					'adults'   => $adults,
					'quantity' => $quantity,
				);
			}
		}

		return $services;
	}

	/**
	 * @param array $rawData
	 * @param array $errors Optional. An array to add the errors to.
	 * @return array|false Customer data or FALSE.
	 *
	 * @since 3.7.2
	 */
	public static function parseCustomer( $rawData, &$errors = null ) {
		if ( is_null( $errors ) ) {
			$errors = array();
		}

		if ( ! is_admin() ) {
			$customerFields = mphb_get_customer_fields();
		} else {
			$customerFields = mphb_get_admin_checkout_customer_fields();
		}

		// [Field name => '']
		$customerData = array_combine( array_keys( $customerFields ), array_fill( 0, count( $customerFields ), '' ) );

		// Parse inputs
		foreach ( $customerFields as $fieldName => $field ) {
			$fullName = MPHB()->addPrefix( $fieldName, '_' ); // 'mphb_first_name'

			if ( isset( $rawData[ $fullName ] ) ) {
				$value = $rawData[ $fullName ];

				if ( $field['type'] == 'email' ) {
					$value = sanitize_email( $value );
				} elseif ( $field['type'] == 'textarea' ) {
					$value = sanitize_textarea_field( $value );
				} else {
					$validValue = apply_filters( 'mphb_sanitize_customer_field', null, $value, $field['type'], $fieldName );

					if ( is_null( $validValue ) ) {
						$value = sanitize_text_field( $value );
					} else {
						$value = $validValue;
					}
				}

				$customerData[ $fieldName ] = $value;
			}
		}

		/**
		 *
		 * @since 4.3.0 $rawData
		 * @since 4.3.0 $customerFields
		 */
		$customerData = apply_filters( 'mphb_parse_customer_data', $customerData, $rawData, $customerFields );

		/**
		 *
		 * @param array $errors
		 *
		 * @since 4.3.0
		 */
		$errors = apply_filters( 'mphb_parse_customer_errors', $errors );

		// Check for errors
		foreach ( $customerFields as $fieldName => $field ) {

			$value = $customerData[ $fieldName ];

			if ( empty( $value ) && $field['required'] ) {
				$errors[] = $field['labels']['required_error'];
			}
		}

		// Return the results
		if ( empty( $errors ) ) {
			return $customerData;
		} else {
			return false;
		}
	}
}
