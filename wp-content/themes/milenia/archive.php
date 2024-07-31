<?php
/**
* The template file that responsible to display an archive page.
*
* @package WordPress
* @subpackage Milenia
* @since Milenia 1.0
*/

// Prevent the direct loading of the file
if ( ! defined( 'ABSPATH' ) ) {
	die( esc_html__('You cannot access this file directly', 'milenia') );
}

$milenia_archive_post_type = !empty(get_query_var('post_type')) ? get_query_var('post_type') : 'post';

if( is_tax('milenia-tm-categories') || is_post_type_archive('milenia-team-members') )
{
	$milenia_archive_post_type = 'milenia-team-members';
}
elseif( is_tax('milenia-portfolio-categories') || is_tax('milenia-portfolio-tags') || is_post_type_archive('milenia-portfolio') )
{
	$milenia_archive_post_type = 'milenia-portfolio';
}
elseif( is_tax('milenia-gallery-categories') || is_post_type_archive('milenia-galleries') )
{
	$milenia_archive_post_type = 'milenia-galleries';
}
elseif( is_tax('milenia-offers-categories') || is_post_type_archive('milenia-offers') )
{
	$milenia_archive_post_type = 'milenia-offers';
}
elseif( is_tax('mphb_room_type_category') || is_tax('mphb_room_type_category') || is_post_type_archive('mphb_room_type') )
{
	$milenia_archive_post_type = 'milenia-rooms';
}

get_header();

get_template_part(sprintf('template-parts/%s/archive-%1$s', $milenia_archive_post_type));

get_footer();
?>
