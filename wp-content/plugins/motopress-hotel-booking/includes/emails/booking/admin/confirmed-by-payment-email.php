<?php

namespace MPHB\Emails\Booking\Admin;

class ConfirmedByPaymentEmail extends BaseEmail {

	public function getDefaultMessageHeaderText() {
		return __( 'Booking Confirmed', 'motopress-hotel-booking' );
	}

	public function getDefaultSubject() {
		return __( '%site_title% - Booking #%booking_id% Confirmed', 'motopress-hotel-booking' );
	}

	protected function initDescription() {
		$this->description = __( 'Email that will be sent to Admin when payment is completed.', 'motopress-hotel-booking' );
	}

	protected function initLabel() {
		$this->label = __( 'Approved Booking Email (via payment)', 'motopress-hotel-booking' );
	}
}
