<?php

namespace MPHB\Shortcodes;

class CheckoutShortcode extends AbstractShortcode {

	protected $name = 'mphb_checkout';

	const STEP_CHECKOUT             = 'checkout';
	const STEP_BOOKING              = 'booking';
	const STEP_COMPLETE             = 'complete';
	const NONCE_ACTION_CHECKOUT     = 'mphb-checkout';
	const NONCE_ACTION_BOOKING      = 'mphb-booking';
	const NONCE_NAME                = 'mphb-checkout-nonce';
	const RECOMMENDATION_NONCE_NAME = 'mphb-checkout-recommendation-nonce';
	const BOOKING_CID_NAME          = 'mphb-booking-checkout-id';

	/**
	 *
	 * @var string
	 */
	protected $currentStep;

	/*
	 *  Booking info
	 */
	protected $isCorrectPage  = false;
	protected $isCorrectNonce = false;

	/**
	 *
	 * @var CheckoutShortcode\Step[]
	 */
	protected $steps = array();

	public function __construct() {
		parent::__construct();

		$this->steps[ self::STEP_CHECKOUT ] = new CheckoutShortcode\StepCheckout();
		$this->steps[ self::STEP_BOOKING ]  = new CheckoutShortcode\StepBooking();
		$this->steps[ self::STEP_COMPLETE ] = new CheckoutShortcode\StepComplete();

		add_action( 'mphb_sc_checkout_errors_content', array( $this, 'showErrorsContent' ) );
		add_filter( 'mphb_sc_checkout_error', array( $this, 'filterErrorOutput' ) );

		add_action( 'wp', array( $this, 'setup' ) );

		add_action( 'template_redirect', array( $this, 'enforceSSLRedirect' ) );
	}

	public function setup() {

		$this->isCorrectPage = mphb_is_checkout_page();

		if ( ! $this->isCorrectPage ) {
			return;
		}

		$this->currentStep = $this->detectStep();

		if ( ! $this->checkNonce() ) {
			return;
		}

		$this->steps[ $this->currentStep ]->setup();
	}

	/**
	 *
	 * @return string
	 */
	protected function detectStep() {
		// phpcs:ignore WordPress.Security.ValidatedSanitizedInput
		if ( isset( $_REQUEST['mphb_checkout_step'] ) ) {
			$step = mphb_clean( wp_unslash( $_REQUEST['mphb_checkout_step'] ) );
		} elseif ( mphb_get_cookie( 'mphb_checkout_step' ) ) {
			$step = mphb_clean( wp_unslash( mphb_get_cookie( 'mphb_checkout_step' ) ) );
		} else {
			$step = self::STEP_CHECKOUT;
		}

		if ( $step === self::STEP_BOOKING && MPHB()->getSession()->get( 'mphb_checkout_step' ) === self::STEP_COMPLETE ) {

			// Is it a rebooking?
			// phpcs:ignore WordPress.Security.ValidatedSanitizedInput
			$checkoutId        = isset( $_REQUEST[ self::BOOKING_CID_NAME ] ) ? mphb_clean( wp_unslash( $_REQUEST[ self::BOOKING_CID_NAME ] ) ) : '';
			$unfinishedBooking = ( $checkoutId != '' ) ? MPHB()->getBookingRepository()->findByCheckoutId( $checkoutId ) : null;

			if ( MPHB()->settings()->main()->getConfirmationMode() !== 'payment' ) {
				// No, don't even try to rebook, when paymens are disabled
				$step = self::STEP_COMPLETE;

			} elseif ( empty( $unfinishedBooking ) ) {
				// No, just go to the next step, as it was in previous versions
				$step = self::STEP_COMPLETE;

			} else {
				$expectPaymentId = $unfinishedBooking->getExpectPaymentId();
				$expectPayment   = $expectPaymentId !== false ? MPHB()->getPaymentRepository()->findById( $expectPaymentId ) : null;

				if ( ! $unfinishedBooking->isPending()
					|| is_null( $expectPayment )
					|| $expectPayment->isFinished()
					|| $expectPayment->isAuthorized()
				) {
					// No, the booking with such checkout ID is not "unfinished"
					$step = self::STEP_COMPLETE;
				}
			}
		}

		return $step;
	}

	/**
	 *
	 * @return boolean
	 */
	protected function checkNonce() {

		$nonce = '';

		if ( $this->currentStep === self::STEP_CHECKOUT ) {

			$this->isCorrectNonce = true;

			return $this->isCorrectNonce;

			// Skip nonce verification for logged in users during checkout. Because nonce fields are different before and after authorization.
			/*
			if( get_current_user_id() ) {

				$this->isCorrectNonce = true;

				return $this->isCorrectNonce;
			}

			if( isset( $_GET['login_failed'] ) && $_GET['login_failed'] == 'error' ) {
				$this->isCorrectNonce = true;

				return $this->isCorrectNonce;
			}

			$nonceAction = self::NONCE_ACTION_CHECKOUT;

			if ( isset( $_POST[self::NONCE_NAME] ) ) {

				$nonce = sanitize_text_field( wp_unslash( $_POST[self::NONCE_NAME] ));

			} else if ( isset( $_POST[self::RECOMMENDATION_NONCE_NAME] ) ) {

				$nonce = sanitize_text_field( wp_unslash( $_POST[self::RECOMMENDATION_NONCE_NAME] ));
			}*/
		} elseif ( $this->currentStep === self::STEP_BOOKING ) {

			$nonceAction = self::NONCE_ACTION_BOOKING;

			if ( isset( $_POST[ self::BOOKING_CID_NAME ] ) ) {
				$nonceAction .= '-' . sanitize_text_field( wp_unslash( $_POST[ self::BOOKING_CID_NAME ] ) );
			}

			if ( isset( $_POST[ self::NONCE_NAME ] ) ) {
				$nonce = sanitize_text_field( wp_unslash( $_POST[ self::NONCE_NAME ] ) );
			}
		} elseif ( $this->currentStep === self::STEP_COMPLETE ) {

			$this->isCorrectNonce = true;

			return $this->isCorrectNonce;
		}

		$this->isCorrectNonce = wp_verify_nonce( $nonce, $nonceAction );

		return $this->isCorrectNonce;
	}

	/**
	 *
	 * @param array  $atts
	 * @param string $content
	 * @param string $shortcodeName
	 * @return string
	 */
	public function render( $atts, $content, $shortcodeName ) {

		$defaultAtts = array(
			'class' => '',
		);

		$atts = shortcode_atts( $defaultAtts, $atts, $shortcodeName );

		ob_start();

		if ( $this->isCorrectPage && $this->isCorrectNonce && ! MPHB()->settings()->main()->isBookingDisabled() ) {
			$this->steps[ $this->currentStep ]->render();
		}

		$content = ob_get_clean();

		$wrapperClass  = apply_filters( 'mphb_sc_checkout_wrapper_classes', 'mphb_sc_checkout-wrapper' );
		$wrapperClass .= empty( $wrapperClass ) ? $atts['class'] : ' ' . $atts['class'];
		return '<div class="' . esc_attr( $wrapperClass ) . '">' . $content . '</div>';
	}

	/**
	 *
	 * @param array $errors
	 */
	public function showErrorsContent( $errors ) {
		foreach ( $errors as $error ) {
			// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			echo apply_filters( 'mphb_sc_checkout_error', $error );
		}
	}

	public function filterErrorOutput( $error ) {
		return '<br/>' . $error;
	}

	/**
	 * Handle redirections for SSL enforced checkouts
	 */
	public function enforceSSLRedirect() {

		if ( is_ssl() ) {
			return;
		}

		if ( ! mphb_is_checkout_page() || ! MPHB()->settings()->payment()->isForceCheckoutSSL() ) {
			return;
		}

		$requestedURI = isset( $_SERVER['REQUEST_URI'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REQUEST_URI'] ) ) : '';

		if ( 0 === strpos( $requestedURI, 'http' ) ) {
			$url = preg_replace( '|^http://|', 'https://', $requestedURI );
		} else {

			$url = 'https://';
			if ( ! empty( $_SERVER['HTTP_X_FORWARDED_HOST'] ) ) {

				$url .= sanitize_text_field( wp_unslash( $_SERVER['HTTP_X_FORWARDED_HOST'] ) );

			} elseif ( isset( $_SERVER['HTTP_HOST'] ) ) {

				$url .= sanitize_text_field( wp_unslash( $_SERVER['HTTP_HOST'] ) );

			} else {

				$url = get_site_url();
			}
			$url .= $requestedURI;
		}

		wp_safe_redirect( $url );
		exit;
	}

}
