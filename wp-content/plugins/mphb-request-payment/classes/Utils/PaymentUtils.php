<?php

namespace MPHB\Addons\RequestPayment\Utils;

use MPHB\PostTypes\PaymentCPT\Statuses as PaymentStatuses;

class PaymentUtils
{
    /**
     * @param \MPHB\Entities\Payment[] $payments
     * @return float
     */
    public static function getPaidPrice($payments)
    {
        $paid = array_reduce($payments, function ($paid, $payment) {
            if ($payment->getStatus() == PaymentStatuses::STATUS_COMPLETED) {
                $paid += $payment->getAmount();
            }

            return $paid;
        }, 0.0);

        return $paid;
    }

    /**
     * @param \MPHB\Entities\Payment|int $payment
     * @return string
     */
    public static function getGatewayTitle($payment)
    {
        if (is_int($payment)) {
            $payment = MPHB()->getPaymentRepository()->findById($payment);
        }

        if (is_object($payment)) {
            $gatewayId = $payment->getGatewayId();
            $gatewayTitle = MPHB()->gatewayManager()->getGateway($gatewayId)->getTitle();
        } else {
            $gatewayTitle = '';
        }

        return $gatewayTitle;
    }

    /**
     * @param \MPHB\Entities\Payment $payment
     */
    public static function waitForTransition($payment)
    {
        update_post_meta($payment->getId(), '_listen_request_payment_transitions', true);
    }

    /**
     * @param \MPHB\Entities\Payment $payment
     */
    public static function stopListenTransitions($payment)
    {
        update_post_meta($payment->getId(), '_listen_request_payment_transitions', false);
    }

    /**
     * @param \MPHB\Entities\Payment $payment
     */
    public static function isWaitingForTransition($payment)
    {
        return (bool)get_post_meta($payment->getId(), '_listen_request_payment_transitions', true);
    }
}
