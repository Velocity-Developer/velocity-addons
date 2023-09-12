<?php

/**
 * Define the internationalization functionality
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @link       https://velocitydeveloper.com
 * @since      1.0.0
 *
 * @package    Velocity_Addons
 * @subpackage Velocity_Addons/includes
 */

/**
 * Define the internationalization functionality.
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @since      1.0.0
 * @package    Velocity_Addons
 * @subpackage Velocity_Addons/includes
 * @author     Velocity <bantuanvelocity@gmail.com>
 */

 class Velocity_Addons_Limit_Login_Attempts {
    public function __construct() {
        if (get_option('limit_login_attempts')) {
            add_action('wp_login_failed', array($this, 'limit_login_attempts'), 10, 2);
        }
    }

    public function limit_login_attempts($username = null, $error = null) {
        $login_attempts = get_option('login_attempts', array());

        if (!is_array($login_attempts)) {
            $login_attempts = array();
        }

        $ip_address = $_SERVER['REMOTE_ADDR'];
        $current_time = current_time('timestamp');

        // Limit the number of login attempts per IP address
        $max_attempts = 5;
        $expiration_time = 24 * 60 * 60; // 24 hours

        if (isset($login_attempts[$ip_address])) {
            $attempts = $login_attempts[$ip_address];

            if ($attempts >= $max_attempts) {
                $remaining_time = $expiration_time - ($current_time - $login_attempts[$ip_address.'_time']);

                if ($remaining_time > 0) {
                    $minutes = ceil($remaining_time / 60);
                    $error_message = __('Too many login attempts. Please try again after '.$minutes.' minutes.', 'your-text-domain');
                    $error = new WP_Error('login_attempts_exceeded', $error_message);

                    // Log the error for reference
                    $this->log_error($username, $ip_address);

                    // Redirect the user back to the login page with the error message
                    wp_safe_redirect(wp_login_url().'?login_error='.urlencode($error->get_error_message()));
                    exit;
                } else {
                    unset($login_attempts[$ip_address]);
                    unset($login_attempts[$ip_address.'_time']);
                }
            }
        }

        $login_attempts[$ip_address] = isset($login_attempts[$ip_address]) ? ++$login_attempts[$ip_address] : 1;
        $login_attempts[$ip_address.'_time'] = $current_time;

        update_option('login_attempts', $login_attempts);
    }

    private function log_error($username, $ip_address) {
        // Log the error for reference (you can customize this method to suit your logging needs)
        error_log('Login attempts exceeded for username: '.$username.' from IP address: '.$ip_address);
    }
}

// Inisialisasi class Velocity_Addons_Limit_Login_Attempts
$velocity_limit_login_attempts = new Velocity_Addons_Limit_Login_Attempts();
