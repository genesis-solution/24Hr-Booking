<?php

namespace MPHB\Notifier\Update;

use MPHB\Notifier\Libraries\EddPluginUpdater;

/**
 * @since 1.0
 */
class PluginUpdater
{
    public function __construct()
    {
        add_action('admin_init', [$this, 'runEddUpdater'], 9);
    }

    public function runEddUpdater()
    {
        if (mphb_notifier_use_edd_license()) {
            $apiData = array(
                'version' => mphb_notifier()->pluginVersion(),
                'license' => mphb_notifier()->api()->eddLicense()->getKey(),
                'item_id' => mphb_notifier()->api()->eddLicense()->getProductId(),
                'author'  => mphb_notifier()->pluginAuthor()
            );

            $pluginFile = \MPHB\Notifier\PLUGIN_FILE;

            new EddPluginUpdater(mphb_notifier()->pluginStoreUri(), $pluginFile, $apiData);
        }
    }
}
