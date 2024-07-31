<?php

if ( !defined( 'ABSPATH' ) ) {
	exit;
}
?>

<?php

/**
 * @hooked \MPHB\Views\SingleRoomTypeView::_renderCalendarTitle - 10
 */
do_action( 'mphb_render_single_room_type_before_calendar' );
?>

<?php mphb_tmpl_the_room_type_calendar(); ?>

<?php do_action( 'mphb_render_single_room_type_after_calendar' ); ?>