<?php

/*
 * Plugin Name: Hotel Booking PDF Invoices
 * Plugin URI: https://motopress.com/products/hotel-booking-invoices/
 * Description: Send automated PDF invoices with the booking and payment details to your guests.
 * Version: 1.3.0
 * Author: MotoPress
 * Author URI: https://motopress.com/
 * License: GPLv2 or later
 * Text Domain: mphb-invoices
 * Domain Path: /languages
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( version_compare( PHP_VERSION, '7.1', '<' ) ) {

	// Don't show multiple duplicate notices when multiple instances of the
	// plugin are active
	if ( ! function_exists( 'mphb_invoice_php_version_error_notice' ) ) {
		add_action( 'init', 'mphb_invoice_load_translations' );
		add_action( 'admin_notices', 'mphb_invoice_php_version_error_notice' );
		function mphb_invoice_load_translations() {
			$pluginDir = plugin_dir_path( __FILE__ );
			$pluginDir = plugin_basename( $pluginDir ); // "mphb-invoices" or renamed name

			load_plugin_textdomain( 'mphb-invoices', false, $pluginDir . '/languages' );
		}
		function mphb_invoice_php_version_error_notice() {
			echo '<div class="error"><p>' . esc_html__( 'Your version of PHP is below the minimum version of PHP required by Hotel Booking Invoices Addon. Please contact your host and request that your version be upgraded to 7.1 or later.', 'mphb-invoices' ) . '</p></div>';
		}
	}

} elseif ( ! class_exists( '\MPHB\Addons\Invoice\Plugin' ) ) {

	define( 'MPHB\Addons\Invoice\PLUGIN_FILE', __FILE__ );
	define( 'MPHB\Addons\Invoice\PLUGIN_DIR', plugin_dir_path( __FILE__ ) ); // With trailing slash
	define( 'MPHB\Addons\Invoice\PLUGIN_URL', plugin_dir_url( __FILE__ ) ); // With trailing slash
	define( 'MPHB_INVOICE_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );

	include_once __DIR__ . '/includes/mphb-invoice-dependency.php';
	$mphb_invoice_dependency = new MPHB_Invoice_Dependencies();

	if ( ! function_exists( 'is_plugin_active' ) || ! $mphb_invoice_dependency->check() ) {
		return;
	}

	if ( ! class_exists( 'HTML5_Parser' ) ) {
		include_once __DIR__ . '/vendors/dompdf/autoload.inc.php';
	}
    
	require __DIR__ . '/includes/functions.php';
	require __DIR__ . '/includes/autoloader.php';
	mphbinvoice();
	require __DIR__ . '/includes/actions-and-filters.php';
}
