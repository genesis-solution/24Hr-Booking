<?php

namespace MPHB\CheckoutFields;

/**
 * @since 1.0
 */
class Plugin {

	/** @var static */
	protected static $instance = null;

	/** @var \MPHB\CheckoutFields\Admin\EddLicenseApi */
	private $eddLicenseApi = null;

	/** @var \MPHB\CheckoutFields\PostTypes\CheckoutFieldCPT */
	private $checkoutFieldsPostType = null;

	/** @var \MPHB\CheckoutFields\Repositories\CheckoutFieldRepository */
	private $checkoutFieldRepository = null;

	/** @var MPHB\CheckoutFields\Admin\PluginLifecycleHandler */
	private $pluginLifecycleHandler = null;

	/** @var \MPHB\CheckoutFields\Settings */
	protected $settings = null;


	// prevent cloning of singleton
	public function __clone() {}
	public function __wakeup() {}

	public static function getInstance(): Plugin {

		if ( is_null( static::$instance ) ) {
			static::$instance = new static();
		}

		return static::$instance;
	}

	private function __construct() {

		$this->settings      = new Settings();
		$this->eddLicenseApi = new Admin\EddLicenseApi();

		$this->loadCustomPostType();

		load_plugin_textdomain(
			'mphb-checkout-fields',
			false,
			plugin_basename( PLUGIN_DIR ) . '/languages'
		);

		$this->pluginLifecycleHandler = new Admin\PluginLifecycleHandler( PLUGIN_FILE );

		add_action(
			'plugins_loaded',
			function() {

				$this->loadPlugin();
			},
			9 // Hotel Booking uses "plugins_loaded" with priority 10 so we want to be loaded before it
		);
	}

	private function loadCustomPostType() {

		if ( null == $this->checkoutFieldsPostType && class_exists( 'MPHB\PostTypes\EditableCPT' ) ) {

			$this->checkoutFieldsPostType = new PostTypes\CheckoutFieldCPT();

			$persistence                   = new \MPHB\Persistences\CPTPersistence( $this->checkoutFieldsPostType->getPostType() );
			$this->checkoutFieldRepository = new Repositories\CheckoutFieldRepository( $persistence );
		}
	}

	private function loadPlugin() {

		if ( ! $this->pluginLifecycleHandler->isWPEnvironmentSuitedForPlugin() ) {
			return;
		}

		$this->loadCustomPostType();

		require_once PLUGIN_DIR . 'includes/polyfills.php';

		new CheckoutFieldsHandler();
		new EmailsHandler();
		new ReportsHandler();

		if ( wp_doing_ajax() ) {

			new Listeners\Ajax();

		} elseif ( ! wp_doing_cron() ) {

			new Admin\SettingsPage();
			new Views\ViewActions();
		}
	}

	public function settings(): Settings {
		return $this->settings;
	}

	public function getEddLicenseApi(): Admin\EddLicenseApi {
		return $this->eddLicenseApi;
	}

	public function getCheckoutFieldsPostType(): PostTypes\CheckoutFieldCPT {
		return $this->checkoutFieldsPostType;
	}

	public function getCheckoutFieldRepository(): Repositories\CheckoutFieldRepository {
		return $this->checkoutFieldRepository;
	}

	public function capabilities(): UsersAndRoles\Capabilities {
		return new UsersAndRoles\Capabilities();
	}

	public static function getPluginAuthorName(): string {
		return AUTHOR;
	}

	public static function getPluginVersion(): string {
		return VERSION;
	}

	public static function getPluginSourceServerUrl(): string {
		return STORE_URI;
	}

	public function isEDDLicenseEnabled(): bool {
		return (bool) apply_filters( 'mphb_checkout_fields_use_edd_license', true );
	}
}
