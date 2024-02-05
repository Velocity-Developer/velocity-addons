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
        $cookie_name = 'VD_'.ip2long($ip);

        if(!isset($_COOKIE[$cookie_name])) {

            $response = wp_remote_get( 'http://www.geoplugin.net/json.gp?ip='. $ip );            

            if ( is_array( $response ) && ! is_wp_error( $response ) ) {

                $data = json_decode($response['body']);

                $country_code = isset($data->geoplugin_countryCode)?$data->geoplugin_countryCode:'';

                // Menentukan waktu kedaluwarsa cookie (7 hari)
                $expirationTime = time() + (7 * 24 * 60 * 60);
                // Menetapkan cookie
                setcookie($cookie_name, $country_code, $expirationTime, "/");

                return $country_code;

            } else {

                wp_die('Velocity Security gagal akses geoplugin.net untuk ip <strong>'.$ip.'</strong>', 'Geoplugin gagal');

            }

        } else {

            return $_COOKIE[$cookie_name];
        }

    }
}

// Inisialisasi class Velocity_Addons_Block_Wp_Login
$velocity_block_wp_login = new Velocity_Addons_Block_Wp_Login();
