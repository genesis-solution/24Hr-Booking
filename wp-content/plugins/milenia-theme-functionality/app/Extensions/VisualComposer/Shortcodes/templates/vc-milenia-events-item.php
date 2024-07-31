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

<div <?php post_class('milenia-grid-item'); ?>>
    <article <?php post_class('milenia-entity format-standard'); ?>>
        <?php if(has_post_thumbnail()) : ?>
            <div class="milenia-entity-media">
                <a href="<?php the_permalink(); ?>">
                    <?php the_post_thumbnail('entity-thumb-standard'); ?>
                </a>
            </div>
        <?php endif; ?>

        <div class="milenia-entity-content milenia-aligner">
            <div class="milenia-aligner-outer">
                <div class="milenia-aligner-inner">
                    <header class="milenia-entity-header">
                        <div class="milenia-entity-meta">
                            <div>
                                <time datetime="<?php echo esc_attr(tribe_get_start_date(get_post(), false, 'c')); ?>"><?php echo tribe_events_event_schedule_details() ?></time>
                            </div>
                        </div>
                        <h2 class="milenia-entity-title">
                            <a href="<?php the_permalink(); ?>" class="milenia-color--unchangeable"><?php the_title(); ?></a>
                        </h2>
                    </header>

                    <?php if(!empty(tribe_get_address()) && !empty(tribe_get_city())) : ?>
                        <footer class="milenia-entity-footer">
                            <div class="milenia-entity-location">
                                <?php printf('%s, %s', tribe_get_address(), tribe_get_city()); ?>
                            </div>
                        </footer>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </article>
</div>
