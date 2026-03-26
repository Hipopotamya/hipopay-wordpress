<?php
/**
 * Transactions Page View
 *
 * @package Hipopay_Payment_Gateway
 * @subpackage Hipopay_Payment_Gateway/admin/partials
 */

if (!defined('ABSPATH')) {
    exit;
}
?>
<div class="wrap">
    <h1><?php esc_html_e('İşlemler', 'hipopay-payment-gateway'); ?></h1>
    <p style="color:#646970;"><?php esc_html_e('HipoPAY üzerinden gerçekleştirilen tüm ödeme işlemleri burada listelenir.', 'hipopay-payment-gateway'); ?></p>

    <form method="get">
        <input type="hidden" name="page" value="<?php echo esc_attr(isset($_REQUEST['page']) ? wp_unslash($_REQUEST['page']) : ''); ?>" />
        <?php
require_once HIPOPAY_PLUGIN_DIR . 'admin/class-hipopay-transactions-table.php';
$transactions_table = new Hipopay_Transactions_Table();
$transactions_table->prepare_items();
$transactions_table->search_box(__('Ara', 'hipopay-payment-gateway'), 'hipopay-search');
$transactions_table->display();
?>
    </form>
</div>
