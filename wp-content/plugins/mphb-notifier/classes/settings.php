<?php

namespace MPHB\Notifier;

/**
 * @since 1.0
 */
class Settings {

	/**
	 * @return string
	 */
	public function getDefaultSubject() {
		//translators: title of website
		return esc_html__( 'Notification from %site_title%', 'mphb-notifier' );
	}

	/**
	 * @return string
	 */
	public function getDefaultHeader() {
		//translators: booking ID
		return esc_html__( 'Notification for your booking #%booking_id%', 'mphb-notifier' );
	}

	/**
	 * @return string
	 */
	public function getDefaultMessage() {
		ob_start();
			mphb_get_template_part( 'emails/notification-default' );
		return ob_get_clean();
	}

    public function isDoNotSendImportedBookingsToAdmin(): bool {

        return get_option( 'mphb_notifier_do_not_send_imported_bookings_to_admin', false );
    }

    public function isDoNotSendImportedBookingsToCustomer(): bool {

        return get_option( 'mphb_notifier_do_not_send_imported_bookings_to_customer', false );
    }

    public function isDoNotSendImportedBookingsToCustomEmails(): bool {

        return get_option( 'mphb_notifier_do_not_send_imported_bookings_to_custom_emails', false );
    }
}
