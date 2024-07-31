<?php

namespace MPHB\Addons\RequestPayment;

class Settings
{
    public static function getLastSkippedBookingId()
    {
        return (int)get_option('mphbrp_last_skipped_booking_id', 0);
    }

    /**
     * @return bool
     */
    public static function isAutomaticEmailsEnabled()
    {
        return (bool)get_option('mphbrp_enable_auto_emails', false);
    }

    /**
     * @return int
     */
    public static function defaultDaysBeforeCheckIn()
    {
        return 7;
    }

    /**
     * @return int
     */
    public static function getDaysBeforeCheckIn()
    {
        return (int)get_option('mphbrp_days_before_check_in', static::defaultDaysBeforeCheckIn());
    }

    /**
     * @return int
     */
    public static function getCheckoutPageId()
    {
        return (int) apply_filters( '_mphb_translate_page_id', get_option('mphbrp_checkout_page', 0) );
    }

    /**
     * @return string
     */
    public static function getCheckoutPageUrl()
    {
        $id  = static::getCheckoutPageId();
        $url = get_permalink($id);

        if (MPHB()->settings()->payment()->isForceCheckoutSSL()) {
            $url = preg_replace('/^http:/', 'https:', $url);
        }

        return $url;
    }

    /**
     * @param int $pageId
     */
    public static function setCheckoutPageId($pageId)
    {
        update_option('mphbrp_checkout_page', $pageId, 'no');
    }

    public static function isEddEnabled()
    {
        return (bool)apply_filters('mphbrp_use_edd_license', true);
    }

    public static function getEddProductId()
    {
        return 751734;
    }

    public static function getLicenseKey()
    {
        return get_option('mphbrp_license_key', '');
    }

    public static function setLicenseKey($licenseKey)
    {
        update_option('mphbrp_license_key', $licenseKey, 'yes');
    }

    public static function removeLicenseKey()
    {
        delete_option('mphbrp_license_key');
    }

    public static function setLicenseStatus($licenseStatus)
    {
        update_option('mphbrp_license_status', $licenseStatus, 'no');
    }

    public static function removeLicenseStatus()
    {
        delete_option('mphbrp_license_status');
    }
}
