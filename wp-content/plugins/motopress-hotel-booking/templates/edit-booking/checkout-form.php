<?php

/**
 * Available variables:
 *     string $actionUrl
 *     string $nextStep
 *     \MPHB\Entities\Booking $booking
 *     array $rooms
 *
 * @since 3.8
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?>
<div class="mphb-checkout-form-wrapper">
	<form class="" action="<?php echo esc_attr( $actionUrl ); ?>" method="POST">
		<input type="hidden" name="step" value="<?php echo esc_html( $nextStep ); ?>">
		<input type="hidden" name="check_in_date" value="<?php echo esc_html( $checkInDate ); ?>">
		<input type="hidden" name="check_out_date" value="<?php echo esc_html( $checkOutDate ); ?>">
		<?php wp_nonce_field( 'edit-booking', 'checkout_nonce' ); ?>

		<?php do_action( 'mphb_edit_booking_checkout_form', $booking, $rooms ); ?>

		<p class="mphb-submit-button-wrapper">
			<input type="submit" name="edit-booking" class="button button-primary button-hero" value="<?php esc_attr_e( 'Save', 'motopress-hotel-booking' ); ?>">
		</p>
	</form>
</div>
<?php
