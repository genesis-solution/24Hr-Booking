<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<?php if ( has_post_thumbnail() ) : ?>

	<?php

	/**
	 * @hooked \MPHB\Views\LoopServiceView::_renderFeaturedImageParagraphOpen - 10
	 */
	do_action( 'mphb_render_loop_service_before_featured_image' );
	?>

	<?php mphb_tmpl_the_loop_service_thumbnail(); ?>

	<?php

	/**
	 * @hooked \MPHB\Views\LoopServiceView::_renderFeaturedImageParagraphClose - 10
	 */
	do_action( 'mphb_render_loop_service_after_featured_image' );
	?>

	<?php
endif;
