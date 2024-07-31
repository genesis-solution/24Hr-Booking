<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<?php $view = mphb_tmpl_get_room_type_view(); ?>

<?php if ( ! empty( $view ) ) : ?>

	<?php

	/**
	 * @hooked \MPHB\Views\LoopRoomTypeView::_renderViewListItemOpen                    - 10
	 * @hooked \MPHB\Views\LoopRoomTypeView::_renderViewTitle                           - 20
	 * @hooked \MPHB\Views\LoopRoomTypeView::_renderAttributesListItemValueHolderOpen   - 30
	 */
	do_action( 'mphb_render_loop_room_type_before_view' );
	?>

	<?php

	// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	echo $view;

	?>

	<?php

	/**
	 * @hooked \MPHB\Views\LoopRoomTypeView::_renderAttributesListItemValueHolderClose  - 10
	 * @hooked \MPHB\Views\LoopRoomTypeView::_renderAdultsListItemClose                 - 20
	 */
	do_action( 'mphb_render_loop_room_type_after_view' );
	?>

	<?php
endif;
