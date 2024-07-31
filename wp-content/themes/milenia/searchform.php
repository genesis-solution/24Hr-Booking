<?php
// Prevent the direct loading of the file
if ( ! defined( 'ABSPATH' ) ) {
	die( esc_html__('You cannot access this file directly', 'milenia') );
}

// For the loading through AJAX
if(wp_doing_ajax()) {
	$milenia_query_data = $_GET['data'];
	$milenia_search_query = isset($milenia_query_data['search_query']) ? stripslashes(strip_tags($milenia_query_data['search_query'])) : '';
}

?>
<!-- - - - - - - - - - - - - - Searchform - - - - - - - - - - - - - - - - -->
<form role="search" class="milenia-singlefield-form milenia-form--fields-white milenia-searchform" method="get" name="searchform" action="<?php echo esc_url( home_url( '/' ) ); ?>">
    <input type="search" name="s" placeholder="<?php esc_attr_e('Search...', 'milenia'); ?>" value="<?php echo (wp_doing_ajax() && isset($milenia_search_query)) ? esc_attr($milenia_search_query) : esc_attr(get_search_query()); ?>"/>
    <button type="submit"><span class="icon icon-magnifier"></span></button>
</form>
<!-- - - - - - - - - - - - - - End of Searchform - - - - - - - - - - - - - - - - -->
