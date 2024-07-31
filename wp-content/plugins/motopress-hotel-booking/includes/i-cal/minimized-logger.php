<?php

namespace MPHB\iCal;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @since 4.2.2
 */
class MinimizedLogger extends Logger {

	public function log( $status, $message ) {
		// Skip info messages
		if ( $status !== 'info' ) {
			parent::log( $status, $message );
		}
	}
}
