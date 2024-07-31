<?php
/**
 * The MileniaMetaBoxRegistrator class.
 *
 * This class is responsible to register new meta boxes.
 *
 * @package WordPress
 * @subpackage MileniaThemeFunctionality
 * @since MileniaThemeFunctionality 1.0.0
 */

// Prevent the direct loading of the file
if ( ! defined( 'ABSPATH' ) ) {
	die( esc_html__('You cannot access this file directly.', 'milenia-app-textdomain') );
}

if( !class_exists('MileniaMetaBoxRegistrator') )
{
    class MileniaMetaBoxRegistrator implements MetaBoxRegistratorInterface
    {
        /**
         * Contains all necessary meta boxes.
         *
         * @access protected
         * @var array
         */
        protected $meta_boxes = array();

        /**
         * The class constructor.
         */
        public function __construct()
        {
            add_filter('rwmb_meta_boxes', array(&$this, 'registerMetaBoxes'));
        }

        /**
         * Registers new meta boxes in the class.
         *
         * @param array $meta_boxes
         * @access public
         */
        public function register( array $meta_boxes = array() )
        {
            $this->meta_boxes = array_merge($this->meta_boxes, $meta_boxes);
        }

        /**
         * Registers new meta boxes in the WordPress system.
         *
         * @param array $meta_boxes
         * @access public
         * @return array
         */
        public function registerMetaBoxes( $meta_boxes )
        {
            $meta_boxes = array_merge($meta_boxes, $this->meta_boxes);

            return $meta_boxes;
        }
    }
}
?>
