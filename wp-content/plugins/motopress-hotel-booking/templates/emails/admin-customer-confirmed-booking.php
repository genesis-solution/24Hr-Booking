<?php
/*
 * The Template for Approved Booking Email content
 *
 * Email that will be sent to Admin when customer confirms booking.
 *
 * @version 2.0.0
 */
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<?php echo esc_html( sprintf( __( 'Booking #%s is confirmed by customer.', 'motopress-hotel-booking' ), '%booking_id%' ) ); ?>
<br/><br/><a href="%booking_edit_link%"><?php esc_html_e( 'View Booking', 'motopress-hotel-booking' ); ?></a>
<h4><?php esc_html_e( 'Details of booking', 'motopress-hotel-booking' ); ?></h4>
<?php echo esc_html( sprintf( __( 'Check-in: %1$s, from %2$s', 'motopress-hotel-booking' ), '%check_in_date%', '%check_in_time%' ) ); ?>
<br/>
<?php echo esc_html( sprintf( __( 'Check-out: %1$s, until %2$s', 'motopress-hotel-booking' ), '%check_out_date%', '%check_out_time%' ) ); ?>
<br/>
%reserved_rooms_details%
<h4><?php esc_html_e( 'Customer Info', 'motopress-hotel-booking' ); ?></h4>
<?php echo esc_html( sprintf( __( 'Name: %1$s %2$s', 'motopress-hotel-booking' ), '%customer_first_name%', '%customer_last_name%' ) ); ?>
<br/>
<?php echo esc_html( sprintf( __( 'Email: %s', 'motopress-hotel-booking' ), '%customer_email%' ) ); ?>
<br/>
<?php echo esc_html( sprintf( __( 'Phone: %s', 'motopress-hotel-booking' ), '%customer_phone%' ) ); ?>
<br/>
<?php echo esc_html( sprintf( __( 'Note: %s', 'motopress-hotel-booking' ), '%customer_note%' ) ); ?>
<br/>
<h4><?php esc_html_e( 'Total Price:', 'motopress-hotel-booking' ); ?></h4>
%booking_total_price%
<br/>
