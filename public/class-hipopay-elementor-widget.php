<?php
/**
 * Elementor Widget — HipoPAY Ödeme / Bağış Formu
 *
 * @package Hipopay_Payment_Gateway
 * @subpackage Hipopay_Payment_Gateway/public
 */

if (!defined('ABSPATH')) {
    exit;
}

class Hipopay_Elementor_Widget extends \Elementor\Widget_Base
{

    public function get_name()
    {
        return 'hipopay_form_widget';
    }

    public function get_title()
    {
        return __('HipoPAY Ödeme Formu', 'hipopay-payment-gateway');
    }

    public function get_icon()
    {
        return 'eicon-form-horizontal';
    }

    public function get_categories()
    {
        return array('general');
    }

    public function get_keywords()
    {
        return array('hipopay', 'ödeme', 'bağış', 'payment', 'form');
    }

    protected function register_controls()
    {

        /* ── İçerik Bölümü ── */
        $this->start_controls_section(
            'section_content',
            array(
            'label' => __('Form Ayarları', 'hipopay-payment-gateway'),
            'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
        )
        );

        $this->add_control(
            'form_type',
            array(
            'label' => __('Form Tipi', 'hipopay-payment-gateway'),
            'type' => \Elementor\Controls_Manager::SELECT,
            'default' => 'hipopay_form',
            'options' => array(
                'hipopay_form' => __('Sabit Tutarlı Ödeme Formu', 'hipopay-payment-gateway'),
                'hipopay_donate' => __('Değişken Tutarlı Bağış Formu', 'hipopay-payment-gateway'),
            ),
        )
        );

        $this->add_control(
            'product_name',
            array(
            'label' => __('Ürün / Hizmet Adı', 'hipopay-payment-gateway'),
            'type' => \Elementor\Controls_Manager::TEXT,
            'default' => __('Ödeme İşlemi', 'hipopay-payment-gateway'),
            'placeholder' => __('150 Silk, VIP Üyelik, vb.', 'hipopay-payment-gateway'),
        )
        );

        $this->add_control(
            'price',
            array(
            'label' => __('Tutar (₺)', 'hipopay-payment-gateway'),
            'type' => \Elementor\Controls_Manager::TEXT,
            'default' => '100',
            'placeholder' => '99.99',
            'description' => __('Yalnızca "Sabit Tutarlı Ödeme Formu" seçildiğinde geçerlidir.', 'hipopay-payment-gateway'),
            'condition' => array(
                'form_type' => 'hipopay_form',
            ),
        )
        );

        $this->add_control(
            'button_text',
            array(
            'label' => __('Buton Metni', 'hipopay-payment-gateway'),
            'type' => \Elementor\Controls_Manager::TEXT,
            'default' => __('Ödeme Yap', 'hipopay-payment-gateway'),
        )
        );

        $this->end_controls_section();
    }

    protected function render()
    {
        $settings = $this->get_settings_for_display();
        $form_type = !empty($settings['form_type']) ? $settings['form_type'] : 'hipopay_form';
        $product_name = !empty($settings['product_name']) ? $settings['product_name'] : __('Ödeme İşlemi', 'hipopay-payment-gateway');
        $button_text = !empty($settings['button_text']) ? $settings['button_text'] : __('Ödeme Yap', 'hipopay-payment-gateway');

        if ('hipopay_form' === $form_type) {
            $price = !empty($settings['price']) ? $settings['price'] : '0';

            $shortcode = sprintf(
                '[hipopay_form product_name="%s" price="%s" button_text="%s"]',
                esc_attr($product_name),
                esc_attr($price),
                esc_attr($button_text)
            );
        }
        else {
            $shortcode = sprintf(
                '[hipopay_donate product_name="%s" button_text="%s"]',
                esc_attr($product_name),
                esc_attr($button_text)
            );
        }

        echo do_shortcode($shortcode);
    }

    protected function content_template()
    {
        // Elementor editör önizlemesi için JS şablonu
?>
        <div style="padding:16px; border:2px dashed #ccc; text-align:center; color:#888; font-size:13px;">
            <strong>HipoPAY {{ settings.form_type === 'hipopay_donate' ? '<?php esc_html_e('Bağış', 'hipopay-payment-gateway'); ?>' : '<?php esc_html_e('Ödeme', 'hipopay-payment-gateway'); ?>' }} <?php esc_html_e('Formu', 'hipopay-payment-gateway'); ?></strong><br>
            <span>{{ settings.product_name }}</span>
            <# if ( settings.form_type === 'hipopay_form' ) { #>
            <br><span>₺{{ settings.price }}</span>
            <# } #>
        </div>
        <?php
    }
}
