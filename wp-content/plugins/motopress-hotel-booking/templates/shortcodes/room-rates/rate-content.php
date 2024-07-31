<?php
/**
 * Available variables
 * - string $title
 * - string $minPrice
 * - string $description
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<?php do_action( 'mphb_sc_room_rates_item_top' ); ?>

<?php // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
echo $title;
?>
, <?php echo wp_kses_post( sprintf( __( 'from %s', 'motopress-hotel-booking' ), $minPrice ) ); ?>
<br/>
<?php // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
echo $description;
?>

<?php do_action( 'mphb_sc_room_rates_item_bottom' ); ?>
