<?php
/**
 * Settings Page View
 *
 * @package Hipopay_Payment_Gateway
 * @subpackage Hipopay_Payment_Gateway/admin/partials
 */

if (!defined('ABSPATH')) {
    exit;
}

// Ayarları kaydet
if (isset($_POST['hipopay_save_settings'])) {
    check_admin_referer('hipopay_settings_nonce');

    $api_key = isset($_POST['api_key']) ? sanitize_text_field(wp_unslash($_POST['api_key'])) : '';
    $api_secret = isset($_POST['api_secret']) ? sanitize_text_field(wp_unslash($_POST['api_secret'])) : '';

    $settings = get_option('woocommerce_hipopay_settings', array());
    $settings['api_key'] = $api_key;
    $settings['api_secret'] = $api_secret;

    update_option('woocommerce_hipopay_settings', $settings);

    echo '<div class="notice notice-success is-dismissible"><p><strong>' . esc_html__('Ayarlar kaydedildi.', 'hipopay-payment-gateway') . '</strong></p></div>';
}

$settings = get_option('woocommerce_hipopay_settings', array());
$api_key = isset($settings['api_key']) ? $settings['api_key'] : '';
$api_secret = isset($settings['api_secret']) ? $settings['api_secret'] : '';

$wc_settings_url = admin_url('admin.php?page=wc-settings&tab=checkout&section=hipopay');
$ipn_url = rest_url('hipopay/v1/ipn');
?>

<div class="wrap">
    <h1><?php esc_html_e('HipoPAY — Shortcode / Bağımsız Form Ayarları', 'hipopay-payment-gateway'); ?></h1>

    <!-- WooCommerce yönlendirme bandı -->
    <div class="notice notice-info" style="display:flex; align-items:center; gap:16px; padding:12px 16px; flex-wrap:wrap;">
        <strong style="white-space:nowrap;"><?php esc_html_e('WooCommerce entegrasyonu için:', 'hipopay-payment-gateway'); ?></strong>
        <a href="<?php echo esc_url($wc_settings_url); ?>" class="button button-primary">
            <?php esc_html_e('WooCommerce &rarr; HipoPAY Ayarlarını Aç', 'hipopay-payment-gateway'); ?>
        </a>
        <span style="color:#666; font-size:12px;">
            <?php esc_html_e('Ödeme başlığı, komisyon tipi, test modu ve debug loglama gibi WooCommerce checkout ayarları buradadır.', 'hipopay-payment-gateway'); ?>
        </span>
    </div>

    <hr>

    <h2><?php esc_html_e('API Kimlik Bilgileri (Shortcode Formları)', 'hipopay-payment-gateway'); ?></h2>
    <p style="color:#555; max-width:640px;">
        <?php echo wp_kses_post(__('Aşağıdaki anahtar bilgileri <strong>[hipopay_form]</strong> ve <strong>[hipopay_donate]</strong> shortcode\'larıyla oluşturulan bağımsız ödeme formları tarafından kullanılır. WooCommerce kurulu olmayan sayfalarda veya özel sayfalarda ödeme almak istiyorsanız bu alanları doldurun.', 'hipopay-payment-gateway')); ?>
    </p>

    <form method="post" action="">
        <?php wp_nonce_field('hipopay_settings_nonce'); ?>
        <table class="form-table">
            <tr valign="top">
                <th scope="row"><?php esc_html_e('API Anahtarı (API Key)', 'hipopay-payment-gateway'); ?></th>
                <td>
                    <input type="text" name="api_key" id="api_key"
                           value="<?php echo esc_attr($api_key); ?>"
                           class="regular-text" autocomplete="off" />
                    <p class="description"><?php esc_html_e('Hipopotamya panelinden aldığınız API Key.', 'hipopay-payment-gateway'); ?></p>
                </td>
            </tr>
            <tr valign="top">
                <th scope="row"><?php esc_html_e('Gizli Anahtar (API Secret)', 'hipopay-payment-gateway'); ?></th>
                <td>
                    <input type="password" name="api_secret" id="api_secret"
                           value="<?php echo esc_attr($api_secret); ?>"
                           class="regular-text" autocomplete="off" />
                    <p class="description"><?php esc_html_e('Hipopotamya panelinden aldığınız API Secret.', 'hipopay-payment-gateway'); ?></p>
                </td>
            </tr>
            <tr valign="top">
                <th scope="row"><?php esc_html_e('Bağlantıyı Test Et', 'hipopay-payment-gateway'); ?></th>
                <td>
                    <button type="button" class="button" id="hipopay-test-connection"><?php esc_html_e('Bağlantıyı Test Et', 'hipopay-payment-gateway'); ?></button>
                    <span id="hipopay-test-result" style="margin-left:10px;"></span>
                </td>
            </tr>
        </table>
        <?php submit_button(__('Ayarları Kaydet', 'hipopay-payment-gateway'), 'primary', 'hipopay_save_settings'); ?>
    </form>

    <hr>

    <!-- IPN / Webhook URL -->
    <h2><?php esc_html_e('IPN / Webhook URL', 'hipopay-payment-gateway'); ?></h2>
    <p style="max-width:640px; color:#555;">
        <?php echo wp_kses_post(__('Aşağıdaki adresi <a href="https://www.hipopotamya.com/merchants/stores" target="_blank">Hipopotamya paneli &rarr; Mağazalarım</a> bölümündeki <strong>Callback URL</strong> alanına yapıştırın. Bu adres olmadan ödemeler WordPress\'e yansımaz.', 'hipopay-payment-gateway')); ?>
    </p>
    <code style="display:inline-block; padding:8px 12px; background:#f5f5f5; border:1px solid #ddd; border-radius:3px; font-size:13px; word-break:break-all;">
        <?php echo esc_url($ipn_url); ?>
    </code>

    <hr>

    <!-- Shortcode Kullanım Rehberi -->
    <h2><?php esc_html_e('Shortcode Kullanım Rehberi', 'hipopay-payment-gateway'); ?></h2>
    <p style="max-width:640px; color:#555;">
        <?php echo wp_kses_post(__('Herhangi bir WordPress sayfasına veya yazısına aşağıdaki kısa kodları ekleyerek ödeme formu oluşturabilirsiniz. WooCommerce kurulu olmasına <strong>gerek yoktur</strong>.', 'hipopay-payment-gateway')); ?>
    </p>

    <h3 style="margin-top:24px;"><?php esc_html_e('Sabit Tutarlı Ödeme Formu', 'hipopay-payment-gateway'); ?> — <code>[hipopay_form]</code></h3>
    <table class="widefat striped" style="max-width:720px;">
        <thead>
            <tr>
                <th style="width:200px;"><?php esc_html_e('Parametre', 'hipopay-payment-gateway'); ?></th>
                <th style="width:120px;"><?php esc_html_e('Zorunlu', 'hipopay-payment-gateway'); ?></th>
                <th><?php esc_html_e('Açıklama', 'hipopay-payment-gateway'); ?></th>
            </tr>
        </thead>
        <tbody>
            <tr><td><code>product_name</code></td><td><?php esc_html_e('Hayır', 'hipopay-payment-gateway'); ?></td><td><?php esc_html_e('İşlem adı. Varsayılan: "Ödeme İşlemi"', 'hipopay-payment-gateway'); ?></td></tr>
            <tr><td><code>price</code></td><td><strong><?php esc_html_e('Evet', 'hipopay-payment-gateway'); ?></strong></td><td><?php esc_html_e('Sabit tutar (₺). Örn: 150 veya 99.99', 'hipopay-payment-gateway'); ?></td></tr>
            <tr><td><code>button_text</code></td><td><?php esc_html_e('Hayır', 'hipopay-payment-gateway'); ?></td><td><?php esc_html_e('Buton metni. Varsayılan: "Ödeme Yap"', 'hipopay-payment-gateway'); ?></td></tr>
            <tr><td><code>class</code></td><td><?php esc_html_e('Hayır', 'hipopay-payment-gateway'); ?></td><td><?php esc_html_e('Forma eklenecek ekstra CSS sınıfı', 'hipopay-payment-gateway'); ?></td></tr>
        </tbody>
    </table>

    <h3 style="margin-top:24px;"><?php esc_html_e('Değişken Tutarlı Bağış Formu', 'hipopay-payment-gateway'); ?> — <code>[hipopay_donate]</code></h3>
    <table class="widefat striped" style="max-width:720px;">
        <thead>
            <tr>
                <th style="width:200px;"><?php esc_html_e('Parametre', 'hipopay-payment-gateway'); ?></th>
                <th style="width:120px;"><?php esc_html_e('Zorunlu', 'hipopay-payment-gateway'); ?></th>
                <th><?php esc_html_e('Açıklama', 'hipopay-payment-gateway'); ?></th>
            </tr>
        </thead>
        <tbody>
            <tr><td><code>product_name</code></td><td><?php esc_html_e('Hayır', 'hipopay-payment-gateway'); ?></td><td><?php esc_html_e('Bağış/işlem adı. Varsayılan: "Bağış"', 'hipopay-payment-gateway'); ?></td></tr>
            <tr><td><code>button_text</code></td><td><?php esc_html_e('Hayır', 'hipopay-payment-gateway'); ?></td><td><?php esc_html_e('Buton metni. Varsayılan: "Bağış Yap"', 'hipopay-payment-gateway'); ?></td></tr>
            <tr><td><code>class</code></td><td><?php esc_html_e('Hayır', 'hipopay-payment-gateway'); ?></td><td><?php esc_html_e('Forma eklenecek ekstra CSS sınıfı', 'hipopay-payment-gateway'); ?></td></tr>
        </tbody>
    </table>

    <h3 style="margin-top:28px;"><?php esc_html_e('Örnek Kullanım Senaryoları', 'hipopay-payment-gateway'); ?></h3>
    <table class="widefat striped" style="max-width:860px;">
        <thead>
            <tr>
                <th style="width:240px;"><?php esc_html_e('Senaryo', 'hipopay-payment-gateway'); ?></th>
                <th><?php esc_html_e('Kullanılacak Shortcode', 'hipopay-payment-gateway'); ?></th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td><?php esc_html_e('Oyun sunucusu — 150₺ sabit E-Pin satışı', 'hipopay-payment-gateway'); ?></td>
                <td><code>[hipopay_form product_name="150 Silk Paketi" price="150" button_text="Satın Al"]</code></td>
            </tr>
            <tr>
                <td><?php esc_html_e('Dernek / Yayıncı — kullanıcı tutar girer bağış kutusu', 'hipopay-payment-gateway'); ?></td>
                <td><code>[hipopay_donate product_name="Kanal Destek Bağışı" button_text="Destek Ol"]</code></td>
            </tr>
            <tr>
                <td><?php esc_html_e('VIP üyelik sayfası — 199₺ sabit', 'hipopay-payment-gateway'); ?></td>
                <td><code>[hipopay_form product_name="VIP Üyelik (1 Ay)" price="199"]</code></td>
            </tr>
            <tr>
                <td><?php esc_html_e('Özel CSS entegrasyonu (tema sınıfı ekle)', 'hipopay-payment-gateway'); ?></td>
                <td><code>[hipopay_form product_name="Premium Paket" price="49.99" class="my-theme-form"]</code></td>
            </tr>
            <tr>
                <td><?php esc_html_e('Etkinlik bilet satışı', 'hipopay-payment-gateway'); ?></td>
                <td><code>[hipopay_form product_name="Lan Party Bileti" price="75" button_text="Bilet Al"]</code></td>
            </tr>
        </tbody>
    </table>

    <div class="notice notice-warning" style="margin-top:20px; max-width:720px;">
        <p>
            <strong><?php esc_html_e('Not:', 'hipopay-payment-gateway'); ?></strong> <?php esc_html_e('Oturum açmış WordPress kullanıcılarında ad ve e-posta alanları hesap bilgilerinden otomatik doldurulur ve salt okunur hale gelir.', 'hipopay-payment-gateway'); ?>
        </p>
    </div>

</div>
