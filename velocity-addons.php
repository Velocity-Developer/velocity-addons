<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://velocitydeveloper.com
 * @since             1.0.0
 * @package           Velocity_Addons
 *
 * @wordpress-plugin
 * Plugin Name:       Velocity Security
 * Plugin URI:        https://velocitydeveloper.com
 * Description:       Addon plugin for Velocitydeveloper Client
 * Version:           1.0.3
 * Author:            Velocity
 * Author URI:        https://velocitydeveloper.com
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       velocity-addons
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if (!defined('WPINC')) {
	die;
}

/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define('VELOCITY_ADDONS_VERSION', '1.0.3');

define('PLUGIN_DIR', plugin_dir_path(__DIR__));
define('PLUGIN_FILE', plugin_basename(__FILE__));
define('PLUGIN_BASE_NAME', plugin_basename(__DIR__));
define('VELOCITY_ADDONS_PLUGIN_DIR_URL', plugin_dir_url(__FILE__));

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-velocity-addons-activator.php
 */
function activate_velocity_addons()
{
	require_once plugin_dir_path(__FILE__) . 'includes/class-velocity-addons-activator.php';
	Velocity_Addons_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-velocity-addons-deactivator.php
 */
function deactivate_velocity_addons()
{
	require_once plugin_dir_path(__FILE__) . 'includes/class-velocity-addons-deactivator.php';
	Velocity_Addons_Deactivator::deactivate();
}

register_activation_hook(__FILE__, 'activate_velocity_addons');
register_deactivation_hook(__FILE__, 'deactivate_velocity_addons');

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path(__FILE__) . 'includes/class-velocity-addons.php';

/**
 * The core plugin class that is used to cek update and run auto update.
 */
require 'lib/plugin-update-checker/plugin-update-checker.php';

use YahnisElsts\PluginUpdateChecker\v5\PucFactory;

$myUpdateChecker = PucFactory::buildUpdateChecker(
	'https://velocitydeveloper.id/auto-update/plugins/velocity-addons/info.json',
	__FILE__, //Full path to the main plugin file or functions.php.
	'velocity-addons'
);

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_velocity_addons()
{

	$plugin = new Velocity_Addons();
	$plugin->run();
}
run_velocity_addons();
