<?php

namespace MPHB;

/**
 * @since 3.6.0
 * @since 3.6.0 MPHB\Downloader was replaced with MPHB\ActionsHandler.
 * @since 3.6.0 method doActions() was replaced with doEarlyActions() and doLateActions().
 */
class ActionsHandler {

	public function __construct() {

		// Late action: wait for the plugins when it initialize more components
		add_action( 'init', array( $this, 'doLateActions' ), 1004 );
	}

	/**
	 * @since 3.6.0
	 */
	public function doLateActions() {
		if ( ! isset( $_GET['mphb_action'] ) ) {
			return;
		}

		switch ( $_GET['mphb_action'] ) {
			// Requires gateways and API to initialize first
			case 'handle_stripe_errors':
				$this->handleStripeErrors();
				break;
			case 'force_upgrade':
				$this->forceUpgrader();
				break;
			case 'update_confirmation_endpoints':
				$this->updateConfirmationEndpoints();
				break;
			case 'hide_notice':
				$this->hideNotice();
				break;
		}
	}

	/**
	 * @since 3.6.0
	 */
	protected function handleStripeErrors() {
		if ( ! mphb_verify_nonce( 'handle_stripe_errors' ) ) {
			$this->fireError( __( 'Nonce verification failed.', 'motopress-hotel-booking' ) );
		}

		if ( ! isset( $_REQUEST['source'] ) ) {
			$this->fireError( __( 'Source ID is missing.', 'motopress-hotel-booking' ) );
		}

        // phpcs:ignore WordPress.Security.ValidatedSanitizedInput
		$sourceId = $_REQUEST['source'];

		if ( is_array( $sourceId ) ) {

			$sourceId = array_map( 'wp_unslash', $sourceId );
			$sourceId = array_map( 'sanitize_text_field', $sourceId );

		} else {
			$sourceId = sanitize_text_field( wp_unslash( $sourceId ) );
		}

		try {
			$stripeApi = MPHB()->gatewayManager()->getGateway( 'stripe' )->getApi();
			$source    = $stripeApi->retrieveSource( $sourceId );

			// Show Transaction Failed Page instead of Payment Success Page
			if ( ( $source->status == 'canceled' || $source->status == 'failed' ) && ! is_admin() ) {
				wp_redirect( MPHB()->settings()->pages()->getPaymentFailedPageUrl() );
				exit;
			}
		} catch ( \Exception $e ) {
			$this->fireError( $e->getMessage() );
		}
	}

	protected function forceUpgrader() {
		if ( ! isset( $_GET['mphb_action'] ) ||
			! mphb_verify_nonce( sanitize_text_field( wp_unslash( $_GET['mphb_action'] ) ), 'mphb_notice_nonce' ) ) {
			return;
		}

		MPHB()->upgrader()->forceUpgrade();
	}

	protected function updateConfirmationEndpoints() {
		if ( ! isset( $_GET['mphb_action'] ) ||
			! mphb_verify_nonce( sanitize_text_field( wp_unslash( $_GET['mphb_action'] ) ), 'mphb_notice_nonce' ) ) {
			return;
		}

		$bookingConfirmedId    = MPHB()->settings()->pages()->getBookingConfirmedPageId();
		$reservationReceivedId = MPHB()->settings()->pages()->getReservationReceivedPageId();

		$pageContent = MPHB()->getShortcodes()->getBookingConfirmation()->generateShortcode();

		if ( $bookingConfirmedId != 0 ) {
			wp_update_post(
				array(
					'ID'           => $bookingConfirmedId,
					'post_content' => $pageContent,
				)
			);
		}

		if ( $reservationReceivedId != 0 ) {
			wp_update_post(
				array(
					'ID'           => $reservationReceivedId,
					'post_content' => $pageContent,
				)
			);
		}

		MPHB()->notices()->hideNotice( sanitize_text_field( wp_unslash( $_GET['mphb_action'] ) ) );
	}

	protected function hideNotice() {
		if ( ! isset( $_GET['mphb_action'] ) ||
			! mphb_verify_nonce( sanitize_text_field( wp_unslash( $_GET['mphb_action'] ) ), 'mphb_notice_nonce' ) ) {
			return;
		}

		if ( ! isset( $_GET['notice_id'] ) ) {
			return;
		}

		$noticeId = sanitize_text_field( wp_unslash( $_GET['notice_id'] ) );

		MPHB()->notices()->hideNotice( $noticeId );
	}

	public function fireError( $message ) {
		if ( is_admin() ) {
            // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			wp_die( $message, esc_html__( 'Error', 'motopress-hotel-booking' ), array( 'response' => 403 ) );
		}

		return false;
	}
}
