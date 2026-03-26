<?php
/**
 * Fired during plugin activation
 *
 * @package Hipopay_Payment_Gateway
 * @subpackage Hipopay_Payment_Gateway/includes
 */

class Hipopay_Activator {

    /**
     * Eklenti aktifleştirildiğinde çalışacak kodlar.
     * Veritabanı tablolarını oluşturur ve varsayılan ayarları yükler.
     */
    public static function activate() {
        self::create_tables();

        // Rewrite kurallarını temizle (REST API endpoint'leri için)
        flush_rewrite_rules();
    }

    /**
     * Custom veritabanı tablolarını oluştur.
     */
    private static function create_tables() {
        global $wpdb;

        $charset_collate = $wpdb->get_charset_collate();
        $table_name = $wpdb->prefix . 'hipopay_transactions';

        $sql = "CREATE TABLE $table_name (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            order_id bigint(20) unsigned DEFAULT NULL,
            reference_id varchar(100) NOT NULL,
            transaction_id varchar(100) DEFAULT NULL,
            token varchar(255) DEFAULT NULL,
            payment_url text DEFAULT NULL,
            user_id bigint(20) unsigned DEFAULT NULL,
            email varchar(255) NOT NULL,
            username varchar(100) DEFAULT NULL,
            product_name varchar(255) NOT NULL,
            amount decimal(10,2) NOT NULL,
            commission_type tinyint(1) NOT NULL DEFAULT 1,
            status varchar(20) NOT NULL DEFAULT 'pending',
            ip_address varchar(45) DEFAULT NULL,
            raw_request longtext DEFAULT NULL,
            raw_response longtext DEFAULT NULL,
            ipn_data longtext DEFAULT NULL,
            created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY  (id),
            KEY idx_order_id (order_id),
            KEY idx_reference_id (reference_id),
            KEY idx_transaction_id (transaction_id),
            KEY idx_status (status),
            KEY idx_created_at (created_at)
        ) $charset_collate;";

        require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
        dbDelta( $sql );

        // DB versiyonunu kaydet
        update_option( 'hipopay_db_version', HIPOPAY_DB_VERSION );
    }
}
