<?php
/**
* The template for displaying a single post page with sidebar.
*
* @package WordPress
* @subpackage Milenia
* @since Milenia 1.0
*/

// Prevent the direct loading of the file
if ( ! defined( 'ABSPATH' ) ) {
	die( esc_html__('You cannot access this file directly', 'milenia') );
}

global $Milenia, $MileniaLayout, $milenia_settings;
$MileniaHelper = $Milenia->helper();

$milenia_share_buttons_state = $Milenia->getThemeOption('post-single-show-social-links', 'show', array(
	'overriden_by' => 'milenia-post-share-buttons-state',
	'depend_on' => array( 'key' => 'post-single-layout-state-individual', 'value' => '0' )
));

$milenia_tags_state = $Milenia->getThemeOption('post-single-show-tags', 'show', array(
	'overriden_by' => 'milenia-post-tags-state',
	'depend_on' => array( 'key' => 'post-single-layout-state-individual', 'value' => '0' )
));


$milenia_related_posts_state = $Milenia->getThemeOption('post-single-related-posts-state', 'show', array(
	'overriden_by' => 'milenia-post-related-posts-state',
	'depend_on' => array( 'key' => 'post-single-layout-state-individual', 'value' => '0' )
));


if($milenia_related_posts_state == 'show' || $milenia_related_posts_state == '1')
{
	$mielnia_post_cats = get_the_terms(get_the_ID(), 'category');

	if(is_array($mielnia_post_cats) && !empty($mielnia_post_cats))
	{
		$milenia_post_cats_final = array();
		foreach($mielnia_post_cats as $milenia_cat) {
			if($milenia_cat->count == 1) continue;
			array_push($milenia_post_cats_final, $milenia_cat->term_id);
		}
	}
}
?>


<?php if(have_posts()) : ?>
	<?php while(have_posts()) : the_post(); ?>
		<!-- - - - - - - - - - - - - - Content Section - - - - - - - - - - - - - -->
		<div class="milenia-section milenia-section--py-small">
			<div class="milenia-entity-header milenia-entity-header--single">
				<?php if(!empty(get_the_title())) : ?>
					<h1 class="milenia-entity-title"><?php the_title(); ?></h1>
				<?php endif; ?>

				<div class="milenia-entity-meta">
					<div>
						<time datetime="<?php echo esc_attr(get_the_date('c')); ?>"><?php the_date('F j, Y'); ?></time>
					</div>
					<div>
						<?php esc_html_e('by', 'milenia'); ?>&nbsp;<a href="<?php echo esc_url(get_author_posts_url(get_the_author_meta('ID'))); ?>"><?php the_author(); ?></a>
					</div>
					<?php if(milenia_has_post_terms(get_the_ID())) : ?>
						<div><?php
							esc_html_e('in', 'milenia');
							echo milenia_get_post_terms(get_the_ID());
						?></div>
					<?php endif; ?>
				</div>
			</div>
		</div>
		<!-- - - - - - - - - - - - - - End of Content Section - - - - - - - - - - - - - -->

		<div class="row">
			<!-- - - - - - - - - - - - - - Main Content Column - - - - - - - - - - - - - - - - -->
			<div class="<?php echo esc_attr($MileniaLayout->getMainLayoutClasses('main')); ?>">
				<!-- - - - - - - - - - - - - - Content Section - - - - - - - - - - - - - -->
				<div class="milenia-section">
					<main <?php post_class('milenia-entity-single milenia-entity--post milenia-entity--style-1'); ?>>
						<?php if(!post_password_required()) : ?>
							<?php get_template_part('template-parts/post/post-format-media', get_post_format()); ?>
						<?php endif; ?>

						<?php if(!empty(get_the_content())) : ?>
							<div class="milenia-entity-content">
								<?php
									the_content();
									wp_link_pages(array(
										'before' => '<div class="milenia-page-links"><span class="milenia-page-links-title">' . esc_html__( 'Pages:', 'milenia' ) . '</span>',
										'after' => '</div>',
										'link_before' => '<span>',
										'link_after' => '</span>'
									));
								?>
							</div>
						<?php endif; ?>

						<?php if(!post_password_required()) : ?>
							<?php if((($milenia_share_buttons_state == 'show' || $milenia_share_buttons_state == '1') && $Milenia->functionalityEnabled()) || (($milenia_tags_state == 'show' || $milenia_tags_state == '1') && milenia_has_post_terms(get_the_ID(), 'post_tag'))) : ?>
								<footer class="milenia-entity-footer">
									<div class="row milenia-columns-aligner--edges-lg">
										<div class="col-lg-9">
											<?php if(($milenia_tags_state == 'show' || $milenia_tags_state == '1') && milenia_has_post_terms(get_the_ID(), 'post_tag')) : ?>
												<?php esc_html_e('Tags:', 'milenia'); ?>
												<?php echo milenia_get_post_terms(get_the_ID(), 'post_tag'); ?>
											<?php endif; ?>

											<?php
											/**
											 * Hook for the add some content after the main content of the post.
											 *
											 * @hooked
											 */
											do_action('milenia_single_post_footer_left_col', get_post());
											?>
										</div>

										<div class="col-lg-3">
											<?php
											/**
											 * Hook for the add some content after the main content of the post.
											 *
											 * @hooked
											 */
											do_action('milenia_single_post_footer_right_col', get_post());
											?>
										</div>
									</div>
								</footer>
							<?php endif; ?>
						<?php endif; ?>
					</main>
				</div>
				<!-- - - - - - - - - - - - - - End of Content Section - - - - - - - - - - - - - -->

				<?php if(($milenia_share_buttons_state == 'show' || $milenia_share_buttons_state == '1') && isset($milenia_post_cats_final) && !empty($milenia_post_cats_final) && shortcode_exists('vc_milenia_blog_posts')) : ?>
					<!-- - - - - - - - - - - - - - Content Section - - - - - - - - - - - - - -->
					<section class="milenia-section">
						<h3><?php esc_html_e('Related Posts', 'milenia'); ?></h3>
						<?php echo do_shortcode(sprintf('[vc_milenia_blog_posts milenia_blog_posts_data_exclude="%s" milenia_blog_posts_data_categories="%s" milenia_blog_posts_data_total_items="3" milenia_blog_posts_columns="milenia-grid--cols-3" milenia_blog_posts_style="milenia-entities--style-4" milenia_blog_posts_no_content_state="true" milenia_blog_posts_no_read_more_btn_state="true" milenia_reduce_bq_characters="true"]', get_the_ID(), implode(',', $milenia_post_cats_final))); ?>
					</section>
					<!-- - - - - - - - - - - - - - End of Content Section - - - - - - - - - - - - - -->
				<?php endif; ?>

				<?php if(comments_open()) : ?>
					<?php comments_template(); ?>
				<?php endif; ?>

				<?php if(get_next_post() || get_previous_post()) : ?>
					<!-- - - - - - - - - - - - - - Posts Navgation - - - - - - - - - - - - - -->
					<nav class="milenia-section">
						<ul class="milenia-list--unstyled milenia-posts-navigation">
							<?php previous_post_link('<li class="milenia-posts-navigation-prev"><span>%link</span></li>'); ?>
							<?php next_post_link('<li class="milenia-posts-navigation-next"><span>%link</span></li>'); ?>
						</ul>
					</nav>
					<!-- - - - - - - - - - - - - - End of Posts Navigation - - - - - - - - - - - - - -->
				<?php endif; ?>
			</div>
			<!-- - - - - - - - - - - - - - End of Main Content Column - - - - - - - - - - - - - - - - -->
<?php endwhile; else : ?>
	<div class="row">
		<!-- - - - - - - - - - - - - - Main Content Column - - - - - - - - - - - - - - - - -->
		<div class="<?php echo esc_attr($MileniaLayout->getMainLayoutClasses('main')); ?>">
			<?php get_template_part('template-parts/content', 'none'); ?>
		</div>
		<!-- - - - - - - - - - - - - - End of Main Content Column - - - - - - - - - - - - - -->
<?php endif; ?>

    <?php if($MileniaLayout->hasSidebar() || (!isset($milenia_settings) || !isset($milenia['milenia-post-archive-layout']))) : ?>
    	<!-- - - - - - - - - - - - - - Sidebar - - - - - - - - - - - - - - - - -->
    	<aside class="milenia-sidebar <?php echo esc_attr($MileniaLayout->getMainLayoutClasses('side')); ?>" id="milenia-sidebar">
    		<?php get_sidebar(); ?>
    	</aside>
    	<!-- - - - - - - - - - - - - - End of Sidebar - - - - - - - - - - - - - - - - -->
    	<?php endif; ?>
    </div>
