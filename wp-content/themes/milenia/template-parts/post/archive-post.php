<?php
/**
* The template file that responsible to display archive page of blog posts.
*
* @package WordPress
* @subpackage Milenia
* @since Milenia 1.0
*/

// Prevent the direct loading of the file
if ( ! defined( 'ABSPATH' ) ) {
	die( esc_html__('You cannot access this file directly', 'milenia') );
}

global $Milenia, $MileniaLayout;
$MileniaHelper = $Milenia->helper();

$milenia_post_archive_style = $Milenia->getThemeOption('milenia-post-archive-style', 'milenia-entities--style-7');
$milenia_post_archive_columns = $Milenia->getThemeOption('milenia-post-archive-columns', 'milenia-grid--cols-1');
$milenia_post_archive_isotope_layout = $Milenia->getThemeOption('milenia-post-archive-isotope-layout', 'grid');

$milenia_entities_container_classes = explode(' ', $milenia_post_archive_style);
$milenia_entities_grid_classes = array($milenia_post_archive_columns);

set_query_var('milenia-post-archive-isotope-layout', $milenia_post_archive_isotope_layout);

?>
<div class="milenia-section">
	<div class="row">
		<!-- - - - - - - - - - - - - - Main Content Column - - - - - - - - - - - - - - - - -->
		<main class="<?php echo esc_attr($MileniaLayout->getMainLayoutClasses('main')); ?>">
			<?php if ( have_posts() ) : ?>
				<div class="milenia-section">
			        <!-- - - - - - - - - - - - - - Isotope Container - - - - - - - - - - - - - - - - -->
			        <div class="milenia-entities <?php echo esc_attr($MileniaHelper->getSanitizedHtmlClasses($milenia_entities_container_classes)); ?>">
						<div class="milenia-grid milenia-grid--isotope <?php echo esc_attr($MileniaHelper->getSanitizedHtmlClasses($milenia_entities_grid_classes)); ?>"
							data-isotope-layout="<?php echo esc_attr( $milenia_post_archive_isotope_layout ); ?>"
							data-items-per-page="<?php echo esc_attr( get_option('posts_per_page') ); ?>">
				            <?php
				                while( have_posts() ) : the_post();
									set_query_var('milenia-post-archive-style', $milenia_post_archive_style);
				                    get_template_part('template-parts/post/post');
				                endwhile;
				            ?>
							<div class="milenia-grid-sizer"></div>
						</div>
			        </div>
			        <!-- - - - - - - - - - - - - - End of Isotope Container - - - - - - - - - - - - - - - - -->
			    </div>

				<!-- - - - - - - - - - - - - - Pagination - - - - - - - - - - - - - - - - -->
				<?php milenia_pagination(array( 'end_size' => 1 ), array('milenia-pagination--justified')); ?>
				<!-- - - - - - - - - - - - - - End of Pagination - - - - - - - - - - - - - - - - -->
			<?php else :
			    get_template_part('template-parts/content', 'none');
			endif;?>
		</main>
		<!-- - - - - - - - - - - - - - End of Main Content Column - - - - - - - - - - - - - - - - -->

		<?php if($MileniaLayout->hasSidebar()) : ?>
			<!-- - - - - - - - - - - - - - Sidebar - - - - - - - - - - - - - - - - -->
			<aside class="milenia-sidebar <?php echo esc_attr($MileniaLayout->getMainLayoutClasses('side')); ?>" id="milenia-sidebar">
				<?php get_sidebar(); ?>
			</aside>
			<!-- - - - - - - - - - - - - - End of Sidebar - - - - - - - - - - - - - - - - -->
		<?php endif; ?>
	</div>
</div>
