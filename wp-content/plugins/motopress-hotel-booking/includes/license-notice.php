<?php

namespace MPHB;

class LicenseNotice {

	const ACTION_DISMISS = 'mphb_dismiss_license_notice';

	public function __construct() {
		add_action( 'admin_notices', array( $this, 'showNotice' ) );
		if ( is_multisite() ) {
			add_action( 'network_admin_notices', array( $this, 'showNotice' ) );
		}
	}

	public function showNotice() {

		global $pagenow;

		if ( $pagenow === 'plugins.php' && is_main_site() && ! MPHB()->settings()->license()->needHideNotice() ) {

			$license = MPHB()->settings()->license()->getLicenseKey();

			if ( $license ) {
				$licenseData = MPHB()->settings()->license()->getLicenseData();
			}

			if ( ! $license || ! isset( $licenseData->license ) || $licenseData->license !== 'valid' ) {
				?>
				<div class="error">
					<a id="mphb-dismiss-license-notice" href="javascript:void(0);" style="float: right;padding-top: 9px; text-decoration: none;">
						<?php esc_html_e( 'Dismiss ', 'motopress-hotel-booking' ); ?><strong>X</strong>
					</a>
					<p>
						<b><?php echo esc_html( MPHB()->settings()->license()->getProductName() ); ?></b><br/>
						<?php
						$licensePageUrl = add_query_arg(
							array(
								'page' => MPHB()->getSettingsMenuPage()->getName(),
								'tab'  => 'license',
							),
							admin_url( 'admin.php' )
						);

						// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
						printf( __( "Your License Key is not active. Please, <a href='%s'>activate your License Key</a> to get plugin updates", 'motopress-hotel-booking' ), esc_url( $licensePageUrl ) );
						?>
					</p>
				</div>
				<script type="text/javascript">
					(function( $ ) {
						var dismissBtn = $( '#mphb-dismiss-license-notice' );
						dismissBtn.one( 'click', function() {
							$.ajax( {
								<?php // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
								url: '<?php echo admin_url( 'admin-ajax.php' ); ?>',
								type: 'POST',
								dataType: 'json',
								data: {
									<?php // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
									action: '<?php echo self::ACTION_DISMISS; ?>',
									<?php // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
									mphb_nonce: '<?php echo wp_create_nonce( self::ACTION_DISMISS ); ?>',
								},
								success: function( data ) {
									if ( !data.hasOwnProperty( 'success' ) ) {
										return;
									}
									if ( data.success ) {
										dismissBtn.closest( 'div.error' ).remove();
									} else {
										dismissBtn.closest( 'div.error' ).append( data.data.message );
									}
								}
							} );

						} );
					})( jQuery );
				</script>
				<?php
			}
		}
	}

}
