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
class Velocity_Addons_Admin {

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
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;

	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Velocity_Addons_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Velocity_Addons_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		$page = $this->get_current_page();
		if ( ! $this->is_velocity_plugin_page( $page ) ) {
			return;
		}

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/velocity-addons-admin.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Velocity_Addons_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Velocity_Addons_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		$page = $this->get_current_page();
		if ( ! $this->is_velocity_plugin_page( $page ) ) {
			return;
		}

		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/velocity-addons-admin.js', array( 'jquery' ), $this->version, false );

		if ( $this->should_enqueue_chart_js( $page ) ) {
			wp_enqueue_script( 'chart-js', 'https://cdn.jsdelivr.net/npm/chart.js', array(), '4.4.1', true );
		}

		if ( $this->is_velocity_settings_page( $page ) ) {
			$settings_handle = 'velocity-addons-settings-bridge';
			$alpine_handle   = 'velocity-addons-alpinejs';
			$settings_path   = plugin_dir_path( __FILE__ ) . 'js/velocity-addons-settings.js';
			$settings_ver    = file_exists( $settings_path ) ? (string) filemtime( $settings_path ) : $this->version;

			wp_enqueue_script(
				$settings_handle,
				plugin_dir_url( __FILE__ ) . 'js/velocity-addons-settings.js',
				array(),
				$settings_ver,
				true
			);
			wp_localize_script(
				$settings_handle,
				'velocitySettingsConfig',
				array(
					'restBase' => esc_url_raw( rest_url( 'velocity-addons/v1' ) ),
					'nonce'    => wp_create_nonce( 'wp_rest' ),
					'page'     => $page,
					'bindings' => class_exists( 'Velocity_Addons_Settings_Registry' )
						? Velocity_Addons_Settings_Registry::get_settings_bindings()
						: array(),
					'dynamicMenuItems' => class_exists( 'Velocity_Addons_Settings_Registry' )
						? Velocity_Addons_Settings_Registry::get_general_dynamic_menu_items()
						: array(),
				)
			);
			wp_script_add_data( $settings_handle, 'defer', true );

			wp_enqueue_script(
				$alpine_handle,
				'https://cdn.jsdelivr.net/npm/alpinejs@3.14.8/dist/cdn.min.js',
				array( $settings_handle ),
				'3.14.8',
				true
			);
			wp_script_add_data( $alpine_handle, 'defer', true );
		}

		if ( $this->is_velocity_rest_action_page( $page ) ) {
			$actions_handle = 'velocity-addons-admin-actions';
			$actions_path   = plugin_dir_path( __FILE__ ) . 'js/velocity-addons-admin-actions.js';
			$actions_ver    = file_exists( $actions_path ) ? (string) filemtime( $actions_path ) : $this->version;

			wp_enqueue_script(
				$actions_handle,
				plugin_dir_url( __FILE__ ) . 'js/velocity-addons-admin-actions.js',
				array(),
				$actions_ver,
				true
			);
			wp_localize_script(
				$actions_handle,
				'velocitySettingsConfig',
				array(
					'restBase' => esc_url_raw( rest_url( 'velocity-addons/v1' ) ),
					'nonce'    => wp_create_nonce( 'wp_rest' ),
					'page'     => $page,
				)
			);
			wp_script_add_data( $actions_handle, 'defer', true );
		}

		if ($page == 'admin_velocity_addons') {
			
			if (file_exists(get_template_directory() . '/js/theme.min.js')) {            
				$the_theme     	= wp_get_theme();
				$theme_version 	= $the_theme->get( 'Version' );
				wp_enqueue_style( 'justg-styles', get_template_directory_uri() . '/css/theme.min.css', array(), $theme_version );
				wp_enqueue_script( 'justg-scripts', get_template_directory_uri() . '/js/theme.min.js', array(), $theme_version, true );
				wp_enqueue_script( 'chartjs-scripts', 'https://cdn.jsdelivr.net/npm/chart.js', array( 'jquery' ), $this->version, false );
			}

			wp_enqueue_script( array( 'jquery','jquery-ui-datepicker','jquery-ui-tooltip' ) );
		}
	}

	private function is_velocity_settings_page( $page ) {
		$pages = class_exists( 'Velocity_Addons_Settings_Registry' )
			? Velocity_Addons_Settings_Registry::get_settings_page_slugs()
			: array();

		return in_array( $page, $pages, true );
	}

	private function is_velocity_rest_action_page( $page ) {
		$pages = class_exists( 'Velocity_Addons_Settings_Registry' )
			? Velocity_Addons_Settings_Registry::get_action_page_slugs()
			: array();

		return in_array( $page, $pages, true );
	}

	private function is_velocity_plugin_page( $page ) {
		if ( $page === 'admin_velocity_addons' ) {
			return true;
		}

		$pages = array();
		if ( class_exists( 'Velocity_Addons_Settings_Registry' ) ) {
			$submenu_pages = Velocity_Addons_Settings_Registry::get_submenu_pages();
			foreach ( $submenu_pages as $submenu ) {
				if ( isset( $submenu['slug'] ) && $submenu['slug'] !== '' ) {
					$pages[] = (string) $submenu['slug'];
				}
			}
		}

		return in_array( $page, $pages, true );
	}

	private function should_enqueue_chart_js( $page ) {
		$pages = array( 'admin_velocity_addons', 'velocity_statistics', 'velocity_optimize_db' );
		return in_array( $page, $pages, true );
	}

	private function get_current_page() {
		return isset( $_GET['page'] ) ? sanitize_key( wp_unslash( $_GET['page'] ) ) : '';
	}

}
