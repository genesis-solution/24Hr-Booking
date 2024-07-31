<?php

if (!defined('WP_UNINSTALL_PLUGIN')) {
    wp_die('no uninstallation started');
}
global $wpdb;
$wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}wpbe_history");
$wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}wpbe_history_bulk");

