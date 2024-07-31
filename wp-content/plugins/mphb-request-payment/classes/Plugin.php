<?php

namespace MPHB\Addons\RequestPayment;

use MPHB\Addons\RequestPayment\Crons\RequestPaymentsCron;
use MPHB\Addons\RequestPayment\Emails\NewTags;
use MPHB\Addons\RequestPayment\Listeners\PostUpdateListener;
use MPHB\Addons\RequestPayment\Listeners\TransitionsListener;
use MPHB\Addons\RequestPayment\MetaBoxes\RequestPaymentMetaBox;
use MPHB\Addons\RequestPayment\Shortcodes\CheckoutShortcode;
use MPHB\Addons\RequestPayment\Utils\EmailUtils;
use MPHB\Addons\RequestPayment\Utils\SettingsTabUtils;
use MPHB\Addons\RequestPayment\Utils\TemplateUtils;

final class Plugin
{
    /** @var Plugin */
    private static $instance = null;

    /** @var Ajax */
    private $ajax = null;

    /** @var Assets */
    private $assets = null;

    /** @var RequestPaymentsCron */
    private $cron = null;

    /** @var NewTags */
    private $tags = null;

    /** @var CheckoutShortcode */
    private $shortcode = null;

    /** @var string[] */
    private $pluginHeaders = array();

    private function __construct()
    {   
        // Emails are inited on priority 10; run earlier to add our emails
        add_action('plugins_loaded', array($this, 'onLoad'), 9);

        add_action('init', array($this, 'onInit'));
    }

    public function onLoad()
    {
        if (!class_exists('HotelBookingPlugin')) {
            return;
        }

        self::upgrade();

        // Load translations
        add_action('init', array($this, 'loadTranslations'));

        if ($this->isAjax()) {
            $this->ajaxLoad();
        } else {
            $this->regularLoad();
        }
    }

    private function ajaxLoad()
    {
        $this->ajax = new Ajax();

        // Add +1 handler of action "get_billing_fields"
        $this->ajax->redefineActions();
    }

    private function regularLoad()
    {
        $this->assets = new Assets();
        $this->shortcode = new CheckoutShortcode();

        // Run configurator
        new Configurator();

        // Run auto-updater
        new Updater();

        // Run transitions listener and post update listener
        new TransitionsListener();
        new PostUpdateListener();

        // Add cron to request payments automatically
        $this->cron = new RequestPaymentsCron('request_payments', 'twicedaily');
        $this->cron->schedule(); // Always run and check bookings

        MPHB()->cronManager()->addCron($this->cron);

        // Register new emails
        $this->tags = new NewTags();

        EmailUtils::addRequestPaymentEmail();
        EmailUtils::addRequestPaidEmail();

        // Add more templates and register settings tab
        TemplateUtils::addCustomTemplatesPath();
        SettingsTabUtils::addSettingsTab();

        // Register Request Payment meta box for Edit Booking page
        new RequestPaymentMetaBox('request_payment_link', esc_html__('Payment Request', 'mphb-request-payment'),
            MPHB()->postTypes()->booking()->getPostType(), 'side');
    }

    /**
     *
     * @since 1.1.4
     */
    public function onInit() {

        if (!class_exists('HotelBookingPlugin')) {
            return;
        }

        // Fix for Cron Jobs
        if( null == $this->tags ) {
            $this->tags = new NewTags();
        }
    }

    public static function upgrade()
    {
        if (!self::getPluginDbVersion() || version_compare(self::getPluginDbVersion(), MPHBRP()->getVersion(), '<')) {
            UsersAndRoles\Capabilities::setup();
            self::setPluginDbVersion();
        }
    }

    /**
     * @return Assets
     */
    public function assets()
    {
        return $this->assets;
    }

    /**
     * @return NewTags
     */
    public function tags()
    {
        return $this->tags;
    }

    /**
     * @return CheckoutShortcode
     */
    public function checkoutShortcode()
    {
        return $this->shortcode;
    }

    public function getVersion()
    {
        $headers = $this->getPluginHeaders();
        return $headers['Version'];
    }

    public function getAuthor()
    {
        $headers = $this->getPluginHeaders();
        return $headers['Author'];
    }

    public function getPluginUri()
    {
        $headers = $this->getPluginHeaders();
        return $headers['PluginURI'];
    }

    public function getPluginFile()
    {
        return $this->pathTo(SLUG . '.php');
    }

    public function getPluginHeaders()
    {
        if (empty($this->pluginHeaders)) {
            if (!function_exists('get_plugin_data')) {
                require_once ABSPATH . 'wp-admin/includes/plugin.php';
            }

            $pluginFile = $this->getPluginFile();

            $headers = get_plugin_data($pluginFile, false, false);
            $headers = array_merge(array(
                'Version'   => '1.0',
                'Author'    => 'MotoPress',
                'PluginURI' => 'https://motopress.com/products/hotel-booking-payment-request/'
            ), $headers);

            $this->pluginHeaders = $headers;
        }

        return $this->pluginHeaders;
    }

    public function isAjax()
    {
        return defined('DOING_AJAX') && DOING_AJAX;
    }

    public function pathTo($relativePath)
    {
        return ROOT . $relativePath;
    }

    public function urlTo($relativePath)
    {
        return ROOT_URL . $relativePath;
    }

    public function loadTranslations()
    {
        load_plugin_textdomain('mphb-request-payment', false, SLUG . '/languages');
    }

    public function capabilities()
    {
        return new UsersAndRoles\Capabilities();
    }

    public function onActivate()
    {
        global $wpdb;

        // Add options to autoload set
        $wpdb->query("UPDATE {$wpdb->options} SET autoload = 'yes' WHERE option_name IN ('mphbrp_configured', 'mphbrp_license_key')");

        // Save latest booking ID (activation hook works before "plugins_loaded",
        // so the cron class will have the proper ID)
        $latestBookingId = get_option('mphbrp_last_skipped_booking_id', -1);

        if ($latestBookingId == -1) {
            $latestBookings = get_posts(array(
                'post_type'      => 'mphb_booking',
                'post_status'    => 'any',
                'posts_per_page' => 1,
                'orderby'        => 'ID',
                'order'          => 'DESC'
            ));

            if (!empty($latestBookings)) {
                $latestBooking = reset($latestBookings);
                $latestBookingId = $latestBooking->ID;
            } else {
                $latestBookingId = 0;
            }

            update_option('mphbrp_last_skipped_booking_id', $latestBookingId, 'no');
        }

        UsersAndRoles\Capabilities::setup();
    }

    public function onDeactivate()
    {
        global $wpdb;

        // Remove options from autoload set
        $wpdb->query("UPDATE {$wpdb->options} SET autoload = 'no' WHERE option_name LIKE 'mphbrp_%'");

        // Don't leave the running crons
        if( $this->cron ) {
          $this->cron->unschedule();
        }
    }

    public static function setPluginDbVersion()
    {
        update_option('mphb_request_payment_db_version', MPHBRP()->getVersion());
    }

    public static function getPluginDbVersion()
    {
        return get_option('mphb_request_payment_db_version');
    }

    public static function getInstance()
    {
        if (is_null(static::$instance)) {
            static::$instance = new self();
        }

        return static::$instance;
    }
}
