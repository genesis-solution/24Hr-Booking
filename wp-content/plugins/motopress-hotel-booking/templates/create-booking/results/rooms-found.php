<?php

/**
 * Available variables
 * - int $foundRooms Count of found rooms
 * - string $checkInDate Date in human-readable format
 * - string $checkOutDate Date in human-readable format
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?>

<p class="mphb-search-results-summary">
	<?php
	if ( $foundRooms > 0 ) {
		echo esc_html( sprintf( _n( '%s accommodation found', '%s accommodations found', $foundRooms, 'motopress-hotel-booking' ), $foundRooms ) );
	} else {
		esc_html_e( 'No accommodations found', 'motopress-hotel-booking' );
	}

		echo esc_html( sprintf( __( ' from %s - till %s', 'motopress-hotel-booking' ), $checkInDate, $checkOutDate ) );
	?>
</p>
