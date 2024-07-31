<?php
/**
 * Week View Nav
 * This file loads the week view navigation.
 *
 * Override this template in your own theme by creating a file at [your-theme]/tribe-events/pro/week/nav.php
 *
 * @package TribeEventsCalendar
 * @version 4.4.28
 *
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( '-1' );
} ?>

<nav class="tribe-events-nav-pagination" aria-label="<?php esc_attr_e( 'Week Navigation', 'milenia' ); ?>">
	<ul class="milenia-list--unstyled milenia-posts-navigation tribe-events-sub-nav">
		<li class="milenia-posts-navigation-prev tribe-events-nav-previous">
			<span><?php echo tribe_events_week_previous_link() ?></span>
		</li><!-- .tribe-events-nav-previous -->
		<li class="milenia-posts-navigation-next tribe-events-nav-next">
			<span><?php echo tribe_events_week_next_link() ?></span>
		</li><!-- .tribe-events-nav-next -->
	</ul><!-- .tribe-events-sub-nav -->
</nav>
