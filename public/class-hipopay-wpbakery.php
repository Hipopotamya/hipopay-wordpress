<?php
/**
 * WPBakery Page Builder (Visual Composer) Element Tanımları
 *
 * Bu dosya yalnızca WPBakery yüklüyse ve vc_map() fonksiyonu mevcutsa çalışır.
 *
 * @package Hipopay_Payment_Gateway
 * @subpackage Hipopay_Payment_Gateway/public
 */

if (!defined('ABSPATH')) {
    exit;
}

if (!function_exists('vc_map')) {
    return;
}

/**
 * Element 1: HipoPAY Sabit Tutarlı Ödeme Formu
 * Shortcode: [hipopay_form]
 */
vc_map(array(
    'name' => __('HipoPAY Ödeme Formu', 'hipopay-payment-gateway'),
    'base' => 'hipopay_form',
    'description' => __('Sabit tutarlı ödeme formu ekler.', 'hipopay-payment-gateway'),
    'category' => __('HipoPAY', 'hipopay-payment-gateway'),
    'icon' => HIPOPAY_PLUGIN_URL . 'admin/images/hipopay-logo.png',
    'params' => array(
            array(
            'type' => 'textfield',
            'heading' => __('Ürün / Hizmet Adı', 'hipopay-payment-gateway'),
            'param_name' => 'product_name',
            'value' => __('Ödeme İşlemi', 'hipopay-payment-gateway'),
            'description' => __('Ödeme formunda ve işlem kaydında görünecek ürün adı.', 'hipopay-payment-gateway'),
            'admin_label' => true,
        ),
            array(
            'type' => 'textfield',
            'heading' => __('Tutar (₺)', 'hipopay-payment-gateway'),
            'param_name' => 'price',
            'value' => '',
            'description' => __('Sabit ödeme tutarı. Örn: 150 veya 99.99', 'hipopay-payment-gateway'),
            'admin_label' => true,
        ),
            array(
            'type' => 'textfield',
            'heading' => __('Buton Metni', 'hipopay-payment-gateway'),
            'param_name' => 'button_text',
            'value' => __('Ödeme Yap', 'hipopay-payment-gateway'),
            'description' => __('Gönder butonunda görünecek metin.', 'hipopay-payment-gateway'),
        ),
            array(
            'type' => 'textfield',
            'heading' => __('Ekstra CSS Sınıfı', 'hipopay-payment-gateway'),
            'param_name' => 'class',
            'value' => '',
            'description' => __('Forma eklenecek özel CSS sınıfı (isteğe bağlı).', 'hipopay-payment-gateway'),
        ),
    ),
));

/**
 * Element 2: HipoPAY Değişken Tutarlı Bağış Formu
 * Shortcode: [hipopay_donate]
 */
vc_map(array(
    'name' => __('HipoPAY Bağış Formu', 'hipopay-payment-gateway'),
    'base' => 'hipopay_donate',
    'description' => __('Kullanıcının kendi tutarını girdiği bağış / ödeme formu.', 'hipopay-payment-gateway'),
    'category' => __('HipoPAY', 'hipopay-payment-gateway'),
    'icon' => HIPOPAY_PLUGIN_URL . 'admin/images/hipopay-logo.png',
    'params' => array(
            array(
            'type' => 'textfield',
            'heading' => __('Ürün / Bağış Adı', 'hipopay-payment-gateway'),
            'param_name' => 'product_name',
            'value' => __('Bağış', 'hipopay-payment-gateway'),
            'description' => __('İşlem kaydında görünecek bağış/ürün adı.', 'hipopay-payment-gateway'),
            'admin_label' => true,
        ),
            array(
            'type' => 'textfield',
            'heading' => __('Buton Metni', 'hipopay-payment-gateway'),
            'param_name' => 'button_text',
            'value' => __('Bağış Yap', 'hipopay-payment-gateway'),
            'description' => __('Gönder butonunda görünecek metin.', 'hipopay-payment-gateway'),
        ),
            array(
            'type' => 'textfield',
            'heading' => __('Ekstra CSS Sınıfı', 'hipopay-payment-gateway'),
            'param_name' => 'class',
            'value' => '',
            'description' => __('Forma eklenecek özel CSS sınıfı (isteğe bağlı).', 'hipopay-payment-gateway'),
        ),
    ),
));
