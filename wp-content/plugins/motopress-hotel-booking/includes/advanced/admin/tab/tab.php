<?php
/**
 * Tabs
 *
 * @package MPHB\Advanced\Admin
 * @since 4.1.0
 */

namespace MPHB\Advanced\Admin\Tab;

use MPHB\Admin\Groups\SettingsGroup;

class Tab {

	const TAB = array(
		'name'  => 'advanced',
		'label' => 'Advanced',
		'class' => '\MPHB\Advanced\Admin\Tab\Subtabs\ApiKeys\ApiKeys',
	);

	/**
	 * Fill constant in format below for append additional subtabs for this tab
	 *
	 * @var array [ {[ 'name' => $name, 'label' => $label, 'class' => $class ]} ]
	 */
	const SUBTABS = array();

	const PAGE_NOT_FOUND = 'The page template has not been created yet.';

	protected $subtabControllers = array();

	public function __construct() {

		add_action( 'mphb_generate_settings_advanced', array( $this, 'createSubTabs' ) );

		$this->initTabControllers();
	}

	private function initTabControllers() {
		$tabName  = self::TAB['name'];
		$tabClass = self::TAB['class'];

		$this->subtabControllers[ $tabName ] = new $tabClass();

		if ( ! count( self::SUBTABS ) ) {
			return;
		}
		foreach ( self::SUBTABS as $subtabName => $subtabController ) {
			$this->subtabControllers[ $subtabName ] = new $subtabController['class']();
		}
	}

	/**
	 * @param  SettingsTabAdvanced $tab
	 * @param  string              $subTabName
	 * @param  string              $subTabLabel
	 */
	private function createSubTab( SettingsTabAdvanced $tab, string $subTabName, string $subTabLabel ) {
		$subTab       = new SettingsSubTabAdvanced( $subTabName, $subTabLabel, 'mphb_settings', 'advanced' );
		$apiKeysGroup = new SettingsGroup(
			'mphb_advanced_group',
			__( $subTabLabel, 'motopress-hotel-booking' ),
			$subTab->getOptionGroupName()
		);
		$subTab->addGroup( $apiKeysGroup );

		$tab->addSubTab( $subTab );
	}

	/**
	 * @param  SettingsTabAdvanced $tab
	 */
	public function createSubTabs( SettingsTabAdvanced $tab ) {
		if ( ! count( self::SUBTABS ) ) {
			return;
		}
		foreach ( self::SUBTABS as $subTab ) {
			$this->createSubTab( $tab, $subTab['name'], $subTab['label'] );
		}
	}

	public function render( string $page, string $tab, string $subtab ) {
		if ( $subtab === '' ) {
			$subtab = $tab;
		}

		if ( ! isset( $this->subtabControllers[ $subtab ] ) ) {
			wp_die( self::PAGE_NOT_FOUND );
		}

		$tabController = $this->subtabControllers[ $subtab ];

		$queryArgs = array(
			'page' => $page,
			'tab'  => $tab,
		);
		if ( $subtab ) {
			$queryArgs['subtab'] = $subtab;
		}
		$destinationUrl = add_query_arg( $queryArgs, admin_url( 'admin.php' ) );

		return $tabController->render( $destinationUrl );
	}
}
