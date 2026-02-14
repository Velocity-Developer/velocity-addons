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
        // Deprecated: disable_rest_api is removed from settings UI.
        // Force reset legacy value to avoid blocking plugin REST endpoints.
        if (get_option('disable_rest_api')) {
            update_option('disable_rest_api', 0);
        }
    }

    public function block_rest_api()
    {
        global $wp_rewrite;
        $wp_rewrite->set_permalink_structure('/%postname%/%category%/');
    }
}

// Inisialisasi class Velocity_Addons_Disable_Rest_Api
$velocity_disable_rest_api = new Velocity_Addons_Disable_Rest_Api();
