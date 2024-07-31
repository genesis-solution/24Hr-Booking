<?php

namespace MPHB\Notifier\Admin\MetaBoxes;

use MPHB\Notifier\Utils\BookingUtils;
use MPHB\Notifier\Helpers\NotificationHelper;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


class TestMetaBox extends CustomMetaBox {

	/**
	 * @param \MPHB\Admin\Groups\MetaBoxGroup[] $metaBoxes
	 * @param string                            $postType
	 * @return \MPHB\Admin\Groups\MetaBoxGroup[]
	 */
	public function registerThis( $metaBoxes, $postType ) {

		if ( $postType == $this->postType ) {
			$metaBoxes[] = $this;
		}

		return $metaBoxes;
	}

	public function render() {

		parent::render();

		echo '<p class="mphb-notifier-test-email-description">';
			echo esc_html__( 'Save and send this notification to the administrator email address. To test custom notices, make sure you’ve added Accommodation Notice 1/Notice 2 in the Accommodation type menu.', 'mphb-notifier' );
		echo '</p>';

		echo '<p class="mphb-notifier-test-email-submit">';
			echo '<input name="send_notification" type="submit" class="button button-secondary button-large" value="' . esc_attr__( 'Send Email', 'mphb-notifier' ) . '" />';
		echo '</p>';
	}

	public function save() {

		parent::save();

		if ( isset( $_POST['send_notification'] ) && $this->isValidRequest() ) {

			NotificationHelper::sendEmailNotificationForBooking(
				BookingUtils::getTestBooking(),
				$this->getEditingPostId(),
				false,
				true
			);
		}
	}
}
