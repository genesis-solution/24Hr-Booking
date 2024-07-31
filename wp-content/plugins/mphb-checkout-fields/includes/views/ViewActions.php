<?php

namespace MPHB\CheckoutFields\Views;

use MPHB\CheckoutFields\CheckoutFieldsHelper;
use MPHB\CheckoutFields\Plugin;

/**
 * @since 1.0
 */
class ViewActions {

	public function __construct() {

		// After CheckoutStep::setup() of CheckoutShortcode
		add_action( 'init', array( $this, 'replaceCheckoutFields' ), 11 );

		// After the load of Add New Booking page
		add_action( 'load-admin_page_mphb_add_new_booking', array( $this, 'replaceAdminCheckoutFields' ), 11 );

		// Load field controls scripts on Checkout Page
		add_action( 'mphb_enqueue_checkout_scripts', array( $this, 'enqueueCheckoutScripts' ) );
	}

	public function replaceCheckoutFields() {

		remove_action( 'mphb_sc_checkout_form', array( '\MPHB\Views\Shortcodes\CheckoutView', 'renderCustomerDetails' ), 40 );
		add_action( 'mphb_sc_checkout_form', array( '\MPHB\CheckoutFields\Views\CheckoutView', 'renderCustomerDetails' ), 40, 3 );
	}

	public function replaceAdminCheckoutFields() {

		remove_action( 'mphb_cb_checkout_form', array( '\MPHB\Views\Shortcodes\CheckoutView', 'renderCustomerDetails' ), 40 );
		add_action( 'mphb_cb_checkout_form', array( '\MPHB\CheckoutFields\Views\CheckoutView', 'renderCustomerDetails' ), 40, 3 );
	}

	public function enqueueCheckoutScripts() {

		wp_enqueue_script(
			'mphb-cf-date-of-birth',
			CheckoutFieldsHelper::getUrlToFile( 'assets/js/date-of-birth-control.js' ),
			array( 'jquery' ),
			Plugin::getInstance()->getPluginVersion(),
			true
		);

		wp_enqueue_style(
			'mphb-cf-checkout-styles',
			CheckoutFieldsHelper::getUrlToFile( 'assets/css/checkout-page.css' ),
			array(),
			Plugin::getInstance()->getPluginVersion()
		);
	}
}
