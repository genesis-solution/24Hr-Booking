<?php
/**
 * Photo View Nav
 * This file contains the photo view navigation.
 *
 * Override this template in your own theme by creating a file at [your-theme]/tribe-events/pro/photo/nav.php
 *
 * @package TribeEventsCalendar
 * @version 4.4.28
 *
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( esc_html__('You cannot access this file directly', 'milenia') );
}

$events_label_plural = tribe_get_event_label_plural();

?>
<nav class="text-center" aria-label="<?php echo esc_attr( sprintf( __( '%s Navigation', 'milenia' ), tribe_get_event_label_plural() ) ) ?>">
	<ul class="milenia-list--unstyled milenia-posts-navigation">
		<?php if ( tribe_has_previous_event() ) : ?>
			<li class="<?php echo esc_attr( tribe_left_navigation_classes() ); ?> milenia-posts-navigation-prev">
				<span>
					<a href="<?php echo esc_url( tribe_get_listview_prev_link() ); ?>" rel="prev">
						<?php echo esc_html( sprintf( __( 'Previous %s', 'milenia' ), tribe_get_event_label_plural() ) ); ?>
					</a>
				</span>
			</li><!-- .tribe-events-nav-left -->
		<?php endif; ?>

		<li>
			<?php do_action( 'milenia_tribe_nav_links' ); ?>
		</li>

		<?php if ( tribe_has_next_event() ) : ?>
			<li class="<?php echo esc_attr( tribe_right_navigation_classes() ); ?> milenia-posts-navigation-next">
				<span>
					<a href="<?php echo esc_url( tribe_get_listview_next_link() ); ?>" rel="next">
						<?php echo esc_html( sprintf( __( 'Next %s', 'milenia' ), tribe_get_event_label_plural() ) ); ?>
					</a>
				</span>
			</li><!-- .tribe-events-nav-right -->
		<?php endif; ?>
	</ul>
</nav>
