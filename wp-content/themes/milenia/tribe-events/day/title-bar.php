<?php
/**
 * Day View Title Template
 * The title template for the day view of events.
 *
 * Override this template in your own theme by creating a file at [your-theme]/tribe-events/day/title-bar.php
 *
 * @package TribeEventsCalendar
 * @version 4.6.19
 * @since   4.6.19
 *
 */
?>

<!-- Day Title -->
<?php do_action( 'tribe_events_before_the_title' ); ?>
<h3 class="text-center"><?php echo tribe_get_events_title() ?></h3>
<?php do_action( 'tribe_events_after_the_title' ); ?>
