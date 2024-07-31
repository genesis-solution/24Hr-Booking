<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<?php $bedType = mphb_tmpl_get_room_type_bed_type(); ?>

<?php if ( ! empty( $bedType ) ) : ?>
	<?php

	/**
	 * @hooked \MPHB\Views\SingleRoomTypeView::_renderBedTypeListItemOpen               - 10
	 * @hooked \MPHB\Views\SingleRoomTypeView::_renderBedTypeTitle                      - 20
	 * @hooked \MPHB\Views\SingleRoomTypeView::_renderAttributesListItemValueHolderOpen - 30
	 */
	do_action( 'mphb_render_single_room_type_before_bed_type' );
	?>

	<?php

	// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	echo $bedType;

	?>

	<?php

	/**
	 * @hooked \MPHB\Views\SingleRoomTypeView::_renderAttributesListItemValueHolderClose    - 10
	 * @hooked \MPHB\Views\SingleRoomTypeView::_renderBedTypeListItemClose                  - 20
	 */
	do_action( 'mphb_render_single_room_type_after_bed_type' );
	?>

	<?php
endif;
