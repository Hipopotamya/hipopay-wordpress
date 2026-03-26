<?php
/**
 * Define the internationalization functionality.
 *
 * @package Hipopay_Payment_Gateway
 */

class Hipopay_i18n {
    public function load_plugin_textdomain() {
        load_plugin_textdomain(
            'hipopay-payment-gateway',
            false,
            dirname( dirname( plugin_basename( __FILE__ ) ) ) . '/languages/'
        );
    }
}
