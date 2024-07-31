<?php

/**
 * Single Room title
 *
 * This template can be overridden by copying it to %theme%/hotel-booking/single-room-type/title.php.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<?php

/**
 * @hooked \MPHB\Views\SingleRoomTypeView::_renderTitleHeadingOpen - 10
 */
do_action( 'mphb_render_single_room_type_before_title' );
?>

<?php the_title(); ?>

<?php

/**
 * @hooked \MPHB\Views\SingleRoomTypeView::_renderTitleHeadingClose - 10
 */
do_action( 'mphb_render_single_room_type_after_title' );
