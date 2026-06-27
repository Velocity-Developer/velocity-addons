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
        $license_opt = get_option('velocity_license');
        $license_active = is_array($license_opt) && isset($license_opt['status']) && $license_opt['status'] === 'active';
        $license_exp = is_array($license_opt) && isset($license_opt['expire_date']) ? $license_opt['expire_date'] : '';
        $summary_stats = null;
        $online_count  = null;
        if (class_exists('Velocity_Addons_Statistic')) {
            $stats_handler = new Velocity_Addons_Statistic();
            $summary_stats = $stats_handler->get_summary_stats();
            if (method_exists($stats_handler, 'online_users_count')) {
                $online_count = (int) $stats_handler->online_users_count();
            }
        }
        $draft_count = (int) (wp_count_posts()->draft ?? 0);
        $moderated_comments = (int) (wp_count_comments()->moderated ?? 0);
?>
        <div class="velocity-dashboard-wrapper">
            <?php Velocity_Addons_Admin_Navigation::render(); ?>

            

            <div class="vd-grid">
                <div class="vd-card">
                    <div class="vd-stat-label">Posts</div>
                    <div class="vd-stat-value"><?php echo number_format_i18n($post_count); ?></div>
                </div>
                <div class="vd-card">
                    <div class="vd-stat-label">Pages</div>
                    <div class="vd-stat-value"><?php echo number_format_i18n($page_count); ?></div>
                </div>
                <div class="vd-card">
                    <div class="vd-stat-label">Media</div>
                    <div class="vd-stat-value"><?php echo number_format_i18n($media_count); ?></div>
                </div>
                <div class="vd-card">
                    <div class="vd-stat-label">Visitors Today</div>
                    <div class="vd-stat-value"><?php echo number_format_i18n((int) ($summary_stats['today']->unique_visitors ?? 0)); ?></div>
                </div>
                <div class="vd-card">
                    <div class="vd-stat-label">Online Now</div>
                    <div class="vd-stat-value"><?php echo number_format_i18n((int) ($online_count ?? 0)); ?></div>
                </div>
                <div class="vd-card">
                    <div class="vd-stat-label">Pageviews Today</div>
                    <div class="vd-stat-value"><?php echo number_format_i18n((int) ($summary_stats['today']->total_visits ?? 0)); ?></div>
                </div>
            </div>

            <div class="vd-grid-2">
                <div class="vd-section">
                    <div class="vd-section-header" style="padding: 1.25rem 1.5rem; border-bottom: 1px solid #e5e7eb; background-color: #f9fafb; display:flex; align-items:center; justify-content:space-between;">
                        <h3 style="margin:0; font-size:1.1rem; color:#374151;">Statistik Pengunjung (30 Hari Terakhir)</h3>

                    </div>
                    <div class="vd-section-body" style="position: relative; height: 350px;">
                        <?php if (empty($labels)): ?>
                            <div style="position:absolute;inset:0;display:flex;align-items:center;justify-content:center;color:#6b7280">Belum ada data statistik</div>
                        <?php endif; ?>
                        <canvas id="velocityVisitorChart"></canvas>
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


        </div>
<?php
    }
}

// Inisialisasi class Velocity_Addons_Dashboard
$velocity_news = new Velocity_Addons_Dashboard();
