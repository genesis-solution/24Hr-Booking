<?php
/**
 * The Template for cancellation details
 *
 * Content that will be replace %cancellation_details% tag in emails.
 *
 * @version 2.0.0
 */
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<?php esc_html_e( 'Click the link below to cancel your booking.', 'motopress-hotel-booking' ); ?>
<br/>
<a href="%user_cancel_link%"><?php esc_html_e( 'Cancel your booking', 'motopress-hotel-booking' ); ?></a>
<br/>
