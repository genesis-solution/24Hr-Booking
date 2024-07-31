<?php

namespace MPHB\Emails\Booking\Customer;

class PendingEmail extends BaseEmail {

	public function getDefaultMessageHeaderText() {
		return __( 'Your booking is placed', 'motopress-hotel-booking' );
	}

	public function getDefaultSubject() {
		return __( '%site_title% - Booking #%booking_id% is placed', 'motopress-hotel-booking' );
	}

	protected function initDescription() {
		$userConfirmationNote = '&nbsp;<strong>' . __( 'This email is sent when "Booking Confirmation Mode" is set to Admin confirmation.', 'motopress-hotel-booking' ) . '</strong>';
		$this->description    = __( 'Email that will be sent to customer after booking is placed.', 'motopress-hotel-booking' ) . $userConfirmationNote;
	}

	protected function initLabel() {
		$this->label = __( 'New Booking Email (Confirmation by Admin)', 'motopress-hotel-booking' );
	}
}
