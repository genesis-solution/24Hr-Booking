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

// load header.php
get_header();

$milenia_single_post_type = !empty(get_query_var('post_type')) ? get_query_var('post_type') : 'post';
$milenia_handler_path = sprintf('template-parts/%s/single-%1$s.php', $milenia_single_post_type);

if(is_file(get_theme_file_path($milenia_handler_path)) && is_readable(get_theme_file_path($milenia_handler_path))) {
	get_template_part(sprintf('template-parts/%s/single-%1$s', $milenia_single_post_type));
}
else {
	get_template_part('template-parts/post/single', 'post');
}


// load footer.php
get_footer();
?>
