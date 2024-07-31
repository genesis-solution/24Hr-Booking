<?php

namespace MPHB\Addons\RequestPayment;

use MPHB\Addons\RequestPayment\Update\EDD_Plugin_Updater;

class Updater
{
    public function __construct()
    {
        add_action('admin_init', array($this, 'initEddUpdater'), 9);
    }

    public function initEddUpdater()
    {
        if (Settings::isEddEnabled()) {
            $apiData = array(
                'version' => MPHBRP()->getVersion(),
                'license' => Settings::getLicenseKey(),
                'item_id' => Settings::getEddProductId(),
                'author'  => MPHBRP()->getAuthor()
            );

            new EDD_Plugin_Updater(MPHBRP()->getPluginUri(), MPHBRP()->getPluginFile(), $apiData);
        }
    }
}
