<?php
/**
 * The template file that is responsible to describe a testimonial element markup.
 *
 * @package WordPress
 * @subpackage MileniaThemeFunctionality
 * @since MileniaThemeFunctionality 1.0.0
 */

// Prevent the direct loading of the file
if (!defined('ABSPATH'))
{
	die(esc_html__('You cannot access this file directly.', 'milenia-theme-functionality'));
}

$milenia_t_location = get_post_meta(get_the_ID(), 'milenia-testimonial-author-location', true);
$milenia_t_assessment = get_post_meta(get_the_ID(), 'milenia-testimonial-author-assessment', true);
$milenia_t_logo = get_post_meta(get_the_ID(), 'milenia-testimonial-service-logo', true);
$milenia_t_link_url = get_post_meta(get_the_ID(), 'milenia-testimonial-service-link-url', true);
$milenia_t_link_nofollow = get_post_meta(get_the_ID(), 'milenia-testimonial-service-link-nofollow', true);
$milenia_t_link_target_blank = get_post_meta(get_the_ID(), 'milenia-testimonial-service-link-target-blank', true);

$milenia_t_color = get_query_var('milenia-testimonial-text-color', '');
$milenia_t_assessment_color = get_query_var('milenia-testimonial-assessment-color', '');
$milenia_t_author_name_color = get_query_var('milenia-testimonial-author-name-color', '');

$milenia_t_style = get_query_var('milenia-testimonial-style', 'milenia-testimonials--style-1');

?>

<div <?php post_class((bool) $this->attributes['no_grid_system'] ? '' : 'milenia-grid-item') ?>>
	<!--================ Testimonial ================-->
	<div class="milenia-testimonial">
		<?php if(!empty($milenia_t_assessment) && $milenia_t_style == 'milenia-testimonials--style-1') : ?>
			<div data-estimate="<?php echo esc_attr($milenia_t_assessment); ?>" class="milenia-rating milenia-text-color--primary" <?php if(!empty($milenia_t_assessment_color)) : ?>style="color: <?php echo esc_attr($milenia_t_assessment_color); ?>;"<?php endif; ?>></div>
		<?php endif; ?>

		<blockquote aria-labelledby="testimonial-cite-<?php echo esc_attr($this->unique_id . get_the_ID()); ?>" class="milenia-blockquote--unstyled milenia-text-color--darkest" <?php if(!empty($milenia_t_color)) : ?>style="color: <?php echo esc_attr($milenia_t_color); ?>;"<?php endif; ?>>
			<?php the_content(); ?>
		</blockquote>
		<footer class="milenia-author<?php if($milenia_t_style == 'milenia-testimonials--style-3') : ?> milenia-author--style-2<?php endif; ?>">
			<?php if(in_array($milenia_t_style, array('milenia-testimonials--style-2', 'milenia-testimonials--style-3')) && has_post_thumbnail()) : ?>
				<div data-bg-image-src="<?php echo esc_url(get_the_post_thumbnail_url(get_the_ID(), 'full')); ?>" class="milenia-author-photo"></div>
			<?php endif; ?>

			<div class="milenia-author-info">
				<cite id="testimonial-cite-<?php echo esc_attr($this->unique_id . get_the_ID()); ?>" <?php if(!empty($milenia_t_author_name_color)) : ?>style="color: <?php echo esc_attr($milenia_t_author_name_color); ?>;"<?php endif; ?>>
					<a href="<?php the_permalink() ?>" class="milenia-ln--independent"><?php the_title(); ?><?php if(!empty($milenia_t_location)) : ?>,<?php if($milenia_t_style == 'milenia-testimonials--style-3') : ?><br><?php endif; ?>  <?php echo esc_html($milenia_t_location); endif; ?></a>
				</cite>

				<?php if(!empty($milenia_t_logo) && $milenia_t_style == 'milenia-testimonials--style-1') : ?>
					<?php if(!empty($milenia_t_link_url)) : ?>
						<a href="<?php echo esc_url($milenia_t_link_url); ?>" <?php if(intval($milenia_t_link_nofollow) == 1) : ?>rel="nofollow"<?php endif; ?> <?php if(intval($milenia_t_link_target_blank) == 1) : ?>target="_blank"<?php endif; ?> class="milenia-testimonial-service milenia-ln--independent">
					<?php endif; ?>
						<?php echo wp_get_attachment_image(intval($milenia_t_logo), 'medium'); ?>
					<?php if(!empty($milenia_t_link_url)) : ?>
						</a>
					<?php endif; ?>
				<?php endif; ?>
			</div>
		</footer>
	</div>
	<!--================ End of Testimonial ================-->
</div>
