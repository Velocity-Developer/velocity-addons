<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

/**
 * Velocity Addons Statistic Legacy Import
 *
 * Utilities to import/add legacy statistics into the current schema.
 */
class Velocity_Addons_Statistic_Legacy {
    const VERSION = '1.0.0';

    /** @var string|null */
    protected $old_plugin_path;

    /** @var bool */
    protected $dry_run = false;

    /** @var array */
    protected $log = array();

    public function __construct( $old_plugin_path = null, $dry_run = false ) {
        // Do not auto-guess any local path; only use provided path.
        $this->old_plugin_path = $old_plugin_path;
        $this->dry_run = (bool) $dry_run;
    }

    // WP-CLI integration removed; this class runs via plugin hooks only.

    public function run() {
        global $wpdb;

        $errors = array();
        $this->log = array();

        $this->log_msg( 'Version: ' . self::VERSION );
        $this->log_msg( 'Dry-run: ' . ( $this->dry_run ? 'yes' : 'no' ) );
        $this->log_msg( 'Old plugin path: ' . ( $this->old_plugin_path ? $this->old_plugin_path : '(none)' ) );

        try {
            $this->backup_snapshot();
            $this->migrate_options();
            $did_any = false;
            $did_any = $this->migrate_from_old_vd_statistic() || $did_any;
            $this->migrate_custom_tables();
            $did_any = $this->migrate_files_from_old_path() || $did_any;
            if ( $did_any && ! $this->dry_run ) {
                update_option( 'velocity_addons_stats_legacy_added', current_time( 'mysql' ) );
                $this->log_msg( 'Marked legacy addition complete in options.' );
            } else {
                $this->log_msg( 'No legacy data found to add; flag not set.' );
            }
        } catch ( \Throwable $e ) {
            $errors[] = 'Exception: ' . $e->getMessage();
        }

        return array(
            'log'    => $this->log,
            'errors' => $errors,
        );
    }

    /**
     * Add cumulative data from legacy table wp_vd_statistic into current tables.
     * - Sum into vd_daily_stats (unique_visitors, total_pageviews per date)
     * - Increment post meta 'hit' per post
     */
    protected function migrate_from_old_vd_statistic() {
        global $wpdb;

        $old_table = $wpdb->prefix . 'vd_statistic';

        if ( ! $this->table_exists( $old_table ) ) {
            $this->log_msg( 'Legacy table not found: ' . $old_table . ' — skipping.' );
            return false;
        }

        $this->log_msg( 'Found legacy table: ' . $old_table );

        // Ensure new daily table exists (schema minimal, compatible with new plugin)
        $daily_table = $wpdb->prefix . 'vd_daily_stats';
        if ( ! $this->table_exists( $daily_table ) ) {
            $this->ensure_daily_stats_table( $daily_table );
        }

        // 1) Aggregate per-day pageviews (COUNT(*))
        $pv_rows = $wpdb->get_results(
            "SELECT DATE(`timestamp`) AS stat_date, COUNT(*) AS total_pageviews
             FROM {$old_table}
             GROUP BY DATE(`timestamp`)
             ORDER BY DATE(`timestamp`)",
            ARRAY_A
        );
        $pv_by_date = array();
        foreach ( (array) $pv_rows as $r ) { $pv_by_date[$r['stat_date']] = (int) $r['total_pageviews']; }

        // 1b) Aggregate per-day unique visitors based on first visit date of each session
        $uv_rows = $wpdb->get_results(
            "SELECT first_date AS stat_date, COUNT(*) AS unique_visitors
             FROM (
               SELECT sesi, MIN(DATE(`timestamp`)) AS first_date
               FROM {$old_table}
               GROUP BY sesi
             ) t
             GROUP BY first_date
             ORDER BY first_date",
            ARRAY_A
        );
        $uv_by_date = array();
        foreach ( (array) $uv_rows as $r ) { $uv_by_date[$r['stat_date']] = (int) $r['unique_visitors']; }

        // 1c) Insert per-day values; if a row already exists for that date, leave it unchanged (no-op)
        $all_dates = array_unique( array_merge( array_keys($pv_by_date), array_keys($uv_by_date) ) );
        sort($all_dates);
        $inserted_days = 0; $did = false;
        foreach ( $all_dates as $date ) {
            $uv = isset($uv_by_date[$date]) ? (int)$uv_by_date[$date] : 0;
            $pv = isset($pv_by_date[$date]) ? (int)$pv_by_date[$date] : 0;
            if ( $this->dry_run ) {
                $this->log_msg( sprintf('[DRY-RUN] Insert daily %s: UV=%d, PV=%d (no-op if exists)', $date, $uv, $pv) );
                $inserted_days++;
                continue;
            }
            $wpdb->query( $wpdb->prepare(
                "INSERT INTO {$daily_table} (stat_date, unique_visitors, total_pageviews)
                 VALUES (%s, %d, %d)
                 ON DUPLICATE KEY UPDATE stat_date = stat_date",
                $date, $uv, $pv
            ) );
            $inserted_days++;
            $did = true;
        }
        $this->log_msg( sprintf('Daily stats inserted for %d day(s).', $inserted_days) );

        // 2) Update post meta 'hit' based on old counts
        $sql_posts = "SELECT post_id, COUNT(*) AS hits FROM {$old_table} WHERE post_id IS NOT NULL AND post_id > 0 GROUP BY post_id";
        $per_posts = $wpdb->get_results( $sql_posts, ARRAY_A );
        $updated_posts = 0;
        foreach ( (array) $per_posts as $pr ) {
            $post_id = (int) $pr['post_id'];
            $hits    = (int) $pr['hits'];
            if ( $post_id <= 0 ) { continue; }
            if ( $this->dry_run ) {
                $this->log_msg( sprintf('[DRY-RUN] Would add %d to post #%d meta hit', $hits, $post_id) );
            } else {
                $current = (int) get_post_meta( $post_id, 'hit', true );
                update_post_meta( $post_id, 'hit', $current + $hits );
            }
            $updated_posts++;
            $did = true;
        }
        $this->log_msg( sprintf('Post meta "hit" updated for %d post(s).', $updated_posts) );
        // 3) Store baseline totals from legacy table to reconcile "all-time" in new summary
        $base_visits = (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$old_table}" );
        $base_unique = (int) $wpdb->get_var( "SELECT COUNT(DISTINCT sesi) FROM {$old_table}" );
        $last_date   = (string) $wpdb->get_var( "SELECT DATE(MAX(`timestamp`)) FROM {$old_table}" );

        $this->log_msg( sprintf('Legacy base totals: visits=%d, unique=%d, last_date=%s', $base_visits, $base_unique, $last_date ?: '-') );
        if ( ! $this->dry_run ) {
            update_option( 'vd_legacy_total_visits', $base_visits, true );
            update_option( 'vd_legacy_total_unique', $base_unique, true );
            if ( ! empty($last_date) ) {
                update_option( 'vd_legacy_last_date', $last_date, true );
            }
        }
        return $did;
    }

    protected function ensure_daily_stats_table( $daily_table ) {
        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        $charset_collate = function_exists('maybe_create_table') ? '' : '';
        $sql = "CREATE TABLE {$daily_table} (
            id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            stat_date DATE NOT NULL,
            unique_visitors BIGINT(20) UNSIGNED NOT NULL DEFAULT 0,
            total_pageviews BIGINT(20) UNSIGNED NOT NULL DEFAULT 0,
            PRIMARY KEY  (id),
            UNIQUE KEY stat_date (stat_date)
        ) ENGINE=InnoDB";
        if ( $this->dry_run ) {
            $this->log_msg( '[DRY-RUN] Would create table: ' . $daily_table );
            return;
        }
        dbDelta( $sql );
        $this->log_msg( 'Ensured table exists: ' . $daily_table );
    }

    // Removed get_default_old_path(); online environment should not guess local paths.

    protected function backup_snapshot() {
        global $wpdb;
        // Minimal backup: selected option keys and detected tables metadata
        $payload = array(
            'timestamp' => current_time( 'mysql' ),
            'options'   => $this->fetch_option_keys(array(
                'velocity_addons_stat%', 'velocity_addons_stats%', 'velocity_stats%', 'va_stat%'
            )),
            'tables'    => $this->detect_stat_tables(),
        );

        $upload_dir = wp_get_upload_dir();
        $dir = trailingslashit( $upload_dir['basedir'] ) . 'velocity-addons';
        $filename = 'migration-backup-' . gmdate( 'Ymd-His' ) . '.json';
        if ( ! wp_mkdir_p( $dir ) ) {
            $this->log_msg( 'Could not create backup dir: ' . $dir );
            return;
        }
        $path = trailingslashit( $dir ) . $filename;
        if ( ! $this->dry_run ) {
            file_put_contents( $path, wp_json_encode( $payload, JSON_PRETTY_PRINT ) );
        }
        $this->log_msg( 'Wrote backup snapshot to: ' . $path );
    }

    protected function migrate_options() {
        global $wpdb;
        // Map old option names to new ones (edit as needed)
        $map = array(
            // 'old_option_key' => 'new_option_key',
            // Example guesses below (adjust to actual plugin keys):
            'velocity_addons_stat_total_views' => 'velocity_addons_statistics_total_views',
            'velocity_addons_stat_total_clicks' => 'velocity_addons_statistics_total_clicks',
        );

        if ( empty( $map ) ) {
            $this->log_msg( 'No option mapping defined; skipping option migration.' );
            return;
        }

        foreach ( $map as $old => $new ) {
            $val = get_option( $new, null );
            if ( null !== $val ) { continue; }
            $old_val = get_option( $old, null );
            if ( null === $old_val ) { continue; }
            if ( ! $this->dry_run ) {
                update_option( $new, $old_val, true );
            }
            $this->log_msg( sprintf( 'Migrated option %s -> %s', $old, $new ) );
        }
    }

    protected function migrate_custom_tables() {
        global $wpdb;
        $tables = $this->detect_stat_tables();
        if ( empty( $tables ) ) {
            $this->log_msg( 'No statistic-like tables detected; skipping table migration.' );
            return;
        }

        // Heuristic: choose one "old" and one "new" by name pattern
        $old = null; $new = null;
        foreach ( $tables as $t ) {
            $name = $t['name'];
            if ( false !== strpos( $name, 'addons' ) && false !== strpos( $name, 'stat' ) ) {
                // candidate new table
                $new = $name;
            } elseif ( false !== strpos( $name, 'velocity' ) && false !== strpos( $name, 'stat' ) ) {
                $old = $name;
            }
        }

        if ( ! $old || ! $new || $old === $new ) {
            $this->log_msg( 'Could not determine old/new stat tables; skipping copy.' );
            return;
        }

        $old_cols = $this->get_table_columns( $old );
        $new_cols = $this->get_table_columns( $new );
        $columns_in_common = array_values( array_intersect( $old_cols, $new_cols ) );
        if ( empty( $columns_in_common ) ) {
            $this->log_msg( 'No common columns between ' . $old . ' and ' . $new . '; skipping.' );
            return;
        }

        $cols = implode( ',', array_map( array( $this, 'esc_sql_ident' ), $columns_in_common ) );
        $sql  = 'INSERT INTO ' . $this->esc_sql_ident( $new ) . ' (' . $cols . ') SELECT ' . $cols . ' FROM ' . $this->esc_sql_ident( $old );
        if ( $this->dry_run ) {
            $this->log_msg( '[DRY-RUN] Would execute: ' . $sql );
            return;
        }
        $rows = $wpdb->query( $sql );
        $this->log_msg( sprintf( 'Copied %d rows: %s -> %s', intval( $rows ), $old, $new ) );
    }

    protected function migrate_files_from_old_path() {
        if ( empty( $this->old_plugin_path ) || ! $this->path_exists( $this->old_plugin_path ) ) {
            $this->log_msg( 'Old plugin path not found; skipping file-based migrations.' );
            return false;
        }
        $possible = array(
            'assets/data/stats.json',
            'assets/data/statistics.json',
            'assets/stats.json',
            'export/stats.json',
        );
        $found = null;
        foreach ( $possible as $rel ) {
            $path = trailingslashit( $this->old_plugin_path ) . str_replace( array('/', '\\'), DIRECTORY_SEPARATOR, $rel );
            if ( file_exists( $path ) ) { $found = $path; break; }
        }
        if ( ! $found ) {
            $this->log_msg( 'No JSON export found in old plugin path.' );
            return false;
        }
        $raw = file_get_contents( $found );
        $data = json_decode( $raw, true );
        if ( ! is_array( $data ) ) {
            $this->log_msg( 'Invalid JSON in ' . $found );
            return;
        }
        // Hook for site-specific import handling
        do_action( 'velocity_addons_before_stats_json_import', $data );
        // Example: expect array of rows for a table insert
        if ( isset( $data['table'] ) && isset( $data['rows'] ) && is_array( $data['rows'] ) ) {
            $this->import_rows( $data['table'], $data['rows'] );
            $this->log_msg( 'Processed JSON import from: ' . $found );
            return true;
        }
        $this->log_msg( 'No recognized data format in: ' . $found );
        return false;
    }

    protected function import_rows( $table, $rows ) {
        global $wpdb;
        $table = $this->normalize_table_name( $table );
        foreach ( $rows as $row ) {
            if ( $this->dry_run ) { continue; }
            $wpdb->insert( $table, $row );
        }
    }

    protected function fetch_option_keys( $like_patterns ) {
        global $wpdb;
        $out = array();
        foreach ( (array) $like_patterns as $like ) {
            $like = str_replace( array('%', '_'), array('\\%','\\_'), $like );
            $query = $wpdb->prepare( "SELECT option_name FROM {$wpdb->options} WHERE option_name LIKE %s ESCAPE '\\'", $like );
            $names = $wpdb->get_col( $query );
            $out = array_merge( $out, $names );
        }
        return array_values( array_unique( $out ) );
    }

    protected function detect_stat_tables() {
        global $wpdb;
        $prefix = $wpdb->esc_like( $wpdb->prefix );
        $patterns = array( '%velocity%stat%', '%v_addons%stat%', '%addons%stat%' );
        $found = array();
        foreach ( $patterns as $pat ) {
            $sql = $wpdb->prepare( 'SHOW TABLES LIKE %s', $prefix . $pat );
            $rows = $wpdb->get_col( $sql );
            foreach ( $rows as $r ) {
                $found[ $r ] = array( 'name' => $r );
            }
        }
        return array_values( $found );
    }

    protected function table_exists( $table_full_name ) {
        global $wpdb;
        if ( empty( $table_full_name ) ) return false;
        $res = $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $table_full_name ) );
        return $res === $table_full_name;
    }

    protected function get_table_columns( $table ) {
        global $wpdb;
        $table = $this->normalize_table_name( $table );
        $cols = $wpdb->get_col( 'DESCRIBE ' . $this->esc_sql_ident( $table ), 0 );
        return array_map( 'strval', (array) $cols );
    }

    protected function normalize_table_name( $table ) {
        global $wpdb;
        if ( 0 !== strpos( $table, $wpdb->prefix ) ) {
            return $wpdb->prefix . ltrim( $table, '_' );
        }
        return $table;
    }

    protected function esc_sql_ident( $identifier ) {
        // Basic identifier quoting with backticks; ensure no backticks inside
        $identifier = str_replace( '`', '``', $identifier );
        return '`' . $identifier . '`';
    }

    protected function path_exists( $path ) {
        return is_string( $path ) && ( file_exists( $path ) || is_dir( $path ) );
    }

    protected function log_msg( $msg ) {
        $this->log[] = (string) $msg;
    }
}

// No CLI auto-registration
