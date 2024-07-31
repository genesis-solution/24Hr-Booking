<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<?php get_header(); ?>

<?php
/**
 * @hooked \MPHB\Views\SingleRoomTypeView::renderPageWrapperStart - 10
 */
do_action( 'mphb_render_single_room_type_wrapper_start' );
?>

<?php
while ( have_posts() ) :
	the_post();

	if ( post_password_required() ) {
		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo get_the_password_form();
		return;
	}
	?>

	<div <?php post_class(); ?>>

		<?php do_action( 'mphb_render_single_room_type_before_content' ); ?>

		<?php
		/**
		 * @hooked \MPHB\Views\SingleRoomTypeView::renderTitle              - 10
		 * @hooked \MPHB\Views\SingleRoomTypeView::renderFeaturedImage      - 20
		 * @hooked \MPHB\Views\SingleRoomTypeView::renderDescription        - 30
		 * @hooked \MPHB\Views\SingleRoomTypeView::renderPrice              - 40
		 * @hooked \MPHB\Views\SingleRoomTypeView::renderAttributes         - 50
		 * @hooked \MPHB\Views\SingleRoomTypeView::renderCalendar           - 60
		 * @hooked \MPHB\Views\SingleRoomTypeView::renderReservationForm    - 70
		 */
		do_action( 'mphb_render_single_room_type_content' );
		?>

		<?php do_action( 'mphb_render_single_room_type_after_content' ); ?>

	</div>

	<?php
endwhile;
?>

<?php
/**
 * @hooked \MPHB\Views\SingleRoomTypeView::renderPageWrapperEnd - 10
 */
do_action( 'mphb_render_single_room_type_wrapper_end' );
?>

<?php get_footer(); ?>
