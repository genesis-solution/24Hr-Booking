<?php
/**
 * Map View Nav
 * This file contains the map view navigation.
 *
 * Override this template in your own theme by creating a file at [your-theme]/tribe-events/pro/map/nav.php
 *
 * @package TribeEventsCalendar
 * @version 4.4.28
 *
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( '-1' );
}

$events_label_plural = tribe_get_event_label_plural();

?>

<nav class="text-center" aria-label="<?php printf( __( '%s List Navigation', 'milenia' ), $events_label_plural ); ?>">
	<ul class="milenia-list--unstyled milenia-posts-navigation">
		<?php if ( tribe_has_previous_event() ) : ?>
			<li class="<?php echo esc_attr( tribe_left_navigation_classes() ); ?> milenia-posts-navigation-prev">
				<span>
					<a href="#" class="tribe_map_paged"><?php printf( esc_html__( 'Previous %s', 'milenia' ), $events_label_plural ); ?></a>
				</span>
			</li>
		<?php endif; ?>

		<?php if ( tribe_has_next_event() ) : ?>
			<li class="<?php echo esc_attr( tribe_right_navigation_classes() ); ?> milenia-posts-navigation-next">
				<span>
					<a href="#" class="tribe_map_paged"><?php printf( esc_html__( 'Next %s', 'milenia' ), $events_label_plural ); ?></a>
				</span>
			</li><!-- .tribe-events-nav-right -->
		<?php endif; ?>
	</ul>
</nav>
