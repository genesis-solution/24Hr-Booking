<?php
/**
 * Single Event Meta (Details) Template
 *
 * Override this template in your own theme by creating a file at:
 * [your-theme]/tribe-events/modules/meta/details.php
 *
 * @package TribeEventsCalendar
 * @version 4.6.19
 */


$time_format = get_option( 'time_format', Tribe__Date_Utils::TIMEFORMAT );
$time_range_separator = tribe_get_option( 'timeRangeSeparator', ' - ' );

$start_datetime = tribe_get_start_date();
$start_date = tribe_get_start_date( null, false );
$start_time = tribe_get_start_date( null, false, $time_format );
$start_ts = tribe_get_start_date( null, false, Tribe__Date_Utils::DBDATEFORMAT );

$end_datetime = tribe_get_end_date();
$end_date = tribe_get_display_end_date( null, false );
$end_time = tribe_get_end_date( null, false, $time_format );
$end_ts = tribe_get_end_date( null, false, Tribe__Date_Utils::DBDATEFORMAT );

$time_formatted = null;
if ( $start_time == $end_time ) {
	$time_formatted = esc_html( $start_time );
} else {
	$time_formatted = esc_html( $start_time . $time_range_separator . $end_time );
}

$event_id = Tribe__Main::post_id_helper();

$event_tags_label = esc_html__('Tags:', 'milenia');
$event_tags_list      = get_the_term_list( get_the_id(), 'post_tag', '<li><span>' . $event_tags_label . '</span>', ', ', '</li>');
$event_tags_list      = apply_filters( 'tribe_meta_event_tags', $event_tags_list, $event_tags_label, ',' );

/**
 * Returns a formatted time for a single event
 *
 * @var string Formatted time string
 * @var int Event post id
 */
$time_formatted = apply_filters( 'tribe_events_single_event_time_formatted', $time_formatted, $event_id );

/**
 * Returns the title of the "Time" section of event details
 *
 * @var string Time title
 * @var int Event post id
 */
$time_title = apply_filters( 'tribe_events_single_event_time_title', __( 'Time:', 'milenia' ), $event_id );

$cost    = tribe_get_formatted_cost();
$website = tribe_get_event_website_link();
?>

<h6 class="milenia-fw-bold"><?php esc_html_e('Details', 'milenia'); ?></h6>

<ul class="milenia-details-list milenia-details-list--colors-reversed milenia-list--unstyled">
	<?php
	do_action( 'tribe_events_single_meta_details_section_start' );

	// All day (multiday) events
	if ( tribe_event_is_all_day() && tribe_event_is_multiday() ) :
		?>
		<li>
			<span><?php esc_html_e('Date:', 'milenia'); ?></span>
			<time datetime="<?php echo esc_attr($start_ts); ?>">
				<?php printf('%s - %s', esc_html( $start_date ), esc_html( $end_date )); ?>
			</time>
		</li>
	<?php
	// All day (single day) events
	elseif ( tribe_event_is_all_day() ):
		?>
		<li>
			<span><?php esc_html_e('Date:', 'milenia'); ?></span>
			<time datetime="<?php echo esc_attr($start_ts); ?>"><?php echo esc_html( $start_date ); ?></time>
		</li>
	<?php
	// Multiday events
	elseif ( tribe_event_is_multiday() ) :
		?>
		<li>
			<span><?php esc_html_e('Start:', 'milenia'); ?></span>
			<time datetime="<?php echo esc_attr($start_ts); ?>"><?php echo esc_html( $start_datetime ); ?></time>
		</li>

		<li>
			<span><?php esc_html_e('End:', 'milenia'); ?></span>
			<time datetime="<?php echo esc_attr($end_ts); ?>"><?php echo esc_html( $end_datetime ); ?></time>
		</li>
	<?php
	// Single day events
	else :
		?>
		<li>
			<span><?php esc_html_e('Date:', 'milenia'); ?></span>
			<time datetime="<?php echo esc_attr($start_ts); ?>"><?php echo esc_html( $start_date ); ?></time>
		</li>

		<li>
			<span><?php echo esc_html( $time_title ); ?></span>
			<?php echo wp_kses($time_formatted, array(
				'div' => array(
					'class' => true
				)
			)); ?>
		</li>
	<?php endif; ?>

	<li>
		<span><?php esc_html_e( 'Cost:', 'milenia' ); ?></span>
		<?php if ( ! empty($cost ) ) : ?>
			<?php echo esc_html( $cost ); ?>
		<?php else : ?>
			<?php esc_html_e('Free', 'milenia'); ?>
		<?php endif; ?>
	</li>

	<?php
		echo tribe_get_event_categories(
			get_the_id(), array(
				'before'       => '',
				'sep'          => ', ',
				'after'        => '',
				'label'        => null, // An appropriate plural/singular label will be provided
				'label_before' => '<li><span>',
				'label_after'  => '</span>',
				'wrap_before'  => '',
				'wrap_after'   => '</li>',
			)
		);
	?>

	<?php if (!empty($website)) : ?>
		<li>
			<span><?php esc_html_e( 'Website:', 'milenia' ); ?></span>
			<?php printf('%s', $website); ?>
		</li>
	<?php endif ?>

	<?php if($event_tags_list) : ?>
		<?php printf('%s', $event_tags_list); ?>
	<?php endif; ?>

	<?php do_action( 'tribe_events_single_meta_details_section_end' ) ?>
</ul>
