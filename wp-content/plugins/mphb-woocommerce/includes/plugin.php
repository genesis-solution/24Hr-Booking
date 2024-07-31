<?php

namespace MPHBW;

class Plugin {

	/**
	 *
	 * @var \MPHBW\Plugin
	 */
	private static $instance = null;

	/**
	 *
	 * @var string
	 */
	private static $filepath;

    /**
     * @var string
     * @since 1.0.5
     */
    private static $dirpath = '';

	/**
	 *
	 * @var Settings\SettingsRegistry
	 */
	private $settings;

	/**
	 *
	 * @var PluginData
	 */
	private $pluginData;

	/**
	 *
	 * @var Dependencies
	 */
	private $dependencies;

	private function __construct(){
		// Do nothing.
	}

	/**
	 *
	 * @param string $filepath
	 */
	public static function setBaseFilepath( $filepath ){
		self::$filepath = $filepath;
        self::$dirpath = plugin_dir_path( $filepath );
	}

	public static function getInstance(){
		if ( !isset( self::$instance ) ) {
			self::$instance = new self();
			self::$instance->afterConstruct();
		}
		return self::$instance;
	}

	public function afterConstruct(){

		$this->pluginData	 = new PluginData( self::$filepath );
		$this->settings		 = new Settings\SettingsRegistry();
		$this->dependencies	 = new Dependencies();
		new AutoUpdater();

		add_action( 'plugins_loaded', array( $this, 'loadTextdomain' ) );
		add_action( 'plugins_loaded', array( $this, 'onLoad' ), 9 );

        add_filter( 'mphb_get_template_part', array( $this, 'addTemplatePath' ), 10, 2 );

		add_filter('woocommerce_prevent_admin_access', array($this, 'access'));
	}

    /**
     * @since 1.0.5
     */
    public function onLoad(){
        if ( $this->dependencies->check() ) {
            // Init payment method
            new \MPHBW\WoocommerceGateway();

            // Init emails
            $noRenewalTemplater = new \MPHB\Emails\Templaters\EmailTemplater();
            $noRenewalTemplater->setTagGroups( array( 'booking' => true, 'payment' => true ) );

            $noRenewalEmail = new Admin\Emails\NoBookingRenewalEmail( array( 'id' => 'admin_no_booking_renewal' ), $noRenewalTemplater );

            MPHB()->emails()->addEmail( $noRenewalEmail );
        }
    }

    /**
     * @param string $template
     * @param string $slug
     * @return string
     *
     * @since 1.0.5
     */
    public function addTemplatePath( $template, $slug ){
        if ( empty( $template ) && file_exists( $this->pathTo( "templates/{$slug}.php" ) ) ) {
            $template = $this->pathTo( "templates/{$slug}.php" );
        }

        return $template;
    }

    /**
     * @param string $relativePath Relative path to the file.
     * @return string Absolute path to the file.
     *
     * @since 1.0.5
     */
    public function pathTo( $relativePath ){
        return self::$dirpath . $relativePath;
    }

	/**
	 *
	 * @return Settings\SettingsRegistry
	 */
	public function getSettings(){
		return $this->settings;
	}

	/**
	 *
	 * @return Dependencies
	 */
	function getDependencies(){
		return $this->dependencies;
	}

	/**
	 *
	 * @return PluginData
	 */
	public function getPluginData(){
		return $this->pluginData;
	}

	public function loadTextDomain(){

		$slug = $this->pluginData->getSlug();

		$locale = mphbw_is_wp_version( '4.7', '>=' ) ? get_user_locale() : get_locale();

		$locale = apply_filters( 'plugin_locale', $locale, $slug );

		// wp-content/languages/mphb-woocommerce/mphb-woocommerce-{lang}_{country}.mo
		$customerMoFile = sprintf( '%1$s/%2$s/%2$s-%3$s.mo', WP_LANG_DIR, $slug, $locale );

		load_textdomain( $slug, $customerMoFile );

		load_plugin_textdomain( $slug, false, $slug . '/languages' );
	}

	public function access()
	{
		if (current_user_can('mphb_view_calendar')) {
			return false;
		}
	}

}
