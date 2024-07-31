<?php

namespace MPHB;

class Wizard {

	const NONCE_NAME         = 'mphb-wizard-nonce';
	const NONCE_ACTION_START = 'mphb-start';
	const NONCE_ACTION_SKIP  = 'mphb-skip';

	public function __construct() {
		add_action( 'admin_notices', array( $this, 'wizardNotice' ) );
		add_action( 'init', array( $this, 'checkUserAction' ) );
	}

	/**
	 * Display the admin notice to users
	 */
	public function wizardNotice() {

		if ( $this->isWizardPassed() ) {
			return;
		}

		if ( ! $this->checkCapabilities() ) {
			return;
		}

		$startWizardUrl = wp_nonce_url( add_query_arg( 'mphb_wizard_action', 'start' ), self::NONCE_ACTION_START, self::NONCE_NAME );
		$skipUrl        = wp_nonce_url( add_query_arg( 'mphb_wizard_action', 'skip' ), self::NONCE_ACTION_SKIP, self::NONCE_NAME );

		echo '<div class="updated">';
		echo '<p><strong>' . esc_html__( 'Hotel Booking Plugin', 'motopress-hotel-booking' ) . '</strong></p>';
		echo '<p>' . esc_html__( 'Booking Confirmation and Search Results pages are required to handle bookings. Press "Install Pages" button to create and set up these pages. Dismiss this notice if you already installed them.', 'motopress-hotel-booking' ) . '</p>';
		echo '<p><a href="' . esc_url( $startWizardUrl ) . '" class="button-primary">' . esc_html__( 'Install Pages', 'motopress-hotel-booking' ) . '</a>';
		echo '&nbsp;<a href="' . esc_url( $skipUrl ) . '" class="button-secondary">' . esc_html__( 'Skip', 'motopress-hotel-booking' ) . '</a></p>';
		echo '</div>';
	}

	private function checkCapabilities() {
		return current_user_can( 'manage_options' ) && current_user_can( 'publish_pages' );
	}

	private function isWizardPassed() {
		return get_option( 'mphb_wizard_passed', false );
	}

	public function checkUserAction() {
		if ( isset( $_GET['mphb_wizard_action'] ) && ! $this->isWizardPassed() ) {
			switch ( $_GET['mphb_wizard_action'] ) {
				case 'start':
					if ( isset( $_GET[ self::NONCE_NAME ] ) &&
						wp_verify_nonce( sanitize_text_field( wp_unslash( $_GET[ self::NONCE_NAME ] ) ), self::NONCE_ACTION_START ) ) {

						$this->start();
					}
					break;
				case 'skip':
					if ( isset( $_GET[ self::NONCE_NAME ] ) &&
						wp_verify_nonce( sanitize_text_field( wp_unslash( $_GET[ self::NONCE_NAME ] ) ), self::NONCE_ACTION_SKIP ) ) {

						$this->pass();
					}
					break;
			}
		}
	}

	public function start() {
		$this->addRoomsPage();
		$this->addSearchPage();
		$this->addResultsPage();
		$this->addBookingCancellationPage();
		$checkoutPageId = $this->addCheckoutPage();
		$this->addBookingConfirmedPage( $checkoutPageId );
		$this->addBookingCanceledPage( $checkoutPageId );
		$this->addReservationReceivedPage( $checkoutPageId );
		$this->addFailedTransactionPage( $checkoutPageId );
		$this->addMyAccountPage();
		$this->pass();
	}

	public function addSearchPage() {
		$title   = __( 'Search Availability', 'motopress-hotel-booking' );
		$content = MPHB()->getShortcodes()->getSearch()->generateShortcode();
		$this->createPage( $title, $content );
	}

	public function addResultsPage() {
		$searchResultsPage = MPHB()->settings()->pages()->getSearchResultsPageId();

		if ( empty( $searchResultsPage ) ) {
			$title   = __( 'Search Results', 'motopress-hotel-booking' );
			$content = MPHB()->getShortcodes()->getSearchResults()->generateShortcode();
			$id      = $this->createPage( $title, $content );
			if ( ! is_wp_error( $id ) ) {
				MPHB()->settings()->pages()->setSearchResultsPage( $id );
			}
		}
	}

	public function addRoomsPage() {
		$title   = __( 'Accommodations', 'motopress-hotel-booking' );
		$content = MPHB()->getShortcodes()->getRooms()->generateShortcode();
		$this->createPage( $title, $content );
	}

	/**
	 * @return int Page ID or 0 (if error occur).
	 */
	public function addCheckoutPage() {
		$checkoutPage = MPHB()->settings()->pages()->getCheckoutPageId();

		if ( empty( $checkoutPage ) ) {
			$title   = __( 'Booking Confirmation', 'motopress-hotel-booking' );
			$content = MPHB()->getShortcodes()->getCheckout()->generateShortcode();
			$id      = $this->createPage( $title, $content );
			if ( ! is_wp_error( $id ) ) {
				MPHB()->settings()->pages()->setCheckoutPage( $id );
				return $id;
			} else {
				return 0;
			}
		}
	}

	/**
	 * @since 3.7.0 the name was changed from "addBookingConfirmationPage".
	 */
	public function addBookingConfirmedPage( $parentId ) {
		$bookingConfirmedPage = MPHB()->settings()->pages()->getBookingConfirmedPageId();

		if ( empty( $bookingConfirmedPage ) ) {
			$title   = __( 'Booking Confirmed', 'motopress-hotel-booking' );
			$content = MPHB()->getShortcodes()->getBookingConfirmation()->generateShortcode();
			$id      = $this->createPage( $title, $content, $parentId );
			if ( ! is_wp_error( $id ) ) {
				MPHB()->settings()->pages()->setBookingConfirmPage( $id );
			}
		}
	}

	public function addBookingCanceledPage( $parentId ) {
		$bookingCanceledPage = MPHB()->settings()->pages()->getUserCancelRedirectPageId();

		if ( empty( $bookingCanceledPage ) ) {
			$title   = __( 'Booking Canceled', 'motopress-hotel-booking' );
			$content = __( 'Your reservation is canceled.', 'motopress-hotel-booking' );
			$id      = $this->createPage( $title, $content, $parentId );
			if ( ! is_wp_error( $id ) ) {
				MPHB()->settings()->pages()->setUserCancelRedirectPage( $id );
			}
		}
	}

	/**
	 *
	 * @since 3.9.9
	 */
	public function addBookingCancellationPage() {
		$bookingCancellationPage = MPHB()->settings()->pages()->getBookingConfirmCancellationPage();

		if ( empty( $bookingCancellationPage ) ) {
			$title   = __( 'Booking Cancellation', 'motopress-hotel-booking' );
			$content = MPHB()->getShortcodes()->getBookingCancellation()->generateShortcode();
			$id      = $this->createPage( $title, $content );
			if ( ! is_wp_error( $id ) ) {
				MPHB()->settings()->pages()->setBookingConfirmCancellationPage( $id );
				return $id;
			} else {
				return 0;
			}
		}
	}

	/**
	 * @since 3.7.0 the name was changed from "addPaymentSuccessPage".
	 */
	public function addReservationReceivedPage( $parentId ) {
		$reservationReceivedPage = MPHB()->settings()->pages()->getPaymentSuccessPageId();

		if ( empty( $reservationReceivedPage ) ) {
			$title   = __( 'Reservation Received', 'motopress-hotel-booking' );
			$content = MPHB()->getShortcodes()->getBookingConfirmation()->generateShortcode();
			$id      = $this->createPage( $title, $content, $parentId );
			if ( ! is_wp_error( $id ) ) {
				MPHB()->settings()->pages()->setPaymentSuccessPage( $id );
			}
		}
	}

	public function addFailedTransactionPage( $parentId ) {
		$failedTransactionPage = MPHB()->settings()->pages()->getPaymentFailedPageId();

		if ( empty( $failedTransactionPage ) ) {
			$title   = __( 'Transaction Failed', 'motopress-hotel-booking' );
			$content = __( 'Unfortunately, your transaction cannot be completed at this time. Please try again or contact us.', 'motopress-hotel-booking' );
			$id      = $this->createPage( $title, $content, $parentId );
			if ( ! is_wp_error( $id ) ) {
				MPHB()->settings()->pages()->setPaymentFailedPage( $id );
			}
		}
	}

	/**
	 *
	 * @since 4.2.0
	 */
	public function addMyAccountPage() {
		$myAccountPage = MPHB()->settings()->pages()->getMyAccountPageId();

		if ( empty( $myAccountPage ) ) {
			$title   = __( 'My Account', 'motopress-hotel-booking' );
			$content = MPHB()->getShortcodes()->getAccount()->generateShortcode();
			$id      = $this->createPage( $title, $content );

			if ( ! is_wp_error( $id ) ) {
				MPHB()->settings()->pages()->setMyAccountPageId( $id );
				return $id;
			} else {
				return 0;
			}
		}
	}

	public function createPage( $title, $content, $parentId = 0 ) {
		global $user_ID;
		$page = array(
			'post_type'    => 'page',
			'post_parent'  => $parentId,
			'post_author'  => $user_ID,
			'post_status'  => 'publish',
			'post_content' => $content,
			'post_title'   => $title,
		);

		$pageid = wp_insert_post( $page );
		return $pageid;
	}

	public function pass() {
		update_option( 'mphb_wizard_passed', true );
	}

}
