<?php
/**
* The template file that describes "breadcrumb" section markup.
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

$milenia_breadcrumb = $MileniaLayout->getBreadcrumb();

// stop execution of the code below in case the breadcrumb section was disabled
if(!$milenia_breadcrumb) return;

$milenia_breadcrumb_classes = array($milenia_breadcrumb['content-alignment']);
$milenia_breadcrumb_bg_is_set = isset($milenia_breadcrumb['background-image']) && !empty($milenia_breadcrumb['background-image']) && $milenia_breadcrumb['background-image'] != 'none';

if(isset($milenia_breadcrumb['parallax']) && $milenia_breadcrumb['parallax'] == 'on' && $milenia_breadcrumb_bg_is_set)
{
    array_push($milenia_breadcrumb_classes, 'milenia-colorizer--parallax');
}
?>

<!--================ Breadcrumb ================-->
<div class="milenia-breadcrumb milenia-colorizer-functionality <?php echo esc_attr($MileniaHelper->getSanitizedHtmlClasses($milenia_breadcrumb_classes)); ?>"
	<?php if($milenia_breadcrumb_bg_is_set) : ?>
	    data-bg-image-src="<?php echo esc_url(wp_get_attachment_image_url($milenia_breadcrumb['background-image'], 'full')); ?>"
		data-bg-image-opacity="<?php echo esc_attr($milenia_breadcrumb['background-image-opacity']); ?>"
	<?php endif; ?>
	data-bg-color="<?php echo esc_attr($milenia_breadcrumb['background-color']); ?>"
>
    <div class="container">
		<?php if(isset($milenia_breadcrumb['page-title-state']) && $milenia_breadcrumb['page-title-state'] == 'on') : ?>
			<?php if(is_archive()) : ?>
				<h1 class="milenia-page-title"><?php the_archive_title(); ?></h1>
			<?php elseif (is_home()) : ?>

				<?php
				$blog_page = get_page(get_option('page_for_posts')); ?>

				<?php if ( !empty($blog_page) ): ?>
					<h1 class="milenia-page-title"><?php echo esc_html($blog_page->post_title); ?></h1>
				<?php else: ?>
					<h1 class="milenia-page-title"><?php the_archive_title(); ?></h1>
				<?php endif; ?>

			<?php else: ?>
        		<h1 class="milenia-page-title"><?php the_title(); ?></h1>
			<?php endif; ?>
		<?php endif; ?>

		<?php if(isset($milenia_breadcrumb['breadcrumb-path-state']) && $milenia_breadcrumb['breadcrumb-path-state'] == 'on') : ?>
        	<?php milenia_breadcrumbs(); ?>
		<?php endif; ?>
    </div>
</div>
<!--================ End of Breadcrumb ================-->
