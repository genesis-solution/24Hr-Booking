<?php
/**
 * SettingsSubTabAdvanced
 *
 * @package MPHB\Advanced\Admin
 * @since 4.1.0
 */

namespace MPHB\Advanced\Admin\Tab;

use MPHB\Admin\Tabs\SettingsSubTab;

class SettingsSubTabAdvanced extends SettingsSubTab {

	public function render() {

		MPHB()->getAdvanced()->getAdmin()->getTab()->render(
			$this->getPageName(),
			$this->tabName,
			$this->getName()
		);
	}
}
