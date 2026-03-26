<?php
/**
 * WooCommerce Payment Gateway Integration
 *
 * @package Hipopay_Payment_Gateway
 * @subpackage Hipopay_Payment_Gateway/woocommerce
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * HipoPAY WooCommerce Gateway
 */
class Hipopay_WC_Gateway extends WC_Payment_Gateway
{

    public $api_key;
    public $api_secret;
    public $commission_type;
    public $debug;

    public function __construct()
    {
        $this->id = 'hipopay';
        $this->icon = apply_filters('hipopay_gateway_icon', HIPOPAY_PLUGIN_URL . 'admin/images/hipopay-logo.png');
        $this->has_fields = false;
        $this->method_title = 'HipoPAY';
        $this->method_description = __('HipoPAY üzerinden güvenli ve hızlı ödeme almanızı sağlar. E-pin satışları ve top-up işlemleri için idealdir.', 'hipopay-payment-gateway');

        // Load settings
        $this->init_form_fields();
        $this->init_settings();

        // Varsayılan değişkenleri ayarla
        $this->title = $this->get_option('title');
        $this->description = $this->get_option('description');
        $this->enabled = $this->get_option('enabled');
        $this->api_key = $this->get_option('api_key');
        $this->api_secret = $this->get_option('api_secret');
        $this->commission_type = $this->get_option('commission_type', '1');
        $this->debug = 'yes' === $this->get_option('debug', 'no');

        // Ayarları kaydetme işlemi için
        add_action('woocommerce_update_options_payment_gateways_' . $this->id, array($this, 'process_admin_options'));

        // Return URL Callback
        add_action('woocommerce_api_hipopay_return', array($this, 'check_return_url'));

        // Para birimi uyarısı
        if (is_admin()) {
            add_action('admin_notices', array($this, 'currency_admin_notice'));
        }
    }

    /**
     * Admin Ayar Alanları
     */
    public function init_form_fields()
    {
        $this->form_fields = array(
            'enabled' => array(
                'title' => __('Aktifleştir/Devre Dışı Bırak', 'hipopay-payment-gateway'),
                'label' => __('HipoPAY Ödeme Yöntemini Aktifleştir', 'hipopay-payment-gateway'),
                'type' => 'checkbox',
                'description' => '',
                'default' => 'no'
            ),
            'title' => array(
                'title' => __('Başlık', 'hipopay-payment-gateway'),
                'type' => 'text',
                'description' => __('Ödeme sayfasında müşterinin göreceği başlık.', 'hipopay-payment-gateway'),
                'default' => __('HipoPAY ile Öde', 'hipopay-payment-gateway'),
                'desc_tip' => true,
            ),
            'description' => array(
                'title' => __('Açıklama', 'hipopay-payment-gateway'),
                'type' => 'textarea',
                'description' => __('Ödeme yöntemi seçildiğinde müşteriye gösterilecek açıklama.', 'hipopay-payment-gateway'),
                'default' => __('Kredi kartı ile güvenle ödeme yapabilirsiniz.', 'hipopay-payment-gateway'),
            ),
            'api_endpoint_info' => array(
                'title' => __('API Bilgileri', 'hipopay-payment-gateway'),
                'type' => 'title',
                'description' => __('Hipopotamya (HipoPAY) üzerinden aldığınız API anahtarlarını girin.<br><br><strong>Önemli:</strong> Aşağıdaki Webhook URL adresini kopyalayarak Hipopotamya müşteri panelinizdeki (<a href="https://www.hipopotamya.com/merchants/stores" target="_blank">Mağazalarım</a>) <strong>Callback URL</strong> kısmına yapıştırmanız gerekmektedir. Aksi takdirde ödemeler WordPress sisteminize yansımaz!<br><br><strong>Webhook (Callback) URL:</strong> <code>', 'hipopay-payment-gateway') . rest_url('hipopay/v1/ipn') . '</code>',
            ),
            'api_key' => array(
                'title' => __('API Key', 'hipopay-payment-gateway'),
                'type' => 'text',
                'description' => __('HipoPAY Merchant API Key', 'hipopay-payment-gateway'),
                'default' => '',
                'desc_tip' => true,
            ),
            'api_secret' => array(
                'title' => __('API Secret', 'hipopay-payment-gateway'),
                'type' => 'password',
                'description' => __('HipoPAY Merchant API Secret', 'hipopay-payment-gateway'),
                'default' => '',
                'desc_tip' => true,
            ),
            'commission_type' => array(
                'title' => __('Komisyon Tipi', 'hipopay-payment-gateway'),
                'type' => 'select',
                'class' => 'wc-enhanced-select',
                'description' => __('Komisyon yükünün kim tarafından karşılanacağını seçin.', 'hipopay-payment-gateway'),
                'default' => '1',
                'desc_tip' => true,
                'options' => array(
                    '1' => __('Tip 1 - Bayi Komisyonunu Üstlen (Assume Dealer Commission)', 'hipopay-payment-gateway'),
                    '2' => __('Tip 2 - Tüm Komisyonu Üstlen (Assume All Commission)', 'hipopay-payment-gateway'),
                    '3' => __('Tip 3 - Komisyonu Tamamen Yansıt (Reflect Commission Fully)', 'hipopay-payment-gateway'),
                )
            ),
            'advanced_settings' => array(
                'title' => __('Gelişmiş Ayarlar', 'hipopay-payment-gateway'),
                'type' => 'title',
            ),
            'test_mode' => array(
                'title' => __('Test Modu', 'hipopay-payment-gateway'),
                'label' => __('Test(Sandbox) Modunu Aktif Et', 'hipopay-payment-gateway'),
                'type' => 'checkbox',
                'description' => __('Geliştirme aşamasında test modunu aktif tutun.', 'hipopay-payment-gateway'),
                'default' => 'no',
                'desc_tip' => true,
            ),
            'debug' => array(
                'title' => __('Hata Ayıklama (Debug)', 'hipopay-payment-gateway'),
                'label' => __('Loglamayı Etkinleştir', 'hipopay-payment-gateway'),
                'type' => 'checkbox',
                'description' => __('IPN isteklerini ve hataları WooCommerce Loglarına yazar.', 'hipopay-payment-gateway'),
                'default' => 'no',
                'desc_tip' => true,
            )
        );
    }

    /**
     * Eklenti ayarları aktif olup olmadığını kontrol et
     */
    public function is_available()
    {
        if (!parent::is_available()) {
            return false;
        }

        if (empty($this->api_key) || empty($this->api_secret)) {
            return false;
        }

        // Yalnızca TRY para birimi desteklenir
        if (get_woocommerce_currency() !== 'TRY') {
            return false;
        }

        return true;
    }

    /**
     * TRY dışı para birimi uyarısı
     */
    public function currency_admin_notice()
    {
        if (get_woocommerce_currency() === 'TRY') {
            return;
        }

        if (!current_user_can('manage_woocommerce')) {
            return;
        }

        $settings_url = admin_url('admin.php?page=wc-settings&tab=general');

        echo '<div class="notice notice-error"><p>'
            . '<strong>HipoPAY:</strong> WooCommerce para birimi <strong>'
            . esc_html(get_woocommerce_currency())
            . ' (' . esc_html(get_woocommerce_currency_symbol()) . ')'
            . '</strong> olarak ayarlandı. HipoPAY yalnızca <strong>TRY (₺ Türk Lirası)</strong> destekler. '
            . 'Ödeme yöntemi devre dışı bırakıldı. '
            . '<a href="' . esc_url($settings_url) . '">WooCommerce &rarr; Para Birimini Değiştir &rarr;</a>'
            . '</p></div>';
    }

    /**
     * Ödeme İşlemi - Checkout sırasında çalışır
     */
    public function process_payment($order_id)
    {
        $order = wc_get_order($order_id);

        if (!$order) {
            return;
        }

        $api = new Hipopay_API($this->api_key, $this->api_secret);

        // WooCommerce'den Sipariş ve Ürün Bilgilerini Toplama
        $total_amount = $order->get_total();
        $reference_id = 'WC-' . $order->get_order_number() . '-' . uniqid();

        $items = $order->get_items();
        $product_name = array();
        foreach ($items as $item) {
            $product_name[] = $item->get_name() . ' x ' . $item->get_quantity();
        }
        $product_desc = implode(', ', $product_name);
        if (empty($product_desc)) {
            $product_desc = 'WooCommerce Siparişi #' . $order->get_order_number();
        }

        // İstek Parametreleri
        $params = array(
            'user_id' => $order->get_customer_id() > 0 ? $order->get_customer_id() : 1, // Misafir alışverişte varsayılan
            'email' => $order->get_billing_email(),
            'username' => $order->get_billing_first_name() . ' ' . $order->get_billing_last_name(),
            'product_name' => mb_substr($product_desc, 0, 200),
            'price' => $total_amount,
            'reference_id' => $reference_id,
            'commission_type' => intval($this->commission_type)
        );

        $params = apply_filters('hipopay_wc_payment_params', $params, $order);

        $this->log('API Payment Session Istegi Gönderiliyor: ' . print_r($params, true));

        $response = $api->create_payment_session($params);

        if (!isset($response['success']) || !$response['success']) {
            // Hata Durumu
            $error_message = isset($response['message']) ? $response['message'] : __('Ödeme başlatılamadı. Geçersiz yanıt alındı.', 'hipopay-payment-gateway');
            $this->log('API Payment Session Hatası: ' . $error_message);
            wc_add_notice(__('Ödeme sırasında bir hata oluştu: ', 'hipopay-payment-gateway') . $error_message, 'error');
            return array('result' => 'failure');
        }

        $payment_url = isset($response['data']['payment_url']) ? $response['data']['payment_url'] : '';
        $token = isset($response['data']['token']) ? $response['data']['token'] : '';

        if (empty($payment_url)) {
            $this->log('API Payment Session Hatası: Payment URL dönmedi.');
            wc_add_notice(__('Ödeme yönlendirme bağlantısı alınamadı.', 'hipopay-payment-gateway'), 'error');
            return array('result' => 'failure');
        }

        // Veritabanına kaydet
        Hipopay_Transaction::create(array(
            'order_id' => $order_id,
            'reference_id' => $reference_id,
            'token' => $token,
            'payment_url' => $payment_url,
            'user_id' => $params['user_id'],
            'email' => $params['email'],
            'username' => $params['username'],
            'product_name' => $params['product_name'],
            'amount' => $params['price'],
            'commission_type' => $params['commission_type'],
            'status' => 'pending',
            'ip_address' => $api->get_client_ip(),
            'raw_request' => wp_json_encode($params),
            'raw_response' => wp_json_encode($response)
        ));

        $order->update_status('pending', __('HipoPAY ödeme sayfası oluşturuldu, kullanıcı yönlendiriliyor.', 'hipopay-payment-gateway'));

        $this->log('Ödeme başarıyla başlatıldı, URL: ' . $payment_url);

        // Sepeti temizle
        WC()->cart->empty_cart();

        return array(
            'result' => 'success',
            'redirect' => $payment_url
        );
    }

    /**
     * Müşteri Dönüş Callback URL İşleyicisi
     */
    public function check_return_url()
    {
        // Zaten sipariş IPN'den düşeceği için sadece thank you sayfasına yönlendirilir
        if (isset($_GET['order_id'])) {
            $order_id = absint($_GET['order_id']);
            $order = wc_get_order($order_id);
            if ($order) {
                wp_redirect($this->get_return_url($order));
                exit;
            }
        }
        wp_redirect(wc_get_checkout_url());
        exit;
    }

    /**
     * Logger Yardımcısı
     */
    protected function log($message)
    {
        if ($this->debug) {
            if (function_exists('wc_get_logger')) {
                $logger = wc_get_logger();
                $context = array('source' => 'hipopay');
                $logger->info($message, $context);
            }
        }
    }
}
