<?php
/**
 * The template file that is responsible to describe a project element markup.
 *
 * @package WordPress
 * @subpackage Milenia
 * @since Milenia 1.0
 */

// Prevent the direct loading of the file
if ( ! defined( 'ABSPATH' ) ) {
	die( esc_html__('You cannot access this file directly', 'milenia') );
}

$milenia_item_categories = get_query_var('milenia-item-categories-state', '1');
$milenia_item_thumb_size = get_query_var('milenia-item-thumb-size', 'entity-thumb-size-rectangle');

$milenia_external_link_state = boolval(get_post_meta(get_the_ID(), 'milenia-project-external-link-state', true));
$milenia_external_link_target = boolval(get_post_meta(get_the_ID(), 'milenia-project-external-link-target', true));
$milenia_external_link_nofollow = boolval(get_post_meta(get_the_ID(), 'milenia-project-external-link-nofollow', true));
$milenia_external_link = get_post_meta(get_the_ID(), 'milenia-project-external-link', true);

?>

<!-- - - - - - - - - - - - - - Project - - - - - - - - - - - - - -->
<div <?php post_class('milenia-grid-item'); ?>>
	<article <?php post_class('milenia-entity'); ?>>
		<?php if(has_post_thumbnail()) : ?>
	        <!-- - - - - - - - - - - - - - Project Media - - - - - - - - - - - - - -->
			<div class="milenia-entity-media">
				<?php if($milenia_external_link_state == '1') : ?>
					<a href="<?php echo esc_url($milenia_external_link); ?>" <?php if($milenia_external_link_nofollow == '1') : ?>rel="nofollow"<?php endif ?> <?php if($milenia_external_link_target == '1') : ?>target="_blank"<?php endif; ?> class="milenia-entity-link milenia-ln--independent">
				<?php else : ?>
					<a href="<?php the_permalink(); ?>" class="milenia-entity-link milenia-ln--independent">
				<?php endif; ?>
					<?php the_post_thumbnail($milenia_item_thumb_size); ?>
				</a>
			</div>
	        <!-- - - - - - - - - - - - - - End of Project Media - - - - - - - - - - - - - -->
	    <?php endif; ?>

		<!-- - - - - - - - - - - - - - Project Content - - - - - - - - - - - - - -->
		<div class="milenia-entity-content milenia-aligner">
			<div class="milenia-aligner-outer">
				<div class="milenia-aligner-inner">
					<header class="milenia-entity-header">
						<?php if($milenia_item_categories == '1' && milenia_has_post_terms(get_the_ID(), 'milenia-portfolio-categories')) : ?>
							<div class="milenia-entity-meta">
		                        <?php echo milenia_get_post_terms(get_the_ID(), 'milenia-portfolio-categories'); ?>
							</div>
	                    <?php endif; ?>

						<h2 class="milenia-entity-title" title="<?php the_title_attribute(); ?>">
							<?php if($milenia_external_link_state == '1') : ?>
								<a href="<?php echo esc_url($milenia_external_link); ?>" <?php if($milenia_external_link_nofollow == '1') : ?>rel="nofollow"<?php endif ?> <?php if($milenia_external_link_target == '1') : ?>target="_blank"<?php endif; ?> class="milenia-color--unchangeable">
							<?php else : ?>
								<a href="<?php the_permalink(); ?>" class="milenia-color--unchangeable">
							<?php endif; ?>
								<?php the_title(); ?>
							</a>
						</h2>
					</header>
				</div>
			</div>
		</div>
		<!-- - - - - - - - - - - - - - End of Project Content - - - - - - - - - - - - - -->
	</article>
</div>
<!-- - - - - - - - - - - - - - End of Project - - - - - - - - - - - - - -->
