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
        $errors = array();
        $this->log = array();

        $this->log_msg( 'Version: ' . self::VERSION );
        $this->log_msg( 'Dry-run: ' . ( $this->dry_run ? 'yes' : 'no' ) );
        $this->log_msg( 'Old plugin path: ' . ( $this->old_plugin_path ? $this->old_plugin_path : '(none)' ) );

        try {
            $this->backup_snapshot();
            $this->migrate_options();

            $legacy_table_found = $this->check_legacy_vd_statistic();

            if ( $legacy_table_found && ! $this->dry_run ) {
                update_option( 'velocity_addons_stats_legacy_added', current_time( 'mysql' ) );
                $this->log_msg( 'Legacy table detected; marker updated.' );
            } else {
                $this->log_msg( 'Legacy table not found, no migration needed.' );
                if ( ! $this->dry_run ) {
                    update_option( 'velocity_addons_stats_legacy_added', 'missing' );
                }
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
     * Only verify whether legacy table wp_vd_statistic exists and log its status.
     */
    protected function check_legacy_vd_statistic() {
        global $wpdb;

        $table = $wpdb->prefix . 'vd_statistic';

        if ( ! $this->table_exists( $table ) ) {
            $this->log_msg( 'Legacy table not found: ' . $table );
            return false;
        }

        $count = (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$table}" );
        $this->log_msg( sprintf( 'Legacy table %1$s detected with %2$d row(s).', $table, $count ) );

        return true;
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

    protected function fetch_option_keys( $like_patterns ) {
        global $wpdb;
        $out = array();
        foreach ( (array) $like_patterns as $pattern ) {
            $pattern = (string) $pattern;
            $has_wildcard = (substr( $pattern, -1 ) === '%');
            if ( $has_wildcard ) {
                $pattern = substr( $pattern, 0, -1 );
            }
            $like = $wpdb->esc_like( $pattern );
            if ( $has_wildcard ) {
                $like .= '%';
            }
            $query = $wpdb->prepare( "SELECT option_name FROM {$wpdb->options} WHERE option_name LIKE %s", $like );
            $names = $wpdb->get_col( $query );
            $out   = array_merge( $out, $names );
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

    protected function log_msg( $msg ) {
        $this->log[] = (string) $msg;
    }
}

// No CLI auto-registration
