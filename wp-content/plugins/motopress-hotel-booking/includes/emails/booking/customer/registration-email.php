<?php

namespace MPHB\Emails\Booking\Customer;

class RegistrationEmail extends BaseEmail {

	public function getDefaultMessageHeaderText() {
		return __( 'Welcome', 'motopress-hotel-booking' );
	}

	public function getDefaultSubject() {
		return __( '%site_title% - account details', 'motopress-hotel-booking' );
	}

	protected function initDescription() {
		$this->description = __( 'Email that will be sent to a customer after they registered on your site.', 'motopress-hotel-booking' );
	}

	protected function initLabel() {
		$this->label = __( 'Customer Registration Email', 'motopress-hotel-booking' );
	}
}
