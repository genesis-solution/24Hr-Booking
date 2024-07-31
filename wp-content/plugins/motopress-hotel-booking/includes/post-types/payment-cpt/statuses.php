<?php

namespace MPHB\PostTypes\PaymentCPT;

use \MPHB\PostTypes\AbstractCPT;

/**
 * @since 3.9.6 - 'mphb-p-cancelled' status added.
 */
class Statuses extends AbstractCPT\Statuses {

	const STATUS_PENDING   = 'mphb-p-pending';
	const STATUS_COMPLETED = 'mphb-p-completed';
	const STATUS_FAILED    = 'mphb-p-failed';
	const STATUS_ABANDONED = 'mphb-p-abandoned';
	const STATUS_ON_HOLD   = 'mphb-p-on-hold';
	const STATUS_REFUNDED  = 'mphb-p-refunded';
	const STATUS_CANCELLED = 'mphb-p-cancelled';

	public function __construct( $postType ) {

		parent::__construct( $postType );
		add_action( 'transition_post_status', array( $this, 'transitionStatus' ), 10, 3 );
	}

	protected function initStatuses() {

		$this->statuses[ self::STATUS_PENDING ]   = array();
		$this->statuses[ self::STATUS_COMPLETED ] = array();
		$this->statuses[ self::STATUS_FAILED ]    = array();
		$this->statuses[ self::STATUS_ABANDONED ] = array();
		$this->statuses[ self::STATUS_ON_HOLD ]   = array();
		$this->statuses[ self::STATUS_REFUNDED ]  = array();
		$this->statuses[ self::STATUS_CANCELLED ] = array();
	}

	public function getStatusArgs( $statusName ) {

		$args = array();

		switch ( $statusName ) {

			case self::STATUS_PENDING:
				$args = array(
					'label'                     => _x( 'Pending', 'Payment status', 'motopress-hotel-booking' ),
					'public'                    => true,
					'exclude_from_search'       => false,
					'show_in_admin_all_list'    => true,
					'show_in_admin_status_list' => true,
					'label_count'               => _n_noop( 'Pending <span class="count">(%s)</span>', 'Pending <span class="count">(%s)</span>', 'motopress-hotel-booking' ),
				);
				break;

			case self::STATUS_COMPLETED:
				$args = array(
					'label'                     => _x( 'Completed', 'Payment status', 'motopress-hotel-booking' ),
					'public'                    => true,
					'exclude_from_search'       => false,
					'show_in_admin_all_list'    => true,
					'show_in_admin_status_list' => true,
					'label_count'               => _n_noop( 'Completed <span class="count">(%s)</span>', 'Completed <span class="count">(%s)</span>', 'motopress-hotel-booking' ),
				);
				break;

			case self::STATUS_FAILED:
				$args = array(
					'label'                     => _x( 'Failed', 'Payment status', 'motopress-hotel-booking' ),
					'public'                    => true,
					'exclude_from_search'       => false,
					'show_in_admin_all_list'    => true,
					'show_in_admin_status_list' => true,
					'label_count'               => _n_noop( 'Failed <span class="count">(%s)</span>', 'Failed <span class="count">(%s)</span>', 'motopress-hotel-booking' ),
				);
				break;

			case self::STATUS_ABANDONED:
				$args = array(
					'label'                     => _x( 'Abandoned', 'Payment status', 'motopress-hotel-booking' ),
					'public'                    => true,
					'exclude_from_search'       => false,
					'show_in_admin_all_list'    => true,
					'show_in_admin_status_list' => true,
					'label_count'               => _n_noop( 'Abandoned <span class="count">(%s)</span>', 'Abandoned <span class="count">(%s)</span>', 'motopress-hotel-booking' ),
				);
				break;

			case self::STATUS_ON_HOLD:
				$args = array(
					'label'                     => _x( 'On Hold', 'Payment status', 'motopress-hotel-booking' ),
					'public'                    => true,
					'exclude_from_search'       => false,
					'show_in_admin_all_list'    => true,
					'show_in_admin_status_list' => true,
					'label_count'               => _n_noop( 'On Hold <span class="count">(%s)</span>', 'On Hold <span class="count">(%s)</span>', 'motopress-hotel-booking' ),
				);
				break;

			case self::STATUS_REFUNDED:
				$args = array(
					'label'                     => _x( 'Refunded', 'Payment status', 'motopress-hotel-booking' ),
					'public'                    => true,
					'exclude_from_search'       => false,
					'show_in_admin_all_list'    => true,
					'show_in_admin_status_list' => true,
					'label_count'               => _n_noop( 'Refunded <span class="count">(%s)</span>', 'Refunded <span class="count">(%s)</span>', 'motopress-hotel-booking' ),
				);
				break;

			case self::STATUS_CANCELLED:
				$args = array(
					'label'                     => _x( 'Cancelled', 'Payment status', 'motopress-hotel-booking' ),
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

	public function transitionStatus( $newStatus, $oldStatus, $post ) {

		if ( $post->post_type === $this->postType && $newStatus !== $oldStatus ) {

			// Prevent logging status change while importing
			if ( apply_filters( 'mphb_prevent_handle_payment_status_transition', false ) ) {
				return;
			}

			$payment = MPHB()->getPaymentRepository()->findById( $post->ID, true );

			if ( $oldStatus == 'new' ) {
				$payment->generateKey();
			}

			$expirationStatuses = array(
				self::STATUS_PENDING,
			);

			if ( $newStatus === self::STATUS_PENDING ) {

				$payment->updateExpiration( time() + MPHB()->settings()->payment()->getPendingTime() * MINUTE_IN_SECONDS );

				MPHB()->cronManager()->getCron( 'abandon_payment_pending' )->schedule();
			}

			if ( in_array( $oldStatus, $expirationStatuses ) && ! in_array( $newStatus, $expirationStatuses ) ) {
				$payment->deleteExpiration();
			}

			// For "Pay on arrival" make booking confirmed when the payment is on hold
			$instantPaymentMethods = apply_filters( 'mphb_instant_paymant_methods', array( 'cash' ) );
			$isInstantMethod       = in_array( $payment->getGatewayId(), $instantPaymentMethods );

			$handleStatusAs = ( $newStatus == self::STATUS_ON_HOLD && $isInstantMethod ) ? self::STATUS_COMPLETED : $newStatus;

			switch ( $handleStatusAs ) {

				case self::STATUS_COMPLETED:
					$booking = MPHB()->getBookingRepository()->findById( $payment->getBookingId(), true );
					if ( $booking && $booking->isExpectPayment( $payment->getId() ) ) {
						if ( $isInstantMethod && $newStatus == $handleStatusAs ) {
							// Don't trigger "Approved Booking Email (via payment)" email
							// when "Pay on Arrival" confirmed. Already send it on status "On Hold"
						} else {
							$booking->setStatus( \MPHB\PostTypes\BookingCPT\Statuses::STATUS_CONFIRMED );
							MPHB()->getBookingRepository()->save( $booking );
							do_action( 'mphb_booking_confirmed_with_payment', $booking, $payment );
						}
					}
					break;

				case self::STATUS_ON_HOLD:
					$booking = MPHB()->getBookingRepository()->findById( $payment->getBookingId(), true );

					if ( $booking && $booking->isExpectPayment( $payment->getId() ) ) {

						$booking->addLog( sprintf( __( 'Payment (#%s) for this booking is on hold', 'motopress-hotel-booking' ), $payment->getId() ) );

						if ( \MPHB\Payments\Gateways\BankGateway::PAYMENT_GATEWAY_ID == $payment->getGatewayId() ) {

							$booking->setStatus( \MPHB\PostTypes\BookingCPT\Statuses::STATUS_PENDING_PAYMENT );

						} else {

							$booking->setStatus( \MPHB\PostTypes\BookingCPT\Statuses::STATUS_PENDING );
						}

						MPHB()->getBookingRepository()->save( $booking );
					}
					break;

				case self::STATUS_ABANDONED:
					$booking = MPHB()->getBookingRepository()->findById( $payment->getBookingId(), true );
					if ( $booking && $booking->isExpectPayment( $payment->getId() ) ) {
						$booking->setStatus( \MPHB\PostTypes\BookingCPT\Statuses::STATUS_ABANDONED );
						MPHB()->getBookingRepository()->save( $booking );
					}
					break;

				case self::STATUS_REFUNDED:
				case self::STATUS_FAILED:
					$booking = MPHB()->getBookingRepository()->findById( $payment->getBookingId(), true );
					if ( $booking && $booking->isExpectPayment( $payment->getId() ) ) {
						$booking->setStatus( \MPHB\PostTypes\BookingCPT\Statuses::STATUS_CANCELLED );
						MPHB()->getBookingRepository()->save( $booking );
					}
					break;
			}

			$payment->addLog( sprintf( __( 'Status changed from %s to %s.', 'motopress-hotel-booking' ), mphb_get_status_label( $oldStatus ), mphb_get_status_label( $newStatus ) ) );

			do_action( 'mphb_payment_status_changed', $payment, $oldStatus );
		}
	}

	/**
	 * @return string[]
	 * @since 4.2.2
	 */
	public function getFinishedStatuses() {

		 // All, except STATUS_PENDING and STATUS_ON_HOLD
		return array(
			self::STATUS_COMPLETED,
			self::STATUS_FAILED,
			self::STATUS_ABANDONED,
			self::STATUS_REFUNDED,
			self::STATUS_CANCELLED,
		);
	}
}
