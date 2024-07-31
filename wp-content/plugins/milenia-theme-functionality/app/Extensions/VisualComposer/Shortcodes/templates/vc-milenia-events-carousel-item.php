<?php
/**
 * The template file that is responsible to describe an offer element markup.
 *
 * @package WordPress
 * @subpackage Milenia
 * @since Milenia 1.0.0
 */

// Prevent the direct loading of the file
if ( ! defined( 'ABSPATH' ) ) {
	die( esc_html__('You cannot access this file directly.', 'milenia-app-textdomain') );
}
?>

<!--================ Entity ================-->
<article <?php post_class('milenia-entity milenia-event--text text-center format-standard'); ?>>
    <?php if(has_post_thumbnail()) : ?>
        <div class="milenia-entity-media" data-bg-image-src="<?php echo esc_url(get_the_post_thumbnail_url(get_the_ID(), 'full')); ?>"></div>
    <?php endif; ?>

    <div class="milenia-entity-content">
        <?php if(!empty($this->attributes['slide_title'])) : ?>
            <h6 class="milenia-section-subtitle milenia-font--like-body"><?php echo esc_html($this->attributes['slide_title']); ?></h6>
        <?php endif; ?>

        <h2 class="milenia-section-title"><?php the_title(); ?></h2>
        <div class="milenia-entity-meta">
            <time datetime="<?php echo esc_attr(tribe_get_start_date(get_post(), false, 'c')); ?>"><?php echo tribe_events_event_schedule_details() ?></time>
        </div>
        <a href="<?php the_permalink(); ?>" class="milenia-btn milenia-btn--unbordered"><?php esc_html_e('Read More', 'milenia-app-textdomain'); ?></a>
    </div>
</article>
<!--================ End of Entity ================-->
