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

		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/velocity-addons-admin.js', array( 'jquery' ), $this->version, false );

		$page = isset($_GET['page']) ? $_GET['page'] : '';    
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

}
