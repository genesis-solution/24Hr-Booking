<?php

namespace MPHB\Emails\Booking\Customer;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


class DirectBankTransferEmail extends BaseEmail {

	const EMAIL_ID = 'customer_direct_bank_transfer_instructions';


	/**
	 * @param string $atts['id'] ID of Email.
	 * @param string $atts['label'] Label.
	 * @param string $atts['description'] Optional. Email description.
	 * @param string $atts['default_subject'] Optional. Default subject of email.
	 * @param string $atts['default_header_text'] Optional. Default text in header.
	 */
	public function __construct( array $atts, \MPHB\Emails\Templaters\EmailTemplater $templater ) {

		parent::__construct( $atts, $templater );

		remove_action( 'mphb_generate_settings_customer_emails', array( $this, 'generateSettingsFields' ) );

		// TODO: remove these methods in abstract email and check all emails with notifier
		$this->initDescription();
		$this->initLabel();
	}


	public function getDefaultMessageHeaderText() {
		return __( 'Pay for your booking', 'motopress-hotel-booking' );
	}

	public function getDefaultSubject() {
		return __( '%site_title% - Pay for your booking #%booking_id%', 'motopress-hotel-booking' );
	}

	protected function initDescription() {
		$this->description = __( 'Email that will be sent to customer after booking is placed.', 'motopress-hotel-booking' );
	}

	protected function initLabel() {
		$this->label = __( 'Payment Instructions Email', 'motopress-hotel-booking' );
	}

	/**
	 * @return string
	 */
	public function getDefaultMessageTemplate() {

		ob_start();
		mphb_get_template_part( 'emails/payment-gateways/direct-bank-transfer/customer-pending-booking' );
		return ob_get_clean();
	}
}
