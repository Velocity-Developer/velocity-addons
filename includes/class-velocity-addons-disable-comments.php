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

 class Velocity_Addons_Fully_Disable_Comment {
    public function __construct() {
        add_action('wp_loaded', array($this, 'disable_comment'));
    }

    public function disable_comment() {
        if (get_option('fully_disable_comment')) {
            add_filter('comments_open', '__return_false', 20, 2);
            add_filter('pings_open', '__return_false', 20, 2);
            add_filter('comments_array', '__return_empty_array', 10, 2);
            add_action('admin_menu', array($this, 'remove_comment_menu'));
            add_action('admin_bar_menu', array($this, 'remove_comment_admin_bar'), 999);
            add_action('widgets_init', array($this, 'unregister_comment_widgets'));
            add_action('wp_dashboard_setup', array($this, 'remove_comment_dashboard_widget'));
        }
    }

    public function remove_comment_menu() {
        remove_menu_page('edit-comments.php');
    }

    public function remove_comment_admin_bar($wp_admin_bar) {
        $wp_admin_bar->remove_menu('comments');
    }

    public function unregister_comment_widgets() {
        unregister_widget('WP_Widget_Recent_Comments');
    }

    public function remove_comment_dashboard_widget() {
        remove_meta_box('dashboard_recent_comments', 'dashboard', 'normal');
    }
}

// Initialize the Velocity_Addons_Fully_Disable_Comment class
$Velocity_Addons_Fully_Disable_Comment = new Velocity_Addons_Fully_Disable_Comment();