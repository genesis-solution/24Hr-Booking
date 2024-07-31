<?php

namespace MPHBW\Admin\Groups;

class LicenseSettingsGroup extends \MPHB\Admin\Groups\SettingsGroup {

	public function render(){
		parent::render();

		$license = MPHBW()->getSettings()->license()->getLicenseKey();
		if ( $license ) {
			$licenseData = MPHBW()->getSettings()->license()->getLicenseData();
		}
		?>
		<i><?php _e( "The License Key is required in order to get automatic plugin updates and support. You can manage your License Key in your personal account. <a href='https://motopress.zendesk.com/hc/en-us/articles/202812996-How-to-use-your-personal-MotoPress-account' target='blank'>Learn more</a>.", 'mphb-woocommerce' ); ?></i>
		<table class="form-table">
			<tbody>
				<tr valign="top">
					<th scope="row" valign="top">
						<?php echo __( 'License Key', 'mphb-woocommerce' ); ?>
					</th>
					<td>
						<input id="mphbw_edd_license_key" name="mphbw_edd_license_key" type="password"
							   class="regular-text" value="<?php esc_attr_e( $license ); ?>" autocomplete="new-password"/>

						<?php if ( $license ) { ?>
							<i style="display:block;"><?php echo str_repeat( "&#8226;", 20 ) . substr( $license, -7 ); ?></i>
						<?php } ?>
					</td>
				</tr>
				<?php if ( isset( $licenseData, $licenseData->license ) ) { ?>
					<tr valign="top">
						<th scope="row" valign="top">
							<?php _e( 'Status', 'mphb-woocommerce' ); ?>
						</th>
						<td>
							<?php
							switch ( $licenseData->license ) {
								case 'inactive' :
								case 'site_inactive' :
									_e( 'Inactive', 'mphb-woocommerce' );
									break;
								case 'valid' :
									if ( $licenseData->expires !== 'lifetime' ) {
										$date	 = ($licenseData->expires) ? new \DateTime( $licenseData->expires ) : false;
										$expires = ($date) ? ' ' . $date->format( 'd.m.Y' ) : '';
										echo __( 'Valid until', 'mphb-woocommerce' ) . $expires;
									} else {
										echo __( 'Valid (Lifetime)', 'mphb-woocommerce' );
									}
									break;
								case 'disabled' :
									_e( 'Disabled', 'mphb-woocommerce' );
									break;
								case 'expired' :
									_e( 'Expired', 'mphb-woocommerce' );
									break;
								case 'invalid' :
									_e( 'Invalid', 'mphb-woocommerce' );
									break;
								case 'item_name_mismatch' :
									_e( "Your License Key does not match the installed plugin. <a href='https://motopress.zendesk.com/hc/en-us/articles/202957243-What-to-do-if-the-license-key-doesn-t-correspond-with-the-plugin-license' target='_blank'>How to fix this.</a>", 'mphb-woocommerce' );
									break;
								case 'invalid_item_id' :
									_e( 'Product ID is not valid', 'mphb-woocommerce' );
									break;
							}
							?>
						</td>
					</tr>
					<?php if ( in_array( $licenseData->license, array( 'inactive', 'site_inactive', 'valid', 'expired' ) ) ) { ?>
						<tr valign="top">
							<th scope="row" valign="top">
								<?php _e( 'Action', 'mphb-woocommerce' ); ?>
							</th>
							<td>
								<?php
								if ( $licenseData->license === 'inactive' || $licenseData->license === 'site_inactive' ) {
									wp_nonce_field( 'mphbw_edd_nonce', 'mphbw_edd_nonce' );
									?>
									<input type="submit" class="button-secondary" name="edd_license_activate"
										   value="<?php _e( 'Activate License', 'mphb-woocommerce' ); ?>"/>

								<?php } elseif ( $licenseData->license === 'valid' ) { ?>
									<?php wp_nonce_field( 'mphbw_edd_nonce', 'mphbw_edd_nonce' ); ?>

									<input type="submit" class="button-secondary" name="edd_license_deactivate"
										   value="<?php _e( 'Deactivate License', 'mphb-woocommerce' ); ?>"/>

								<?php } elseif ( $licenseData->license === 'expired' ) { ?>

									<a href="<?php echo MPHBW()->getSettings()->license()->getRenewUrl(); ?>"
									   class="button-secondary"
									   target="_blank">
										   <?php _e( 'Renew License', 'mphb-woocommerce' ); ?>
									</a>

									<?php
								}
								?>
							</td>
						</tr>
					<?php } ?>
				<?php } ?>
			</tbody>
		</table>
		<?php
	}

	public function save(){

		parent::save();

		if ( empty( $_POST ) ) {
			return;
		}

		$queryArgs = array(
			'page'	 => $this->getPage(),
			'tab'	 => $this->getName()
		);

		if ( isset( $_POST['mphbw_edd_license_key'] ) ) {

			$licenseKey			 = trim( $_POST['mphbw_edd_license_key'] );
			$licenseKeyChanged	 = $licenseKey != MPHBW()->getSettings()->license()->getLicenseKey();

			if ( $licenseKeyChanged ) {
				MPHBW()->getSettings()->license()->setLicenseKey( $licenseKey );
			}
		}

		//activate
		if ( isset( $_POST['edd_license_activate'] ) ) {
			if ( !check_admin_referer( 'mphbw_edd_nonce', 'mphbw_edd_nonce' ) ) {
				return; // get out if we didn't click the Activate button
			}
			$licenseData = self::activateLicense();

			if ( $licenseData === false ) {
				return false;
			}

			if ( !$licenseData->success && $licenseData->error === 'item_name_mismatch' ) {
				$queryArgs['item-name-mismatch'] = 'true';
			}
		}

		//deactivate
		if ( isset( $_POST['edd_license_deactivate'] ) ) {
			// run a quick security check
			if ( !check_admin_referer( 'mphbw_edd_nonce', 'mphbw_edd_nonce' ) ) {
				return; // get out if we didn't click the Activate button
			}
			// retrieve the license from the database
			$licenseData = self::deactivateLicense();

			if ( $licenseData === false ) {
				return false;
			}
		}
	}

	static public function activateLicense(){
		// data to send in our API request
		$apiParams = array(
			'edd_action' => 'activate_license',
			'license'	 => MPHBW()->getSettings()->license()->getLicenseKey(),
			'item_id'	 => MPHBW()->getSettings()->license()->getProductId(),
			'url'		 => home_url(),
		);

		$activateUrl = add_query_arg( $apiParams, MPHBW()->getSettings()->license()->getStoreUrl() );

		// Call the custom API.
		$response = wp_remote_get( $activateUrl, array( 'timeout' => 15, 'sslverify' => false ) );

		// make sure the response came back okay
		if ( is_wp_error( $response ) ) {
			return false;
		}

		// decode the license data
		$licenseData = json_decode( wp_remote_retrieve_body( $response ) );

		// $licenseData->license will be either "active" or "inactive"
		MPHBW()->getSettings()->license()->setLicenseStatus( $licenseData->license );

		return $licenseData;
	}

	static public function deactivateLicense(){

		// data to send in our API request
		$apiParams = array(
			'edd_action' => 'deactivate_license',
			'license'	 => MPHBW()->getSettings()->license()->getLicenseKey(),
			'item_id'	 => MPHBW()->getSettings()->license()->getProductId(),
			'url'		 => home_url(),
		);

		$deactivateUrl = add_query_arg( $apiParams, MPHBW()->getSettings()->license()->getStoreUrl() );

		// Call the custom API.
		$response = wp_remote_get( $deactivateUrl, array( 'timeout' => 15, 'sslverify' => false ) );

		// make sure the response came back okay
		if ( is_wp_error( $response ) ) {
			return false;
		}

		// decode the license data
		$licenseData = json_decode( wp_remote_retrieve_body( $response ) );

		// $license_data->license will be either "deactivated" or "failed"
		if ( $licenseData->license == 'deactivated' ) {
			MPHBW()->getSettings()->license()->setLicenseStatus( '' );
		}

		return $licenseData;
	}

}
