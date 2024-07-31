<?php

namespace MPHB\Addons\RequestPayment\Utils;

use MPHB\Addons\RequestPayment\Settings;

class LicenseUtils
{
    /**
     * @param string $licenseKey
     */
    public static function setupLicenseKey($licenseKey)
    {
        $previousLicenseKey = Settings::getLicenseKey();

        if (!empty($previousLicenseKey) && $previousLicenseKey != $licenseKey) {
            // New license has been entered, wait reactivation
            Settings::removeLicenseStatus();
        }

        if (!empty($licenseKey)) {
            Settings::setLicenseKey($licenseKey);
        } else {
            Settings::removeLicenseKey();
        }
    }

    /**
     * @return \stdClass|null
     */
    public static function checkLicense()
    {
        $licenseObj = static::doAction('check_license');

        return $licenseObj;
    }

    /**
     * @return \stdClass|null
     */
    public static function activateLicense()
    {
        $licenseObj = static::doAction('activate_license');

        if (!is_null($licenseObj)) {
            Settings::setLicenseStatus($licenseObj->license);
        }

        return $licenseObj;
    }

    /**
     * @return \stdClass|null
     */
    public static function deactivateLicense()
    {
        $licenseObj = static::doAction('deactivate_license');

        if (!is_null($licenseObj) && $licenseObj->license == 'deactivated') {
            Settings::removeLicenseStatus();
        }

        return $licenseObj;
    }

    /**
     * @param action $action
     * @return \stdClass|null
     */
    public static function doAction($action)
    {
        // Retrieve license data via EDD API
        $apiData = array(
            'edd_action' => $action,
            'license'    => Settings::getLicenseKey(),
            'item_id'    => Settings::getEddProductId(),
            'url'        => home_url()
        );

        $callUrl = add_query_arg($apiData, MPHBRP()->getPluginUri());
        $response = wp_remote_get($callUrl, array('timeout' => 15, 'sslverify' => false));

        // Make sure that response is OK
        if (!is_wp_error($response)) {
            $licenseObj = json_decode(wp_remote_retrieve_body($response));
            return $licenseObj;
        } else {
            return null;
        }
    }
}
