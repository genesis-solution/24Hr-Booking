<?php
/**
 * The Template for New Customer Registration Email content
 *
 * Email that will be sent to client after a new customer registered.
 *
 * @version 4.2.0
 */
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<?php echo esc_html( sprintf( __( 'Hi %1$s %2$s,', 'motopress-hotel-booking' ), '%customer_first_name%', '%customer_last_name%' ) ); ?>
<br/><br/>
<?php echo esc_html( sprintf( __( 'Thanks for creating an account on %1$s.', 'motopress-hotel-booking' ), '%site_title%' ) ); ?>
<br/>
<h4><?php esc_html_e( 'You Account Details', 'motopress-hotel-booking' ); ?></h4>
<?php echo esc_html( sprintf( __( 'Login: %s', 'motopress-hotel-booking' ), '%user_login%' ) ); ?><br />
<?php echo esc_html( sprintf( __( 'Password: %s', 'motopress-hotel-booking' ), '%user_pass%' ) ); ?><br />
<?php echo esc_html( sprintf( __( 'Log in here: %s', 'motopress-hotel-booking' ), '%customer_account_link%' ) ); ?><br />
<br/>
<br/>
<?php
esc_html_e( 'Thank you!', 'motopress-hotel-booking' );
