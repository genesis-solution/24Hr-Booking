<?php

/**
 *
 * @since 4.2.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( $user->ID ) {

	$userDisplayName = $user->data->display_name;
	$bookingsUrl     = mphb_create_url( 'bookings', '', $permalink );
	$detailsUrl      = mphb_create_url( 'account-details', '', $permalink );

	$allowed_html = array(
		'a' => array(
			'href' => array(),
		),
	);

	?>
	<p>
	<?php
		printf(
			wp_kses(
				__( 'Hello %1$s (not %1$s? <a href="%2$s">Log out</a>).', 'motopress-hotel-booking' ),
				$allowed_html
			),
			'<strong>' . esc_html( $userDisplayName ) . '</strong>',
			esc_url( wp_logout_url() )
		);
	?>
	</p>
	<p>
	<?php
		printf(
			wp_kses(
				__( 'From your account dashboard you can view <a href="%1$s">your recent bookings</a> or edit your <a href="%2$s">password and account details</a>.', 'motopress-hotel-booking' ),
				$allowed_html
			),
			esc_url( $bookingsUrl ),
			esc_url( $detailsUrl )
		);
	?>
	</p>
	<?php
}
