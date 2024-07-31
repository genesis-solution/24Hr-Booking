<?php

if ( !defined( 'ABSPATH' ) ) {
	exit;
}
?>

<?php if ( mphb_tmpl_has_room_type_default_price() ) : ?>

	<div>
		<?php

		/**
		 * @hooked
		 */
		do_action( 'milenia_mphb_render_loop_room_type_before_price' );
		?>

		<?php mphb_tmpl_the_room_type_default_price(); ?>

		<?php

		/**
		 * @hooked
		 */
		do_action( 'milenia_mphb_render_loop_room_type_after_price' );
		?>
	</div>

<?php endif; ?>
