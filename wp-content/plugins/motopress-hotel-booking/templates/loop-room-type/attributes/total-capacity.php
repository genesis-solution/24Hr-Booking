<?php
/*
 * @since 3.7.2
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$totalCapacity = mphb_tmpl_get_room_type_total_capacity();

?>
<?php if ( ! empty( $totalCapacity ) ) { ?>
	<?php
	/**
	 * @hooked \MPHB\Views\LoopRoomTypeView::_renderTotalCapacityListItemOpen         - 10
	 * @hooked \MPHB\Views\LoopRoomTypeView::_renderTotalCapacityTitle                - 20
	 * @hooked \MPHB\Views\LoopRoomTypeView::_renderAttributesListItemValueHolderOpen - 30
	 */
	do_action( 'mphb_render_loop_room_type_before_total_capacity' );
	?>

	<?php

    // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	echo $totalCapacity;

	?>

	<?php
	/**
	 * @hooked \MPHB\Views\LoopRoomTypeView::_renderAttributesListItemValueHolderClose - 10
	 * @hooked \MPHB\Views\LoopRoomTypeView::_renderTotalCapacityListItemClose         - 20
	 */
	do_action( 'mphb_render_loop_room_type_after_total_capacity' );
	?>
	<?php
}
