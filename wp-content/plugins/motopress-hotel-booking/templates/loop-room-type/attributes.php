<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<?php

/**
 * @hooked \MPHB\Views\LoopRoomTypeView::renderAttributesTitle      - 10
 * @hooked \MPHB\Views\LoopRoomTypeView::renderAttributesListOpen   - 20
 */
do_action( 'mphb_render_loop_room_type_before_attributes' );
?>

<?php

/**
 * @hooked \MPHB\Views\LoopRoomTypeView::renderTotalCapacity    - 5
 * @hooked \MPHB\Views\LoopRoomTypeView::renderAdults           - 10
 * @hooked \MPHB\Views\LoopRoomTypeView::renderChildren         - 20
 * @hooked \MPHB\Views\LoopRoomTypeView::renderFacilities       - 30
 * @hooked \MPHB\Views\LoopRoomTypeView::renderView             - 40
 * @hooked \MPHB\Views\LoopRoomTypeView::renderSize             - 50
 * @hooked \MPHB\Views\LoopRoomTypeView::renderBedType          - 60
 * @hooked \MPHB\Views\LoopRoomTypeView::renderCategories       - 70
 * @hooked \MPHB\Views\LoopRoomTypeView::renderCustomAttributes - 80
 */
do_action( 'mphb_render_loop_room_type_attributes' );
?>

<?php

/**
 * @hooked \MPHB\Views\LoopRoomTypeView::renderAttributesListClose - 10
 */
do_action( 'mphb_render_loop_room_type_after_attributes' );

