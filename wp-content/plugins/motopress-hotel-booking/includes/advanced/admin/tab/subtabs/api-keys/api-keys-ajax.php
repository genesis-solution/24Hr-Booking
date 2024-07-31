<?php
/**
 * Api Keys Ajax
 *
 * @package MPHB\Advanced\Admin
 * @since 4.1.0
 */

namespace MPHB\Advanced\Admin\Tab\Subtabs\ApiKeys;

use MPHB\Advanced\Api\ApiHelper;

defined( 'ABSPATH' ) || exit;

class ApiKeysAjax {

	public function __construct() {
		add_action( 'wp_ajax_update_api_key', array( $this, 'updateApiKey' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'registerAjaxScript' ) );
	}

	public function registerAjaxScript() {
		// API settings.
		if ( isset( $_GET['page'], $_GET['tab'] ) &&
			 'mphb_settings' === $_GET['page'] &&
			 'advanced' === $_GET['tab'] &&
			 empty( $_GET['subtab'] )
		) {
			wp_enqueue_script(
				'jquery-blockui',
				MPHB()->getPluginUrl( 'includes/advanced/admin/tab/subtabs/api-keys/js/lib/jquery.blockUI.js' ),
				array( 'jquery' ),
				'2.70',
				true
			);

			wp_enqueue_script(
				'qrcode',
				MPHB()->getPluginUrl( 'includes/advanced/admin/tab/subtabs/api-keys/js/lib/jquery.qrcode.js' ),
				array( 'jquery' ),
				false,
				true
			);

			wp_enqueue_script(
				'mphb-api-keys-page',
				MPHB()->getPluginUrl( 'includes/advanced/admin/tab/subtabs/api-keys/js/api-keys-page.js' ),
				array( 'jquery', 'qrcode', 'jquery-blockui', 'wp-util' ),
				MPHB()->getVersion()
			);

			wp_localize_script(
				'mphb-api-keys-page',
				'mphb_api_keys',
				array(
					'ajax_url'             => admin_url( 'admin-ajax.php' ),
					'update_api_key_nonce' => wp_create_nonce( 'update_api_key' ),
					'clipboard_failed'     => esc_html__(
						'Copying to clipboard failed. Please press Ctrl/Cmd+C to copy.',
						'motopress-hotel-booking'
					),
				)
			);
		}
	}

	public function updateApiKey() {
		global $wpdb;

		check_ajax_referer( 'update_api_key', 'update_api_key_nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( - 1 );
		}

		$response = array();

		try {
			if ( empty( $_POST['description'] ) ) {
				throw new \Exception( __( 'Description is missing.', 'motopress-hotel-booking' ) );
			}
			if ( empty( $_POST['user'] ) ) {
				throw new \Exception( __( 'User is missing.', 'motopress-hotel-booking' ) );
			}
			if ( empty( $_POST['permissions'] ) ) {
				throw new \Exception( __( 'Permission is missing.', 'motopress-hotel-booking' ) );
			}

			$key_id      = isset( $_POST['key_id'] ) ? absint( $_POST['key_id'] ) : 0;
			$description = sanitize_text_field( wp_unslash( $_POST['description'] ) );
			$permissions = ( in_array(
				wp_unslash( $_POST['permissions'] ),
				array( 'read', 'write', 'read_write' ),
				true
			) ) ? sanitize_text_field( wp_unslash( $_POST['permissions'] ) ) : 'read';
			$user_id     = absint( $_POST['user'] );

			// Check if current user can edit other users.
			if ( $user_id && ! current_user_can( 'edit_user', $user_id ) ) {
				if ( get_current_user_id() !== $user_id ) {
					throw new \Exception(
						__(
							'You do not have permission to assign API Keys to the selected user.',
							'motopress-hotel-booking'
						)
					);
				}
			}

			if ( 0 < $key_id ) {
				$data = array(
					'user_id'     => $user_id,
					'description' => $description,
					'permissions' => $permissions,
				);

				$wpdb->update(
					$wpdb->prefix . 'mphb_api_keys',
					$data,
					array( 'key_id' => $key_id ),
					array(
						'%d',
						'%s',
						'%s',
					),
					array( '%d' )
				);

				$response                    = $data;
				$response['consumer_key']    = '';
				$response['consumer_secret'] = '';
				$response['message']         = __( 'API Key updated successfully.', 'motopress-hotel-booking' );
			} else {
				$consumer_key    = 'ck_' . ApiHelper::randHash();
				$consumer_secret = 'cs_' . ApiHelper::randHash();

				$data = array(
					'user_id'         => $user_id,
					'description'     => $description,
					'permissions'     => $permissions,
					'consumer_key'    => ApiHelper::apiHash( $consumer_key ),
					'consumer_secret' => $consumer_secret,
					'truncated_key'   => substr( $consumer_key, - 7 ),
				);

				$wpdb->insert(
					$wpdb->prefix . 'mphb_api_keys',
					$data,
					array(
						'%d',
						'%s',
						'%s',
						'%s',
						'%s',
						'%s',
					)
				);

				$key_id                      = $wpdb->insert_id;
				$response                    = $data;
				$response['consumer_key']    = $consumer_key;
				$response['consumer_secret'] = $consumer_secret;
				$response['rest_endpoint']   = esc_url( get_rest_url( null, ApiHelper::getNamespace() ) );
				$response['message']         = __(
					'API Key generated successfully. Make sure to copy your new keys now as the secret key will be hidden once you leave this page.',
					'motopress-hotel-booking'
				);
				$response['revoke_url']      = '<a style="color: #a00; text-decoration: none;" href="' . esc_url(
					wp_nonce_url(
						add_query_arg(
							array( 'revoke-key' => $key_id ),
							admin_url( 'admin.php?page=mphb_settings&tab=advanced' )
						),
						'revoke'
					)
				) . '">' . __( 'Revoke key', 'motopress-hotel-booking' ) . '</a>';
			}
		} catch ( \Exception $e ) {
			wp_send_json_error( array( 'message' => $e->getMessage() ) );
		}

		// wp_send_json_success must be outside the try block not to break phpunit tests.
		wp_send_json_success( $response );
	}
}
