<?php

/**
 * Available variables:
 *     string $actionUrl
 *     string $nextStep
 *     string $checkInDate Date in human-readable format.
 *     string $checkOutDate Date in human-readable format.
 *     array  $reservedRooms
 *         int    $reservedRooms[]['room_id']
 *         string $reservedRooms[]['room_title']
 *         int    $reservedRooms[]['room_type_id']
 *         string $reservedRooms[]['room_type_title']
 *         int    $reservedRooms[]['adults']
 *         int    $reservedRooms[]['children']
 *         string $reservedRooms[]['status'] "available" or "unavailable".
 *
 * @since 3.8
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$isValid = true;

?>

<form class="mphb-reserve-rooms" action="<?php echo esc_attr( $actionUrl ); ?>" method="POST">
	<input type="hidden" name="step" value="<?php echo esc_html( $nextStep ); ?>">
	<input type="hidden" name="check_in_date" value="<?php echo esc_html( $checkInDate ); ?>">
	<input type="hidden" name="check_out_date" value="<?php echo esc_html( $checkOutDate ); ?>">

	<h2>
		<?php esc_html_e( 'Edit Accommodations', 'motopress-hotel-booking' ); ?>
		<button id="mphb-add-room-button" class="add-new-h2"><?php esc_html_e( 'Add Accommodation', 'motopress-hotel-booking' ); ?></button>
	</h2>

	<p class="description"><?php esc_html_e( 'Add, remove or replace accommodations in the original booking.', 'motopress-hotel-booking' ); ?></p>

	<table class="widefat striped fixed mphb-reserve-rooms-table">
		<thead>
			<tr>
				<th class="row-title column-room-type"><?php esc_html_e( 'Accommodation Type', 'motopress-hotel-booking' ); ?></th>
				<th class="row-title column-room"><?php esc_html_e( 'Accommodation', 'motopress-hotel-booking' ); ?></th>
				<th class="row-title column-status"><?php esc_html_e( 'Status', 'motopress-hotel-booking' ); ?></th>
				<th class="row-title column-actions"><?php esc_html_e( 'Actions', 'motopress-hotel-booking' ); ?></th>
			</tr>
		</thead>
		<tbody>
			<?php
			foreach ( $reservedRooms as $roomId => $room ) {
				$isAvailable = $room['status'] == 'available';

				if ( ! $isAvailable ) {
					$isValid = false;
				}

				?>
				<tr class="mphb-reserve-room <?php echo $isAvailable ? 'mphb-available-room' : 'mphb-unavailable-room'; ?>" data-room-id="<?php echo esc_attr( $roomId ); ?>" data-room-type-id="<?php echo esc_attr( $room['room_type_id'] ); ?>">
					<td class="column-room-type"><?php echo esc_html( $room['room_type_title'] ); ?></td>
					<td class="column-room"><?php echo esc_html( $room['room_title'] ); ?></td>
					<td class="column-status">
						<?php if ( $isAvailable ) { ?>
							<input type="hidden" name="replace_rooms[<?php echo esc_attr( $roomId ); ?>]" value="<?php echo esc_attr( $roomId ); ?>">
						<?php } ?>
						<span><?php $isAvailable ? esc_html_e( 'Available', 'motopress-hotel-booking' ) : esc_html_e( 'Not Available', 'motopress-hotel-booking' ); ?></span>
					</td>
					<td class="column-actions">
						<button class="button mphb-remove-room-button"><?php esc_html_e( 'Remove', 'motopress-hotel-booking' ); ?></button>
						<button class="button mphb-replace-room-button"><?php esc_html_e( 'Replace', 'motopress-hotel-booking' ); ?></button>
					</td>
				</tr>
			<?php } // For each reserved room ?>
		</tbody>
	</table>

	<p class="mphb-submit-button-wrapper">
		<input type="submit" name="edit-booking" class="button button-primary" value="<?php esc_attr_e( 'Continue', 'motopress-hotel-booking' ); ?>" <?php disabled( ! $isValid ); ?>>
	</p>
</form>
<?php
