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
  private $repo = 'Velocity-Developer/velocity-addons';
  private $slug = 'velocity-addons';
  private $plugin_file = 'velocity-addons/velocity-addons.php';
  private $cache_key = 'velocity_addons_github_release_latest_v1';
  private $cache_ttl = 21600;

  public function __construct()
  {
    add_filter('pre_set_site_transient_update_plugins', [$this, 'check_for_update']);
    add_filter('plugins_api', [$this, 'plugin_info'], 10, 3);
    add_filter('upgrader_post_install', [$this, 'upgrader_post_install'], 10, 3);

    // Menambahkan tombol Enable Auto Update di halaman plugin
    add_action('plugin_action_links', [$this, 'add_link_to_settings'], 10, 2);
  }

  public function check_for_update($transient)
  {
    // Pastikan bahwa data 'checked' sudah ada dalam transient
    if (empty($transient->checked)) {
      return $transient;
    }

    $current_version = $this->get_current_plugin_version();
    $release = $this->get_latest_release();
    if (!$release || empty($release['version']) || empty($release['package'])) {
      return $transient;
    }

    $new_version = (string) $release['version'];
    if (version_compare($current_version, $new_version, '<')) {
      $transient->response[$this->plugin_file] = (object) [
        'slug' => $this->slug,
        'plugin' => $this->plugin_file,
        'new_version' => $new_version,
        'url' => $release['details_url'] ?? '',
        'package' => $release['package'],
      ];
    }

    return $transient;
  }

  public function plugin_info($false, $action, $args)
  {
    if ($action !== 'plugin_information' || !is_object($args) || empty($args->slug) || $args->slug !== $this->slug) {
      return $false;
    }

    $release = $this->get_latest_release();
    if (!$release || empty($release['version']) || empty($release['package'])) {
      return $false;
    }

    $plugin_data = $this->get_current_plugin_data();
    $description = isset($plugin_data['Description']) ? (string) $plugin_data['Description'] : '';

    return (object) [
      'name' => $plugin_data['Name'] ?? 'Velocity Addons',
      'slug' => $this->slug,
      'version' => (string) $release['version'],
      'author' => '<a href="https://velocitydeveloper.com">Velocity</a>',
      'homepage' => $release['details_url'] ?? '',
      'last_updated' => $release['published_at'] ?? '',
      'sections' => [
        'description' => wp_kses_post($description),
        'changelog' => wp_kses_post(wpautop($release['body'] ?? '')),
      ],
      'download_link' => $release['package'],
    ];
  }

  public function upgrader_post_install($response, $hook_extra, $result)
  {
    if (empty($hook_extra['plugin']) || $hook_extra['plugin'] !== $this->plugin_file) {
      return $response;
    }

    if (empty($result['destination']) || empty($result['destination_name'])) {
      return $response;
    }

    if ($result['destination_name'] === $this->slug) {
      return $response;
    }

    if (!function_exists('WP_Filesystem')) {
      require_once ABSPATH . 'wp-admin/includes/file.php';
    }

    global $wp_filesystem;
    if (!$wp_filesystem || !is_object($wp_filesystem)) {
      WP_Filesystem();
    }
    if (!$wp_filesystem || !is_object($wp_filesystem)) {
      return $response;
    }

    $from = trailingslashit($result['destination']) . $result['destination_name'];
    $to = trailingslashit($result['destination']) . $this->slug;

    if ($wp_filesystem->is_dir($from)) {
      $moved = $wp_filesystem->move($from, $to, true);
      if ($moved) {
        $result['destination_name'] = $this->slug;
        $result['destination'] = $to;
        $result['local_destination'] = $to;
        return $result;
      }
    }

    return $response;
  }

  private function get_current_plugin_version()
  {
    if (!function_exists('get_plugin_data')) {
      require_once ABSPATH . 'wp-admin/includes/plugin.php';
    }

    $plugin_data = get_plugin_data(WP_PLUGIN_DIR . '/' . $this->plugin_file);
    return $plugin_data['Version'] ?? '0.0.0';
  }

  private function get_current_plugin_data()
  {
    if (!function_exists('get_plugin_data')) {
      require_once ABSPATH . 'wp-admin/includes/plugin.php';
    }

    return get_plugin_data(WP_PLUGIN_DIR . '/' . $this->plugin_file);
  }

  private function get_latest_release()
  {
    $cached = get_transient($this->cache_key);
    if (is_array($cached) && !empty($cached['version'])) {
      return $cached;
    }

    $url = 'https://api.github.com/repos/' . $this->repo . '/releases/latest';
    $response = wp_remote_get($url, [
      'timeout' => 15,
      'headers' => [
        'Accept' => 'application/vnd.github+json',
        'User-Agent' => 'WordPress; ' . home_url('/'),
      ],
    ]);

    if (is_wp_error($response)) {
      return false;
    }

    $code = (int) wp_remote_retrieve_response_code($response);
    if ($code < 200 || $code >= 300) {
      return false;
    }

    $body = wp_remote_retrieve_body($response);
    $data = json_decode($body, true);
    if (!is_array($data) || empty($data['tag_name'])) {
      return false;
    }

    $version = ltrim((string) $data['tag_name'], 'vV');
    $package = $this->select_package_url($data);
    if (empty($package)) {
      return false;
    }

    $release = [
      'version' => $version,
      'package' => $package,
      'details_url' => $data['html_url'] ?? ('https://github.com/' . $this->repo . '/releases'),
      'body' => $data['body'] ?? '',
      'published_at' => $data['published_at'] ?? '',
    ];

    set_transient($this->cache_key, $release, $this->cache_ttl);
    return $release;
  }

  private function select_package_url($release_data)
  {
    if (!empty($release_data['assets']) && is_array($release_data['assets'])) {
      foreach ($release_data['assets'] as $asset) {
        $name = isset($asset['name']) ? (string) $asset['name'] : '';
        $download = isset($asset['browser_download_url']) ? (string) $asset['browser_download_url'] : '';
        if ($download !== '' && $name !== '' && preg_match('/\\.zip$/i', $name)) {
          return $download;
        }
      }
    }

    if (!empty($release_data['zipball_url'])) {
      return (string) $release_data['zipball_url'];
    }

    return '';
  }

  public function add_link_to_settings($actions, $plugin_file)
  {
    if ($plugin_file === $this->plugin_file) {
      // Point to the main Velocity Addons settings page.
      $url = admin_url('admin.php?page=admin_velocity_addons');
      $actions['link_to_settings'] = '<a href="' . $url . '">Pengaturan Admin</a>';
    }
    return $actions;
  }
}

// Inisialisasi updater
new Velocity_Addons_Auto_Updater();
