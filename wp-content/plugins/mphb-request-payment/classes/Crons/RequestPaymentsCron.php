<?php

namespace MPHB\Addons\RequestPayment\Crons;

use MPHB\Addons\RequestPayment\Utils\RequestUtils;
use MPHB\Addons\RequestPayment\Settings;
use MPHB\Crons\AbstractCron;

class RequestPaymentsCron extends AbstractCron
{
    public function doCronJob()
    {
        if (!Settings::isAutomaticEmailsEnabled()) {
            return;
        }

        $bookingIds = RequestUtils::getUnrequestedIds();

        foreach ($bookingIds as $bookingId) {
            $booking = MPHB()->getBookingRepository()->findById($bookingId);

            if (!is_null($booking)) {
                maybe_request_payment($booking);
            }
        }
    }
}
