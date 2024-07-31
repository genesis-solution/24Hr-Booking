<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<?php do_action( 'mphb_render_loop_service_before_description' ); ?>

<?php the_content(); ?>

<?php
do_action( 'mphb_render_loop_service_after_description' );
