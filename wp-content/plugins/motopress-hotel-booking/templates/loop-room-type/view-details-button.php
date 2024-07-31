<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<?php

/**
 * @hooked \MPHB\Views\LoopRoomTypeView::_renderViewDetailsButtonParagraphOpen - 10
 */
do_action( 'mphb_render_loop_room_type_before_view_details_button' );
?>

<?php mphb_tmpl_the_loop_room_type_view_details_button(); ?>

<?php

/**
 * @hooked \MPHB\Views\LoopRoomTypeView::_renderViewDetailsButtonParagraphClose - 10
 */
do_action( 'mphb_render_loop_room_type_after_view_details_button' );
