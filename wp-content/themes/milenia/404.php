<?php
/**
* The template file that responsible to display an 404 error page.
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

$milenia_404_error_text = $Milenia->getThemeOption('milenia-404-error-text', esc_html__("We're sorry, but we can't find the page you were looking for.", 'milenia'));
$milenia_404_error_description = $Milenia->getThemeOption('milenia-404-error-description', esc_html__("It's probably some thing we've done wrong but now we know about it and we'll try to fix it.", 'milenia'));

get_header(); ?>

<!-- - - - - - - - - - - - - - Content Section - - - - - - - - - - - - - -->
<div class="milenia-section milenia-colorizer--scheme-lightest milenia-section--stretched text-center">
	<div class="milenia-404-content milenia-color--black">
		<h1 class="milenia-404-title">404</h1>
		<?php if(!empty($milenia_404_error_text)) : ?>
			<strong class="milenia-404-message"><?php echo esc_html($milenia_404_error_text); ?></strong>
		<?php endif; ?>

		<?php if(!empty($milenia_404_error_description)) : ?>
			<p><?php echo esc_html($milenia_404_error_description); ?></p>
		<?php endif; ?>

		<p><?php echo sprintf(esc_html__('Go %s or try to search:', 'milenia'), sprintf('<a href="%s">%s</a>', esc_url(home_url('/')), esc_html__('Home', 'milenia'))); ?></p>
	</div>

	<div class="milenia-searchform--btn-dark">
		<?php get_search_form(); ?>
	</div>
</div>
<!-- - - - - - - - - - - - - - End of Content Section - - - - - - - - - - - - - -->

<?php get_footer(); ?>
