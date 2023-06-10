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

class Velocity_Addons_Remove_Slug_Category
{
    public function __construct()
    {
        if (get_option('remove_slug_category_velocity')) {
            add_filter('user_trailingslashit', array($this, 'remove_category'), 100, 2);
        }
    }

    function remove_category($string, $type)
    {
        if ($type != 'single' && $type == 'category' && (strpos($string, 'category') !== false)) {
            $url_without_category = str_replace("/category/", "/", $string);
            return trailingslashit($url_without_category);
        }
        return $string;
    }
}
// Initialize the Velocity_Addons_Standar_Editor class
$velocity_addons_remove_slug_sategory = new Velocity_Addons_Remove_Slug_Category();
