<?php
/**
 * Fired when the plugin is uninstalled.
 *
 * @link       https://www.hipopotamya.com
 * @since      1.0.0
 *
 * @package    Hipopay_Payment_Gateway
 */

// If uninstall not called from WordPress, then exit.
if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

global $wpdb;

// Delete plugin options
$wpdb->query("DELETE FROM {$wpdb->options} WHERE option_name LIKE 'hipopay_%'");

// Drop custom transactions table
$table_name = $wpdb->prefix . 'hipopay_transactions';
$wpdb->query("DROP TABLE IF EXISTS {$table_name}");

// Clear any cached data that has been removed
wp_cache_flush();
