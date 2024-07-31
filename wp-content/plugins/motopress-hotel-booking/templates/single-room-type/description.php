<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<?php do_action( 'mphb_render_single_room_type_before_description' ); ?>

<?php the_content(); ?>

<?php
do_action( 'mphb_render_single_room_type_after_description' );
