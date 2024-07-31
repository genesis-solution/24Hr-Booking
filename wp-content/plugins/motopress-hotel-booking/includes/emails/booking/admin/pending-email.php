<?php

namespace MPHB\Emails\Booking\Admin;

class PendingEmail extends BaseEmail {

	public function getDefaultMessageHeaderText() {
		return __( 'Confirm new booking', 'motopress-hotel-booking' );
	}

	public function getDefaultSubject() {
		return __( '%site_title% - New booking #%booking_id%', 'motopress-hotel-booking' );
	}

	protected function initDescription() {
		$this->description = __( 'Email that will be sent to administrator after booking is placed.', 'motopress-hotel-booking' );
	}

	protected function initLabel() {
		$this->label = __( 'Pending Booking Email', 'motopress-hotel-booking' );
	}
}
