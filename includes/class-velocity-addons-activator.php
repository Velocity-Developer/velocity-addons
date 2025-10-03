<?php
/**
 * Fired during plugin activation
 * @link       https://velocitydeveloper.com
 * @since      1.0.0
 * @package    Velocity_Addons
 * @subpackage Velocity_Addons/includes
 */

class Velocity_Addons_Activator {

    /**
     * Dieksekusi sekali saat plugin diaktifkan:
     * - Buat/upgrade tabel statistik via dbDelta
     * - Jadwalkan cron harian vd_daily_aggregation
     */
    public static function activate() {
        // Pastikan fungsi dbDelta tersedia
        if ( ! function_exists('dbDelta') ) {
            require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        }

        global $wpdb;
        $charset_collate = $wpdb->get_charset_collate();

        $visitor_logs_table   = $wpdb->prefix . 'vd_visitor_logs';
        $daily_stats_table    = $wpdb->prefix . 'vd_daily_stats';
        $monthly_stats_table  = $wpdb->prefix . 'vd_monthly_stats';
        $page_stats_table     = $wpdb->prefix . 'vd_page_stats';
        $referrer_stats_table = $wpdb->prefix . 'vd_referrer_stats';
        $daily_unique_table   = $wpdb->prefix . 'vd_daily_unique';

        // Gunakan VARCHAR(191) untuk kolom yang diindeks (aman untuk utf8mb4)
        $sql1 = "CREATE TABLE $visitor_logs_table (
            id MEDIUMINT(9) NOT NULL AUTO_INCREMENT,
            visitor_ip VARCHAR(45) NOT NULL,
            user_agent TEXT,
            page_url VARCHAR(191) NOT NULL,
            referer VARCHAR(191),
            visit_date DATE NOT NULL,
            visit_time TIME NOT NULL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY ip_url_date (visitor_ip, page_url, visit_date),
            KEY ip_date (visitor_ip, visit_date),
            KEY page_url (page_url),
            KEY visit_date (visit_date)
        ) $charset_collate;";

        $sql2 = "CREATE TABLE $daily_stats_table (
            id MEDIUMINT(9) NOT NULL AUTO_INCREMENT,
            stat_date DATE NOT NULL,
            unique_visitors INT(11) DEFAULT 0,
            total_pageviews INT(11) DEFAULT 0,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY stat_date (stat_date)
        ) $charset_collate;";

        $sql3 = "CREATE TABLE $monthly_stats_table (
            id MEDIUMINT(9) NOT NULL AUTO_INCREMENT,
            stat_year INT(4) NOT NULL,
            stat_month INT(2) NOT NULL,
            unique_visitors INT(11) DEFAULT 0,
            total_pageviews INT(11) DEFAULT 0,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY year_month (stat_year, stat_month)
        ) $charset_collate;";

        $sql4 = "CREATE TABLE $page_stats_table (
            id MEDIUMINT(9) NOT NULL AUTO_INCREMENT,
            page_url VARCHAR(191) NOT NULL,
            stat_date DATE NOT NULL,
            unique_visitors INT(11) DEFAULT 0,
            total_views INT(11) DEFAULT 0,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY page_date (page_url, stat_date),
            KEY page_url (page_url),
            KEY stat_date (stat_date)
        ) $charset_collate;";

        $sql5 = "CREATE TABLE $referrer_stats_table (
            id MEDIUMINT(9) NOT NULL AUTO_INCREMENT,
            referrer_domain VARCHAR(191) NOT NULL,
            stat_date DATE NOT NULL,
            total_visits INT(11) DEFAULT 0,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY referrer_date (referrer_domain, stat_date),
            KEY referrer_domain (referrer_domain),
            KEY stat_date (stat_date)
        ) $charset_collate;";

        // NEW: penanda unique harian (IP+DATE) → atomic unique
        $sql6 = "CREATE TABLE $daily_unique_table (
            id MEDIUMINT(9) NOT NULL AUTO_INCREMENT,
            visitor_ip VARCHAR(45) NOT NULL,
            stat_date DATE NOT NULL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY ip_date (visitor_ip, stat_date),
            KEY stat_date (stat_date)
        ) $charset_collate;";

        // Jalankan dbDelta
        dbDelta($sql1);
        dbDelta($sql2);
        dbDelta($sql3);
        dbDelta($sql4);
        dbDelta($sql5);
        dbDelta($sql6);

        // Jadwalkan cron harian jika belum ada
        if ( ! wp_next_scheduled('vd_daily_aggregation') ) {
            wp_schedule_event(time(), 'daily', 'vd_daily_aggregation');
        }

        // Jangan echo/print apapun di sini (hindari "unexpected output")
    }
}
