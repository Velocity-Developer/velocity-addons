<?php

/**
 * Register all actions and filters for the plugin
 *
 * @link       https://velocitydeveloper.com
 * @since      1.0.0
 *
 * @package    Velocity_Addons
 * @subpackage Velocity_Addons/includes
 */

/**
 * Register all actions and filters for the plugin.
 *
 * Maintain a list of all hooks that are registered throughout
 * the plugin, and register them with the WordPress API. Call the
 * run function to execute the list of actions and filters.
 *
 * @package    Velocity_Addons
 * @subpackage Velocity_Addons/includes
 * @author     Velocity <bantuanvelocity@gmail.com>
 */

class Velocity_Addons_Maintenance_Mode
{
    public function __construct()
    {
        if (get_option('maintenance_mode')) {
            // Jalankan di tahap template_redirect (front-end) agar tak ganggu bootstrap editor/admin
            add_action('template_redirect', [$this, 'check_maintenance_mode'], 0);
            add_action('admin_notices', [self::class, 'qc_maintenance']);
        }
    }

    public function check_maintenance_mode()
    {
        // 1) Selalu lolos untuk konteks non-frontend/editor
        if (is_admin()) return;
        if (wp_doing_ajax()) return;
        if (defined('REST_REQUEST') && REST_REQUEST) return;
        if (is_customize_preview() || is_preview()) return;

        // 2) Lolos untuk user yang sedang kerja (editor/builder)
        if (is_user_logged_in() && current_user_can('edit_posts')) return;

        // 3) Jika mau whitelist halaman tertentu (tetap boleh), contoh 'myaccount'
        if (is_page('myaccount')) return;

        $opt     = get_option('maintenance_mode_data', []);
        $hd      = !empty($opt['header']) ? $opt['header'] : 'Maintenance Mode';
        $bd      = !empty($opt['body']) ? $opt['body'] : 'We are currently performing maintenance. Please check back later.';
        $bg_id   = !empty($opt['background']) ? absint($opt['background']) : 0;
        $bg_url  = $bg_id ? wp_get_attachment_image_url($bg_id, 'full') : '';

        $heading        = esc_html($hd);
        $body_content   = wpautop(wp_kses_post($bd));

        // Setup background style
        $style_bg = $bg_url
            ? "background: url('" . esc_url($bg_url) . "') no-repeat center center fixed; background-size: cover;"
            : "background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%);";

        // Set Headers for 503 Service Unavailable
        if (!headers_sent()) {
            header('HTTP/1.1 503 Service Unavailable');
            header('Status: 503 Service Unavailable');
            header('Retry-After: 3600');
        }
?>
        <!DOCTYPE html>
        <html lang="<?php echo get_locale(); ?>">

        <head>
            <meta charset="<?php bloginfo('charset'); ?>">
            <meta name="viewport" content="width=device-width, initial-scale=1">
            <title><?php echo $heading; ?> - <?php bloginfo('name'); ?></title>
            <style>
                * {
                    box-sizing: border-box;
                }

                body {
                    <?php echo $style_bg; ?>min-height: 100vh;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    font-family: system-ui, -apple-system, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
                    color: #fff;
                    margin: 0;
                    padding: 20px;
                }

                .maintenance-card {
                    background: rgba(255, 255, 255, 0.98);
                    color: #1e293b;
                    border-radius: 16px;
                    box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
                    padding: 3rem;
                    max-width: 600px;
                    width: 100%;
                    position: relative;
                    overflow: hidden;
                    text-align: center;
                }

                .maintenance-card::before {
                    content: '';
                    position: absolute;
                    top: 0;
                    left: 0;
                    width: 100%;
                    height: 6px;
                    background: linear-gradient(90deg, #3b82f6, #8b5cf6);
                }

                .maintenance-card h1 {
                    font-weight: 800;
                    color: #0f172a;
                    margin: 0 0 1.5rem 0;
                    font-size: 2rem;
                    line-height: 1.2;
                }

                .maintenance-card .content {
                    font-size: 1.1rem;
                    color: #475569;
                    line-height: 1.7;
                    margin-bottom: 2rem;
                }

                .maintenance-card .content p {
                    margin-top: 0;
                    margin-bottom: 1rem;
                }

                .maintenance-card .content p:last-child {
                    margin-bottom: 0;
                }

                .btn-reload {
                    background-color: #0f172a;
                    color: #fff;
                    padding: 0.75rem 2rem;
                    border-radius: 50px;
                    text-decoration: none;
                    font-weight: 600;
                    transition: all 0.3s ease;
                    display: inline-block;
                    cursor: pointer;
                    border: none;
                    font-size: 1rem;
                }

                .btn-reload:hover {
                    background-color: #334155;
                    color: #fff;
                    transform: translateY(-2px);
                    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
                }

                @media (max-width: 640px) {
                    .maintenance-card {
                        padding: 2rem;
                    }

                    .maintenance-card h1 {
                        font-size: 1.75rem;
                    }
                }
            </style>
        </head>

        <body>
            <div class="maintenance-card">
                <h1><?php echo $heading; ?></h1>
                <div class="content">
                    <?php echo $body_content; ?>
                </div>
                <a href="<?php echo esc_url(home_url()); ?>" class="btn-reload">
                    Muat Ulang
                </a>
            </div>
        </body>

        </html>
<?php
        exit();
    }

    public static function qc_maintenance()
    {
        echo '<div class="notice notice-warning notice-alt">';
        echo self::check_permalink_settings();
        echo self::check_site_icon();
        echo self::check_recaptcha();
        echo self::check_seo();
        echo self::check_domain_extension();
        echo self::check_installed_plugins();
        echo '</div>';
    }

    public static function qc_maintenance_list()
    {
        $parts = array(
            self::check_permalink_settings(),
            self::check_site_icon(),
            self::check_recaptcha(),
            self::check_seo(),
            self::check_domain_extension(),
            self::check_installed_plugins(),
        );
        $items = array();
        foreach ($parts as $html) {
            $html = trim((string) $html);
            if ($html === '') {
                continue;
            }
            if (preg_match_all('~<p>(.*?)</p>~is', $html, $m)) {
                foreach ($m[1] as $segment) {
                    $items[] = '<li>' . $segment . '</li>';
                }
            } else {
                $items[] = '<li>' . $html . '</li>';
            }
        }
        if (empty($items)) {
            echo '<p>Tidak ada item QC yang perlu ditampilkan.</p>';
            return;
        }
        echo '<ul class="vd-list">' . implode('', $items) . '</ul>';
    }

    public static function check_domain_extension()
    {
        ob_start();
        $site_url = get_site_url();
        $domain   = parse_url($site_url, PHP_URL_HOST);
        $domain_parts = explode('.', (string) $domain);
        $extension    = array_pop($domain_parts);
        $sub_extension = array_pop($domain_parts);
        $valid_extensions = ['go.id', 'desa.id', 'sch.id', 'ac.id'];

        if (in_array($sub_extension . '.' . $extension, $valid_extensions, true)) {
            echo '<p>Setting Desain By Velocity => Open New Tab. Linknya Di Warna Sesuai Background, Rata Kiri (Pojok), Saat Hover Jangan Icon Tangan Tapi Icon Panah Sprti Pada Saat Tanpa Hover.</p>';
        }
        echo '<p>Pastikan Copy Right Sesuai Tahun!</p>';
        return ob_get_clean();
    }

    public static function check_permalink_settings()
    {
        ob_start();
        $permalinks  = get_option('permalink_structure');
        $linksetting = admin_url('options-permalink.php');
        if (empty($permalinks) || $permalinks !== '/%category%/%postname%/') {
            echo '<p>Peringatan: Permalink belum disetting. Silakan setting <a href="' . $linksetting . '"><b>disini.</b></a></p>';
        }
        return ob_get_clean();
    }

    public static function check_site_icon()
    {
        ob_start();
        $site_icon  = get_site_icon_url();
        $linksetting = admin_url('options-general.php');
        if (empty($site_icon)) {
            echo '<p>Peringatan: Favicon belum disetting. Silakan setting <a href="' . $linksetting . '"><b>disini.</b></a></p>';
        }
        return ob_get_clean();
    }

    public static function check_recaptcha()
    {
        ob_start();
        $linksetting     = admin_url('admin.php?page=admin_velocity_addons');
        $check_recaptcha = get_option('captcha_velocity');
        $sitekey  = $check_recaptcha['sitekey']  ?? '';
        $secretkey = $check_recaptcha['secretkey'] ?? '';
        if (empty($sitekey) || empty($secretkey)) {
            echo '<p>Peringatan: Recaptcha belum disetting. Silakan setting <a href="' . $linksetting . '"><b>disini.</b></a></p>';
        }
        return ob_get_clean();
    }

    public static function check_seo()
    {
        ob_start();
        $linksetting  = admin_url('admin.php?page=velocity_seo_settings');
        $home_keywords = get_option('home_keywords');
        $share_image  = get_option('share_image');
        if (empty($home_keywords) || empty($share_image)) {
            echo '<p>Peringatan: SEO belum disetting. Silakan setting <a href="' . $linksetting . '"><b>disini.</b></a></p>';
        }
        return ob_get_clean();
    }

    public static function check_installed_plugins()
    {
        ob_start();
        if (!function_exists('get_plugins')) {
            require_once ABSPATH . 'wp-admin/includes/plugin.php';
        }
        $plugins            = get_plugins();
        $auto_update_plugins = get_site_option('auto_update_plugins', []);
        $excluded_plugins = [
            'bb-ultimate-addon/bb-ultimate-addon.php',
            'velocity-toko/velocity-toko.php',
            'velocity-expedisi/velocity-expedisi.php',
            'velocity-donasi/velocity-donasi.php',
            'velocity-produk/velocity-produk.php',
            'velocity-addons/velocity-addons.php',
            'vd-gallery/vd-gallery.php',
            'velocity-toko-kursus/velocity-kursus.php',
            'custom-plugin/custom-plugin.php',
        ];
        foreach ($plugins as $plugin_file => $plugin_data) {
            if (!in_array($plugin_file, $excluded_plugins, true) && !in_array($plugin_file, $auto_update_plugins, true)) {
                echo '<p>' . esc_html($plugin_data['Name']) . ' belum diaktifkan untuk pembaruan otomatis.</p>';
            }
        }
        return ob_get_clean();
    }
}

// Init
$velocity_maintenance_mode = new Velocity_Addons_Maintenance_Mode();
