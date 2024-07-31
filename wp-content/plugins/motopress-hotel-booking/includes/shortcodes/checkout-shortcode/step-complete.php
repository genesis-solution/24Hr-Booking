<?php

namespace MPHB\Shortcodes\CheckoutShortcode;

class StepComplete extends Step {

	public function setup() {
		// Clear cookies from checkout
		mphb_unset_cookie( 'mphb_checkout_step' );
		mphb_unset_cookie( 'mphb_rooms_details' );
		mphb_unset_cookie( 'mphb_check_in_date' );
		mphb_unset_cookie( 'mphb_check_out_date' );
	}

	public function render() {
		$this->showSuccessMessage();
	}
}
