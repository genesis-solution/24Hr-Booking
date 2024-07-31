<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<?php do_action( 'mphb_render_single_room_type_before_excerpt' ); ?>

<?php the_excerpt(); ?>

<?php
do_action( 'mphb_render_single_room_type_after_excerpt' );
