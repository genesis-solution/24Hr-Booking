<?php
/**
 * Admin view: Edit API keys
 *
 * @package MPHB\Advanced\Admin
 * @since 4.1.0
 */

defined( 'ABSPATH' ) || exit;
?>

<div id="key-fields" class="settings-panel">
	<h2><?php esc_html_e( 'Generate API key', 'motopress-hotel-booking' ); ?></h2>

	<input type="hidden" id="key_id" value="<?php echo esc_attr( $key_id ); ?>" />

	<table id="api-keys-options" class="form-table">
		<tbody>
		<tr valign="top">
			<th scope="row" class="titledesc">
				<label for="key_description">
					<?php esc_html_e( 'Description', 'motopress-hotel-booking' ); ?>
				</label>
				<p class="description">
					<?php esc_html_e( 'Friendly name for identifying this key.', 'motopress-hotel-booking' ); ?>
				</p>
			</th>
			<td class="forminp">
				<input id="key_description" name="key_description" type="text" class="input-text regular-text" value="<?php echo esc_attr( $key_data['description'] ); ?>" />
				<p class="description"><?php esc_html_e( 'Required', 'motopress-hotel-booking' ); ?></p>
			</td>
		</tr>
		<tr valign="top">
			<th scope="row" class="titledesc">
				<label for="key_user">
					<?php esc_html_e( 'User', 'motopress-hotel-booking' ); ?>
				</label>
				<p class="description">
					<?php esc_html_e( 'Owner of these keys.', 'motopress-hotel-booking' ); ?>
				</p>
			</th>
			<td class="forminp">
				<select class="mphb-customer-search" id="key_user" name="key_user">
					<?php foreach ( $users as $user ) : ?>
						<option value="<?php echo esc_attr( $user->ID ); ?>" <?php echo ( $user->ID == $key_user_id ) ? 'selected="selected"' : ''; ?>>
							<?php echo htmlspecialchars( wp_kses_post( sprintf( '#%1$s %2$s – %3$s', absint( $user->ID ), $user->display_name, $user->user_email ) ) ); // htmlspecialchars to prevent XSS when rendered by select. ?>
						</option>
					<?php endforeach; ?>
				</select>
			</td>
		</tr>
		<tr valign="top">
			<th scope="row" class="titledesc">
				<label for="key_permissions">
					<?php esc_html_e( 'Permissions', 'motopress-hotel-booking' ); ?>
				</label>
				<p class="description">
					<?php esc_html_e( 'Access type of these keys.', 'motopress-hotel-booking' ); ?>
				</p>
			</th>
			<td class="forminp">
				<select id="key_permissions" name="key_permissions" class="mphb-enhanced-select">
					<?php
					$permissions = array(
						'read'       => __( 'Read', 'motopress-hotel-booking' ),
						'write'      => __( 'Write', 'motopress-hotel-booking' ),
						'read_write' => __( 'Read/Write', 'motopress-hotel-booking' ),
					);

					foreach ( $permissions as $permission_id => $permission_name ) :
						?>
						<option value="<?php echo esc_attr( $permission_id ); ?>" <?php selected( $key_data['permissions'], $permission_id, true ); ?>><?php echo esc_html( $permission_name ); ?></option>
					<?php endforeach; ?>
				</select>
			</td>
		</tr>

		<?php if ( 0 !== $key_id ) : ?>
			<tr valign="top">
				<th scope="row" class="titledesc">
					<?php esc_html_e( 'Consumer key ending in', 'motopress-hotel-booking' ); ?>
				</th>
				<td class="forminp">
					<code>&hellip;<?php echo esc_html( $key_data['truncated_key'] ); ?></code>
				</td>
			</tr>
			<tr valign="top">
				<th scope="row" class="titledesc">
					<?php esc_html_e( 'Last access', 'motopress-hotel-booking' ); ?>
				</th>
				<td class="forminp">
						<span>
						<?php
						if ( ! empty( $key_data['last_access'] ) ) {
							/* translators: 1: last access date 2: last access time */
							$date = sprintf( __( '%1$s at %2$s', 'motopress-hotel-booking' ), date_i18n( get_option( 'date_format' ), strtotime( $key_data['last_access'] ) ), date_i18n( get_option( 'time_format' ), strtotime( $key_data['last_access'] ) ) );

							echo esc_html( apply_filters( 'mphb_api_key_last_access_datetime', $date, $key_data['last_access'] ) );
						} else {
							esc_html_e( 'Unknown', 'motopress-hotel-booking' );
						}
						?>
						</span>
				</td>
			</tr>
		<?php endif ?>
		</tbody>
	</table>

	<?php do_action( 'mphb_admin_key_fields', $key_data ); ?>

	<?php
	if ( 0 === intval( $key_id ) ) {
		submit_button( __( 'Generate API key', 'motopress-hotel-booking' ), 'primary', 'update_api_key_form_submit' );
	} else {
		?>
		<p class="submit">
			<?php submit_button( __( 'Save Changes', 'motopress-hotel-booking' ), 'primary', 'update_api_key_form_submit', false ); ?>
			<a style="color: #a00; text-decoration: none; margin-left: 10px;" href="<?php echo esc_url( wp_nonce_url( add_query_arg( array( 'revoke-key' => $key_id ), admin_url( 'admin.php?page=mphb_settings&tab=advanced' ) ), 'revoke' ) ); ?>"><?php esc_html_e( 'Revoke key', 'motopress-hotel-booking' ); ?></a>
		</p>
		<?php
	}
	?>
</div>

<style>
	.copy-secret, .copy-key{
		position: relative;
	}
	.copy-secret.tooltip:after,
	.copy-key.tooltip:after {
		content: attr(data-tooltip);
		position: absolute;
		right: -65px;
		color: #2271b1;
	}
	.tooltip-error{
		color: #a00;
	}
</style>

<script type="text/template" id="tmpl-api-keys-template">
	<p id="copy-error"></p>
	<table class="form-table">
		<tbody>
		<tr valign="top">
			<th scope="row" class="titledesc">
				<?php esc_html_e( 'Consumer key', 'motopress-hotel-booking' ); ?>
			</th>
			<td class="forminp">
				<input id="key_consumer_key" name="key_consumer_key" type="text" value="{{ data.consumer_key }}" size="55" readonly="readonly"> <button type="button" class="button-secondary copy-key" data-tooltip="<?php esc_attr_e( 'Copied!', 'motopress-hotel-booking' ); ?>"><?php esc_html_e( 'Copy', 'motopress-hotel-booking' ); ?></button>
			</td>
		</tr>
		<tr valign="top">
			<th scope="row" class="titledesc">
				<?php esc_html_e( 'Consumer secret', 'motopress-hotel-booking' ); ?>
			</th>
			<td class="forminp">
				<input id="key_consumer_secret" name="key_consumer_secret" type="text" value="{{ data.consumer_secret }}" size="55" readonly="readonly"> <button type="button" class="button-secondary copy-secret" data-tooltip="<?php esc_attr_e( 'Copied!', 'motopress-hotel-booking' ); ?>"><?php esc_html_e( 'Copy', 'motopress-hotel-booking' ); ?></button>
			</td>
		</tr>
		<tr valign="top">
			<th scope="row" class="titledesc">
				<?php esc_html_e( 'QR Code', 'motopress-hotel-booking' ); ?>
			</th>
			<td class="forminp">
				<div id="keys-qrcode"></div>
			</td>
		</tr>
		</tbody>
	</table>
</script>
