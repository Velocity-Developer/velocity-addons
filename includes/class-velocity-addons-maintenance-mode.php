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

                :root {
                    --maintenance-text: #111111;
                    --maintenance-muted: #666666;
                    --maintenance-border: #e7e7e7;
                    --maintenance-button: #111111;
                    --maintenance-button-hover: #000000;
                }

                html,
                body {
                    min-height: 100%;
                }

                body {
                    margin: 0;
                    min-height: 100vh;
                    background: #ffffff;
                    color: var(--maintenance-text);
                    font-family: system-ui, -apple-system, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
                }

                .maintenance-shell {
                    min-height: 100vh;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    padding: 32px 20px;
                    background: #ffffff;
                }

                .maintenance-card {
                    width: 100%;
                    max-width: 720px;
                    text-align: center;
                    background: transparent;
                    border: 0;
                    border-radius: 0;
                    padding: 56px 40px;
                    box-shadow: none;
                }

                .maintenance-media {
                    margin: 0 auto 28px;
                    width: 88px;
                    height: 88px;
                    border-radius: 999px;
                    border: 1px solid var(--maintenance-border);
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    background: #ffffff;
                    overflow: hidden;
                }

                .maintenance-media img {
                    width: 100%;
                    height: 100%;
                    object-fit: cover;
                    display: block;
                }

                .maintenance-media svg {
                    width: 36px;
                    height: 36px;
                    stroke: var(--maintenance-text);
                }

                .maintenance-card h1 {
                    margin: 0 0 14px;
                    color: var(--maintenance-text);
                    font-size: clamp(2rem, 4vw, 3.25rem);
                    line-height: 1.08;
                    letter-spacing: -0.04em;
                    font-weight: 800;
                }

                .maintenance-card .content {
                    max-width: 560px;
                    margin: 0 auto 28px;
                    color: var(--maintenance-muted);
                    font-size: 1.05rem;
                    line-height: 1.75;
                }

                .maintenance-card .content p {
                    margin: 0 0 1rem;
                }

                .maintenance-card .content p:last-child {
                    margin-bottom: 0;
                }

                .btn-reload {
                    display: inline-flex;
                    align-items: center;
                    justify-content: center;
                    min-width: 180px;
                    padding: 14px 24px;
                    border-radius: 999px;
                    border: 1px solid var(--maintenance-button);
                    background: var(--maintenance-button);
                    color: #ffffff;
                    text-decoration: none;
                    font-size: 0.95rem;
                    font-weight: 700;
                    letter-spacing: 0.01em;
                    transition: background-color .2s ease, border-color .2s ease, transform .2s ease;
                }

                .btn-reload:hover {
                    background: var(--maintenance-button-hover);
                    border-color: var(--maintenance-button-hover);
                    color: #ffffff;
                    transform: translateY(-1px);
                }

                @media (max-width: 640px) {
                    .maintenance-shell {
                        padding: 20px 14px;
                    }

                    .maintenance-card {
                        padding: 36px 22px;
                    }

                    .maintenance-media {
                        width: 76px;
                        height: 76px;
                        margin-bottom: 22px;
                    }
                }
            </style>
        </head>

        <body>
            <div class="maintenance-shell">
                <div class="maintenance-card">
                    <div class="maintenance-media">
                        <?php if ($bg_url) : ?>
                            <img src="<?php echo esc_url($bg_url); ?>" alt="<?php echo esc_attr($heading); ?>">
                        <?php else : ?>
                            <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                                <path d="M12 6V12L15.5 15.5" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" />
                                <path d="M21 12C21 16.9706 16.9706 21 12 21C7.02944 21 3 16.9706 3 12C3 7.02944 7.02944 3 12 3C16.9706 3 21 7.02944 21 12Z" stroke-width="1.75" />
                            </svg>
                        <?php endif; ?>
                    </div>
                    <h1><?php echo $heading; ?></h1>
                    <div class="content">
                        <?php echo $body_content; ?>
                    </div>
                    <a href="<?php echo esc_url(home_url()); ?>" class="btn-reload">Muat Ulang</a>
                </div>
            </div>
        </body>

        </html>
<?php
        exit();
    }

    public static function qc_maintenance() {}

    public static function qc_maintenance_list() {}

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
