<?php

namespace MPHB;

class CalendarFeed {

	public function __construct() {
		add_action( 'init', array( $this, 'setUpFeed' ) );
	}

	public function setUpFeed() {
		add_feed( 'mphb.ics', array( $this, 'exportIcs' ) );
	}

	public function exportIcs() {
		if ( ! isset( $_GET['accommodation_id'] ) ) {
			return;
		}

		$roomId   = absint( $_GET['accommodation_id'] );
		$exporter = new \MPHB\iCal\Exporter();
		$exporter->export( $roomId );
	}
}
