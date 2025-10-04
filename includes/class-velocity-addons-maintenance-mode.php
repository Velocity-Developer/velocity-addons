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
        $background_css = $bg_url
            ? 'background-color:#0f172a;background-image:url(' . esc_url($bg_url) . ');background-size:cover;background-position:center;'
            : 'background:linear-gradient(135deg,#0f172a,#1e293b);';

        $message  = '<style>body#error-page{width:100%;max-width:100%;overflow:hidden;margin:0;padding:0;border:0;display:flex;align-items:center;justify-content:center;min-height:100vh;font-family:-apple-system,BlinkMacSystemFont,"Segoe UI",sans-serif;color:#fff;' . $background_css . '}';
        $message .= 'body#error-page:before{content:"";position:fixed;inset:0;background:rgba(15,23,42,0.1);}';
        $message .= '#velocity-maintenance__wrapper{position:relative;z-index:1;padding:30px;background:rgba(12,19,33,0.85);border-radius:10px;text-align:center;box-shadow:0 20px 45px rgba(0,0,0,0.35);}';
        $message .= '#velocity-maintenance__wrapper h1{margin:0 0 17px;padding-bottom:17px;font-size:29px;line-height:1.2;color:#fff;}';
        $message .= '#velocity-maintenance__wrapper .velocity-maintenance__body{font-size:18px;line-height:1.7;color:#e2e8f0;}';
        $message .= '#velocity-maintenance__wrapper .velocity-maintenance__body p{margin:0 0 16px;padding:0;}';
        $message .= '#velocity-maintenance__wrapper .velocity-maintenance__body p:last-child{margin-bottom:0;}';
        $message .= '#error-page p,#error-page .wp-die-message{margin:0 auto;padding:20px;}';
        $message .= '</style>';
        $message .= '<div id="velocity-maintenance__wrapper">';
        $message .= '<h1>' . $heading . '</h1>';
        $message .= '<div class="velocity-maintenance__body">' . $body_content . '</div>';
        $message .= '</div>';

        // 503 supaya SEO paham sedang perawatan
        wp_die($message, $heading, ['response' => 503]);
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

    public static function check_domain_extension()
    {
        ob_start();
        $site_url = get_site_url();
        $domain   = parse_url($site_url, PHP_URL_HOST);
        $domain_parts = explode('.', (string) $domain);
        $extension    = array_pop($domain_parts);
        $sub_extension= array_pop($domain_parts);
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
        $linksetting= admin_url('options-general.php');
        if (empty($site_icon)) {
            echo '<p>Peringatan: Favicon belum disetting. Silakan setting <a href="' . $linksetting . '"><b>disini.</b></a></p>';
        }
        return ob_get_clean();
    }

    public static function check_recaptcha()
    {
        ob_start();
        $linksetting     = admin_url('admin.php?page=custom_admin_options');
        $check_recaptcha = get_option('captcha_velocity');
        $sitekey  = $check_recaptcha['sitekey']  ?? '';
        $secretkey= $check_recaptcha['secretkey']?? '';
        if (empty($sitekey) || empty($secretkey)) {
            echo '<p>Peringatan: Recaptcha belum disetting. Silakan setting <a href="' . $linksetting . '"><b>disini.</b></a></p>';
        }
        return ob_get_clean();
    }

    public static function check_seo()
    {
        ob_start();
        $linksetting  = admin_url('admin.php?page=velocity_seo_settings');
        $home_keywords= get_option('home_keywords');
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
        $auto_update_plugins= get_site_option('auto_update_plugins', []);
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
