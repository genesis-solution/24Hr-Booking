<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( '-1' );
}

/** @var bool $is_about_page */
?>
<img class="vc-featured-img" src="<?php echo esc_url( vc_asset_url( 'vc/wpb_introduce_ux_improvements.png' ) ); ?>"/>

<div class="vc-feature-text">
	<h3><?php esc_html_e( 'Instant edit and UX improvements', 'js_composer' ); ?></h3>

	<p><?php esc_html_e( 'Work faster with the updated WPBakery edit forms. See all the changes you make to your elements instantly. You can now enable auto-save functionality in the general settings under WPBakery Page Builder in the WordPress admin dashboard, to experience the enhanced user experience for better content management.', 'js_composer' ); ?></p>
	<ul>
		<li><?php esc_html_e( 'Instant element update and preview', 'js_composer' ); ?></li>
		<li><?php esc_html_e( 'Default value placeholders in Design Options', 'js_composer' ); ?></li>
		<li><?php esc_html_e( 'Enhanced plugin security', 'js_composer' ); ?></li>
	</ul>
	<?php
	$tabs = vc_settings()->getTabs();
	$is_license_tab_access = isset( $tabs['vc-updater'] ) && vc_user_access()->part( 'settings' )->can( 'vc-updater-tab' )->get();
	if ( $is_about_page && ! vc_license()->isActivated() && $is_license_tab_access ) : ?>
		<div class="vc-feature-activation-section">
			<?php $url = 'admin.php?page=vc-updater'; ?>
			<a href="<?php echo esc_attr( is_network_admin() ? network_admin_url( $url ) : admin_url( $url ) ); ?>" class="vc-feature-btn" id="vc_settings-updater-button" data-vc-action="activation"><?php esc_html_e( 'Activate License', 'js_composer' ); ?></a>
			<p class="vc-feature-info-text">
				<?php esc_html_e( 'Direct plugin activation only.', 'js_composer' ); ?>
				<a href="https://wpbakery.com/wpbakery-page-builder-license/?utm_source=wpdashboard&utm_medium=wpb-settings-about-whats-new&utm_content=text" target="_blank" rel="noreferrer noopener"><?php esc_html_e( 'Don\'t have a license?', 'js_composer' ); ?></a>
			</p>
		</div>
	<?php endif; ?>
</div>
