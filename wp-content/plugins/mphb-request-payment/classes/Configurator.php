<?php

namespace MPHB\Addons\RequestPayment;

class Configurator
{
    const NONCE_NAME = 'nonce';

    const ACTION_INSTALL = 'install';
    const ACTION_SKIP    = 'skip-installation';

    public function __construct()
    {
        add_action('admin_notices', array($this, 'displayNotice'));
        add_action('init', array($this, 'handleAction')); // Install required items or skip the step
    }

    public function displayNotice()
    {
        if ($this->isPassed() || !$this->userCanInstall()) {
            return;
        }

        $installUrl = wp_nonce_url(add_query_arg('payment-requests', self::ACTION_INSTALL), self::ACTION_INSTALL, self::NONCE_NAME);
        $skipUrl    = wp_nonce_url(add_query_arg('payment-requests', self::ACTION_SKIP), self::ACTION_SKIP, self::NONCE_NAME);

        echo '<div class="updated">';
            echo '<p><strong>' . esc_html__('Hotel Booking Payment Request plugin.', 'mphb-request-payment') . '</strong></p>';
            echo '<p>' . esc_html__('This plugin requires a separate Checkout Page to handle payment requests. Press "Install Page" button to create this page. Dismiss this notice if you already installed it.', 'mphb-request-payment') . '</p>';
            echo '<p>';
                echo '<a href="' . esc_url($installUrl) . '" class="button-primary">' . esc_html__('Install Page', 'mphb-request-payment') . '</a>';
                echo '&nbsp;';
                echo '<a href="' . esc_url($skipUrl) . '" class="button-secondary">' . esc_html__('Skip', 'mphb-request-payment') . '</a>';
            echo '</p>';
        echo '</div>';
    }

    protected function isPassed()
    {
        return (bool)get_option('mphbrp_configured', false);
    }

    protected function userCanInstall()
    {
        return (current_user_can('manage_options') && current_user_can('publish_pages'));
    }

    public function handleAction()
    {
        if (!isset($_GET['payment-requests']) || $this->isPassed()) {
            return;
        }

        $action = $_GET['payment-requests'];

        if (!$this->nonceValid($action)) {
            return;
        }

        switch ($action) {
            case self::ACTION_INSTALL: $this->install(); break;
            case self::ACTION_SKIP: $this->markPassed(); break;
        }
    }

    public function nonceValid($action)
    {
        if (!isset($_GET[self::NONCE_NAME])) {
            return false;
        }

        switch ($action) {
            case self::ACTION_INSTALL: return wp_verify_nonce($_GET[self::NONCE_NAME], self::ACTION_INSTALL); break;
            case self::ACTION_SKIP: return wp_verify_nonce($_GET[self::NONCE_NAME], self::ACTION_SKIP); break;
        }

        return false;
    }

    protected function install()
    {
        $this->createCheckoutPage();

        $this->markPassed();
    }

    protected function createCheckoutPage()
    {
        $title   = esc_html__('Payment Request', 'mphb-request-payment');
        $content = MPHBRP()->checkoutShortcode()->generateShortcode();

        $pageId = $this->createPage($title, $content);

        if ($pageId > 0) {
            Settings::setCheckoutPageId($pageId);
        }
    }

    protected function createPage($title, $content = '')
    {
        global $user_ID;

        $pageId = wp_insert_post(array(
            'post_type'    => 'page',
            'post_title'   => $title,
            'post_content' => $content,
            'post_status'  => 'publish',
            'post_parent'  => 0,
            'post_author'  => $user_ID
        ));

        return is_wp_error($pageId) ? 0 : $pageId;
    }

    protected function markPassed()
    {
        // Make the option autoloadable, we'll need it every time the page loading
        update_option('mphbrp_configured', true, 'yes');
    }
}
