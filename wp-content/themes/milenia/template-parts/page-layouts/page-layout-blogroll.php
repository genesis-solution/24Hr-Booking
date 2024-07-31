<?php
/**
* The template file for displaying a blogroll page.
*
* @package WordPress
* @subpackage Milenia
* @since Milenia 1.0
*/

// Prevent the direct loading of the file
if ( ! defined( 'ABSPATH' ) ) {
	die( esc_html__('You cannot access this file directly', 'milenia') );
}

if(!class_exists('MileniaPostRepository')) return;

global $Milenia, $post;
$MileniaHelper = $Milenia->helper();

$milenia_item_style = $Milenia->getThemeOption('milenia-post-archive-style', 'milenia-entities--style-4', array(
	'overriden_by' => 'milenia-page-blogroll-item-style',
	'depend_on' => array( 'key' => 'milenia-page-settings-inherit-individual', 'value' => 0 )
));

$milenia_page_columns = $Milenia->getThemeOption('milenia-post-archive-columns', 'milenia-grid--cols-3', array(
	'overriden_by' => 'milenia-page-blogroll-columns-individual',
	'depend_on' => array( 'key' => 'milenia-page-settings-inherit-individual', 'value' => 0 )
));

$milenia_items_layout = $Milenia->getThemeOption('milenia-post-archive-isotope-layout', 'grid', array(
	'overriden_by' => 'milenia-page-items-layout',
	'depend_on' => array( 'key' => 'milenia-page-settings-inherit-individual', 'value' => 0 )
));

$milenia_query_cats = $Milenia->getThemeOption('milenia-page-blogroll-categories', array());
$milenia_query_include = $Milenia->getThemeOption('milenia-page-blogroll-in', array());
$milenia_query_exclude = $Milenia->getThemeOption('milenia-page-blogroll-out', array());
$milenia_query_orderby = $Milenia->getThemeOption('milenia-page-order-by', 'date');
$milenia_query_order = $Milenia->getThemeOption('milenia-page-sort-order', 'DESC');
$milenia_query_limit = $Milenia->getThemeOption('milenia-page-items-per-page', get_option('posts_per_page'));
$milenia_query_tags = $Milenia->getThemeOption('milenia-page-blogroll-tags', array());

$milenia_container_classes = explode(' ', $milenia_item_style);
$milenia_grid_classes = array($milenia_page_columns);
$milenia_query_paged = max(get_query_var('paged'), 1);
$MileniaPostsRepository = new MileniaPostRepository('post');

$milenia_posts = $MileniaPostsRepository->fromCategories($milenia_query_cats,  'category')
									->fromCategories($milenia_query_tags, 'post_tag')
									->in($milenia_query_include)
									->out($milenia_query_exclude)
									->orderBy($milenia_query_orderby)
									->order($milenia_query_order)
									->offset(($milenia_query_paged - 1) * intval($milenia_query_limit))
									->limit($milenia_query_limit)
									->get();

set_query_var('milenia-post-archive-style', $milenia_item_style);
set_query_var('milenia-post-archive-isotope-layout', $milenia_items_layout);
?>
<?php if(is_array($milenia_posts) && !empty($milenia_posts)) : ?>
	<!-- - - - - - - - - - - - - - Blog Posts - - - - - - - - - - - - - -->
	<div class="milenia-entities <?php echo esc_attr($MileniaHelper->getSanitizedHtmlClasses($milenia_container_classes)); ?>">
		<div class="milenia-grid milenia-grid--isotope <?php echo esc_attr($MileniaHelper->getSanitizedHtmlClasses($milenia_grid_classes)); ?>"
			 data-isotope-layout="<?php echo esc_attr($milenia_items_layout); ?>">
			<div class="milenia-grid-sizer"></div>
			<?php foreach($milenia_posts as $index => $post) :
				setup_postdata($post);
				if($milenia_items_layout == 'masonry' && $milenia_item_style == 'milenia-entities--style-8')
				{
					if($index % 7 == 0 || (($index % 6 == 0) && $index % 3 - 1 != 0))
					{
						set_query_var('milenia-post-thumb-size', 'entity-thumb-size-rectangle');
					}
					elseif($index && $index % 3 == 0) {
						set_query_var('milenia-post-thumb-size', 'entity-thumb-size-vertical-rectangle');
					}
					elseif($index % 3 - 1 == 0)
					{
						set_query_var('milenia-post-thumb-size', 'entity-thumb-size-square');
					}
					else
					{
						set_query_var('milenia-post-thumb-size', 'entity-thumb-size-rectangle');
					}
				}
			?>
				<?php get_template_part('template-parts/post/post'); ?>
			<?php endforeach; wp_reset_postdata(); ?>
		</div>
	</div>
	<!-- - - - - - - - - - - - - - End of Blog Posts - - - - - - - - - - - - - -->

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
