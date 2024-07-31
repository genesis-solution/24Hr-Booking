<?php
/**
 * The Template for reserved room details content
 *
 * Content that will be replace %reserved_rooms_details% tag in emails.
 *
 * @version 2.0.0
 */
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<h4><?php echo esc_html( sprintf( __( 'Accommodation #%s', 'motopress-hotel-booking' ), '%room_key%' ) ); ?></h4>
<?php echo esc_html( sprintf( __( 'Adults: %s', 'motopress-hotel-booking' ), '%adults%' ) ); ?>
<br/>
<?php echo esc_html( sprintf( __( 'Children: %s', 'motopress-hotel-booking' ), '%children%' ) ); ?>
<br/>
<?php
	// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	echo sprintf( __( 'Accommodation: <a href="%1$s">%2$s</a>', 'motopress-hotel-booking' ), '%room_type_link%"', '%room_type_title%' );
?>
<br/>
<?php echo esc_html( sprintf( __( 'Accommodation Rate: %s', 'motopress-hotel-booking' ), '%room_rate_title%' ) ); ?>
<br/>
%room_rate_description%
<br/>
<?php echo esc_html( sprintf( __( 'Bed Type: %s', 'motopress-hotel-booking' ), '%room_type_bed_type%' ) ); ?>
<br/>
<h4><?php esc_html_e( 'Additional Services', 'motopress-hotel-booking' ); ?></h4>
%services%
<br/>
