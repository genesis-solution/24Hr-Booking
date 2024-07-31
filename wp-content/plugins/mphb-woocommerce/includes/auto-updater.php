<?php

namespace MPHBW;

class AutoUpdater {

	public function __construct(){
		add_action( 'admin_init', array( $this, 'initAutoUpdater' ), 9 );
	}

	public function initAutoUpdater(){

		if ( MPHBW()->getSettings()->license()->isEnabled() ) {

			$pluginData = MPHBW()->getPluginData();

			$apiData = array(
				'version'	 => $pluginData->getVersion(),
				'license'	 => MPHBW()->getSettings()->license()->getLicenseKey(),
				'item_id'	 => MPHBW()->getSettings()->license()->getProductId(),
				'author'	 => $pluginData->getAuthor()
			);

			new Libraries\EDD_Plugin_Updater\EDD_Plugin_Updater( MPHBW()->getSettings()->license()->getStoreUrl(), $pluginData->getPluginFile(), $apiData );
		}
	}

}
