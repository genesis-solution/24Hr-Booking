<?php

namespace MPHBW\Admin\Emails;

use MPHB\Emails\Booking\Admin\BaseEmail as AdminEmail;

/**
 * @since 1.0.5
 */
abstract class BaseEmail extends AdminEmail
{
    /**
     * @param array $atts
     *     @param string $atts['id'] Required. ID of the email.
     *     @param string $atts['label'] Required. Email label.
     *     @param string $atts['description'] Optional. Email description.
     *     @param string $atts['default_subject'] Optional. Default subject of the email.
     *     @param string $atts['default_header_text'] Optional. Default text in the header.
     * @param \MPHB\Emails\Templaters\EmailTemplater $templater
     */
    public function __construct($atts, $templater)
    {
        parent::__construct($atts, $templater);

        // Don't generate our settings in "Admin Emails" or "Customer Emails", use payment tab instead
        remove_action('mphb_generate_settings_admin_emails', array($this, 'generateSettingsFields'));

        add_action('mphb_generate_settings_woocommerce_emails', array($this, 'generateSettingsFields'));
    }
}
