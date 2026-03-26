<?php
/**
 * HipoPAY IPN Handler
 *
 * @package Hipopay_Payment_Gateway
 * @subpackage Hipopay_Payment_Gateway/includes
 */

if (!defined('ABSPATH')) {
    exit;
}

class Hipopay_IPN_Handler
{

    public function handle_request(WP_REST_Request $request)
    {
        // Gelen JSON verisi
        $params = $request->get_json_params();

        if (empty($params)) {
            return new WP_REST_Response(array('status' => 'error', 'message' => 'No data received'), 400);
        }

        // Ayarlardan API anahtarlarını al
        $settings = get_option('woocommerce_hipopay_settings', array());
        $api_key = isset($settings['api_key']) ? $settings['api_key'] : '';
        $api_secret = isset($settings['api_secret']) ? $settings['api_secret'] : '';

        // API sınıfını başlat
        $api = new Hipopay_API($api_key, $api_secret);

        // Hash Doğrulaması
        if (!$api->verify_ipn($params)) {
            return new WP_REST_Response(array('status' => 'error', 'message' => 'Invalid hash signature'), 403);
        }

        $transaction_id = isset($params['transaction_id']) ? sanitize_text_field($params['transaction_id']) : '';
        $status = isset($params['status']) ? sanitize_text_field($params['status']) : '';

        $reference_id = isset($params['reference_id']) ? sanitize_text_field($params['reference_id']) : '';
        if (empty($reference_id) && isset($params['order_id'])) {
            $reference_id = sanitize_text_field($params['order_id']);
        }
        if (empty($reference_id) && isset($params['custom_data'])) {
            $reference_id = sanitize_text_field($params['custom_data']);
        }

        global $wpdb;
        $table = Hipopay_Transaction::get_table_name();
        $transaction = null;

        // 1. Önce Referans/Sipariş Numarasına göre tam eşleşme arayalım
        if (!empty($reference_id)) {
            $transaction = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$table} WHERE reference_id = %s OR order_id = %s", $reference_id, $reference_id));
        }

        // 2. Referans numarası dönmediyse (HipoPAY sadece standart log dönüyorsa),
        // İmza (Hash) doğrulaması başarıyla geçtiği için Hash onaylı bu E-Posta'nın en son açtığı 'pending' işlemi buluyoruz.
        if (!$transaction && !empty($params['email'])) {
            $email = sanitize_email($params['email']);
            $transaction = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$table} WHERE email = %s AND status = 'pending' ORDER BY id DESC LIMIT 1", $email));
        }

        // Transaction bulunduysa işleme başla
        if ($transaction) {
            $order_id = absint($transaction->order_id);
            $new_status = ($status === 'success' || $status === '100') ? 'completed' : 'failed';

            // Eklentinin kendi transactions tablosunu güncelle
            $wpdb->update(
                $table,
                array(
                'transaction_id' => $transaction_id,
                'status' => $new_status,
                'ipn_data' => wp_json_encode($params),
            ),
                array('id' => $transaction->id)
            );

            // WooCommerce Siparişini güncelle
            if ($order_id > 0 && function_exists('wc_get_order')) {
                $order = wc_get_order($order_id);
                if ($order) {
                    if ($new_status === 'completed') {
                        $order->payment_complete($transaction_id);
                        $order->add_order_note(sprintf('HipoPAY ödemesi başarıyla alındı. İşlem ID: %s', $transaction_id));
                    }
                    else {
                        $order->update_status('failed', sprintf('HipoPAY ödemesi başarısız oldu. İşlem ID: %s', $transaction_id));
                    }
                }
            }
        }
        else {
            // Eşleşecek hiçbir işlem bulunamadı, logla
            if (function_exists('wc_get_logger')) {
                $logger = wc_get_logger();
                $logger->error('HipoPAY IPN Hatası: Transaction siparişle eşleştirilemedi. Params: ' . wp_json_encode($params), array('source' => 'hipopay'));
            }
        }

        do_action('hipopay_ipn_received', $params);

        // HipoPAY API sisteminin döngüyü kesmesi ve başarılı sayması için gereken yegane metin:
        echo "OK";
        exit; // JSON çıktısı üretmesini engelleyip salt 'OK' vererek süreci durduruyoruz
    }
}
