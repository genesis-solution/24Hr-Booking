<?php
/**
* The template file for displaying a portfolio/gallery page.
*
* @package WordPress
* @subpackage Milenia
* @since Milenia 1.0
*/

// Prevent the direct loading of the file
if (!defined( 'ABSPATH' ))
{
	die( esc_html__('You cannot access this file directly', 'milenia') );
}

if(!class_exists('MileniaPostRepository'))
{
	get_template_part('template-parts/content', 'none');
}

global $Milenia;
$MileniaHelper = $Milenia->helper();

$milenia_query_cats = $Milenia->getThemeOption('milenia-page-portfolio-categories');
$milenia_query_paged = get_query_var('paged') ? get_query_var('paged') : 1;
$milenia_query_limit = $Milenia->getThemeOption('milenia-page-items-per-page', get_option('posts_per_page'));
$milenia_query_orderby = $Milenia->getThemeOption('milenia-page-order-by', 'date');
$milenia_query_order = $Milenia->getThemeOption('milenia-page-sort-order', 'desc');
$milenia_query_exclude = $Milenia->getThemeOption('milenia-page-portfolio-out', array());
$milenia_query_include = $Milenia->getThemeOption('milenia-page-portfolio-in', array());
$milenia_item_style = $Milenia->getThemeOption('milenia-page-portfolio-item-style', 'milenia-entities--style-18');
$milenia_items_layout = $Milenia->getThemeOption('milenia-page-items-layout', 'grid');
$milenia_page_columns = $Milenia->getThemeOption('milenia-page-columns-individual', 'milenia-grid--cols-3');
$milenia_item_categories = $Milenia->getThemeOption('milenia-page-portfolio-categories-state', '1');
$milenia_filter = $Milenia->getThemeOption('milenia-page-filter-state', '1');
$milenia_filter_all_tab_text = $Milenia->getThemeOption('milenia-page-filter-all-tab-text', 'All');

$milenia_container_classes = array($milenia_item_style);
$milenia_grid_classes = array($milenia_page_columns);

$MileniaPostsRepository = new MileniaPostRepository('milenia-portfolio');

$milenia_projects = $MileniaPostsRepository->fromCategories($milenia_query_cats,  'milenia-portfolio-categories')
									->in($milenia_query_include)
									->out($milenia_query_exclude)
									->orderBy($milenia_query_orderby)
									->order($milenia_query_order)
									->offset(($milenia_query_paged - 1) * intval($milenia_query_limit))
									->limit($milenia_query_limit)
									->get();

set_query_var('milenia-item-categories-state', $milenia_item_categories);
?>

<?php if(is_array($milenia_projects) && !empty($milenia_projects)) : ?>

	<?php if($milenia_filter == '1') : ?>
		<!-- - - - - - - - - - - - - - Filter - - - - - - - - - - - - - - - - -->
		<?php milenia_display_filter(
				$milenia_projects,
				'milenia-portfolio-filter',
				esc_html($milenia_filter_all_tab_text),
				'milenia-portfolio-categories'
		); ?>
		<!-- - - - - - - - - - - - - - End of Filter - - - - - - - - - - - - - - - - -->
	<?php endif; ?>

	<!-- - - - - - - - - - - - - - Portfolio - - - - - - - - - - - - - -->
	<div class="milenia-entities <?php echo esc_attr($MileniaHelper->getSanitizedHtmlClasses($milenia_container_classes)); ?>">
		<div data-isotope-layout="<?php echo esc_attr($milenia_items_layout); ?>" data-isotope-filter="#milenia-portfolio-filter" class="milenia-grid milenia-grid--isotope <?php echo esc_attr($MileniaHelper->getSanitizedHtmlClasses($milenia_grid_classes)); ?>">
			<div class="milenia-grid-sizer"></div>

			<?php foreach($milenia_projects as $index => $post) :

				if($milenia_items_layout == 'masonry')
				{
					if($index == 0 || $index == 3) {
						set_query_var('milenia-item-thumb-size', 'entity-thumb-size-square');
					}
					elseif($index == 1 || $index == 5 || $index == 6 || $index == 7 || $index == 8 || $index == 9) {
						set_query_var('milenia-item-thumb-size', 'entity-thumb-size-rectangle');
					}
					elseif($index == 2 || $index == 4) {
						set_query_var('milenia-item-thumb-size', 'entity-thumb-size-vertical-rectangle');
					}
					elseif($index != 0 && ($index % 5 == 0 || ($index+1) % 7 == 0))
					{
						set_query_var('milenia-item-thumb-size', 'entity-thumb-size-vertical-rectangle');
					}
					elseif($index == 0 || ($index +1) % 4 == 0)
					{
						set_query_var('milenia-item-thumb-size', 'entity-thumb-size-square');
					}
					else
					{
						set_query_var('milenia-item-thumb-size', 'entity-thumb-size-rectangle');
					}
				}
				else
				{
					if(in_array($milenia_page_columns, array('milenia-grid--cols-3', 'milenia-grid--cols-4')))
					{
						set_query_var('milenia-item-thumb-size', 'entity-thumb-size-square');
					}
				}
				setup_postdata($post);
			?>
				<?php get_template_part('template-parts/milenia-portfolio/milenia-portfolio', 'post'); ?>
			<?php endforeach; wp_reset_postdata(); ?>
		</div>
	</div>
	<!-- - - - - - - - - - - - - - End of Portfolio - - - - - - - - - - - - - -->

	<!-- - - - - - - - - - - - - - Pagination - - - - - - - - - - - - - -->
	<footer class="text-center container">
		<?php milenia_pagination(array(
			'total'   => ceil( count($MileniaPostsRepository) / $milenia_query_limit ),
			'current' => $milenia_query_paged
		), array('milenia-pagination--justified')); ?>
	</footer>
	<!-- - - - - - - - - - - - - - End of Pagination - - - - - - - - - - - - - -->
<?php else : ?>
	<?php get_template_part('template-parts/content', 'none'); ?>
<?php endif; ?>
