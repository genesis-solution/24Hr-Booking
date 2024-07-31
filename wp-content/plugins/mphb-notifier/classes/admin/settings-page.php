<?php

namespace MPHB\Notifier\Admin;

use MPHB\Admin\Tabs\SettingsSubTab as SettingsSubtab;

/**
 * @since 1.0
 */
class SettingsPage {

	public function __construct() {

		if ( ! is_admin() ) {
			return;
		}

		add_action(
			'mphb_generate_extension_settings',
			function( $mphb_settings_tab ) {

				$this->registerSettings( $mphb_settings_tab );
			},
			10,
			1
		);
	}

	/**
	 * @param \MPHB\Admin\Tabs\SettingsTab $tab
	 */
	private function registerSettings( $mphb_settings_tab ) {

		$subtab = new SettingsSubtab(
			'notifier',
			esc_html__( 'Notifier', 'mphb-notifier' ),
			$mphb_settings_tab->getPageName(),
			$mphb_settings_tab->getName()
		);

		$mphb_settings_tab->addSubTab( $subtab );

		$subtab->addGroup(
			new Groups\MainSettingsGroup(
				'mphb_notifier_main_settings',
				esc_html__( 'Notifier Settings', 'mphb-notifier' ),
				$subtab->getOptionGroupName()
			)
		);

		if ( mphb_notifier_use_edd_license() ) {

			$subtab->addGroup(
				new Groups\LicenseSettingsGroup(
					'mphb_notifier_license',
					esc_html__( 'License', 'mphb-notifier' ),
					$subtab->getOptionGroupName()
				)
			);
		}
	}
}
