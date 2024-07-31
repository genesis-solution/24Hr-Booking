<?php

namespace MPHB\Admin\Tabs;

use \MPHB\Admin\Groups;

class SettingsSubTab extends SettingsTab {

	/**
	 *
	 * @var string
	 */
	protected $nonceName;

	/**
	 *
	 * @var string
	 */
	protected $nonceSaveAction;

	/**
	 *
	 * @var \MPHB\Admin\Groups\SettingsGroup[]
	 */
	protected $groups = array();

	/**
	 *
	 * @var string
	 */
	protected $name;

	/**
	 *
	 * @var string
	 */
	protected $label;

	/**
	 *
	 * @var string
	 */
	protected $pageName;

	/**
	 *
	 * @var string
	 */
	protected $tabName;

	/**
	 *
	 * @var string
	 */
	protected $optionGroupName;

	/**
	 *
	 * @var string
	 */
	protected $description;

	/**
	 *
	 * @param string $name
	 * @param string $label
	 * @param string $pageName
	 * @param string $tabName
	 */
	public function __construct( $name, $label, $pageName, $tabName = '' ) {
		$this->name            = $name;
		$this->label           = $label;
		$this->pageName        = $pageName;
		$this->tabName         = $tabName;
		$this->optionGroupName = $this->pageName . '_' . $this->tabName . '_' . $this->name;
		$this->nonceName       = '_nonce_' . $this->optionGroupName;
		$this->nonceSaveAction = '_save_' . $this->optionGroupName;
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
	 * @param \MPHB\Admin\Groups\SettingsGroup[] $fields
	 */
	public function addGroups( $groups ) {
		array_map( array( $this, 'addGroup' ), $groups );
	}

	/**
	 *
	 * @return \MPHB\Admin\Groups\SettingsGroup[]
	 */
	public function getGroups() {
		return $this->groups;
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

	/**
	 *
	 * @return string
	 */
	function getDescription() {
		return $this->description;
	}

	/**
	 *
	 * @param string $description
	 */
	function setDescription( $description ) {
		$this->description = $description;
	}

	public function register() {
		foreach ( $this->groups as $group ) {
			$group->register();
		}
	}

	/**
	 * @since 3.7.0 added new action - "mphb_settings_tab_after_submit_button".
	 */
	public function render() {

		$destinationUrl = add_query_arg(
			array(
				'page'   => $this->pageName,
				'tab'    => $this->tabName,
				'subtab' => $this->name,
			),
			admin_url( 'admin.php' )
		);

		if ( ! empty( $this->description ) ) {
			echo '<p>' . wp_kses_post( $this->description ) . '</p>';
		}

		printf( '<form action="%s" method="POST">', esc_url( $destinationUrl ) );
		wp_nonce_field( $this->nonceSaveAction, $this->nonceName );
		settings_fields( $this->optionGroupName );
		do_settings_sections( $this->optionGroupName );
		echo '<div class="mphb-settings-tab-actions">';
		submit_button();
		do_action( 'mphb_settings_tab_after_submit_button', $this->tabName, $this->name );
		echo '</div>';
		echo '</form>';
	}

	public function save() {
		if ( $this->checkNonce() ) {

			foreach ( $this->groups as $group ) {
				$group->save();
			}

			$urlArgs = array(
				'page'             => $this->pageName,
				'tab'              => $this->tabName,
				'subtab'           => $this->name,
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
