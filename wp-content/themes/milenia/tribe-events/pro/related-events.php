<?php
/**
 * Related Events Template
 * The template for displaying related events on the single event page.
 *
 * You can recreate an ENTIRELY new related events view by doing a template override, and placing
 * a related-events.php file in a tribe-events/pro/ directory within your theme directory, which
 * will override the /views/pro/related-events.php.
 *
 * You can use any or all filters included in this file or create your own filters in
 * your functions.php. In order to modify or extend a single filter, please see our
 * readme on templates hooks and filters
 *
 * @package TribeEventsCalendarPro
 * @version 4.4.28
 *
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( esc_html__('You cannot access this file directly', 'milenia') );
}

$posts = tribe_get_related_posts();

if ( is_array( $posts ) && ! empty( $posts ) ) : ?>

<section class="milenia-section">
	<h3><?php printf( __( 'Related %s', 'milenia' ), tribe_get_event_label_plural() ); ?></h3>

	<div class="milenia-entities milenia-entities--style-19">
		<div class="milenia-grid milenia-grid--cols-3">
			<?php foreach ( $posts as $post ) : ?>
				<div class="milenia-grid-item">
					<article class="milenia-entity format-standard">
						<?php if(has_post_thumbnail($post->ID)) : ?>
							<div data-bg-image-src="<?php echo esc_url(get_the_post_thumbnail_url($post->ID, 'entity-thumb-standard')); ?>" class="milenia-entity-media">
								<a href="<?php echo esc_url( tribe_get_event_link( $post ) ) ?>" class="milenia-ln--independent">
									<?php echo get_the_post_thumbnail($post->ID, 'entity-thumb-standard'); ?>
								</a>
							</div>
						<?php else : ?>
							<div data-bg-image-src="<?php echo esc_url(esc_url( trailingslashit( Tribe__Events__Pro__Main::instance()->pluginUrl ) . 'src/resources/images/tribe-related-events-placeholder.png' )); ?>" class="milenia-entity-media">
								<a href="<?php echo esc_url( tribe_get_event_link( $post ) ) ?>" class="milenia-ln--independent">
									<img src="<?php esc_url( trailingslashit( Tribe__Events__Pro__Main::instance()->pluginUrl ) . 'src/resources/images/tribe-related-events-placeholder.png' ); ?>" alt="<?php echo esc_attr(get_the_title( $post->ID)); ?>" />
								</a>
							</div>
						<?php endif; ?>

						<div class="milenia-entity-content milenia-aligner">
							<div class="milenia-aligner-outer">
								<div class="milenia-aligner-inner">
									<header class="milenia-entity-header">
										<div class="mphb-price">
											<?php if(tribe_get_cost($post->ID)) : ?>
												<?php
													echo tribe_get_cost( $post->ID, true );
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
												<time datetime="<?php echo esc_attr(tribe_get_start_date(get_post($post->ID), false, 'c')); ?>"><?php echo tribe_events_event_schedule_details($post->ID); ?></time>
											</div>
											<?php if(!empty(tribe_get_address($post->ID)) && !empty(tribe_get_city($post->ID))) : ?>
												<div class="milenia-entity-date">
													<?php printf('%s, %s', tribe_get_address($post->ID), tribe_get_city($post->ID)); ?>
												</div>
											<?php endif; ?>
										</div>
										<?php do_action( 'tribe_events_after_the_meta' ) ?>

										<?php do_action( 'tribe_events_before_the_event_title' ) ?>
										<h2 class="milenia-entity-title">
											<a href="<?php echo esc_url( tribe_get_event_link($post->ID) ); ?>" rel="bookmark" class="milenia-color--unchangeable"><?php echo get_the_title($post->ID); ?></a>
										</h2>
										<?php do_action( 'tribe_events_after_the_event_title' ) ?>
									</header>

									<?php do_action( 'tribe_events_before_the_content' ); ?>
									<div class="milenia-entity-body">
										<?php echo tribe_events_get_the_excerpt( $post->ID, wp_kses_allowed_html( 'post' ) ); ?>
									</div>

									<footer class="milenia-entity-footer">
										<a href="<?php echo esc_url( tribe_get_event_link($post->ID) ); ?>" class="milenia-btn"><?php esc_html_e( 'More Details', 'milenia' ); ?></a>

										<div class="milenia-entity-extra-actions">
											<button type="button" data-arctic-modal="#share-modal-<?php echo esc_attr($post->ID); ?>" class="milenia-icon-btn">
								                <i class="icon icon-share2"></i>
								            </button>

								            <div class="milenia-d-none">
								                <div id="share-modal-<?php echo esc_attr($post->ID); ?>" aria-hidden="true" class="milenia-modal milenia-modal--share">
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

											<a href="<?php echo esc_url(tribe_get_map_link($post->ID)); ?>" class="milenia-ln--independent milenia-icon-btn">
												<i class="icon icon-map-marker"></i>
											</a>

											<?php if($milenia_organizer_email = tribe_get_organizer_email($post->ID)) : ?>
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
				</div>
			<?php endforeach; ?>
		</div>
	</div>


</section>
<?php
endif;
