<?php
/**
 * Core plugin class that ties everything together
 *
 * @package Hipopay_Payment_Gateway
 * @subpackage Hipopay_Payment_Gateway/includes
 */

class Hipopay_Payment_Gateway {

    protected $loader;
    protected $plugin_name;
    protected $version;

    public function __construct() {
        $this->plugin_name = 'hipopay-payment-gateway';
        $this->version = HIPOPAY_VERSION;

        $this->load_dependencies();
        $this->set_locale();
        $this->define_admin_hooks();
        $this->define_public_hooks();
        $this->define_woocommerce_hooks();
    }

    private function load_dependencies() {
        // Core ve Hook yöneticisi
        require_once HIPOPAY_PLUGIN_DIR . 'includes/class-hipopay-loader.php';
        require_once HIPOPAY_PLUGIN_DIR . 'includes/class-hipopay-i18n.php';

        // İletişim Sınıfları
        require_once HIPOPAY_PLUGIN_DIR . 'includes/class-hipopay-api.php';
        require_once HIPOPAY_PLUGIN_DIR . 'includes/class-hipopay-transaction.php';

        // REST API ve IPN
        require_once HIPOPAY_PLUGIN_DIR . 'includes/class-hipopay-rest-api.php';
        require_once HIPOPAY_PLUGIN_DIR . 'includes/class-hipopay-ipn-handler.php';

        $this->loader = new Hipopay_Loader();
    }

    private function set_locale() {
        // Çeviri dosyalarını yüklemek için hook eklenecek
    }

    private function define_admin_hooks() {
        if ( is_admin() ) {
            require_once HIPOPAY_PLUGIN_DIR . 'admin/class-hipopay-admin.php';
        }
    }

    private function define_public_hooks() {
        require_once HIPOPAY_PLUGIN_DIR . 'public/class-hipopay-shortcodes.php';
        new Hipopay_Shortcodes();

        // Elementor widget kaydı
        if ( did_action( 'elementor/loaded' ) || defined( 'ELEMENTOR_VERSION' ) ) {
            add_action( 'elementor/widgets/register', function( $widgets_manager ) {
                require_once HIPOPAY_PLUGIN_DIR . 'public/class-hipopay-elementor-widget.php';
                $widgets_manager->register( new Hipopay_Elementor_Widget() );
            } );
        }

        // WPBakery Page Builder desteği
        add_action( 'vc_before_init', function() {
            if ( function_exists( 'vc_map' ) ) {
                require_once HIPOPAY_PLUGIN_DIR . 'public/class-hipopay-wpbakery.php';
            }
        } );

        // REST API Endpoint kaydı
        $rest_api = new Hipopay_REST_API();
        $this->loader->add_action( 'rest_api_init', $rest_api, 'register_routes' );

        // Public JS
        $this->loader->add_action( 'wp_enqueue_scripts', $this, 'enqueue_public_scripts' );
    }

    public function enqueue_public_scripts() {
        wp_enqueue_style( 'hipopay-public', HIPOPAY_PLUGIN_URL . 'public/css/hipopay-public.css', array(), HIPOPAY_VERSION );
        wp_enqueue_script( 'hipopay-public-js', HIPOPAY_PLUGIN_URL . 'public/js/hipopay-public.js', array('jquery'), HIPOPAY_VERSION, true );
    }

    private function define_woocommerce_hooks() {
        // WooCommerce Gateway kaydı
        $this->loader->add_filter( 'woocommerce_payment_gateways', $this, 'add_wc_gateway' );

        // WC yüklendikten sonra gateway sınıfını dahil et
        $this->loader->add_action( 'plugins_loaded', $this, 'init_wc_gateway', 11 );
    }

    public function init_wc_gateway() {
        if ( class_exists( 'WC_Payment_Gateway' ) ) {
            require_once HIPOPAY_PLUGIN_DIR . 'woocommerce/class-hipopay-wc-gateway.php';
        }
    }

    public function add_wc_gateway( $methods ) {
        if ( class_exists( 'WC_Payment_Gateway' ) ) {
            $methods[] = 'Hipopay_WC_Gateway';
        }
        return $methods;
    }

    public function run() {
        $this->loader->run();
    }

    public function get_plugin_name() {
        return $this->plugin_name;
    }

    public function get_version() {
        return $this->version;
    }
}
