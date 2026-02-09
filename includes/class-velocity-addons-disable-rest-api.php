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

class Velocity_Addons_Disable_Rest_Api
{
    public function __construct()
    {
        if (get_option('disable_rest_api')) {
            add_filter('rest_authentication_errors', array($this, 'disable_rest_api'), 99);
            add_filter('rest_jsonp_enabled', '__return_false');
        }
    }

    public function disable_rest_api($access)
    {
        if (!empty($access)) {
            return $access;
        }

        if ($this->is_allowed_velocity_route() || (is_user_logged_in() && current_user_can('manage_options'))) {
            return $access;
        }

        return new WP_Error('rest_disabled', __('The REST API is disabled on this site.'), array('status' => rest_authorization_required_code()));
    }

    private function is_allowed_velocity_route()
    {
        if (empty($_SERVER['REQUEST_URI'])) {
            return false;
        }

        $request_uri = wp_unslash((string) $_SERVER['REQUEST_URI']);
        $allowed = array(
            '/wp-json/velocity-addons/v1/',
            '/wp-json/velocityaddons/v1/duitku_callback',
        );

        foreach ($allowed as $route_prefix) {
            if (strpos($request_uri, $route_prefix) !== false) {
                return true;
            }
        }

        return false;
    }

    public function block_rest_api()
    {
        global $wp_rewrite;
        $wp_rewrite->set_permalink_structure('/%postname%/%category%/');
    }
}

// Inisialisasi class Velocity_Addons_Disable_Rest_Api
$velocity_disable_rest_api = new Velocity_Addons_Disable_Rest_Api();
