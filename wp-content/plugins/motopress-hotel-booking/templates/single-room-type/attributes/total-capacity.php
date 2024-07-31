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
	 * @hooked \MPHB\Views\SingleRoomTypeView::_renderTotalCapacityListItemOpen         - 10
	 * @hooked \MPHB\Views\SingleRoomTypeView::_renderTotalCapacityTitle                - 20
	 * @hooked \MPHB\Views\SingleRoomTypeView::_renderAttributesListItemValueHolderOpen - 30
	 */
	do_action( 'mphb_render_single_room_type_before_total_capacity' );
	?>

	<?php

    // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	echo $totalCapacity;

	?>

	<?php
	/**
	 * @hooked \MPHB\Views\SingleRoomTypeView::_renderAttributesListItemValueHolderClose - 10
	 * @hooked \MPHB\Views\SingleRoomTypeView::_renderTotalCapacityListItemClose         - 20
	 */
	do_action( 'mphb_render_single_room_type_after_total_capacity' );
	?>
	<?php
}
