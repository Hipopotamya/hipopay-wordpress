<?php
/**
 * Overview Page View
 *
 * @package Hipopay_Payment_Gateway
 * @subpackage Hipopay_Payment_Gateway/admin/partials
 */

if (!defined('ABSPATH')) {
    exit;
}

global $wpdb;
$table = Hipopay_Transaction::get_table_name();

// Varsayılanlar
$total_tx = 0;
$success_tx = 0;
$pending_tx = 0;
$failed_tx = 0;
$total_rev = 0;
$today_rev = 0;
$chart_json = '{"labels":[],"values":[]}';

if ($wpdb->get_var($wpdb->prepare('SHOW TABLES LIKE %s', $table)) === $table) {
    $total_tx = (int) $wpdb->get_var("SELECT COUNT(id) FROM `$table`");
    $success_tx = (int) $wpdb->get_var("SELECT COUNT(id) FROM `$table` WHERE status = 'completed'");
    $pending_tx = (int) $wpdb->get_var("SELECT COUNT(id) FROM `$table` WHERE status = 'pending'");
    $failed_tx = (int) $wpdb->get_var("SELECT COUNT(id) FROM `$table` WHERE status = 'failed'");
    $total_rev = (float) $wpdb->get_var("SELECT COALESCE(SUM(amount),0) FROM `$table` WHERE status = 'completed'");
    $today_rev = (float) $wpdb->get_var("SELECT COALESCE(SUM(amount),0) FROM `$table` WHERE status = 'completed' AND DATE(created_at) = CURDATE()");

    // Son 7 günlük ciro — grafik için
    $rows = $wpdb->get_results(
        "SELECT DATE(created_at) AS day, COALESCE(SUM(amount),0) AS total
         FROM `$table`
         WHERE status = 'completed'
           AND created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
         GROUP BY DATE(created_at)
         ORDER BY day ASC",
        ARRAY_A
    );

    // 7 günlük tam diziyi doldur (veri olmayan günler 0)
    $labels = array();
    $values = array();
    for ($i = 6; $i >= 0; $i--) {
        $day = gmdate('Y-m-d', strtotime("-{$i} days"));
        $labels[] = gmdate('d M', strtotime($day));
        $found = 0;
        foreach ($rows as $row) {
            if ($row['day'] === $day) {
                $found = (float) $row['total'];
                break;
            }
        }
        $values[] = $found;
    }

    $chart_json = wp_json_encode(array('labels' => $labels, 'values' => $values));
}
?>

<div class="wrap hipopay-wrap">
    <h1><?php esc_html_e('HipoPAY — Overview', 'hipopay-payment-gateway'); ?></h1>

    <!-- İşlem İstatistikleri -->
    <div class="hipopay-stats-grid">

        <div class="hipopay-stat-card">
            <div class="hipopay-stat-card__icon">📊</div>
            <div class="hipopay-stat-card__body">
                <div class="hipopay-stat-card__value"><?php echo esc_html($total_tx); ?></div>
                <div class="hipopay-stat-card__label"><?php esc_html_e('Total Transactions', 'hipopay-payment-gateway'); ?></div>
            </div>
        </div>

        <div class="hipopay-stat-card hipopay-stat-card--green">
            <div class="hipopay-stat-card__icon">✅</div>
            <div class="hipopay-stat-card__body">
                <div class="hipopay-stat-card__value"><?php echo esc_html($success_tx); ?></div>
                <div class="hipopay-stat-card__label"><?php esc_html_e('Successful Transactions', 'hipopay-payment-gateway'); ?></div>
            </div>
        </div>

        <div class="hipopay-stat-card hipopay-stat-card--orange">
            <div class="hipopay-stat-card__icon">⏳</div>
            <div class="hipopay-stat-card__body">
                <div class="hipopay-stat-card__value"><?php echo esc_html($pending_tx); ?></div>
                <div class="hipopay-stat-card__label"><?php esc_html_e('Pending Transactions', 'hipopay-payment-gateway'); ?></div>
            </div>
        </div>

        <div class="hipopay-stat-card hipopay-stat-card--red">
            <div class="hipopay-stat-card__icon">❌</div>
            <div class="hipopay-stat-card__body">
                <div class="hipopay-stat-card__value"><?php echo esc_html($failed_tx); ?></div>
                <div class="hipopay-stat-card__label"><?php esc_html_e('Failed Transactions', 'hipopay-payment-gateway'); ?></div>
            </div>
        </div>

    </div>

    <!-- Ciro İstatistikleri -->
    <div class="hipopay-revenue-grid">

        <div class="hipopay-stat-card hipopay-stat-card--blue">
            <div class="hipopay-stat-card__icon">💰</div>
            <div class="hipopay-stat-card__body">
                <div class="hipopay-stat-card__value">
                    <?php echo esc_html(number_format($total_rev, 2, ',', '.')); ?> ₺</div>
                <div class="hipopay-stat-card__label"><?php esc_html_e('Total Revenue', 'hipopay-payment-gateway'); ?></div>
            </div>
        </div>

        <div class="hipopay-stat-card hipopay-stat-card--purple">
            <div class="hipopay-stat-card__icon">📅</div>
            <div class="hipopay-stat-card__body">
                <div class="hipopay-stat-card__value">
                    <?php echo esc_html(number_format($today_rev, 2, ',', '.')); ?> ₺</div>
                <div class="hipopay-stat-card__label"><?php esc_html_e('Today Revenue', 'hipopay-payment-gateway'); ?></div>
            </div>
        </div>

    </div>

    <!-- 7 Günlük Grafik -->
    <div class="hipopay-chart-card">
        <h2><?php esc_html_e('Last 7 Days Revenue (₺)', 'hipopay-payment-gateway'); ?></h2>
        <canvas id="hipopay-revenue-chart" style="max-height:220px;"></canvas>
    </div>

    <script>
    (function() {
        var chartData = <?php echo $chart_json; /* wp_json_encode çıktısı — güvenli */ ?>;

        function initHipopayChart() {
            if ( typeof Chart === 'undefined' ) { return; }
            var canvas = document.getElementById( 'hipopay-revenue-chart' );
            if ( ! canvas ) { return; }

            new Chart( canvas, {
                type: 'bar',
                data: {
                    labels: chartData.labels,
                    datasets: [{
                        label: '<?php esc_html_e('Revenue (₺)', 'hipopay-payment-gateway'); ?>',
                        data: chartData.values,
                        backgroundColor: 'rgba(0, 115, 170, 0.75)',
                        borderColor: 'rgba(0, 115, 170, 1)',
                        borderWidth: 1,
                        borderRadius: 4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: true,
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            callbacks: {
                                label: function( ctx ) {
                                    return '₺' + ctx.parsed.y.toLocaleString( 'tr-TR', { minimumFractionDigits: 2 } );
                                }
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                callback: function( value ) {
                                    return '₺' + value.toLocaleString( 'tr-TR' );
                                }
                            }
                        }
                    }
                }
            });
        }

        // window.load: tüm dış scriptler (Chart.js dahil) yüklendikten sonra tetiklenir
        if ( document.readyState === 'complete' ) {
            initHipopayChart();
        } else {
            window.addEventListener( 'load', initHipopayChart );
        }
    })();
    </script>

</div>