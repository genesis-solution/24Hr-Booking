<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<?php $size = mphb_tmpl_get_room_type_size( false ); ?>

<?php if ( ! empty( $size ) ) : ?>

	<?php

	/**
	 * @hooked \MPHB\Views\LoopRoomTypeView::_renderSizeListItemOpen                    - 10
	 * @hooked \MPHB\Views\LoopRoomTypeView::_renderSizeTitle                           - 20
	 * @hooked \MPHB\Views\LoopRoomTypeView::_renderAttributesListItemValueHolderOpen   - 30
	 */
	do_action( 'mphb_render_loop_room_type_before_size' );
	?>

	<?php

	// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	echo mphb_tmpl_get_room_type_size();

	?>

	<?php

	/**
	 * @hooked \MPHB\Views\LoopRoomTypeView::_renderAttributesListItemValueHolderClose  - 10
	 * @hooked \MPHB\Views\LoopRoomTypeView::_renderAdultsListItemClose                 - 20
	 */
	do_action( 'mphb_render_loop_room_type_after_size' );
	?>

	<?php
endif;
