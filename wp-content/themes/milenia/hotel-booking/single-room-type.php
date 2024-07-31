<?php
/**
* The template file that responsible to display a certain accommodation type.
*
* @package WordPress
* @subpackage Milenia
* @since Milenia 1.0
*/

// Prevent the direct loading of the file
if ( ! defined( 'ABSPATH' ) ) {
	die( esc_html__('You cannot access this file directly', 'milenia') );
}


global $Milenia;

$room_type_layout = $Milenia->getThemeOption('accomodation-single-layout-type', 'milenia-right-sidebar');

get_header();

get_template_part('hotel-booking/single-room-type', (in_array($room_type_layout, array('milenia-right-sidebar', 'milenia-left-sidebar')) ? 'sidebar' : 'full-width'));

get_footer();
?>
