<?php

namespace MPHB\Core;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


class RoomAvailabilityHelper {

	private function __construct() {}

	public static function getActiveRoomsCountForRoomType( int $roomTypeOriginalId ) {

		$roomsAtts = array(
			'post_status'  => 'publish',
		);

		if ( 0 < $roomTypeOriginalId ) {
			$roomsAtts['room_type_id'] = $roomTypeOriginalId;
		}

		return MPHB()->getRoomPersistence()->getCount( $roomsAtts );
	}

	public static function getAvailableRoomsCountForRoomType( int $roomTypeOriginalId, \DateTime $date, bool $isIgnoreBookingRules ) {

		$availableRoomsCount = MPHB()->getCoreAPI()->getActiveRoomsCountForRoomType( $roomTypeOriginalId );

		if ( 0 >= $availableRoomsCount ) { // for optimization of calculation
			return $availableRoomsCount;
		}

		$formattedDate = $date->format( 'Y-m-d' );

		$bookedDays = MPHB()->getCoreAPI()->getBookedDaysForRoomType( $roomTypeOriginalId );

		if ( ! empty( $bookedDays['booked'][ $formattedDate ] ) ) {
			$availableRoomsCount = $availableRoomsCount - $bookedDays['booked'][ $formattedDate ];
		}

		if ( 0 >= $availableRoomsCount ) { // for optimization of calculation
			return $availableRoomsCount;
		}

		if ( ! $isIgnoreBookingRules ) {

			$blokedRoomsCount = MPHB()->getCoreAPI()->getBlockedRoomsCountsForRoomType( $roomTypeOriginalId );

			if ( ! empty( $blokedRoomsCount[ $formattedDate ] ) ) {
				$availableRoomsCount = $availableRoomsCount - $blokedRoomsCount[ $formattedDate ];
			}
		}

		return $availableRoomsCount;
	}

	/**
	 * @return string status
	 */
	public static function getRoomTypeAvailabilityStatus( int $roomTypeOriginalId, \DateTime $date, bool $isIgnoreBookingRules ) {

		if ( $date < ( new \DateTime() )->setTime( 0, 0, 0 ) ) {
			return RoomTypeAvailabilityStatus::ROOM_TYPE_AVAILABILITY_STATUS_PAST;
		}

		if ( MPHB()->getCoreAPI()->isBookedDate( $roomTypeOriginalId, $date ) ) {
			return RoomTypeAvailabilityStatus::ROOM_TYPE_AVAILABILITY_STATUS_BOOKED;
		}

		if (  MPHB()->getCoreAPI()->isCheckInEarlierThanMinAdvanceDate( $roomTypeOriginalId, $date, $isIgnoreBookingRules )	) {
			return RoomTypeAvailabilityStatus::ROOM_TYPE_AVAILABILITY_STATUS_EARLIER_MIN_ADVANCE;
		}

		if ( MPHB()->getCoreAPI()->isCheckInLaterThanMaxAdvanceDate( $roomTypeOriginalId, $date, $isIgnoreBookingRules ) ) {
			return RoomTypeAvailabilityStatus::ROOM_TYPE_AVAILABILITY_STATUS_LATER_MAX_ADVANCE;
		}

		if ( 0 < $roomTypeOriginalId ) {

			$datesRates = MPHB()->getCoreAPI()->getDatesRatesForRoomType( $roomTypeOriginalId );

			if ( ! in_array( $date->format( 'Y-m-d' ), $datesRates ) ) {
				return RoomTypeAvailabilityStatus::ROOM_TYPE_AVAILABILITY_STATUS_NOT_AVAILABLE;
			}

			if ( 0 >= static::getAvailableRoomsCountForRoomType( $roomTypeOriginalId, $date, $isIgnoreBookingRules ) ) {
				return RoomTypeAvailabilityStatus::ROOM_TYPE_AVAILABILITY_STATUS_NOT_AVAILABLE;
			}
		} else {

			$allRoomTypeIds = MPHB()->getCoreAPI()->getAllRoomTypeOriginalIds();

			$formattedDateYmd = $date->format( 'Y-m-d' );

			foreach ( $allRoomTypeIds as $roomTypeId ) {

				$datesRates = MPHB()->getCoreAPI()->getDatesRatesForRoomType( $roomTypeId );

				if ( in_array( $formattedDateYmd, $datesRates ) &&
					0 < static::getAvailableRoomsCountForRoomType( $roomTypeId, $date, $isIgnoreBookingRules )
				) {
					// at least one room type has available room
					return RoomTypeAvailabilityStatus::ROOM_TYPE_AVAILABILITY_STATUS_AVAILABLE;
				}
			}

			return RoomTypeAvailabilityStatus::ROOM_TYPE_AVAILABILITY_STATUS_NOT_AVAILABLE;
		}

		return RoomTypeAvailabilityStatus::ROOM_TYPE_AVAILABILITY_STATUS_AVAILABLE;
	}


	/**
	 * @param $considerCheckIn - if true then check-in date considered as booked if there is no any available room
	 * @param $considerCheckOut - if true then check-out date considered as booked if there is no any available room
	 * @return true if given date is booked (there is no any available room)
	 */
	public static function isBookedDate( int $roomTypeOriginalId, \DateTime $date, $considerCheckIn = true, $considerCheckOut = false ) {

		$bookedDays       = MPHB()->getCoreAPI()->getBookedDaysForRoomType( $roomTypeOriginalId );
		$activeRoomsCount = MPHB()->getCoreAPI()->getActiveRoomsCountForRoomType( $roomTypeOriginalId );

		$formattedDate = $date->format( 'Y-m-d' );

		$isBookedDate = ( ! empty( $bookedDays['booked'][ $formattedDate ] ) &&
			$bookedDays['booked'][ $formattedDate ] >= $activeRoomsCount );

		if ( ! $considerCheckIn && ! empty( $bookedDays['check-ins'][ $formattedDate ] ) ) {
			$isBookedDate = false;
		}

		if ( $considerCheckOut && ! $isBookedDate ) {

			$dateBefore = clone $date;
			$dateBefore->modify( '-1 day' );
			$formattedDateBefore = $dateBefore->format( 'Y-m-d' );

			$isBookedDate = ( ! empty( $bookedDays['booked'][ $formattedDateBefore ] ) &&
				$bookedDays['booked'][ $formattedDateBefore ] >= $activeRoomsCount ) &&
				! empty( $bookedDays['check-outs'][ $formattedDate ] );
		}

		return $isBookedDate;
	}


	/**
	 * @return bool - true if check-in is not allowed in the given date
	 */
	public static function isCheckInNotAllowed( int $roomTypeOriginalId, \DateTime $date, bool $isIgnoreBookingRules ) {

		$availabilityStatus = MPHB()->getCoreAPI()->getRoomTypeAvailabilityStatus( $roomTypeOriginalId, $date, $isIgnoreBookingRules );

		if ( RoomTypeAvailabilityStatus::ROOM_TYPE_AVAILABILITY_STATUS_EARLIER_MIN_ADVANCE === $availabilityStatus ||
			RoomTypeAvailabilityStatus::ROOM_TYPE_AVAILABILITY_STATUS_LATER_MAX_ADVANCE === $availabilityStatus ||
			RoomTypeAvailabilityStatus::ROOM_TYPE_AVAILABILITY_STATUS_PAST === $availabilityStatus ||
			RoomTypeAvailabilityStatus::ROOM_TYPE_AVAILABILITY_STATUS_BOOKED === $availabilityStatus
		) {

			return false;

		} elseif ( RoomTypeAvailabilityStatus::ROOM_TYPE_AVAILABILITY_STATUS_NOT_AVAILABLE === $availabilityStatus ) {

			// check if this is the case when date is blocked by Not Stay In Not Check In and Not Check Out rule
			$isCheckInNotAllowed = ! MPHB()->getRulesChecker()->customRules()->verifyNotCheckInRestriction( $date, $date, $roomTypeOriginalId ) ||
				! MPHB()->getRulesChecker()->reservationRules()->verifyCheckInDaysReservationRule( $date, $date, $roomTypeOriginalId );

			return $isCheckInNotAllowed;
		}

		$isCheckInNotAllowed = ! MPHB()->getRulesChecker()->customRules()->verifyNotCheckInRestriction( $date, $date, $roomTypeOriginalId ) ||
			! MPHB()->getRulesChecker()->reservationRules()->verifyCheckInDaysReservationRule( $date, $date, $roomTypeOriginalId );

		// check Not CheckIn before Not Stay In or Booked days
		if ( ! $isCheckInNotAllowed ) {

			$minStayNights = MPHB()->getCoreAPI()->getMinStayLengthReservationDaysCount( $roomTypeOriginalId, $date, $isIgnoreBookingRules );

			$checkingDate    = clone $date;
			$nightsAfterDate = 0;

			do {

				$checkingDate->modify( '+1 day' );
				$nightsAfterDate++;

				$checkingDateStatus = MPHB()->getCoreAPI()->getRoomTypeAvailabilityStatus( $roomTypeOriginalId, $checkingDate, $isIgnoreBookingRules );

				$isCheckinDateNotAvailable = RoomTypeAvailabilityStatus::ROOM_TYPE_AVAILABILITY_STATUS_NOT_AVAILABLE === $checkingDateStatus;
				$isCheckingDateBooked      = RoomTypeAvailabilityStatus::ROOM_TYPE_AVAILABILITY_STATUS_BOOKED === $checkingDateStatus;

				$isCheckinDateNotForStayIn = MPHB()->getCoreAPI()->isStayInNotAllowed( $roomTypeOriginalId, $checkingDate, $checkingDate, $isIgnoreBookingRules );


				$isBookingNotAllowedInMinStayPeriod = $nightsAfterDate < $minStayNights &&
					( $isCheckinDateNotAvailable || $isCheckinDateNotForStayIn || $isCheckingDateBooked );

				$isCheckOutNotAllowedOnLastDayOfMinStayPeriod = $nightsAfterDate === $minStayNights &&
					MPHB()->getCoreAPI()->isCheckOutNotAllowed( $roomTypeOriginalId, $checkingDate, $isIgnoreBookingRules ) &&
					( $isCheckinDateNotAvailable || $isCheckinDateNotForStayIn || $isCheckingDateBooked );

				if ( $isBookingNotAllowedInMinStayPeriod || $isCheckOutNotAllowedOnLastDayOfMinStayPeriod ) {

					$isCheckInNotAllowed = true;
					break;
				}
			} while ( $nightsAfterDate < $minStayNights );
		}

		return $isCheckInNotAllowed;
	}


	/**
	 * @return bool - true if check-out is not allowed in the given date
	 */
	public static function isCheckOutNotAllowed( int $roomTypeOriginalId, \DateTime $date, bool $isIgnoreBookingRules ) {

		$availabilityStatus = MPHB()->getCoreAPI()->getRoomTypeAvailabilityStatus( $roomTypeOriginalId, $date, $isIgnoreBookingRules );

		if ( RoomTypeAvailabilityStatus::ROOM_TYPE_AVAILABILITY_STATUS_PAST === $availabilityStatus ||
			MPHB()->getCoreAPI()->isBookedDate( $roomTypeOriginalId, $date, false, true )
		) {
			return false;
		}

		$isCheckOutNotAllowed = MPHB()->getCoreAPI()->isCheckOutNotAllowed( $roomTypeOriginalId, $date, $isIgnoreBookingRules);

		// check Not Check-out after Not Stay-in, Booked or Not Available days
		if ( ! $isCheckOutNotAllowed ) {

			$checkingDate     = clone $date;
			$nightsBeforeDate = 0;

			do {

				$checkingDate->modify( '-1 day' );
				$nightsBeforeDate++;

				$checkingDateStatus = MPHB()->getCoreAPI()->getRoomTypeAvailabilityStatus( $roomTypeOriginalId, $checkingDate, $isIgnoreBookingRules );

				if ( MPHB()->getCoreAPI()->isStayInNotAllowed( $roomTypeOriginalId, $checkingDate, $checkingDate, $isIgnoreBookingRules ) ||
					RoomTypeAvailabilityStatus::ROOM_TYPE_AVAILABILITY_STATUS_BOOKED === $checkingDateStatus ||
					RoomTypeAvailabilityStatus::ROOM_TYPE_AVAILABILITY_STATUS_NOT_AVAILABLE === $checkingDateStatus ||
					RoomTypeAvailabilityStatus::ROOM_TYPE_AVAILABILITY_STATUS_PAST === $checkingDateStatus ) {

					$isCheckOutNotAllowed = true;
					break;
				}

				$minStayNights = MPHB()->getCoreAPI()->getMinStayLengthReservationDaysCount( $roomTypeOriginalId, $checkingDate, $isIgnoreBookingRules );

			} while ( $nightsBeforeDate < $minStayNights );
		}

		return $isCheckOutNotAllowed;
	}


	public static function getRoomTypeAvailabilityData( int $roomTypeOriginalId, \DateTime $date, bool $isIgnoreBookingRules ) {

		$availabilityStatus = MPHB()->getCoreAPI()->getRoomTypeAvailabilityStatus( $roomTypeOriginalId, $date, $isIgnoreBookingRules );

		$result = null;

		if ( RoomTypeAvailabilityStatus::ROOM_TYPE_AVAILABILITY_STATUS_PAST == $availabilityStatus ) {

			$result = new RoomTypeAvailabilityData( $availabilityStatus );

		} else {

			$availableRoomsCount = self::getAvailableRoomsCountForRoomType( $roomTypeOriginalId, $date, $isIgnoreBookingRules );

			$bookedDays     = MPHB()->getCoreAPI()->getBookedDaysForRoomType( $roomTypeOriginalId );
			$formattedDate  = $date->format( 'Y-m-d' );
			$isCheckInDate  = ! empty( $bookedDays['check-ins'][ $formattedDate ] );
			$isСheckOutDate = ! empty( $bookedDays['check-outs'][ $formattedDate ] );

			$isStayInNotAllowed = MPHB()->getCoreAPI()->isStayInNotAllowed( $roomTypeOriginalId, $date, $date, $isIgnoreBookingRules );

			$isEarlierThanMinAdvanceDate = MPHB()->getCoreAPI()->isCheckInEarlierThanMinAdvanceDate( $roomTypeOriginalId, $date, $isIgnoreBookingRules );

			$isLaterThanMaxAdvanceDate = MPHB()->getCoreAPI()->isCheckInLaterThanMaxAdvanceDate( $roomTypeOriginalId, $date, $isIgnoreBookingRules );

			$minStayNights = MPHB()->getCoreAPI()->getMinStayLengthReservationDaysCount( $roomTypeOriginalId, $date, $isIgnoreBookingRules );

			$maxStayNights = MPHB()->getCoreAPI()->getMaxStayLengthReservationDaysCount( $roomTypeOriginalId, $date, $isIgnoreBookingRules );

			$result = new RoomTypeAvailabilityData(
				$availabilityStatus,
				$availableRoomsCount,
				$isCheckInDate,
				$isСheckOutDate,
				$isStayInNotAllowed,
				static::isCheckInNotAllowed( $roomTypeOriginalId, $date, $isIgnoreBookingRules ),
				static::isCheckOutNotAllowed( $roomTypeOriginalId, $date, $isIgnoreBookingRules ),
				$isEarlierThanMinAdvanceDate,
				$isLaterThanMaxAdvanceDate,
				$minStayNights,
				$maxStayNights
			);
		}

		return $result;
	}

	/**
	 * Returns first available date for check-in for room type or
	 * any of room types if $roomTypeOriginalId = 0
	 * @return \DateTime
	 */
	public static function getFirstAvailableCheckInDate( int $roomTypeOriginalId, bool $isIgnoreBookingRules ) {

		$firstAvailableDate = new \DateTime('yesterday');
		$maxCheckDatesCount = 370;

		do {
			$firstAvailableDate->modify( '+1 day' );
			$maxCheckDatesCount--;

			$availabilityStatus = MPHB()->getCoreAPI()->getRoomTypeAvailabilityStatus(
				$roomTypeOriginalId,
				$firstAvailableDate,
				$isIgnoreBookingRules
			);

		} while (
			RoomTypeAvailabilityStatus::ROOM_TYPE_AVAILABILITY_STATUS_AVAILABLE !== $availabilityStatus &&
			RoomTypeAvailabilityStatus::ROOM_TYPE_AVAILABILITY_STATUS_LATER_MAX_ADVANCE !== $availabilityStatus &&
			0 < $maxCheckDatesCount
		);
		
		return $firstAvailableDate;
	}
}
