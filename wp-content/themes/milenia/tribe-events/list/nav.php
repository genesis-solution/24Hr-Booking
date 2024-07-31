<?php
/**
 * List View Nav Template
 * This file loads the list view navigation.
 *
 * Override this template in your own theme by creating a file at [your-theme]/tribe-events/list/nav.php
 *
 * @package TribeEventsCalendar
 * @version 4.6.19
 *
 */
if ( ! $wp_query = tribe_get_global_query_object() ) {
	return;
}

if ( ! defined( 'ABSPATH' ) ) {
	die( esc_html__('You cannot access this file directly', 'milenia') );
} ?>


<nav class="text-center tribe-events-nav-pagination" aria-label="<?php echo esc_attr( sprintf( esc_html__( '%s List Navigation', 'milenia' ), tribe_get_event_label_plural() ) ); ?>">
	<ul class="milenia-list--unstyled milenia-posts-navigation tribe-events-sub-nav">
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
