<?php

namespace MPHB\Notifier\Admin\MetaBoxes;

use MPHB\Notifier\Helpers\NotificationHelper;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class SendNotificationMetabox extends CustomMetaBox {


	public function __construct() {

		parent::__construct(
			'mphb_send_notification',
			__( 'Send Notification', 'mphb-notifier' ),
			mphb()->postTypes()->booking()->getPostType(),
			'side',
			'default'
		);
	}

	/**
	 * @return array
	 */
	protected function generateFields() {

		$allNotificationsSelectOptions = array(
			'' => __( '— Select Notification —', 'mphb-notifier' ),
		);

		$defaultLanguage = MPHB()->translation()->getDefaultLanguage();
		$notifications   = mphb_notifier()->repositories()->notification()->findAllActive();

		$is_add_notification_to_the_list = true;

		foreach ( $notifications as $notification ) {

			if ( function_exists( 'wpml_get_language_information' ) ) {

				$languageInfo = wpml_get_language_information( null, $notification->getId() );

				$is_add_notification_to_the_list = $languageInfo['language_code'] === $defaultLanguage;
			}

			if ( $is_add_notification_to_the_list ) {
				$allNotificationsSelectOptions[ $notification->getId() ] = $notification->getTitle();
			}
		}

		return array(
			'mphb_notification' => \MPHB\Admin\Fields\FieldFactory::create(
				'mphb_notification',
				array(
					'type'  => 'select',
					'label' => __( 'Notification', 'mphb-notifier' ),
					'list'  => $allNotificationsSelectOptions,
				)
			),
		);
	}

	public function render() {

		parent::render();

		echo '<p class="mphb-send-notification">';
			echo '<input name="send_notification" type="submit" class="button button-secondary button-large" value="' .
				esc_attr__( 'Send Notification', 'mphb-notifier' ) . '" />';
		echo '</p>';
	}

	public function save() {

		parent::save();

		if ( isset( $_POST['send_notification'] ) && $this->isValidRequest() ) {

			$booking = mphb()->getBookingRepository()->findById( $this->getEditingPostId() );
			$notificationId = ! empty( $_POST['mphb_notification'] ) ? absint( wp_unslash( $_POST['mphb_notification'] ) ) : 0;

			NotificationHelper::sendEmailNotificationForBooking( $booking, $notificationId, false );
		}
	}
}
