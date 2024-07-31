<?php
/**
 * Single Event Meta (Venue) Template
 *
 * Override this template in your own theme by creating a file at:
 * [your-theme]/tribe-events/modules/meta/venue.php
 *
 * @package TribeEventsCalendar
 * @version 4.6.19
 */

if ( ! tribe_get_venue_id() ) {
	return;
}

$phone   = tribe_get_phone();
$website = tribe_get_venue_website_link();
$venue = tribe_get_venue();

?>

<h6 class="milenia-fw-bold"><?php esc_html_e('Venue', 'milenia'); ?></h6>

<ul class="milenia-details-list milenia-details-list--colors-reversed milenia-list--unstyled">
	<?php do_action( 'tribe_events_single_meta_venue_section_start' ) ?>

	<?php if(!empty($venue)) : ?>
		<li>
			<span><?php esc_html_e('Venue Name:', 'milenia'); ?></span>
			<?php echo esc_html($venue); ?>
		</li>
	<?php endif; ?>

	<?php if ( tribe_address_exists() ) : ?>
		<li>
			<span><?php esc_html_e('Address:', 'milenia'); ?></span>

			<address>
				<?php echo tribe_get_full_address(); ?>
			</address>
		</li>
	<?php endif; ?>

	<?php if (!empty($phone)): ?>
		<li>
			<span><?php esc_html_e( 'Phone:', 'milenia' ); ?></span>
			<?php echo esc_html($phone); ?>
		</li>
	<?php endif; ?>

	<?php if (!empty($website)): ?>
		<li>
			<span><?php esc_html_e( 'Website:', 'milenia' ); ?></span>
			<?php printf('%s', $website); ?>
		</li>
	<?php endif; ?>

	<?php do_action( 'tribe_events_single_meta_venue_section_end' ) ?>
</ul>
