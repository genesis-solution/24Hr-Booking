<?php

namespace MPHB\Upgrades;

use \MPHB\UsersAndRoles\Customers;
use \MPHB\UsersAndRoles\Customer;

class BackgroundBookingUpgrader_4_2_0 extends \MPHB\BackgroundPausableProcess {

	const BATCH_SIZE = 100;

	/**
	 *
	 * @var string
	 */
	protected $action = '4_2_0';

	protected function task( $bookingId ) {
		$booking = MPHB()->getBookingRepository()->findById( $bookingId );

		if ( null == $booking ) {
			return false;
		}

		$bookingCustomer = $booking->getCustomer();

		$customerEmail = $bookingCustomer->getEmail();

		if ( empty( $customerEmail ) ) {
			return false;
		}

		$customerId = $bookingCustomer->getCustomerId();

		if ( ! $customerId ) {

			$customerId = MPHB()->customers()->findBy( 'email', $customerEmail, false );

			if ( ! $customerId ) {
				$customer = MPHB()->customers()->convertFromEntity( $bookingCustomer );

				$bookingDateTime = $booking->getDateTime();

				$customer->setDateCreated( \MPHB\Utils\DateUtils::formatDateTimeDB( $bookingDateTime ) );

				$customerId = MPHB()->customers()->create( $customer );

				if ( is_wp_error( $customerId ) ) {
					$customerId = 0;
				}

				Customers::updateBookingsByCustomer( $customerEmail );
			}

			update_post_meta( $bookingId, 'mphb_customer_id', $customerId );
		}

		return false;
	}

	protected function get_batch() {
		$batch = parent::get_batch();

		if ( ! empty( $batch ) && property_exists( $batch, 'data' ) && ! empty( $batch->data ) ) {
			// Fill bookings meta
			update_postmeta_cache( $batch->data );
		}

		return $batch;
	}

}
