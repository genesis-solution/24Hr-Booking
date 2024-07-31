<?php

/*
 * Plugin Name: Hotel Booking WooCommerce Payments
 * Plugin URI: https://motopress.com/products/hotel-booking-woocommerce-payments/
 * Description: Use WooCommerce payment gateways to rent out your accommodations.
 * Version: 1.0.10
 * Requires at least: 4.7
 * Requires PHP: 5.4
 * Author: MotoPress
 * Author URI: https://motopress.com/
 * License: GPLv2 or later
 * Text Domain: mphb-woocommerce
 * Domain Path: /languages
 */

require_once dirname( __FILE__ ) . '/includes/autoloader.php';
require_once dirname( __FILE__ ) . '/functions.php';

new \MPHBW\Autoloader( 'MPHBW\\', trailingslashit( dirname( __FILE__ ) . '/includes' ) );

\MPHBW\Plugin::setBaseFilepath( __FILE__ );

// Init
\MPHBW\Plugin::getInstance();
