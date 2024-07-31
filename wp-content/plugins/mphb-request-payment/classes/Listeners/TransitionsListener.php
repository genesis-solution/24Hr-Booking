<?php

namespace MPHB\Addons\RequestPayment\Listeners;

use MPHB\Addons\RequestPayment\Utils\BookingUtils;
use MPHB\Addons\RequestPayment\Utils\PaymentUtils;
use MPHB\Addons\RequestPayment\Settings;

class TransitionsListener
{
    public function __construct()
    {
        $this->addActions();
    }

    protected function addActions()
    {
        if (Settings::isAutomaticEmailsEnabled()) {
            // Send email when booking changes it's status
            add_action('mphb_booking_status_changed', array($this, 'onBookingStatusChange'), 10, 2);
        }

        add_action('mphb_create_booking_via_ical', array($this, 'onBookingImported'), 10, 1);

        add_action('mphb_payment_completed', array($this, 'onPaymentDone'), 10, 1);
        add_action('mphb_payment_on_hold', array($this, 'onHoldPayment'), 10, 1);
    }

    public function onBookingStatusChange($booking, $oldStatus)
    {
        if ($booking->getId() > Settings::getLastSkippedBookingId()
            && !BookingUtils::isRequestAvailableForStatus($oldStatus)
            && BookingUtils::isRequestAvailableForStatus($booking->getStatus())
        ) {
            // Maybe it's a new booking, that now have a proper status
            if (!BookingUtils::isRequestDisabled($booking)
                && !BookingUtils::isRequestSent($booking)
                && BookingUtils::isRequestReady($booking)
            ) {
                maybe_request_payment($booking);
            }
        }
    }

    public function onBookingImported($booking)
    {
        // No need to even check the imported booking for requests
        update_post_meta($booking->getId(), '_ready_for_payment_request', false);
    }

    public function onPaymentDone($payment)
    {
        if (PaymentUtils::isWaitingForTransition($payment)) {
            $this->requestPaid($payment);
        }
    }

    public function onHoldPayment($payment)
    {
        // Do not trigger email for WooCommerce Payments: WooCoomerce payments
        // always on hold, we better wait for "payment done" action (MB-1141)
        if (PaymentUtils::isWaitingForTransition($payment) && $payment->getGatewayId() != 'woocommerce') {
            $this->requestPaid($payment);
        }
    }

    protected function requestPaid($payment)
    {
        $booking = MPHB()->getBookingRepository()->findById($payment->getBookingId());

        if (!is_null($booking)) {
            MPHB()->emails()->getEmail('admin_request_paid')->trigger($booking, array('payment' => $payment));
        }

        PaymentUtils::stopListenTransitions($payment);
    }
}
