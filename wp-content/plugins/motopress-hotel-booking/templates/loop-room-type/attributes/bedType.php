<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<?php $bedType = mphb_tmpl_get_room_type_bed_type(); ?>

<?php if ( ! empty( $bedType ) ) : ?>
	<?php

	/**
	 * @hooked \MPHB\Views\LoopRoomTypeView::_renderBedTypeListItemOpen                 - 10
	 * @hooked \MPHB\Views\LoopRoomTypeView::_renderBedTypeTitle                        - 20
	 * @hooked \MPHB\Views\LoopRoomTypeView::_renderAttributesListItemValueHolderOpen   - 30
	 */
	do_action( 'mphb_render_loop_room_type_before_bed_type' );
	?>

	<?php

	// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	echo $bedType;

	?>

	<?php

	/**
	 * @hooked \MPHB\Views\LoopRoomTypeView::_renderAttributesListItemValueHolderClose  - 10
	 * @hooked \MPHB\Views\LoopRoomTypeView::_renderAdultsListItemClose                 - 20
	 */
	do_action( 'mphb_render_loop_room_type_after_bed_type' );
	?>

	<?php
endif;
