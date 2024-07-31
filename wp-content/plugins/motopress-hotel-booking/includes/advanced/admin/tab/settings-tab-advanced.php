<?php
/**
 * SettingsTabAdvanced
 *
 * @package MPHB\Advanced\Admin
 * @since 4.1.0
 */

namespace MPHB\Advanced\Admin\Tab;

use MPHB\Admin\Tabs\SettingsTab;

class SettingsTabAdvanced extends SettingsTab {

	public function __construct( $name, $label, $pageName, $subTabName = '' ) {

		parent::__construct( $name, $label, $pageName, $subTabName );
	}

	protected function renderTab() {

		if ( $this->detectSubTab() ) {

			parent::renderTab();

		} else {

			MPHB()->getAdvanced()->getAdmin()->getTab()->render(
				$this->getPageName(),
				$this->getName(),
				''
			);
		}
	}
}
