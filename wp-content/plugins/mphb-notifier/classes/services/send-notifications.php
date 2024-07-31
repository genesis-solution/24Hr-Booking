<?php

namespace MPHB\Notifier\Services;

use MPHB\Notifier\Async\BackgroundProcess;
use MPHB\Notifier\Utils\BookingUtils;
use MPHB\Notifier\Helpers\NotificationHelper;
use MPHB\Notifier\Entities\Notification;


if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @since 1.0
 */
class SendNotifications extends BackgroundProcess {

	public $prefix = 'mphb_notifier';
	public $action = 'send_notifications';

	protected $optionStartedAt              = 'wpbg_process_started_at';
	protected $optionNotificationsTriggered = 'wpbg_process_notifications_triggered';
	protected $optionEmailsSent             = 'wpbg_process_emails_sent';

	public function __construct( $properties = array() ) {

		parent::__construct( $properties );

		$this->optionStartedAt              = $this->name . '_started_at';
		$this->optionNotificationsTriggered = $this->name . '_notifications_triggered';
		$this->optionEmailsSent             = $this->name . '_emails_sent';
	}

	public function triggerAll() {

		// Get all active notifications with non-empty list of recipients
		$notifications = mphb_notifier()->repositories()->notification()->findAllActive();

		$tasks = array();

		foreach ( $notifications as $notification ) {

			if ( $notification->isSendingAutomatic() ) {

				$tasks[] = array(
					'action'          => 'trigger-notification',
					'notification_id' => $notification->getId(),
				);
			}
		}

		// Set initial values on the first call
		if ( ! $this->isInProgress() ) {

			$this->updateOption( $this->optionStartedAt, time() );
			$this->updateOption( $this->optionNotificationsTriggered, 0 );
			$this->updateOption( $this->optionEmailsSent, 0 );
		}

		// Send notifications
		$this->addTasks( $tasks );
		$this->touchWhenReady( true );
	}

	/**
	 * @param int $notificationId
	 * @return bool
	 */
	protected function triggerNotification( $notificationId ) {

		$notification = mphb_notifier()->repositories()->notification()->findById( $notificationId );

		if ( is_null( $notification ) ) {
			return false; // Error
		}

		$bookings = BookingUtils::findByNotification( $notification );

		if ( empty( $bookings ) ) {
			return true; // There are no bookings but this is not an error
		}

		// Send notifications for each bookings and each recipient
		$emailsSent = 0;

		foreach ( $bookings as $bookingId ) {

			$booking   = mphb()->getBookingRepository()->findById( $bookingId );
			$receivers = ! is_null( $booking ) ? $notification->getReceivers( $booking ) : array();

			if ( empty( $receivers ) ) {
				continue; // There is nothing to trigger
			}

			$wasSent = $this->triggerNotificationOnBooking( $booking, $notification );

			if ( $wasSent ) {

				$emailsSent += count( $receivers );
			}
		}

		// Save results
		if ( $emailsSent > 0 ) {

			$this->increaseNotificationsTriggered( 1 );
			$this->increaseEmailSent( $emailsSent );
		}

		return true;
	}

	/**
	 * @param \MPHB\Entities\Booking               $booking
	 * @param \MPHB\Notifier\Entities\Notification $notification
	 * @return bool
	 */
	protected function triggerNotificationOnBooking( $booking, $notification ) {

		if ( BookingUtils::isNotificationDisabledForReservationAfterTrigger( $notification, $booking ) ) {

			$booking->addLog(
				sprintf(
					//translators: %s is notification label
					__( 'Notification "%s" not sent, booking date does not meet requirements.', 'mphb-notifier' ),
					$notification->getTitle()
				),
				0
			);

			return false;
		}

		switch ( $notification->getType() ) {

			case 'email':
				return NotificationHelper::sendEmailNotificationForBooking(
					$booking,
					$notification->getId(),
					true
				);
				break;

			default:
				return false;
				break;
		}
	}

	/**
	 * @param \MPHB\Entities\Booking $booking
	 */
	public function triggerNewBooking( $booking ) {

		// Get all active notifications with non-empty list of recipients that
		// suit new booking
		$notifications = mphb_notifier()->repositories()->notification()->findAllByNewBooking( $booking );

		foreach ( $notifications as $notification ) {

			$this->triggerNotificationOnBooking( $booking, $notification );
		}
	}

	public function task( $workload ) {

		if ( ! isset( $workload['action'] ) ) {
			return false;
		}

		switch ( $workload['action'] ) {

			case 'trigger-notification':
				return $this->triggerNotification( $workload['notification_id'] );
				break;

			default:
				return false;
				break;
		}
	}

	protected function afterComplete() {

		// Save process stats
		$this->updateOption(
			'mphb_notifier_last_execution',
			array(
				'execution_time'          => $this->startedAt(),
				'notifications_triggered' => $this->notificationsTriggered(),
				'emails_sent'             => $this->emailsSent(),
			)
		);

		parent::afterComplete();
	}

	protected function clearOptions() {

		parent::clearOptions();

		delete_option( $this->optionStartedAt );
		delete_option( $this->optionNotificationsTriggered );
		delete_option( $this->optionEmailsSent );
	}

	/**
	 * @param int $increment
	 */
	protected function increaseNotificationsTriggered( $increment ) {

		$this->updateOption( $this->optionNotificationsTriggered, $this->notificationsTriggered() + $increment );
	}

	/**
	 * @param int $increment
	 */
	protected function increaseEmailSent( $increment ) {

		$this->updateOption( $this->optionEmailsSent, $this->emailsSent() + $increment );
	}

	/**
	 * @return int
	 */
	protected function startedAt() {

		return (int) $this->getOption( $this->optionStartedAt, 0 );
	}

	/**
	 * @return int
	 */
	protected function notificationsTriggered() {

		return (int) $this->getOption( $this->optionNotificationsTriggered, 0 );
	}

	/**
	 * @return int
	 */
	protected function emailsSent() {

		return (int) $this->getOption( $this->optionEmailsSent, 0 );
	}
}
