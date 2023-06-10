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

class Velocity_Addons_Classic_Widget
{
    public function __construct()
    {
        if (get_option('classic_widget_velocity')) {
            // Disables the block editor from managing widgets in the Gutenberg plugin.
            add_filter('gutenberg_use_widgets_block_editor', '__return_false');
            // Disables the block editor from managing widgets.
            add_filter('use_widgets_block_editor', '__return_false');
        }
    }
}
// Initialize the Velocity_Addons_Classic_Widget class
$velocity_addons_classic_widget = new Velocity_Addons_Classic_Widget();
