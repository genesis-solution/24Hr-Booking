<?php

namespace MPHB\Shortcodes\CheckoutShortcode;

abstract class Step {

	protected $isValidStep = false;

	/**
	 *
	 * @var array
	 */
	protected $errors = array();

	/**
	 * @var \DateTime
	 */
	protected $checkInDate;
	/**
	 * @var \DateTime
	 */
	protected $checkOutDate;

	abstract public function setup();

	abstract public function render();

	/**
	 * @return bool
	 *
	 * @since 3.7.0 added new filter - "mphb_sc_checkout_parse_check_in_date".
	 */
	protected function parseCheckInDate() {
		$this->checkInDate = null;

		$dateString = filter_input( INPUT_POST, 'mphb_check_in_date' );

		if ( empty( $dateString ) ) {
			$dateString = filter_input( INPUT_COOKIE, 'mphb_check_in_date' );
		}

		mphb_set_cookie( 'mphb_check_in_date', $dateString );

		$checkInDate = \DateTime::createFromFormat( MPHB()->settings()->dateTime()->getDateTransferFormat(), $dateString );
		$todayDate   = \DateTime::createFromFormat( 'Y-m-d', mphb_current_time( 'Y-m-d' ) );

		$checkInDate = apply_filters( 'mphb_sc_checkout_parse_check_in_date', $checkInDate, $dateString, $todayDate );

		if ( ! $checkInDate ) {
			$this->errors[] = __( 'Check-in date is not valid.', 'motopress-hotel-booking' );
			return false;
		} elseif ( \MPHB\Utils\DateUtils::calcNights( $todayDate, $checkInDate ) < 0 ) {
			$this->errors[] = __( 'Check-in date cannot be earlier than today.', 'motopress-hotel-booking' );
			return false;
		}

		if ( $checkInDate instanceof \DateTime ) {
			$this->checkInDate = $checkInDate;
		}

		return true;
	}

	/**
	 * @return bool
	 *
	 * @since 3.7.0 added new filter - "mphb_sc_checkout_parse_check_out_date".
	 */
	protected function parseCheckOutDate() {
		$this->checkOutDate = null;
		$dateString         = filter_input( INPUT_POST, 'mphb_check_out_date' );

		if ( empty( $dateString ) ) {
			$dateString = filter_input( INPUT_COOKIE, 'mphb_check_out_date' );
		}

		mphb_set_cookie( 'mphb_check_out_date', $dateString );

		$checkOutDate = \MPHB\Utils\DateUtils::createCheckOutDate( MPHB()->settings()->dateTime()->getDateTransferFormat(), $dateString );

		$checkOutDate = apply_filters( 'mphb_sc_checkout_parse_check_out_date', $checkOutDate, $dateString, $this->checkInDate );

		if ( ! $checkOutDate ) {
			$this->errors[] = __( 'Check-out date is not valid.', 'motopress-hotel-booking' );
			return false;
		} elseif ( isset( $this->checkInDate ) && ! MPHB()->getRulesChecker()->verify( $this->checkInDate, $checkOutDate ) ) {
			$this->errors[] = __( 'Nothing found. Please try again with different search parameters.', 'motopress-hotel-booking' );
			return false;
		}

		$this->checkOutDate = $checkOutDate;
		return true;
	}

	protected function showAlreadyBookedMessage() {
		$message = apply_filters( 'mphb_sc_checkout_already_booked_message', __( 'Accommodation is already booked.', 'motopress-hotel-booking' ) );
		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo $message;
	}

	protected function showSuccessMessage() {
		switch ( MPHB()->settings()->main()->getConfirmationMode() ) {
			case 'auto':
				ob_start();
				?>
				<h4 class="mphb-reservation-submitted-title"><?php esc_html_e( 'Reservation submitted', 'motopress-hotel-booking' ); ?></h4>
				<p class="mphb_sc_checkout-success-reservation-message"><?php esc_html_e( 'Details of your reservation have just been sent to you in a confirmation email. Please check your inbox to complete booking.', 'motopress-hotel-booking' ); ?></p>
				<?php
				// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				echo apply_filters( 'mphb_sc_checkout_auto_mode_success_message', ob_get_clean() );
				break;
			case 'manual':
				ob_start();
				?>
				<h4 class="mphb-reservation-submitted-title"><?php esc_html_e( 'Reservation submitted', 'motopress-hotel-booking' ); ?></h4>
				<p class="mphb_sc_checkout-success-reservation-message"><?php esc_html_e( 'We received your booking request. Once it is confirmed we will notify you via email.', 'motopress-hotel-booking' ); ?></p>
				<?php
				// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				echo apply_filters( 'mphb_sc_checkout_manual_mode_success_message', ob_get_clean() );
				break;
			case 'payment':
				ob_start();
				?>
				<h4 class="mphb-reservation-submitted-title"><?php esc_html_e( 'Reservation submitted', 'motopress-hotel-booking' ); ?></h4>
				<p class="mphb_sc_checkout-success-reservation-message"><?php esc_html_e( 'We received your booking request. Once it is confirmed we will notify you via email.', 'motopress-hotel-booking' ); ?></p>
				<?php
				// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				echo apply_filters( 'mphb_sc_checkout_payment_mode_success_message', ob_get_clean() );
				break;
		}
	}

	public function showErrorsMessage() {
		?>
		<p class="mphb-data-incorrect">
			<?php do_action( 'mphb_sc_checkout_errors_content', $this->errors ); ?>
		</p>
		<?php
	}

	protected function stepValid() {
		$this->isValidStep = true;
	}

}
