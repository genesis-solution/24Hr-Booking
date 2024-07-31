<?php

/**
 * Available variables
 * - string $actionUrl Action URL for search form
 * - \MPHB\Entities\Booking $booking
 * - array $details [%Room type ID% => [allowed_rates]]
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/** @hooked None */
do_action( 'mphb_cb_checkout_form_before_start' );

?>

<form class="mphb_cb_checkout_form" enctype="<?php echo esc_attr( apply_filters( 'mphb_checkout_form_enctype_data', '' ) ); ?>" method="POST" action="<?php echo esc_url( $actionUrl ); ?>">

	<?php
		/**
		 * @hooked \MPHB\Admin\MenuPages\CreateBooking\CheckoutStep::printNonceFields - 10
		 * @hooked \MPHB\Admin\MenuPages\CreateBooking\Step::printDateHiddenFields - 20
		 */
		do_action( 'mphb_cb_checkout_form_after_start' );
	?>

	<?php
		/**
		 * @see \MPHB\Admin\MenuPages\CreateBooking\CheckoutStep::setup()
		 */
		do_action( 'mphb_cb_checkout_form', $booking, $details );
	?>

	<?php
		/** @hooked None */
		do_action( 'mphb_cb_checkout_form_before_submit_button' );
	?>

	<p class="mphb-submit-button-wrapper">
		<input type="submit" class="button" value="<?php esc_attr_e( 'Book Now', 'motopress-hotel-booking' ); ?>" />
	</p>

	<?php
		/** @hooked None */
		do_action( 'mphb_cb_checkout_form_before_end' );
	?>

</form>

<?php
	/** @hooked None */
	do_action( 'mphb_cb_checkout_form_after_end' );
?>
