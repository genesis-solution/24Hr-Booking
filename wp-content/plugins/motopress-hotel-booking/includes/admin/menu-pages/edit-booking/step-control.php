<?php

namespace MPHB\Admin\MenuPages\EditBooking;

use MPHB\Entities\Booking;

/**
 * @since 3.8
 */
class StepControl {

	/**
	 * @var Booking
	 */
	protected $editBooking = null;

	/**
	 * @param Booking $editBooking
	 */
	public function __construct( $editBooking ) {
		$this->editBooking = $editBooking;
	}

	public function setup() {
		add_action( 'admin_enqueue_scripts', array( MPHB()->getAdminScriptManager(), 'enqueue' ) );

		add_action( 'mphb_edit_booking_form', array( $this, 'display' ), 10, 2 );
	}

	/**
	 * @param Booking $editBooking Completely similar to the booking in the constructor.
	 * @param array   $settings
	 *
	 * @see \MPHB\Admin\MenuPages\EditBookingMenuPage2::renderValid()
	 */
	public function display( $editBooking, $settings ) {}
}
