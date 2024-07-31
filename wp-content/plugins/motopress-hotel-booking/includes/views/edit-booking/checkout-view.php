<?php

namespace MPHB\Views\EditBooking;

use MPHB\Entities\Booking;
use MPHB\Entities\ReservedRoom;
use MPHB\Entities\RoomType;

/**
 * @since 3.8
 */
class CheckoutView {

	/**
	 * @param Booking $booking
	 * @param array   $rooms Array of [room_id, room_type_id, rate_id, allowed_rates,
	 *       adults, children].
	 *
	 * @since 3.8
	 */
	public static function renderBookingDetails( $booking, $rooms ) {
		?>
		<section id="mphb-booking-details" class="mphb-booking-details mphb-checkout-section">
			<h3 class="mphb-booking-details-title">
				<?php esc_html_e( 'New Booking Details', 'motopress-hotel-booking' ); ?>
			</h3>

			<?php do_action( 'mphb_edit_booking_checkout_booking_details', $booking, $rooms ); ?>
		</section>
		<?php
	}

	/**
	 * @param Booking $booking
	 * @param array   $rooms Array of [room_id, room_type_id, ...].
	 *
	 * @since 3.8
	 */
	public static function renderBookedDetails( $booking, $rooms ) {
		?>
		<section class="mphb-booking-details mphb-checkout-section">
			<h3 class="mphb-booking-details-title">
				<?php esc_html_e( 'Original Booking Details', 'motopress-hotel-booking' ); ?>
			</h3>

			<?php do_action( 'mphb_edit_booking_checkout_booked_details', $booking, $rooms ); ?>
		</section>
		<?php
	}

	/**
	 * @param Booking $booking
	 * @param array   $rooms Array of [room_id, room_type_id, rate_id, allowed_rates,
	 *       adults, children].
	 *
	 * @since 3.8
	 */
	public static function renderAccommodations( $booking, $rooms ) {
		?>
		<div class="mphb-reserve-rooms-details">
			<?php
			foreach ( $booking->getReservedRooms() as $index => $reservedRoom ) {
				$roomTypeId = apply_filters( '_mphb_translate_post_id', $reservedRoom->getRoomTypeId() );
				$roomType   = MPHB()->getRoomTypeRepository()->findById( $roomTypeId );

				?>
				<div class="mphb-room-details" data-index="<?php echo esc_attr( $index ); ?>">
					<input type="hidden" name="mphb_room_details[<?php echo esc_attr( $index ); ?>][room_type_id]" value="<?php echo esc_attr( $roomType->getOriginalId() ); ?>">
					<input type="hidden" name="mphb_room_details[<?php echo esc_attr( $index ); ?>][room_id]" value="<?php echo esc_attr( $reservedRoom->getRoomId() ); ?>">

					<?php do_action( 'mphb_edit_booking_checkout_room_details', $reservedRoom, $index, $roomType, $booking, $rooms ); ?>
				</div>
			<?php } ?>
		</div>
		<?php
	}

	/**
	 * @param Booking $booking
	 * @param array   $rooms Array of [room_id, room_type_id, ...].
	 *
	 * @since 3.8
	 */
	public static function renderReservations( $booking, $rooms ) {
		?>
		<div class="mphb-reserve-rooms-details">
			<?php
			foreach ( $booking->getReservedRooms() as $index => $reservedRoom ) {
				$roomTypeId = apply_filters( '_mphb_translate_post_id', $reservedRoom->getRoomTypeId() );
				$roomType   = mphb_get_room_type( $roomTypeId );

				?>
				<div class="mphb-room-details" data-index="<?php echo esc_attr( $index ); ?>">
					<input type="hidden" name="mphb_room_details[<?php echo esc_attr( $index ); ?>][room_type_id]" value="<?php echo esc_attr( $roomType->getOriginalId() ); ?>">
					<input type="hidden" name="mphb_room_details[<?php echo esc_attr( $index ); ?>][room_id]" value="<?php echo esc_attr( $reservedRoom->getRoomId() ); ?>">

					<?php do_action( 'mphb_edit_booking_checkout_booked_room_details', $reservedRoom, $index, $roomType, $booking, $rooms ); ?>
				</div>
			<?php } ?>
		</div>
		<?php
	}

	/**
	 * @param ReservedRoom $reservedRoom
	 *
	 * @since 3.8
	 */
	public static function renderRoomTitle( $reservedRoom ) {
		$roomId = $reservedRoom->getRoomId();

		?>
		<p class="mphb-room-title">
			<span>
				<?php esc_html_e( 'Accommodation:', 'motopress-hotel-booking' ); ?>
			</span>
			<a href="<?php echo esc_url( get_edit_post_link( $roomId ) ); ?>" target="_blank">
				<?php echo esc_html( get_the_title( $roomId ) ); ?>
			</a>
		</p>
		<?php
	}

	/**
	 * @param ReservedRoom $reservedRoom
	 * @param int          $roomIndex
	 * @param RoomType     $roomType
	 *
	 * @since 3.8
	 */
	public static function renderGuests( $reservedRoom, $roomIndex, $roomType ) {
		$adults    = $reservedRoom->getAdults();
		$children  = $reservedRoom->getChildren();
		$guestName = $reservedRoom->getGuestName();

		$adultsAllowed   = MPHB()->settings()->main()->isAdultsAllowed();
		$childrenAllowed = MPHB()->settings()->main()->isChildrenAllowed();

		if ( $adultsAllowed ) {
			?>
			<p class="mphb-adults-chooser">
				<label>
					<?php $childrenAllowed ? esc_html_e( 'Adults', 'motopress-hotel-booking' ) : esc_html_e( 'Guests', 'motopress-hotel-booking' ); ?>
				</label>
				<input type="text" name="edit-<?php echo esc_attr( uniqid() ); ?>-adults" value="<?php echo esc_attr( $adults ); ?>" readonly="readonly">
			</p>
			<?php
		}

		if ( $childrenAllowed && $roomType->getChildrenCapacity() > 0 ) {
			?>
			<p class="mphb-children-chooser">
				<label>
					<?php echo esc_html( sprintf( __( 'Children %s', 'motopress-hotel-booking' ), MPHB()->settings()->main()->getChildrenAgeText() ) ); ?>
				</label>
				<input type="text" name="edit-<?php echo esc_attr( uniqid() ); ?>-children" value="<?php echo esc_attr( $children ); ?>" readonly="readonly">
			</p>
			<?php
		}

		?>
		<p class="mphb-guest-name-wrapper">
			<label>
				<?php esc_html_e( 'Full Guest Name', 'motopress-hotel-booking' ); ?>
			</label>
			<input type="text" name="edit-<?php echo esc_attr( uniqid() ); ?>-guest-name" value="<?php echo esc_attr( $guestName ); ?>" readonly="readonly">
		</p>
		<?php
	}

	/**
	 * @param ReservedRoom $reservedRoom
	 * @param int          $roomIndex
	 * @param RoomType     $roomType
	 * @param Booking      $booking
	 *
	 * @since 3.8
	 */
	public static function renderRate( $reservedRoom, $roomIndex, $roomType, $booking ) {
		$rateId       = $reservedRoom->getRateId();
		$translatedId = apply_filters( '_mphb_translate_post_id', $rateId );

		$rate = MPHB()->getRateRepository()->findById( $translatedId );

		if ( ! is_null( $rate ) ) {
			$rateTitle       = $rate->getTitle();
			$rateDescription = $rate->getDescription();

			MPHB()->reservationRequest()->setupParameters(
				array(
					'adults'         => $reservedRoom->getAdults(),
					'children'       => $reservedRoom->getChildren(),
					'check_in_date'  => $booking->getCheckInDate(),
					'check_out_date' => $booking->getCheckOutDate(),
				)
			);

			$ratePrice = mphb_format_price( $rate->calcPrice( $booking->getCheckInDate(), $booking->getCheckOutDate() ) );
		} else {
			$rateTitle       = get_the_title( $rateId );
			$rateDescription = '';
			$ratePrice       = '';
		}

		?>
		<section class="mphb-rate-chooser mphb-checkout-item-section">
			<h4 class="mphb-room-rate-chooser-title">
				<?php
				$rateInfo = esc_html( $rateTitle );

				if ( ! empty( $ratePrice ) ) {
					$rateInfo .= ', ' . $ratePrice;
				}
                // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				printf( esc_html__( 'Rate: %s', 'motopress-hotel-booking' ), $rateInfo );
				?>
			</h4>
		</section>
		<?php
	}

	/**
	 * @param ReservedRoom $reservedRoom
	 *
	 * @since 3.8
	 */
	public static function renderServices( $reservedRoom ) {
		$services = $reservedRoom->getReservedServices();

		if ( empty( $services ) ) {
			// MB-858 - don't show "Choose Additional Services" when there are no available services
			return;
		}

		?>
		<section class="mphb-services-details mphb-checkout-item-section">
			<h4 class="mphb-services-details-title">
				<?php esc_html_e( 'Additional Services', 'motopress-hotel-booking' ); ?>
			</h4>

			<ul class="mphb_sc_checkout-services-list mphb_checkout-services-list">
				<?php
				foreach ( $services as $service ) {
					$translatedService = apply_filters( '_mphb_translate_service', $service );

					?>
					<li>
						<label>
							<input type="checkbox" name="edit-<?php echo esc_attr( uniqid() ); ?>[service_id]" class="mphb_sc_checkout-service mphb_checkout-service" value="" checked="checked" disabled="disabled">
							<?php echo esc_html( $translatedService->getTitle() ); ?>

							<em>(<?php echo wp_kses_post( $service->getPriceWithConditions( false ) ); ?>)</em>

							<?php if ( $service->isPayPerAdult() ) { ?>
								<?php echo esc_html( sprintf( _n( 'x %d guest', 'x %d guests', $service->getAdults(), 'motopress-hotel-booking' ), $service->getAdults() ) ); ?>
							<?php } ?>

							<?php if ( $service->isFlexiblePay() ) { ?>
								<?php echo esc_html( sprintf( _n( 'x %d time', 'x %d times', $service->getQuantity(), 'motopress-hotel-booking' ), $service->getQuantity() ) ); ?>
							<?php } ?>
						</label>
					</li>
					<?php
				}
				?>
			</ul>
		</section>
		<?php
	}
}
