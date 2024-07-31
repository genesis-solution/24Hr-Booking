<?php
/**
 * The Template for Payment Request Email content.
 *
 * Email that will be automatically or manually sent to customer requesting a
 * payment.
 *
 * @version 1.0
 */

if (!defined('ABSPATH')) {
   exit;
}
?>

<?php printf(esc_html__('Dear %1$s %2$s,', 'mphb-request-payment'), '%customer_first_name%', '%customer_last_name%'); ?><br />
<br />
<?php printf(esc_html__('We are contacting you in regard to %1$s balance due for your booking.', 'mphb-request-payment'), '%booking_balance_due%'); ?><br />
<br />
<a href="%booking_payment_request_link%"><?php esc_html_e('Pay Balance Now', 'mphb-request-payment'); ?></a><br />

<h4><?php esc_html_e('Details of booking', 'mphb-request-payment'); ?></h4>
<?php printf(esc_html__('ID: #%s', 'mphb-request-payment'), '%booking_id%'); ?><br />
<?php printf(esc_html__('Check-in: %1$s, from %2$s', 'mphb-request-payment'), '%check_in_date%', '%check_in_time%'); ?><br />
<?php printf(esc_html__('Check-out: %1$s, until %2$s', 'mphb-request-payment'), '%check_out_date%', '%check_out_time%'); ?><br />
<?php printf(esc_html__('Total Price: %s', 'mphb-request-payment'), '%booking_total_price%'); ?><br />
<?php printf(esc_html__('Balance Due: %s', 'mphb-request-payment'), '%booking_balance_due%'); ?><br />
<br />
<?php esc_html_e('If you are unable to complete payment now, please contact us so we can resolve the issue.', 'mphb-request-payment'); ?><br />
<br />
<?php esc_html_e('Thank you!', 'mphb-request-payment'); ?>