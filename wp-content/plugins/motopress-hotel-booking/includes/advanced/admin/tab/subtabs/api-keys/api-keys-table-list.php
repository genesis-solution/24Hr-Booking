<?php
/**
 * Api Keys Table List
 *
 * @package MPHB\Advanced\Admin
 * @since 4.1.0
 */

namespace MPHB\Advanced\Admin\Tab\Subtabs\ApiKeys;

use WP_List_Table;

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

class ApiKeysTableList extends WP_List_Table {

	/**
	 * Initialize the API key table list.
	 */
	public function __construct() {
		parent::__construct(
			array(
				'singular' => 'key',
				'plural'   => 'keys',
				'ajax'     => false,
			)
		);

	}

	public function render() {
		$this->prepare_items();
		echo '<div class="mphb-ctrl-wrapper">'
			 . $this->display()
			 . '</div>';
	}

	/**
	 * No items found text.
	 */
	public function no_items() {
		esc_html_e( 'No keys found.', 'motopress-hotel-booking' );
	}

	/**
	 * Get list columns.
	 *
	 * @return array
	 */
	public function get_columns() {
		return array(
			'cb'            => '<input type="checkbox" />',
			'title'         => __( 'Description', 'motopress-hotel-booking' ),
			'truncated_key' => __( 'Consumer key ending in', 'motopress-hotel-booking' ),
			'user'          => __( 'User', 'motopress-hotel-booking' ),
			'permissions'   => __( 'Permissions', 'motopress-hotel-booking' ),
			'last_access'   => __( 'Last access', 'motopress-hotel-booking' ),
		);
	}

	public function get_hidden_columns() {
		return array();
	}

	/**
	 * Column cb.
	 *
	 * @param  array $key  Key data.
	 *
	 * @return string
	 */
	public function column_cb( $key ) {
		return sprintf( '<input type="checkbox" name="key[]" value="%1$s" />', $key['key_id'] );
	}

	/**
	 * Return title column.
	 *
	 * @param  array $key  Key data.
	 *
	 * @return string
	 */
	public function column_title( $key ) {
		$url     = admin_url( 'admin.php?page=mphb_settings&tab=advanced&edit-key=' . $key['key_id'] );
		$user_id = intval( $key['user_id'] );

		// Check if current user can edit other users or if it's the same user.
		$can_edit = current_user_can( 'edit_user', $user_id ) || get_current_user_id() === $user_id;

		$output = '<strong>';
		if ( $can_edit ) {
			$output .= '<a href="' . esc_url( $url ) . '" class="row-title">';
		}
		if ( empty( $key['description'] ) ) {
			$output .= esc_html__( 'API key', 'motopress-hotel-booking' );
		} else {
			$output .= esc_html( $key['description'] );
		}
		if ( $can_edit ) {
			$output .= '</a>';
		}
		$output .= '</strong>';

		// Get actions.
		$actions = array(
			/* translators: %d: API key ID. */
			'id' => sprintf( __( 'ID: %d', 'motopress-hotel-booking' ), $key['key_id'] ),
		);

		if ( $can_edit ) {
			$actions['edit']  = '<a href="' . esc_url( $url ) . '">' . __( 'Edit', 'motopress-hotel-booking' ) . '</a>';
			$actions['trash'] = '<a class="submitdelete" aria-label="' . esc_attr__( 'Revoke key', 'motopress-hotel-booking' ) . '" href="' . esc_url(
				wp_nonce_url(
					add_query_arg(
						array(
							'revoke-key' => $key['key_id'],
						),
						admin_url( 'admin.php?page=mphb_settings&tab=advanced' )
					),
					'revoke'
				)
			) . '">' . esc_html__( 'Revoke', 'motopress-hotel-booking' ) . '</a>';
		}

		$row_actions = array();

		foreach ( $actions as $action => $link ) {
			$row_actions[] = '<span class="' . esc_attr( $action ) . '">' . $link . '</span>';
		}

		$output .= '<div class="row-actions">' . implode( ' | ', $row_actions ) . '</div>';

		return $output;
	}

	/**
	 * Return truncated consumer key column.
	 *
	 * @param  array $key  Key data.
	 *
	 * @return string
	 */
	public function column_truncated_key( $key ) {
		return '<code>&hellip;' . esc_html( $key['truncated_key'] ) . '</code>';
	}

	/**
	 * Return user column.
	 *
	 * @param  array $key  Key data.
	 *
	 * @return string
	 */
	public function column_user( $key ) {
		$user = get_user_by( 'id', $key['user_id'] );

		if ( ! $user ) {
			return '';
		}

		if ( current_user_can( 'edit_user', $user->ID ) ) {
			return '<a href="' . esc_url( add_query_arg( array( 'user_id' => $user->ID ), admin_url( 'user-edit.php' ) ) ) . '">' . esc_html( $user->display_name ) . '</a>';
		}

		return esc_html( $user->display_name );
	}

	/**
	 * Return permissions column.
	 *
	 * @param  array $key  Key data.
	 *
	 * @return string
	 */
	public function column_permissions( $key ) {
		$permission_key = $key['permissions'];
		$permissions    = array(
			'read'       => __( 'Read', 'motopress-hotel-booking' ),
			'write'      => __( 'Write', 'motopress-hotel-booking' ),
			'read_write' => __( 'Read/Write', 'motopress-hotel-booking' ),
		);

		if ( isset( $permissions[ $permission_key ] ) ) {
			return esc_html( $permissions[ $permission_key ] );
		} else {
			return '';
		}
	}

	/**
	 * Return last access column.
	 *
	 * @param  array $key  Key data.
	 *
	 * @return string
	 */
	public function column_last_access( $key ) {
		if ( ! empty( $key['last_access'] ) ) {
			/* translators: 1: last access date 2: last access time */
			$date = sprintf(
				__( '%1$s at %2$s', 'motopress-hotel-booking' ),
				date_i18n( get_option( 'date_format' ), strtotime( $key['last_access'] ) ),
				date_i18n( get_option( 'time_format' ), strtotime( $key['last_access'] ) )
			);

			return apply_filters( 'mphb_api_key_last_access_datetime', $date, $key['last_access'] );
		}

		return __( 'Unknown', 'motopress-hotel-booking' );
	}

	/**
	 * Get bulk actions.
	 *
	 * @return array
	 */
	protected function get_bulk_actions() {
		if ( ! current_user_can( 'remove_users' ) ) {
			return array();
		}

		return array(
			'revoke' => __( 'Revoke', 'motopress-hotel-booking' ),
		);
	}

	/**
	 * Prepare table list items.
	 */
	public function prepare_items() {
		global $wpdb;

		$columns      = $this->get_columns();
		$hidden       = $this->get_hidden_columns();
		$sortable     = $this->get_sortable_columns();
		$per_page     = $this->get_items_per_page( ApiKeysScreenOption::SCREEN_OPTION_NAME );
		$current_page = $this->get_pagenum();

		if ( 1 < $current_page ) {
			$offset = $per_page * ( $current_page - 1 );
		} else {
			$offset = 0;
		}

		$search = '';

		if ( ! empty( $_REQUEST['s'] ) ) { // WPCS: input var okay, CSRF ok.
			$search = "AND description LIKE '%" . esc_sql( $wpdb->esc_like( sanitize_text_field( wp_unslash( $_REQUEST['s'] ) ) ) ) . "%' "; // WPCS: input var okay, CSRF ok.
		}

		$data = $wpdb->get_results(
			"SELECT key_id, user_id, description, permissions, truncated_key, last_access FROM {$wpdb->prefix}mphb_api_keys WHERE 1 = 1 {$search}" .
			$wpdb->prepare( 'ORDER BY key_id DESC LIMIT %d OFFSET %d;', $per_page, $offset ),
			ARRAY_A
		); // WPCS: unprepared SQL ok.

		$count = $wpdb->get_var( "SELECT COUNT(key_id) FROM {$wpdb->prefix}mphb_api_keys WHERE 1 = 1 {$search};" ); // WPCS: unprepared SQL ok.

		$this->set_pagination_args(
			array(
				'total_items' => $count,
				'per_page'    => $per_page,
				'total_pages' => ceil( $count / $per_page ),
			)
		);

		$this->_column_headers = array( $columns, $hidden, $sortable );
		$this->items           = $data;
	}
}
