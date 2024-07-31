<?php

namespace MPHB\Views\CreateBooking;

class CheckoutView {

	/**
	 * @param \MPHB\Entities\Booking $booking
	 * @param array                  $details
	 */
	public static function renderBookingDetails( $booking, $details ) {
		?>
		<section id="mphb-booking-details" class="mphb-booking-details mphb-checkout-section">
			<h3 class="mphb-booking-details-title">
				<?php esc_html_e( 'Booking Details', 'motopress-hotel-booking' ); ?>
			</h3>
			<?php do_action( 'mphb_cb_checkout_booking_details', $booking, $details ); ?>
		</section>
		<?php
	}

	/**
	 * @param \MPHB\Entities\Booking $booking
	 * @param array                  $details
	 *
	 * @since 3.7.0 added parameter $reservedRoom to action "mphb_cb_checkout_room_details".
	 * @since 3.7.0 parameter $roomType of the action "mphb_cb_checkout_room_details" became third.
	 */
	public static function renderBookingDetailsInner( $booking, $details ) {
		?>
		<div class="mphb-reserve-rooms-details">
			<?php
			foreach ( $booking->getReservedRooms() as $index => $reservedRoom ) {
				$roomTypeId = apply_filters( '_mphb_translate_post_id', $reservedRoom->getRoomTypeId() );
				$roomType   = MPHB()->getRoomTypeRepository()->findById( $roomTypeId );
				?>
					<div class="mphb-room-details" data-index="<?php echo esc_attr( $index ); ?>">
						<input type="hidden" name="mphb_room_details[<?php echo esc_attr( $index ); ?>][room_type_id]" value="<?php echo esc_attr( $roomType->getOriginalId() ); ?>" />
						<input type="hidden" name="mphb_room_details[<?php echo esc_attr( $index ); ?>][room_id]" value="<?php echo esc_attr( $reservedRoom->getRoomId() ); ?>" />

					<?php do_action( 'mphb_cb_checkout_room_details', $reservedRoom, $index, $roomType, $booking, $details ); ?>
					</div>
			<?php } ?>

		</div>
		<?php
	}

	public static function renderCoupon() {
		if ( ! MPHB()->settings()->main()->isCouponsEnabled() ) {
			return;
		}

		$couponTitle = apply_filters( 'mphb_cb_checkout_coupon_title', '' );
		$couponLabel = apply_filters( 'mphb_cb_checkout_coupon_label', __( 'Coupon Code:', 'motopress-hotel-booking' ) );
		$applyText   = apply_filters( 'mphb_cb_checkout_coupon_apply_text', __( 'Apply', 'motopress-hotel-booking' ) );

		?>
		<section id="mphb-coupon-details" class="mphb-coupon-code-wrapper mphb-checkout-section">

			<?php
				/** @hooked None */
				do_action( 'mphb_cb_checkout_coupon_top' );

				/** @hooked None */
				do_action( 'mphb_cb_checkout_before_coupon_title' );
			?>

			<?php if ( ! empty( $couponTitle ) ) { ?>
				<h3><?php echo esc_html( $couponTitle ); ?></h3>
			<?php } ?>

			<p>
				<?php
					/** @hooked None */
					do_action( 'mphb_cb_checkout_before_coupon_label' );
				?>

				<?php if ( ! empty( $couponLabel ) ) { ?>
					<label for="mphb_coupon_code" class="mphb-coupon-code-title">
						<?php echo esc_html( $couponLabel ); ?>
					</label>
				<?php } ?>

				<?php
					/** @hooked None */
					do_action( 'mphb_cb_checkout_before_coupon_input' );
				?>

				<input type="hidden" id="mphb_applied_coupon_code" name="mphb_applied_coupon_code" />
				<input type="text" id="mphb_coupon_code" name="mphb_coupon_code" />
			</p>

			<p>
				<?php
					/** @hooked None */
					do_action( 'mphb_cb_checkout_before_coupon_button' );
				?>

				<button class="button btn mphb-apply-coupon-code-button">
					<?php echo esc_html( $applyText ); ?>
				</button>
			</p>

			<?php
				/** @hooked None */
				do_action( 'mphb_cb_checkout_before_coupon_message' );
			?>

			<p class="mphb-coupon-message mphb-hide"></p>

			<?php
				/** @hooked None */
				do_action( 'mphb_cb_checkout_coupon_bottom' );
			?>

		</section>
		<?php
	}

}
