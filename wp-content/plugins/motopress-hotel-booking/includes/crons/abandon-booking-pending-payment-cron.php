<?php

namespace MPHB\Crons;

use \MPHB\Entities;

class AbandonBookingPendingPaymentCron extends AbstractCron {

	private $retrievePostsLimit = 10;

	public function doCronJob() {

		// get abandon-ready bookings
		$bookingAtts = array(
			'pending_payment_expired' => true,
			'posts_per_page'          => $this->retrievePostsLimit,
			'paged'                   => 1,
		);

		// change booking status to abandoned
		$bookings = MPHB()->getBookingRepository()->findAll( $bookingAtts );

		foreach ( $bookings as $booking ) {
			$booking->setStatus( \MPHB\PostTypes\BookingCPT\Statuses::STATUS_ABANDONED );
			MPHB()->getBookingRepository()->save( $booking );
		}

		// remove cron task if the abandon-ready bookings are finished
		if ( count( $bookings ) < $this->retrievePostsLimit ) {

			$pendingBookingAtts = array(
				'post_status'    => \MPHB\PostTypes\BookingCPT\Statuses::STATUS_PENDING_PAYMENT,
				'posts_per_page' => 1,
			);

			$pendingBookings = MPHB()->getBookingPersistence()->getPosts( $pendingBookingAtts );

			if ( ! count( $pendingBookings ) ) {
				$this->unschedule();
			}
		}
	}

}
