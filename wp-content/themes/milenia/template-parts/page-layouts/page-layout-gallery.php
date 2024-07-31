<?php
/**
* The template file that displays gallery page.
*
* @package WordPress
* @subpackage Milenia
* @since Milenia 1.0
*/

// Prevent the direct loading of the file
if (!defined( 'ABSPATH' ))
{
	die(esc_html__('You cannot access this file directly', 'milenia'));
}

if(!class_exists('MileniaGalleryRepository'))
{
	get_template_part('template-parts/content', 'none');
}

global $Milenia;
$MileniaHelper = $Milenia->helper();

$milenia_query_cats = $Milenia->getThemeOption('milenia-page-gallery-categories');
$milenia_query_paged = max(get_query_var('paged'), 1);
$milenia_query_limit = $Milenia->getThemeOption('milenia-page-items-per-page', get_option('posts_per_page'));
$milenia_query_order = $Milenia->getThemeOption('milenia-page-sort-order', 'desc');
$milenia_page_columns = $Milenia->getThemeOption('milenia-page-columns-individual', 'milenia-grid--cols-4');
$milenia_query_exclude = $Milenia->getThemeOption('milenia-page-gallery-out', array());
$milenia_query_include = $Milenia->getThemeOption('milenia-page-gallery-in', array());
$milenia_items_layout = $Milenia->getThemeOption('milenia-page-items-layout', 'grid');
$milenia_filter = $Milenia->getThemeOption('milenia-page-filter-state', '1');
$milenia_filter_all_tab_text = $Milenia->getThemeOption('milenia-page-filter-all-tab-text', 'All');

$milenia_grid_classes = array($milenia_page_columns);

$MileniaPostsRepository = new MileniaGalleryRepository();
$milenia_items = $MileniaPostsRepository->fromCategories($milenia_query_cats, 'milenia-gallery-categories')
										->in($milenia_query_include)
										->out($milenia_query_exclude)
										->order($milenia_query_order)
										->offset(($milenia_query_paged - 1) * intval($milenia_query_limit))
										->limit($milenia_query_limit)
										->get();
?>

<div class="milenia-entity-content">
    <?php if(is_array($milenia_items) && !empty($milenia_items)) : ?>
		<?php if($milenia_filter == '1') : ?>
			<!-- - - - - - - - - - - - - - Filter - - - - - - - - - - - - - - - - -->
			<?php milenia_display_filter(
					$milenia_items,
					'milenia-gallery-filter',
					esc_html($milenia_filter_all_tab_text),
					'milenia-gallery-categories',
					'parent_gallery_id'
			); ?>
			<!-- - - - - - - - - - - - - - End of Filter - - - - - - - - - - - - - - - - -->
		<?php endif; ?>


        <!-- - - - - - - - - - - - - - Gallery - - - - - - - - - - - - - -->
        <div class="milenia-gallery">
            <div class="milenia-grid milenia-grid--isotope <?php echo esc_attr($MileniaHelper->getSanitizedHtmlClasses($milenia_grid_classes)); ?>"
                 data-isotope-layout="<?php echo esc_attr($milenia_items_layout); ?>"
				 data-isotope-filter="#milenia-gallery-filter">
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
				'current' => $milenia_query_paged
			), array('milenia-pagination--justified')); ?>
		</footer>
		<!-- - - - - - - - - - - - - - End of Pagination - - - - - - - - - - - - - -->
    <?php else : ?>
        <?php get_template_part('template-parts/content', 'none'); ?>
    <?php endif; ?>
</div>
