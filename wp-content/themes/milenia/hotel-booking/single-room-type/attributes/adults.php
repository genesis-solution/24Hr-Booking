<?php

if ( !defined( 'ABSPATH' ) ) {
	exit;
}
?>

<?php $adults = mphb_tmpl_get_room_type_adults_capacity(); ?>

<?php if ( !empty( $adults ) ) : ?>

	<?php

	/**
	 * @hooked \MPHB\Views\SingleRoomTypeView::_renderAdultsListItemOpen				- 10
	 * @hooked \MPHB\Views\SingleRoomTypeView::_renderAdultsTitle						- 20
	 * @hooked \MPHB\Views\SingleRoomTypeView::_renderAttributesListItemValueHolderOpen	- 30
	 */
	do_action( 'mphb_render_single_room_type_before_adults' );
	?>

	<?php echo esc_html($adults); ?>

	<?php

	/**
	 * @hooked \MPHB\Views\SingleRoomTypeView::_renderAttributesListItemValueHolderClose	- 10
	 * @hooked \MPHB\Views\SingleRoomTypeView::_renderAdultsListItemClose - 20
	 */
	do_action( 'mphb_render_single_room_type_after_adults' );
	?>

<?php endif; ?>
