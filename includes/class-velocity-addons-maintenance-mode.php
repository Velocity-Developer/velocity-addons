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
            add_action('wp', array($this, 'check_maintenance_mode'));
            add_action('admin_notices', [$this, 'qc_maintenance']);
        }
    }

    public function check_maintenance_mode()
    {
        if (!current_user_can('manage_options') && !is_admin() && !is_page('myaccount')) {
            $opt    = get_option('maintenance_mode_data',[]);
            $hd     = isset($opt['header'])&&!empty($opt['header'])?$opt['header']:'Maintenance Mode';
            $bd     = isset($opt['body'])&&!empty($opt['body'])?$opt['body']:'';

            wp_die('<h1>'.$hd.'</h1><p>'.$bd.'</p>', 'Maintenance Mode');
        }
    }

    public function qc_maintenance(){
        echo '<div class="notice notice-warning notice-alt">';
            echo $this->check_permalink_settings();
            echo $this->check_site_icon();
            echo $this->check_recaptcha();
            echo $this->check_seo();
            echo $this->check_domain_extension();
            echo $this->check_installed_plugins();
        echo '</div>';
    }

    public function check_domain_extension(){
        ob_start();
        // Mendapatkan URL situs saat ini
        $site_url = get_site_url();
        
        // Menghapus skema (http:// atau https://) dari URL
        $domain = parse_url($site_url, PHP_URL_HOST);

        // Memisahkan nama domain menjadi bagian-bagian
        $domain_parts = explode('.', $domain);
        
        // Mengambil ekstensi domain (bagian terakhir)
        $extension = array_pop($domain_parts);
        
        // Memeriksa sub-ekstensi
        $sub_extension = array_pop($domain_parts); // Ambil bagian sebelum ekstensi

        // Daftar ekstensi yang valid
        $valid_extensions = ['go.id', 'desa.id', 'sch.id', 'ac.id'];

        // Memeriksa apakah domain berakhir dengan ekstensi yang valid
        if (in_array($sub_extension . '.' . $extension, $valid_extensions)) {
            echo '<p>Setting Desain By Velocity => Open New Tab. Linknya Di Warna Sesuai Background, Rata Kiri (Pojok), Saat Hover Jangan Icon Tangan Tapi Icon Panah Sprti Pada Saat Tanpa Hover.</p>';
        }
        echo '<p>Pastikan Copy Right Sesuai Tahun!</p>';

        return ob_get_clean();
    }

    public function check_permalink_settings() {
        ob_start();
        // Mendapatkan pengaturan permalink
        $permalinks = get_option('permalink_structure');
        $linksetting = admin_url('options-permalink.php');

        // Memeriksa apakah permalink tidak diatur
        if (empty($permalinks) || $permalinks != '/%category%/%postname%/') {
            // Menambahkan log peringatan
            echo '<p>Peringatan: Permalink belum disetting. Silakan setting <a href="'.$linksetting.'"><b> disini.</b></a></p>';
        }

        return ob_get_clean();
    }

    public function check_site_icon(){
        ob_start();
        $site_icon = get_site_icon_url();
        $linksetting = admin_url('customize.php');

        if(empty($site_icon)) {
            echo '<p>Peringatan: Favicon belum disetting. Silakan setting <a href="'.$linksetting.'"><b> disini.</b></a></p>';
        }
        return ob_get_clean();
    }

    public function check_recaptcha(){
        ob_start();
        $linksetting    = admin_url('options-general.php?page=custom_admin_options');
        $check_recaptcha = get_option('captcha_velocity');
        $aktif  = $check_recaptcha['aktif']??'';
        $sitekey    = $check_recaptcha['sitekey'];
        $secretkey  = $check_recaptcha['secretkey'];

        if($aktif == false || empty($sitekey) || empty($secretkey)){
            echo '<p>Peringatan: Recaptcha belum disetting. Silakan setting <a href="'.$linksetting.'"><b> disini.</b></a></p>';
        }

        return ob_get_clean();
    }

    public function check_seo(){
        ob_start();
        $linksetting    = admin_url('admin.php?page=velocity_seo_settings');
        $home_keywords  = get_option('home_keywords');
        $share_image    = get_option('share_image');

        if(empty($home_keywords) || empty($share_image)){
            echo '<p>Peringatan: SEO belum disetting. Silakan setting <a href="'.$linksetting.'"><b> disini.</b></a></p>';
        }

        return ob_get_clean();
    }

    public function check_installed_plugins() {
        ob_start();

        // Mendapatkan semua plugin yang terinstal
        $plugins = get_plugins();

        // Mendapatkan pengaturan auto-update untuk plugin
        $auto_update_plugins = get_site_option( 'auto_update_plugins', [] );

        // Plugin yang dikecualikan
        $excluded_plugins = [
            'bb-ultimate-addon/bb-ultimate-addon.php',
            'velocity-toko/velocity-toko.php',
            'velocity-expedisi/velocity-expedisi.php',
            'velocity-donasi/velocity-donasi.php',
            'velocity-produk/velocity-produk.php',
            'vd-gallery/vd-gallery.php',
            'velocity-toko-kursus/velocity-kursus.php',
            'custom-plugin/custom-plugin.php',
            ];

        foreach ( $plugins as $plugin_file => $plugin_data ) {
            // Mengambil slug dari plugin
            $plugin_slug = $plugin_file; // Contoh: 'plugin-directory/plugin-file.php'
            if(!in_array($plugin_slug, $excluded_plugins)) {
                if (!in_array($plugin_slug, $auto_update_plugins) ) {
                    echo '<p>'.$plugin_data['Name'] . ' belum diaktifkan untuk pembaruan otomatis.</p>';
                }
            }
            
        }

        return ob_get_clean();
    }
}

// Inisialisasi class Velocity_Addons_Maintenance_Mode
$velocity_maintenance_mode = new Velocity_Addons_Maintenance_Mode();
