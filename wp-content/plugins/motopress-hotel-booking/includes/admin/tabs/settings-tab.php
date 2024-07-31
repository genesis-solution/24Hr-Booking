<?php

namespace MPHB\Admin\Tabs;

use \MPHB\Admin\Groups;

class SettingsTab {

	/**
	 * @var string
	 */
	private $nonceName;

	/**
	 * @var string
	 */
	private $nonceSaveAction;

	/**
	 * @var \MPHB\Admin\Groups\SettingsGroup[]
	 */
	protected $groups = array();

	/**
	 * @var SettingsSubTab[]
	 */
	protected $subTabs = array();

	/**
	 * @var string
	 */
	protected $name;

	/**
	 * @var string
	 */
	protected $label;

	/**
	 * @var string
	 */
	protected $pageName;

	/**
	 * @var string
	 */
	protected $optionGroupName;

	private $subTabName;

	/**
	 * @param string $name
	 * @param string $label
	 * @param string $pageName
	 * @param string $subTabName
	 */
	public function __construct( $name, $label, $pageName, $subTabName = '' ) {
		$this->name            = $name;
		$this->label           = $label;
		$this->pageName        = $pageName;
		$this->optionGroupName = $this->pageName . '_' . $this->name;
		$this->subTabName      = empty( $subTabName ) ? $this->label : $subTabName;
		$this->nonceName       = '_nonce_' . $this->optionGroupName;
		$this->nonceSaveAction = '_save_' . $this->optionGroupName;
	}

	/**
	 *
	 * @param \MPHB\Admin\Tabs\SettingsSubTab $subTab
	 */
	public function addSubTab( SettingsSubTab $subTab ) {
		$this->subTabs[ $subTab->getName() ] = $subTab;
	}

	/**
	 *
	 * @param \MPHB\Admin\Groups\SettingsGroup $group
	 */
	public function addGroup( Groups\SettingsGroup $group ) {
		$this->groups[] = $group;
	}

	/**
	 *
	 * @return \MPHB\Admin\Groups\SettingsGroup[]
	 */
	public function getGroups() {
		return $this->groups;
	}

	/**
	 * @param string $name Field name.
	 * @param int    $groupIndex Optional. The group to search in. All (-1) by
	 *    default.
	 * @return \MPHB\Admin\Fields\InputField|null Searched field or null if
	 * nothing found.
	 *
	 * @since 3.5.1
	 */
	public function findField( $name, $groupIndex = -1 ) {
		$searchInGroups = $groupIndex >= 0 ? array( $groupIndex ) : array_keys( $this->groups );

		foreach ( $searchInGroups as $groupIndex ) {
			$field = $this->groups[ $groupIndex ]->getFieldByName( $name );

			if ( ! is_null( $field ) ) {
				return $field;
			}
		}

		// Nothing found
		return null;
	}

	/**
	 *
	 * @return string
	 */
	function getNonceName() {
		return $this->nonceName;
	}

	/**
	 *
	 * @return string
	 */
	function getNonceSaveAction() {
		return $this->nonceSaveAction;
	}

	/**
	 *
	 * @return string
	 */
	function getName() {
		return $this->name;
	}

	/**
	 *
	 * @return string
	 */
	function getLabel() {
		return $this->label;
	}

	/**
	 *
	 * @return string
	 */
	function getPageName() {
		return $this->pageName;
	}

	/**
	 *
	 * @return string
	 */
	function getOptionGroupName() {
		return $this->optionGroupName;
	}

	public function register() {
		foreach ( $this->groups as $group ) {
			$group->register();
		}
		foreach ( $this->subTabs as $subTab ) {
			$subTab->register();
		}
	}

	public function render() {
		$subTab = $this->detectSubTab();

		if ( ! empty( $this->subTabs ) ) {
			$this->renderNavs( $subTab );
		}

		if ( empty( $subTab ) ) {
			$this->renderTab();
		} else {
			$this->subTabs[ $subTab ]->render();
		}
	}

	protected function renderNavs( $currentSubTab ) {
		?>
		<ul class="subsubsub">
			<?php
			$tabUrl    = esc_url(
				add_query_arg(
					array(
						'page' => $this->pageName,
						'tab'  => $this->name,
					),
					admin_url( 'admin.php' )
				)
			);
			$tabClass  = empty( $currentSubTab ) ? 'current' : '';
			$separator = ! empty( $this->subTabs ) ? ' | ' : '';
			// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			printf( '<li><a href="%1$s" class="%2$s">%3$s</a>%4$s</li>', esc_url( $tabUrl ), esc_attr( $tabClass ), $this->subTabName, $separator );

			$subTabsKeys = array_keys( $this->subTabs );
			$lastKey     = end( $subTabsKeys );
			foreach ( $this->subTabs as $subTab ) {
				$subTabUrl   = esc_url(
					add_query_arg(
						array(
							'page'   => $this->pageName,
							'tab'    => $this->name,
							'subtab' => $subTab->getName(),
						),
						admin_url( 'admin.php' )
					)
				);
				$subTabClass = $currentSubTab === $subTab->getName() ? 'current' : '';
				$separator   = $subTab->getName() !== $lastKey ? ' | ' : '';
				// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				printf( '<li><a href="%1$s" class="%2$s">%3$s</a>%4$s</li>', esc_url( $subTabUrl ), esc_attr( $subTabClass ), $subTab->getLabel(), $separator );
			}
			?>
		</ul>
		<br class="clear" />
		<?php
	}

	/**
	 * @since 3.7.0 added new action - "mphb_settings_tab_after_submit_button".
	 */
	protected function renderTab() {
		$destinationUrl = add_query_arg(
			array(
				'page' => $this->getPageName(),
				'tab'  => $this->getName(),
			),
			admin_url( 'admin.php' )
		);

		printf( '<form action="%s" method="POST">', esc_url( $destinationUrl ) );
		wp_nonce_field( $this->nonceSaveAction, $this->nonceName );
		settings_fields( $this->optionGroupName );
		do_settings_sections( $this->optionGroupName );
		echo '<div class="mphb-settings-tab-actions">';
		submit_button();
		do_action( 'mphb_settings_tab_after_submit_button', $this->getName(), '' );
		echo '</div>';
		echo '</form>';
	}

	public function detectSubTab() {

		$subTab = '';

		if ( ! empty( $_GET['subtab'] ) ) {

			// phpcs:ignore WordPress.Security.ValidatedSanitizedInput
			$subTabKey = mphb_clean( wp_unslash( $_GET['subtab'] ) );
			$subTab    = array_key_exists( $subTabKey, $this->subTabs ) ? $subTabKey : '';
		}

		return $subTab;
	}

	public function save() {

		$subTab = $this->detectSubTab();

		if ( empty( $subTab ) ) {
			$this->saveTab();
		} else {
			$this->subTabs[ $subTab ]->save();
		}
	}

	private function saveTab() {
		if ( $this->checkNonce() ) {

			foreach ( $this->groups as $group ) {
				$group->save();
			}

			$urlArgs = array(
				'page'             => $this->getPageName(),
				'tab'              => $this->getName(),
				'settings-updated' => 'true',
			);

			$redirectUrl = add_query_arg( $urlArgs, admin_url( 'admin.php' ) );

			wp_redirect( $redirectUrl );
		}
	}

	/**
	 *
	 * @return bool
	 */
	private function checkNonce() {
		return isset( $_POST[ $this->nonceName ] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST[ $this->nonceName ] ) ), $this->nonceSaveAction );
	}

}
