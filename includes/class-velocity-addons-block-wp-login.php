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
            $whitelist_countries = get_option('whitelist_country', 'ID');
            $ipdat = $this->get_country_code($_SERVER['REMOTE_ADDR'], $whitelist_countries);
            if ($ipdat) {
                add_action('init', array($this, 'block_wp_login'));
            }
        }
    }

    public function block_wp_login() {
        if ('wp-login.php' === $GLOBALS['pagenow']) {
            $ip = $_SERVER['REMOTE_ADDR'];
            $country_code = $this->get_country_code($ip);

            $whitelist_countries = get_option('whitelist_country', 'ID');
            $whitelist_countries = array_map('trim', explode(',', $whitelist_countries));

            if (!in_array($country_code, $whitelist_countries)) {
                $redirect_to = get_option('redirect_to');
                wp_redirect($redirect_to);
                exit;
            }
        }
    }

    private function get_country_code($ip = NULL) {
        if (filter_var($ip, FILTER_VALIDATE_IP) === FALSE) {
            $ip = $_SERVER["REMOTE_ADDR"];
        }
        
        $url = "http://www.geoplugin.net/json.gp?ip=" . $ip;
        $response = @file_get_contents($url);

        if ($response) {
            $data = json_decode($response);
            if (isset($data->geoplugin_countryCode)) {
                return $data->geoplugin_countryCode;
            }
        }

        return 'ID'; // Mengembalikan ID sebagai nilai default jika gagal mendapatkan data
    }
}

// Inisialisasi class Velocity_Addons_Block_Wp_Login
$velocity_block_wp_login = new Velocity_Addons_Block_Wp_Login();
