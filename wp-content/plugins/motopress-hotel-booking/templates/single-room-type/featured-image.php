<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<?php if ( has_post_thumbnail() ) : ?>

	<?php

	/**
	 * @hooked \MPHB\Views\SingleRoomTypeView::_renderFeaturedImageParagraphOpen    - 10
	 */
	do_action( 'mphb_render_single_room_type_before_featured_image' );
	?>

	<?php mphb_tmpl_the_room_type_featured_image(); ?>

	<?php

	/**
	 * @hooked \MPHB\Views\SingleRoomTypeView::_renderFeaturedImageParagraphClose   - 10
	 * @hooked \MPHB\Views\SingleRoomTypeView::renderGallery                        - 20
	 */
	do_action( 'mphb_render_single_room_type_after_featured_image' );
	?>

	<?php
endif;
