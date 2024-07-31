<?php
/**
 * Single Event Meta (Organizer) Template
 *
 * Override this template in your own theme by creating a file at:
 * [your-theme]/tribe-events/modules/meta/organizer.php
 *
 * @package TribeEventsCalendar
 * @version 4.6.19
 */

$organizer_ids = tribe_get_organizer_ids();
$multiple = count( $organizer_ids ) > 1;

$phone = tribe_get_organizer_phone();
$email = tribe_get_organizer_email();
$website = tribe_get_organizer_website_link();
?>

<h6 class="milenia-fw-bold"><?php esc_html_e('Organizer', 'milenia'); ?></h6>

<ul class="milenia-details-list milenia-details-list--colors-reversed milenia-list--unstyled">
	<?php do_action( 'tribe_events_single_meta_organizer_section_start' ); ?>

	<?php foreach ( $organizer_ids as $organizer ) : ?>
		<?php if ( ! $organizer ) continue; ?>
		<li>
			<span><?php esc_html_e('Organizer Name:', 'milenia'); ?></span>
			<?php echo tribe_get_organizer_link( $organizer ) ?>
		</li>
	<?php endforeach; ?>

	<?php if(!$multiple) : ?>
		<?php if(!empty($phone)) : ?>
			<li>
				<span><?php esc_html_e( 'Phone:', 'milenia' ); ?></span>
				<?php echo esc_html($phone); ?>
			</li>
		<?php endif; ?>

		<?php if(!empty($email)) : ?>
			<li>
				<span><?php esc_html_e( 'Email:', 'milenia' ); ?></span>
				<a href="mailto:<?php echo esc_attr($email); ?>"><?php echo esc_html( $email ); ?></a>
			</li>
		<?php endif; ?>

		<?php if(!empty($website)) : ?>
			<li>
				<span><?php esc_html_e('Website:', 'milenia'); ?></span>
				<?php printf('%s', $website); ?>
			</li>
		<?php endif; ?>
	<?php endif; ?>

	<?php do_action( 'tribe_events_single_meta_organizer_section_end' ); ?>
</ul>
