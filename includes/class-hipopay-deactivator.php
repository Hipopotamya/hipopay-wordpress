<?php
/**
 * Fired during plugin deactivation
 *
 * @package Hipopay_Payment_Gateway
 * @subpackage Hipopay_Payment_Gateway/includes
 */

class Hipopay_Deactivator {

    /**
     * Eklenti deaktive edildiğinde çalışacak kodlar.
     */
    public static function deactivate() {
        // Varsa planlanmış cron görevlerini temizle
        wp_clear_scheduled_hook( 'hipopay_cron_hook' );

        // Rewrite kurallarını temizle
        flush_rewrite_rules();
    }
}
