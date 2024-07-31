<?php

if (!function_exists('wp_doing_ajax')) {
    /**
     * @return bool
     *
     * @since 1.0
     * @since WordPress 4.7
     */
    function wp_doing_ajax()
    {
        return apply_filters('wp_doing_ajax', defined('DOING_AJAX') && DOING_AJAX);
    }
}

if (!function_exists('wp_doing_cron')) {
    /**
     * @return bool
     *
     * @since 1.0
     * @since WordPress 4.8
     */
    function wp_doing_cron()
    {
        return apply_filters('wp_doing_cron', defined('DOING_CRON') && DOING_CRON);
    }
}
