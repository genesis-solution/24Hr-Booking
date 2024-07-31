<?php

namespace MPHBW;

/**
 * @since 1.0.5
 */
class BookingUtils
{
    /**
     * @param \MPHB\Entities\Booking $booking
     * @return bool
     *
     * @since 1.0.5
     *
     * @todo Remove "exclude_booking" parameter (deprecated since Hotel Booking 3.8).
     */
    public static function canRebook($booking)
    {
        // Get the list of rooms we need to rebook
        $rooms = array(); // [Room type ID => Room IDs]

        foreach ($booking->getReservedRooms() as $reservedRoom) {
            $roomTypeId = $reservedRoom->getRoomTypeId();

            if (!isset($rooms[$roomTypeId])) {
                $rooms[$roomTypeId] = array();
            }

            $rooms[$roomTypeId][] = $reservedRoom->getRoomId();
        }

        // Check availability
        foreach ($rooms as $roomTypeId => $roomIds) {
            $lockedRooms = MPHB()->getRoomPersistence()->searchRooms(array(
                'availability'     => 'locked',
                'from_date'        => $booking->getCheckInDate(),
                'to_date'          => $booking->getCheckOutDate(),
                'room_type_id'     => $roomTypeId,
                'exclude_bookings' => $booking->getId(),
                // Deprecated since Hotel Booking 3.8
                'exclude_booking'  => $booking->getId()
            ));

            $unavailableRooms = array_intersect($lockedRooms, $roomIds);

            if (count($unavailableRooms) > 0) {
                return false;
            }
        }

        return true;
    }
}
