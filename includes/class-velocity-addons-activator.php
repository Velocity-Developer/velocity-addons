<?php
/**
 * Fired during plugin activation.
 *
 * Membuat/upgrade tabel statistik dan menjadwalkan cron harian.
 * Disusun agar ramah MySQL 8 & utf8mb4 (pakai backtick + TIMESTAMP).
 *
 * @link       https://velocitydeveloper.com
 * @since      1.0.0
 * @package    Velocity_Addons
 * @subpackage Velocity_Addons/includes
 */

if ( ! defined('ABSPATH') ) exit;

class Velocity_Addons_Activator {

	public static function activate() {
		global $wpdb;

		// Pastikan dbDelta/maybe_create_table tersedia
		if ( ! function_exists('dbDelta') ) {
			require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		}

		$charset_collate = $wpdb->get_charset_collate();

		$visitor_logs_table   = $wpdb->prefix . 'vd_visitor_logs';
		$daily_stats_table    = $wpdb->prefix . 'vd_daily_stats';
		$monthly_stats_table  = $wpdb->prefix . 'vd_monthly_stats';
		$page_stats_table     = $wpdb->prefix . 'vd_page_stats';
		$referrer_stats_table = $wpdb->prefix . 'vd_referrer_stats';
		$daily_unique_table   = $wpdb->prefix . 'vd_daily_unique';
		$online_sessions_table = $wpdb->prefix . 'vd_online_sessions';

		// DDL (backtick + TIMESTAMP + BIGINT unsigned untuk counter)
		$sql1 = "CREATE TABLE `{$visitor_logs_table}` (
			`id` MEDIUMINT(9) NOT NULL AUTO_INCREMENT,
			`visitor_ip` VARCHAR(45) NOT NULL,
			`user_agent` TEXT NULL,
			`page_url` VARCHAR(191) NOT NULL,
			`referer` VARCHAR(191) NULL,
			`visit_date` DATE NOT NULL,
			`visit_time` TIME NOT NULL,
			`created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
			PRIMARY KEY (`id`),
			UNIQUE KEY `ip_url_date` (`visitor_ip`,`page_url`,`visit_date`),
			KEY `ip_date` (`visitor_ip`,`visit_date`),
			KEY `page_url` (`page_url`),
			KEY `visit_date` (`visit_date`)
		) {$charset_collate};";

		$sql2 = "CREATE TABLE `{$daily_stats_table}` (
			`id` MEDIUMINT(9) NOT NULL AUTO_INCREMENT,
			`stat_date` DATE NOT NULL,
			`unique_visitors` BIGINT UNSIGNED DEFAULT 0,
			`total_pageviews` BIGINT UNSIGNED DEFAULT 0,
			`created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
			`updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
			PRIMARY KEY (`id`),
			UNIQUE KEY `stat_date` (`stat_date`)
		) {$charset_collate};";

		$sql3 = "CREATE TABLE `{$monthly_stats_table}` (
			`id` MEDIUMINT(9) NOT NULL AUTO_INCREMENT,
			`stat_year` SMALLINT(4) UNSIGNED NOT NULL,
			`stat_month` TINYINT(2) UNSIGNED NOT NULL,
			`unique_visitors` BIGINT UNSIGNED DEFAULT 0,
			`total_pageviews` BIGINT UNSIGNED DEFAULT 0,
			`created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
			`updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
			PRIMARY KEY (`id`),
			UNIQUE KEY `year_month` (`stat_year`,`stat_month`)
		) {$charset_collate};";

		$sql4 = "CREATE TABLE `{$page_stats_table}` (
			`id` MEDIUMINT(9) NOT NULL AUTO_INCREMENT,
			`page_url` VARCHAR(191) NOT NULL,
			`stat_date` DATE NOT NULL,
			`unique_visitors` BIGINT UNSIGNED DEFAULT 0,
			`total_views` BIGINT UNSIGNED DEFAULT 0,
			`created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
			`updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
			PRIMARY KEY (`id`),
			UNIQUE KEY `page_date` (`page_url`,`stat_date`),
			KEY `page_url` (`page_url`),
			KEY `stat_date` (`stat_date`)
		) {$charset_collate};";

		$sql5 = "CREATE TABLE `{$referrer_stats_table}` (
			`id` MEDIUMINT(9) NOT NULL AUTO_INCREMENT,
			`referrer_domain` VARCHAR(191) NOT NULL,
			`stat_date` DATE NOT NULL,
			`total_visits` BIGINT UNSIGNED DEFAULT 0,
			`created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
			`updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
			PRIMARY KEY (`id`),
			UNIQUE KEY `referrer_date` (`referrer_domain`,`stat_date`),
			KEY `referrer_domain` (`referrer_domain`),
			KEY `stat_date` (`stat_date`)
		) {$charset_collate};";

		$sql6 = "CREATE TABLE `{$daily_unique_table}` (
			`id` MEDIUMINT(9) NOT NULL AUTO_INCREMENT,
			`visitor_ip` VARCHAR(45) NOT NULL,
			`stat_date` DATE NOT NULL,
			`created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
			PRIMARY KEY (`id`),
			UNIQUE KEY `ip_date` (`visitor_ip`,`stat_date`),
			KEY `stat_date` (`stat_date`)
		) {$charset_collate};";

		$sql7 = "CREATE TABLE `{$online_sessions_table}` (
			`session_id` VARCHAR(64) NOT NULL,
			`user_id` BIGINT(20) UNSIGNED NOT NULL DEFAULT 0,
			`current_url` VARCHAR(512) NOT NULL DEFAULT '',
			`first_seen` DATETIME NOT NULL,
			`last_seen` DATETIME NOT NULL,
			PRIMARY KEY (`session_id`),
			KEY `last_seen_idx` (`last_seen`),
			KEY `user_id_idx` (`user_id`)
		) {$charset_collate};";

		$tables = [
			$visitor_logs_table   => $sql1,
			$daily_stats_table    => $sql2,
			$monthly_stats_table  => $sql3,
			$page_stats_table     => $sql4,
			$referrer_stats_table => $sql5,
			$daily_unique_table   => $sql6,
			$online_sessions_table => $sql7,
		];

		foreach ($tables as $table_name => $ddl) {
			self::create_table($table_name, $ddl);
		}

		// Jadwalkan cron harian kalau belum ada (mulai dari sekarang, interval daily)
		if ( ! wp_next_scheduled('vd_daily_aggregation') ) {
			wp_schedule_event(time(), 'daily', 'vd_daily_aggregation');
		}

		// Tambahkan statistik dari versi lama (vd_statistic) sekali saja
		try {
			if ( ! get_option('velocity_addons_stats_legacy_added') && ! get_option('velocity_addons_stats_migrated') ) {
				require_once plugin_dir_path(__FILE__) . 'class-velocity-addons-statistic-legacy.php';
				$migrator = new Velocity_Addons_Statistic_Legacy(null, false);
				$migrator->run();
			}
		} catch ( \Throwable $e ) {
			// Jangan hentikan aktivasi jika migrasi error; hanya log di error_log
			if ( function_exists('error_log') ) {
				error_log('[velocity-addons] Migration error: ' . $e->getMessage());
			}
		}

		update_option('velocity_addons_db_version', VELOCITY_ADDONS_DB_VERSION);
	}

	/* ===== Helpers internal ===== */

	private static function create_table(string $table_name, string $ddl) : void {
		global $wpdb;

		if ('' === trim($table_name) || '' === trim($ddl)) return;

		// 1) dbDelta
		dbDelta($ddl);
		if ( self::table_exists($table_name) ) return;

		// 2) maybe_create_table
		if ( function_exists('maybe_create_table') ) {
			maybe_create_table($table_name, $ddl);
			if ( self::table_exists($table_name) ) return;
		}

		// 3) Fallback IF NOT EXISTS
		$fallback = preg_replace('/^CREATE\s+TABLE\s+/i', 'CREATE TABLE IF NOT EXISTS ', trim($ddl));
		if ( $fallback ) $wpdb->query($fallback);
	}

	private static function table_exists(string $table_name) : bool {
		global $wpdb;
		if ('' === trim($table_name)) return false;
		$found = $wpdb->get_var( $wpdb->prepare('SHOW TABLES LIKE %s', $table_name) );
		return $found === $table_name;
	}
}
