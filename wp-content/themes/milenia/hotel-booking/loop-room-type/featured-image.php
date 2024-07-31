<?php
if ( !defined( 'ABSPATH' ) ) {
	exit;
}
?>

<?php if ( has_post_thumbnail() ) : ?>

	<?php
	/**
	 * @hooked
	 */
	do_action( 'milenia_mphb_render_loop_room_type_before_featured_image' );
	?>

	<div class="milenia-entity-media milenia-entity-media--featured-image">
		<div class="milenia-entity-media-inner" data-bg-image-src="<?php echo esc_url(get_the_post_thumbnail_url()); ?>">
			<a href="<?php esc_url( the_permalink() ); ?>" class="milenia-entity-link milenia-ln--independent"></a>
		</div>
	</div>

	<?php
	/**
	 * @hooked
	 */
	do_action( 'milenia_mphb_render_loop_room_type_after_featured_image' );
	?>

<?php endif; ?>
