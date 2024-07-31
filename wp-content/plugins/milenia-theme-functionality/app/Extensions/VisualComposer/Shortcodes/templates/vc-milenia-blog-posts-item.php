<?php
/**
 * Describes a base markup of the post.
 *
 * @package WordPress
 * @subpackage Milenia
 * @since Milenia 1.0
 */

// Prevent the direct loading of the file
if ( ! defined( 'ABSPATH' ) ) {
	die( esc_html__('You cannot access this file directly', 'milenia') );
}

$milenia_post_archive_style = get_query_var('milenia-post-archive-style', 'milenia-entities--style-9');
$milenia_post_archive_isotope_layout = get_query_var('milenia-post-archive-isotope-layout', 'grid');
$milenia_post_no_content = get_query_var('milenia-post-no-content', '0');
$milenia_post_no_read_more_btn = get_query_var('milenia-post-no-read-more-btn', '0');
$milenia_post_thumb_size = get_query_var('milenia-post-thumb-size', 'entity-thumb-standard');

// audio post
$milenia_soundcloud_src = get_post_meta(get_the_ID(), 'milenia-audio-soundcloud', true);

// gallery post
$milenia_gallery = get_post_meta(get_the_ID(), 'milenia-post-gallery', false);

// link post
$milenia_link_text = get_post_meta(get_the_ID(), 'milenia-post-link-text', true);
$milenia_link_url = get_post_meta(get_the_ID(), 'milenia-post-link-url', true);
$milenia_link_target = get_post_meta(get_the_ID(), 'milenia-post-link-target', true);
$milenia_link_nofollow = get_post_meta(get_the_ID(), 'milenia-post-link-nofollow', true);

// quote post
$milenia_quote = get_post_meta(get_the_ID(), 'milenia-post-quote', true);
$milenia_quote_author = get_post_meta(get_the_ID(), 'milenia-post-quote-author', true);
$milenia_quote_author_link = get_post_meta(get_the_ID(), 'milenia-post-quote-author-link', true);
$milenia_quote_author_link_target = get_post_meta(get_the_ID(), 'milenia-post-quote-author-link-target', true);
$milenia_quote_author_link_nofollow = get_post_meta(get_the_ID(), 'milenia-post-quote-author-link-nofollow', true);

// video post
$milenia_is_selfhosted = get_post_meta(get_the_ID(), 'milenia-video-selfhosted-state', true);
$milenia_src_outer = get_post_meta(get_the_ID(), 'milenia-video-src-outer', true);
$milenia_src_selfhosted = get_post_meta(get_the_ID(), 'milenia-video-src-selfhosted', false);

$milenia_post_classes = array('milenia-entity');

if($milenia_post_no_content) array_push($milenia_post_classes, 'text-center');

if(!has_post_thumbnail()) array_push($milenia_post_classes, 'milenia-entity--without-thumb');
?>

<div <?php post_class('milenia-grid-item'); ?>>
    <!-- - - - - - - - - - - - - - Entity - - - - - - - - - - - - - - - - -->
    <article <?php post_class($milenia_post_classes); ?>>
		<?php if(strpos($milenia_post_archive_style, 'milenia-entities--without-media') == false) : ?>
			<?php if($milenia_post_archive_style == 'milenia-entities--style-8') : ?>
				<?php if( has_post_thumbnail() && strpos($milenia_post_archive_style, 'milenia-entities--without-media') == false ) : ?>
					<!-- - - - - - - - - - - - - - Entity Media - - - - - - - - - - - - - - - - -->
					<?php if($milenia_post_archive_style == 'milenia-entities--style-8') : ?>
						<div class="milenia-entity-media" data-bg-image-src="<?php echo esc_url(get_the_post_thumbnail_url(get_the_ID(), $milenia_post_thumb_size)); ?>">
							<?php if($milenia_post_archive_isotope_layout == 'masonry' && empty(get_post_format())) : ?>
								<a class="milenia-ln--independent" href="<?php the_permalink(); ?>">
									<?php the_post_thumbnail($milenia_post_thumb_size); ?>
								</a>
							<?php endif; ?>
						</div>
					<?php else : ?>
						<div class="milenia-entity-media">
							<a class="milenia-ln--independent" href="<?php the_permalink(); ?>">
								<?php the_post_thumbnail($milenia_post_thumb_size); ?>
							</a>
						</div>
					<?php endif; ?>
					<!-- - - - - - - - - - - - - - End of Entry Media - - - - - - - - - - - - - - - - -->
				<?php endif; ?>
			<?php else :
				switch(get_post_format()) {
					case 'audio' : ?>
						<?php if(!empty($milenia_soundcloud_src)) : ?>
							<!-- - - - - - - - - - - - - - Entity Media - - - - - - - - - - - - - - - - -->
						    <div class="milenia-entity-media">
						        <div class="milenia-fullwidth-iframe">
						          <?php echo wp_kses($milenia_soundcloud_src, array('iframe' => array('src' => true))); ?>
						        </div>
						    </div>
						    <!-- - - - - - - - - - - - - - End of Entry Media - - - - - - - - - - - - - - - - -->
						<?php endif; ?>
					<?php break;
					case 'gallery' : ?>
						<?php if(!empty($milenia_gallery)) : ?>
							<!-- - - - - - - - - - - - - - Entity Media - - - - - - - - - - - - - - - - -->
							<div class="milenia-entity-media">
								<div class="owl-carousel milenia-simple-slideshow milenia-simple-slideshow--shortcode">
									<?php foreach($milenia_gallery as $attachment_id) : ?>
										<?php echo wp_get_attachment_image($attachment_id, $milenia_post_thumb_size, false, array(
											'class' => 'owl-carousel-img'
										)); ?>
									<?php endforeach; ?>
								</div>
							</div>
							<!-- - - - - - - - - - - - - - End of Entry Media - - - - - - - - - - - - - - - - -->
						<?php endif; ?>
					<?php break;
					case 'link' : ?>
						<?php if(!empty($milenia_link_text)) : ?>
							<!-- - - - - - - - - - - - - - Entity Media - - - - - - - - - - - - - - - - -->
							<div class="milenia-entity-media">
								<a class="milenia-entity-link-element milenia-ln--independent"
								   href="<?php echo !empty($milenia_link_url) ? esc_url($milenia_link_url) : '#'; ?>"
								   target="<?php echo esc_attr($milenia_link_target == '1' ? '_blank' : '_self'); ?>"
								   <?php if($milenia_link_nofollow == '1') : ?>rel="nofollow"<?php endif; ?>>
									<span class="icon icon-link2"></span>
									<?php echo esc_html($milenia_link_text); ?>
								</a>
							</div>
							<!-- - - - - - - - - - - - - - End of Entry Media - - - - - - - - - - - - - - - - -->
						<?php endif; ?>
					<?php break;
					case 'quote' : ?>
						<?php if(!empty($milenia_quote) || !empty($milenia_quote_author)) : ?>
						    <!-- - - - - - - - - - - - - - Entity Media - - - - - - - - - - - - - - - - -->
						    <div class="milenia-entity-media">
						        <blockquote>
									<?php if(boolval($this->attributes['milenia_reduce_bq_characters'])) : ?>
										<?php echo wp_kses_post(wpautop(substr($milenia_quote, 0, 60) . '...')); ?>
									<?php else : ?>
										<?php echo wp_kses_post(wpautop($milenia_quote)); ?>
									<?php endif; ?>


									<?php if(!empty($milenia_quote_author) && !boolval($this->attributes['milenia_reduce_bq_characters'])) : ?>
						            	<cite>
											<?php if(!empty($milenia_quote_author_link)) : ?>
												<a href="<?php echo esc_url($milenia_quote_author_link); ?>"
												   target="<?php echo esc_attr( $milenia_quote_author_link_target == '1' ? '_blank' : '_self' ); ?>"
												   <?php if($milenia_quote_author_link_nofollow == '1'): ?>rel="nofollow"<?php endif;?>>
											<?php endif; ?>
												<?php echo wp_kses_post($milenia_quote_author); ?>
											<?php if(!empty($milenia_quote_author_link)) : ?>
												</a>
											<?php endif; ?>
										</cite>
									<?php endif; ?>
						        </blockquote>
						    </div>
						    <!-- - - - - - - - - - - - - - End of Entry Media - - - - - - - - - - - - - - - - -->
						<?php endif; ?>
					<?php break;
					case 'video' : ?>
						<?php if(!empty($milenia_src_outer) || !empty($milenia_src_selfhosted)) : ?>
						    <!-- - - - - - - - - - - - - - Entity Media - - - - - - - - - - - - - - - - -->
						    <div class="milenia-entity-media">
						        <?php if($milenia_is_selfhosted && !empty($milenia_src_selfhosted)) : ?>
						            <?php foreach($milenia_src_selfhosted as $video_id) : ?>
						                <div class="milenia-selfhosted-video">
						                    <video src="<?php echo esc_attr(wp_get_attachment_url($video_id)); ?>" style="max-width:100%" class="mejs__player" data-mejsoptions='{"pluginPath": "<?php echo esc_attr(MILENIA_FUNCTIONALITY_ROOT . '/visual-composer/assets/vendors/mediaelement/'); ?>","poster": "<?php echo esc_attr(get_the_post_thumbnail_url(get_the_ID())); ?>","hideVideoControlsOnLoad": true,"showPosterWhenPaused": true,"controlsTimeoutMouseEnter": 1000}'></video>
						                </div>
						            <?php endforeach; ?>
						        <?php elseif(!$milenia_is_selfhosted && !empty($milenia_src_outer)) : ?>
						            <div class="milenia-responsive-iframe">
						              <?php echo wp_kses($milenia_src_outer, array(
										  'iframe' => array(
											  'src' => true
										  )
									  )); ?>
						            </div>
						        <?php endif; ?>
						    </div>
						    <!-- - - - - - - - - - - - - - End of Entry Media - - - - - - - - - - - - - - - - -->
						<?php endif; ?>
					<?php break;
					default : ?>
						<?php if( has_post_thumbnail() && strpos($milenia_post_archive_style, 'milenia-entities--without-media') == false ) : ?>
							<!-- - - - - - - - - - - - - - Entity Media - - - - - - - - - - - - - - - - -->
							<?php if($milenia_post_archive_style == 'milenia-entities--style-8') : ?>
								<div class="milenia-entity-media" data-bg-image-src="<?php echo esc_url(get_the_post_thumbnail_url(get_the_ID(), $milenia_post_thumb_size)); ?>">
									<?php if($milenia_post_archive_isotope_layout == 'masonry' && empty(get_post_format())) : ?>
										<a class="milenia-ln--independent" href="<?php the_permalink(); ?>">
											<?php the_post_thumbnail($milenia_post_thumb_size); ?>
										</a>
									<?php endif; ?>
								</div>
							<?php else : ?>
								<div class="milenia-entity-media">
									<a class="milenia-ln--independent" href="<?php the_permalink(); ?>">
										<?php the_post_thumbnail($milenia_post_thumb_size); ?>
									</a>
								</div>
							<?php endif; ?>
							<!-- - - - - - - - - - - - - - End of Entry Media - - - - - - - - - - - - - - - - -->
						<?php endif; ?>
					<?php break;
				}
			?>
			<?php endif; ?>
		<?php endif; ?>

        <!-- - - - - - - - - - - - - - Entity Content - - - - - - - - - - - - - - - - -->
        <div class="milenia-entity-content milenia-aligner">
            <div class="milenia-aligner-outer">
                <div class="milenia-aligner-inner">
                    <header class="milenia-entity-header">
						<?php if($milenia_post_archive_style == 'milenia-entities--style-8' && in_array(get_post_format(), array('quote', 'link', 'gallery', 'video', 'audio'))) : ?>
							<div class="milenia-entity-icon">
								<?php
									switch(get_post_format()) {
										case 'quote' : ?>
											<i class="icon icon-quote-open"></i>
										<?php break;
										case 'link' : ?>
											<i class="icon icon-link2"></i>
										<?php break;
										case 'gallery' : ?>
											<i class="icon icon-pictures"></i>
										<?php break;
										case 'video' : ?>
											<i class="icon icon-film-play"></i>
										<?php break;
										case 'audio' : ?>
											<i class="icon icon-music-note3"></i>
										<?php break;
									}
								?>
							</div>
						<?php endif; ?>

						<div class="milenia-entity-meta">
							<div>
								<time datetime="<?php echo esc_attr(get_the_date('c')); ?>" class="milenia-entity-publish-date">
									<a href="<?php the_permalink(); ?>" class="milenia-ln--independent"><?php echo get_the_date(get_option('date_format')); ?></a>
								</time>
							</div>
							<div>
								<?php _e('by', 'milenia'); ?>
								<a href="<?php echo esc_url( get_author_posts_url( get_the_author_meta( 'ID' ) ) ); ?>"><?php the_author(); ?></a>
							</div>
							<?php if(milenia_has_post_terms(get_the_ID())) : ?>
								<div>
									<?php _e('in', 'milenia'); ?>
									<?php echo milenia_get_post_terms(get_the_ID()); ?>
								</div>
							<?php endif; ?>

							<?php if(is_user_logged_in()) : ?>
								<div><?php edit_post_link(__('Edit', 'milenia'), null, null, get_the_ID(), 'milenia-entity-edit-link'); ?></div>
							<?php endif; ?>
						</div>

						<?php if(is_sticky() || post_password_required()) : ?>
							<div class="milenia-entity-labels">
								<?php if(is_sticky() && in_array($milenia_post_archive_style, array('milenia-entities--style-7', 'milenia-entities--style-8'))) : ?>
									<span class="milenia-entity-label"><?php esc_html_e('Sticky', 'milenia-app-textdomain'); ?></span>
								<?php endif; if(post_password_required() && in_array($milenia_post_archive_style, array('milenia-entities--style-7', 'milenia-entities--style-8'))) : ?>
									<span class="milenia-entity-label"><?php esc_html_e('Password protected', 'milenia-app-textdomain'); ?></span>
								<?php endif; ?>
							</div>
						<?php endif; ?>

                        <h2 class="milenia-entity-title">
							<?php if(is_sticky() && !in_array($milenia_post_archive_style, array('milenia-entities--style-7', 'milenia-entities--style-8'))) : ?>
								<span class="milenia-entity-label"><?php esc_html_e('Sticky', 'milenia-app-textdomain'); ?></span>
							<?php endif; if(post_password_required() && !in_array($milenia_post_archive_style, array('milenia-entities--style-7', 'milenia-entities--style-8'))) : ?>
								<span class="milenia-entity-label"><?php esc_html_e('Password protected', 'milenia-app-textdomain'); ?></span>
							<?php endif; ?>
                            <a href="<?php the_permalink(); ?>"><?php echo get_the_title(); ?></a>
                        </h2>
                    </header>

					<?php if($milenia_post_archive_style != 'milenia-entities--style-8') : ?>
						<?php if(!$milenia_post_no_content) : ?>
							<?php if(has_excerpt() || is_search() || strpos($milenia_post_archive_style, 'milenia-entities--list') != false ): ?>
								<div class="milenia-entity-body"><?php the_excerpt(); ?></div>
							<?php else : ?>
								<div class="milenia-entity-body"><?php the_content(''); ?></div>
								<?php wp_link_pages( array(
									'before' => '<div class="milenia-page-links"><span class="milenia-page-links-title">' . esc_html__( 'Pages:', 'milenia' ) . '</span>',
									'after' => '</div>',
									'link_before' => '<span>',
									'link_after' => '</span>'
								) ); ?>
							<?php endif; ?>
						<?php endif; ?>
					<?php endif; ?>

					<?php if(!$milenia_post_no_read_more_btn) : ?>
	                    <footer class="milenia-entity-footer">
							<?php if($milenia_post_archive_style == 'milenia-entities--style-8') : ?>
								<a href="<?php the_permalink(); ?>" class="milenia-btn milenia-btn--link milenia-btn--scheme-white"><?php _e('Read More', 'milenia'); ?></a>
							<?php else : ?>
								<a href="<?php the_permalink(); ?>" class="milenia-btn milenia-btn--link milenia-btn--scheme-primary"><?php _e('Read More', 'milenia'); ?></a>
							<?php endif; ?>
	                    </footer>
					<?php endif; ?>
                </div>
        	</div>
		</div>
		<!-- - - - - - - - - - - - - - End of Entity Content - - - - - - - - - - - - - - - - -->
	</article>
    <!-- - - - - - - - - - - - - - End of Entry - - - - - - - - - - - - - - - - -->
</div>
