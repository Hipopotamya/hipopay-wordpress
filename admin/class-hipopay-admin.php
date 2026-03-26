<?php
/**
 * Admin Panel Manager
 *
 * @package Hipopay_Payment_Gateway
 * @subpackage Hipopay_Payment_Gateway/admin
 */

if (!defined('ABSPATH')) {
    exit;
}

class Hipopay_Admin
{

    public function __construct()
    {
        add_action('admin_menu', array($this, 'add_plugin_admin_menu'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_styles'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_scripts'));
        add_action('wp_ajax_hipopay_test_connection', array($this, 'ajax_test_connection'));
    }

    /**
     * Admin CSS Ekle
     */
    public function enqueue_styles($hook)
    {
        if (strpos($hook, 'hipopay') === false) {
            return;
        }
        wp_enqueue_style('hipopay-admin-css', HIPOPAY_PLUGIN_URL . 'admin/css/hipopay-admin.css', array(), HIPOPAY_VERSION, 'all');
    }

    /**
     * Admin JS Ekle
     */
    public function enqueue_scripts($hook)
    {
        if (strpos($hook, 'hipopay') === false) {
            return;
        }

        // Overview sayfasında Chart.js önce yüklenmeli; hipopay-admin-js buna bağımlı olacak
        $admin_js_deps = array('jquery');
        if ('toplevel_page_hipopay-overview' === $hook) {
            wp_enqueue_script(
                'hipopay-chartjs',
                'https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js',
                array(),
                '4.4.0',
                true
            );
            $admin_js_deps[] = 'hipopay-chartjs';
        }

        wp_enqueue_script('hipopay-admin-js', HIPOPAY_PLUGIN_URL . 'admin/js/hipopay-admin.js', $admin_js_deps, HIPOPAY_VERSION, true);
        wp_localize_script('hipopay-admin-js', 'hipopay_admin_ajax', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('hipopay_admin_nonce')
        ));
    }

    /**
     * Menü Ekle
     */
    public function add_plugin_admin_menu()
    {
        // Ana Menü
        add_menu_page(
            'HipoPAY',
            'HipoPAY',
            'manage_options',
            'hipopay-overview',
            array($this, 'display_overview_page'),
            'dashicons-money-alt',
            56
        );

        // İşlemler Alt Menüsü
        add_submenu_page(
            'hipopay-overview',
            __('İşlemler', 'hipopay-payment-gateway'),
            __('İşlemler', 'hipopay-payment-gateway'),
            'manage_options',
            'hipopay-transactions',
            array($this, 'display_transactions_page')
        );

        // Shortcode Ayarları Alt Menüsü
        add_submenu_page(
            'hipopay-overview',
            __('Shortcode Ayarları', 'hipopay-payment-gateway'),
            __('Shortcode Ayarları', 'hipopay-payment-gateway'),
            'manage_options',
            'hipopay-settings',
            array($this, 'display_settings_page')
        );

        // WooCommerce Ayarları — doğrudan WC checkout sayfasına yönlendirir
        add_submenu_page(
            'hipopay-overview',
            __('WooCommerce Ayarları', 'hipopay-payment-gateway'),
            __('WooCommerce Ayarları', 'hipopay-payment-gateway'),
            'manage_options',
            'hipopay-wc-redirect',
            array($this, 'redirect_to_wc_settings')
        );
    }

    /**
     * WooCommerce HipoPAY ayar sayfasına yönlendir
     */
    public function redirect_to_wc_settings()
    {
        wp_redirect(admin_url('admin.php?page=wc-settings&tab=checkout&section=hipopay'));
        exit;
    }

    /**
     * Overview Sayfası Görüntüle
     */
    public function display_overview_page()
    {
        require_once HIPOPAY_PLUGIN_DIR . 'admin/partials/overview-page.php';
    }

    /**
     * İşlemler Sayfası Görüntüle
     */
    public function display_transactions_page()
    {
        require_once HIPOPAY_PLUGIN_DIR . 'admin/partials/transactions-page.php';
    }

    /**
     * Ayarlar Sayfası Görüntüle
     */
    public function display_settings_page()
    {
        require_once HIPOPAY_PLUGIN_DIR . 'admin/partials/settings-page.php';
    }

    /**
     * AJAX: API Bağlantı Testi
     */
    public function ajax_test_connection()
    {
        check_ajax_referer('hipopay_admin_nonce', 'nonce');

        $api_key = isset($_POST['api_key']) ? sanitize_text_field(wp_unslash($_POST['api_key'])) : '';
        $api_secret = isset($_POST['api_secret']) ? sanitize_text_field(wp_unslash($_POST['api_secret'])) : '';

        if (empty($api_key) || empty($api_secret)) {
            wp_send_json_error(array('message' => __('Lütfen API anahtarlarını kontrol edin.', 'hipopay-payment-gateway')));
        }

        $api = new Hipopay_API($api_key, $api_secret);

        $params = array(
            'user_id' => get_current_user_id() ? get_current_user_id() : 1,
            'email' => get_option('admin_email'),
            'username' => wp_get_current_user()->display_name,
            'product_name' => 'Connection Test',
            'price' => 1.00,
            'reference_id' => 'TEST-' . uniqid(),
            'commission_type' => 1,
        );

        $response = $api->create_payment_session($params);

        if (isset($response['success']) && $response['success']) {
            wp_send_json_success(array('message' => __('API bağlantısı başarılı! Test işlemi oluşturuldu.', 'hipopay-payment-gateway')));
        }
        else {
            $msg = isset($response['message']) ? $response['message'] : __('Bilinmeyen hata.', 'hipopay-payment-gateway');
            wp_send_json_error(array('message' => __('API bağlantısı başarısız: ', 'hipopay-payment-gateway') . $msg));
        }
    }
}

// Init Admin UI
if (is_admin()) {
    new Hipopay_Admin();
}
