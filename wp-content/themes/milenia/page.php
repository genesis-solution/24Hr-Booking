<?php
/**
* The template for displaying all single pages
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
$milenia_main_section_classes = array();

if($MileniaLayout->isFullWidth())
{
	array_push($milenia_main_section_classes, 'milenia-section--stretched-content-no-px');
}

// load header.php
get_header();
?>
<div class="milenia-section <?php echo esc_attr($MileniaHelper->getSanitizedHtmlClasses($milenia_main_section_classes)); ?>">
	<div class="row">
		<!-- - - - - - - - - - - - - - Main Content Column - - - - - - - - - - - - - - - - -->
		<main class="<?php echo esc_attr($MileniaLayout->getMainLayoutClasses('main')); ?>">
			<?php get_template_part('template-parts/page-layouts/page-layout', preg_replace('/milenia-/', '', $MileniaLayout->getPageType())); ?>
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
<?php

// load footer.php
get_footer();
?>
