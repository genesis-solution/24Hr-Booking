<?php
/**
* The MileniaContactForm7Config class.
*
* Tunes the "Contact form 7" plugin for the theme.
*
* @package WordPress
* @subpackage Milenia
* @since Milenia 1.0
*/

// Prevent the direct loading of the file
if ( ! defined( 'ABSPATH' ) ) {
	die( esc_html__('You cannot access this file directly', 'milenia') );
}

if(!class_exists('MileniaContactForm7Config')) {
    class MileniaContactForm7Config
    {
        /**
         * The class constructor.
         */
        public function __construct()
        {
			if(!defined('WPCF7_VERSION')) return;

            if (!is_admin()) {
				add_action('wp_enqueue_scripts', array(&$this, 'frontendAssets'), 1);
			}
        }

        /**
         * Manages frontend assets.
         *
         * @access public
         * @return void
         */
        public function frontendAssets()
        {
			wp_deregister_style('contact-form-7');
			wp_register_style('contact-form-7',  get_template_directory_uri() . '/includes/config-contact-form-7/assets/css/style.css', null, WPCF7_VERSION);
		}
    }

    new MileniaContactForm7Config();
}


?>
