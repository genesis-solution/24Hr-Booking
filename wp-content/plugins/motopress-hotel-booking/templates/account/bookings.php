<?php

/**
 *
 * @since 4.2.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( $customer ) {
	if ( ! empty( $bookings ) ) {
		?>
		<table class="mphb-account-bookings">
			<thead>
				<tr>
					<th><?php echo esc_html__( 'Booking', 'motopress-hotel-booking' ); ?></th>
					<th><?php echo esc_html__( 'Check-in', 'motopress-hotel-booking' ); ?></th>
					<th><?php echo esc_html__( 'Check-out', 'motopress-hotel-booking' ); ?></th>
					<th><?php echo esc_html__( 'Total', 'motopress-hotel-booking' ); ?></th>
					<th><?php echo esc_html__( 'Actions', 'motopress-hotel-booking' ); ?></th>
				</tr>
			</thead>
			<?php
			foreach ( $bookings as $booking ) {
				$reservationReceivedId = MPHB()->settings()->pages()->getReservationReceivedPageId();

				$detailsLink = '';

				$paymentIds = MPHB()->getPaymentPersistence()->getPosts(
					array(
						'meta_query' => array(
							array(
								'key'   => '_mphb_booking_id',
								'value' => (int) $booking->getId(),
							),
						),
					)
				);

				if ( $paymentIds ) {

					foreach ( $paymentIds as $paymentId ) {
						$payment = MPHB()->getPaymentRepository()->findById( $paymentId );

						$detailsLink = add_query_arg(
							array(
								'payment_id'  => (int) $payment->getId(),
								'payment_key' => esc_attr( $payment->getKey() ),
							),
							get_permalink( $reservationReceivedId )
						);
					}
				} else {
					$detailsLink = add_query_arg(
						array(
							'booking_id'  => (int) $booking->getId(),
							'booking_key' => esc_attr( $booking->getKey() ),
						),
						get_permalink( $reservationReceivedId )
					);
				}
				?>
				<tr>
					<td class="booking-number" data-title="<?php esc_attr_e( 'Booking', 'motopress-hotel-booking' ); ?>">
						<?php echo '#' . (int) $booking->getId(); ?><br/>
						<span class="booking-status"><?php echo mphb_get_status_label( $booking->getStatus() ); ?></span>
					</td>
					<td class="booking-check-in" data-title="<?php esc_html_e( 'Check-in', 'motopress-hotel-booking' ); ?>">
						<?php echo \MPHB\Utils\DateUtils::formatDateWPFront( $booking->getCheckInDate() ); ?>
					</td>
					<td class="booking-check-out" data-title="<?php esc_html_e( 'Check-out', 'motopress-hotel-booking' ); ?>">
						<?php echo \MPHB\Utils\DateUtils::formatDateWPFront( $booking->getCheckOutDate() ); ?>
					</td>
					<td class="booking-price" data-title="<?php esc_html_e( 'Total', 'motopress-hotel-booking' ); ?>">
						<?php echo mphb_format_price( $booking->getTotalPrice() ); ?>
					</td>
					<td class="booking-actions" data-title="<?php esc_html_e( 'Actions', 'motopress-hotel-booking' ); ?>">
						<?php printf( '<a href="%s" target="_blank">%s</a>', esc_url( $detailsLink ), esc_html__( 'View', 'motopress-hotel-booking' ) ); ?>
					</td>
				</tr>
				<?php
			}
			?>
		</table>
		<?php

		if ( $totalPages > 1 ) {
			?>
			<nav class="mphb-pagination">
				<div class="mphb-nav-links">
					<?php
					for ( $i = 1; $i <= $totalPages; $i++ ) {
						if ( $i == $cur ) {
							?>
							<span class="page-numbers"><?php echo (int) $i; ?></span>
							<?php
						} else {
							?>
							<a class="page-numbers"
							   href="<?php echo esc_url( add_query_arg( '_page', $i, $baseLink ) ); ?>">
								<?php echo (int) $i; ?>
							</a>
							<?php
						}
					}
					?>
				</div>
			</nav>
			<?php
		}
	} else {
		?>
		<p>
			<?php echo esc_html__( 'No bookings found.', 'motopress-hotel-booking' ); ?>
		</p>
		<?php
	}
} else {
	echo esc_html__( 'No bookings found.', 'motopress-hotel-booking' );
}
