<?php
/**
 * Fired during plugin activation
 *
 * @link       https://velocitydeveloper.com
 * @since      1.0.0
 *
 * @package    Velocity_Addons
 * @subpackage Velocity_Addons/includes
 */

 class Velocity_Addons_Block_Wp_Login {
    public function __construct() {
        if (get_option('block_wp_login')) {
            add_action('init', array($this, 'block_wp_login'));
        }
    }

    public function block_wp_login() {
        if ('wp-login.php' === $GLOBALS['pagenow']) {
            $ip                     = $_SERVER['REMOTE_ADDR'];
            $ip_white               = false;
            $whitelist_ip           = get_option('whitelist_block_wp_login', '');
            $whitelist_countries    = get_option('whitelist_country', 'ID');

            if($whitelist_ip){
                $whitelist_ip = array_map('trim', explode(',', $whitelist_ip));
                if (in_array($ip, $whitelist_ip)) {
                    $ip_white = true;
                }
            }

            if(!$ip_white){
                $ipdat = $this->get_country_code($_SERVER['REMOTE_ADDR'], $whitelist_countries);
                if ($ipdat) {
                    $country_code = $ipdat;

                    $whitelist_countries = get_option('whitelist_country', 'ID');
                    $whitelist_countries = array_map('trim', explode(',', $whitelist_countries));

                    if (!in_array($country_code, $whitelist_countries)) {
                        $redirect_to = get_option('redirect_to');
                        wp_redirect($redirect_to);
                        exit;
                    }
                }
            }

        }
    }

    private function get_country_code($ip = NULL) {
        if (filter_var($ip, FILTER_VALIDATE_IP) === FALSE) {
            $ip = $_SERVER["REMOTE_ADDR"];
        }

        // Cache disimpan di server (transient), bukan cookie: cookie bisa dipalsukan
        // pengunjung (mis. VD_<ip>=ID) untuk lolos dari blok negara.
        $cache_key    = 'vd_geo_' . md5($ip);
        $country_code = get_transient($cache_key);

        if ($country_code === false) {
            $country_code = '';
            // geoplugin.net tidak melayani HTTPS untuk akun gratis, jadi pakai api.country.is (HTTPS, tanpa API key).
            $response = wp_remote_get('https://api.country.is/' . rawurlencode($ip), array('timeout' => 5));

            if (!is_wp_error($response) && wp_remote_retrieve_response_code($response) === 200) {
                $data = json_decode(wp_remote_retrieve_body($response));
                if (isset($data->country) && preg_match('/^[A-Z]{2}$/', $data->country)) {
                    $country_code = $data->country;
                }
            }

            // Hasil gagal di-cache 1 jam agar login tidak menunggu timeout tiap kali API mati.
            set_transient($cache_key, $country_code, $country_code ? 7 * DAY_IN_SECONDS : HOUR_IN_SECONDS);
        }

        if ($country_code) {
            return $country_code;
        }

        // Lookup gagal: dulu wp_die() sehingga halaman login mati untuk semua orang.
        // Sekarang pakai header CF-IPCountry bila ada (situs di belakang Cloudflare). Header ini bisa
        // dipalsukan bila situs tidak lewat Cloudflare, tapi hanya dipakai saat lookup gagal.
        // Tanpa header, kembalikan '' = login tetap diizinkan; limit login + captcha masih melindungi.
        $cf_country = isset($_SERVER['HTTP_CF_IPCOUNTRY']) ? strtoupper(trim($_SERVER['HTTP_CF_IPCOUNTRY'])) : '';
        if (preg_match('/^[A-Z0-9]{2}$/', $cf_country)) {
            return $cf_country;
        }

        return '';
    }
}

// Inisialisasi class Velocity_Addons_Block_Wp_Login
$velocity_block_wp_login = new Velocity_Addons_Block_Wp_Login();
