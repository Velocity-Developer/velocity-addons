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
        }
    }

    public function check_maintenance_mode()
    {
        if (!current_user_can('manage_options') && !is_admin() && !is_page('myaccount')) {
            wp_die('<h1>Maintenance Mode</h1><p>We are currently performing maintenance. Please check back later.</p>', 'Maintenance Mode');
        }
    }
}

// Inisialisasi class Velocity_Addons_Maintenance_Mode
$velocity_maintenance_mode = new Velocity_Addons_Maintenance_Mode();
