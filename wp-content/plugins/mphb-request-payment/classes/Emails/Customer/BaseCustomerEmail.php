<?php

namespace MPHB\Addons\RequestPayment\Emails\Customer;

use MPHB\Emails\Booking\Customer\BaseEmail;

abstract class BaseCustomerEmail extends BaseEmail
{
    /** @var int|null User ID of null. */
    protected $author = null;

    /**
     * @param array $atts
     * @param string $atts['id'] ID of email.
     * @param string $atts['label'] Email label.
     * @param string $atts['description'] Optional. Email description.
     * @param string $atts['default_subject'] Optional. Default subject of email.
     * @param string $atts['default_header_text'] Optional. Default text in header.
     * @param \MPHB\Emails\Templaters\EmailTemplater $templater
     */
    public function __construct($atts, $templater)
    {
        parent::__construct($atts, $templater);

        // Don't generate our settings in "Admin Emails" and "Customer Emails"
        // tabs, use extension tab instead
        remove_action('mphb_generate_settings_customer_emails', array($this, 'generateSettingsFields'), 10, 1);

        add_action('mphb_generate_settings_request_emails', array($this, 'generateSettingsFields'), 10, 1);
    }

    /**
     * @return int|null User ID or null.
     */
    protected function getAuthor()
    {
        return $this->author;
    }

    /**
     * @param \MPHB\Entities\Booking $booking
     * @param array $atts
     * @param bool $isAuto
     */
    public function trigger($booking, $atts = array(), $isAuto = false)
    {
        if ($isAuto) {
            $this->triggerAuto($booking, $atts);
        } else {
            parent::trigger($booking, $atts);
        }
    }

    /**
     * @param \MPHB\Entities\Booking $booking
     * @param array $atts
     */
    public function triggerAuto($booking, $atts = array())
    {
        $this->author = 0; // "Auto"
        parent::trigger($booking, $atts);
        $this->author = null;
    }
}
