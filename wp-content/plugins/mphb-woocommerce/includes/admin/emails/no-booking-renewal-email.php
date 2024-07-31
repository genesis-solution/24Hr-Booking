<?php

namespace MPHBW\Admin\Emails;

/**
 * @since 1.0.5
 */
class NoBookingRenewalEmail extends BaseEmail
{
    protected function initLabel()
    {
        $this->label = __('Manual Action Required Notice', 'mphb-woocommerce');
    }

    protected function initDescription()
    {
        $this->description = __('An email notice that is sent to Admin when Hotel Booking is not able to process the booking automatically.', 'mphb-woocommerce');
    }

    public function getDefaultSubject()
    {
        return __('%site_title% - Manual action required for the booking #%booking_id%', 'mphb-woocommerce');
    }

    public function getDefaultMessageHeaderText()
    {
        return __('Manual action required', 'mphb-woocommerce');
    }
}
