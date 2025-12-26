<?php

/**
 * Display Dashboard Menu in the WordPress admin panel
 *
 * @link       https://velocitydeveloper.com
 * @since      1.0.0
 *
 * @package    Velocity_Addons
 * @subpackage Velocity_Addons/includes
 */

class Velocity_Addons_Dashboard
{

    public function __construct() {}

    public static function render_dashboard_page()
    {
        global $wpdb;

        // Fetch 30 days stats
        $table_name = $wpdb->prefix . 'vd_daily_stats';
        // Check if table exists to avoid errors on fresh install
        $table_exists = $wpdb->get_var("SHOW TABLES LIKE '$table_name'") === $table_name;

        $labels = [];
        $visitors = [];
        $pageviews = [];

        if ($table_exists) {
            $results = $wpdb->get_results("
                SELECT stat_date, unique_visitors, total_pageviews 
                FROM $table_name 
                WHERE stat_date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY) 
                ORDER BY stat_date ASC
            ");

            if ($results) {
                foreach ($results as $row) {
                    $labels[] = date('d M', strtotime($row->stat_date));
                    $visitors[] = (int)$row->unique_visitors;
                    $pageviews[] = (int)$row->total_pageviews;
                }
            }
        }

        $site_title    = get_bloginfo('name');
        $home_url      = home_url('/');
        $admin_email   = get_option('admin_email');
        $wp_version    = get_bloginfo('version');
        $theme         = wp_get_theme();
        $theme_name    = $theme ? $theme->get('Name') : '';
        $theme_version = $theme ? $theme->get('Version') : '';
        $php_version   = PHP_VERSION;
        $timezone      = function_exists('wp_timezone_string') ? wp_timezone_string() : get_option('timezone_string');
        $server_time   = wp_date('d M Y H:i', current_time('timestamp'));
        $active_plugins = count((array) get_option('active_plugins', array()));
        $users_count    = function_exists('count_users') ? count_users() : array('total_users' => 0);
        $total_users    = isset($users_count['total_users']) ? (int) $users_count['total_users'] : 0;

        // Menghitung jumlah post, page, dan media
        $post_count = wp_count_posts()->publish;
        $page_count = wp_count_posts('page')->publish;
        $media_count = wp_count_posts('attachment')->inherit;
?>
        <div class="velocity-dashboard-wrapper">


            <div class="vd-header">
                <h1 class="vd-title">Dashboard</h1>
                <p class="vd-subtitle">Ringkasan status dan informasi Velocity Addons.</p>
            </div>

            <div class="vd-grid">
                <!-- Post Card -->
                <div class="vd-card">
                    <div class="vd-card-header">
                        <div class="vd-icon-wrapper vd-icon-blue">
                            <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" fill="currentColor" viewBox="0 0 16 16">
                                <path d="M4 3.5a.5.5 0 0 1 .5-.5h5a.5.5 0 0 1 0 1h-5a.5.5 0 0 1-.5-.5m0 2a.5.5 0 0 1 .5-.5h7a.5.5 0 0 1 .5.5v8a.5.5 0 0 1-.5.5h-7a.5.5 0 0 1-.5-.5z" />
                                <path d="M2 2a2 2 0 0 1 2-2h8a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2zm10-1H4a1 1 0 0 0-1 1v12a1 1 0 0 0 1 1h8a1 1 0 0 0 1-1V2a1 1 0 0 0-1-1" />
                            </svg>
                        </div>
                        <div class="vd-stat-content">
                            <div class="vd-stat-label">Jumlah Post</div>
                            <div class="vd-stat-value"><?php echo number_format_i18n($post_count); ?></div>
                        </div>
                    </div>
                    <div class="vd-stat-desc">Total post yang telah dipublikasikan.</div>
                </div>

                <!-- Page Card -->
                <div class="vd-card">
                    <div class="vd-card-header">
                        <div class="vd-icon-wrapper vd-icon-red">
                            <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" fill="currentColor" viewBox="0 0 16 16">
                                <path d="M14 4.5V14a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V2a2 2 0 0 1 2-2h5.5zm-3 0A1.5 1.5 0 0 1 9.5 3V1H4a1 1 0 0 0-1 1v12a1 1 0 0 0 1 1h8a1 1 0 0 0 1-1V4.5z" />
                            </svg>
                        </div>
                        <div class="vd-stat-content">
                            <div class="vd-stat-label">Jumlah Page</div>
                            <div class="vd-stat-value"><?php echo number_format_i18n($page_count); ?></div>
                        </div>
                    </div>
                    <div class="vd-stat-desc">Total halaman yang telah dibuat.</div>
                </div>

                <!-- Media Card -->
                <div class="vd-card">
                    <div class="vd-card-header">
                        <div class="vd-icon-wrapper vd-icon-cyan">
                            <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" fill="currentColor" viewBox="0 0 16 16">
                                <path d="M15 12a1 1 0 0 1-1 1H2a1 1 0 0 1-1-1V6a1 1 0 0 1 1-1h1.172a3 3 0 0 0 2.12-.879l.83-.828A1 1 0 0 1 6.827 3h2.344a1 1 0 0 1 .707.293l.828.828A3 3 0 0 0 12.828 5H14a1 1 0 0 1 1 1zM2 4a2 2 0 0 0-2 2v6a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V6a2 2 0 0 0-2-2h-1.172a2 2 0 0 1-1.414-.586l-.828-.828A2 2 0 0 0 9.172 2H6.828a2 2 0 0 0-1.414.586l-.828.828A2 2 0 0 1 3.172 4z" />
                                <path d="M8 11a2.5 2.5 0 1 1 0-5 2.5 2.5 0 0 1 0 5m0 1a3.5 3.5 0 1 0 0-7 3.5 3.5 0 0 0 0 7M3 6.5a.5.5 0 1 1-1 0 .5.5 0 0 1 1 0" />
                            </svg>
                        </div>
                        <div class="vd-stat-content">
                            <div class="vd-stat-label">Jumlah Media</div>
                            <div class="vd-stat-value"><?php echo number_format_i18n($media_count); ?></div>
                        </div>
                    </div>
                    <div class="vd-stat-desc">Total file media yang diupload.</div>
                </div>
            </div>

            <div class="vd-grid-2">
                <div class="vd-section">
                    <div class="vd-section-header" style="padding: 1.25rem 1.5rem; border-bottom: 1px solid #e5e7eb; background-color: #f9fafb; display:flex; align-items:center; justify-content:space-between;">
                        <h3 style="margin:0; font-size:1.1rem; color:#374151;">Statistik Pengunjung (30 Hari Terakhir)</h3>
                        <button id="vd-seed-statistics" class="button button-primary" data-nonce="<?php echo esc_attr(wp_create_nonce('vd_seed_statistics')); ?>">Seed Statistik</button>
                    </div>
                    <div class="vd-section-body" style="position: relative; height: 350px;">
                        <canvas id="velocityVisitorChart"></canvas>
                        <div id="seed-message"></div>
                    </div>
                </div>
                <div class="vd-section">
                    <div class="vd-section-header" style="padding: 1.25rem 1.5rem; border-bottom: 1px solid #e5e7eb; background-color: #f9fafb;">
                        <h3 style="margin:0; font-size:1.1rem; color:#374151;">Info Web</h3>
                    </div>
                    <div class="vd-section-body">
                        <div class="vd-kv"><span class="vd-kv-label">Nama Situs</span><span class="vd-kv-value"><?php echo esc_html($site_title); ?></span></div>
                        <div class="vd-kv"><span class="vd-kv-label">URL</span><span class="vd-kv-value"><a href="<?php echo esc_url($home_url); ?>" target="_blank"><?php echo esc_html($home_url); ?></a></span></div>
                        <div class="vd-kv"><span class="vd-kv-label">Email Admin</span><span class="vd-kv-value"><?php echo esc_html($admin_email); ?></span></div>
                        <div class="vd-kv"><span class="vd-kv-label">WordPress</span><span class="vd-kv-value"><?php echo esc_html($wp_version); ?></span></div>
                        <div class="vd-kv"><span class="vd-kv-label">Tema Aktif</span><span class="vd-kv-value"><?php echo esc_html($theme_name . ($theme_version ? ' v' . $theme_version : '')); ?></span></div>
                        <div class="vd-kv"><span class="vd-kv-label">PHP</span><span class="vd-kv-value"><?php echo esc_html($php_version); ?></span></div>
                        <div class="vd-kv"><span class="vd-kv-label">Timezone</span><span class="vd-kv-value"><?php echo esc_html($timezone ?: 'UTC'); ?></span></div>
                        <div class="vd-kv"><span class="vd-kv-label">Waktu Server</span><span class="vd-kv-value"><?php echo esc_html($server_time); ?></span></div>
                        <div class="vd-kv"><span class="vd-kv-label">Plugin Aktif</span><span class="vd-kv-value"><?php echo number_format_i18n($active_plugins); ?></span></div>
                        <div class="vd-kv"><span class="vd-kv-label">Total User</span><span class="vd-kv-value"><?php echo number_format_i18n($total_users); ?></span></div>
                    </div>
                </div>
            </div>

            <script>
                window.velocityDashboardData = {
                    labels: <?php echo json_encode($labels); ?>,
                    visitors: <?php echo json_encode($visitors); ?>,
                    pageviews: <?php echo json_encode($pageviews); ?>
                };
            </script>

            <div class="vd-grid-2">
                <div class="vd-section">
                    <div class="vd-section-header" style="padding: 1.25rem 1.5rem; border-bottom: 1px solid #e5e7eb; background-color: #f9fafb;">
                        <h3 style="margin:0; font-size:1.1rem; color:#374151;">List Shortcode</h3>
                    </div>
                    <div class="vd-section-body">
                        <h6>#Breadcrumbs</h6>
                        <p><span class="vd-code">[vd-breadcrumbs]</span></p>

                        <h6>#Velocity Recaptcha</h6>
                        <p><span class="vd-code">[velocity_captcha]</span></p>

                        <h6>#Share Post</h6>
                        <p><span class="vd-code">[velocity-sharepost title='' label_share='' platforms='']</span></p>
                        <ul class="vd-list">
                            <li><strong>title</strong>: nama label share. Share this post</li>
                            <li><strong>label_share</strong>: tampilkan label share. true/false</li>
                            <li><strong>platforms</strong>: platform berbagi. facebook/twitter/whatsapp/telegram/email</li>
                        </ul>

                        <h6>#Velocity Statistics</h6>
                        <p><span class="vd-code">[velocity-statistics style='' show='' with_online='']</span></p>
                        <ul class="vd-list">
                            <li><strong>style</strong>: pilih tampilan statistik. list/inline (default list)</li>
                            <li><strong>show</strong>: filter data yang ditampilkan. all/today/total (default all)</li>
                            <li><strong>with_online</strong>: tampilkan/sembunyikan baris <em>Pengunjung Online</em>. 1/0 (default 1)</li>
                            <li><strong>label_today_visits</strong>: ganti label "Kunjungan Hari Ini" (opsional)</li>
                            <li><strong>label_today_visitors</strong>: ganti label "Pengunjung Hari Ini" (opsional)</li>
                            <li><strong>label_total_visits</strong>: ganti label "Total Kunjungan" (opsional)</li>
                            <li><strong>label_total_visitors</strong>: ganti label "Total Pengunjung" (opsional)</li>
                            <li><strong>label_online</strong>: ganti label "Pengunjung Online" (opsional)</li>
                        </ul>

                        <h6>#Velocity Hits</h6>
                        <p><span class="vd-code">[velocity-hits post_id='' format='' before='' after='' class='']</span></p>
                        <ul class="vd-list">
                            <li><strong>post_id</strong>: ID posting (opsional; default get_the_ID())</li>
                            <li><strong>format</strong>: format angka. compact/number (default compact)</li>
                            <li><strong>before</strong>: teks/HTML sebelum angka hit</li>
                            <li><strong>after</strong>: teks/HTML setelah angka hit</li>
                            <li><strong>class</strong>: kelas CSS untuk elemen angka hit</li>
                        </ul>

                        <h6>#VD Gallery</h6>
                        <p><span class="vd-code">[vdgallery id='']</span></p>

                        <h6>#VD Gallery Slide</h6>
                        <p><span class="vd-code">[vdgalleryslide id='']</span></p>
                    </div>
                </div>
                <div class="vd-section">
                    <div class="vd-section-header" style="padding: 1.25rem 1.5rem; border-bottom: 1px solid #e5e7eb; background-color: #f9fafb;">
                        <h3 style="margin:0; font-size:1.1rem; color:#374151;">QC List Check</h3>
                    </div>
                    <div class="vd-section-body">
                        <?php Velocity_Addons_Maintenance_Mode::qc_maintenance_list(); ?>
                    </div>
                </div>
            </div>

            <div class="vd-footer">
                <small>Powered by <a href="https://velocitydeveloper.com/" target="_blank">velocitydeveloper.com</a></small>
            </div>
        </div>
<?php
    }
}

// Inisialisasi class Velocity_Addons_Dashboard
$velocity_news = new Velocity_Addons_Dashboard();
