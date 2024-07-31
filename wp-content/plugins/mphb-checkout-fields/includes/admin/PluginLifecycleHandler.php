<?php

namespace MPHB\CheckoutFields\Admin;

use \MPHB\CheckoutFields\Plugin;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


class PluginLifecycleHandler {

	const REQUIRED_HOTEL_BOOKING_PLUGIN_VERSION = '4.3.0';

	const OPTION_NAME_UPGRADED_PLUGIN_VERSION = 'mphbcf_upgraded_version';

	private $pluginMainFilePath = '';

	private $isWPEnvironmentSuitedForPlugin = true;

	public function __construct( string $pluginMainFilePath ) {

		$this->pluginMainFilePath = $pluginMainFilePath;

		add_action(
			'plugins_loaded',
			function() {
				$this->checkIsWPEnvironmentSuitedForPlugin();
			},
			-1
		);

		register_activation_hook(
			$this->pluginMainFilePath,
			function( $isNetworkWide = false ) {
				$this->activatePlugin( $isNetworkWide );
			}
		);

		add_action(
			'mphb_activated',
			function() {
				$this->activatePlugin();
			}
		);

		// add installation for a new site in multisite WordPress
		add_action(
			version_compare( get_bloginfo( 'version' ), '5.1', '>=' ) ? 'wp_initialize_site' : 'wpmu_new_blog',
			/**
			 * @param $blog in case of wp_initialize_site action is WP_Site otherwise int (site id)
			 */
			function( $blog ) {
				$blogId = is_int( $blog ) ? $blog : $blog->blog_id;
				$this->activatePlugin( false, $blogId );
			}
		);

		register_deactivation_hook(
			$this->pluginMainFilePath,
			function() {
				$this->deactivatePlugin();
			}
		);

		register_uninstall_hook( $this->pluginMainFilePath, array( __CLASS__, 'uninstallPlugin' ) );

		// initialize EDD updater
		if ( ! wp_doing_ajax() && ! wp_doing_cron() ) {

			add_action(
				'admin_init',
				function() {

					if ( Plugin::getInstance()->isEDDLicenseEnabled() ) {

						new EddPluginUpdater(
							Plugin::getInstance()->getPluginSourceServerUrl(),
							$this->pluginMainFilePath,
							array(
								'version' => Plugin::getInstance()->getPluginVersion(),
								'license' => Plugin::getInstance()->getEddLicenseApi()->getKey(),
								'item_id' => Plugin::getInstance()->getEddLicenseApi()->getProductId(),
								'author'  => Plugin::getInstance()->getPluginAuthorName(),
							)
						);
					}
				}
			);
		}

		// check and upgrade plugin data if it is needed after plugin update
		add_action(
			'init',
			function() {
				if ( Plugin::getInstance()->settings()->isActivated() && $this->isWPEnvironmentSuitedForPlugin() ) {

					$this->upgradePluginData();
				}
			}
		);

	}

	private function checkIsWPEnvironmentSuitedForPlugin() {

		$isRequiredVersionOfHotelBookingPluginActive = false;

		if ( function_exists( 'mphb_version_at_least' ) ) {

			$isRequiredVersionOfHotelBookingPluginActive = mphb_version_at_least( static::REQUIRED_HOTEL_BOOKING_PLUGIN_VERSION );
		}

		if ( ! $isRequiredVersionOfHotelBookingPluginActive ) {

			$this->isWPEnvironmentSuitedForPlugin = false;

			$this->addErrorAdminNotice(
				sprintf(
					# translators: %1$s is a plugin name, for example 'Hotel Booking' and %2$s it's version, for example '1.2.0'
					__( 'The Hotel Booking Checkout Fields addon requires the %1$s plugin %2$s version or higher. Install and activate it for proper work.', 'mphb-checkout-fields' ),
					'Hotel Booking',
					static::REQUIRED_HOTEL_BOOKING_PLUGIN_VERSION
				)
			);
		}

		return $this->isWPEnvironmentSuitedForPlugin;
	}

	private function addErrorAdminNotice( string $errorText ) {

		add_action(
			'admin_notices',
			function() use ( $errorText ) {
				echo '<div class="notice notice-error">
					<div style="display: flex; align-items: center; gap: 10px; margin: 10px 10px 10px 0;">
						<svg style="overflow: visible;" width="40" height="40" xmlns="http://www.w3.org/2000/svg"><path fill="#d63638" d="M39.375 20c0 10.703-8.675 19.375-19.375 19.375S.625 30.703.625 20C.625 9.303 9.3.625 20 .625S39.375 9.303 39.375 20ZM20 23.906a3.594 3.594 0 1 0 0 7.188 3.594 3.594 0 0 0 0-7.188ZM16.588 10.99l.58 10.625a.937.937 0 0 0 .936.886h3.792c.498 0 .91-.39.936-.886l.58-10.625a.938.938 0 0 0-.936-.989h-4.952a.937.937 0 0 0-.936.989Z"/></svg>
						<p>' . esc_html( $errorText ) . '</p>
					</div></div>';
			}
		);
	}

	/**
	 * @return bool
	 */
	public function isWPEnvironmentSuitedForPlugin() {

		return $this->isWPEnvironmentSuitedForPlugin;
	}

	private function activatePlugin( $isNetworkWide = false, $multisiteBlogId = 0 ) {

		// check environment because plugin was not loaded yet before activation
		if ( ! $this->checkIsWPEnvironmentSuitedForPlugin() ) {

			return;
		}

		if ( is_multisite() && $isNetworkWide ) {

			$sites = get_sites();

			foreach ( $sites as $site ) {

				switch_to_blog( $site->blog_id );

				$this->installPlugin();

				restore_current_blog();
			}
		} elseif ( is_multisite() && 0 < $multisiteBlogId ) {

			if ( ! is_plugin_active_for_network( plugin_basename( $this->pluginMainFilePath ) ) ) {
				return;
			}

				switch_to_blog( $multisiteBlogId );

				$this->installPlugin();

				restore_current_blog();

		} else {

			$this->installPlugin();
		}
	}

	private function installPlugin() {

		if ( Plugin::getInstance()->settings()->isActivated() ) {
			return;
		}

		// add checkout fields to the database
		$defaultFields = mphb_get_default_customer_fields();
		$postType      = Plugin::getInstance()->getCheckoutFieldsPostType()->getPostType();

		// we try to find previously stored fields if it is not a first plugin installation
		// to avoide duplicated fields in the database because of old plugin versions
		$alreadyStoredCheckoutFields = Plugin::getInstance()->getCheckoutFieldRepository()->findAll();

		$order = 1;

		foreach ( $defaultFields as $name => $args ) {

			$storingFieldData = array(
				'post_type'   => $postType,
				'post_title'  => $args['label'],
				'post_status' => 'publish',
				'menu_order'  => $order,
				'post_author' => 0,
				'meta_input'  => array(
					'mphb_cf_name'     => $name,
					'mphb_cf_type'     => $args['type'],
					'mphb_cf_enabled'  => (int) $args['enabled'],
					'mphb_cf_required' => (int) $args['required'],
				),
			);

			if ( ! empty( $alreadyStoredCheckoutFields ) ) {

				foreach ( $alreadyStoredCheckoutFields as $alreadyStoredField ) {

					if ( $name == $alreadyStoredField->name ) {

						$storingFieldData['ID'] = $alreadyStoredField->getId();
						break;
					}
				}
			}

			wp_insert_post( $storingFieldData );

			$order++;
		}

		$this->upgradePluginData();

		Plugin::getInstance()->settings()->setActivated();
	}

	private function upgradePluginData() {

		$upgradedPluginVersion = get_option( static::OPTION_NAME_UPGRADED_PLUGIN_VERSION );

		if ( $upgradedPluginVersion && version_compare( $upgradedPluginVersion, Plugin::getInstance()->getPluginVersion(), '>=' ) ) {

			return;
		}

		try {

			// add plugin's custom roles and capabilities
			\MPHB\CheckoutFields\UsersAndRoles\Capabilities::setup();

			$this->createUploadsFolder();

			update_option( static::OPTION_NAME_UPGRADED_PLUGIN_VERSION, Plugin::getInstance()->getPluginVersion() );

		} catch ( \Throwable $e ) {

			error_log( 'ERROR: ' . $e->getMessage() . PHP_EOL . $e->getTraceAsString() );
			$this->addErrorAdminNotice( $e->getMessage() );
		}
	}

	/**
	 * @throws Exception when uploads directory was not created
	 */
	private function createUploadsFolder() {

		$uploadFolder = wp_upload_dir()['basedir'] . '/' . \MPHB\CheckoutFields\Fields\FileUploadField::SECRET_UPLOADS;

		if ( file_exists( $uploadFolder ) ) {
			return;
		}

		require_once ABSPATH . 'wp-admin/includes/file.php';

		WP_Filesystem();

		if ( wp_mkdir_p( $uploadFolder ) ) {

			$result = copy_dir(
				\MPHB\CheckoutFields\PLUGIN_DIR . 'assets/others/' . \MPHB\CheckoutFields\Fields\FileUploadField::SECRET_UPLOADS,
				$uploadFolder
			);

			if ( true !== $result ) {
				throw new \Exception( __( 'Could not copy .htaccess file to the created uploads directory.', 'mphb-checkout-fields' ) );
			}
		} else {

			throw new \Exception( __( 'Could not create uploads directory.', 'mphb-checkout-fields' ) );
		}
	}

	private function deactivatePlugin() {}

	/**
	 * Do not use any plugin classed here to make sure that any plugin's 
	 * code will not be working during uninstallation.
	 */
	public static function uninstallPlugin() {

		// remove checkout fields posts from the database
		$checkoutFieldsPostsIds = get_posts(
			array(
				'post_type'      => 'mphb_checkout_field',
				'fields'         => 'ids',
				'nopaging'       => 'true',
				'posts_per_page' => -1,
			)
		);

		if ( ! empty( $checkoutFieldsPostsIds ) ) {

			add_filter(
				'pre_delete_post',
				function( $check, \WP_Post $wpPost ) {
					return null;
				},
				99999, // overwrite filter in CheckoutFieldsHandler which prevent default fields deletion
				2
			);

			foreach ( $checkoutFieldsPostsIds as $postId ) {

				wp_delete_post( $postId, true );
			}
		}

		update_option( static::OPTION_NAME_UPGRADED_PLUGIN_VERSION, null );
		update_option( 'mphb_checkout_fields_activated', false, false );
	}
}
