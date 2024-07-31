<?php

namespace MPHB\Addons\RequestPayment\Emails\Admin;

class RequestPaidEmail extends BaseAdminEmail
{
    protected function initLabel()
    {
        $this->label = esc_html__('Payment Received Email', 'mphb-request-payment');
    }

    protected function initDescription()
    {
        $this->description = esc_html__('Email that is sent to Admin after customer has made the requested payment.', 'mphb-request-payment');
    }

    public function getDefaultSubject()
    {
        return esc_html__('%site_title% - Payment received for booking #%booking_id%', 'mphb-request-payment');
    }

    public function getDefaultMessageHeaderText()
    {
        return esc_html__('Payment Received', 'mphb-request-payment');
    }
}
