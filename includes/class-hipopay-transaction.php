<?php
/**
 * HipoPAY Transaction Model
 *
 * @package Hipopay_Payment_Gateway
 * @subpackage Hipopay_Payment_Gateway/includes
 */

if (!defined('ABSPATH')) {
    exit;
}

class Hipopay_Transaction
{

    /**
     * Tablo adını getir
     */
    public static function get_table_name()
    {
        global $wpdb;
        return $wpdb->prefix . 'hipopay_transactions';
    }

    /**
     * Yeni işlem oluştur
     */
    public static function create($data)
    {
        global $wpdb;

        // Benzersiz referans ID üret (yoksa)
        if (empty($data['reference_id'])) {
            $data['reference_id'] = self::generate_reference_id();
        }

        $inserted = $wpdb->insert(
            self::get_table_name(),
            $data
        );

        if ($inserted) {
            return $wpdb->insert_id;
        }

        return false;
    }

    /**
     * İşlemi güncelle
     */
    public static function update($id, $data)
    {
        global $wpdb;

        return $wpdb->update(
            self::get_table_name(),
            $data,
            array('id' => $id)
        );
    }

    /**
     * İşlemi güncelle (Referans ID ile)
     */
    public static function update_by_reference($reference_id, $data)
    {
        global $wpdb;

        return $wpdb->update(
            self::get_table_name(),
            $data,
            array('reference_id' => $reference_id)
        );
    }

    /**
     * Referans ID ile işlemi getir
     */
    public static function get_by_reference($reference_id)
    {
        global $wpdb;
        $table = self::get_table_name();

        return $wpdb->get_row($wpdb->prepare("SELECT * FROM {$table} WHERE reference_id = %s", $reference_id));
    }

    /**
     * Benzersiz referans numarası üret
     */
    public static function generate_reference_id()
    {
        return 'HPO-' . strtoupper(uniqid()) . '-' . wp_rand(1000, 9999);
    }
}
