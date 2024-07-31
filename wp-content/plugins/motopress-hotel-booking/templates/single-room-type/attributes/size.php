<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<?php $size = mphb_tmpl_get_room_type_size( false ); ?>

<?php if ( ! empty( $size ) ) : ?>

	<?php

	/**
	 * @hooked \MPHB\Views\SingleRoomTypeView::_renderSizeListItemOpen                  - 10
	 * @hooked \MPHB\Views\SingleRoomTypeView::_renderSizeTitle                         - 20
	 * @hooked \MPHB\Views\SingleRoomTypeView::_renderAttributesListItemValueHolderOpen - 30
	 */
	do_action( 'mphb_render_single_room_type_before_size' );
	?>

	<?php

	// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	echo mphb_tmpl_get_room_type_size();

	?>

	<?php

	/**
	 * @hooked \MPHB\Views\SingleRoomTypeView::_renderAttributesListItemValueHolderClose    - 10
	 * @hooked \MPHB\Views\SingleRoomTypeView::_renderSizeListItemClose                     - 20
	 */
	do_action( 'mphb_render_single_room_type_after_size' );
	?>

	<?php
endif;
