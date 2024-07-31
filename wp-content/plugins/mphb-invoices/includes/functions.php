<?php

/**
 * @return \MPHB\Addons\Invoice\Plugin
 */
function mphbinvoice() {
	return \MPHB\Addons\Invoice\Plugin::getInstance();
}


/**
 * Add 'Invoice' button to 'Bookings' table
 *
 * @return array $actions
 */
function mphb_invoice_add_print_button( $actions, $post ) {

	global $current_screen;

	if ( $current_screen->id !== 'edit-mphb_booking' ) {
		return $actions;
	}

	if ( ! current_user_can( \MPHB\Addons\Invoice\UsersAndRoles\Capabilities::GENERATE_INVOICES ) ) {
		return $actions;
	}

	$booking = MPHB()->getBookingRepository()->findById( $post->ID );

	if ( $booking->isImported() ) {
		return $actions;
	}

	$nonce        = wp_create_nonce( 'mphb-invoice' );
	$invoice_link = admin_url( 'admin.php?post=' . $post->ID . '&action=mphb-invoice&_wpnonce=' . $nonce );

	$actions['mphb-invoice'] = '<a target="_blank" href="' . $invoice_link
		. '" title="'
		. esc_attr( __( 'Open invoice in PDF', 'mphb-invoices' ) )
		. '">' . __( 'Invoice', 'mphb-invoices' ) . '</a>';

	return $actions;
}


/**
 * Print PDF action
 *
 * @return void
 */
function mphb_invoice_action_printpdf() {

	if (
	   ( isset( $_GET['action'] ) && 'mphb-invoice' == $_GET['action'] )
	   && ! isset( $_GET['post'] )
	) {
		wp_die( esc_html__( 'No booking found!', 'mphb-invoices' ) );
	}

	if ( ! empty( $_GET['_wpnonce'] ) &&
	   wp_verify_nonce( sanitize_text_field( wp_unslash( $_GET['_wpnonce'] ) ), 'mphb-invoice' ) &&
	   current_user_can( \MPHB\Addons\Invoice\UsersAndRoles\Capabilities::GENERATE_INVOICES )
	) {

		$id = (int) $_GET['post'];

		if ( $id ) {

			mphbinvoice()->pdf()->printPdf( $id );
			exit;

		} else {
			wp_die( esc_html__( 'No booking found!', 'mphb-invoices' ) );
		}
	} else {
		wp_die( esc_html__( 'You don\'t have permissions for this action!', 'mphb-invoices' ) );
	}
}

function mphb_invoice_add_secure_pdf_link() {

	$add_link = get_option( 'mphb_invoice_add_link_to_confirmation', false );
	$html     = '';

	if ( $add_link ) {

		$html = '<div class="mphb-booking-details-section invoice"><p><a class="invoice-link" target="_blank" href="' .
			add_query_arg(
				array(
					'action' => 'mphp-print-invoice',
				)
			)
		   . '">' .
		   __( 'View Invoice', 'mphb-invoices' ) . '</a></p></div>';
	}

	echo wp_kses_post( $html );
}

function mphb_invoice_analyze_request() {

	if ( isset( $_GET['action'] ) &&
		sanitize_text_field( wp_unslash( $_GET['action'] ) ) === 'mphp-print-invoice' ) {

		$booking_id = isset( $_GET['booking_id'] ) ? sanitize_text_field( wp_unslash( $_GET['booking_id'] ) ) : false;
		$payment_id = isset( $_GET['payment_id'] ) ? sanitize_text_field( wp_unslash( $_GET['payment_id'] ) ) : false;

		if ( $booking_id && isset( $_GET['booking_id'] ) ) {

			$bookingKey = empty( $_GET['booking_key'] ) ? '' : sanitize_text_field( wp_unslash( $_GET['booking_key'] ) );
			$booking    = MPHB()->getBookingRepository()->findById( $booking_id );

			if ( $booking->getKey() == $bookingKey ) {

				mphbinvoice()->pdf()->printPdf( $booking_id );
				die();

			} else {
				die( esc_html__( 'No booking found!', 'mphb-invoices' ) );
			}
		} elseif ( $payment_id && isset( $_GET['payment_key'] ) ) {

			$paymentKey = sanitize_text_field( wp_unslash( $_GET['payment_key'] ) );
			$payment    = MPHB()->getPaymentRepository()->findById( $payment_id );
			$booking    = MPHB()->getBookingRepository()->findByPayment( $payment_id );

			if ( $payment->getKey() == $paymentKey ) {

				mphbinvoice()->pdf()->printPdf( $booking->getId() );
				die();
			} else {
				die( esc_html__( 'No booking found!', 'mphb-invoices' ) );
			}
		} else {
			die( esc_html__( 'No booking found!', 'mphb-invoices' ) );
		}
	}
}

/**
 * @return string "Region/City" or "UTC+2".
 */
function mphb_invoice_get_wp_timezone() {
	$timezone = get_option( 'timezone_string', '' );

	if ( empty( $timezone ) ) {
		$gmtOffset = (float) get_option( 'gmt_offset', 0 ); // -2.5

		$hours = abs( (int) $gmtOffset ); // 2

		$minutes = abs( $gmtOffset ) - $hours; // 0.5
		$minutes = round( $minutes * 4 ) / 4; // Only 0, 0.25, 0.5, 0.75 or 1
		$minutes = (int) ( $minutes * 60 ); // Only 0, 15, 30, 45 or 60

		if ( $minutes == 60 ) {
			$hours++;
			$minutes = 0;
		}

		$timezone  = $gmtOffset >= 0 ? 'UTC+' : 'UTC-';
		$timezone .= $hours;

		if ( $minutes > 0 ) {
			$timezone .= ':' . $minutes;
		}
	}

	return $timezone;
}

/**
 * @param string $file
 * @return string
 */
function mphb_invoice_url_to( $file ) {
	return \MPHB\Addons\Invoice\PLUGIN_URL . $file;
}

function mphb_invoice_use_edd_license() {
	return (bool) apply_filters( 'mphb_invoice_use_edd_license', true );
}
