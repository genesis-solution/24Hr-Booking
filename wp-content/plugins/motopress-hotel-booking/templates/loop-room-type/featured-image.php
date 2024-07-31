<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<?php if ( has_post_thumbnail() ) : ?>

	<?php
	/**
	 * @hooked \MPHB\Views\LoopRoomTypeView::_renderFeaturedImageParagraphOpen  - 10
	 */
	do_action( 'mphb_render_loop_room_type_before_featured_image' );

	$linkUrl = get_permalink();
	?>

	<a href="<?php echo esc_url( null == $linkUrl ? '' : $linkUrl ); ?>">
	<?php mphb_tmpl_the_loop_room_type_thumbnail(); ?>
	</a>

	<?php
	/**
	 * @hooked \MPHB\Views\LoopRoomTypeView::_renderFeaturedImageParagraphClose - 10
	 */
	do_action( 'mphb_render_loop_room_type_after_featured_image' );
	?>

<?php endif; ?>
