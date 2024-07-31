<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<?php do_action( 'mphb_render_loop_service_before_excerpt' ); ?>

<?php the_excerpt(); ?>

<?php
do_action( 'mphb_render_loop_service_after_excerpt' );
