<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<?php

/**
 * @hooked \MPHB\Views\LoopRoomTypeView::_renderBookButtonWrapperOpen - 10
 */
do_action( 'mphb_render_loop_room_type_before_book_button' );
?>

<?php mphb_tmpl_the_loop_room_type_book_button_form(); ?>

<?php

/**
 * @hooked \MPHB\Views\LoopRoomTypeView::_renderBookButtonBr - 10
 * @hooked \MPHB\Views\LoopRoomTypeView::_renderBookButtonWrapperClose - 20
 */
do_action( 'mphb_render_loop_room_type_after_book_button' );

