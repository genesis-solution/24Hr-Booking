<?php
/**
 * Api Keys Tab
 *
 * @package MPHB\Advanced\Admin
 * @since 4.1.0
 */

namespace MPHB\Advanced\Admin\Tab\Subtabs\ApiKeys;

defined( 'ABSPATH' ) || exit;

class ApiKeys {

	const TAB    = 'advanced';
	const SUBTAB = '';

	/**
	 * Initialize the API Keys admin actions.
	 */
	public function __construct() {
		new ApiKeysAjax();
		new ApiKeysScreenOption();
		add_action( 'current_screen', array( $this, 'actions' ) );
	}

	private function isCurrentPage() {
		return MPHB()->getSettingsMenuPage()->isCurrentPage(
			array(
				'tab'    => self::TAB,
				'subtab' => self::SUBTAB,
			)
		);
	}

	private function getButtonMarkupBackToKeysList( $destinationUrl ) {
		$backToKeysListUrl = remove_query_arg( array( 'create-key', 'edit-key' ), $destinationUrl );
		$linkStyles        = 'display: flex; width: max-content; align-items: center';
		$linkIcon          = '<span class="dashicons dashicons-arrow-left"></span>';
		$linkLabel         = __( 'Back', 'motopress-hotel-booking' );

		return sprintf( '<p><a href="%s" class="button" style="%s">%s<span> %s</span></a></p>', $backToKeysListUrl, $linkStyles, $linkIcon, $linkLabel );
	}

	public function render( $destinationUrl ) {
		if ( ! $this->isCurrentPage() ) {
			return false;
		}

		if ( isset( $_GET['create-key'] ) || isset( $_GET['edit-key'] ) ) {

			echo $this->getButtonMarkupBackToKeysList( $destinationUrl );

			$current_user_id = get_current_user_id();
			if ( current_user_can( 'edit_user' ) ) {
				$get_users_args = array( 'role__in' => array( 'administrator', \MPHB\UsersAndRoles\Roles::MANAGER ) );
			} else {
				$get_users_args = array( 'include' => array( $current_user_id ) );
			}

			$key_id      = isset( $_GET['edit-key'] ) ? absint( $_GET['edit-key'] ) : 0; // WPCS: input var okay, CSRF ok.
			$key_data    = $this->getKeyData( $key_id );
			$key_user_id = (int) $key_data['user_id'] ?? $current_user_id;

			$isCurrentUserCanEditKey = current_user_can( 'edit_user', $key_user_id );
			if ( $key_id && $key_user_id && ! $isCurrentUserCanEditKey ) {
				if ( $current_user_id !== $key_user_id ) {
					wp_die( esc_html__( 'You do not have permission to edit this API Key', 'motopress-hotel-booking' ) );
				}
			}

			$users = get_users( $get_users_args );

			echo '<form id="update_api_key" action="' . esc_url( $destinationUrl ) . '" method="POST">';
			include MPHB()->getPluginPath( 'includes/advanced/admin/tab/subtabs/api-keys/html-keys-edit.php' );
			echo '</form>';
		} else {
			echo '<form action="' . esc_url( $destinationUrl ) . '" method="POST">';
			wp_nonce_field( 'api_key_bulk_action', 'api_key_bulk_action_nonce' );
			$this->renderTableList();
			echo '</form>';
		}

		return true;
	}

	private function getTableListHeader() {
		return '<h2 class="mphb-table-list-header">' .
			   esc_html__( 'REST API', 'motopress-hotel-booking' ) .
			   ' <a href="' . esc_url( admin_url( 'admin.php?page=mphb_settings&tab=advanced&create-key=1' ) ) .
			   '" class="add-new-h2">' . esc_html__( 'Add key', 'motopress-hotel-booking' ) .
			   '</a></h2>';
	}

	/**
	 * Table list.
	 */
	private function renderTableList() {
		echo $this->getTableListHeader();
		$keys_table_list = new ApiKeysTableList();
		$keys_table_list->render();
	}

	/**
	 * Get key data.
	 *
	 * @param  int $key_id  API Key ID.
	 *
	 * @return array
	 */
	private function getKeyData( $key_id ) {
		global $wpdb;

		$empty = array(
			'key_id'        => 0,
			'user_id'       => '',
			'description'   => '',
			'permissions'   => '',
			'truncated_key' => '',
			'last_access'   => '',
		);

		if ( 0 === $key_id ) {
			return $empty;
		}

		$key = $wpdb->get_row(
			$wpdb->prepare(
				"SELECT key_id, user_id, description, permissions, truncated_key, last_access
				FROM {$wpdb->prefix}mphb_api_keys
				WHERE key_id = %d",
				$key_id
			),
			ARRAY_A
		);

		if ( is_null( $key ) ) {
			return $empty;
		}

		return $key;
	}

	/**
	 * API Keys admin actions.
	 */
	public function actions() {
		if ( ! $this->isCurrentPage() ) {
			return false;
		}
		// Revoke key.if
		if ( isset( $_REQUEST['revoke-key'] ) ) { // WPCS: input var okay, CSRF ok.
			$this->revokeKey();
		}
		// Bulk actions.
		if ( isset( $_REQUEST['action'] ) && isset( $_REQUEST['key'] ) ) { // WPCS: input var okay, CSRF ok.
			$this->bulkActions();
		}
	}

	/**
	 * Revoke key.
	 */
	private function revokeKey() {
		global $wpdb;

		check_admin_referer( 'revoke' );

		if ( isset( $_REQUEST['revoke-key'] ) ) { // WPCS: input var okay, CSRF ok.
			$key_id  = absint( $_REQUEST['revoke-key'] ); // WPCS: input var okay, CSRF ok.
			$user_id = (int) $wpdb->get_var( $wpdb->prepare( "SELECT user_id FROM {$wpdb->prefix}mphb_api_keys WHERE key_id = %d", $key_id ) );

			if ( $key_id && $user_id && ( current_user_can( 'edit_user', $user_id ) || get_current_user_id() === $user_id ) ) {
				$this->removeKey( $key_id );
			} else {
				wp_die( esc_html__( 'You do not have permission to revoke this API Key', 'motopress-hotel-booking' ) );
			}
		}

		wp_safe_redirect( esc_url_raw( add_query_arg( array( 'revoked' => 1 ), admin_url( 'admin.php?page=mphb_settings&tab=advanced' ) ) ) );
		exit();
	}

	/**
	 * Bulk actions.
	 */
	private function bulkActions() {
		check_admin_referer( 'api_key_bulk_action', 'api_key_bulk_action_nonce' );

		if ( ! current_user_can( 'remove_users' ) ) {
			wp_die( esc_html__( 'You do not have permission to edit API Keys', 'motopress-hotel-booking' ) );
		}

		if ( isset( $_REQUEST['action'] ) ) { // WPCS: input var okay, CSRF ok.
			$action = sanitize_text_field( wp_unslash( $_REQUEST['action'] ) ); // WPCS: input var okay, CSRF ok.
			$keys   = isset( $_REQUEST['key'] ) ? array_map( 'absint', (array) $_REQUEST['key'] ) : array(); // WPCS: input var okay, CSRF ok.

			if ( 'revoke' === $action ) {
				$this->bulkRevokeKey( $keys );
			}
		}
	}

	/**
	 * Bulk revoke key.
	 *
	 * @param  array $keys  API Keys.
	 */
	private function bulkRevokeKey( $keys ) {
		if ( ! current_user_can( 'remove_users' ) ) {
			wp_die( esc_html__( 'You do not have permission to revoke API Keys', 'motopress-hotel-booking' ) );
		}

		$qty = 0;
		foreach ( $keys as $key_id ) {
			$result = $this->removeKey( $key_id );

			if ( $result ) {
				$qty++;
			}
		}

		// Redirect to keys list page.
		wp_safe_redirect( esc_url_raw( add_query_arg( array( 'revoked' => $qty ), admin_url( 'admin.php?page=mphb_settings&tab=advanced' ) ) ) );
		exit();
	}

	/**
	 * Remove key.
	 *
	 * @param  int $key_id  API Key ID.
	 *
	 * @return bool
	 */
	private function removeKey( $key_id ) {
		global $wpdb;

		$delete = $wpdb->delete( $wpdb->prefix . 'mphb_api_keys', array( 'key_id' => $key_id ), array( '%d' ) );

		return $delete;
	}
}
