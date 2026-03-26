<?php
/**
 * HipoPAY REST API Endpoints
 *
 * @package Hipopay_Payment_Gateway
 * @subpackage Hipopay_Payment_Gateway/includes
 */

if (!defined('ABSPATH')) {
    exit;
}

class Hipopay_REST_API
{

    public function register_routes()
    {
        // IPN Endpoint: /wp-json/hipopay/v1/ipn
        register_rest_route('hipopay/v1', '/ipn', array(
            'methods' => 'GET, POST', // Test için GET ve asıl veri için POST kabul ediyoruz
            'callback' => array($this, 'handle_ipn'),
            'permission_callback' => '__return_true', // IPN herkes tarafından erişilebilir (doğrulama class içinde)
        ));
    }

    public function handle_ipn(WP_REST_Request $request)
    {
        // Eğer kullanıcı tarayıcıdan test için giriyorsa (GET) ona çalıştığına dair mesaj göster
        if ($request->get_method() === 'GET') {
            return new WP_REST_Response(array(
                'status' => 'success',
                'message' => 'HipoPAY IPN Endpoint çalışıyor. Ödeme bildirimleri için bu adresi POST isteği olarak kullanabilirsiniz.'
            ), 200);
        }

        $ipn_handler = new Hipopay_IPN_Handler();
        return $ipn_handler->handle_request($request);
    }
}
