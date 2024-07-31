<?php
// Prevent the direct loading of the file
if ( ! defined( 'ABSPATH' ) ) {
	die( esc_html__('You cannot access this file directly', 'milenia') );
}

if(!function_exists('milenia_free_navigation_links')) {
	/**
	 * Adds specific class to the links in navigation to not underline them.
	 *
	 * @param  array   $atts
	 * @param  WP_Post $item
	 * @param  array   $args
	 * @return array
	 */
    function milenia_free_navigation_links( $atts, $item, $args ) {
		if(!empty($args->theme_location) && in_array($args->theme_location, array('primary', 'hidden-sidebar-nav', 'header')))
		{
			$atts['class'] = 'milenia-ln--independent';
		}

		return $atts;
    }
}

add_filter( 'nav_menu_link_attributes', 'milenia_free_navigation_links', 10, 3 );

/**
 * Sets optimal amount of characters that will be returned by the_excerpt
 * function.
 */
if(!function_exists('milenia_modify_excerpt_length')) {
    function milenia_modify_excerpt_length( $length ) {
        return 18;
    }
}
add_filter('excerpt_length', 'milenia_modify_excerpt_length', 10);

/**
 * Modifies the excerpt 'more'.
 */
if(!function_exists('milenia_modify_excerpt_more')) {
    function milenia_modify_excerpt_more( $more ) {
        return '...';
    }
}
add_filter('excerpt_more', 'milenia_modify_excerpt_more');

if(!function_exists('milenia_ocdi_import_files')) {
	function milenia_ocdi_import_files() {
		return array(
			array(
				'import_file_name' => esc_html__('Demo Import', 'milenia'),
				'local_import_file' => trailingslashit( get_template_directory() ) . 'demo-content/demo-content.xml',
				'local_import_widget_file' => trailingslashit( get_template_directory() ) . 'demo-content/demo-widgets.wie',
				'local_import_customizer_file' => trailingslashit( get_template_directory() ) . 'demo-content/demo-customizer.dat',
			)
		);
	}
}
add_filter( 'pt-ocdi/import_files', 'milenia_ocdi_import_files' );

add_action('wp_loaded', 'milenia_buffer_start');
function milenia_buffer_start() {
    ob_start("milenia_callback");
}

add_action('shutdown', 'milenia_buffer_end');
function milenia_buffer_end() {
    if(ob_get_length() > 0) {
		ob_get_flush();
	}
}

function milenia_callback($buffer) {
    return preg_replace( "%[ ]type=[\'\"]text\/(javascript|css)[\'\"]%", '', $buffer );
}



?>
