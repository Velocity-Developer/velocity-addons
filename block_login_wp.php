<?php

/**
 * @link              velocitydeveloper.com
 * @since             1.0.0
 * @package           block-login-wp
 *
 * @wordpress-plugin
 * Plugin Name:       Block Login Wordpress
 * Plugin URI:        velocitydeveloper.com
 * Description:       Berisi fuction untuk blokir login wordpress berdasarkan plugin aios
 * Version:           1.0.0
 * Author:            Velocity Developer
 * Author URI:        velocitydeveloper.com
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       block-login-wp
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}

//require_once plugin_dir_path(__FILE__) . '/inc/enqueue.php';

function ip_info($ip = NULL, $purpose = "location", $deep_detect = TRUE) {
    $output = NULL;
    if (filter_var($ip, FILTER_VALIDATE_IP) === FALSE) {
        $ip = $_SERVER["REMOTE_ADDR"];
        if ($deep_detect) {
            if (filter_var(@$_SERVER['HTTP_X_FORWARDED_FOR'], FILTER_VALIDATE_IP))
                $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
            if (filter_var(@$_SERVER['HTTP_CLIENT_IP'], FILTER_VALIDATE_IP))
                $ip = $_SERVER['HTTP_CLIENT_IP'];
        }
    }
    $purpose    = str_replace(array("name", "\n", "\t", " ", "-", "_"), NULL, strtolower(trim($purpose)));
    $support    = array("country", "countrycode", "state", "region", "city", "location", "address");
    $continents = array(
        "AF" => "Africa",
        "AN" => "Antarctica",
        "AS" => "Asia",
        "EU" => "Europe",
        "OC" => "Australia (Oceania)",
        "NA" => "North America",
        "SA" => "South America"
    );
    if (filter_var($ip, FILTER_VALIDATE_IP) && in_array($purpose, $support)) {
        $ipdat = @json_decode(file_get_contents("http://www.geoplugin.net/json.gp?ip=" . $ip));
        if (@strlen(trim($ipdat->geoplugin_countryCode)) == 2) {
            switch ($purpose) {
                case "location":
                    $output = array(
                        "city"           => @$ipdat->geoplugin_city,
                        "state"          => @$ipdat->geoplugin_regionName,
                        "country"        => @$ipdat->geoplugin_countryName,
                        "country_code"   => @$ipdat->geoplugin_countryCode,
                        "continent"      => @$continents[strtoupper($ipdat->geoplugin_continentCode)],
                        "continent_code" => @$ipdat->geoplugin_continentCode
                    );
                    break;
                case "address":
                    $address = array($ipdat->geoplugin_countryName);
                    if (@strlen($ipdat->geoplugin_regionName) >= 1)
                        $address[] = $ipdat->geoplugin_regionName;
                    if (@strlen($ipdat->geoplugin_city) >= 1)
                        $address[] = $ipdat->geoplugin_city;
                    $output = implode(", ", array_reverse($address));
                    break;
                case "city":
                    $output = @$ipdat->geoplugin_city;
                    break;
                case "state":
                    $output = @$ipdat->geoplugin_regionName;
                    break;
                case "region":
                    $output = @$ipdat->geoplugin_regionName;
                    break;
                case "country":
                    $output = @$ipdat->geoplugin_countryName;
                    break;
                case "countrycode":
                    $output = @$ipdat->geoplugin_countryCode;
                    break;
            }
        }
    }
    return $output;
}

function cek_block_negara() {
  if ( $GLOBALS['pagenow'] === 'wp-login.php' ) {
    global $aio_wp_security, $aio_wp_security_premium;
    if($aio_wp_security_premium){
      $list_block = isset($aio_wp_security_premium->configs->configs['aiowps_cb_secondary_blocked_countries']) ?  $aio_wp_security_premium->configs->configs['aiowps_cb_secondary_blocked_countries'] : [];
      $redir_url = isset($aio_wp_security_premium->configs->configs['aiowps_cb_secondary_redirect_url']) ?  $aio_wp_security_premium->configs->configs['aiowps_cb_secondary_redirect_url'] : get_site_url();
      $id_negara_pengunjung = ip_info("Visitor", "Country Code");
      //echo $id_negara_pengunjung.' = '.implode(',',$list_block);
      if(in_array($id_negara_pengunjung,$list_block)){
        //echo 'kode negara '.$id_negara_pengunjung.' di block';
        wp_redirect( $redir_url );
        exit;
      }
    }
  }
}
add_action( 'init','cek_block_negara' );