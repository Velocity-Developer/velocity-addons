<?php

/**
 * Optimize DB admin page renderer.
 *
 * @package Velocity_Addons
 * @subpackage Velocity_Addons/admin/pages
 */
class Velocity_Addons_Admin_Page_Optimize
{
    public static function render()
    {
        if (!current_user_can('manage_options')) {
            return;
        }

        Velocity_Addons_Optimasi::render_optimize_db_page();
    }
}

