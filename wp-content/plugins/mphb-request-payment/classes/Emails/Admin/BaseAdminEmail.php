<?php

namespace MPHB\Addons\RequestPayment\Emails\Admin;

use MPHB\Emails\Booking\Admin\BaseEmail;

abstract class BaseAdminEmail extends BaseEmail
{
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
        remove_action('mphb_generate_settings_admin_emails', array($this, 'generateSettingsFields'), 10, 1);

        add_action('mphb_generate_settings_request_emails', array($this, 'generateSettingsFields'), 10, 1);
    }
}
