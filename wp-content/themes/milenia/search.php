<?php
/**
* The template file that is responsible to show search results.
*
* @package WordPress
* @subpackage Milenia
* @since Milenia 1.0
*/

// Prevent the direct loading of the file
if ( ! defined( 'ABSPATH' ) ) {
    die( esc_html__('You cannot access this file directly', 'milenia') );
}
// Load header
get_header();

if(have_posts()) {
    get_template_part('template-parts/post/archive', 'post');
}
else {
    get_template_part('template-parts/service-pages/search', 'not-found');
}

// Load footer
get_footer();
?>
