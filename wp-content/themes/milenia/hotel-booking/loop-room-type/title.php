<?php
/**
 * Loop Room title
 *
 * This template can be overridden by copying it to %theme%/hotel-booking/loop-room-type/title.php.
 *
 */
if ( !defined( 'ABSPATH' ) ) {
	exit;
}
?>

<?php
/**
 * @hooked
 */
do_action( 'milenia_mphb_render_loop_room_type_before_title' );
?>

<?php $linkClass = apply_filters( 'mphb_loop_room_type_title_link_class', 'mphb-room-type-title' ); ?>

<h2 class="milenia-entity-title">
	<a class="<?php echo esc_attr( $linkClass ); ?>" href="<?php esc_url( the_permalink() ); ?>"><?php esc_html( the_title() ); ?></a>
</h2>

<?php
/**
 * @hooked
 */
do_action( 'milenia_mphb_render_loop_room_type_after_title' );
?>
