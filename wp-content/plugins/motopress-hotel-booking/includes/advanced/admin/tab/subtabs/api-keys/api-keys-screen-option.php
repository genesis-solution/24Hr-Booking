<?php
/**
 * Api Keys Screen Option
 *
 * @package MPHB\Advanced\Admin
 * @since 4.1.0
 */

namespace MPHB\Advanced\Admin\Tab\Subtabs\ApiKeys;

defined( 'ABSPATH' ) || exit;

class ApiKeysScreenOption {

	const TAB                = 'advanced';
	const SUBTAB             = '';
	const SCREEN_OPTION_NAME = 'mphb_api_keys_per_page';
	const SCREEN_OPTION      = array(
		'default' => 10,
		'option'  => self::SCREEN_OPTION_NAME,
	);

	public function __construct() {
		add_action( 'current_screen', array( $this, 'showScreenOption' ) );
		add_filter( 'set-screen-option', array( $this, 'setScreenOption' ), 10, 3 );
	}

	/**
	 * Add screen option.
	 */
	public function showScreenOption() {
		$page_hook = get_plugin_page_hookname( MPHB()->getSettingsMenuPage()->getName(), '' );
		add_action( "load-{$page_hook}", array( $this, 'screenOption' ) );
	}

	/**
	 * Show screen option.
	 */
	public function screenOption() {

		if ( ! isset( $_GET['create-key'] ) &&
			 ! isset( $_GET['edit-key'] ) &&
			MPHB()->getSettingsMenuPage()->isCurrentPage(
				array(
					'tab'    => self::TAB,
					'subtab' => self::SUBTAB,
				)
			)
		) {
			add_screen_option( 'per_page', self::SCREEN_OPTION );
		}
	}

	/**
	 * Save screen options on update.
	 *
	 * @param bool|int $status Screen option value. Default false to skip.
	 * @param string   $option The option name.
	 * @param int      $value  The number of rows to use.
	 */
	public function setScreenOption( $status, $option, $value ) {
		if ( in_array( $option, array( self::SCREEN_OPTION_NAME ), true ) ) {
			return $value;
		}

		return $status;
	}
}
