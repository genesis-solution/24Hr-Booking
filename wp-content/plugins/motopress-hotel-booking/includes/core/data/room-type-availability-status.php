<?php

namespace MPHB\Core;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Later this class will be enum
 */
class RoomTypeAvailabilityStatus {

	const ROOM_TYPE_AVAILABILITY_STATUS_AVAILABLE           = 'available';
	const ROOM_TYPE_AVAILABILITY_STATUS_NOT_AVAILABLE       = 'not-available';
	const ROOM_TYPE_AVAILABILITY_STATUS_BOOKED              = 'booked';
	const ROOM_TYPE_AVAILABILITY_STATUS_PAST                = 'past';
	const ROOM_TYPE_AVAILABILITY_STATUS_EARLIER_MIN_ADVANCE = 'earlier-min-advance';
	const ROOM_TYPE_AVAILABILITY_STATUS_LATER_MAX_ADVANCE   = 'later-max-advance';

	private function __construct() {}
}
