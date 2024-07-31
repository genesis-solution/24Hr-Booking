<?php

namespace MPHB\UserActions;

class BookingCancellationAction {

	const QUERY_ACTION                     = 'cancel_booking';
	const STATUS_CANCELLED                 = 'cancelled';
	const STATUS_INVALID_REQUEST           = 'invalid-request';
	const STATUS_ALREADY_CANCELLED         = 'already-cancelled';
	const STATUS_CANCELLATION_NOT_POSSIBLE = 'cancellation-not-possible';

	/**
	 * @since 3.9.9
	 */
	const STATUS_CANCELLATION_POSSIBLE = 'cancellation-possible';
	const STEP_USER_CONFIRM            = 'cancellation-step-user-confirm';
	const STEP_CONFIRMED_BY_USER       = 'cancellation-step-confirmed-by-user';

	/**
	 *
	 * @var \MPHB\Entities\Booking
	 */
	private $booking;

	public function __construct() {

		if ( MPHB()->settings()->main()->canUserCancelBooking() &&
			isset( $_GET['mphb_action'] ) &&
			$_GET['mphb_action'] == self::QUERY_ACTION &&
			( ! isset( $_GET['mphb_step_cancellation'] ) || $_GET['mphb_step_cancellation'] != self::STEP_USER_CONFIRM ) ) {
			add_action( 'init', array( $this, 'checkCancellation' ), 15 );
		}
	}

	/**
	 *
	 * @since 3.9.9
	 *
	 * @return string
	 */
	public function getStatus() {
		if ( ! $this->parseRequest() ) {
			return self::STATUS_INVALID_REQUEST;
		}

		if ( $this->booking->getStatus() === \MPHB\PostTypes\BookingCPT\Statuses::STATUS_CANCELLED ) {
			return self::STATUS_ALREADY_CANCELLED;
		}

		if ( ! mphb_is_locking_booking( $this->booking ) ||
			new \DateTime() > $this->booking->getCheckInDate()
		) {
			return self::STATUS_CANCELLATION_NOT_POSSIBLE;
		}

		return self::STATUS_CANCELLATION_POSSIBLE;
	}

	/**
	 *
	 * @param string $status
	 *
	 * @since 3.9.9
	 */
	public function redirectIfCanNotBeCancelled( $status ) {
		if ( in_array( $status, array( self::STATUS_INVALID_REQUEST, self::STATUS_ALREADY_CANCELLED, self::STATUS_CANCELLATION_NOT_POSSIBLE ) ) ) {
			$pageId = $this->bookingConfirmCancellationPageExists();
			if ( $pageId ) {
				$this->redirectToConfirmCancellationPage( $pageId, $status );
			} else {
				$this->redirectWithStatus( $status );
			}
		}
	}

	/**
	 *
	 * @since 3.9.9
	 */
	public function cancellBooking() {
		$this->booking->setStatus( \MPHB\PostTypes\BookingCPT\Statuses::STATUS_CANCELLED );
		$isSaved = MPHB()->getBookingRepository()->save( $this->booking );

		if ( ! $isSaved ) {
			$this->redirectIfCanNotBeCancelled( self::STATUS_CANCELLATION_NOT_POSSIBLE );
		}

		do_action( 'mphb_customer_cancelled_booking', $this->booking );
		$this->redirectWithStatus( self::STATUS_CANCELLED );
	}

	/**
	 *
	 * @since 3.9.9
	 */
	public function bookingConfirmCancellationPageExists() {
		return MPHB()->settings()->pages()->getBookingConfirmCancellationPage();
	}

	public function checkCancellation() {
		$status = $this->getStatus();

		$this->redirectIfCanNotBeCancelled( $status );

		if ( isset( $_GET['mphb_action'] ) && $_GET['mphb_action'] == self::QUERY_ACTION ) {

			$pageId = $this->bookingConfirmCancellationPageExists();

			if ( $pageId && ! isset( $_GET['mphb_step_cancellation'] ) ) { // If Cancellation page exists
				$this->redirectToConfirmCancellationPage( $pageId ); // Redirect to Cancellation page
			}

			$this->cancellBooking();
		}
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
			'booking_id',
			'booking_key',
			'mphb_action',
			'token',
		);

		if ( isset( $_GET['mphb_step_cancellation'] ) ) {
			$allowedArgs[] = 'mphb_step_cancellation';
		}

		if ( ! MPHB()->userActions()->getActionLinkHelper()->isValidToken( $allowedArgs ) ) {
			return false;
		}

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
		return isset( $_GET['booking_id'] ) &&
			isset( $_GET['booking_key'] ) &&
			isset( $_GET['token'] );
	}

	private function redirectWithStatus( $status, $pageUrl = '' ) {
		$pageUrl = MPHB()->settings()->pages()->getUserCancelRedirectPageUrl();

		$redirectUrl = add_query_arg( 'mphb_cancellation_status', $status, $pageUrl ? $pageUrl : '' );

		wp_redirect( $redirectUrl );
		exit;
	}

	protected function redirectToConfirmCancellationPage( $pageId, $status = '' ) {
		$pageUrl = get_permalink( $pageId );

		if ( ! $pageUrl ) {
			return;
		}

		$params                           = $_GET;
		$params['mphb_step_cancellation'] = self::STEP_USER_CONFIRM;

		if ( ! empty( $status ) ) {
			$params['mphb_cancellation_status'] = esc_attr( $status );
		}

		$redirectUrl = add_query_arg( $params, $pageUrl );

		wp_redirect( $redirectUrl );
		exit;
	}

	/**
	 *
	 * @param \MPHB\Entities\Booking $booking
	 * @return string
	 */
	public function generateLink( \MPHB\Entities\Booking $booking, $step = '' ) {

		$args = array(
			'booking_id'  => $booking->getId(),
			'booking_key' => $booking->getKey(),
			'mphb_action' => self::QUERY_ACTION,
		);

		if ( ! empty( $step ) ) {
			$args['mphb_step_cancellation'] = esc_attr( $step );
		}

		return MPHB()->userActions()->getActionLinkHelper()->generateLink( $args );
	}
}
