<?php

namespace InstagramFeed\Vendor\Smashballoon\Framework\Utilities;

/** @internal */
class UsageTracking
{
    const LIB_VERSION = '1.0.0';
    const API_BASE_URL = 'https://usage.smashballoon.com/v1/';
    const TRANSIENT_KEY = 'sb_%s_usage_tracking_last_send';
    const TRANSIENT_EXPIRATION = 604800;
    /**
     * @param array $tracked_settings
     * @param array $default_settings
     *
     * @return array
     */
    public static function tracked_settings_to_booleans($tracked_settings, $default_settings, $feed_settings)
    {
        $settings = [];
        foreach ($tracked_settings as $setting) {
            if (isset($default_settings[$setting]) && isset($feed_settings[$setting])) {
                $settings[$setting] = $feed_settings[$setting] !== $default_settings[$setting] ? 1 : 0;
            }
        }
        return $settings;
    }
    public static function tracked_settings_to_strings($tracked_settings, $feed_settings)
    {
        $settings = [];
        foreach ($tracked_settings as $setting) {
            if (isset($feed_settings[$setting])) {
                $settings[$setting] = $feed_settings[$setting];
            }
        }
        return $settings;
    }
    public static function send_usage_update($data, $plugin_slug)
    {
        $plugin_name = self::get_plugin_name_from_slug($plugin_slug);
        if (empty($plugin_name)) {
            return \false;
        }
        $last_send_transient = get_transient(\sprintf(self::TRANSIENT_KEY, $plugin_name));
        // Return if the last send was less than a week ago
        if (\false !== $last_send_transient) {
            return \true;
        }
        // Filter usage tracking data
        $data = apply_filters('sb_usage_tracking_data', $data, $plugin_slug);
        if (!\is_array($data) || empty($data)) {
            return \false;
        }
        if (self::post_data($data)) {
            set_transient(self::get_transient_name($plugin_name), \time(), self::TRANSIENT_EXPIRATION);
            return \true;
        }
        return \false;
    }
    private static function post_data($data)
    {
        $response = wp_remote_post(self::API_BASE_URL . 'checkin/', array('body' => \json_encode($data), 'timeout' => 5, 'blocking' => \true, 'sslverify' => \false, 'headers' => array('Content-Type' => 'application/json; charset=utf-8', 'user-agent' => 'SB/' . self::LIB_VERSION . '; ' . get_bloginfo('url'))));
        if (is_wp_error($response)) {
            return \false;
        }
        $response_code = wp_remote_retrieve_response_code($response);
        if (200 !== $response_code) {
            return \false;
        }
        return \true;
    }
    private static function get_transient_name($plugin_name)
    {
        return \sprintf(self::TRANSIENT_KEY, $plugin_name);
    }
    private static function get_plugin_name_from_slug($slug)
    {
        switch ($slug) {
            case 'cff':
                return 'facebook';
            case 'ctf':
                return 'twitter';
            case 'sby':
                return 'youtube';
            case 'sbr':
                return 'reviews';
            case 'sbtt':
                return 'tiktok';
            case 'sbi':
                return 'instagram';
        }
        return '';
    }
}
