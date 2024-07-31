<?php
if (!defined('ABSPATH')) {
    exit;
}
?>
<?php printf(esc_html__('Dear %1$s %2$s,', 'mphb-notifier'), '%customer_first_name%', '%customer_last_name%'); ?>
<br /><br />
<?php esc_html_e('For your information:', 'mphb-notifier'); ?>
<br />
<?php printf(esc_html__('Accommodation Notice 1 - %s', 'mphb-notifier'), '%accommodation_notice_1%'); ?>
<br />
<?php printf(esc_html__('Accommodation Notice 2 - %s', 'mphb-notifier'), '%accommodation_notice_2%'); ?>
<h4><?php esc_html_e('Details of booking', 'mphb-notifier'); ?></h4>
<?php printf(esc_html__('Check-in: %1$s, from %2$s', 'mphb-notifier'), '%check_in_date%', '%check_in_time%'); ?>
<br />
<?php printf(esc_html__('Check-out: %1$s, until %2$s', 'mphb-notifier'), '%check_out_date%', '%check_out_time%'); ?>
<br /><br />
<?php esc_html_e('Thank you!', 'mphb-notifier'); ?>