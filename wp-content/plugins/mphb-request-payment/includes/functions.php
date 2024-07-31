<?php

use MPHB\Addons\RequestPayment\Utils\BookingUtils;
use MPHB\Addons\RequestPayment\Utils\RequestUtils;
use MPHB\Addons\RequestPayment\Plugin as RequestPaymentPlugin;

/**
 * @return MPHB\Addons\RequestPayment\Plugin
 */
function MPHBRP()
{
    return RequestPaymentPlugin::getInstance();
}

/**
 * @param \MPHB\Entities\Booking $booking
 */
function maybe_request_payment($booking)
{
    // Check price and time
    $haveToPay = (BookingUtils::getToPayPrice($booking) > 0);
    $isTime = RequestUtils::isTimeForRequest($booking);

    if ($haveToPay && $isTime) {
        RequestUtils::sendRequest($booking, true);
    } else if (!$haveToPay) {
        // All paid, no need to check this booking anymore
        update_post_meta($booking->getId(), '_ready_for_payment_request', false);
    }
}
