<?php
/**
 * Month View Nav Template
 * This file loads the month view navigation.
 *
 * Override this template in your own theme by creating a file at [your-theme]/tribe-events/month/nav.php
 *
 * @package TribeEventsCalendar
 * @version 4.6.19
 *
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( esc_html__('You cannot access this file directly', 'milenia') );
} ?>

<?php do_action( 'tribe_events_before_nav' ) ?>
<nav class="tribe-events-nav-pagination" aria-label="<?php esc_attr_e( 'Calendar Month Navigation', 'milenia' ) ?>">
	<ul class="milenia-list--unstyled milenia-posts-navigation tribe-events-sub-nav">
		<li class="milenia-posts-navigation-prev tribe-events-nav-previous">
			<span><?php tribe_events_the_previous_month_link(); ?></span>
		</li>

		<li class="milenia-posts-navigation-next tribe-events-nav-next">
			<span><?php tribe_events_the_next_month_link(); ?></span>
		</li>
	</ul>
</nav>
<?php
do_action( 'tribe_events_after_nav' );
