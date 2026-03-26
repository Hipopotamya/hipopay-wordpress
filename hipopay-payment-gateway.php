<?php
/**
 * Plugin Name: HipoPAY Payment Gateway
 * Plugin URI: https://www.hipopotamya.com
 * Description: HipoPAY güçlü, güvenli ve hızlı ödeme altyapısı ile WooCommerce e-ticaret sitelerinizden kolayca ödeme almanızı sağlar. Shortcode desteği ile WooCommerce olmadan da kullanılabilir.
 * Version: 1.0.0
 * Author: Hipopotamya
 * Author URI: https://www.hipopotamya.com
 * Text Domain: hipopay-payment-gateway
 * Domain Path: /languages
 * Requires at least: 5.8
 * Requires PHP: 7.4
 * License: GPLv2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * WC requires at least: 5.0
 * WC tested up to: 8.5
 *
 * @package Hipopay_Payment_Gateway
 */

// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}

/**
 * Check if the current environment meets the plugin requirements.
 *
 * @return bool
 */
function hipopay_check_requirements()
{
    global $wp_version;
    if (version_compare(PHP_VERSION, '7.4', '<') || version_compare($wp_version, '5.8', '<')) {
        return false;
    }
    return true;
}

/**
 * Show a notice if requirements are not met.
 */
function hipopay_requirements_notice()
{
    echo '<div class="notice notice-error"><p>' . esc_html__('HipoPAY Payment Gateway eklentisi için en az PHP 7.4 ve WordPress 5.8 gereklidir. Lütfen sisteminizi güncelleyin.', 'hipopay-payment-gateway') . '</p></div>';
}

// Stop execution if requirements are not met.
if (!hipopay_check_requirements()) {
    add_action('admin_notices', 'hipopay_requirements_notice');
    return;
}

/**
 * Currently plugin version.
 */
define('HIPOPAY_VERSION', '1.0.0');
define('HIPOPAY_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('HIPOPAY_PLUGIN_URL', plugin_dir_url(__FILE__));
define('HIPOPAY_DB_VERSION', '1.0.0');

/**
 * The code that runs during plugin activation.
 */
function activate_hipopay_payment_gateway()
{
    require_once plugin_dir_path(__FILE__) . 'includes/class-hipopay-activator.php';
    Hipopay_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 */
function deactivate_hipopay_payment_gateway()
{
    require_once plugin_dir_path(__FILE__) . 'includes/class-hipopay-deactivator.php';
    Hipopay_Deactivator::deactivate();
}

register_activation_hook(__FILE__, 'activate_hipopay_payment_gateway');
register_deactivation_hook(__FILE__, 'deactivate_hipopay_payment_gateway');

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path(__FILE__) . 'includes/class-hipopay-payment-gateway.php';

/**
 * Begins execution of the plugin.
 */
function run_hipopay_payment_gateway()
{
    $plugin = new Hipopay_Payment_Gateway();
    $plugin->run();
}

run_hipopay_payment_gateway();
