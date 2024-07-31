<?php
/**
 * The Template for Request Paid Email.
 *
 * Email that will be sent to Admin after customer has made the requested payment.
 *
 * @version 1.0
 */

if (!defined('ABSPATH')) {
    exit;
}
?>

<?php printf(esc_html__('New payment received for booking #%s.', 'mphb-request-payment'), '%booking_id%'); ?><br />
<br />
<a href="%booking_edit_link%"><?php esc_html_e('View Booking', 'mphb-request-payment'); ?></a>

<h4><?php esc_html_e('Details of payment', 'mphb-request-payment'); ?></h4>
<?php printf(esc_html__('Payment ID: #%s', 'mphb-request-payment'), '%payment_id%'); ?><br />
<?php printf(esc_html__('Amount: %s', 'mphb-request-payment'), '%payment_amount%'); ?><br />
<?php printf(esc_html__('Method: %s', 'mphb-request-payment'), '%payment_method%'); ?><br />

<h4><?php esc_html_e('Details of booking', 'mphb-request-payment'); ?></h4>
<?php printf(esc_html__('Check-in: %1$s, from %2$s', 'mphb-request-payment'), '%check_in_date%', '%check_in_time%'); ?><br />
<?php printf(esc_html__('Check-out: %1$s, until %2$s', 'mphb-request-payment'), '%check_out_date%', '%check_out_time%'); ?><br />

<h4><?php esc_html_e('Customer Info', 'mphb-request-payment'); ?></h4>
<?php printf(esc_html__('Name: %1$s %2$s', 'mphb-request-payment'), '%customer_first_name%', '%customer_last_name%'); ?><br />
<?php printf(esc_html__('Email: %s', 'mphb-request-payment'), '%customer_email%'); ?><br />
<?php printf(esc_html__('Phone: %s', 'mphb-request-payment'), '%customer_phone%'); ?><br />
<?php printf(esc_html__('Note: %s', 'mphb-request-payment'), '%customer_note%'); ?><br />
