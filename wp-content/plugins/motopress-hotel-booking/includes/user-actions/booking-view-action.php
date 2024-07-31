<?php

namespace MPHB\UserActions;

/**
 * @since 3.7.0
 */
class BookingViewAction {

	/**
	 * @param array                  $args
	 * @param \MPHB\Entities\Booking $args['booking']
	 * @param \MPHB\Entities\Payment $args['payment']
	 * @return string|false
	 */
	public function generateLink( $args = array() ) {

		$url = MPHB()->settings()->pages()->getBookingConfirmedPageUrl();

		if ( ! $url ) {
			return false;
		}

		if ( isset( $args['payment'] ) ) {
			$url = add_query_arg(
				array(
					'payment_id'  => $args['payment']->getId(),
					'payment_key' => $args['payment']->getKey(),
				),
				$url
			);
		} elseif ( isset( $args['booking'] ) ) {
			$url = add_query_arg(
				array(
					'booking_id'  => $args['booking']->getId(),
					'booking_key' => $args['booking']->getKey(),
				),
				$url
			);
		}

		return $url;
	}
}
