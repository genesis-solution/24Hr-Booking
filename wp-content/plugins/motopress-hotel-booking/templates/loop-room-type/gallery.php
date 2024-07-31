<?php
/**
 * Available varialbes
 * - array $gallery Array of gallery images ids.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<?php if ( mphb_tmpl_has_room_type_gallery() ) : ?>

	<?php
	/**
	 * @hooked MPHB\Views\LoopRoomTypeView::_renderImagesWrapperOpen - 10
	 */
	do_action( 'mphb_render_loop_room_type_before_gallery' );
	?>

	<?php mphb_tmpl_the_room_type_flexslider_gallery(); ?>

	<?php
	/**
	 * @hooked \MPHB\Views\LoopRoomTypeView::_enqueueGalleryScripts - 10
	 * @hooked MPHB\Views\LoopRoomTypeView::_renderImagesWrapperClose - 20
	 */
	do_action( 'mphb_render_loop_room_type_after_gallery' );
	?>

	<?php
endif;
