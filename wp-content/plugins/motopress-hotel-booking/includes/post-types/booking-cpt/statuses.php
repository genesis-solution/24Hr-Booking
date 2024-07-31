<?php

namespace MPHB\PostTypes\BookingCPT;

use \MPHB\PostTypes\AbstractCPT;

class Statuses extends AbstractCPT\Statuses {

	const STATUS_CONFIRMED       = 'confirmed';
	const STATUS_PENDING         = 'pending';
	const STATUS_PENDING_USER    = 'pending-user';
	const STATUS_PENDING_PAYMENT = 'pending-payment';
	const STATUS_CANCELLED       = 'cancelled';
	const STATUS_ABANDONED       = 'abandoned';
	const STATUS_AUTO_DRAFT      = 'auto-draft';

	public function __construct( $postType ) {

		parent::__construct( $postType );
		add_action( 'transition_post_status', array( $this, 'transitionStatus' ), 10, 3 );
	}

	protected function initStatuses() {

		$this->statuses[ self::STATUS_PENDING_USER ] = array(
			'lock_room' => true,
		);

		$this->statuses[ self::STATUS_PENDING_PAYMENT ] = array(
			'lock_room' => true,
		);

		$this->statuses[ self::STATUS_PENDING ] = array(
			'lock_room' => true,
		);

		$this->statuses[ self::STATUS_ABANDONED ] = array(
			'lock_room' => false,
		);

		$this->statuses[ self::STATUS_CONFIRMED ] = array(
			'lock_room' => true,
		);

		$this->statuses[ self::STATUS_CANCELLED ] = array(
			'lock_room' => false,
		);
	}

	public function getStatusArgs( $statusName ) {

		$args = array();

		switch ( $statusName ) {

			case self::STATUS_PENDING_USER:
				$args = array(
					'label'                     => _x( 'Pending User Confirmation', 'Booking status', 'motopress-hotel-booking' ),
					'public'                    => true,
					'exclude_from_search'       => false,
					'show_in_admin_all_list'    => true,
					'show_in_admin_status_list' => true,
					'label_count'               => _n_noop( 'Pending User Confirmation <span class="count">(%s)</span>', 'Pending User Confirmation <span class="count">(%s)</span>', 'motopress-hotel-booking' ),
				);
				break;

			case self::STATUS_PENDING_PAYMENT:
				$args = array(
					'label'                     => _x( 'Pending Payment', 'Booking status', 'motopress-hotel-booking' ),
					'public'                    => true,
					'exclude_from_search'       => false,
					'show_in_admin_all_list'    => true,
					'show_in_admin_status_list' => true,
					'label_count'               => _n_noop( 'Pending Payment <span class="count">(%s)</span>', 'Pending Payment <span class="count">(%s)</span>', 'motopress-hotel-booking' ),
				);
				break;

			case self::STATUS_PENDING:
				$args = array(
					'label'                     => _x( 'Pending Admin', 'Booking status', 'motopress-hotel-booking' ),
					'public'                    => true,
					'exclude_from_search'       => false,
					'show_in_admin_all_list'    => true,
					'show_in_admin_status_list' => true,
					'label_count'               => _n_noop( 'Pending Admin <span class="count">(%s)</span>', 'Pending Admin <span class="count">(%s)</span>', 'motopress-hotel-booking' ),
				);
				break;

			case self::STATUS_ABANDONED:
				$args = array(
					'label'                     => _x( 'Abandoned', 'Booking status', 'motopress-hotel-booking' ),
					'public'                    => true,
					'exclude_from_search'       => false,
					'show_in_admin_all_list'    => true,
					'show_in_admin_status_list' => true,
					'label_count'               => _n_noop( 'Abandoned <span class="count">(%s)</span>', 'Abandoned <span class="count">(%s)</span>', 'motopress-hotel-booking' ),
				);
				break;

			case self::STATUS_CONFIRMED:
				$args = array(
					'label'                     => _x( 'Confirmed', 'Booking status', 'motopress-hotel-booking' ),
					'public'                    => true,
					'exclude_from_search'       => false,
					'show_in_admin_all_list'    => true,
					'show_in_admin_status_list' => true,
					'label_count'               => _n_noop( 'Confirmed <span class="count">(%s)</span>', 'Confirmed <span class="count">(%s)</span>', 'motopress-hotel-booking' ),
				);
				break;

			case self::STATUS_CANCELLED:
				$args = array(
					'label'                     => _x( 'Cancelled', 'Booking status', 'motopress-hotel-booking' ),
					'public'                    => true,
					'exclude_from_search'       => false,
					'show_in_admin_all_list'    => true,
					'show_in_admin_status_list' => true,
					'label_count'               => _n_noop( 'Cancelled <span class="count">(%s)</span>', 'Cancelled <span class="count">(%s)</span>', 'motopress-hotel-booking' ),
				);
				break;
		}
		return $args;
	}

	/**
	 * TODO move expiration functionality to action handler
	 *
	 * @param string   $newStatus
	 * @param string   $oldStatus
	 * @param \WP_Post $post
	 */
	public function transitionStatus( $newStatus, $oldStatus, $post ) {

		if ( $post->post_type !== $this->postType ) {
			return;
		}

		if ( $newStatus === $oldStatus ) {
			return;
		}

		// Prevent logging status change while importing
		if ( apply_filters( 'mphb_prevent_handle_booking_status_transition', false ) ) {
			return;
		}

		$booking = MPHB()->getBookingRepository()->findById( $post->ID, true );

		if ( $oldStatus == 'new' ) {
			$booking->generateKey();
		}

		$expirationStatuses = array(
			self::STATUS_PENDING_USER,
			self::STATUS_PENDING_PAYMENT,
		);

		if ( $newStatus === self::STATUS_PENDING_USER ) {

			$booking->updateExpiration( 'user', time() + MPHB()->settings()->main()->getUserApprovalTime() * MINUTE_IN_SECONDS );

			MPHB()->cronManager()->getCron( 'abandon_booking_pending_user' )->schedule();
		}

		if ( $oldStatus === self::STATUS_PENDING_USER ) {
			$booking->deleteExpiration( 'user' );
		}

		if ( $newStatus === self::STATUS_PENDING_PAYMENT ) {

			$booking->updateExpiration( 'payment', time() + MPHB()->settings()->payment()->getPendingTime() * MINUTE_IN_SECONDS );

			MPHB()->cronManager()->getCron( 'abandon_booking_pending_payment' )->schedule();
		}

		if ( $oldStatus === self::STATUS_PENDING_PAYMENT ) {
			$booking->deleteExpiration( 'payment' );
		}

		$booking->addLog( sprintf( __( 'Status changed from %s to %s.', 'motopress-hotel-booking' ), mphb_get_status_label( $oldStatus ), mphb_get_status_label( $newStatus ) ) );

		do_action( 'mphb_booking_status_changed', $booking, $oldStatus );

		$customerId = get_post_meta( $booking->getId(), 'mphb_customer_id', true );

		if ( $customerId ) { // Update bookings count for a customer
			MPHB()->customers()->updateBookings( $customerId );
		}

		if ( $newStatus === self::STATUS_CONFIRMED ) {
			do_action( 'mphb_booking_confirmed', $booking, $oldStatus );
		}

		if ( $newStatus === self::STATUS_CANCELLED ) {
			do_action( 'mphb_booking_cancelled', $booking, $oldStatus );
		}
	}

	/**
	 * @return array
	 */
	public function getLockedRoomStatuses() {

		return array_keys(
			array_filter(
				$this->statuses,
				function( $status ) {
					return isset( $status['lock_room'] ) && $status['lock_room'];
				}
			)
		);
	}

	/**
	 * @return array
	 */
	public function getBookedRoomStatuses() {
		return (array) self::STATUS_CONFIRMED;
	}

	/**
	 * @return array
	 */
	public function getPendingRoomStatuses() {
		return array(
			self::STATUS_PENDING,
			self::STATUS_PENDING_USER,
			self::STATUS_PENDING_PAYMENT,
		);
	}

	/**
	 * @return array
	 * @since 3.7.6
	 */
	public function getFailedStatuses() {

		return array(
			self::STATUS_CANCELLED,
			self::STATUS_ABANDONED,
		);
	}

	/**
	 * @return array
	 */
	public function getAvailableRoomStatuses() {

		return array_merge( 'trash', array_diff( array_keys( $this->statuses ), $this->getLockedRoomStatuses() ) );
	}

	/**
	 * @return string
	 */
	public function getDefaultNewBookingStatus() {

		$confirmationMode = MPHB()->settings()->main()->getConfirmationMode();

		switch ( $confirmationMode ) {
			case 'manual':
				$defaultStatus = self::STATUS_PENDING;
				break;
			case 'payment':
				$defaultStatus = self::STATUS_PENDING_PAYMENT;
				break;
			case 'auto':
			default:
				$defaultStatus = self::STATUS_PENDING_USER;
				break;
		}

		return $defaultStatus;
	}
}
