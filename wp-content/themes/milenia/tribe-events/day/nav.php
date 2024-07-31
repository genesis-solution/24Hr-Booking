<?php
/**
 * Day View Nav
 * This file contains the day view navigation.
 *
 * Override this template in your own theme by creating a file at [your-theme]/tribe-events/day/nav.php
 *
 * @package TribeEventsCalendar
 * @version 4.6.19
 *
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( '-1' );
} ?>

<nav class="tribe-events-nav-pagination" aria-label="<?php esc_attr_e( 'Day Navigation', 'milenia' ) ?>">
	<ul class="tribe-events-sub-nav milenia-list--unstyled milenia-posts-navigation">

		<!-- Previous Page Navigation -->
		<li class="tribe-events-nav-previous milenia-posts-navigation-prev"><span><?php tribe_the_day_link( 'previous day' ) ?></span></li>

		<!-- Next Page Navigation -->
		<li class="tribe-events-nav-next milenia-posts-navigation-next"><span><?php tribe_the_day_link( 'next day' ) ?></span></li>

	</ul>
</nav>
