<?php

namespace MPHB\Addons\Invoice\Containers;

use MPHB\Addons\Invoice\API;

class ApisContainer
{
    protected $eddLicenseApi = null;

    /**
     * @return \MPHB\Addons\Invoice\API\EddLicenseApi
     */
    public function eddLicense()
    {
        if (is_null($this->eddLicenseApi)) {
            $this->eddLicenseApi = new API\EddLicenseApi();
        }

        return $this->eddLicenseApi;
    }


}

