<?php

namespace MPHB\Shortcodes;

use MPHB\Utils\BookingUtils;
use MPHB\Utils\DateUtils;
use MPHB\UserActions\BookingCancellationAction;

/**
 * @since 3.9.9
 */
class BookingCancellationShortcode extends AbstractShortcode {
	protected $name = 'mphb_booking_cancellation';

	public function render( $atts, $content, $shortcodeName ) {

		$defaultAtts = array(
			'class' => '',
		);

		$atts = shortcode_atts( $defaultAtts, $atts, $shortcodeName );

		$booking = $this->fetchBooking();

		$status = $this->fetchStatusCancellationNotPossible();

		// Render shortcode
		$wrapperClass = apply_filters( 'mphb_sc_booking_cancellation_wrapper_class', 'mphb_sc_booking_confirmation' );
		$wrapperClass = trim( $wrapperClass . ' ' . $atts['class'] );

		$output = '<div class="' . esc_attr( $wrapperClass ) . '">';

		if ( $status ) {
			$output .= $this->renderStatusCancellationNotPossible( $status );
		} else {
			$output .= $this->renderBookingCancellation( $booking );
		}

		$output .= $this->renderBottomInformation();

		$output .= '</div>';

		return $output;
	}

	public function fetchStatusCancellationNotPossible() {

		if ( ! isset( $_GET['mphb_cancellation_status'] ) ) {

			return false;

		} else {

			$mphb_cancellation_status = sanitize_text_field( wp_unslash( $_GET['mphb_cancellation_status'] ) );

			if ( in_array(
				$mphb_cancellation_status,
				array(
					BookingCancellationAction::STATUS_INVALID_REQUEST,
					BookingCancellationAction::STATUS_ALREADY_CANCELLED,
					BookingCancellationAction::STATUS_CANCELLATION_NOT_POSSIBLE,
				)
			) ) {

				return $mphb_cancellation_status;
			}
		}

		return false;
	}

	public function fetchBooking() {

		if ( ! isset( $_GET['booking_id'], $_GET['booking_key'] ) ) {
			return null;
		}

		$bookingId  = absint( $_GET['booking_id'] );
		$bookingKey = sanitize_text_field( wp_unslash( $_GET['booking_key'] ) );

		$booking = MPHB()->getBookingRepository()->findById( $bookingId );

		if ( is_null( $booking ) || $booking->getKey() !== $bookingKey ) {
			return null;
		}

		return $booking;
	}

	public function renderStatusCancellationNotPossible( $status ) {
		switch ( $status ) {
			case BookingCancellationAction::STATUS_INVALID_REQUEST:
				mphb_get_template_part( 'shortcodes/booking-cancellation/invalid-request' );
				break;
			case BookingCancellationAction::STATUS_CANCELLATION_NOT_POSSIBLE:
				mphb_get_template_part( 'shortcodes/booking-cancellation/not-possible' );
				break;
			case BookingCancellationAction::STATUS_ALREADY_CANCELLED:
				mphb_get_template_part( 'shortcodes/booking-cancellation/already-cancelled' );
				break;
		}
	}

	public function renderBookingCancellation( $booking ) {
		$output = '';

		$output .= $this->renderBookingDetails( $booking );
		$output .= $this->renderCancellationButton( $booking );

		return $output;
	}

	public function renderBookingDetails( $booking ) {
		if ( is_null( $booking ) ) {
			return '';
		}

		$reservedTypes = BookingUtils::getReservedRoomTypesList( $booking );
		if ( empty( $reservedTypes ) ) {
			$accommodations = '&#8212;';
		} else {
			$links          = array_map(
				function ( $roomTypeId, $title ) {
					return '<a href="' . esc_url( get_permalink( $roomTypeId ) ) . '">' . esc_html( $title ) . '</a>';
				},
				array_keys( $reservedTypes ),
				$reservedTypes
			);
			$accommodations = implode( ', ', $links );
		}

		$checkInDateFormatted  = DateUtils::formatDateWPFront( $booking->getCheckInDate() );
		$checkOutDateFormatted = DateUtils::formatDateWPFront( $booking->getCheckOutDate() );

		ob_start();

		mphb_get_template_part(
			'shortcodes/booking-details/booking-details',
			array(
				'booking'               => $booking,
				'checkInDateFormatted'  => $checkInDateFormatted,
				'checkOutDateFormatted' => $checkOutDateFormatted,
				'accommodations'        => $accommodations,
			)
		);

		$output = ob_get_clean();

		return $output;
	}

	public function renderCancellationButton( $booking ) {
		if ( is_null( $booking ) ) {
			return '';
		}

		$step = BookingCancellationAction::STEP_CONFIRMED_BY_USER;

		$cancellationLink = MPHB()->userActions()->getBookingCancellationAction()->generateLink( $booking, $step );
		$cancellationLink = apply_filters( 'wpml_permalink', $cancellationLink, apply_filters( 'wpml_current_language', null ) );

		ob_start();

		mphb_get_template_part(
			'shortcodes/booking-cancellation/booking-cancellation-button',
			array(
				'cancellationLink' => $cancellationLink,
			)
		);

		$output = ob_get_clean();
		return $output;
	}

	public function renderBottomInformation() {
		ob_start();
		do_action( 'mphb_sc_booking_cancellation_bottom' );
		return ob_get_clean();
	}
}


