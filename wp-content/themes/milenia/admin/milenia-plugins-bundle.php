<?php
/**
 * This file represents code that the theme uses to register
 * the required plugins.
 *
 * @package    WordPress
 * @subpackage Milenia
 * @since Milenia 1.0
 */

// Prevent the direct loading of the file
if ( ! defined( 'ABSPATH' ) ) {
	die( esc_html__('You cannot access this file directly', 'milenia') );
}

require_once dirname(__FILE__) . '/class-tgm-plugin-activation.php';
add_action( 'tgmpa_register', 'milenia_register_required_plugins' );

/**
 * Register the required plugins for this theme.
 *
 * This function is hooked into `tgmpa_register`, which is fired on the WP `init` action on priority 10.
 */
function milenia_register_required_plugins() {

	global $Milenia;

	$theme_plugins = $Milenia->getRequiredThemePlugins();

	$plugins = array_merge($theme_plugins, array(
		array(
			'name'      => esc_html__('Breadcrumb NavXT', 'milenia'),
			'slug'      => 'breadcrumb-navxt',
			'required'  => true
		),
		array(
			'name'      => esc_html__('Contact Form 7', 'milenia'),
			'slug'      => 'contact-form-7',
			'required'  => true
		),
		array(
			'name'      => esc_html__('Meta Box', 'milenia'),
			'slug'      => 'meta-box',
			'required'  => true
		),
		array(
			'name'     => esc_html__('Smash Balloon Instagram Feed', 'milenia' ),
			'slug'     => 'instagram-feed',
			'required' => false
		),
		array(
			'name'      => esc_html__('Redux Framework', 'milenia'),
			'slug'      => 'redux-framework',
			'required'  => true
		),
		array(
			'name'      => esc_html__('The Events Calendar', 'milenia'),
			'slug'      => 'the-events-calendar',
			'required'  => true
		),
		array(
			'name' => esc_html__('MailPoet Newsletters', 'milenia'),
			'slug' => 'mailpoet',
			'required' => false
		),
		array(
			'name' => esc_html__('WooCommerce', 'milenia'),
			'slug' => 'woocommerce',
			'required' => false
		),
		array(
			'name' => esc_html__('One Click Demo Import', 'milenia'),
			'slug' => 'one-click-demo-import',
			'required' => true
		)
	));

	/*
	 * Array of configuration settings.
	 */
	$config = array(
		'id'           => 'milenia',                 // Unique ID for hashing notices for multiple instances of TGMPA.
		'default_path' => 'http://velikorodnov.com/wordpress/sample-data/milenia/', // Default absolute path to bundled plugins.
		'menu'         => 'install-required-plugins', // Menu slug.
		'has_notices'  => true,                    // Show admin notices or not.
		'dismissable'  => true,                    // If false, a user cannot dismiss the nag message.
		'dismiss_msg'  => '',                      // If 'dismissable' is false, this message will be output at top of nag.
		'is_automatic' => true,                   // Automatically activate plugins after installation or not.
		'message'      => ''
	);

	tgmpa( $plugins, $config );
}
