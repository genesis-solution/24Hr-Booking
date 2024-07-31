<?php

namespace MPHB\Addons\RequestPayment\Utils;

use MPHB\Addons\RequestPayment\AdminGroups\LicenseSettingsGroup;
use MPHB\Addons\RequestPayment\Settings;
use MPHB\Admin\Fields\FieldFactory;
use MPHB\Admin\Groups\SettingsGroup;
use MPHB\Admin\Tabs\SettingsSubTab;

class SettingsTabUtils
{
    public static function addSettingsTab()
    {
        add_action('mphb_generate_extension_settings', array(__CLASS__, 'onGenerateExtensionSettings'), 10, 1);
    }

    /**
     * @param \MPHB\Admin\Tabs\SettingsTab $tab
     */
    public static function onGenerateExtensionSettings($tab)
    {
        $subtab = new SettingsSubTab('request_payment',
            esc_html__('Payment Request', 'mphb-request-payment'), $tab->getPageName(), $tab->getName());

        $mainGroup = new SettingsGroup('mphbrp_main', '', $subtab->getOptionGroupName());
        $mainFields = array(
            FieldFactory::create('mphbrp_enable_auto_emails', array(
                'type'        => 'checkbox',
                'label'       => esc_html__('Automatic Emails', 'mphb-request-payments'),
                'inner_label' => esc_html__('Enable automatic payment request emails to customers in regard to the booking balance or the full balance', 'mphb-request-payments'),
                'default'     => false
            )),
            FieldFactory::create('mphbrp_days_before_check_in', array(
                'type'        => 'number',
                'label'       => esc_html__('Days Before Check-in', 'mphb-request-payment'),
                'description' => esc_html__('The number of days to send automatic emails prior to check-in date.', 'mphb-request-payment'),
                'min'         => 1,
                'step'        => 1,
                'default'     => Settings::defaultDaysBeforeCheckIn()
            )),
            FieldFactory::create('mphbrp_checkout_page', array(
                'type'        => 'page-select',
                'label'       => esc_html__('Payment Request Page', 'mphb-request-payment'),
                'description' => esc_html__('Select page user will be redirected to pay the booking balance. Must contain [mphb_payment_request_checkout] shortcode.', 'mphb-request-payment'),
                'default'     => ''
            ))
        );
        $mainGroup->addFields($mainFields);
        $subtab->addGroup($mainGroup);

        // Add emails
        do_action('mphb_generate_settings_request_emails', $subtab);

        if (Settings::isEddEnabled()) {
            $licenseGroup = new LicenseSettingsGroup('mphbrp_license', esc_html__('License', 'mphb-request-payment'), $subtab->getOptionGroupName());
            $subtab->addGroup($licenseGroup);
        }

        $tab->addSubTab($subtab);
    }
}
