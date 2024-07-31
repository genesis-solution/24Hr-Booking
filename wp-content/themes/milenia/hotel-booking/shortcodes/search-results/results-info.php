<?php
/**
 * Available variables
 * - int $roomTypesCount count of found rooms
 * - int $adults
 * - int $children
 * - string $checkInDate date in human readable format
 * - string $checkOutDate date in human readable format
 */
if ( !defined( 'ABSPATH' ) ) {
	exit;
}
?>

<!--================ Alert Box ================-->
<div id="milenia-nothing-found-alert-box" role="alert" class="milenia-alert-box milenia-alert-box--info">
    <div class="milenia-alert-box-inner">
        <button type="button" class="milenia-alert-box-close"><?php esc_html_e('Close', 'milenia'); ?></button>
		<?php
		if ( $roomTypesCount > 0 ) {
			printf( _n( '%s accommodation found', '%s accommodations found', $roomTypesCount, 'milenia' ), $roomTypesCount );
		} else {
			esc_html_e( 'No accommodations found', 'milenia' );
		}
		printf( esc_html__( ' from %s - till %s', 'milenia' ), $checkInDate, $checkOutDate );
		?>
    </div>
</div>
<!--================ End of Alert Box ================-->
