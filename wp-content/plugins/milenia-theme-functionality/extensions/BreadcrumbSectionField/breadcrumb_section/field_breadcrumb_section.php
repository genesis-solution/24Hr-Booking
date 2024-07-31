<?php
/**
 * Redux Framework is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 2 of the License, or
 * any later version.
 *
 * Redux Framework is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Redux Framework. If not, see <http://www.gnu.org/licenses/>.
 *
 * @package     ReduxFramework
 * @author      Dovy Paukstys
 * @version     3.1.5
 */
// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;
// Don't duplicate me!
if( !class_exists( 'ReduxFramework_breadcrumb_section' ) )
{
    /**
     * Main ReduxFramework_breadcrumb_section class
     *
     * @since       1.0.0
     */
    class ReduxFramework_breadcrumb_section
    {

        /**
         * Contains amount of grid rows in the admin panel.
         *
         * @access protected
         * @var int
         */
        protected $rows_count = 5;

        /**
         * Field Constructor.
         *
         * Required - must call the parent constructor, then assign field and value to vars, and obviously call the render field function
         *
         * @since       1.0.0
         * @access      public
         * @return      void
         */
        function __construct( $field = array(), $value ='', $parent )
        {
            $this->parent = $parent;
            $this->field = $field;
            $this->value = $value;
            if ( empty( $this->extension_dir ) ) {
                $this->extension_dir = trailingslashit( str_replace( '\\', '/', dirname( __FILE__ ) ) );
                $this->extension_url = site_url( str_replace( trailingslashit( str_replace( '\\', '/', ABSPATH ) ), '', $this->extension_dir ) );
            }
            // Set default args for this field to avoid bad indexes. Change this to anything you use.
            $defaults = array(
                'options'           => array(),
                'stylesheet'        => '',
                'output'            => true,
                'enqueue'           => true,
                'enqueue_frontend'  => true
            );
            $this->field = wp_parse_args( $this->field, $defaults );

            if(empty($this->value) && !empty($this->field['options'])) {
                $this->value = $this->field['options'];
            }

        }
        /**
         * Field Render Function.
         *
         * Takes the vars and outputs the HTML for the field in the settings
         *
         * @since       1.0.0
         * @access      public
         * @return      void
         */
        public function render()
        {

            global $milenia_settings;

            if(!is_array($this->value)) $this->value = array();

            // fill the value array by default parameters
            $this->value = array_merge(array(
                'page-title' => esc_html__('Page Title', 'milenia-app-textdomain'),
                'breadcrumb-path' => esc_html__('Home, Events, Single Event Page', 'milenia-app-textdomain'),
                'breadcrumb-path-delimiter' => '/',
                'content-alignment' => 'text-center',
                'background-color' => '#f1f1f1',
                'title-color' => '#1c1c1c',
                'page-title-bottom-offset' => 20,
                'content-color' => '#858585',
                'links-color' => '#1c1c1c',
                'background-image' => 'none',
                'background-image-url' => 'none',
                'background-image-opacity' => 1,
                'padding-top' => 40,
                'padding-right' => 15,
                'padding-bottom' => 40,
                'padding-left' => 15
            ), $this->value);

            if(isset($milenia_settings) && function_exists('milenia_google_fonts_url'))
            {
        		$fonts_charsets = isset($milenia_settings['milenia-google-charsets']) && !empty($milenia_settings['milenia-google-charsets']) ? $milenia_settings['milenia-google-charsets'] : array('latin');

        		wp_enqueue_style('milenia-google-fonts', milenia_google_fonts_url(array(
                    $milenia_settings['h1-font']['font-family'] => array($milenia_settings['h1-font']['font-weight'])
                ), $fonts_charsets), null, null);
            }

            include_once(dirname(__FILE__) . '/field_breadcrumb_section.tpl.php');
            include_once(dirname(__FILE__) . '/field_breadcrumb_section_init.tpl.php');

        }

        /**
         * Enqueue Function.
         *
         * If this field requires any scripts, or css define this function and register/enqueue the scripts/css
         *
         * @since       1.0.0
         * @access      public
         * @return      void
         */
        public function enqueue()
        {
            wp_enqueue_style( 'wp-color-picker' );
            wp_enqueue_script( 'wp-color-picker' );
            wp_enqueue_media();

            wp_enqueue_script(
                'redux-field-breadcrumb-section-vue-lib',
                $this->extension_url . 'assets/vendors/vue.min.js',
                null,
                time(),
                true
            );

            wp_enqueue_script(
                'redux-field-breadcrumb-section-parallax-lib',
                $this->extension_url . 'assets/vendors/jquery.parallax-1.1.3.min.js',
                array('jquery'),
                time(),
                true
            );

            wp_enqueue_script(
                'jqueryui',
                $this->extension_url . 'assets/vendors/jqueryui/jquery-ui.min.js',
                array('jquery'),
                time(),
                true
            );

            wp_enqueue_script(
                'redux-field-breadcrumb-section-vue-component-js',
                $this->extension_url . 'assets/js/breadcrumb-section.vue.js',
                array( 'redux-field-breadcrumb-section-vue-lib' ),
                time(),
                true
            );

            wp_enqueue_style(
                'redux-field-breadcrumb-section-jqu-css',
                $this->extension_url . 'assets/css/breadcrumb-section.vue.css',
                time(),
                true
            );

            wp_enqueue_style(
                'jqueryui',
                $this->extension_url . 'assets/vendors/jqueryui/jquery-ui.min.css',
                time(),
                true
            );

        }

        /**
         * Output Function.
         *
         * Used to enqueue to the front-end
         *
         * @since       1.0.0
         * @access      public
         * @return      void
         */
        public function output()
        {
            if ( $this->field['enqueue_frontend'] ) {
            }
        }

    }
}
