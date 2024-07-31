<?php
/**
* The template for displaying a single post.
*
* @package WordPress
* @subpackage Milenia
* @since Milenia 1.0
*/

// Prevent the direct loading of the file
if ( ! defined( 'ABSPATH' ) ) {
	die( esc_html__('You cannot access this file directly', 'milenia') );
}

global $MileniaLayout;

get_template_part('template-parts/post/single-post', $MileniaLayout->hasSidebar() ? 'sidebar' : 'without-sidebar');
?>
