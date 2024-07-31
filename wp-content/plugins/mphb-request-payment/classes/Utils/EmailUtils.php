<?php

namespace MPHB\Addons\RequestPayment\Utils;

use MPHB\Addons\RequestPayment\Emails\Admin\RequestPaidEmail;
use MPHB\Addons\RequestPayment\Emails\Customer\RequestPaymentEmail;
use MPHB\Addons\RequestPayment\Emails\EmailTemplater;

class EmailUtils
{
    public static function addRequestPaymentEmail()
    {
        $requestPaymentTemplater = new EmailTemplater();
        $requestPaymentTemplater->setTagGroups(array('booking' => true, 'user_cancellation' => true));

        $requestPaymentEmail = new RequestPaymentEmail(array('id' => 'customer_request_payment'), $requestPaymentTemplater);

        MPHB()->emails()->addEmail($requestPaymentEmail);
    }

    public static function addRequestPaidEmail()
    {
        $requestPaidTemplater = new EmailTemplater();
        $requestPaidTemplater->setTagGroups(array('booking' => true, 'payment' => true));

        $requestPaidEmail = new RequestPaidEmail(array('id' => 'admin_request_paid'), $requestPaidTemplater);

        MPHB()->emails()->addEmail($requestPaidEmail);
    }
}
