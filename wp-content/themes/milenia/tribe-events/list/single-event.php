<?php
/**
 * List View Single Event
 * This file contains one event in the list view
 *
 * Override this template in your own theme by creating a file at [your-theme]/tribe-events/list/single-event.php
 *
 * @version 4.6.19
 *
 */
if ( ! defined( 'ABSPATH' ) ) {
	die( esc_html__('You cannot access this file directly', 'milenia') );
}

// Setup an array of venue details for use later in the template
$venue_details = tribe_get_venue_details();

// The address string via tribe_get_venue_details will often be populated even when there's
// no address, so let's get the address string on its own for a couple of checks below.
$venue_address = tribe_get_address();

// Venue
$has_venue_address = ( ! empty( $venue_details['address'] ) ) ? ' location' : '';

// Organizer
$organizer = tribe_get_organizer();

global $post;
?>

<article class="milenia-entity format-standard">
	<div class="milenia-entity-extra-data">
		<?php
			$milenia_current_post = get_post();

			$milenia_start_day = tribe_get_start_date($milenia_current_post, false, 'j');
			$milenia_start_month = tribe_get_start_date($milenia_current_post, false, 'F');
			$milenia_start_year = tribe_get_start_date($milenia_current_post, false, 'Y');

			$milenia_end_day = tribe_get_end_date($milenia_current_post, false, 'j');
			$milenia_end_month = tribe_get_end_date($milenia_current_post, false, 'F');
			$milenia_end_year = tribe_get_end_date($milenia_current_post, false, 'Y');

			$milenia_same_day = $milenia_start_day == $milenia_end_day;
			$milenia_same_month = $milenia_start_month == $milenia_end_month;
			$milenia_same_year = $milenia_start_year == $milenia_end_year;
		?>

		<div class="milenia-entity-date-date">
			<?php if(!$milenia_same_day || !$milenia_same_month || !$milenia_same_year) : ?>
				<?php echo esc_html(sprintf('%s-%s', $milenia_start_day, $milenia_end_day)); ?>
			<?php else : ?>
				<?php echo esc_html($milenia_start_day); ?>
			<?php endif; ?>
		</div>
		<div class="milenia-entity-date-month-year">
			<?php if(!$milenia_same_month || !$milenia_same_year) : ?>
				<?php echo esc_html(sprintf('%s-%s', $milenia_start_month, $milenia_end_month)); ?>,
			<?php else : ?>
				<?php echo esc_html($milenia_start_month); ?>,
			<?php endif; ?>
			<?php if(!$milenia_same_year) : ?>
				<?php echo esc_html(sprintf('%s-%s', $milenia_start_year, $milenia_end_year)); ?>
			<?php else : ?>
				<?php echo esc_html($milenia_start_year); ?>
			<?php endif; ?>
		</div>
	</div>

	<?php if(has_post_thumbnail()) : ?>
		<div class="milenia-entity-media">
			<div class="milenia-entity-media-inner" data-bg-image-src="<?php echo esc_url(get_the_post_thumbnail_url(get_the_ID(), 'entity-thumb-standard')); ?>">
				<a href="<?php echo esc_url( tribe_get_event_link() ); ?>" class="milenia-entity-link milenia-ln--independent"></a>
			</div>
		</div>
	<?php endif; ?>


	<div class="milenia-entity-content milenia-aligner">
		<div class="milenia-aligner-outer">
			<div class="milenia-aligner-inner">
				<header class="milenia-entity-header">
					<div class="mphb-price">
						<?php if(tribe_get_cost()) : ?>
							<?php
								echo tribe_get_cost( null, true );
								/**
								 * Runs after cost is displayed in list style views
								 *
								 * @since 4.5
								 */
								do_action( 'tribe_events_inside_cost' )
							?>
						<?php else : ?>
							<?php esc_html_e('Free', 'milenia'); ?>
						<?php endif; ?>
					</div>

					<?php do_action( 'tribe_events_before_the_meta' ) ?>
					<div class="milenia-entity-meta">
						<div>
							<time datetime="<?php echo esc_attr(tribe_get_start_date($milenia_current_post, false, 'c')); ?>"><?php echo tribe_events_event_schedule_details() ?></time>
						</div>
						<?php if(!empty(tribe_get_address()) && !empty(tribe_get_city())) : ?>
							<div class="milenia-entity-date">
								<?php printf('%s, %s', tribe_get_address(), tribe_get_city()); ?>
							</div>
						<?php endif; ?>
					</div>
					<?php do_action( 'tribe_events_after_the_meta' ) ?>

					<?php do_action( 'tribe_events_before_the_event_title' ) ?>
					<h2 class="milenia-entity-title">
						<a href="<?php echo esc_url( tribe_get_event_link() ); ?>" title="<?php the_title_attribute() ?>" rel="bookmark" class="milenia-color--unchangeable"><?php the_title() ?></a>
					</h2>
					<?php do_action( 'tribe_events_after_the_event_title' ) ?>
				</header>

				<?php do_action( 'tribe_events_before_the_content' ); ?>
				<div class="milenia-entity-body">
					<?php echo tribe_events_get_the_excerpt( null, wp_kses_allowed_html( 'post' ) ); ?>
				</div>

				<footer class="milenia-entity-footer">
					<a href="<?php echo esc_url( tribe_get_event_link() ); ?>" class="milenia-btn"><?php esc_html_e( 'More Details', 'milenia' ); ?></a>

					<div class="milenia-entity-extra-actions">
						<button type="button" data-arctic-modal="#share-modal-<?php echo esc_attr(get_the_ID()); ?>" class="milenia-icon-btn">
			                <i class="icon icon-share2"></i>
			            </button>

			            <div class="milenia-d-none">
			                <div id="share-modal-<?php echo esc_attr(get_the_ID()); ?>" aria-hidden="true" class="milenia-modal milenia-modal--share">
			                    <button type="button" class="milenia-icon-btn arcticmodal-close"><i class="icon icon-cross"></i></button>
			                    <h3><?php esc_html_e('Share On', 'milenia'); ?>:</h3>

			                    <div class="milenia-share-buttons">
									<a href="#" target="_blank" title="<?php esc_attr_e('Facebook', 'milenia'); ?>" class="milenia-btn milenia-btn--icon milenia-btn--scheme-facebook milenia-sharer--facebook"
		                                data-sharer-url="<?php echo esc_url(get_the_permalink($post->ID)); ?>"
		                                data-sharer-thumbnail="<?php echo esc_url(wp_get_attachment_url(get_post_thumbnail_id($post->ID))); ?>"
		                                data-sharer-title="<?php echo esc_attr(get_the_title($post->ID)); ?>">
		                                <span class="fab fa-facebook-f"></span> <?php esc_html_e('Facebook', 'milenia'); ?>
		                            </a>
		                            <a href="#" target="_blank" title="<?php esc_attr_e('Twitter', 'milenia'); ?>" class="milenia-btn milenia-btn--icon milenia-btn--scheme-twitter milenia-sharer--twitter"
		                                data-sharer-text="<?php echo esc_attr(get_the_title($post->ID)); ?>"
		                                data-sharer-url="<?php echo esc_url(get_the_permalink($post->ID)); ?>">
		                                <span class="fab fa-twitter"></span> <?php esc_html_e('Twitter', 'milenia'); ?>
		                            </a>
		                            <a href="#" target="_blank" title="<?php esc_attr_e('Google+', 'milenia'); ?>" class="milenia-btn milenia-btn--icon milenia-btn--scheme-google-plus milenia-sharer--google-plus"
		                                data-sharer-url="<?php echo esc_url(get_the_permalink($post->ID)); ?>">
		                                <span class="fab fa-google-plus-g"></span> <?php esc_html_e('Google +', 'milenia'); ?>
		                            </a>
		                            <a href="#" target="_blank" title="<?php esc_attr_e('Pinterest', 'milenia') ?>" class="milenia-btn milenia-btn--icon milenia-btn--scheme-pinterest milenia-sharer--pinterest"
		                                data-sharer-url="<?php echo esc_url(get_the_permalink($post->ID)); ?>"
		                                data-sharer-media="<?php echo esc_url(wp_get_attachment_url( get_post_thumbnail_id($post->ID) )); ?>"
		                                data-sharer-description="<?php echo esc_attr(get_the_title($post->ID)); ?>">
		                                <span class="fab fa-pinterest-p"></span> <?php esc_html_e('Pinterest', 'milenia'); ?>
		                            </a>
		                            <a href="mailto:#&subject=<?php echo urlencode(get_the_title($post->ID)); ?>&body=<?php echo esc_url(get_the_permalink($post->ID)); ?>" class="milenia-btn milenia-btn--icon">
		                                <span class="fas fa-envelope"></span> <?php esc_html_e('Email to a Friend', 'milenia'); ?>
		                            </a>
								</div>
			                </div>
			            </div>

						<a href="<?php echo esc_url(tribe_get_map_link()); ?>" target="_blank" class="milenia-ln--independent milenia-icon-btn">
							<i class="icon icon-map-marker"></i>
						</a>

						<?php if($milenia_organizer_email = tribe_get_organizer_email()) : ?>
							<a href="mailto:<?php echo esc_attr($milenia_organizer_email); ?>" class="milenia-ln--independent milenia-icon-btn">
								<i class="icon icon-at-sign"></i>
							</a>
						<?php endif; ?>
					</div>
				</footer>
				<?php do_action( 'tribe_events_after_the_content' ); ?>
			</div>
		</div>
	</div>
</article>
