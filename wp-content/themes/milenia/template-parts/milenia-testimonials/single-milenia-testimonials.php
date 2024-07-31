<?php
/**
* The template for displaying a single team member page.
*
* @package WordPress
* @subpackage Milenia
* @since Milenia 1.0
*/

// Prevent the direct loading of the file
if ( ! defined( 'ABSPATH' ) ) {
	die( esc_html__('You cannot access this file directly', 'milenia') );
}

get_header(); ?>

<div class="milenia-section">
	<div class="row">
		<div class="col-lg-8 offset-lg-2">
			<?php if(have_posts()) : ?>
		        <?php while( have_posts() ) : the_post(); ?>
		            <!-- - - - - - - - - - - - - - Main Content - - - - - - - - - - - - - - - - -->
		    		<main class="milenia-section milenia-section-thin">
		                <div class="milenia-section milenia-section--py-medium">
		                    <div class="milenia-testimonials milenia-testimonials--style-1">
		                        <?php get_template_part('template-parts/milenia-testimonials/milenia-testimonials', 'post'); ?>
								<?php wp_link_pages( array(
									'before' => '<div class="milenia-page-links"><span class="milenia-page-links-title">' . esc_html__( 'Pages:', 'milenia' ) . '</span>',
									'after' => '</div>',
									'link_before' => '<span>',
									'link_after' => '</span>'
								) ); ?>
		                    </div>
		                </div>

						<div class="milenia-section text-center">
							<a href="<?php echo esc_url( get_post_type_archive_link( get_post_type() ) ); ?>" class="milenia-btn" role="button"><?php esc_html_e('View all testimonials', 'milenia'); ?></a>
						</div>

						<!-- - - - - - - - - - - - - - Posts Navgation - - - - - - - - - - - - - -->
			            <nav class="milenia-section">
			                <ul class="milenia-list--unstyled milenia-posts-navigation">
			                    <?php previous_post_link('<li class="milenia-posts-navigation-prev"><span>%link</span></li>'); ?>
			                    <?php next_post_link('<li class="milenia-posts-navigation-next"><span>%link</span></li>'); ?>
			                </ul>
			            </nav>
			            <!-- - - - - - - - - - - - - - End of Posts Navigation - - - - - - - - - - - - - -->

		                <?php if(comments_open()) : ?>
		                    <?php comments_template(); ?>
		                <?php endif; ?>
		    	    </main>
		            <!-- - - - - - - - - - - - - - End of Main Content - - - - - - - - - - - - - - - - -->
		        <?php endwhile; ?>
			<?php else : ?>
				<?php get_template_part('template-parts/content', 'none'); ?>
			<?php endif; ?>
		</div>
	</div>
</div>
<?php get_footer(); ?>
