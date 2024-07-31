<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$adults        = mphb_tmpl_get_room_type_adults_capacity();
$totalCapacity = mphb_tmpl_get_room_type_total_capacity();

?>

<?php if ( ! empty( $adults ) && empty( $totalCapacity ) ) : ?>

	<?php

	/**
	 * @hooked \MPHB\Views\LoopRoomTypeView::_renderAdultsListItemOpen                  - 10
	 * @hooked \MPHB\Views\LoopRoomTypeView::_renderAdultsTitle                         - 20
	 * @hooked \MPHB\Views\LoopRoomTypeView::_renderAttributesListItemValueHolderOpen   - 30
	 */
	do_action( 'mphb_render_loop_room_type_before_adults' );
	?>

	<?php

	// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	echo $adults;

	?>

	<?php

	/**
	 * @hooked \MPHB\Views\LoopRoomTypeView::_renderAttributesListItemValueHolderClose  - 10
	 * @hooked \MPHB\Views\LoopRoomTypeView::_renderAdultsListItemClose                 - 20
	 */
	do_action( 'mphb_render_loop_room_type_after_adults' );
	?>

	<?php
endif;
