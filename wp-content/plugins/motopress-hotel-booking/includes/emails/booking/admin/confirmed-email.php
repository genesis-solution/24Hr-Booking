<?php

namespace MPHB\Emails\Booking\Admin;

class ConfirmedEmail extends BaseEmail {

	public function getDefaultMessageHeaderText() {
		return __( 'Booking Confirmed', 'motopress-hotel-booking' );
	}

	public function getDefaultSubject() {
		return __( '%site_title% - Booking #%booking_id% Confirmed', 'motopress-hotel-booking' );
	}

	protected function initDescription() {
		$this->description = __( 'Email that will be sent to Admin when customer confirms booking.', 'motopress-hotel-booking' );
	}

	protected function initLabel() {
		$this->label = __( 'Approved Booking Email', 'motopress-hotel-booking' );
	}
}
