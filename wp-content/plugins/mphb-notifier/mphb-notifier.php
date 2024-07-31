<?php

/*
 * Plugin Name: Hotel Booking Notifier - Event-driven emails
 * Plugin URI: https://motopress.com/products/hotel-booking-notifier/
 * Description: Automate common notifications by sending check-in and check-out date-driven emails, such as key pick-up instructions, feedback request, etc.
 * Version: 1.3.2
 * Requires at least: 5.1
 * Requires PHP: 7.0
 * Author: MotoPress
 * Author URI: https://motopress.com/
 * License: GPLv2 or later
 * Text Domain: mphb-notifier
 * Domain Path: /languages
 */

if (!defined('ABSPATH')) {
    exit;
}

add_action( 'plugins_loaded', function() {

    load_plugin_textdomain(
        'mphb-notifier',
        false,
        plugin_basename( plugin_dir_path( __FILE__ ) ) . '/languages'
    );
}, 1 );


// Don't register duplicate classes (with multiple builds active)
if (!class_exists('\MPHB\Notifier\Plugin')) {
    define('MPHB\Notifier\PLUGIN_FILE', __FILE__);
    define('MPHB\Notifier\PLUGIN_DIR', plugin_dir_path(__FILE__)); // With trailing slash
    define('MPHB\Notifier\PLUGIN_URL', plugin_dir_url(__FILE__)); // With trailing slash

    define('MPHB\Notifier\PLUGIN_STORE_URI', 'https://motopress.com/products/hotel-booking-notifier/');
    define('MPHB\Notifier\PLUGIN_VERSION', '1.3.2');
    define('MPHB\Notifier\PLUGIN_AUTHOR', 'MotoPress');

    require __DIR__ . '/includes/functions.php';
    require __DIR__ . '/includes/template-functions.php';
    require __DIR__ . '/includes/autoloader.php';
    require __DIR__ . '/includes/actions-and-filters.php';

    mphb_notifier()->setup();
}
