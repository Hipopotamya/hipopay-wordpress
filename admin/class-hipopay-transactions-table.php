<?php
/**
 * Transactions List Table
 *
 * @package Hipopay_Payment_Gateway
 * @subpackage Hipopay_Payment_Gateway/admin
 */

if ( ! class_exists( 'WP_List_Table' ) ) {
    require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

class Hipopay_Transactions_Table extends WP_List_Table {

    public function __construct() {
        parent::__construct( array(
            'singular' => __( 'işlem', 'hipopay-payment-gateway' ),
            'plural'   => __( 'işlemler', 'hipopay-payment-gateway' ),
            'ajax'     => false,
        ) );
    }

    public function get_columns() {
        return array(
            'cb'             => '<input type="checkbox" />',
            'id'             => 'ID',
            'reference_id'   => __( 'Referans No', 'hipopay-payment-gateway' ),
            'order_id'       => __( 'Sipariş', 'hipopay-payment-gateway' ),
            'user'           => __( 'Müşteri', 'hipopay-payment-gateway' ),
            'product_name'   => __( 'Ürün / Açıklama', 'hipopay-payment-gateway' ),
            'amount'         => __( 'Tutar (₺)', 'hipopay-payment-gateway' ),
            'status'         => __( 'Durum', 'hipopay-payment-gateway' ),
            'created_at'     => __( 'Tarih', 'hipopay-payment-gateway' ),
        );
    }

    protected function get_sortable_columns() {
        return array(
            'id'           => array( 'id', false ),
            'reference_id' => array( 'reference_id', false ),
            'amount'       => array( 'amount', false ),
            'status'       => array( 'status', false ),
            'created_at'   => array( 'created_at', true ),
        );
    }

    public function prepare_items() {
        global $wpdb;

        $per_page     = 20;
        $current_page = $this->get_pagenum();
        $table_name   = Hipopay_Transaction::get_table_name();

        $columns  = $this->get_columns();
        $hidden   = array();
        $sortable = $this->get_sortable_columns();

        $this->_column_headers = array( $columns, $hidden, $sortable );

        // Sıralama
        $orderby = ! empty( $_GET['orderby'] ) ? sanitize_sql_orderby( wp_unslash( $_GET['orderby'] ) ) : 'id';
        $order   = ! empty( $_GET['order'] ) && in_array( strtolower( wp_unslash( $_GET['order'] ) ), array( 'asc', 'desc' ), true )
                   ? strtoupper( sanitize_text_field( wp_unslash( $_GET['order'] ) ) )
                   : 'DESC';

        // Filtreler
        $search  = isset( $_GET['s'] )             ? sanitize_text_field( wp_unslash( $_GET['s'] ) )             : '';
        $status  = isset( $_GET['status_filter'] ) ? sanitize_text_field( wp_unslash( $_GET['status_filter'] ) ) : '';
        $from    = isset( $_GET['date_from'] )      ? sanitize_text_field( wp_unslash( $_GET['date_from'] ) )      : '';
        $to      = isset( $_GET['date_to'] )        ? sanitize_text_field( wp_unslash( $_GET['date_to'] ) )        : '';

        // WHERE koşulları — parametreli sorgularla
        $where_parts = array( '1=1' );
        $prepare_args = array();

        if ( $search ) {
            $like = '%' . $wpdb->esc_like( $search ) . '%';
            $where_parts[] = '(username LIKE %s OR email LIKE %s OR reference_id LIKE %s OR transaction_id LIKE %s)';
            $prepare_args  = array_merge( $prepare_args, array( $like, $like, $like, $like ) );
        }
        if ( $status ) {
            $where_parts[] = 'status = %s';
            $prepare_args[] = $status;
        }
        if ( $from ) {
            $where_parts[] = 'DATE(created_at) >= %s';
            $prepare_args[] = $from;
        }
        if ( $to ) {
            $where_parts[] = 'DATE(created_at) <= %s';
            $prepare_args[] = $to;
        }

        $where_sql = 'WHERE ' . implode( ' AND ', $where_parts );

        if ( $prepare_args ) {
            // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
            $where_sql = $wpdb->prepare( "WHERE " . implode( ' AND ', $where_parts ), $prepare_args );
        }

        // Toplam kayıt
        // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
        $total_items = (int) $wpdb->get_var( "SELECT COUNT(id) FROM `$table_name` $where_sql" );

        $offset = ( $current_page - 1 ) * $per_page;

        // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
        $this->items = $wpdb->get_results(
            // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
            "SELECT * FROM `$table_name` $where_sql ORDER BY $orderby $order LIMIT $per_page OFFSET $offset",
            ARRAY_A
        );

        $this->set_pagination_args( array(
            'total_items' => $total_items,
            'per_page'    => $per_page,
            'total_pages' => ceil( $total_items / $per_page ),
        ) );
    }

    /**
     * Filtre navigasyonu — durum dropdown + tarih aralığı
     */
    protected function extra_tablenav( $which ) {
        if ( 'top' !== $which ) {
            return;
        }

        $current_status = isset( $_GET['status_filter'] ) ? sanitize_text_field( wp_unslash( $_GET['status_filter'] ) ) : '';
        $date_from      = isset( $_GET['date_from'] )     ? sanitize_text_field( wp_unslash( $_GET['date_from'] ) )     : '';
        $date_to        = isset( $_GET['date_to'] )       ? sanitize_text_field( wp_unslash( $_GET['date_to'] ) )       : '';

        $statuses = array(
            ''           => __( 'Tüm Durumlar', 'hipopay-payment-gateway' ),
            'pending'    => __( 'Bekliyor', 'hipopay-payment-gateway' ),
            'processing' => __( 'İşleniyor', 'hipopay-payment-gateway' ),
            'completed'  => __( 'Tamamlandı', 'hipopay-payment-gateway' ),
            'failed'     => __( 'Başarısız', 'hipopay-payment-gateway' ),
            'refunded'   => __( 'İade Edildi', 'hipopay-payment-gateway' ),
        );
        ?>
        <div class="hipopay-filter-bar alignleft actions">
            <label for="hipopay-status-filter" class="screen-reader-text"><?php esc_html_e( 'Duruma göre filtrele', 'hipopay-payment-gateway' ); ?></label>
            <select name="status_filter" id="hipopay-status-filter">
                <?php foreach ( $statuses as $val => $label ) : ?>
                    <option value="<?php echo esc_attr( $val ); ?>" <?php selected( $current_status, $val ); ?>>
                        <?php echo esc_html( $label ); ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <input type="date" name="date_from" id="hipopay-date-from"
                   value="<?php echo esc_attr( $date_from ); ?>"
                   placeholder="<?php esc_attr_e( 'Başlangıç', 'hipopay-payment-gateway' ); ?>" title="<?php esc_attr_e( 'Başlangıç tarihi', 'hipopay-payment-gateway' ); ?>">

            <input type="date" name="date_to" id="hipopay-date-to"
                   value="<?php echo esc_attr( $date_to ); ?>"
                   placeholder="<?php esc_attr_e( 'Bitiş', 'hipopay-payment-gateway' ); ?>" title="<?php esc_attr_e( 'Bitiş tarihi', 'hipopay-payment-gateway' ); ?>">

            <?php submit_button( __( 'Filtrele', 'hipopay-payment-gateway' ), 'button', 'filter_action', false ); ?>

            <?php if ( $current_status || $date_from || $date_to ) : ?>
                <a href="<?php echo esc_url( admin_url( 'admin.php?page=hipopay-transactions' ) ); ?>" class="button"><?php esc_html_e( 'Sıfırla', 'hipopay-payment-gateway' ); ?></a>
            <?php endif; ?>
        </div>
        <?php
    }

    protected function column_default( $item, $column_name ) {
        switch ( $column_name ) {
            case 'id':
            case 'reference_id':
            case 'product_name':
                return esc_html( $item[ $column_name ] );

            case 'amount':
                return '₺' . esc_html( number_format( (float) $item['amount'], 2, ',', '.' ) );

            case 'created_at':
                return esc_html( $item['created_at'] );

            case 'status':
                return $this->format_status( $item['status'] );

            case 'user':
                return $this->format_user_column( $item );

            case 'order_id':
                return $this->format_order_column( $item );

            default:
                return esc_html( $item[ $column_name ] ?? '' );
        }
    }

    protected function column_cb( $item ) {
        return sprintf(
            '<input type="checkbox" name="transaction[]" value="%s" />',
            esc_attr( $item['id'] )
        );
    }

    /**
     * Müşteri kolonu — kullanıcı profil linki ile
     */
    private function format_user_column( $item ) {
        $out = esc_html( $item['username'] );
        if ( $item['email'] ) {
            $out .= '<br><small style="color:#646970;">' . esc_html( $item['email'] ) . '</small>';
        }
        if ( ! empty( $item['user_id'] ) && (int) $item['user_id'] > 0 ) {
            $profile_url = admin_url( 'user-edit.php?user=' . intval( $item['user_id'] ) );
            $out .= '<br><a href="' . esc_url( $profile_url ) . '" style="font-size:11px;">' . esc_html__( 'Profili Görüntüle &rarr;', 'hipopay-payment-gateway' ) . '</a>';
        }
        return $out;
    }

    /**
     * Sipariş kolonu — WooCommerce HPOS uyumlu link
     */
    private function format_order_column( $item ) {
        if ( empty( $item['order_id'] ) || 0 === (int) $item['order_id'] ) {
            return '<span style="color:#c3c4c7;">—</span>';
        }

        $order_id = intval( $item['order_id'] );

        // WC 8.0+ HPOS veya klasik CPT
        if ( function_exists( 'wc_get_order' ) ) {
            $order = wc_get_order( $order_id );
            if ( $order ) {
                $url = $order->get_edit_order_url();
            } else {
                $url = admin_url( 'post.php?post=' . $order_id . '&action=edit' );
            }
        } else {
            $url = admin_url( 'post.php?post=' . $order_id . '&action=edit' );
        }

        return '<a href="' . esc_url( $url ) . '">#' . esc_html( $order_id ) . '</a>';
    }

    private function format_status( $status ) {
        $map = array(
            'pending'    => array( '#f0ad4e', __( 'Bekliyor', 'hipopay-payment-gateway' ) ),
            'processing' => array( '#5bc0de', __( 'İşleniyor', 'hipopay-payment-gateway' ) ),
            'completed'  => array( '#5cb85c', __( 'Tamamlandı', 'hipopay-payment-gateway' ) ),
            'failed'     => array( '#d9534f', __( 'Başarısız', 'hipopay-payment-gateway' ) ),
            'refunded'   => array( '#999', __( 'İade', 'hipopay-payment-gateway' ) ),
        );

        list( $color, $label ) = isset( $map[ $status ] ) ? $map[ $status ] : array( '#bdc3c7', ucfirst( $status ) );

        return sprintf(
            '<span class="hipopay-badge" style="background:%s;">%s</span>',
            esc_attr( $color ),
            esc_html( $label )
        );
    }
}
