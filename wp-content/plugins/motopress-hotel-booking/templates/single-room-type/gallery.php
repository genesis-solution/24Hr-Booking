<?php
/**
 * Available variables
 * - array $galleryIds Array of gallery images ids.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<?php if ( mphb_tmpl_has_room_type_gallery() ) : ?>

	<?php
	do_action( 'mphb_render_single_room_type_before_gallery' );
	?>

	<?php mphb_tmpl_the_single_room_type_gallery(); ?>

	<?php

	/**
	 * @hooked \MPHB\Views\SingleRoomTypeView::_enqueueGalleryScripts - 10
	 */
	do_action( 'mphb_render_single_room_type_after_gallery' );
	?>

	<?php
endif;
