<?php

namespace MPHB\Notifier\API;

/**
 * @since 1.0
 */
class EddLicenseApi
{
    const OPTION_KEY    = 'mphb_notifier_edd_license_key';
    const OPTION_STATUS = 'mphb_notifier_edd_license_status';

    const PRODUCT_ID = 890485;

    /**
     * @param string $newKey
     */
    public function setKey($newKey)
    {
        $oldKey = $this->getKey();

        // Remove previous status if have new key
        if (!empty($oldKey) && $newKey != $oldKey) {
            $this->removeStatus();
        }

        if (!empty($newKey)) {
            update_option(self::OPTION_KEY, $newKey, true);
        } else {
            $this->removeKey();
        }
    }

    /**
     * @return string
     */
    public function getKey()
    {
        return get_option(self::OPTION_KEY, '');
    }

    public function removeKey()
    {
        delete_option(self::OPTION_KEY);
    }

    /**
     * @return \stdClass|null Response object.
     */
    public function activate()
    {
        $responseObject = $this->request('activate_license');

        if (!is_null($responseObject)) {
            $this->setStatus($responseObject->license);
        }

        return $responseObject;
    }

    /**
     * @return \stdClass|null Response object.
     */
    public function deactivate()
    {
        $responseObject = $this->request('deactivate_license');

        // Remove status only if successfully deactivated
        if (!is_null($responseObject) && $responseObject->license == 'deactivated') {
            $this->removeStatus();
        }

        return $responseObject;
    }

    /**
     * @return \stdClass|null Response object.
     */
    public function check()
    {
        $responseObject = $this->request('check_license');
        return $responseObject;
    }

    /**
     * @param string $eddAction activate_license|deactivate_license|check_license
     * @return \stdClass|null Response object.
     */
    public function request($eddAction)
    {
        // Retrieve license data via EDD API
        $requestArgs = array(
            'edd_action' => $eddAction,
            'license'    => $this->getKey(),
            'item_id'    => self::PRODUCT_ID,
            'url'        => home_url()
        );

        $requestUrl = add_query_arg($requestArgs, mphb_notifier()->pluginStoreUri());
        $response = wp_remote_get($requestUrl, array('timeout' => 15, 'sslverify' => false));

        // Make sure that response is OK
        if (!is_wp_error($response)) {
            $responseObject = json_decode(wp_remote_retrieve_body($response));
            return $responseObject;
        } else {
            return null;
        }
    }

    /**
     * @param string $newStatus
     */
    public function setStatus($newStatus)
    {
        update_option(self::OPTION_STATUS, $newStatus, false);
    }

    /**
     * @return string
     */
    public function getStatus()
    {
        return get_option(self::OPTION_STATUS, '');
    }

    public function removeStatus()
    {
        delete_option(self::OPTION_STATUS);
    }

    public function getProductId()
    {
        return self::PRODUCT_ID;
    }
}
