<?php

namespace MPHB\Notifier\Helpers;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class NotificationHelper {

	const BOOKING_META_SENT_NOTIFICATION_ID = '_mphb_notification_sent';

	// this is helper with static methods only
	private function __construct() {}


	/**
	 * @return \MPHB\Emails\Templaters\EmailTemplater
	 */
	public static function getEmailNotificationsTemplater() {

		$emailTemplater = \MPHB\Emails\Templaters\EmailTemplater::create(
			array(
				'global'            => true,
				'booking'           => true,
				'booking_details'   => true,
				'user_confirmation' => true,
				'user_cancellation' => true,
			)
		);

		$emailTemplater->setupTags();

		return $emailTemplater;
	}

	/**
	 * @param \MPHB\Notifier\Entities\Notification|int $notification
	 * @param bool $testMode Optional. FALSE by default.
	 * @return \MPHB\Notifier\Emails\NotificationEmail|null
	 */
	public static function getNotificationEmail( $notification, $testMode = false ) {

		if ( is_int( $notification ) ) {
			$notification = mphb_notifier()->repositories()->notification()->findById( $notification );
		}

		if ( ! is_null( $notification ) ) {
			$notificationEmail = new \MPHB\Notifier\Emails\NotificationEmail(
				array(
					'id'           => $notification->getSlug(),
					'notification' => $notification,
				),
				static::getEmailNotificationsTemplater()
			);

			$notificationEmail->initStrings();

			return $notificationEmail;
		} else {
			return null;
		}
	}

	/**
	 * @param int $notificationId any notification id (in default language or any other language)
	 * @param string $languageTranslateTo language code like 'en'
	 * @return \MPHB\Notifier\Entities\Notification|null
	 */
	public static function getTranslatedNotification( $notificationId, $languageTranslateTo ) {

		$translatedNotificationId = apply_filters(
			'wpml_object_id',
			absint( $notificationId ),
			\MPHB\Notifier\Plugin::getInstance()->postTypes()->notification()->getPostType(),
			true,
			$languageTranslateTo
		);

		// we need to force reloading because cached translated notification could
		// have wrong origin notification meta data because it was loaded before wpml_object_id is initialised
		return mphb_notifier()->repositories()->notification()->findById( $translatedNotificationId, true );
	}

	/**
	 * @param \MPHB\Entities\Booking $booking
	 * @param int $notificationId - id of notification (in default language or translated id WPML is active)
	 * @return true if notification sent successfully otherwise false
	 */
	public static function sendEmailNotificationForBooking( $booking, $notificationId, $isSendOnlyOnce = true, $isTestNotification = false ) {

		if ( null === $booking ) {
			return;
		}

		$translatedNotification = static::getTranslatedNotification( $notificationId, $booking->getLanguage() );

		if ( null === $translatedNotification ) {
			return;
		}

		$notificationEmail = new \MPHB\Notifier\Emails\NotificationEmail(
			array(
				'id'           => $translatedNotification->getSlug(),
				'notification' => $translatedNotification,
			),
			static::getEmailNotificationsTemplater()
		);

		$notificationEmail->initStrings();

		if ( null === $notificationEmail ) {
			return;
		}

		$isSend = $notificationEmail->trigger( $booking, array( 'test_mode' => $isTestNotification ) );

		if ( $isSendOnlyOnce && $isSend && ! $isTestNotification ) {

			add_post_meta(
				$booking->getId(),
				static::BOOKING_META_SENT_NOTIFICATION_ID,
				$translatedNotification->getOriginalId()
			);
		}

		return $isSend;
	}
}
