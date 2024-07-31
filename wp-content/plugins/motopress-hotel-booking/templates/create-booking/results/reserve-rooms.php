<?php
/**
 * Available variables
 * - string $actionUrl Action URL for search form
 * - string $checkInDate
 * - string $checkOutDate
 * - array $roomsList [%Room type ID% => [title, rooms => [id, type_id, title, adults, children, price]]]
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/** @hooked None */
do_action( 'mphb_cb_reserve_rooms_form_before_start' );

?>

<form method="POST" class="mphb_cb_reserve_rooms" action="<?php echo esc_url( $actionUrl ); ?>">

	<?php
		/** @hooked None */
		do_action( 'mphb_reserve_rooms_form_after_start' );
	?>

	<?php foreach ( $roomsList as $roomTypeId => $roomType ) { ?>
		<?php $rooms = $roomType['rooms']; ?>

		<h4><a href="<?php echo esc_url( $roomType['url'] ); ?>" target="_blank"><?php echo esc_html( $roomType['title'] ); ?></a></h4>

		<table class="widefat striped fixed">
			<thead>
				<tr>
					<th class="check-column">&nbsp;</th>
					<th class="row-title"><?php esc_html_e( 'Title', 'motopress-hotel-booking' ); ?></th>
					<th class="row-title"><?php esc_html_e( 'Capacity', 'motopress-hotel-booking' ); ?></th>
					<th class="row-title"><?php esc_html_e( 'Base price', 'motopress-hotel-booking' ); ?></th>
				</tr>
			</thead>
			<tbody>
				<?php foreach ( $rooms as $room ) { ?>
					<tr>
						<td>
							<input type="checkbox" name="mphb_rooms[<?php echo esc_attr( $room['type_id'] ); ?>][]" value="<?php echo esc_attr( $room['id'] ); ?>" id="mphb_room-<?php echo esc_attr( $room['id'] ); ?>"/>
						</td>
						<td>
							<label for="mphb_room-<?php echo esc_attr( $room['id'] ); ?>"><?php echo esc_html( $room['title'] ); ?></label>
						</td>
						<td>
							<?php
								echo esc_html__( 'Adults:', 'motopress-hotel-booking' ), '&nbsp;', esc_html( $room['adults'] ), ' ';
								echo esc_html__( 'Children:', 'motopress-hotel-booking' ), '&nbsp;', esc_html( $room['children'] );
							?>
						</td>
						<td>
							<?php // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
							echo mphb_format_price( $room['price'] );
							?>
						</td>
					</tr>
				<?php } ?>
			</tbody>
		</table>

	<?php } // For each room type ?>

	<?php
		/**
		 * @hooked \MPHB\Admin\MenuPages\CreateBooking\Step::printDateHiddenFields - 10
		 * @hooked \MPHB\Admin\MenuPages\CreateBooking\Step::printPresetHiddenFields - 20
		 */
		do_action( 'mphb_cb_reserve_rooms_form_before_submit_button' );
	?>

	<p class="mphb-submit-button-wrapper">
		<input type="submit" class="button button-primary" value="<?php esc_attr_e( 'Reserve', 'motopress-hotel-booking' ); ?>" />
	</p>

	<?php
		/** @hooked None */
		do_action( 'mphb_cb_reserve_rooms_form_before_end' );
	?>

</form>

<?php
	/** @hooked None */
	do_action( 'mphb_cb_reserve_rooms_form_after_end' );
?>
