<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<?php

/**
 * @hooked \MPHB\Views\SingleRoomTypeView::_renderReservationFormTitle - 10
 */
do_action( 'mphb_render_single_room_type_before_reservation_form' );
?>

<?php mphb_tmpl_the_room_reservation_form(); ?>

<?php
do_action( 'mphb_render_single_room_type_after_reservation_form' );
