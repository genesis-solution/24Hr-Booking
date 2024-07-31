<?php

/**
 * Available variables:
 *     string $actionUrl
 *     string $nextStep
 *     string $checkInDate Date in human-readable format.
 *     string $checkOutDate Date in human-readable format.
 *     array $mapRooms [Room ID => Reserved room ID]
 *     array $copyFrom [Reserved room ID => Room title] (already with "— Add new —" item).
 *     array $roomsList [Room ID => Room title]
 *
 * @since 3.8
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?>
<form class="" action="<?php echo esc_attr( $actionUrl ); ?>" method="POST">
	<input type="hidden" name="step" value="<?php echo esc_html( $nextStep ); ?>">
	<input type="hidden" name="check_in_date" value="<?php echo esc_html( $checkInDate ); ?>">
	<input type="hidden" name="check_out_date" value="<?php echo esc_html( $checkOutDate ); ?>">

	<h2><?php esc_html_e( 'Choose how to associate data', 'motopress-hotel-booking' ); ?></h2>
	<p class="description"><?php esc_html_e( 'Use Source Accommodation to assign pre-filled booking information available in the original booking, e.g., full guest name, selected rate, services, etc.', 'motopress-hotel-booking' ); ?></p>

	<table class="widefat striped fixed">
		<thead>
			<tr>
				<th class="row-title column-room"><?php esc_html_e( 'Source accommodation', 'motopress-hotel-booking' ); ?></th>
				<th class="row-title column-transition">&nbsp;</th>
				<th class="row-title column-preset-room"><?php esc_html_e( 'Target accommodation', 'motopress-hotel-booking' ); ?></th>
			</tr>
		</thead>
		<tbody>
			<?php
			$index = 0;

			foreach ( $mapRooms as $roomId => $reservedRoomId ) {
				?>
				<tr>
					<td class="column-preset-room">
                        <?php // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
						<input type="hidden" name="map_rooms[<?php echo $index; ?>][room_id]" value="<?php echo esc_attr( $roomId ); ?>">
                        <?php // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
						<select name="map_rooms[<?php echo $index; ?>][reserved_room_id]">
                            <?php // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
							echo mphb_tmpl_render_select_options( $copyFrom, $reservedRoomId );
							?>
						</select>
					</td>
					<td class="column-transition">&rarr;</td>
					<td class="column-room"><?php echo esc_html( $roomsList[ $roomId ] ); ?></td>
				</tr>
				<?php
				$index++;
			}
			?>
		</tbody>
	</table>

	<p class="mphb-submit-button-wrapper">
		<input type="submit" name="edit-booking" class="button button-primary" value="<?php esc_attr_e( 'Continue', 'motopress-hotel-booking' ); ?>">
	</p>
</form>
<?php
