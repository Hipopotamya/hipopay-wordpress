<?php
/**
 * HipoPAY API Integration Class
 *
 * @package Hipopay_Payment_Gateway
 * @subpackage Hipopay_Payment_Gateway/includes
 */

if (!defined('ABSPATH')) {
    exit;
}

class Hipopay_API
{

    protected $api_key;
    protected $api_secret;
    protected $api_url = 'https://www.hipopotamya.com/api/v1/merchants';

    public function __construct($api_key, $api_secret)
    {
        $this->api_key = $api_key;
        $this->api_secret = $api_secret;
    }

    /**
     * Ödeme oturumu oluştur (POST /payment/token)
     */
    public function create_payment_session($params)
    {
        $user_id = isset($params['user_id']) ? $params['user_id'] : 0;
        $email = isset($params['email']) ? $params['email'] : '';
        $username = isset($params['username']) ? $params['username'] : '';
        $product_name = isset($params['product_name']) ? $params['product_name'] : '';

        // Fiyatı float olarak al, 100 ile çarpıp yuvarla ve integer türüne çevir (ör. 10.50 -> 1050)
        $raw_price = isset($params['price']) ? floatval($params['price']) : 0;
        $price = intval(round($raw_price * 100));

        $reference_id = isset($params['reference_id']) ? $params['reference_id'] : '';
        $commission_type = isset($params['commission_type']) ? $params['commission_type'] : 1;

        // Hash: base64_encode(hash_hmac('sha256', userId.email.username.apiKey, apiSecret, true))
        $hash_string = $user_id . $email . $username . $this->api_key;
        $hash = base64_encode(hash_hmac('sha256', $hash_string, $this->api_secret, true));

        $data = array(
            'api_key' => $this->api_key,
            'api_secret' => $this->api_secret,
            'user_id' => $user_id,
            'email' => $email,
            'username' => $username,
            'ip_address' => $this->get_client_ip(),
            'hash' => $hash,
            'pro' => true,
            'product[name]' => $product_name,
            'product[price]' => $price,
            'product[reference_id]' => $reference_id,
            'product[commission_type]' => $commission_type,
        );

        return $this->request('/payment/token', $data);
    }

    /**
     * IPN Callback Hash Doğrulaması
     */
    public function verify_ipn($data)
    {
        if (!isset($data['hash'])) {
            return false;
        }

        $transaction_id = isset($data['transaction_id']) ? $data['transaction_id'] : '';
        $user_id = isset($data['user_id']) ? $data['user_id'] : '';
        $email = isset($data['email']) ? $data['email'] : '';
        $name = isset($data['name']) ? $data['name'] : '';
        $status = isset($data['status']) ? $data['status'] : '';

        // Hash: base64_encode(hash_hmac('sha256', transaction_id.user_id.email.name.status.apiKey, apiSecret, true))
        $hash_string = $transaction_id . $user_id . $email . $name . $status . $this->api_key;
        $expected_hash = base64_encode(hash_hmac('sha256', $hash_string, $this->api_secret, true));

        return hash_equals($expected_hash, $data['hash']);
    }

    /**
     * API İsteği Gönder (wp_remote_post ile)
     */
    protected function request($endpoint, $data)
    {
        $url = $this->api_url . $endpoint;

        $args = array(
            'body' => $data,
            'timeout' => 30,
            'redirection' => 5,
            'blocking' => true,
            'headers' => array(
                'Accept' => 'application/json',
            ),
        );

        $response = wp_remote_post($url, $args);

        if (is_wp_error($response)) {
            return array(
                'success' => false,
                'message' => $response->get_error_message(),
                'data' => array()
            );
        }

        $body = wp_remote_retrieve_body($response);
        $decoded = json_decode($body, true);

        if (!$decoded) {
            return array(
                'success' => false,
                'message' => 'API response is not valid JSON.',
                'raw' => $body
            );
        }

        return $decoded;
    }

    /**
     * İstemci IP Adresini Al (Cloudflare desteği ile)
     */
    public function get_client_ip()
    {
        $ip = '';
        if (isset($_SERVER['HTTP_CF_CONNECTING_IP'])) {
            $ip = sanitize_text_field(wp_unslash($_SERVER['HTTP_CF_CONNECTING_IP']));
        }
        elseif (isset($_SERVER['HTTP_CLIENT_IP'])) {
            $ip = sanitize_text_field(wp_unslash($_SERVER['HTTP_CLIENT_IP']));
        }
        elseif (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ip = sanitize_text_field(wp_unslash($_SERVER['HTTP_X_FORWARDED_FOR']));
        }
        elseif (isset($_SERVER['REMOTE_ADDR'])) {
            $ip = sanitize_text_field(wp_unslash($_SERVER['REMOTE_ADDR']));
        }

        // Birden fazla IP virgülle geldiyse ilkini al
        if (strpos($ip, ',') !== false) {
            $ips = explode(',', $ip);
            $ip = trim($ips[0]);
        }

        return filter_var($ip, FILTER_VALIDATE_IP) ? $ip : '127.0.0.1';
    }
}
