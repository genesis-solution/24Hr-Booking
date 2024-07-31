<?php
/**
 * Available variables
 * - string $id
 * - string $actionUrl Action URL for search form
 * - string $checkInDate
 * - string $checkOutDate
 * - int $roomTypeId
 * - int $adults
 * - int $children
 * - array $adultsList
 * - array $childrenList
 * - array $roomsList The list of accommodation types
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$jsDateFormat = MPHB()->settings()->dateTime()->getDateFormatJS();

/** @hooked None */
do_action( 'mphb_cb_search_form_before_start' );

$firstAvailableCheckInDate = MPHB()->getCoreAPI()->getFirstAvailableCheckInDate(
	0,
	MPHB()->settings()->main()->isBookingRulesForAdminDisabled()
)->format( 'Y-m-d' );
?>

<form method="GET" class="mphb_cb_search_form mphb-search-form" action="<?php echo esc_url( $actionUrl ); ?>" data-first_available_check_in_date="<?php echo esc_attr( $firstAvailableCheckInDate ); ?>">

	<?php
		/**
		 * @hooked \MPHB\Admin\MenuPages\CreateBooking\Step::printQueryHiddenFields - 10
		 */
		do_action( 'mphb_cb_search_form_after_start' );
	?>

	<p class="mphb-check-in-date">
		<label for="<?php echo esc_attr( 'mphb_check_in_date-' . $id ); ?>">
			<?php esc_html_e( 'Check-in', 'motopress-hotel-booking' ); ?>
			<abbr title="<?php echo esc_html( sprintf( _x( 'Formatted as %s', 'Date format tip', 'motopress-hotel-booking' ), $jsDateFormat ) ); ?>">*</abbr>
		</label>
		<br />
		<?php // Skip name in date input (see [MB-397]) ?>
		<input
			id="<?php echo esc_attr( 'mphb_check_in_date-' . $id ); ?>"
			class="mphb-datepick"
			type="text"
			inputmode="none"
			value="<?php echo esc_attr( $checkInDate ); ?>"
			placeholder="<?php esc_attr_e( 'Check-in Date', 'motopress-hotel-booking' ); ?>"
			required="required"
			autocomplete="off"
			data-datepick-group="<?php echo esc_attr( $id ); ?>"
		/>
	</p>

	<p class="mphb-check-out-date">
		<label for="<?php echo esc_attr( 'mphb_check_out_date-' . $id ); ?>">
			<?php esc_html_e( 'Check-out', 'motopress-hotel-booking' ); ?>
			<abbr title="<?php echo esc_html( sprintf( _x( 'Formatted as %s', 'Date format tip', 'motopress-hotel-booking' ), $jsDateFormat ) ); ?>">*</abbr>
		</label>
		<br />
		<?php // Skip name in date input (see [MB-397]) ?>
		<input
			id="<?php echo esc_attr( 'mphb_check_out_date-' . $id ); ?>"
			class="mphb-datepick"
			type="text"
			inputmode="none"
			value="<?php echo esc_attr( $checkOutDate ); ?>"
			placeholder="<?php esc_attr_e( 'Check-out Date', 'motopress-hotel-booking' ); ?>"
			required="required"
			autocomplete="off"
			data-datepick-group="<?php echo esc_attr( $id ); ?>"
		/>
	</p>

	<p class="mphb-room-type-id">
		<label for="<?php echo esc_attr( 'mphb_room_type_id-' . $id ); ?>">
			<?php esc_html_e( 'Accommodation Type', 'motopress-hotel-booking' ); ?>
		</label>
		<br />
		<select id="<?php echo esc_attr( 'mphb_room_type_id-' . $id ); ?>" name="mphb_room_type_id">
			<?php foreach ( $roomsList as $value => $label ) { ?>
				<option value="<?php echo esc_attr( $value ); ?>" <?php selected( $roomTypeId, $value ); ?>><?php echo esc_html( $label ); ?></option>
			<?php } ?>
		</select>
	</p>

	<p class="mphb-adults">
		<label for="<?php echo esc_attr( 'mphb_adults-' . $id ); ?>">
			<?php esc_html_e( 'Adults', 'motopress-hotel-booking' ); ?>
		</label>
		<br />
		<select id="<?php echo esc_attr( 'mphb_adults-' . $id ); ?>" name="mphb_adults">
			<?php foreach ( $adultsList as $value => $label ) { ?>
				<option value="<?php echo esc_attr( $value ); ?>" <?php selected( $adults, $value ); ?>><?php echo esc_html( $label ); ?></option>
			<?php } ?>
		</select>
	</p>

	<p class="mphb-children">
		<label for="<?php echo esc_attr( 'mphb_children-' . $id ); ?>">
			<?php
				$childrenAgeText = MPHB()->settings()->main()->getChildrenAgeText();
			if ( empty( $childrenAgeText ) ) {
				esc_html_e( 'Children', 'motopress-hotel-booking' );
			} else {
				printf( esc_html__( 'Children %s', 'motopress-hotel-booking' ), esc_html( $childrenAgeText ) );
			}
			?>
		</label>
		<br />
		<select id="<?php echo esc_attr( 'mphb_children-' . $id ); ?>" name="mphb_children">
			<?php foreach ( $childrenList as $value => $label ) { ?>
				<option value="<?php echo esc_attr( $value ); ?>" <?php selected( $children, $value ); ?>><?php echo esc_html( $label ); ?></option>
			<?php } ?>
		</select>
	</p>

	<?php
		/**
		 * @hooked \MPHB\Admin\MenuPages\CreateBooking\Step::printDateHiddenFields - 10
		 */
		do_action( 'mphb_cb_search_form_before_submit_button' );
	?>

	<p class="mphb-submit-button-wrapper">
		<input type="submit" class="button" value="<?php esc_attr_e( 'Search', 'motopress-hotel-booking' ); ?>" />
	</p>

	<?php
		/** @hooked None */
		do_action( 'mphb_cb_search_form_before_end' );
	?>

</form>

<?php

/** @hooked None */
do_action( 'mphb_cb_search_form_after_end' );
