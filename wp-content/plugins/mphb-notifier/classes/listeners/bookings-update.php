<?php

namespace MPHB\Notifier\Listeners;

/**
 * @since 1.0
 */
class BookingsUpdate {

	public function __construct() {

		add_action( 'mphb_booking_confirmed', array( $this, 'onConfirmBooking' ), 10, 2 );
	}

	/**
	 * @param \MPHB\Entities\Booking $booking
	 * @param string                 $oldStatus
	 */
	public function onConfirmBooking( $booking, $oldStatus ) {
        
		// Skip posts restored from the trash
		if ( $oldStatus == 'trash' ) {
			return;
		}

		// Skip imported booking
		if ( $booking->isImported() ) {
			return;
		}

		mphb_notifier()->services()->sendNotifications()->triggerNewBooking( $booking );
	}
}
