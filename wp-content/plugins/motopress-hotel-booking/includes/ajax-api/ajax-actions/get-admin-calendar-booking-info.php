<?php

namespace MPHB\AjaxApi;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class GetAdminCalendarBookingInfo extends AbstractAjaxApiAction {

	const REQUEST_DATA_BOOKING_ID = 'booking_id';


	public static function getAjaxActionNameWithouPrefix() {
		return 'get_admin_calendar_booking_info';
	}

	public static function isActionForGuestUser() {
		return false;
	}

	/**
	 * @throws Exception when validation of request parameters failed
	 */
	protected static function getValidatedRequestData() {

		$requestData = parent::getValidatedRequestData();

		$requestData[ static::REQUEST_DATA_BOOKING_ID ] = static::getIntegerFromRequest( static::REQUEST_DATA_BOOKING_ID, true );

		if ( 0 >= $requestData[ static::REQUEST_DATA_BOOKING_ID ] ) {

			throw new \Exception(
				'Parameter ' . static::REQUEST_DATA_BOOKING_ID .
				' must be integer > 0 but (' . $requestData[ static::REQUEST_DATA_BOOKING_ID ] . ') was given.'
			);
		}

		return $requestData;
	}


	protected static function doAction( array $requestData ) {

		$booking = MPHB()->getBookingRepository()->findById( $requestData[ static::REQUEST_DATA_BOOKING_ID ] );

		if ( null == $booking ) {
			wp_send_json_error( array( 'errorMessage' => __( 'The booking not found.', 'motopress-hotel-booking' ) ), 400 );
			return;
		}

		$customer   = $booking->getCustomer();
		$couponCode = $booking->getCouponCode();
		$dateFormat = MPHB()->settings()->dateTime()->getDateFormat();

		ob_start();

		?>
		<h2><?php esc_html_e( 'Booking Information', 'motopress-hotel-booking' ); ?></h2>
		<table class="widefat striped">
			<tbody>
				<tr>
					<th><?php esc_html_e( 'ID', 'motopress-hotel-booking' ); ?></th>
					<td><?php echo esc_html( $booking->getId() ); ?></td>
				</tr>
				<tr>
					<th><?php esc_html_e( 'Check-in Date', 'motopress-hotel-booking' ); ?></th>
					<td><?php echo esc_html( $booking->getCheckInDate()->format( $dateFormat ) ); ?></td>
				</tr>
				<tr>
					<th><?php esc_html_e( 'Check-out Date', 'motopress-hotel-booking' ); ?></th>
					<td><?php echo esc_html( $booking->getCheckOutDate()->format( $dateFormat ) ); ?></td>
				</tr>

				<?php if ( $booking->isImported() ) : ?>

					<?php
						$reservedRoomsUID = '';

					foreach ( $booking->getReservedRooms() as $reservedRoom ) {

						$reservedRoomsUID .= ( empty( $reservedRoomsUID ) ? '' : ', ' ) . $reservedRoom->getUid();
					}
					?>
					<tr>
						<th>UID</th>
						<td><?php echo esc_html( $reservedRoomsUID ); ?></td>
					</tr>
					<tr>
						<th><?php esc_html_e( 'Summary', 'motopress-hotel-booking' ); ?></th>
						<td><?php echo esc_html( $booking->getICalSummary() ); ?></td>
					</tr>
					<tr>
						<th><?php esc_html_e( 'Description', 'motopress-hotel-booking' ); ?></th>
						<td><?php echo esc_html( $booking->getICalDescription() ); ?></td>
					</tr>
					<tr>
						<th><?php esc_html_e( 'Source', 'motopress-hotel-booking' ); ?></th>
						<td><?php echo esc_html( $booking->getICalProdid() ); ?></td>
					</tr>

				<?php endif; ?>
			</tbody>
		</table>

		<h2><?php esc_html_e( 'Reserved Accommodations', 'motopress-hotel-booking' ); ?></h2>
		<?php mphb_tmpl_the_reserved_rooms_details( $booking->getReservedRooms() ); ?>

		<h2><?php esc_html_e( 'Customer Information', 'motopress-hotel-booking' ); ?></h2>
		<table class="widefat striped">
			<tbody>
				<tr>
					<th><?php esc_html_e( 'First Name', 'motopress-hotel-booking' ); ?></th>
					<td><?php echo esc_html( $customer->getFirstName() ); ?></td>
				</tr>
				<tr>
					<th><?php esc_html_e( 'Last Name', 'motopress-hotel-booking' ); ?></th>
					<td><?php echo esc_html( $customer->getLastName() ); ?></td>
				</tr>
				<tr>
					<th><?php esc_html_e( 'Email', 'motopress-hotel-booking' ); ?></th>
					<td><?php echo esc_html( $customer->getEmail() ); ?></td>
				</tr>
				<tr>
					<th><?php esc_html_e( 'Phone', 'motopress-hotel-booking' ); ?></th>
					<td><?php echo esc_html( $customer->getPhone() ); ?></td>
				</tr>
				<tr>
					<th><?php esc_html_e( 'Country', 'motopress-hotel-booking' ); ?></th>
					<td><?php echo esc_html( $customer->getCountry() ); ?></td>
				</tr>
				<tr>
					<th><?php esc_html_e( 'Address', 'motopress-hotel-booking' ); ?></th>
					<td><?php echo esc_html( $customer->getAddress1() ); ?></td>
				</tr>
				<tr>
					<th><?php esc_html_e( 'City', 'motopress-hotel-booking' ); ?></th>
					<td><?php echo esc_html( $customer->getCity() ); ?></td>
				</tr>
				<tr>
					<th><?php esc_html_e( 'State / County', 'motopress-hotel-booking' ); ?></th>
					<td><?php echo esc_html( $customer->getState() ); ?></td>
				</tr>
				<tr>
					<th><?php esc_html_e( 'Postcode', 'motopress-hotel-booking' ); ?></th>
					<td><?php echo esc_html( $customer->getZip() ); ?></td>
				</tr>
				<tr>
					<th><?php esc_html_e( 'Customer Note', 'motopress-hotel-booking' ); ?></th>
					<td><?php echo esc_html( $booking->getNote() ); ?></td>
				</tr>
			</tbody>
		</table>

		<h2><?php esc_html_e( 'Additional Information', 'motopress-hotel-booking' ); ?></h2>
		<table class="form-table">
			<tbody>
				<tr>
					<th><?php esc_html_e( 'Coupon', 'motopress-hotel-booking' ); ?></th>
					<td><?php echo ! empty( $couponCode ) ? esc_html( $couponCode ) : '&#8212;'; ?></td>
				</tr>
				<tr>
					<th><?php esc_html_e( 'Total Booking Price', 'motopress-hotel-booking' ); ?></th>
					<td><?php mphb_tmpl_the_payments_table( $booking ); ?></td>
				</tr>
			</tbody>
		</table>

		<?php
		if ( ! empty( $booking->getInternalNotes() ) ) {
			?>
			<h2><?php esc_html_e( 'Notes', 'motopress-hotel-booking' ); ?></h2>
			<table class="widefat striped">
				<tbody>
					<?php
					$dateFormat = get_option( 'date_format' );
					foreach ( $booking->getInternalNotes() as $note ) {
						$user        = get_user_by( 'id', $note['user'] );
						$displayName = $user ? esc_attr( $user->display_name ) : '';
						$noteDate    = wp_date( $dateFormat, $note['date'] );
						?>
						<tr>
							<td>
								<?php echo wp_kses_post( wpautop( sprintf( '%s', $note['note'] ) ) ); ?>
								<p class="description">
								<?php
								if ( $displayName ) {
									/* translators: %1$s: note author, %1$s: note date */
									echo esc_html(
										sprintf(
											__( '%1$s on %2$s', 'motopress-hotel-booking' ),
											$displayName,
											$noteDate
										)
									);
								} else {
									echo esc_html( $noteDate );
								}
								?>
								</p>
							</td>
						</tr>
						<?php
					}
					?>
				</tbody>
			</table>
			<p></p>
			<?php
		}

		$bookingInfo = ob_get_clean();

		// wp_send_json_success(array('bookingInfo' => $bookingInfo));
		wp_send_json_success( $bookingInfo, 200 );
	}
}
