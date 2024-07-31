<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<?php if ( mphb_tmpl_has_room_type_default_price() ) : ?>

	<?php

	/**
	 * @hooked \MPHB\Views\LoopRoomTypeView::_renderPriceParagraphOpen  - 10
	 * @hooked \MPHB\Views\LoopRoomTypeView::_renderPriceTitle          - 20
	 */
	do_action( 'mphb_render_loop_room_type_before_price' );
	?>

	<?php mphb_tmpl_the_room_type_default_price(); ?>

	<?php

	/**
	 * @hooked \MPHB\Views\LoopRoomTypeView::_renderPriceParagraphClose - 10
	 */
	do_action( 'mphb_render_loop_room_type_after_price' );
	?>

	<?php
endif;
