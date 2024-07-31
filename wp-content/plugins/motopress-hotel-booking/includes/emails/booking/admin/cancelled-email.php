<?php

namespace MPHB\Emails\Booking\Admin;

class CancelledEmail extends BaseEmail {

	public function getDefaultMessageHeaderText() {
		return __( 'Booking Cancelled', 'motopress-hotel-booking' );
	}

	public function getDefaultSubject() {
		return __( '%site_title% - Booking #%booking_id% Cancelled', 'motopress-hotel-booking' );
	}

	protected function initDescription() {
		$this->description = __( 'Email that will be sent to Admin when customer cancels booking.', 'motopress-hotel-booking' );
	}

	protected function initLabel() {
		$this->label = __( 'Cancelled Booking Email', 'motopress-hotel-booking' );
	}
}
