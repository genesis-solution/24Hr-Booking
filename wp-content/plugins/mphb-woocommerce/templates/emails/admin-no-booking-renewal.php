<?php
/**
 * The template for Manual Action Required Notice.
 *
 * An email notice that is sent to Admin when Hotel Booking is not able to process the booking automatically.
 *
 * @since 1.0.5
 */

if ( !defined('ABSPATH')) {
    exit;
}

?>

<?php _e('A WooCommerce order has been successful. However, there is no way to automatically update the status of the associated booking in Hotel Booking — some of the booked accommodations had already been reserved for these dates.', 'mphb-woocommerce'); ?><br>
<br>
<a href="%booking_edit_link%"><?php _e('View Booking', 'mphb-woocommerce'); ?></a>

<h4><?php _e('Details of booking', 'mphb-woocommerce'); ?></h4>
<?php printf(__('ID: #%s', 'mphb-woocommerce'), '%booking_id%'); ?><br>
<?php printf(__('Check-in: %1$s, from %2$s', 'mphb-woocommerce'), '%check_in_date%', '%check_in_time%'); ?><br>
<?php printf(__('Check-out: %1$s, until %2$s', 'mphb-woocommerce'), '%check_out_date%', '%check_out_time%'); ?><br>

<h4><?php _e('Customer Info', 'mphb-woocommerce'); ?></h4>
<?php printf(__('Name: %1$s %2$s', 'mphb-woocommerce'), '%customer_first_name%', '%customer_last_name%'); ?><br>
<?php printf(__('Email: %s', 'mphb-woocommerce'), '%customer_email%'); ?><br>
<?php printf(__('Phone: %s', 'mphb-woocommerce'), '%customer_phone%'); ?><br>
<?php printf(__('Note: %s', 'mphb-woocommerce'), '%customer_note%'); ?><br>

<h4><?php _e('Total Price:', 'mphb-woocommerce'); ?></h4>
%booking_total_price%<br>