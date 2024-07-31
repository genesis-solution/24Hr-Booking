<?php
/**
 * Single Event Template
 * A single event. This displays the event title, description, meta, and
 * optionally, the Google map for the event.
 *
 * Override this template in your own theme by creating a file at [your-theme]/tribe-events/single-event.php
 *
 * @package TribeEventsCalendar
 * @version 4.6.19
 *
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( esc_html__('You cannot access this file directly', 'milenia') );
}

$events_label_singular = tribe_get_event_label_singular();
$events_label_plural   = tribe_get_event_label_plural();
$event = get_post();
$event_id = get_the_ID();
?>
<?php while ( have_posts() ) :  the_post(); ?>

	<!-- - - - - - - - - - - - - - Content Section - - - - - - - - - - - - - -->
	<div id="post-<?php the_ID(); ?>" <?php post_class('milenia-entity-single milenia-entity--event milenia-section milenia-section--py-small'); ?>>
		<header class="milenia-entity-header" <?php tribe_events_the_header_attributes(); ?>>
			<?php if(!empty(get_the_title())) : ?>
				<h1 class="milenia-entity-title text-center"><?php the_title() ?></h1>
			<?php endif; ?>

			<nav>
				<ul class="milenia-list--unstyled milenia-pagination milenia-pagination--stretched milenia-pagination--independent">
					<li class="milenia-pagination--pushed-to-left-md">
						<a href="<?php echo esc_url( tribe_get_events_link() ); ?>" class="prev"><?php printf( esc_html_x( 'All %s', '%s Events plural label', 'milenia' ), $events_label_plural ); ?></a>
					</li>
					<li class="milenia-pagination--pushed-to-right-md"><a href="<?php echo esc_url(tribe_get_gcal_link()); ?>" class="pull-right"><?php esc_html_e( '+ Google Calendar', 'milenia'); ?></a></li>
					<li><a href="<?php echo esc_url(tribe_get_single_ical_link()); ?>" class="pull-right"><?php esc_html_e('+ Export to Calendar', 'milenia'); ?></a></li>
				</ul>
			</nav>

			<!-- Notices -->
			<?php tribe_the_notices() ?>
		</header>

		<div class="row">
			<div class="col-lg-8">
				<?php if(has_post_thumbnail()) : ?>
					<!-- - - - - - - - - - - - - - Event Image - - - - - - - - - - - - - -->
					<div class="milenia-entity-media">
						<?php the_post_thumbnail('entity-thumb-standard'); ?>
					</div>
					<!-- - - - - - - - - - - - - - End of Event Image - - - - - - - - - - - - - -->
				<?php endif; ?>


				<div class="milenia-entity-extra-data">
					<?php do_action( 'milenia_tribe_events_single_event_before_the_meta' ) ?>
					<div class="row">
						<div class="col-lg-6">
							<?php tribe_get_template_part( 'modules/meta/details' ); ?>
						</div>

						<div class="col-lg-6">
							<?php tribe_get_template_part( 'modules/meta/organizer' ); ?>
						</div>
					</div>
					<?php do_action( 'milenia_tribe_events_single_event_after_the_meta' ) ?>
				</div>

				<?php do_action( 'tribe_events_single_event_before_the_content' ) ?>
				<?php if(!empty(get_the_content())) : ?>
					<div class="milenia-entity-content">
						<?php the_content(); ?>
					</div>
				<?php endif; ?>
				<?php do_action( 'milenia_tribe_events_single_event_after_the_content' ) ?>


				<?php do_action('milenia_single_post_after_content', get_post(), true); ?>
			</div>

			<aside class="col-lg-4">
				<?php tribe_get_template_part( 'modules/meta/map' ); ?>
				<?php tribe_get_template_part( 'modules/meta/venue' ); ?>
			</aside>
		</div>
	</div>
	<!-- - - - - - - - - - - - - - End of Content Section - - - - - - - - - - - - - -->
<?php endwhile; ?>

<!-- - - - - - - - - - - - - - Content Section - - - - - - - - - - - - - -->
<div class="milenia-section">
	<nav aria-label="<?php printf( esc_html__( '%s Navigation', 'milenia' ), $events_label_singular ); ?>">
		<ul class="milenia-list--unstyled milenia-posts-navigation">
			<li class="milenia-posts-navigation-prev">
				<span><?php tribe_the_prev_event_link('%title%'); ?></span>
			</li>
			<li class="milenia-posts-navigation-next">
				<span><?php tribe_the_next_event_link('%title%'); ?></span>
			</li>
		</ul>
	</nav>
</div>
<!-- - - - - - - - - - - - - - End of Content Section - - - - - - - - - - - - - -->

<?php do_action( 'tribe_events_single_event_after_the_meta' ); ?>

<?php if ( comments_open() && get_post_type() == Tribe__Events__Main::POSTTYPE && tribe_get_option( 'showComments', false ) ) comments_template(); ?>
