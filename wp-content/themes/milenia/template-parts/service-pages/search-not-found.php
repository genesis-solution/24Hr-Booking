<?php
/**
* The template file that is responsible to display message on the search
* results page in case when nothing was found.
*
* @package WordPress
* @subpackage Milenia
* @since Milenia 1.0
*/

// Prevent the direct loading of the file
if ( ! defined( 'ABSPATH' ) ) {
    die( esc_html__('You cannot access this file directly', 'milenia') );
}
?>

<!-- - - - - - - - - - - - - - Content Section - - - - - - - - - - - - - -->
<div class="milenia-section text-center">
    <div class="row">
        <main class="col-md-8 offset-md-2">
            <h1><?php esc_html_e('Nothing found', 'milenia'); ?></h1>

            <p><?php esc_html_e('Nothing found on your request.', 'milenia'); ?> <?php echo sprintf(esc_html__('Go %s or try to search again:', 'milenia'), sprintf('<a href="%s">%s</a>', esc_url(home_url('/')), esc_html__('Home', 'milenia'))); ?></p>

            <?php get_search_form(); ?>
        </main>
    </div>
</div>
<!-- - - - - - - - - - - - - - End of Content Section - - - - - - - - - - - - - -->
