<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://velocitydeveloper.com
 * @since      1.0.0
 *
 * @package    Velocity_Addons
 * @subpackage Velocity_Addons/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Velocity_Addons
 * @subpackage Velocity_Addons/admin
 * @author     Velocity <bantuanvelocity@gmail.com>
 */
class Velocity_Addons_Admin
{

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct($plugin_name, $version)
	{

		$this->plugin_name = $plugin_name;
		$this->version = $version;
	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles()
	{

		wp_enqueue_style($this->plugin_name, plugin_dir_url(__FILE__) . 'css/velocity-addons-admin.css', array(), $this->version, 'all');

		// Enqueue Chart.js
		wp_enqueue_script('chart-js', 'https://cdn.jsdelivr.net/npm/chart.js', array(), '4.4.1', true);
	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts()
	{

		wp_enqueue_script($this->plugin_name, plugin_dir_url(__FILE__) . 'js/velocity-addons-admin.js', array('jquery'), $this->version, false);

		$page = isset($_GET['page']) ? sanitize_key(wp_unslash($_GET['page'])) : '';

		if ($this->is_velocity_settings_page($page)) {
			$settings_handle = 'velocity-addons-settings-bridge';
			$alpine_handle   = 'alpinejs';
			$settings_path   = plugin_dir_path(__FILE__) . 'js/velocity-addons-settings.js';
			$settings_ver    = file_exists($settings_path) ? (string) filemtime($settings_path) : $this->version;

			wp_enqueue_script(
				$settings_handle,
				plugin_dir_url(__FILE__) . 'js/velocity-addons-settings.js',
				array(),
				$settings_ver,
				true
			);
			wp_localize_script(
				$settings_handle,
				'velocitySettingsConfig',
				array(
					'restBase' => esc_url_raw(rest_url('velocity-addons/v1')),
					'nonce'    => wp_create_nonce('wp_rest'),
					'page'     => $page,
				)
			);
			wp_script_add_data($settings_handle, 'defer', true);

			wp_enqueue_script(
				$alpine_handle,
				'https://cdn.jsdelivr.net/npm/alpinejs@3.14.8/dist/cdn.min.js',
				array($settings_handle),
				'3.14.8',
				true
			);
			wp_script_add_data($alpine_handle, 'defer', true);
		}

		if ($this->is_velocity_rest_action_page($page)) {
			$actions_handle = 'velocity-addons-admin-actions';
			$actions_path   = plugin_dir_path(__FILE__) . 'js/velocity-addons-admin-actions.js';
			$actions_ver    = file_exists($actions_path) ? (string) filemtime($actions_path) : $this->version;

			wp_enqueue_script(
				$actions_handle,
				plugin_dir_url(__FILE__) . 'js/velocity-addons-admin-actions.js',
				array(),
				$actions_ver,
				true
			);
			wp_localize_script(
				$actions_handle,
				'velocitySettingsConfig',
				array(
					'restBase' => esc_url_raw(rest_url('velocity-addons/v1')),
					'nonce'    => wp_create_nonce('wp_rest'),
					'page'     => $page,
				)
			);
			wp_script_add_data($actions_handle, 'defer', true);
		}

		if ($page == 'admin_velocity_addons') {
			if (file_exists(get_template_directory() . '/js/theme.min.js')) {
				$the_theme     = wp_get_theme();
				$theme_version = $the_theme->get('Version');
				wp_enqueue_style('justg-styles', get_template_directory_uri() . '/css/theme.min.css', array(), $theme_version);
				wp_enqueue_script('justg-scripts', get_template_directory_uri() . '/js/theme.min.js', array(), $theme_version, true);
				wp_enqueue_script('chartjs-scripts', 'https://cdn.jsdelivr.net/npm/chart.js', array('jquery'), $this->version, false);
			}

			wp_enqueue_script(array('jquery', 'jquery-ui-datepicker', 'jquery-ui-tooltip'));
		}
	}

	private function is_velocity_settings_page($page)
	{
		return in_array($page, array(
			'velocity_general_settings',
			'velocity_captcha_settings',
			'velocity_maintenance_settings',
			'velocity_license_settings',
			'velocity_security_settings',
			'velocity_auto_resize_settings',
			'velocity_seo_settings',
			'velocity_floating_whatsapp',
			'velocity_snippet_settings',
			'velocity_duitku_settings',
			'velocity_news_settings',
		), true);
	}

	private function is_velocity_rest_action_page($page)
	{
		return in_array($page, array(
			'velocity_statistics',
			'velocity_optimize_db',
		), true);
	}
}

class Velocity_Addons_Admin_Navigation
{
	public static function get_items()
	{
		$items = array(
			array(
				'page'  => 'admin_velocity_addons',
				'label' => 'Dashboard',
			),
			array(
				'page'  => 'velocity_general_settings',
				'label' => 'Umum',
			),
			array(
				'page'  => 'velocity_license_settings',
				'label' => 'License',
			),
			array(
				'page'     => 'velocity_security_settings',
				'label'    => 'Security',
				'children' => array(
					array(
						'page'  => 'velocity_captcha_settings',
						'label' => 'Captcha',
					),
					array(
						'page'  => 'velocity_maintenance_settings',
						'label' => 'Maintenance',
					),
				),
			),
			array(
				'page'  => 'velocity_auto_resize_settings',
				'label' => 'Auto Resize',
			),
			array(
				'page'  => 'velocity_snippet_settings',
				'label' => 'Snippet',
			),
			array(
				'page'    => 'velocity_seo_settings',
				'label'   => 'SEO',
				'enabled' => get_option('seo_velocity', '1') === '1',
			),
			array(
				'page'    => 'velocity_floating_whatsapp',
				'label'   => 'WhatsApp',
				'enabled' => get_option('floating_whatsapp', '1') === '1',
			),
			array(
				'page'    => 'velocity_news_settings',
				'label'   => 'Import Artikel',
				'enabled' => get_option('news_generate', '1') === '1',
			),
			array(
				'page'    => 'velocity_duitku_settings',
				'label'   => 'Duitku',
				'enabled' => get_option('velocity_duitku', '0') === '1',
			),
			array(
				'page'    => 'velocity_statistics',
				'label'   => 'Statistik',
				'enabled' => get_option('statistik_velocity', '1') === '1',
			),
			array(
				'page'    => 'velocity_optimize_db',
				'label'   => 'Optimasi',
				'enabled' => get_option('velocity_optimasi', '0') === '1',
			),
		);

		return array_values(
			array_filter(
				$items,
				static function ($item) {
					return !isset($item['enabled']) || $item['enabled'];
				}
			)
		);
	}

	public static function render($current_page = '')
	{
		if ($current_page === '') {
			$current_page = isset($_GET['page']) ? sanitize_key(wp_unslash($_GET['page'])) : '';
		}

		$items = self::get_items();
		if (empty($items) || $current_page === '') {
			return;
		}

		$subnav_parent = null;
		$subnav_children = array();

		echo '<div class="velocity-topnav">';
		echo '<div class="velocity-topnav__brand">Velocity Addons</div>';
		echo '<nav class="velocity-topnav__links" aria-label="Velocity Addons Navigation">';

		foreach ($items as $item) {
			if (empty($item['page']) || empty($item['label'])) {
				continue;
			}

			$children = !empty($item['children']) && is_array($item['children']) ? $item['children'] : array();
			$active_child = false;
			foreach ($children as $child) {
				if (!empty($child['page']) && $child['page'] === $current_page) {
					$active_child = true;
					break;
				}
			}

			$is_active = $item['page'] === $current_page || $active_child;
			if ($active_child || ($item['page'] === $current_page && !empty($children))) {
				$subnav_parent = $item['label'];
				$subnav_children = $children;
			}

			echo '<a class="velocity-topnav__link' . ($is_active ? ' is-active' : '') . '" href="' . esc_url(admin_url('admin.php?page=' . $item['page'])) . '">' . esc_html($item['label']) . '</a>';
		}

		echo '</nav>';
		echo '</div>';

		if (!empty($subnav_children)) {
			echo '<div class="velocity-subnav">';
			echo '<div class="velocity-subnav__title">' . esc_html($subnav_parent) . '</div>';
			echo '<nav class="velocity-subnav__links" aria-label="' . esc_attr($subnav_parent . ' Navigation') . '">';
			foreach ($subnav_children as $child) {
				if (empty($child['page']) || empty($child['label'])) {
					continue;
				}
				echo '<a class="velocity-subnav__link' . ($child['page'] === $current_page ? ' is-active' : '') . '" href="' . esc_url(admin_url('admin.php?page=' . $child['page'])) . '">' . esc_html($child['label']) . '</a>';
			}
			echo '</nav>';
			echo '</div>';
		}
	}
}
