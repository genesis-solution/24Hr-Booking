<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<?php if ( mphb_tmpl_has_room_type_default_price() ) : ?>

	<?php

	/**
	 * @hooked \MPHB\Views\SingleRoomTypeView::_renderPriceParagraphOpen    - 10
	 * @hooked \MPHB\Views\SingleRoomTypeView::_renderPriceTitle            - 20
	 */
	do_action( 'mphb_render_single_room_type_before_price' );
	?>

	<?php mphb_tmpl_the_room_type_default_price(); ?>

	<?php

	/**
	 * @hooked \MPHB\Views\SingleRoomTypeView::_renderPriceParagraphClose   - 10
	 */
	do_action( 'mphb_render_single_room_type_after_price' );
	?>

	<?php
endif;
