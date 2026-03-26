<?php
/**
 * Shortcodes
 *
 * @package Hipopay_Payment_Gateway
 * @subpackage Hipopay_Payment_Gateway/public
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Hipopay_Shortcodes {

    public function __construct() {
        add_shortcode( 'hipopay_form', array( $this, 'render_form' ) );
        add_shortcode( 'hipopay_donate', array( $this, 'render_donate' ) );
        add_action( 'wp_ajax_hipopay_process_form', array( $this, 'process_form' ) );
        add_action( 'wp_ajax_nopriv_hipopay_process_form', array( $this, 'process_form' ) );
    }

    /**
     * Oturum açmış kullanıcı bilgilerini döner.
     *
     * @return array { name: string, email: string, logged_in: bool }
     */
    private function get_prefill_data() {
        $current_user = wp_get_current_user();

        if ( ! $current_user || ! $current_user->ID ) {
            return array( 'name' => '', 'email' => '', 'logged_in' => false );
        }

        $name = trim( $current_user->first_name . ' ' . $current_user->last_name );
        if ( empty( $name ) ) {
            $name = $current_user->display_name;
        }

        return array(
            'name'      => $name,
            'email'     => $current_user->user_email,
            'logged_in' => true,
        );
    }

    /**
     * Sabit Tutarlı Ödeme Formu [hipopay_form product_name="Test" price="100"]
     */
    public function render_form( $atts ) {
        $atts = shortcode_atts( array(
            'product_name' => __( 'Ödeme İşlemi', 'hipopay-payment-gateway' ),
            'price'        => '0',
            'button_text'  => __( 'Ödeme Yap', 'hipopay-payment-gateway' ),
            'class'        => ''
        ), $atts );

        if ( empty( $atts['price'] ) || floatval( $atts['price'] ) <= 0 ) {
            return '<p style="color:#e74c3c;">' . __( 'Hatalı form parametresi: Geçerli bir <code>price</code> (tutar) girilmelidir.', 'hipopay-payment-gateway' ) . '</p>';
        }

        $prefill     = $this->get_prefill_data();
        $is_logged   = $prefill['logged_in'];
        $readonly    = $is_logged ? ' readonly' : '';

        ob_start();
        ?>
        <form class="hipopay-public-form <?php echo esc_attr( $atts['class'] ); ?>" action="<?php echo esc_url( admin_url( 'admin-ajax.php' ) ); ?>" method="POST">
            <?php wp_nonce_field( 'hipopay_public_nonce', 'hipopay_nonce' ); ?>
            <input type="hidden" name="action" value="hipopay_process_form">
            <input type="hidden" name="product_name" value="<?php echo esc_attr( $atts['product_name'] ); ?>">
            <input type="hidden" name="price" value="<?php echo esc_attr( $atts['price'] ); ?>">

            <div class="hipopay-form-summary">
                <strong><?php esc_html_e( 'Ödenecek Tutar:', 'hipopay-payment-gateway' ); ?></strong> <span>₺<?php echo esc_html( number_format( floatval( $atts['price'] ), 2, ',', '.' ) ); ?></span><br>
                <strong><?php esc_html_e( 'İşlem Detayı:', 'hipopay-payment-gateway' ); ?></strong> <span><?php echo esc_html( $atts['product_name'] ); ?></span>
            </div>

            <div class="hipopay-form-inputs">
                <input type="text" name="name" placeholder="<?php esc_attr_e( 'Adınız Soyadınız', 'hipopay-payment-gateway' ); ?>" required
                       value="<?php echo esc_attr( $prefill['name'] ); ?>"<?php echo $readonly; ?>>
                <input type="email" name="email" placeholder="<?php esc_attr_e( 'E-Posta Adresiniz', 'hipopay-payment-gateway' ); ?>" required
                       value="<?php echo esc_attr( $prefill['email'] ); ?>"<?php echo $readonly; ?>>
                <?php if ( $is_logged ) : ?>
                    <span class="hipopay-autofill-notice"><?php esc_html_e( 'Bilgileriniz hesabınızdan otomatik dolduruldu.', 'hipopay-payment-gateway' ); ?></span>
                <?php endif; ?>
            </div>

            <button type="submit" class="hipopay-submit-btn"><?php echo esc_html( $atts['button_text'] ); ?></button>
            <div class="hipopay-form-message"></div>
        </form>
        <?php
        return ob_get_clean();
    }

    /**
     * Bağış / Değişken Tutarlı Form [hipopay_donate product_name="Bağış"]
     */
    public function render_donate( $atts ) {
        $atts = shortcode_atts( array(
            'product_name' => __( 'Bağış', 'hipopay-payment-gateway' ),
            'button_text'  => __( 'Bağış Yap', 'hipopay-payment-gateway' ),
            'class'        => ''
        ), $atts );

        $prefill   = $this->get_prefill_data();
        $is_logged = $prefill['logged_in'];
        $readonly  = $is_logged ? ' readonly' : '';

        ob_start();
        ?>
        <form class="hipopay-public-form <?php echo esc_attr( $atts['class'] ); ?>" action="<?php echo esc_url( admin_url( 'admin-ajax.php' ) ); ?>" method="POST">
            <?php wp_nonce_field( 'hipopay_public_nonce', 'hipopay_nonce' ); ?>
            <input type="hidden" name="action" value="hipopay_process_form">
            <input type="hidden" name="product_name" value="<?php echo esc_attr( $atts['product_name'] ); ?>">

            <div class="hipopay-form-inputs">
                <input type="number" name="price" placeholder="<?php esc_attr_e( 'Tutar Giriniz (₺)', 'hipopay-payment-gateway' ); ?>" required step="0.01" min="1">
                <input type="text" name="name" placeholder="<?php esc_attr_e( 'Adınız Soyadınız', 'hipopay-payment-gateway' ); ?>" required
                       value="<?php echo esc_attr( $prefill['name'] ); ?>"<?php echo $readonly; ?>>
                <input type="email" name="email" placeholder="<?php esc_attr_e( 'E-Posta Adresiniz', 'hipopay-payment-gateway' ); ?>" required
                       value="<?php echo esc_attr( $prefill['email'] ); ?>"<?php echo $readonly; ?>>
                <?php if ( $is_logged ) : ?>
                    <span class="hipopay-autofill-notice"><?php esc_html_e( 'Bilgileriniz hesabınızdan otomatik dolduruldu.', 'hipopay-payment-gateway' ); ?></span>
                <?php endif; ?>
            </div>

            <button type="submit" class="hipopay-submit-btn"><?php echo esc_html( $atts['button_text'] ); ?></button>
            <div class="hipopay-form-message"></div>
        </form>
        <?php
        return ob_get_clean();
    }

    /**
     * Form Process AJAX
     */
    public function process_form() {
        if ( ! isset( $_POST['hipopay_nonce'] ) || ! wp_verify_nonce( $_POST['hipopay_nonce'], 'hipopay_public_nonce' ) ) {
            wp_send_json_error( __( 'Güvenlik doğrulaması başarısız oldu.', 'hipopay-payment-gateway' ) );
            return;
        }

        $price        = isset( $_POST['price'] ) ? floatval( wp_unslash( $_POST['price'] ) ) : 0;
        $product_name = isset( $_POST['product_name'] ) ? sanitize_text_field( wp_unslash( $_POST['product_name'] ) ) : '';
        $name         = isset( $_POST['name'] ) ? sanitize_text_field( wp_unslash( $_POST['name'] ) ) : '';
        $email        = isset( $_POST['email'] ) ? sanitize_email( wp_unslash( $_POST['email'] ) ) : '';

        if ( $price <= 0 || empty( $name ) || empty( $email ) ) {
            wp_send_json_error( __( 'Lütfen bilgileri eksiksiz girin.', 'hipopay-payment-gateway' ) );
            return;
        }

        // Ayarları topla
        $settings   = get_option( 'woocommerce_hipopay_settings', array() );
        $api_key    = isset( $settings['api_key'] ) ? $settings['api_key'] : '';
        $api_secret = isset( $settings['api_secret'] ) ? $settings['api_secret'] : '';

        // API İsteki
        $api          = new Hipopay_API( $api_key, $api_secret );
        $reference_id = 'HP-' . uniqid();

        $params = array(
            'user_id'         => get_current_user_id() ? get_current_user_id() : 1,
            'email'           => $email,
            'username'        => $name,
            'product_name'    => $product_name,
            'price'           => $price,
            'reference_id'    => $reference_id,
            'commission_type' => 1
        );

        $response = $api->create_payment_session( $params );

        if ( ! isset( $response['success'] ) || ! $response['success'] ) {
            wp_send_json_error( isset( $response['message'] ) ? $response['message'] : __( 'Sistemsel bir hata oluştu.', 'hipopay-payment-gateway' ) );
        }

        // DB'ye kaydet
        Hipopay_Transaction::create( array(
            'reference_id'    => $reference_id,
            'token'           => $response['data']['token'],
            'payment_url'     => $response['data']['payment_url'],
            'user_id'         => $params['user_id'],
            'email'           => $params['email'],
            'username'        => $params['username'],
            'product_name'    => $params['product_name'],
            'amount'          => $params['price'],
            'commission_type' => $params['commission_type'],
            'status'          => 'pending',
            'ip_address'      => $api->get_client_ip(),
        ) );

        wp_send_json_success( array(
            'redirect' => $response['data']['payment_url']
        ) );
    }
}
