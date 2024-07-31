<?php

namespace MPHB\Notifier;

/**
 * @since 1.0
 */
class Plugin {

	/** @var self */
	private static $instance = null;

	/** @var \MPHB\Notifier\Settings */
	private $settings = null;

	/** @var \MPHB\Notifier\Containers\ApiContainer */
	private $apiContainer = null;

	/** @var \MPHB\Notifier\Containers\CptContainer */
	private $cptContainer = null;

	/** @var \MPHB\Notifier\Containers\RepositoriesContainer */
	private $repositoriesContainer = null;

	/** @var \MPHB\Notifier\Containers\ServicesContainer */
	private $servicesContainer = null;

	/** @var \MPHB\Notifier\Crons\SendNotificationsCron */
	private $notificationsCron = null;

	/** @var \MPHB\Notifier\UsersAndRoles\Capabilities */
	private $capabilities;


	public function setup() {

		new Emails\CustomTags();

		// Load on priority 9 - Hotel Booking uses "plugins_loaded" (10) to init
		// some of its modules
		add_action( 'plugins_loaded', array( $this, 'load' ), 9 );
	}

	public static function upgrade() {

		if ( ! self::getPluginDbVersion() || version_compare( self::getPluginDbVersion(), PLUGIN_VERSION, '<' ) ) {

			UsersAndRoles\Capabilities::setup();
			self::setPluginDbVersion();
		}
	}

	public static function setPluginDbVersion() {
		update_option( 'mphb_notifier_db_version', PLUGIN_VERSION );
	}

	public static function getPluginDbVersion() {
		return get_option( 'mphb_notifier_db_version' );
	}

	/**
	 * Callback for action "plugins_loaded".
	 */
	public function load() {

		if ( ! class_exists( 'HotelBookingPlugin' ) ) {
			return;
		}

		self::upgrade();

		$this->settings = new Settings();

		$this->apiContainer          = new Containers\ApiContainer();
		$this->cptContainer          = new Containers\CptContainer();
		$this->repositoriesContainer = new Containers\RepositoriesContainer();
		$this->servicesContainer     = new Containers\ServicesContainer();

		add_filter(
			'cron_schedules',
			function( $schedules ) {

				$schedules['mphb_send_notifications_interval'] = array(
					'interval' => 6 * HOUR_IN_SECONDS,
					'display'  => esc_html__( 'Interval for sending notifications', 'mphb-notifier' ),
				);

				return $schedules;
			}
		);

		$this->notificationsCron = new Crons\SendNotificationsCron( 'send_notifications', 'mphb_send_notifications_interval' );
		$this->notificationsCron->schedule();

		add_action(
			'plugins_loaded',
			function() {

				if ( ! wp_doing_ajax() ) {

					new Update\PluginUpdater();
					new Listeners\BookingsUpdate();

					if ( is_admin() ) {

						new Admin\SettingsPage();

						new Admin\MetaBoxes\NoticesMetaBox(
							'mphb_notification_notices',
							__( 'Notification Notices', 'mphb-notifier' ),
							mphb()->postTypes()->roomType()->getPostType(),
							'normal'
						);

						new Admin\MetaBoxes\TestMetaBox(
							'mphb_notification_test',
							// translators: "Test" as a verb
							__( 'Test Notification Now', 'mphb-notifier' ),
							\MPHB\Notifier\Plugin::getInstance()->postTypes()->notification()->getPostType(),
							'side'
						);

						new Admin\MetaBoxes\SendNotificationMetabox();
					}
				}
			},
			11 // Hotel Booking uses "plugins_loaded" with priority 10 so we want to be loaded after it
		);

		// Instantiate and register custom post type "Notification"
		$this->cptContainer->notification();

		// Start background process
		$this->servicesContainer->sendNotifications();

		$this->capabilities = new UsersAndRoles\Capabilities();
	}

	/**
	 * @return \MPHB\Notifier\Settings
	 */
	public function settings() {
		return $this->settings;
	}

	/**
	 * @return \MPHB\Notifier\Containers\ApiContainer
	 */
	public function api() {
		return $this->apiContainer;
	}

	/**
	 * @return \MPHB\Notifier\Containers\CptContainer
	 */
	public function postTypes() {
		return $this->cptContainer;
	}

	/**
	 * @return \MPHB\Notifier\Containers\RepositoriesContainer
	 */
	public function repositories() {
		return $this->repositoriesContainer;
	}

	/**
	 * @return \MPHB\Notifier\Containers\ServicesContainer
	 */
	public function services() {
		return $this->servicesContainer;
	}

	/**
	 * @return \MPHB\Notifier\Crons\SendNotificationsCron
	 */
	public function cron() {
		return $this->notificationsCron;
	}

	public function getCapabilities() {
		return new UsersAndRoles\Capabilities();
	}

	/**
	 * @return string
	 */
	public function pluginStoreUri() {
		return PLUGIN_STORE_URI;
	}

	/**
	 * @return string
	 */
	public function pluginVersion() {
		return PLUGIN_VERSION;
	}

	/**
	 * @return string
	 */
	public function pluginAuthor() {
		return PLUGIN_AUTHOR;
	}

	/**
	 * @return static
	 */
	public static function getInstance() {

		if ( is_null( static::$instance ) ) {
			static::$instance = new static();
		}

		return static::$instance;
	}

	public static function activate() {
		UsersAndRoles\Capabilities::setup();
	}
}

register_activation_hook( PLUGIN_FILE, array( 'MPHB\Notifier\Plugin', 'activate' ) );
