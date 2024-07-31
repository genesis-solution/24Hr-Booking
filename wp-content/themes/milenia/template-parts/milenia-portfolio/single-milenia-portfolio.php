<?php
/**
* The template file for displaying a single portfolio project.
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

global $Milenia;

$milenia_project_layout = $Milenia->getThemeOption('milenia-project-layout', 'slideshow');
$milenia_project_related = $Milenia->getThemeOption('milenia-project-related-state', 'slideshow');

if($milenia_project_related == 'show')
{
	$mielnia_project_cats = get_the_terms(get_the_ID(), 'milenia-portfolio-categories');

	if(is_array($mielnia_project_cats) && !empty($mielnia_project_cats))
	{
		$milenia_projects_cats_final = array();
		foreach($mielnia_project_cats as $milenia_cat) {
			if($milenia_cat->count == 1) continue;
			array_push($milenia_projects_cats_final, $milenia_cat->term_id);
		}
	}
}

?>

<?php if(have_posts()) : ?>
    <?php while(have_posts()) : the_post(); ?>
		<div class="milenia-section milenia-entity-single milenia-entity--project">
			<?php get_template_part('template-parts/milenia-portfolio/milenia-portfolio-single-layout', $milenia_project_layout); ?>

			<!-- - - - - - - - - - - - - - Content Section - - - - - - - - - - - - - -->
			<div class="milenia-section milenia-section--py-small">
				<nav>
					<ul class="milenia-list--unstyled milenia-posts-navigation">
						<li class="milenia-posts-navigation-prev"><span><?php previous_post_link('%link'); ?></span></li>
						<li><a href="<?php echo esc_url( get_post_type_archive_link( get_post_type() ) ); ?>" class="milenia-posts-navigation-icon milenia-non-underlined-link"><span class="icon icon-icons"></span></a></li>
						<li class="milenia-posts-navigation-next"><span><?php next_post_link('%link'); ?></span></li>
					</ul>
				</nav>
			</div>
			<!-- - - - - - - - - - - - - - End of Content Section - - - - - - - - - - - - - -->
		</div>

		<?php if($milenia_project_related == 'show' && isset($milenia_projects_cats_final) && !empty($milenia_projects_cats_final)) : ?>
			<!-- - - - - - - - - - - - - - Content Section - - - - - - - - - - - - - -->
			<section class="milenia-section">
				<h3><?php esc_html_e('Related Portfolio Posts', 'milenia'); ?></h3>
				<?php echo do_shortcode(sprintf('[vc_milenia_portfolio milenia_portfolio_categories_state="1" milenia_portfolio_columns="3" milenia_portfolio_data_total_items="3" milenia_portfolio_data_exc="%s" milenia_portfolio_data_categories="%s"]', get_the_ID(), implode(',', $milenia_projects_cats_final))); ?>
			</section>
			<!-- - - - - - - - - - - - - - End of Content Section - - - - - - - - - - - - - -->
		<?php endif; ?>

		<?php if(comments_open()) : ?>
			<?php comments_template(); ?>
		<?php endif; ?>
    <?php endwhile; ?>
<?php else : ?>
    <?php get_template_part('template-parts/content', 'none'); ?>
<?php endif; ?>
