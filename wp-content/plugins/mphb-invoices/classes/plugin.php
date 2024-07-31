<?php

namespace MPHB\Addons\Invoice;

class Plugin
{
    /** @var self */
    protected static $instance = null;

    // Single components
    /** @var \MPHB\Addons\Invoice\Admin\SettingsTab */
    protected $settingsTab = null;
    /** @var \MPHB\Addons\Invoice\Update\PluginUpdater */
    protected $pluginUpdater = null;
    /** @var \MPHB\Addons\Invoice\PDF\PDFHelper */
    protected $pdf = null;
    /** @var \MPHB\Addons\Invoice\Email\TagsProcessor */
    protected $emailTags = null;
    // Containers
    /** @var \MPHB\Addons\Invoice\Containers\ApisContainer */
    protected $apisContainer = null;
    /** @var \MPHB\Addons\Invoice\Containers\ApisContainer */

    // Other fields
    protected $pluginHeaders = [];

    public function __construct()
    {
        add_action('plugins_loaded', [$this, 'load']);
        add_action('init', [$this, 'init']);

        $this->emailTags = new Email\TagsProcessor();
    }

    public function load()
    {
        if (!class_exists('HotelBookingPlugin')) {
            return;
        }

     

        $this->apisContainer         = new Containers\ApisContainer();
        $this->pdf                   = new PDF\PDFHelper();
        add_action('init', [$this, 'loadTranslations']);

        if (wp_doing_ajax()) {

        } else {
            $this->settingsTab   = new Admin\SettingsTab();
            $this->pluginUpdater = new Update\PluginUpdater();
        }

        self::upgrade();
    }

    public static function upgrade()
    {
        if (!self::getPluginDbVersion() || version_compare(self::getPluginDbVersion(), mphbinvoice()->pluginVersion(), '<')) {
            UsersAndRoles\Capabilities::setup();
            self::setPluginDbVersion();
        }
    }

    public static function getPluginDbVersion()
    {
        return get_option('mphb_invoice_db_version');
    }

    public static function setPluginDbVersion()
    {
        update_option('mphb_invoice_db_version', mphbinvoice()->pluginVersion());
    }

    public function init()
    {
        if (!class_exists('HotelBookingPlugin')) {
            return;
        }

    }

    public function loadTranslations()
    {
        $pluginDir = plugin_basename(PLUGIN_DIR); // "mphb-invoices" or renamed name
        load_plugin_textdomain('mphb-invoices', false, $pluginDir . '/languages');
    }




    /**
     * @return \MPHB\Addons\Invoice\Containers\ApisContainer
     */
    public function api()
    {
        return $this->apisContainer;
    }

    /**
     * @return \MPHB\Addons\Invoice\PDF\PDFHelper
     */
    public function pdf()
    {
        return $this->pdf;
    }

    /**
     * @return string
     */
    public function pluginUri()
    {
        $headers = $this->pluginHeaders();
        return $headers['PluginURI'];
    }

    /**
     * @return string
     */
    public function pluginVersion()
    {
        $headers = $this->pluginHeaders();
        return $headers['Version'];
    }

    /**
     * @return string
     */
    public function pluginAuthor()
    {
        $headers = $this->pluginHeaders();
        return $headers['Author'];
    }

    /**
     * @return string[]
     */
    public function pluginHeaders()
    {
        if (empty($this->pluginHeaders)) {
            if (!function_exists('get_plugin_data')) {
                require_once ABSPATH . 'wp-admin/includes/plugin.php';
            }

            $headers = get_plugin_data(PLUGIN_FILE, false, false);
            $headers = array_merge([
                'PluginURI' => 'https://motopress.com/products/hotel-booking-invoices/',
                'Version'   => '1.0',
                'Author'    => 'MotoPress'
            ], $headers);

            $this->pluginHeaders = $headers;
        }

        return $this->pluginHeaders;
    }

    public function capabilities()
    {
        return new UsersAndRoles\Capabilities();
    }

    public static function activate()
    {
        UsersAndRoles\Capabilities::setup();
    }

    /**
     * @return static
     */
    public static function getInstance()
    {
        if (is_null(static::$instance)) {
            static::$instance = new static();
        }

        return static::$instance;
    }
}

register_activation_hook(PLUGIN_FILE, array('MPHB\Addons\Invoice\Plugin', 'activate'));
