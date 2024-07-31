<?php
/**
 * Photo View Loop
 * This file sets up the structure for the photo view events loop
 *
 * Override this template in your own theme by creating a file at [your-theme]/tribe-events/pro/photo/loop.php
 *
 * @version 4.4.28
 * @package TribeEventsCalendar
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( esc_html__('You cannot access this file directly', 'milenia') );
}

global $more;
$more = false;

?>

<h2 class="tribe-events-visuallyhidden"><?php printf( esc_html__( 'List of %s', 'milenia' ), tribe_get_event_label_plural() ); ?></h2>

<div class="milenia-entities milenia-entities--style-19" id="milenia-tribe-events-photo-events">
	<div class="milenia-grid milenia-grid--isotope milenia-grid--cols-3" data-isotope-layout="masonry">
		<div class="milenia-grid-sizer"></div>
		<?php while ( have_posts() ) : the_post(); ?>
			<?php do_action( 'tribe_events_inside_before_loop' ); ?>

			<!-- Event  -->
			<div id="post-<?php the_ID() ?>" class="<?php tribe_events_event_classes() ?> milenia-grid-item">
				<?php tribe_get_template_part( 'pro/photo/single', 'event' ) ?>
			</div>

			<?php do_action( 'tribe_events_inside_after_loop' ); ?>
		<?php endwhile; ?>
	</div>
</div><!-- .tribe-events-loop -->
