<?php
/**
* The template file that is responsible to display galleries archive page.
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

$milenia_query_paged = intval(isset($_GET['pagenum']) ? $_GET['pagenum'] : 1);
$milenia_query_limit = intval(get_option('posts_per_page'));

$MileniaPostsRepository = new MileniaGalleryRepository();
$milenia_items = $MileniaPostsRepository->fromCategories(get_query_var('milenia-gallery-categories'))
									->offset(($milenia_query_paged - 1) * $milenia_query_limit)
                                    ->limit($milenia_query_limit)
                                    ->get();
?>

<div class="milenia-entity-content">
    <?php if(is_array($milenia_items) && !empty($milenia_items)) : ?>
        <!-- - - - - - - - - - - - - - Gallery - - - - - - - - - - - - - -->
        <div class="milenia-gallery">
            <div class="milenia-grid milenia-grid--isotope milenia-grid--cols-4"
                 data-isotope-layout="masonry">
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
