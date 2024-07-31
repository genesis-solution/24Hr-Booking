<?php

namespace MPHB\CheckoutFields\Admin;

use MPHB\Admin\Tabs\SettingsSubTab as SettingsSubtab;
use MPHB\CheckoutFields\Plugin;

/**
 * @since 1.0
 */
class SettingsPage
{
    public function __construct()
    {
        add_action('mphb_generate_extension_settings', [$this, 'registerSettings']);
    }

    /**
     * Callback for action "mphb_generate_extension_settings".
     *
     * @param \MPHB\Admin\Tabs\SettingsTab $tab
     */
    public function registerSettings($tab)
    {
        // Add tab "Checkout Fields"
        $subtab = new SettingsSubtab('checkout_fields', esc_html__('Checkout Fields', 'mphb-checkout-fields'), $tab->getPageName(), $tab->getName());
        $tab->addSubTab($subtab);

        // Add group "License"
        $plugin = Plugin::getInstance();

        if ( $plugin->isEDDLicenseEnabled() ) {
            
            $licenseGroup = new LicenseSettingsGroup('mphb_checkout_fields_license', esc_html__('License', 'mphb-checkout-fields'), $subtab->getOptionGroupName());
            $subtab->addGroup($licenseGroup);
        }
    }
}
