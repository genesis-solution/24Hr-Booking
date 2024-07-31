<?php
/**
* The template file that responsible to display archive page of testimonials.
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

<div class="row">
	<div class="col-lg-8 offset-lg-2">
		<?php if(have_posts()) : ?>
		    <!-- - - - - - - - - - - - - - Main Content - - - - - - - - - - - - - - - - -->
		    <main class="milenia-section">
		        <!-- - - - - - - - - - - - - - Testimonials - - - - - - - - - - - - - - - - -->
		        <div class="milenia-testimonials milenia-testimonials--style-1">
					<div class="milenia-grid milenia-grid--cols-1">
			            <?php while(have_posts()) : the_post(); ?>
			                <?php get_template_part('template-parts/milenia-testimonials/milenia-testimonials', 'post'); ?>
			            <?php endwhile; ?>
					</div>
		        </div>
		        <!-- - - - - - - - - - - - - - End of Testimonials - - - - - - - - - - - - - - - - -->

				<div class="text-center">
					<?php milenia_pagination(array( 'prev_next' => false, 'end_size' => 1), array('milenia-pagination--justified')); ?>
				</div>
		    </main>
		    <!-- - - - - - - - - - - - - - End of Main Content - - - - - - - - - - - - - - - - -->
		<?php else : ?>
		    <?php get_template_part('template-parts/content', 'none'); ?>
		<?php endif; ?>
	</div>
</div>
