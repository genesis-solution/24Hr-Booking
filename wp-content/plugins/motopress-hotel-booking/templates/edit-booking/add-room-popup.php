<?php

/**
 * Available variables:
 *     array $availableRooms
 *         int    $availableRooms[]['room_id']
 *         string $availableRooms[]['room_title']
 *         int    $availableRooms[]['room_type_id']
 *         string $availableRooms[]['room_type_title']
 *     array $availableRoomTypes [Room type ID => Room type title]
 *
 * @since 3.8
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?>
<div id="mphb-add-room-popup" class="mphb-popup mphb-hide">
	<div class="mphb-popup-backdrop"></div>
	<div class="mphb-popup-body">
		<div class="mphb-header">
			<h2 class="mphb-title mphb-inline"><?php esc_html_e( 'Add Accommodation', 'motopress-hotel-booking' ); ?></h2>
			<button class="mphb-close-popup-button dashicons dashicons-no-alt"></button>
		</div>
		<div class="mphb-content">
			<h2><?php esc_html_e( 'Accommodation Type', 'motopress-hotel-booking' ); ?></h2>
			<select name="room_type_id" class="mphb-room-type-select">
				<option value=""><?php esc_html_e( '— Select —', 'motopress-hotel-booking' ); ?></option>
				<?php foreach ( $availableRoomTypes as $roomTypeId => $title ) { ?>
					<option value="<?php echo esc_attr( $roomTypeId ); ?>"><?php echo esc_html( $title ); ?></option>
				<?php } ?>
			</select>

			<h2><?php esc_html_e( 'Accommodation', 'motopress-hotel-booking' ); ?></h2>
			<select name="room_id" class="mphb-room-select">
				<option value=""><?php esc_html_e( '— Select —', 'motopress-hotel-booking' ); ?></option>
				<?php foreach ( $availableRooms as $roomId => $room ) { ?>
					<option value="<?php echo esc_attr( $roomId ); ?>" data-room-type-id="<?php echo esc_attr( $room['room_type_id'] ); ?>"><?php echo esc_html( $room['room_title'] ); ?></option>
				<?php } ?>
			</select>
		</div>
		<div class="mphb-footer">
			<button class="button button-primary button-hero mphb-submit-popup-button"><?php esc_html_e( 'Add', 'motopress-hotel-booking' ); ?></button>
		</div>
	</div>
</div>
