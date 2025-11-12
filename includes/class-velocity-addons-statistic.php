<?php
/**
 * Visitor Statistics functionality
 * @link       https://velocitydeveloper.com
 * @since      1.0.0
 * @package    Velocity_Addons
 * @subpackage Velocity_Addons/includes
 */

if ( ! class_exists('Velocity_Addons_Statistic') ) :

class Velocity_Addons_Statistic {

    /** Tables (hanya referensi nama) */
    private $logs_table;
    private $daily_stats_table;
    private $monthly_stats_table;
    private $page_stats_table;
    private $referrer_stats_table;
    private $daily_unique_table; // penanda unique harian (atomic)

    /** Popular posts helper */
    private $visitor_id = '';
    private $hit_window = 240; // detik (4 menit)
    /** Online users helper */
    private $online_ttl = 300; // detik (5 menit)
    private $online_debounce = 10; // detik
    private $online_sessions_table;

    private function is_enabled(): bool {
        // cek opsi di database, default aktif (1)
        return get_option('statistik_velocity', '1') === '1';
    }

    public function __construct() {

        if ( ! $this->is_enabled() ) return;

        global $wpdb;

        $this->logs_table           = $wpdb->prefix . 'vd_visitor_logs';
        $this->daily_stats_table    = $wpdb->prefix . 'vd_daily_stats';
        $this->monthly_stats_table  = $wpdb->prefix . 'vd_monthly_stats';
        $this->page_stats_table     = $wpdb->prefix . 'vd_page_stats';
        $this->referrer_stats_table = $wpdb->prefix . 'vd_referrer_stats';
        $this->daily_unique_table   = $wpdb->prefix . 'vd_daily_unique';
        $this->online_sessions_table = $wpdb->prefix . 'vd_online_sessions';

        // Cookie visitor anonim utk throttle per user
        add_action('init', array($this, 'ensure_visitor_id'));

        // Track kunjungan + update meta 'hit'
        add_action('template_redirect', array($this, 'track_visitor'), 11);

        // Cron harian (agregasi + cleanup)
        add_action('vd_daily_aggregation', array($this, 'run_daily_aggregation'));

        // Cron 10 menit untuk cleanup sesi online
        add_filter('cron_schedules', array($this, 'crons_add_ten_minutes'));
        add_action('vd_cleanup_online_sessions', array($this, 'cleanup_online_sessions'));
        if ( ! wp_next_scheduled('vd_cleanup_online_sessions') ) {
            wp_schedule_event( time() + 600, 'vd_ten_minutes', 'vd_cleanup_online_sessions' );
        }

        // Tabel sesi online dibuat di activator; tidak dibuat di sini lagi

        // Shortcodes
        add_shortcode('velocity-statistics', array($this, 'statistics_shortcode'));
        add_shortcode('velocity-hits', array($this, 'shortcode_post_hit'));
    }

    /** ==================================
     *  Main tracker + meta 'hit' updater
     *  ================================== */
    public function track_visitor() {
        // Hanya front-end biasa
        if ( is_admin() || wp_doing_ajax() || wp_doing_cron() ) return;

        // Metode HTTP yang dihitung saja
        $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
        if ( ! in_array($method, ['GET','HEAD'], true) ) return;

        // Skip bot umum
        $ua = $_SERVER['HTTP_USER_AGENT'] ?? '';
        if ( $this->is_bot($ua) ) return;

        // Abaikan request internal/asset/preview dsb.
        if ( $this->should_ignore_request() ) return;

        // Jangan rekam jika 404
        if ( is_404() ) return;

        global $wpdb;

        // Update aktivitas untuk hitung "online users" (UPSERT + debounce)
        $this->upsert_online_session();

        $visitor_ip = $this->get_visitor_ip();
        $user_agent = $ua ? sanitize_text_field($ua) : '';

        // Normalisasi URL → simpan path + whitelist query saja
        $raw_uri  = wp_unslash($_SERVER['REQUEST_URI'] ?? '/');
        $page_url = esc_url_raw( $this->normalize_path($raw_uri) );

        // Referer (boleh kosong), simpan domain agregat di tabel referrer
        $referer  = isset($_SERVER['HTTP_REFERER']) ? esc_url_raw( wp_unslash($_SERVER['HTTP_REFERER']) ) : '';

        // Konsisten timezone WP
        $ts         = current_time('timestamp');
        $visit_date = wp_date('Y-m-d', $ts);
        $visit_time = wp_date('H:i:s', $ts);

        // 1) Hit per post dengan throttle
        if ( is_singular() ) {
            $post_id = get_queried_object_id();
            if ( $post_id ) {
                $this->maybe_update_post_hit( $post_id, 'session' );
            }
        }

        // 2) Insert log atomic (hindari duplikasi IP+URL+DATE)
        $inserted = $wpdb->query( $wpdb->prepare(
            "INSERT IGNORE INTO {$this->logs_table}
             (visitor_ip, user_agent, page_url, referer, visit_date, visit_time)
             VALUES (%s, %s, %s, %s, %s, %s)",
            $visitor_ip, $user_agent, $page_url, $referer, $visit_date, $visit_time
        ) );

        if ( $inserted ) {
            // 3) Unique harian atomic via tabel vd_daily_unique
            $is_unique_today = (int) $wpdb->query( $wpdb->prepare(
                "INSERT IGNORE INTO {$this->daily_unique_table} (visitor_ip, stat_date) VALUES (%s, %s)",
                $visitor_ip, $visit_date
            ) );

            // 4) Update agregasi real-time
            $this->update_daily_stats( $visit_date, $is_unique_today ? 1 : 0 );
            $this->update_page_stats(  $page_url,  $visit_date, 1 ); // log pertama utk IP+URL+DATE
            $this->update_referrer_stats( $referer, $visit_date );
        }
    }

    /** ==========================
     *  Online users (via DB table)
     *  ========================== */
    public function crons_add_ten_minutes($schedules) {
        if ( ! isset($schedules['vd_ten_minutes']) ) {
            $schedules['vd_ten_minutes'] = array(
                'interval' => 10 * 60,
                'display'  => __('Every 10 minutes', 'velocity-addons'),
            );
        }
        return $schedules;
    }

    

    private function get_online_ttl() {
        $ttl = (int) apply_filters('vd_online_ttl', $this->online_ttl);
        return $ttl > 0 ? $ttl : 300;
    }

    private function upsert_online_session() {
        global $wpdb;
        $table = $this->online_sessions_table;

        // Pastikan ada session id
        $sid = $this->visitor_id ?: '';
        if ( empty($sid) ) {
            $sid = md5($this->get_visitor_ip());
        }
        $sid = substr(sanitize_text_field($sid), 0, 64);

        $user_id = is_user_logged_in() ? get_current_user_id() : 0;

        // Normalisasi URL yg disimpan
        $raw_uri  = wp_unslash($_SERVER['REQUEST_URI'] ?? '/');
        $current_url = esc_url_raw( $this->normalize_path($raw_uri) );

        // 1) Coba INSERT jika belum ada (session baru)
        $inserted = $wpdb->query( $wpdb->prepare(
            "INSERT IGNORE INTO {$table} (session_id, user_id, current_url, first_seen, last_seen)
             VALUES (%s, %d, %s, NOW(), NOW())",
            $sid, (int) $user_id, $current_url
        ) );

        // 2) Jika sudah ada, update hanya bila melewati debounce
        if ( $inserted === 0 ) {
            $wpdb->query( $wpdb->prepare(
                "UPDATE {$table}
                 SET user_id = %d, current_url = %s, last_seen = NOW()
                 WHERE session_id = %s AND last_seen <= (NOW() - INTERVAL %d SECOND)",
                (int) $user_id, $current_url, $sid, (int) max(1, $this->online_debounce)
            ) );
        }
    }

    public function cleanup_online_sessions() {
        global $wpdb;
        $table = $this->online_sessions_table;
        $ttl = $this->get_online_ttl();
        $wpdb->query( $wpdb->prepare(
            "DELETE FROM {$table} WHERE last_seen < (NOW() - INTERVAL %d SECOND)",
            (int) $ttl
        ) );
    }

    private function get_online_users_count($ttl = null) {
        global $wpdb;
        $table = $this->online_sessions_table;
        if ($ttl === null) {
            $ttl = $this->get_online_ttl();
        }
        $count = $wpdb->get_var( $wpdb->prepare(
            "SELECT COUNT(*) FROM {$table} WHERE last_seen >= (NOW() - INTERVAL %d SECOND)",
            (int) $ttl
        ) );
        return (int) $count;
    }

    /** ============================
     *  Helper: filter request & URL
     *  ============================ */

    /** Abaikan request yang tidak relevan untuk statistik */
    private function should_ignore_request(): bool {
        // Customizer/Preview builder
        if ( function_exists('is_customize_preview') && is_customize_preview() ) return true;

        $raw_uri = wp_unslash($_SERVER['REQUEST_URI'] ?? '/');
        $parts   = wp_parse_url($raw_uri);
        $path    = isset($parts['path']) ? $parts['path'] : '/';

        // 1) Abaikan path internal & asset statis (generik)
        $ignored_path_fragments = apply_filters('vd_ignored_path_fragments', [
            '/wp-content/', '/wp-includes/', '/wp-admin/', '/wp-json', '/xmlrpc.php',
        ]);
        foreach ($ignored_path_fragments as $frag) {
            if (strpos($path, $frag) !== false) return true;
        }

        // /feed, /sitemap*, robots.txt, favicon.ico
        if ( preg_match('~/(?:feed(?:/.*)?|sitemap[^/]*|robots\.txt|favicon\.ico)$~i', $path) ) return true;

        // Ekstensi asset statis (css/js/img/fonts/media/archives/map/json/pdf, dll.)
        $ignored_exts = apply_filters('vd_ignored_file_exts', ['css','js','map','json','png','jpe?g','gif','webp','svg','ico','woff2?','ttf','eot','otf','mp4','mp3','wav','avi','mov','mkv','zip','rar','7z','gz','pdf']);
        $ext_regex = '~\.(' . implode('|', $ignored_exts) . ')$~i';
        if ( preg_match($ext_regex, $path) ) return true;

        // 2) Abaikan GET param internal/editor/preview lainnya
        $get = $_GET;

        $ignore_get_keys = apply_filters('vd_ignore_query_keys', [
            'customize_changeset_uuid','customize_theme','customize_messenger_channel','customize_autosaved',
            'elementor-preview','fl_builder','wp_scrape_key','wp_scrape_nonce',
            'preview','preview_id','preview_nonce',
        ]);
        foreach ($ignore_get_keys as $k) {
            if ( isset($get[$k]) ) return true;
        }
        if ( isset($get['action']) && preg_match('~^(kirki-styles|oembed|heartbeat)$~i', $get['action']) ) {
            return true;
        }

        // (Opsional) abaikan user login yang bisa edit
        // if ( is_user_logged_in() && current_user_can('edit_posts') ) return true;

        // Allow devs extend/override
        return (bool) apply_filters('vd_should_ignore_request', false, $path, $get);
    }

    /** Simpan path + whitelist query param yang penting saja */
    private function normalize_path($uri) {
        $parts = wp_parse_url($uri);
        $path  = $parts['path'] ?? '/';

        // Pertahankan hanya parameter yang mempengaruhi konten
        $whitelist = apply_filters('vd_url_whitelist_params', ['page','paged','s','amp']);

        if (!empty($parts['query'])) {
            parse_str($parts['query'], $q);
            $keep = array_intersect_key($q, array_flip($whitelist));
            if ($keep) {
                ksort($keep);
                $path .= '?' . http_build_query($keep, '', '&', PHP_QUERY_RFC3986);
            }
        }
        return $path;
    }

    /** ============================
     *  Helper: detect IP & bot
     *  ============================ */
    private function get_visitor_ip() {
        $candidates = array('HTTP_CF_CONNECTING_IP','HTTP_X_FORWARDED_FOR','HTTP_CLIENT_IP','REMOTE_ADDR');
        foreach ($candidates as $key) {
            if ( ! empty($_SERVER[$key]) ) {
                $ips = explode(',', $_SERVER[$key]);
                foreach ($ips as $ip) {
                    $ip = trim($ip);
                    if ( filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) ) {
                        $ip = apply_filters('vd_raw_visitor_ip', $ip);
                        $mode = apply_filters('vd_ip_mode', 'plain'); // 'plain'|'masked'|'hash'
                        if ($mode === 'masked' && filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
                            $parts = explode('.', $ip);
                            $ip = $parts[0].'.'.$parts[1].'.'.$parts[2].'.0';
                        } elseif ($mode === 'hash') {
                            $salt = apply_filters('vd_ip_hash_salt', wp_salt('auth'));
                            $ip = hash('sha256', $ip.$salt);
                        }
                        return $ip;
                    }
                }
            }
        }
        $ip = isset($_SERVER['REMOTE_ADDR']) ? sanitize_text_field($_SERVER['REMOTE_ADDR']) : '0.0.0.0';
        $ip = apply_filters('vd_raw_visitor_ip', $ip);
        $mode = apply_filters('vd_ip_mode', 'plain');
        if ($mode === 'masked' && filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
            $parts = explode('.', $ip);
            $ip = $parts[0].'.'.$parts[1].'.'.$parts[2].'.0';
        } elseif ($mode === 'hash') {
            $salt = apply_filters('vd_ip_hash_salt', wp_salt('auth'));
            $ip = hash('sha256', $ip.$salt);
        }
        return $ip;
    }

    private function is_bot($ua) {
        if (empty($ua)) return false;
        return (bool) preg_match('~(bot|spider|crawl|slurp|bingpreview|yandex|ahrefs|facebookexternalhit|bytespider|semrush|duckduckbot|lighthouse|headlesschrome)~i', $ua);
    }

    // Cek apakah sebuah tabel ada di DB
    private function table_exists(string $table_name): bool {
        global $wpdb;
        if ('' === trim($table_name)) return false;
        // exact match terhadap nama tabel lengkap (termasuk prefix)
        $found = $wpdb->get_var( $wpdb->prepare('SHOW TABLES LIKE %s', $table_name) );
        return $found === $table_name;
    }


    /** ======================================
     *  Aggregation updaters (using flags)
     *  ====================================== */
    private function update_daily_stats($visit_date, $is_unique_today) {
        global $wpdb;
        $wpdb->query( $wpdb->prepare(
            "INSERT INTO {$this->daily_stats_table} (stat_date, unique_visitors, total_pageviews)
             VALUES (%s, %d, 1)
             ON DUPLICATE KEY UPDATE
               unique_visitors = unique_visitors + CASE WHEN %d = 1 THEN 1 ELSE 0 END,
               total_pageviews = total_pageviews + 1",
            $visit_date, (int)$is_unique_today, (int)$is_unique_today
        ) );
    }

    private function update_page_stats($page_url, $visit_date, $is_unique_page_today) {
        global $wpdb;
        $wpdb->query( $wpdb->prepare(
            "INSERT INTO {$this->page_stats_table} (page_url, stat_date, unique_visitors, total_views)
             VALUES (%s, %s, %d, 1)
             ON DUPLICATE KEY UPDATE
               unique_visitors = unique_visitors + CASE WHEN %d = 1 THEN 1 ELSE 0 END,
               total_views = total_views + 1",
            $page_url, $visit_date, (int)$is_unique_page_today, (int)$is_unique_page_today
        ) );
    }

    private function update_referrer_stats($referer, $visit_date) {
        if ( empty($referer) ) return;
        global $wpdb;
        $referrer_domain = parse_url($referer, PHP_URL_HOST) ?: $referer;
        $wpdb->query( $wpdb->prepare(
            "INSERT INTO {$this->referrer_stats_table} (referrer_domain, stat_date, total_visits)
             VALUES (%s, %s, 1)
             ON DUPLICATE KEY UPDATE total_visits = total_visits + 1",
            $referrer_domain, $visit_date
        ) );
    }

    /** ===========================
     *  Cron: monthly + cleanup
     *  =========================== */
    public function run_daily_aggregation() {
        $this->aggregate_monthly_stats();
        $this->cleanup_old_logs();
    }

    private function aggregate_monthly_stats() {
        global $wpdb;
        $ts = current_time('timestamp');
        $current_month = (int) wp_date('n', $ts);
        $current_year  = (int) wp_date('Y', $ts);

        $monthly_data = $wpdb->get_row( $wpdb->prepare(
            "SELECT
                SUM(unique_visitors) AS unique_visitors,
                SUM(total_pageviews) AS total_pageviews
             FROM {$this->daily_stats_table}
             WHERE MONTH(stat_date) = %d AND YEAR(stat_date) = %d",
            $current_month, $current_year
        ) );

        if ( $monthly_data ) {
            $wpdb->query( $wpdb->prepare(
                "INSERT INTO {$this->monthly_stats_table}
                    (stat_year, stat_month, unique_visitors, total_pageviews)
                 VALUES (%d, %d, %d, %d)
                 ON DUPLICATE KEY UPDATE
                    unique_visitors = VALUES(unique_visitors),
                    total_pageviews = VALUES(total_pageviews)",
                $current_year, $current_month,
                (int) $monthly_data->unique_visitors,
                (int) $monthly_data->total_pageviews
            ) );
        }
    }

    private function cleanup_old_logs() {
        global $wpdb;
        $wpdb->query(
            "DELETE FROM {$this->logs_table}
             WHERE visit_date < DATE_SUB(CURDATE(), INTERVAL 90 DAY)"
        );
    }

    /** ======================================
     *  Rebuild from raw logs (VAS compatible)
     *  ====================================== */

    /**
     * Rebuild vd_daily_stats from vd_visitor_logs.
     * Menghasilkan 1 baris per tanggal: unique_visitors & total_pageviews.
     * @return int Jumlah baris harian yang diinsert
     */
    public function rebuild_daily_stats() {
        global $wpdb;

        // Kosongkan tabel agregat harian
        $wpdb->query( "TRUNCATE TABLE {$this->daily_stats_table}" );

        // Isi ulang dari log
        $inserted = $wpdb->query(
            "INSERT INTO {$this->daily_stats_table} (stat_date, unique_visitors, total_pageviews)
            SELECT
                visit_date AS stat_date,
                COUNT(DISTINCT visitor_ip) AS unique_visitors,
                COUNT(*) AS total_pageviews
            FROM {$this->logs_table}
            GROUP BY visit_date"
        );

        // Segarkan agregasi bulanan agar konsisten
        $this->aggregate_monthly_stats();

        return (int) $inserted;
    }

    /**
     * Rebuild vd_page_stats dari vd_visitor_logs.
     * Menghasilkan 1 baris per (page_url, tanggal).
     * @return int Jumlah baris halaman-harian yang diinsert
     */
    public function rebuild_page_stats() {
        global $wpdb;

        // Kosongkan tabel agregat per-halaman
        $wpdb->query( "TRUNCATE TABLE {$this->page_stats_table}" );

        // Isi ulang dari log
        $inserted = $wpdb->query(
            "INSERT INTO {$this->page_stats_table} (page_url, stat_date, unique_visitors, total_views)
            SELECT
                page_url,
                visit_date AS stat_date,
                COUNT(DISTINCT visitor_ip) AS unique_visitors,
                COUNT(*) AS total_views
            FROM {$this->logs_table}
            GROUP BY page_url, visit_date"
        );

        return (int) $inserted;
    }


    /** ===========================
     *  Reader APIs
     *  =========================== */
    public function get_daily_stats($days = 30) {
        global $wpdb;
        return $wpdb->get_results( $wpdb->prepare(
            "SELECT stat_date AS visit_date, unique_visitors AS unique_visits, total_pageviews AS total_visits
             FROM {$this->daily_stats_table}
             WHERE stat_date >= DATE_SUB(CURDATE(), INTERVAL %d DAY)
             ORDER BY stat_date ASC",
            (int)$days
        ) );
    }

    public function get_page_stats($days = 30) {
        global $wpdb;
        return $wpdb->get_results( $wpdb->prepare(
            "SELECT page_url, SUM(unique_visitors) AS unique_visitors, SUM(total_views) AS total_views
             FROM {$this->page_stats_table}
             WHERE stat_date >= DATE_SUB(CURDATE(), INTERVAL %d DAY)
             GROUP BY page_url
             ORDER BY total_views DESC
             LIMIT 10",
            (int)$days
        ) );
    }

    public function get_referer_stats($days = 30) {
        global $wpdb;
        return $wpdb->get_results( $wpdb->prepare(
            "SELECT referrer_domain AS referer, SUM(total_visits) AS visits
             FROM {$this->referrer_stats_table}
             WHERE stat_date >= DATE_SUB(CURDATE(), INTERVAL %d DAY)
             GROUP BY referrer_domain
             ORDER BY visits DESC
             LIMIT 10",
            (int)$days
        ) );
    }

    public function get_summary_stats() {
        global $wpdb;

        // Pakai tanggal WP, hindari selisih timezone dengan CURDATE()
        $today_str = wp_date('Y-m-d', current_time('timestamp'));

        $today = $wpdb->get_row( $wpdb->prepare(
            "SELECT unique_visitors, total_pageviews AS total_visits
            FROM {$this->daily_stats_table}
            WHERE stat_date = %s", $today_str
        ));

        $this_week = $wpdb->get_row(
            "SELECT SUM(unique_visitors) AS unique_visitors, SUM(total_pageviews) AS total_visits
            FROM {$this->daily_stats_table}
            WHERE stat_date >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)"
        );

        $this_month = $wpdb->get_row(
            "SELECT SUM(unique_visitors) AS unique_visitors, SUM(total_pageviews) AS total_visits
            FROM {$this->daily_stats_table}
            WHERE stat_date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)"
        );

        // --- ALL TIME ---
        // 1) total dari daily: selalu ada
        $all_from_daily = $wpdb->get_row(
            "SELECT COALESCE(SUM(unique_visitors),0) AS uv, COALESCE(SUM(total_pageviews),0) AS pv
            FROM {$this->daily_stats_table}"
        );

        // 2) jika tabel monthly ada, pakai akumulasi monthly + bulan berjalan
        $all_time = null;
        if ( $this->table_exists($this->monthly_stats_table) ) {
            $all_time = $wpdb->get_row(
                "SELECT
                    (SELECT COALESCE(SUM(unique_visitors),0) FROM {$this->monthly_stats_table}) +
                    (SELECT COALESCE(SUM(unique_visitors),0) FROM {$this->daily_stats_table}
                    WHERE MONTH(stat_date)=MONTH(CURDATE()) AND YEAR(stat_date)=YEAR(CURDATE())) AS unique_visitors,
                    (SELECT COALESCE(SUM(total_pageviews),0) FROM {$this->monthly_stats_table}) +
                    (SELECT COALESCE(SUM(total_pageviews),0) FROM {$this->daily_stats_table}
                    WHERE MONTH(stat_date)=MONTH(CURDATE()) AND YEAR(stat_date)=YEAR(CURDATE())) AS total_visits"
            );
        }

        if ( empty($all_time) ) {
            $all_time = (object) [
                'unique_visitors' => (int) ($all_from_daily->uv ?? 0),
                'total_visits'    => (int) ($all_from_daily->pv ?? 0),
            ];
        }

        // Adjust with legacy baseline options so "all time" matches old plugin totals
        $base_visits = (int) get_option('vd_legacy_total_visits', 0);
        $base_unique = (int) get_option('vd_legacy_total_unique', 0);
        $last_date   = (string) get_option('vd_legacy_last_date', '');
        if ( $base_visits > 0 || $base_unique > 0 ) {
            if ( ! empty($last_date) ) {
                $after = $wpdb->get_row( $wpdb->prepare(
                    "SELECT COALESCE(SUM(unique_visitors),0) AS uv, COALESCE(SUM(total_pageviews),0) AS pv
                     FROM {$this->daily_stats_table}
                     WHERE stat_date > %s",
                    $last_date
                ) );
                $all_time->total_visits    = (int) $base_visits + (int) ($after->pv ?? 0);
                $all_time->unique_visitors = (int) $base_unique + (int) ($after->uv ?? 0);
            } else {
                // fallback: ensure baseline at minimum
                $all_time->total_visits    = max( (int)$all_time->total_visits, $base_visits );
                $all_time->unique_visitors = max( (int)$all_time->unique_visitors, $base_unique );
            }
        }

        return array(
            'today'      => $today      ?: (object) ['unique_visitors'=>0, 'total_visits'=>0],
            'this_week'  => $this_week  ?: (object) ['unique_visitors'=>0, 'total_visits'=>0],
            'this_month' => $this_month ?: (object) ['unique_visitors'=>0, 'total_visits'=>0],
            'all_time'   => $all_time   ?: (object) ['unique_visitors'=>0, 'total_visits'=>0],
        );
    }


    /** ===========================
     *  Shortcodes (Bootstrap 5)
     *  =========================== */
    /**
     * Shortcode: [velocity-statistics style="list|inline" show="all|today|total" label_today_visits="..." label_today_visitors="..." label_total_visits="..." label_total_visitors="..."]
     * Menampilkan ringkasan statistik kunjungan dalam tampilan list atau inline dengan label yang dapat disesuaikan.
     */
    public function statistics_shortcode($atts) {
        $atts = shortcode_atts(array(
            'style'                => 'list',   // list|inline
            'show'                 => 'all',    // all|today|total
            'with_online'          => '1',     // default tampilkan user online
            'label_today_visits'   => __('Kunjungan Hari Ini', 'velocity-addons'),
            'label_today_visitors' => __('Pengunjung Hari Ini', 'velocity-addons'),
            'label_total_visits'   => __('Total Kunjungan', 'velocity-addons'),
            'label_total_visitors' => __('Total Pengunjung', 'velocity-addons'),
            'label_online'         => __('Pengunjung Online', 'velocity-addons'),
        ), $atts, 'velocity-statistics');

        // Sanitasi label
        $label_keys = array(
            'label_today_visits',
            'label_today_visitors',
            'label_total_visits',
            'label_total_visitors',
            'label_online',
        );
        foreach ($label_keys as $key) {
            $atts[$key] = sanitize_text_field($atts[$key]);
        }

        // Ambil data statistik
        $stats = $this->get_summary_stats();

        // Kumpulkan item yang akan ditampilkan
        $items = array();
        if ($atts['show'] === 'all' || $atts['show'] === 'today') {
            $items[] = array(
                'label' => $atts['label_today_visits'],
                'value' => number_format_i18n((int) ($stats['today']->total_visits ?? 0)),
            );
            $items[] = array(
                'label' => $atts['label_today_visitors'],
                'value' => number_format_i18n((int) ($stats['today']->unique_visitors ?? 0)),
            );
        }
        if ($atts['show'] === 'all' || $atts['show'] === 'total') {
            $items[] = array(
                'label' => $atts['label_total_visits'],
                'value' => number_format_i18n((int) ($stats['all_time']->total_visits ?? 0)),
            );
            $items[] = array(
                'label' => $atts['label_total_visitors'],
                'value' => number_format_i18n((int) ($stats['all_time']->unique_visitors ?? 0)),
            );
        }

        // Online users (opsional)
        $with_online = in_array(strtolower((string)$atts['with_online']), array('1','true','yes','on'), true);
        if ( $with_online ) {
            $items[] = array(
                'label' => $atts['label_online'],
                'value' => number_format_i18n( (int) $this->get_online_users_count() ),
            );
        }

        ob_start();

        // ======== STYLE INLINE ========
        if ($atts['style'] === 'inline') {
            $parts = array();
            foreach ($items as $it) {
                $parts[] = esc_html($it['label']) . ': ' . esc_html($it['value']);
            }
            echo '<div class="velocity-inline-stats">' . implode(' | ', $parts) . '</div>';

        // ======== STYLE LIST (default & fallback) ========
        } elseif ($atts['style'] === 'list' || empty($atts['style'])) {
            echo '<div class="velocity-list-stats">';
            foreach ($items as $it) {
                echo '<div class="d-flex justify-content-between align-items-center border-bottom py-2">';
                echo '<span>' . esc_html($it['label']) . '</span>';
                echo '<span class="fw-bold">' . esc_html($it['value']) . '</span>';
                echo '</div>';
            }
            echo '</div>';
        }

        return ob_get_clean();
    }


    /**
     * Shortcode: [velocity-hits post_id="123" format="compact|number" before="" after=" views" class="velocity-hits-count"]
     * Menampilkan hit posting (default ke post aktif) dengan opsi format angka, prefix/suffix, dan class custom.
     */
    public function shortcode_post_hit($atts) {
        $atts = shortcode_atts(array(
            'post_id' => 0,
            'format'  => 'compact', // number|compact
            'before'  => '',
            'after'   => '',
            'class'   => 'velocity-hits-count',
        ), $atts, 'velocity-hits');

        $post_id = absint($atts['post_id']);
        if ( ! $post_id ) {
            $post_id = get_the_ID();
        }
        if ( ! $post_id ) {
            return '';
        }

        $raw = get_post_meta($post_id, 'hit', true);
        $hit = is_numeric($raw) ? (int) $raw : 0;

        $display = ($atts['format'] === 'compact') ? $this->compact_number($hit) : number_format_i18n($hit);

        $html  = '';
        $html .= $atts['before'];
        $html .= '<span class="' . esc_attr($atts['class']) . '" data-post="' . esc_attr($post_id) . '">' . esc_html($display) . '</span>';
        $html .= $atts['after'];

        return $html;
    }

    /** Helper: format 1.2K / 3.4M */
    private function compact_number($n) {
        $n = (int) $n;
        if ( $n >= 1000000000 ) return round($n/1000000000, 1) . 'B';
        if ( $n >= 1000000 )    return round($n/1000000, 1) . 'M';
        if ( $n >= 1000 )       return round($n/1000, 1) . 'K';
        return (string) $n;
    }

    

    /** ======================================
     *  Popular posts: cookie + throttle + SQL
     *  ====================================== */
    public function ensure_visitor_id() {
        if ( ! empty($_COOKIE['vd_sid']) ) {
            $this->visitor_id = sanitize_text_field($_COOKIE['vd_sid']);
            return;
        }
        $this->visitor_id = wp_generate_uuid4();
        @setcookie('vd_sid', $this->visitor_id, array(
            'expires'  => time() + 30 * DAY_IN_SECONDS,
            'path'     => COOKIEPATH ?: '/',
            'domain'   => COOKIE_DOMAIN,
            'secure'   => is_ssl(),
            'httponly' => true,
            'samesite' => 'Lax',
        ));
        $_COOKIE['vd_sid'] = $this->visitor_id;
    }

    private function maybe_update_post_hit($post_id, $mode = 'session') {
        if ( ! $post_id ) return;

        $window = (int) apply_filters('vd_hit_window', $this->hit_window);
        $window = $window > 0 ? $window : 240;

        switch ($mode) {
            case 'unique_day':
                $key = sprintf('vd_hitd_%d_%s_%s', $post_id, md5($this->visitor_id ?: $this->get_visitor_ip()), wp_date('Ymd', current_time('timestamp')));
                if ( false === get_transient($key) ) {
                    $this->increment_post_meta_atomic($post_id, 'hit', 1);
                    $ttl = DAY_IN_SECONDS - ( current_time('timestamp') - strtotime(wp_date('Y-m-d 00:00:00', current_time('timestamp'))) );
                    set_transient($key, 1, max(60, $ttl));
                }
                break;

            case 'always':
                $this->increment_post_meta_atomic($post_id, 'hit', 1);
                break;

            case 'session':
            default:
                $sid = $this->visitor_id ?: md5($this->get_visitor_ip());
                $key = sprintf('vd_hit_%d_%s', $post_id, md5($sid));
                if ( false === get_transient($key) ) {
                    $this->increment_post_meta_atomic($post_id, 'hit', 1);
                    set_transient($key, 1, $window);
                }
                break;
        }
    }

    private function increment_post_meta_atomic($post_id, $meta_key = 'hit', $by = 1) {
        global $wpdb;

        // Pastikan baris meta ada; jika belum, buat 0 (unique add)
        $exists = $wpdb->get_var( $wpdb->prepare(
            "SELECT meta_id FROM {$wpdb->postmeta} WHERE post_id=%d AND meta_key=%s LIMIT 1",
            $post_id, $meta_key
        ) );
        if ( ! $exists ) {
            add_post_meta($post_id, $meta_key, 0, true);
        }

        // Increment atomik langsung di SQL (anti race condition)
        $wpdb->query( $wpdb->prepare(
            "UPDATE {$wpdb->postmeta}
             SET meta_value = IF(meta_value REGEXP '^[0-9]+$', meta_value + %d, %d)
             WHERE post_id=%d AND meta_key=%s",
            (int)$by, (int)$by, (int)$post_id, $meta_key
        ) );

        // Sinkronkan object cache
        wp_cache_delete($post_id, 'post_meta');
    }
}

endif;

/** Inisialisasi kelas setelah semua plugin siap (hindari output saat aktivasi) */
add_action('plugins_loaded', function () {
    new Velocity_Addons_Statistic();
});

