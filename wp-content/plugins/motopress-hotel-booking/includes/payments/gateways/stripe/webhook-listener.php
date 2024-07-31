<?php

namespace MPHB\Payments\Gateways\Stripe;

use MPHB\Payments\Gateways\AbstractNotificationListener;
use MPHB\PostTypes\PaymentCPT\Statuses as PaymentStatuses;
use Stripe\Event;
use Stripe\Stripe;

/**
 * @since 3.6.0
 */
class WebhookListener extends AbstractNotificationListener {

	protected $secretKey      = '';
	protected $endpointSecret = '';

	// Values from validate()
	protected $eventType     = ''; // "%object%.%status%"
	protected $eventObject   = null; // \Stripe\Source or \Stripe\Charge
	protected $isCardPIEvent = false; // Is "charge.XXX" event of PaymentIntent on Card Payment

	public function __construct( $atts ) {
		$this->secretKey      = $atts['secret_key'];
		$this->endpointSecret = $atts['endpoint_secret'];

		parent::__construct( $atts );
	}

	protected function initUrlIdentificationValue() {
		return 'stripe';
	}

	protected function parseInput() {
		$payload = @file_get_contents( 'php://input' );
		return $payload;
	}

	protected function validate( $payload ) {
		if ( ! isset( $_SERVER['HTTP_STRIPE_SIGNATURE'] ) ) {
			return false;
		}

		Stripe::setAppInfo( MPHB()->getName(), MPHB()->getVersion(), MPHB()->getPluginStoreUri(), StripeAPI6::PARTNER_ID );
		Stripe::setApiKey( $this->secretKey );
		Stripe::setApiVersion( StripeAPI6::API_VERSION );

		$signatureHeader = sanitize_text_field( wp_unslash( $_SERVER['HTTP_STRIPE_SIGNATURE'] ) );
		$event           = null;

		try {
			// See code example at https://stripe.com/docs/webhooks/setup#create-endpoint
			$event = Event::constructFrom( json_decode( $payload, true ) );
		} catch ( \Exception $e ) {
			return false;
		}

		$this->eventType = $event->type;

		// Get source/charge object
		$eventObjectType = $event->data->object->object;

		if ( $eventObjectType == 'source' || $eventObjectType == 'charge' ) {
			$this->eventObject = $event->data->object;
		}

		return ! is_null( $this->eventObject );
	}

	/**
	 * @return \MPHB\Entities\Payment|null
	 */
	protected function retrievePayment() {
		if ( $this->eventObject->object == 'source' ) {
			return MPHB()->getPaymentRepository()->findByMeta( '_mphb_transaction_source_id', $this->eventObject->id );
		} else {
			$payment = MPHB()->getPaymentRepository()->findByMeta( '_mphb_transaction_id', $this->eventObject->id );

			if ( is_null( $payment ) && ! empty( $this->eventObject->payment_intent ) ) {
				$this->isCardPIEvent = true;

				// Card payments hold PaymentIntend ID instead of Charge ID
				$payment = MPHB()->getPaymentRepository()->findByMeta( '_mphb_transaction_id', $this->eventObject->payment_intent );
			}

			return $payment;
		}
	}

	protected function process() {
		if ( ! in_array( $this->payment->getStatus(), array( PaymentStatuses::STATUS_PENDING, PaymentStatuses::STATUS_ON_HOLD ) ) ) {
			// translators: %s - event type, like "source.chargeable"
			$message = sprintf( __( 'Webhook "%s" skipped: payment\'s flow already completed.', 'motopress-hotel-booking' ), $this->eventType );
			$this->payment->addLog( $message );

			return;
		}

		$source = $this->eventObject;
		$charge = $this->eventObject;

		switch ( $this->eventType ) {
			case 'source.chargeable':
				// translators: %s - Stripe Source ID
				$message = sprintf( __( 'Webhook received. The source %s is chargeable.', 'motopress-hotel-booking' ), $source->id );
				$this->payment->addLog( $message );

				MPHB()->gatewayManager()->getGateway( $this->gatewayId )->chargePayment( $this->payment, $source );
				break;

			case 'source.canceled':
				// translators: %s - Stripe Source ID
				$message = sprintf( __( 'Webhook received. Payment source %s was cancelled by customer.', 'motopress-hotel-booking' ), $source->id );
				$this->payment->addLog( $message );

				$this->paymentFailed( '' );
				break;

			case 'source.failed':
				// translators: %s - Stripe Source ID
				$message = sprintf( __( "Webhook received. Payment source %s failed and couldn't be processed.", 'motopress-hotel-booking' ), $source->id );
				$this->payment->addLog( $message );

				$this->paymentFailed( '' );
				break;

			case 'charge.succeeded':
				// translators: %s - Stripe Charge ID
				$message = sprintf( __( 'Webhook received. Charge %s succeeded.', 'motopress-hotel-booking' ), $charge->id );
				$this->payment->addLog( $message );

				$this->paymentCompleted( '' );
				break;

			case 'charge.failed':
				// translators: %s - Stripe Charge ID
				$message = sprintf( __( 'Webhook received. Charge %s failed.', 'motopress-hotel-booking' ), $charge->id );
				$this->payment->addLog( $message );

				$this->paymentFailed( '' );
				break;
		}
	}

	public function fireExit( $succeed ) {
		// Stripe triggers webhook too early, the payment was not created yet.
		// Skip the event and don't mark it as "Failed" in Stripe Dashboard.
		// Otherwise the customer will have "Failed" for each first try of the
		// webhook (when payed by card)
		if ( $succeed || $this->isCardPIEvent ) {
			http_response_code( 200 );
		} else {
			http_response_code( 400 );
		}

		parent::fireExit( $succeed );
	}

	protected function paymentCompleted( $log ) {
		return MPHB()->paymentManager()->completePayment( $this->payment, $log );
	}

	protected function paymentRefunded( $log ) {
		return MPHB()->paymentManager()->refundPayment( $this->payment, $log );
	}

	protected function paymentFailed( $log ) {
		return MPHB()->paymentManager()->failPayment( $this->payment, $log );
	}

	protected function paymentOnHold( $log ) {
		return MPHB()->paymentManager()->holdPayment( $this->payment, $log );
	}
}
