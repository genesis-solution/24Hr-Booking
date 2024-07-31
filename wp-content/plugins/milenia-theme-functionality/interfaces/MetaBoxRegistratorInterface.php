<?php
/**
 * The MetaBoxRegistratorInterface interface.
 *
 * This interface describes the basic functionality of the meta box registrator instance.
 *
 * @package WordPress
 * @subpackage MileniaThemeFunctionality
 * @since MileniaThemeFunctionality 1.0.0
 */

// Prevent the direct loading of the file
if ( ! defined( 'ABSPATH' ) ) {
	die( esc_html__('You cannot access this file directly.', 'milenia-app-textdomain') );
}

if( !interface_exists('MetaBoxRegistratorInterface') )
{
    interface MetaBoxRegistratorInterface
    {
        /**
         * Registers new meta boxes.
         *
         * @param array $meta_boxes
         * @access public
         */
        public function register( array $meta_boxes = array() );
    }
}
?>
