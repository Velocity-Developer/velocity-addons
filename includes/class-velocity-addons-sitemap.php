<?php

/**
 * Sitemap XML Generator
 *
 * @link       https://velocitydeveloper.com
 * @since      1.0.0
 *
 * @package    Velocity_Addons
 * @subpackage Velocity_Addons/includes
 */

class Velocity_Addons_Sitemap
{
  public function __construct()
  {
    // Hook to flush rules when option is updated
    add_action('update_option_enable_xml_sitemap', [$this, 'flush_rules_on_update'], 10, 2);

    // Clear cache on post update
    add_action('save_post', [$this, 'clear_sitemap_cache']);
    add_action('publish_post', [$this, 'clear_sitemap_cache']);
    add_action('delete_post', [$this, 'clear_sitemap_cache']);

    add_action('init', [$this, 'add_rewrite_rules']);
    add_filter('query_vars', [$this, 'add_query_vars']);
    add_action('template_redirect', [$this, 'render_sitemap']);

    // Disable WP Core sitemap if enabled
    add_filter('wp_sitemaps_enabled', [$this, 'disable_wp_sitemaps']);

    // Attempt one-time flush
    add_action('init', [$this, 'maybe_flush_rewrite_rules'], 99);
  }

  public function maybe_flush_rewrite_rules()
  {
    if (get_option('velocity_sitemap_flushed_v1') !== 'yes') {
      $this->add_rewrite_rules();
      flush_rewrite_rules();
      update_option('velocity_sitemap_flushed_v1', 'yes');
    }
  }

  public function disable_wp_sitemaps($enabled)
  {
    if (get_option('enable_xml_sitemap', '1') === '1') {
      return false;
    }
    return $enabled;
  }

  public function clear_sitemap_cache()
  {
    delete_transient('velocity_sitemap_xml');
  }

  public function add_rewrite_rules()
  {
    if (get_option('enable_xml_sitemap', '1') === '1') {
      add_rewrite_rule('^sitemap\.xml$', 'index.php?velocity_sitemap=1', 'top');
    }
  }

  public function add_query_vars($vars)
  {
    $vars[] = 'velocity_sitemap';
    return $vars;
  }

  public function render_sitemap()
  {
    // Double check option to prevent execution if disabled but rule persists
    if (get_option('enable_xml_sitemap', '1') !== '1') {
      return;
    }

    if (get_query_var('velocity_sitemap')) {
      header('Content-Type: application/xml; charset=utf-8');

      // Check cache first
      $sitemap = get_transient('velocity_sitemap_xml');
      if ($sitemap !== false) {
        echo $sitemap;
        exit;
      }

      $posts = $this->get_all_posts();

      // Get XSL stylesheet URL
      $xsl_url = plugins_url('public/sitemap.xsl', dirname(__FILE__));

      ob_start();
      echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
      echo '<?xml-stylesheet type="text/xsl" href="' . esc_url($xsl_url) . '"?>' . "\n";
      echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";

      // Homepage
      echo "\t<url>\n";
      echo "\t\t<loc>" . esc_url(home_url('/')) . "</loc>\n";
      echo "\t\t<lastmod>" . date('Y-m-d\TH:i:s+00:00', current_time('timestamp')) . "</lastmod>\n";
      echo "\t\t<changefreq>daily</changefreq>\n";
      echo "\t\t<priority>1.0</priority>\n";
      echo "\t</url>\n";

      foreach ($posts as $post) {
        $lastmod = get_the_modified_time('Y-m-d\TH:i:s+00:00', $post->ID);
        echo "\t<url>\n";
        echo "\t\t<loc>" . esc_url(get_permalink($post->ID)) . "</loc>\n";
        echo "\t\t<lastmod>" . $lastmod . "</lastmod>\n";
        echo "\t\t<changefreq>monthly</changefreq>\n";
        echo "\t\t<priority>0.8</priority>\n";
        echo "\t</url>\n";
      }

      echo '</urlset>';

      $sitemap = ob_get_clean();

      // Cache for 12 hours
      set_transient('velocity_sitemap_xml', $sitemap, 12 * HOUR_IN_SECONDS);

      echo $sitemap;
      exit;
    }
  }

  private function get_all_posts()
  {
    // Get allowed post types from SEO settings if available, or default to post/page
    $post_types = get_option('seo_post_types', ['post', 'page']);

    $args = [
      'post_type'      => $post_types,
      'post_status'    => 'publish',
      'posts_per_page' => -1,
      'orderby'        => 'modified',
      'order'          => 'DESC',
      'no_found_rows'  => true,
      'update_post_meta_cache' => false,
      'update_post_term_cache' => false,
    ];
    return get_posts($args);
  }

  public function flush_rules_on_update($old_value, $value)
  {
    if ($old_value !== $value) {
      // If enabling, ensure rule is added before flush
      if ($value === '1') {
        $this->add_rewrite_rules();
      }
      flush_rewrite_rules();
    }
  }
}

new Velocity_Addons_Sitemap();
