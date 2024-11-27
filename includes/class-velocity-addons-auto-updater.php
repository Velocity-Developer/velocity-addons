<?php

/**
 * Fired during plugin activation
 *
 * @link       https://velocitydeveloper.com
 * @since      1.0.0
 *
 * @package    Velocity_Addons
 * @subpackage Velocity_Addons/includes
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    Velocity_Addons
 * @subpackage Velocity_Addons/includes
 * @author     Velocity <bantuanvelocity@gmail.com>
 */

class Velocity_Addons_Auto_Updater
{
  private $api_url = 'https://api.velocitydeveloper.id/wp-json/plugins/v1/velocity-addons';
  private $license_key;
  private $source;

  public function __construct()
  {
    $this->license_key = get_option('velocity_license')['key'] !== '' ? get_option('velocity_license')['key'] : '';
    $this->source = parse_url(get_site_url(), PHP_URL_HOST);
    add_filter('pre_set_site_transient_update_plugins', [$this, 'check_for_update']);
    add_filter('plugins_api', [$this, 'plugin_info'], 10, 3);

    // Menambahkan tombol Enable Auto Update di halaman plugin
    add_action('plugin_action_links', [$this, 'add_link_to_settings'], 10, 2);

    // Menangani klik tombol Enable Auto Update
    add_action('admin_init', [$this, 'enable_auto_update']);
  }

  public function check_for_update($transient)
  {
    // Pastikan bahwa data 'checked' sudah ada dalam transient
    if (empty($transient->checked)) {
      return $transient;
    }

    // Cek apakah versi plugin saat ini lebih rendah dari versi yang baru
    $current_version = $this->get_current_plugin_version();
    $response = $this->api_request();

    if ($response && isset($response->data->version)) {
      $new_version = $response->data->version;

      // Cek apakah versi saat ini lebih rendah dari versi baru
      if (version_compare($current_version, $new_version, '<')) {
        // Menambahkan informasi pembaruan dalam transient
        $transient->response['velocity-addons/velocity-addons.php'] = (object) [
          'slug' => 'velocity-addons',
          'plugin' => 'velocity-addons/velocity-addons.php',
          'new_version' => $new_version,
          'url' => $response->data->details_url,
          'package' => $response->data->download_url,
        ];
      }
    }

    return $transient;
  }

  public function plugin_info($false, $action, $args)
  {
    if ($action !== 'plugin_information' || $args->slug !== 'velocity-addons') {
      return $false;
    }

    $response = $this->api_request();
    if ($response && isset($response->data->version)) {
      return (object) [
        'name' => 'Velocity Addons',
        'slug' => 'velocity-addons',
        'version' => $response->data->version,
        'author' => '<a href="https://velocitydeveloper.com">Velocity</a>',
        'homepage' => $response->data->details_url ?? '',
        'sections' => [
          'description' => $response->data->description ?? 'No description available.',
        ],
        'download_link' => $response->data->download_url ?? '',
      ];
    }

    return $false;
  }

  private function api_request()
  {
    $response = wp_remote_get($this->api_url, [
      'headers' => [
        'license_key' => $this->license_key,
        'source' => $this->source,
      ],
    ]);

    if (is_wp_error($response)) {
      error_log('API Request Error: ' . $response->get_error_message());
      return false;
    }

    $body = wp_remote_retrieve_body($response);
    return json_decode($body);
  }

  private function get_current_plugin_version()
  {
    if (!function_exists('get_plugin_data')) {
      require_once ABSPATH . 'wp-admin/includes/plugin.php';
    }

    $plugin_data = get_plugin_data(WP_PLUGIN_DIR . '/velocity-addons/velocity-addons.php');
    return $plugin_data['Version'] ?? '0.0.0';
  }

  public function add_link_to_settings($actions, $plugin_file)
  {
    if ($plugin_file === 'velocity-addons/velocity-addons.php') {
      $url = admin_url('admin.php?page=custom_admin_options');
      $actions['link_to_settings'] = '<a href="' . $url . '">Pengaturan Admin</a>';
    }
    return $actions;
  }

  public function enable_auto_update()
  {
    if (isset($_GET['action']) && $_GET['action'] === 'enable-auto-update' && isset($_GET['plugin']) && wp_verify_nonce($_GET['_wpnonce'], 'enable_auto_update_' . $_GET['plugin'])) {
      // Simpan status auto update di opsi WordPress
      update_option('velocity_addons_auto_update_enabled', true);

      // Redirect kembali ke halaman plugin
      wp_redirect(admin_url('plugins.php'));
      exit;
    }
  }
}

// Inisialisasi updater
new Velocity_Addons_Auto_Updater();
