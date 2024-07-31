<?php

namespace MPHB\Addons\Invoice\Update;

class PluginUpdater
{
    public function __construct()
    {
        add_action('admin_init', [$this, 'initEddUpdater'], 9);
    }

    public function initEddUpdater()
    {
        if (mphb_invoice_use_edd_license()) {
            $apiData = array(
                'version' => mphbinvoice()->pluginVersion(),
                'license' => mphbinvoice()->api()->eddLicense()->getKey(),
                'item_id' => mphbinvoice()->api()->eddLicense()->getProductId(),
                'author'  => mphbinvoice()->pluginAuthor()
            );

            $pluginFile = \MPHB\Addons\Invoice\PLUGIN_FILE;

            new EddPluginUpdater(mphbinvoice()->pluginUri(), $pluginFile, $apiData);
        }
    }
}
