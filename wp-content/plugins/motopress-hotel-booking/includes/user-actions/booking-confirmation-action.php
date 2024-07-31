<?php

namespace MPHB\UserActions;

class BookingConfirmationAction {

	const QUERY_ACTION                     = 'confirm_email';
	const STATUS_CONFIRMED                 = 'confirmed';
	const STATUS_EXPIRED                   = 'expired';
	const STATUS_INVALID_REQUEST           = 'invalid-request';
	const STATUS_ALREADY_CONFIRMED         = 'already-confirmed';
	const STATUS_CONFIRMATION_NOT_POSSIBLE = 'confirmation-not-possible';

	/**
	 *
	 * @var \MPHB\Entities\Booking
	 */
	private $booking;

	/**
	 *
	 * @var int
	 */
	private $ttl = 0;

	public function __construct() {

		if ( isset( $_GET['mphb_action'] ) && $_GET['mphb_action'] === self::QUERY_ACTION ) {
			add_action( 'init', array( $this, 'checkConfirmation' ), 15 );
		}
	}

	public function checkConfirmation() {

		if ( ! $this->parseRequest() ) {
			$this->redirectWithStatus( self::STATUS_INVALID_REQUEST );
		}

		if ( ! $this->isActualTTL() ) {
			$this->redirectWithStatus( self::STATUS_EXPIRED );
		}

		if ( $this->booking->getStatus() === \MPHB\PostTypes\BookingCPT\Statuses::STATUS_CONFIRMED ) {
			$this->redirectWithStatus( self::STATUS_ALREADY_CONFIRMED );
		}

		if ( $this->booking->getStatus() !== \MPHB\PostTypes\BookingCPT\Statuses::STATUS_PENDING_USER ) {
			$this->redirectWithStatus( self::STATUS_CONFIRMATION_NOT_POSSIBLE );
		}

		$this->booking->setStatus( \MPHB\PostTypes\BookingCPT\Statuses::STATUS_CONFIRMED );

		$isSaved = MPHB()->getBookingRepository()->save( $this->booking );

		if ( ! $isSaved ) {
			$this->redirectWithStatus( self::STATUS_CONFIRMATION_NOT_POSSIBLE );
		}

		do_action( 'mphb_customer_confirmed_booking', $this->booking );
		$this->redirectWithStatus( self::STATUS_CONFIRMED );
	}

	/**
	 *
	 * @return bool
	 */
	private function parseRequest() {

		if ( ! $this->issetRequiredParameters() ) {
			return false;
		}

		$allowedArgs = array(
			'email',
			'booking_id',
			'booking_key',
			'ttl',
			'mphb_action',
			'token',
		);

		if ( ! MPHB()->userActions()->getActionLinkHelper()->isValidToken( $allowedArgs ) ) {
			return false;
		}

		$this->ttl = isset( $_GET['ttl'] ) ? absint( $_GET['ttl'] ) : 0;

		$bookingId = isset( $_GET['booking_id'] ) ? absint( $_GET['booking_id'] ) : 0;

		if ( get_post_type( $bookingId ) !== MPHB()->postTypes()->booking()->getPostType() ) {
			return false;
		}

		$booking = MPHB()->getBookingRepository()->findById( $bookingId );

		if ( ! $booking ) {
			return false;
		}

		$bookingKey = isset( $_GET['booking_key'] ) ? sanitize_text_field( wp_unslash( $_GET['booking_key'] ) ) : '';

		if ( $booking->getKey() !== $bookingKey ) {
			return false;
		}

		$this->booking = $booking;

		return true;
	}

	/**
	 *
	 * @return bool
	 */
	private function issetRequiredParameters() {
		return isset( $_GET['ttl'] ) &&
			isset( $_GET['booking_id'] ) &&
			isset( $_GET['booking_key'] ) &&
			isset( $_GET['email'] ) &&
			isset( $_GET['token'] );
	}

	private function redirectWithStatus( $status ) {
		$confirmedPageUrl = MPHB()->settings()->pages()->getBookingConfirmedPageUrl();

		if ( $confirmedPageUrl ) {
			$redirectUrl = add_query_arg( 'mphb_confirmation_status', $status, $confirmedPageUrl );

			// Pass booking ID and key to Booking Confirmed Page to show booking
			// details there
			if ( isset( $_GET['booking_id'], $_GET['booking_key'] ) ) {

				$redirectUrl = add_query_arg(
					array(
						'booking_id'  => absint( $_GET['booking_id'] ),
						'booking_key' => sanitize_text_field( wp_unslash( $_GET['booking_key'] ) ),
					),
					$redirectUrl
				);
			}
		} else {
			$redirectUrl = home_url();
		}

		wp_redirect( $redirectUrl );
		exit;
	}

	/**
	 *
	 * @param \MPHB\Entities\Booking $booking
	 * @return string
	 */
	public function generateLink( \MPHB\Entities\Booking $booking ) {

		$args = array(
			'ttl'         => $booking->retrieveExpiration( 'user' ),
			'booking_id'  => $booking->getId(),
			'booking_key' => $booking->getKey(),
			'email'       => $booking->getCustomer()->getEmail(),
			'mphb_action' => self::QUERY_ACTION,
		);

		return MPHB()->userActions()->getActionLinkHelper()->generateLink( $args );
	}

	/**
	 *
	 * @param int $ttl
	 * @return bool
	 */
	private function isActualTTL() {
		return $this->ttl > time();
	}

}
