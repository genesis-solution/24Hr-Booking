<div class="milenia-grid-item">
    <!--================ Room Type ================-->
    <article class="<?php echo implode(' ', get_post_class('milenia-entity mphb-room-type', $room->getId())); ?>">
        <?php if(boolval($this->attributes['carousel'])) : ?>
            <?php if($room->hasGallery() && in_array($this->style, array('milenia-entities--style-15'))) : ?>
                <div class="milenia-entity-media milenia-entity-media--slideshow">
                    <div class="owl-carousel owl-carousel--vadaptive milenia-simple-slideshow milenia-simple-slideshow--shortcode">
                        <?php foreach($room->getGalleryIds() as $room_slide_id) : ?>
                            <div data-bg-image-src="<?php echo esc_url(wp_get_attachment_image_url($room_slide_id, 'entity-thumb-standard')); ?>" class="milenia-entity-slide"></div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php elseif($room->hasFeaturedImage()) : ?>
                <div class="milenia-entity-media milenia-entity-media--featured-image">
            		<div class="milenia-entity-media-inner" data-bg-image-src="<?php echo esc_url(get_the_post_thumbnail_url($room->getId(), 'entity-thumb-standard')); ?>">
            			<a href="<?php echo esc_url( get_the_permalink($room->getId()) ); ?>" class="milenia-entity-link milenia-ln--independent"></a>
            		</div>
                </div>
            <?php endif; ?>
        <?php else : ?>

			<?php if ( $show_gallery ): ?>

				<?php if($room->hasGallery() && !in_array($this->style, array('milenia-entities--style-12', 'milenia-entities--style-13'))) : ?>

					<div class="milenia-entity-media milenia-entity-media--slideshow">
						<div class="owl-carousel owl-carousel--vadaptive milenia-simple-slideshow milenia-simple-slideshow--shortcode">
							<?php foreach($room->getGalleryIds() as $room_slide_id) : ?>
								<div data-bg-image-src="<?php echo esc_url(wp_get_attachment_image_url($room_slide_id, 'entity-thumb-standard')); ?>" class="milenia-entity-slide"></div>
							<?php endforeach; ?>
						</div>
					</div>

				<?php endif; ?>

			<?php else: ?>

		        <?php if ( $room->hasFeaturedImage() ) : ?>

					<div class="milenia-entity-media milenia-entity-media--featured-image">
						<div class="milenia-entity-media-inner" data-bg-image-src="<?php echo esc_url(get_the_post_thumbnail_url($room->getId(), 'entity-thumb-standard')); ?>">
							<a href="<?php echo esc_url( get_the_permalink($room->getId()) ); ?>" class="milenia-entity-link milenia-ln--independent"></a>
						</div>
					</div>

	       		<?php endif; ?>

			<?php endif; ?>

        <?php endif; ?>

        <div class="milenia-entity-content milenia-aligner"<?php if(!empty($this->attributes['content_area_background'])) : ?> style="background-color: <?php echo esc_attr($this->attributes['content_area_background']); ?>;"<?php endif; ?>>
            <div class="milenia-aligner-outer">
                <div class="milenia-aligner-inner">
                    <header class="milenia-entity-header">
                        <div class="milenia-entity-meta">
                            <div>
                                <?php
                                    do_action( 'milenia_mphb_render_loop_room_type_before_price' );
                                    mphb_tmpl_the_room_type_default_price($room->getId());
                                    do_action( 'milenia_mphb_render_loop_room_type_after_price' );
                                ?>
                            </div>
                        </div>
                        <h2 class="milenia-entity-title"><a href="<?php echo esc_url(get_the_permalink($room->getId())); ?>" class="milenia-color--unchangeable"><?php echo esc_html($room->getTitle()); ?></a></h2>
                    </header>
                    <?php if(boolval($this->attributes['show_content'])) : ?>
                        <div class="milenia-entity-body">
                            <?php the_content(''); ?>
                        </div>
                    <?php endif; ?>
                    <?php if(boolval($this->attributes['show_button_book']) || boolval($this->attributes['show_button_details'])) : ?>
                        <footer class="milenia-entity-footer">
                            <?php if(boolval($this->attributes['show_button_details'])) : ?>
                                <?php mphb_tmpl_the_loop_room_type_view_details_button(); ?>
                            <?php endif; ?>
                            <?php if(boolval($this->attributes['show_button_book'])) : ?>
                                <?php mphb_tmpl_the_loop_room_type_book_button(); ?>
                            <?php endif; ?>
                        </footer>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </article>
    <!--================ End of Room Type ================-->
</div>
