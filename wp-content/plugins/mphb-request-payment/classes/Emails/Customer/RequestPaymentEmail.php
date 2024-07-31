<?php

namespace MPHB\Addons\RequestPayment\Emails\Customer;

class RequestPaymentEmail extends BaseCustomerEmail
{
    protected function initLabel()
    {
        $this->label = esc_html__('Payment Request Email', 'mphb-request-payment');
    }

    protected function initDescription()
    {
        $this->description = esc_html__('Email that will be automatically or manually sent to customer in regard to a booking balance.', 'mphb-request-payment');
    }

    public function getDefaultSubject()
    {
        return esc_html__('%site_title% - Payment request for booking #%booking_id%', 'mphb-request-payment');
    }

    public function getDefaultMessageHeaderText()
    {
        return esc_html__('Payment Request', 'mphb-request-payment');
    }
}
