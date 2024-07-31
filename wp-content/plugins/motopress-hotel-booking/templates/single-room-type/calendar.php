<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<?php

/**
 * @hooked \MPHB\Views\SingleRoomTypeView::_renderCalendarTitle - 10
 */
do_action( 'mphb_render_single_room_type_before_calendar' );
?>

<?php
mphb_tmpl_the_room_type_calendar(
	null,
	'',
	MPHB()->settings()->main()->isRoomTypeCalendarShowPrices(),
	MPHB()->settings()->main()->isRoomTypeCalendarTruncatePrices(),
	MPHB()->settings()->main()->isRoomTypeCalendarShowPricesCurrency()
);
?>

<?php
do_action( 'mphb_render_single_room_type_after_calendar' );
