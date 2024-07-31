<?php

/*
 * Plugin Name: Hotel Booking Checkout Fields
 * Plugin URI: https://motopress.com/products/hotel-booking-checkout-fields/
 * Description: Edit existing or add new checkout fields in Hotel Booking to collect more information about your guests.
 * Version: 1.2.1
 * Requires at least: 5.1
 * Requires PHP: 7.1
 * Author: MotoPress
 * Author URI: https://motopress.com/
 * License: GPLv2 or later
 * Text Domain: mphb-checkout-fields
 * Domain Path: /languages
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Don't register duplicate classes (with multiple builds active)
if ( ! class_exists( '\MPHB\CheckoutFields\Plugin' ) ) {

	define( 'MPHB\CheckoutFields\PLUGIN_FILE', __FILE__ );
	define( 'MPHB\CheckoutFields\PLUGIN_DIR', plugin_dir_path( __FILE__ ) ); // With trailing slash
	define( 'MPHB\CheckoutFields\PLUGIN_URL', plugin_dir_url( __FILE__ ) ); // With trailing slash

	define( 'MPHB\CheckoutFields\STORE_URI', 'https://motopress.com/products/hotel-booking-checkout-fields/' );
	define( 'MPHB\CheckoutFields\VERSION', '1.2.1' );
	define( 'MPHB\CheckoutFields\AUTHOR', 'MotoPress' );

	require_once __DIR__ . '/includes/autoloader.php';

    // init plugin
	\MPHB\CheckoutFields\Plugin::getInstance();
}
