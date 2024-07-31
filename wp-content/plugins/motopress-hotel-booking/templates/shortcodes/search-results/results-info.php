<?php
/**
 * Available variables
 * - int $roomTypesCount count of found rooms
 * - int $adults
 * - int $children
 * - string $checkInDate date in human readable format
 * - string $checkOutDate date in human readable format
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<p class="mphb_sc_search_results-info">
	<?php
	if ( $roomTypesCount > 0 ) {
		echo esc_html( sprintf( _n( '%s accommodation found', '%s accommodations found', $roomTypesCount, 'motopress-hotel-booking' ), $roomTypesCount ) );
	} else {
		esc_html_e( 'No accommodations found', 'motopress-hotel-booking' );
	}
	echo esc_html( sprintf( __( ' from %s - till %s', 'motopress-hotel-booking' ), $checkInDate, $checkOutDate ) );
	// echo esc_html( sprintf( __( ' for adults: %d, children: %d', 'motopress-hotel-booking' ), $adults, $children ) );
	// echo esc_html( sprintf( __( ' from %s - till %s', 'motopress-hotel-booking' ), $checkInDate, $checkOutDate ) );
	?>
</p>
