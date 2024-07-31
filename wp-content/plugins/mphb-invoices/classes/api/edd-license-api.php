<?php

namespace MPHB\Addons\Invoice\API;

class EddLicenseApi
{
    const KEY_OPTION = 'mphb_invoice_edd_license_key';
    const STATUS_OPTION = 'mphb_invoice_edd_license_status';

    const PRODUCT_ID = 1008672;

    /**
     * @param string $newKey
     */
    public function setupKey($newKey)
    {
        $oldKey = $this->getKey();

        // Remove previous status if have new key
        if (!empty($oldKey) && $oldKey != $newKey) {
            $this->clearStatus();
        }

        if (!empty($newKey)) {
            $this->setKey($newKey);
        } else {
            $this->removeKey();
        }
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

    public function deactivate()
    {
        $responseObject = $this->request('deactivate_license');

        // Remove status if successfully deactivated
        if (!is_null($responseObject) && $responseObject->license == 'deactivated') {
            $this->clearStatus();
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

        $requestUrl = add_query_arg($requestArgs, mphbinvoice()->pluginUri());
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
     * @return string
     */
    public function getKey()
    {
        return get_option(self::KEY_OPTION, '');
    }

    /**
     * @param string $newKey
     */
    public function setKey($newKey)
    {
        update_option(self::KEY_OPTION, $newKey, 'yes');
    }

    public function removeKey()
    {
        delete_option(self::KEY_OPTION);
    }

    /**
     * @return string
     */
    public function getStatus()
    {
        return get_option(self::STATUS_OPTION, '');
    }

    /**
     * @param string $newStatus
     */
    public function setStatus($newStatus)
    {
        update_option(self::STATUS_OPTION, $newStatus, 'no');
    }

    public function clearStatus()
    {
        delete_option(self::STATUS_OPTION);
    }

    public function getProductId()
    {
        return self::PRODUCT_ID;
    }
}
