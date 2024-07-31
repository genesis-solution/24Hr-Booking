<?php
/**
 * Available varialbes
 * - array $gallery Array of gallery images ids.
 */

if ( !defined( 'ABSPATH' ) ) {
	exit;
}
?>

<?php if ( mphb_tmpl_has_room_type_gallery() ) : ?>

	<?php
	/**
	 * @hooked MPHB\Views\LoopRoomTypeView::_renderImagesWrapperOpen - 10
	 */
	do_action( 'milenia_mphb_render_loop_room_type_before_gallery' );
	?>

	<?php
		$galleryIds = mphb_tmpl_get_room_type_gallery_ids();

		if(is_array($galleryIds) && !empty($galleryIds)) : ?>
			<div class="milenia-entity-media milenia-entity-media--slideshow">
				<div class="owl-carousel owl-carousel--vadaptive milenia-simple-slideshow">
					<?php foreach($galleryIds as $gallery_image_id) : ?>
						<div data-bg-image-src="<?php echo esc_url(wp_get_attachment_image_url($gallery_image_id, 'entity-thumb-size-rectangle')); ?>" class="milenia-entity-slide"></div>
					<?php endforeach; ?>
				</div>
			</div>

		<?php endif; ?>

	<?php
	/**
	 * @hooked \MPHB\Views\LoopRoomTypeView::_enqueueGalleryScripts - 10
	 * @hooked MPHB\Views\LoopRoomTypeView::_renderImagesWrapperClose - 20
	 */
	do_action( 'milenia_mphb_render_loop_room_type_after_gallery' );
	?>

<?php endif; ?>
