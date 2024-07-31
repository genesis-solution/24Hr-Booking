<?php
/**
* The template file that responsible to display archive page of portfolio projects.
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

<?php if(have_posts()) : ?>
	<!-- - - - - - - - - - - - - - Portfolio - - - - - - - - - - - - - -->
	<div class="milenia-entities milenia-entities--style-17">
		<div data-isotope-layout="masonry" data-isotope-filter="#milenia-portfolio-filter" class="milenia-grid milenia-grid--isotope milenia-grid--cols-3">
			<div class="milenia-grid-sizer"></div>
			<?php
				$milenia_loop_counter = 0;
			 	while(have_posts()) {
					the_post();

					if($milenia_loop_counter % 2 == 0) {
						set_query_var('milenia-item-thumb-size', 'entity-thumb-size-vertical-rectangle');
					}
					else {
						set_query_var('milenia-item-thumb-size', 'entity-thumb-size-rectangle');
					}

					get_template_part('template-parts/milenia-portfolio/milenia-portfolio', 'post');

					$milenia_loop_counter++;
				}
			?>
		</div>
	</div>
	<!-- - - - - - - - - - - - - - End of Portfolio - - - - - - - - - - - - - -->

	<!-- - - - - - - - - - - - - - Pagination - - - - - - - - - - - - - -->
	<footer class="text-center">
		<?php milenia_pagination(array(
			'end_size' => 1
		), array('milenia-pagination--justified')); ?>
	</footer>
	<!-- - - - - - - - - - - - - - End of Pagination - - - - - - - - - - - - - -->
<?php else : ?>
    <?php get_template_part('template-parts/content', 'none'); ?>
<?php endif; ?>
