<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<?php

/**
 * @hooked \MPHB\Views\SingleRoomTypeView::renderAttributesTitle    - 10
 * @hooked \MPHB\Views\SingleRoomTypeView::renderAttributesListOpen - 20
 */
do_action( 'mphb_render_single_room_type_before_attributes' );
?>

<?php

/**
 * @hooked \MPHB\Views\SingleRoomTypeView::renderTotalCapacity      - 5
 * @hooked \MPHB\Views\SingleRoomTypeView::renderAdults             - 10
 * @hooked \MPHB\Views\SingleRoomTypeView::renderChildren           - 20
 * @hooked \MPHB\Views\SingleRoomTypeView::renderFacilities         - 30
 * @hooked \MPHB\Views\SingleRoomTypeView::renderView               - 40
 * @hooked \MPHB\Views\SingleRoomTypeView::renderSize               - 50
 * @hooked \MPHB\Views\SingleRoomTypeView::renderBedType            - 60
 * @hooked \MPHB\Views\SingleRoomTypeView::renderCategories         - 70
 * @hooked \MPHB\Views\SingleRoomTypeView::renderCustomAttributes   - 80
 */
do_action( 'mphb_render_single_room_type_attributes' );
?>

<?php

/**
 * @hooked \MPHB\Views\SingleRoomTypeView::renderAttributesListClose - 10
 */
do_action( 'mphb_render_single_room_type_after_attributes' );
