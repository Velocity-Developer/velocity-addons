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

 class Velocity_Addons_Disable_Gutenberg {
    public function __construct() {
        if (get_option('disable_gutenberg')) {
            add_filter('use_block_editor_for_post_type', array($this, 'disable_gutenberg'), 10, 2);
        }
    }

    public function disable_gutenberg($use_block_editor, $post_type) {
        // if ($post_type === 'post') {
            return false;
        // }
        return $use_block_editor;
    }
}

// Inisialisasi class Velocity_Addons_Disable_Gutenberg
$velocity_disable_gutenberg = new Velocity_Addons_Disable_Gutenberg();