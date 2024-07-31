<?php

namespace MPHB\Settings;

class PageSettings {

	/**
	 * Retrieve checkout page id.
	 * The Checkout Page ID or 0 if checkout page not setted.
	 *
	 * @return int
	 */
	public function getCheckoutPageId() {
		return $this->getPageId( 'checkout' );
	}

	/**
	 * Retrieve checkout page url.
	 * Description:
	 * The permalink URL or false if post does not exist or checkout page not setted.
	 *
	 * @return string|bool
	 */
	public function getCheckoutPageUrl() {
		$url = $this->getUrl( $this->getCheckoutPageId() );

		if ( MPHB()->settings()->payment()->isForceCheckoutSSL() ) {
			$url = preg_replace( '/^http:/', 'https:', $url );
		}

		return $url;
	}

	/**
	 * @return int
	 *
	 * @since 3.7.0 the name was changed from "getBookingConfirmPageId".
	 */
	public function getBookingConfirmedPageId() {
		/** TODO Rename with "booking_confirmed". */
		return $this->getPageId( 'booking_confirmation' );
	}

	/**
	 * @return string|false
	 *
	 * @since 3.7.0 the name was changed from "getBookingConfirmPageUrl".
	 */
	public function getBookingConfirmedPageUrl() {
		return $this->getUrl( $this->getBookingConfirmedPageId() );
	}

	/**
	 *
	 * @return int
	 */
	public function getSearchResultsPageId() {
		return $this->getPageId( 'search_results' );
	}

	/**
	 *
	 * @return string|bool False if search results page was not setted.
	 */
	public function getSearchResultsPageUrl() {
		return $this->getUrl( $this->getSearchResultsPageId() );
	}

	/**
	 *
	 * @return int|false
	 */
	public function getUserCancelRedirectPageId() {
		return $this->getPageId( 'user_cancel_redirect' );
	}

	/**
	 *
	 * @return string|false
	 */
	public function getUserCancelRedirectPageUrl() {
		return $this->getUrl( $this->getUserCancelRedirectPageId() );
	}

	/**
	 * @return int
	 * @deprecated since 3.7
	 * @see \MPHB\Settings\PageSettings::getReservationReceivedPageId()
	 */
	public function getPaymentSuccessPageId() {
		return $this->getReservationReceivedPageId();
	}

	/**
	 * @param \MPHB\Entities\Payment $payment Optional.
	 * @param array                  $additionalArgs Optional.
	 * @return string|false
	 * @deprecated since 3.7
	 * @see \MPHB\Settings\PageSettings::getReservationReceivedPageUrl()
	 */
	public function getPaymentSuccessPageUrl( $payment = null, $additionalArgs = array() ) {
		return $this->getReservationReceivedPageUrl( $payment, $additionalArgs );
	}

	/**
	 * @return int
	 *
	 * @since 3.7.0
	 */
	public function getReservationReceivedPageId() {
		/** TODO Rename with "reservation_received". */
		return $this->getPageId( 'payment_success' );
	}

	/**
	 * @param \MPHB\Entities\Payment $payment Optional.
	 * @param array                  $additionalArgs Optional.
	 * @return string|false
	 *
	 * @since 3.7.0
	 */
	public function getReservationReceivedPageUrl( $payment = null, $additionalArgs = array() ) {
		$url = $this->getUrl( $this->getReservationReceivedPageId() );

		if ( ! empty( $url ) ) {
			if ( $payment ) {
				$additionalArgs['payment_id']  = $payment->getId();
				$additionalArgs['payment_key'] = $payment->getKey();

				if ( ! isset( $additionalArgs['mphb_payment_status'] ) ) {
					$additionalArgs['mphb_payment_status'] = $payment->getStatus();
				}
			}

			$url = add_query_arg( $additionalArgs, $url );
		}

		return $url;
	}

	/**
	 *
	 * @return int
	 */
	public function getPaymentFailedPageId() {
		return $this->getPageId( 'payment_failed' );
	}

	/**
	 *
	 * @param \MPHB\Entities\Payment $payment Optional.
	 * @return string
	 */
	public function getPaymentFailedPageUrl( $payment = null, $additionalArgs = array() ) {
		$url = $this->getUrl( $this->getPaymentFailedPageId() );

		if ( ! empty( $url ) ) {

			if ( $payment ) {
				$additionalArgs['payment_id'] = $payment->getId();
			}

			$url = add_query_arg( $additionalArgs, $url );
		}

		return $url;
	}

	/**
	 *
	 * @return int
	 */
	public function getTermsAndConditionsPageId() {
		return $this->getPageId( 'terms_and_conditions' );
	}

	/**
	 *
	 * @return bool
	 * @since 4.2.5
	 */
	public function getOpenTermsAndConditionsInNewWindow() {
		return (bool) get_option( 'mphb_open_terms_in_new_window', false );
	}

	/**
	 *
	 * @since 4.2.0
	 */
	public function getMyAccountPageId() {
		return $this->getPageId( 'my_account' );
	}

	/**
	 *
	 * @param int $id
	 *
	 * @since 4.2.0
	 */
	public function setMyAccountPageId( $id ) {
		return update_option( 'mphb_my_account_page', $id );
	}

	/**
	 *
	 * @param string|int $id
	 * @return string|false
	 */
	public function getUrl( $id ) {
		return get_permalink( $id );
	}

	/**
	 *
	 * @param string $name
	 * @return int
	 */
	private function getPageId( $name ) {

		$pageId = get_option( 'mphb_' . $name . '_page' );

		$pageId = apply_filters( '_mphb_translate_page_id', $pageId );

		return (int) $pageId;
	}

	/**
	 *
	 * @param string $id ID of page
	 * @return bool False if value was not updated and true if value was updated.
	 */
	public function setCheckoutPage( $id ) {
		return update_option( 'mphb_checkout_page', $id );
	}

	/**
	 *
	 * @param string $id ID of page
	 * @return bool False if value was not updated and true if value was updated.
	 */
	public function setSearchResultsPage( $id ) {
		return update_option( 'mphb_search_results_page', $id );
	}

	/**
	 *
	 * @param string $id ID of page
	 * @return bool False if value was not updated and true if value was updated.
	 */
	public function setBookingConfirmPage( $id ) {
		return update_option( 'mphb_booking_confirmation_page', $id );
	}

	/**
	 *
	 * @param string $id ID of page
	 * @return bool False if value was not updated and true if value was updated.
	 */
	public function setUserCancelRedirectPage( $id ) {
		return update_option( 'mphb_user_cancel_redirect_page', $id );
	}

	/**
	 *
	 * @param string $id ID of page
	 * @return bool False if value was not updated and true if value was updated.
	 */
	public function setPaymentSuccessPage( $id ) {
		return update_option( 'mphb_payment_success_page', $id );
	}

	/**
	 *
	 * @param string $id ID of page
	 * @return bool False if value was not updated and true if value was updated.
	 */
	public function setPaymentFailedPage( $id ) {
		return update_option( 'mphb_payment_failed_page', $id );
	}

	/**
	 *
	 * @param string $id ID of page
	 * @return bool False if value was not updated and true if value was updated.
	 *
	 * @since 3.9.9
	 */
	public function setBookingConfirmCancellationPage( $id ) {
		return update_option( 'mphb_booking_cancellation_page', $id );
	}

	/**
	 *
	 * @since 3.9.9
	 *
	 * @return int
	 */
	public function getBookingConfirmCancellationPage() {
		return $this->getPageId( 'booking_cancellation' );
	}

}
