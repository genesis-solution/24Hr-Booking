<?php

if ( !defined( 'ABSPATH' ) ) {
	exit;
}
?>

<?php if ( mphb_tmpl_has_room_type_default_price() ) : ?>

	<?php
	/**
	 * @hooked milenia_mphb_price_modification	- 10
	 */
	do_action( 'milenia_mphb_render_single_room_type_before_price' );
	?>

	<?php mphb_tmpl_the_room_type_default_price(); ?>

	<?php do_action( 'milenia_mphb_render_single_room_type_after_price' ); ?>

<?php endif; ?>
