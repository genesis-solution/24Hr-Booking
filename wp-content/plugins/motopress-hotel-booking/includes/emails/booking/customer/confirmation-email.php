<?php

namespace MPHB\Emails\Booking\Customer;

class ConfirmationEmail extends BaseEmail {

	public function getDefaultMessageHeaderText() {
		return __( 'Confirm your booking', 'motopress-hotel-booking' );
	}

	public function getDefaultSubject() {
		return __( '%site_title% - Confirm your booking #%booking_id%', 'motopress-hotel-booking' );
	}

	protected function initDescription() {
		$userConfirmationNote = '&nbsp;<strong>' . __( 'This email is sent when "Booking Confirmation Mode" is set to Customer confirmation via email.', 'motopress-hotel-booking' ) . '</strong>';
		$this->description    = __( 'Email that will be sent to customer after booking is placed.', 'motopress-hotel-booking' ) . $userConfirmationNote;
	}

	protected function initLabel() {
		$this->label = __( 'New Booking Email (Confirmation by User)', 'motopress-hotel-booking' );
	}
}
