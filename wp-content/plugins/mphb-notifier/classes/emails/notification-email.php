<?php

namespace MPHB\Notifier\Emails;

use MPHB\Emails\Templaters\EmailTemplater;
use MPHB\Emails\AbstractEmail;

/**
 * @since 1.0
 */
class NotificationEmail extends AbstractEmail {

	/** @var \MPHB\Notifier\Entities\Notification */
	protected $notification = null;

	protected $isTestMode = false;

	/**
	 * @param array          $atts [id, notification]
	 * @param EmailTemplater $templater
	 */
	public function __construct( $atts, EmailTemplater $templater ) {

		$this->notification = $atts['notification'];

		parent::__construct( $atts, $templater );
	}

	/**
	 * @param \MPHB\Entities\Booking $booking
	 * @param array                  $atts Optional.
	 * @param \MPHB\Entities\Payment $atts['payment']
	 * @param bool                   $atts['test_mode'] Trigger email but don't add the logs.
	 *                       False by default.
	 * @return bool
	 *
	 * @todo Remove the outdated code after some time.
	 */
	public function trigger( $booking, $atts = array() ) {

		if ( mphb_version_at_least( '3.8.6' ) ) {
			return parent::trigger( $booking, $atts );
		}

		// Outdated version of the method

		$this->isTestMode = $isTestMode = isset( $atts['test_mode'] ) && $atts['test_mode'];

		if ( ! $isTestMode && ( $this->isDisabled() || $this->isPrevented() ) ) {
			return false;
		}

		// Do we have any receiver? (Test mode does not require any actual
		// receiver from the notification)
		if ( ! $isTestMode ) {
			$receivers = $this->notification->getReceivers( $booking );

			if ( empty( $receivers ) ) {

				// translators: %s is notification label
				$booking->addLog( sprintf( __( 'Notification "%s" will not be sent: there is no email address to send the notification to.', 'mphb-notifier' ), $this->label ), $this->getAuthor() );

				return false;
			}
		}

		// Setup booking and payment
		$this->setupBooking( $booking );

		if ( isset( $atts['payment'] ) ) {
			$this->templater->setupPayment( $atts['payment'] );
		}

		$isSent = $this->send();

		if ( ! $isTestMode ) {
			$this->log( $isSent );
		}

		return $isSent;
	}

	/**
	 * @return bool
	 *
	 * @todo Remove the outdated code after some time.
	 */
	public function send() {
		if ( mphb_version_at_least( '3.8.6' ) ) {
			return parent::send();
		}

		// Outdated version of the method

		$receivers = $this->getReceiver();
		$sentOne   = false;

		foreach ( $receivers as $receiver ) {
			$isSent  = mphb()->emails()->getMailer()->send( $receiver, $this->getSubject(), $this->getMessage() );
			$sentOne = $sentOne || $isSent;
		}

		return $sentOne;
	}

	/**
	 * @return string[]
	 */
	protected function getReceiver() {
		if ( ! $this->isTestMode ) {
			return $this->notification->getReceivers( $this->booking );
		} else {
			return array( mphb()->settings()->emails()->getHotelAdminEmail() );
		}
	}

	public function isDisabled() {
		return $this->notification->isDisabled();
	}

	public function isPrevented() {
		return false;
	}

	protected function getSubjectTemplate() {
		return $this->notification->getSubject();
	}

	public function getDefaultSubject() {
		return '';
	}

	protected function getMessageHeaderTextTemplate() {
		return $this->notification->getHeader();
	}

	public function getDefaultMessageHeaderText() {
		return '';
	}

	protected function getMessageTemplate() {
		return $this->notification->getMessage();
	}

	public function getDefaultMessageTemplate() {
		return '';
	}

	/**
	 * @param bool $isSent
	 */
	protected function log( $isSent ) {
		$author = $this->getAuthor();

		if ( $isSent ) {
			// translators: %s is notification label
			$this->booking->addLog( sprintf( esc_html__( 'Notification "%s" was sent.', 'mphb-notifier' ), $this->label ), $author );
		} else {
			// translators: %s is notification label
			$this->booking->addLog( sprintf( esc_html__( 'Notification "%s" sending failed.', 'mphb-notifier' ), $this->label ), $author );
		}
	}

	/**
	 * @return string
	 *
	 * @since 1.0.2
	 */
	protected function receiverError() {
		// translators: %s is notification label
		return sprintf( __( 'Notification "%s" will not be sent: there is no email address to send the notification to.', 'mphb-notifier' ), $this->label );
	}

	protected function initLabel() {
		$this->label = $this->notification->getTitle();
	}

	protected function initDescription() {}
}
