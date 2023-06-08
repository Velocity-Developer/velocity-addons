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
class Velocity_Addons_Disable_Xmlrpc {
    public function __construct() {
        if (get_option('disable_xmlrpc')) {
            add_filter('xmlrpc_enabled', '__return_false');
        }
    }
}

// Inisialisasi class Velocity_Addons_Disable_Xmlrpc
$velocity_disable_xmlrpc = new Velocity_Addons_Disable_Xmlrpc();