<?php

/**
 * Available variables
 *  - DateTime $check_in_date
 *  - DateTime $check_out_date
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<?php

/**
 * @hooked \MPHB\Views\SingleRoomTypeView::_renderPriceParagraphOpen    - 10
 * @hooked \MPHB\Views\SingleRoomTypeView::_renderPriceTitle            - 20
 */
do_action( 'mphb_render_single_room_type_before_price' );
?>

<?php mphb_tmpl_the_room_type_price_for_dates( $check_in_date, $check_out_date ); ?>

<?php

/**
 * @hooked \MPHB\Views\SingleRoomTypeView::_renderPriceParagraphClose   - 10
 */
do_action( 'mphb_render_single_room_type_after_price' );
