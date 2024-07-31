<?php

namespace MPHB\Emails\Booking\Customer;

abstract class BaseEmail extends \MPHB\Emails\AbstractEmail {

	/**
	 * @param array  $atts
	 * @param string $atts['id'] ID of Email.
	 * @param string $atts['label'] Label.
	 * @param string $atts['description'] Optional. Email description.
	 * @param string $atts['default_subject'] Optional. Default subject of email.
	 * @param string $atts['default_header_text'] Optional. Default text in header.
	 */
	public function __construct( $atts, \MPHB\Emails\Templaters\EmailTemplater $templater ) {

		parent::__construct( $atts, $templater );

		add_action( 'mphb_generate_settings_customer_emails', array( $this, 'generateSettingsFields' ) );
	}

	/**
	 * @return string
	 * @since 3.8.6 returns the hotel admin email in test mode.
	 */
	protected function getReceiver() {

		$receiverEmail = '';

		if ( $this->isTestMode ) {

			$receiverEmail = MPHB()->settings()->emails()->getHotelAdminEmail();

		} elseif ( isset( $this->customer ) ) {

			$receiverEmail = $this->customer->getEmail();

		} elseif ( isset( $this->booking ) ) {

			$receiverEmail = $this->booking->getCustomer()->getEmail();
		}

		return $receiverEmail;
	}

	/**
	 * @param bool $isSended
	 */
	protected function log( $isSended ) {

		$author = $this->getAuthor();

		if ( $isSended ) {

			$this->booking->addLog( sprintf( __( '"%s" mail was sent to customer.', 'motopress-hotel-booking' ), $this->label ), $author );

		} else {

			$this->booking->addLog( sprintf( __( '"%s" mail sending is failed.', 'motopress-hotel-booking' ), $this->label ), $author );
		}
	}

	/**
	 * @return bool
	 */
	public function send() {

		do_action( '_mphb_translate_customer_email_before_send', $this->booking );

		$isSended = parent::send();

		do_action( '_mphb_translate_customer_email_after_send', $this->booking );

		return $isSended;
	}
}
