<?php

namespace MPHB\Notifier\Admin\Groups;

use MPHB\Admin\Groups\SettingsGroup;

/**
 * @since 1.0
 */
class LicenseSettingsGroup extends SettingsGroup
{
    public function render()
    {
        parent::render();

        $eddApi = mphb_notifier()->api()->eddLicense();
        $licenseKey = $eddApi->getKey();
        $licenseObject = !empty($licenseKey) ? $eddApi->check() : null;

        ?>
        <i><?php
            $notice = __('The License Key is required in order to get automatic plugin updates and support. You can manage your License Key in your personal account. <a>Learn more</a>.', 'mphb-notifier');
            $notice = wp_kses($notice, ['a' => []]);
            $notice = str_replace('<a>', '<a href="https://motopress.zendesk.com/hc/en-us/articles/202812996-How-to-use-your-personal-MotoPress-account" target="_blank">', $notice);
            echo $notice;
        ?></i>
        <table class="form-table">
            <tbody>
                <tr valign="top">
                    <th scope="row" valign="top"><?php esc_html_e('License Key', 'mphb-notifier'); ?></th>
                    <td>
                        <input name="mphb_notifier_edd_license_key" type="password" class="regular-text" value="<?php echo esc_attr($licenseKey); ?>" autocomplete="new-password" />
                        <?php if (!empty($licenseKey)) { ?>
                            <i style="display: block;"><?php echo str_repeat('&#8226;', 20) . substr($licenseKey, -7); ?></i>
                        <?php } ?>
                    </td>
                </tr>

                <?php if (isset($licenseObject->license)) { ?>
                    <tr valign="top">
                        <th scope="row" valign="top"><?php esc_html_e('Status', 'mphb-notifier'); ?></th>
                        <td>
                            <?php
                            switch ($licenseObject->license) {
                                case 'inactive':
                                case 'site_inactive':
                                    esc_html_e('Inactive', 'mphb-notifier');
                                    break;

                                case 'valid':
                                    if ($licenseObject->expires != 'lifetime') {
                                        $date = $licenseObject->expires ? new \DateTime($licenseObject->expires) : false;
                                        $expires = $date ? ' ' . $date->format('d.m.Y') : '';

                                        // translators: %s - date in format d.m.Y
                                        printf(esc_html__('Valid until %s', 'mphb-notifier'), $expires);
                                    } else {
                                        esc_html_e('Valid (Lifetime)', 'mphb-notifier');
                                    }
                                    break;

                                case 'disabled':
                                    esc_html_e('Disabled', 'mphb-notifier');
                                    break;

                                case 'expired':
                                    esc_html_e('Expired', 'mphb-notifier');
                                    break;

                                case 'invalid':
                                    esc_html_e('Invalid', 'mphb-notifier');
                                    break;

                                case 'item_name_mismatch':
                                    $notice = __('Your License Key does not match the installed plugin. <a>How to fix this.</a>', 'mphb-notifier');
                                    $notice = wp_kses($notice, ['a' => []]);
                                    $notice = str_replace('<a>', '<a href="https://motopress.zendesk.com/hc/en-us/articles/202957243-What-to-do-if-the-license-key-doesn-t-correspond-with-the-plugin-license" target="_blank">', $notice);
                                    echo $notice;
                                    break;

                                case 'invalid_item_id':
                                    esc_html_e('Product ID is not valid', 'mphb-notifier');
                                    break;
                            }
                            ?>
                        </td>
                    </tr>

                    <?php if (in_array($licenseObject->license, ['inactive', 'site_inactive', 'valid', 'expired'])) { ?>
                        <tr valign="top">
                            <th scope="row" valign="top"><?php esc_html_e('Action', 'mphb-notifier'); ?></th>
                            <td>
                                <?php if ($licenseObject->license == 'inactive' || $licenseObject->license == 'site_inactive') { ?>
                                    <?php wp_nonce_field('activate-edd-license', 'edd_nonce'); ?>
                                    <input type="submit" class="button-secondary" name="activate_license" value="<?php esc_attr_e('Activate License', 'mphb-notifier'); ?>" />
                                <?php } else if ($licenseObject->license == 'valid') { ?>
                                    <?php wp_nonce_field('deactivate-edd-license', 'edd_nonce'); ?>
                                    <input type="submit" class="button-secondary" name="deactivate_license" value="<?php esc_attr_e('Deactivate License', 'mphb-notifier'); ?>" />
                                <?php } else if ($licenseObject->license == 'expired') { ?>
                                    <a href="<?php echo esc_url(mphb_notifier()->pluginStoreUri()); ?>" class="button-secondary" target="_blank">
                                        <?php esc_html_e('Renew License', 'mphb-notifier'); ?>
                                    </a>
                                <?php } ?>
                            </td>
                        </tr>
                    <?php } // if $licenseObject->license in [...] ?>
                <?php } // if isset $licenseObject->license ?>

            </tbody>
        </table>
        <?php
    }

    public function save()
    {
        // parent::save(); - we have no registered groups and fields here

        $eddApi = mphb_notifier()->api()->eddLicense();

        // Save new license key
        if (isset($_POST['mphb_notifier_edd_license_key'])) {
            $licenseKey = trim($_POST['mphb_notifier_edd_license_key']);
            $eddApi->setKey($licenseKey);
        }

        // Activate license
        if (isset($_POST['activate_license'])) {
            if (check_admin_referer('activate-edd-license', 'edd_nonce')) {
                $eddApi->activate();
            }
        }

        // Deactivate license
        if (isset($_POST['deactivate_license'])) {
            if (check_admin_referer('deactivate-edd-license', 'edd_nonce')) {
                $eddApi->deactivate();
            }
        }
    }
}
