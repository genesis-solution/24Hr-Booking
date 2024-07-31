<?php

/**
 * Available variables:
 *     \MPHB\Entities\Booking $booking
 *     array $rooms
 *         int $rooms[]['room_id']
 *         string $rooms[]['room_title']
 *         int $rooms[]['room_type_id']
 *         string $rooms[]['room_type_title']
 *         int $rooms[]['rate_id']
 *         string $rooms[]['rate_title']
 *         \MPHB\Entities\Rate[] $rooms[]['allowed_rates']
 *         int $rooms['adults']
 *         int $rooms['children']
 *
 * @since 3.8
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?>
<div class="mphb-checkout-form-wrapper mphb-original-checkout-wrapper">
	<?php do_action( 'mphb_edit_booking_original_checkout', $booking, $rooms ); ?>
</div>
<?php
