<?php
/**
 * Month View Single Day
 * This file contains one day in the month grid
 *
 * Override this template in your own theme by creating a file at [your-theme]/tribe-events/month/single-day.php
 *
 * @package TribeEventsCalendar
 * @version 4.6.19
 *
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( esc_html__('You cannot access this file directly', 'milenia') );
}

$day = tribe_events_get_current_month_day();
$events_label = ( 1 === $day['total_events'] ) ? tribe_get_event_label_singular() : tribe_get_event_label_plural();
$date_label = date_i18n( tribe_get_date_option( 'dateWithoutYearFormat', 'F j' ), strtotime( $day['date'] ) )
?>

<!-- Day Header -->
<div id="tribe-events-daynum-<?php echo esc_attr($day['daynum-id']); ?>" class="milenia-events-daynum">
	<?php if ( $day['total_events'] > 0 && tribe_events_is_view_enabled( 'day' ) ) : ?>
		<?php $view_day_label = sprintf( __( 'View %s', 'milenia' ), $date_label ); ?>
		<a href="<?php echo esc_url( tribe_get_day_link( $day['date'] ) ); ?>" aria-label="<?php echo esc_attr( $view_day_label ); ?>">
			<?php echo esc_html($day['daynum']); ?>
		</a>
	<?php else : ?>
		<?php echo esc_html($day['daynum']); ?>
	<?php endif; ?>

</div>

<!-- Events List -->
<?php if($day['events']->have_posts()) : ?>
	<?php while ( $day['events']->have_posts() ) : $day['events']->the_post(); ?>
		<?php tribe_get_template_part( 'month/single', 'event' ) ?>
	<?php endwhile; ?>
<?php else : ?>
	<div class="milenia-day-empty-space"></div>
<?php endif; ?>

<!-- View More -->
<?php if ( $day['view_more'] ) : ?>
	<div class="tribe-events-viewmore">
		<?php

			$view_all_label = sprintf(esc_html__('View %s more', 'milenia'), $day['total_events']);

			$view_all_aria_label = sprintf( esc_html__( '%s for %s', 'milenia' ), $view_all_label, $date_label );
		?>
		<a href="<?php echo esc_url( $day['view_more'] ); ?>" aria-label="<?php echo esc_attr( $view_all_aria_label ); ?>">
			<?php echo esc_html($view_all_label); ?> &raquo;
		</a>
	</div>
<?php
endif;
