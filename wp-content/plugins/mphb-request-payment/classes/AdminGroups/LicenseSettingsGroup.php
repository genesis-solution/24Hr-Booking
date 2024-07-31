<?php

namespace MPHB\Addons\RequestPayment\AdminGroups;

use MPHB\Addons\RequestPayment\Utils\LicenseUtils;
use MPHB\Addons\RequestPayment\Settings;
use MPHB\Admin\Groups\SettingsGroup;

class LicenseSettingsGroup extends SettingsGroup
{
    public function render()
    {
        parent::render();

        $licenseKey = Settings::getLicenseKey();

        /** @var \stdClass|null */
        $licenseObj = null;

        if (!empty($licenseKey)) {
            $licenseObj = LicenseUtils::checkLicense();
        }

        ?>
        <i><?php echo wp_kses( __("The License Key is required in order to get automatic plugin updates and support. You can manage your License Key in your personal account. <a href='https://motopress.zendesk.com/hc/en-us/articles/202812996-How-to-use-your-personal-MotoPress-account' target='_blank'>Learn more</a>.", 'motopress-hotel-booking'), array('a' => array('href' => array(), 'title' => array(), 'target' => array())) ); ?></i>
        <table class="form-table">
            <tbody>
                <tr valign="top">
                    <th scope="row" valign="top">
                        <?php esc_html_e('License Key', 'motopress-hotel-booking'); ?>
                    </th>
                    <td>
                        <input id="mphbrp_license_key" name="mphbrp_license_key" type="password" class="regular-text" value="<?php echo esc_attr($licenseKey); ?>" autocomplete="new-password" />
                        <?php if (!empty($licenseKey)) { ?>
                            <i style="display: block;"><?php echo str_repeat('&#8226;', 20) . substr($licenseKey, -7); ?></i>
                        <?php } ?>
                    </td>
                </tr>
                <?php if (!is_null($licenseObj) && isset($licenseObj->license)) { ?>
                    <tr valign="top">
                        <th scope="row" valign="top">
                            <?php esc_html_e('Status', 'mphb-request-payment'); ?>
                        </th>
                        <td>
                            <?php
                            switch ($licenseObj->license) {
                                case 'inactive':
                                case 'site_inactive':
                                    esc_html_e('Inactive', 'mphb-request-payment');
                                    break;

                                case 'valid':
                                    if ($licenseObj->expires != 'lifetime') {
                                        $date = ($licenseObj->expires) ? new \DateTime($licenseObj->expires) : false;
                                        $expires = ($date) ? ' ' . $date->format('d.m.Y') : '';
                                        printf(esc_html__('Valid until %s', 'mphb-request-payment'), $expires);
                                    } else {
                                        esc_html_e('Valid (Lifetime)', 'mphb-request-payment');
                                    }
                                    break;

                                case 'disabled':
                                    esc_html_e('Disabled', 'mphb-request-payment');
                                    break;

                                case 'expired':
                                    esc_html_e('Expired', 'mphb-request-payment');
                                    break;

                                case 'invalid':
                                    esc_html_e('Invalid', 'mphb-request-payment');
                                    break;

                                case 'item_name_mismatch':
                                    echo wp_kses( __('Your License Key does not match the installed plugin. <a href="https://motopress.zendesk.com/hc/en-us/articles/202957243-What-to-do-if-the-license-key-doesn-t-correspond-with-the-plugin-license" target="_blank">How to fix this.</a>', 'mphb-request-payment'), array('a' => array('href' => array(), 'title' => array(), 'target' => array())) );
                                    break;

                                case 'invalid_item_id':
                                    esc_html_e('Product ID is not valid', 'mphb-request-payment');
                                    break;
                            } // switch $licenseObj->license
                            ?>
                        </td>
                    </tr>

                    <?php if (in_array($licenseObj->license, array('inactive', 'site_inactive', 'valid', 'expired'))) { ?>
                        <tr valign="top">
                            <th scope="row" valign="top">
                                <?php esc_html_e('Action', 'mphb-request-payment'); ?>
                            </th>
                            <td>
                                <?php if ($licenseObj->license == 'inactive' || $licenseObj->license == 'site_inactive') { ?>
                                    <?php wp_nonce_field('edd-nonce', 'edd-nonce'); ?>
                                    <input type="submit" class="button-secondary" name="edd_license_activate" value="<?php esc_attr_e('Activate License', 'mphb-request-payment'); ?>" />
                                <?php } else if ($licenseObj->license == 'valid') { ?>
                                    <?php wp_nonce_field('edd-nonce', 'edd-nonce'); ?>
                                    <input type="submit" class="button-secondary" name="edd_license_deactivate" value="<?php esc_attr_e('Deactivate License', 'mphb-request-payment'); ?>" />
                                <?php } else if ($licenseObj->license == 'expired') { ?>
                                    <a href="<?php echo esc_url(MPHBRP()->getPluginUri()); ?>" class="button-secondary" target="_blank">
                                        <?php esc_html_e('Renew License', 'mphb-request-payment'); ?>
                                    </a>
                                <?php } ?>
                            </td>
                        </tr>
                    <?php } // if $licenseObj->license in array(...) ?>
                <?php } // if isset $licenseObj->license ?>
            </tbody>
        </table>
        <?php
    }

    public function save()
    {
        parent::save();

        // Save new license key
        if (isset($_POST['mphbrp_license_key'])) {
            $licenseKey = trim($_POST['mphbrp_license_key']);
            LicenseUtils::setupLicenseKey($licenseKey);
        }

        // Activate license
        if (isset($_POST['edd_license_activate'])) {
            if (!check_admin_referer('edd-nonce', 'edd-nonce')) {
                return; // Did not click the Activate button
            }

            LicenseUtils::activateLicense();
        }

        // Deactivate license
        if (isset($_POST['edd_license_deactivate'])) {
            if (!check_admin_referer('edd-nonce', 'edd-nonce')) {
                return; // Did not click the Deactivate button
            }

            LicenseUtils::deactivateLicense();
        }
    }
}
