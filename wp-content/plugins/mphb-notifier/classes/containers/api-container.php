<?php

namespace MPHB\Notifier\Containers;

use MPHB\Notifier\API;

/**
 * @since 1.0
 */
class ApiContainer
{
    protected $eddLicenseApi = null;

    /**
     * @return \MPHB\Notifier\API\EddLicenseApi
     */
    public function eddLicense()
    {
        if (is_null($this->eddLicenseApi)) {
            $this->eddLicenseApi = new API\EddLicenseApi();
        }

        return $this->eddLicenseApi;
    }
}
