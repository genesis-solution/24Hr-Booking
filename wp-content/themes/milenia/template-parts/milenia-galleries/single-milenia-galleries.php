<?php
/**
* The template that is responsible to display a single gallery page.
*
* @package WordPress
* @subpackage Milenia
* @since Milenia 1.0
*/

// Prevent the direct loading of the file
if ( ! defined( 'ABSPATH' ) )
{
	die( esc_html__('You cannot access this file directly', 'milenia') );
}

if(!class_exists('MileniaGalleryRepository'))
{
	get_template_part('template-parts/content', 'none');
}

global $Milenia;
$MileniaHelper = $Milenia->helper();

if(have_posts()) :
	while(have_posts()) :
		the_post();

		$milenia_gallery_builder = get_post_meta(get_the_ID(), 'milenia_gallery_builder', true);
		$milenia_query_paged = intval(isset($_GET['pagenum']) ? $_GET['pagenum'] : 1);
		$milenia_query_limit = intval(get_option('posts_per_page'));

		$MileniaPostsRepository = new MileniaGalleryRepository();

		if(is_array($milenia_gallery_builder) && isset($milenia_gallery_builder['sliders']) && is_array($milenia_gallery_builder['sliders'])) {
			$milenia_layouts_whitelist = array('grid', 'masonry');
			$milenia_columns_whitelist = array('milenia-grid--cols-4', 'milenia-grid--cols-3', 'milenia-grid--cols-2');

			// Filtering the options
			if(!isset($milenia_gallery_builder['sliders']['single-page-layout']) || !in_array($milenia_gallery_builder['sliders']['single-page-layout'], $milenia_layouts_whitelist)) {
				$milenia_gallery_builder['sliders']['single-page-layout'] = 'grid';
			}

			if(!isset($milenia_gallery_builder['sliders']['single-page-columns']) || !in_array($milenia_gallery_builder['sliders']['single-page-columns'], $milenia_columns_whitelist)) {
				$milenia_gallery_builder['sliders']['single-page-columns'] = 'milenia-grid--cols-4';
			}

			if(isset($milenia_gallery_builder['sliders']['single-page-items-per-page'])) {
				$milenia_query_limit = intval($milenia_gallery_builder['sliders']['single-page-items-per-page']);
			}
		}

		$milenia_items = $MileniaPostsRepository->in(array(get_the_ID()))
												->offset(($milenia_query_paged - 1) * $milenia_query_limit)
												->limit($milenia_query_limit)
												->get();
		?>
			<div class="milenia-entity-content">
				<?php if(is_array($milenia_items) && !empty($milenia_items)) : ?>
					<!-- - - - - - - - - - - - - - Gallery - - - - - - - - - - - - - -->
					<div class="milenia-gallery">
						<div class="milenia-grid milenia-grid--isotope <?php echo esc_attr($milenia_gallery_builder['sliders']['single-page-columns']); ?>"
							 data-isotope-layout="<?php echo esc_attr($milenia_gallery_builder['sliders']['single-page-layout']); ?>">
							 <div class="milenia-grid-sizer"></div>
							<?php foreach($milenia_items as $item) : set_query_var('milenia-gallery-item', serialize($item)); ?>
								<?php get_template_part('template-parts/milenia-galleries/milenia-galleries', 'post'); ?>
							<?php endforeach; ?>
						</div>
					</div>
					<!-- - - - - - - - - - - - - - End of Gallery - - - - - - - - - - - - - -->
					<!-- - - - - - - - - - - - - - Pagination - - - - - - - - - - - - - -->
					<footer class="text-center">
						<?php milenia_pagination(array(
							'total'   => ceil( count($MileniaPostsRepository) / $milenia_query_limit ),
							'current' => $milenia_query_paged,
							'format' => '?pagenum=%#%',
				            'prev_next' => false,
				            'end_size' => 1
						), array('milenia-pagination--justified')); ?>
					</footer>
					<!-- - - - - - - - - - - - - - End of Pagination - - - - - - - - - - - - - -->
				<?php else : ?>
					<?php get_template_part('template-parts/content', 'none'); ?>
				<?php endif; ?>
			</div>
		<?php
	endwhile;
else :
	get_template_part('template-parts/content', 'none');
endif; ?>
