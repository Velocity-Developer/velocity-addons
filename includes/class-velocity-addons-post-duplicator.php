<?php

/**
 * Register post duplicator in the WordPress admin panel
 *
 * @link       https://velocitydeveloper.com
 * @since      1.0.0
 *
 * @package    Velocity_Addons
 * @subpackage Velocity_Addons/includes
 */

class Velocity_Addons_Post_Duplicator {
    
    public function __construct() {
        add_action('admin_action_duplicate_post', array($this, 'duplicate_post_as_draft'));
        add_filter('post_row_actions', array($this, 'duplicate_post_link'), 10, 2);
        add_filter('page_row_actions', array($this, 'duplicate_post_link'), 10, 2); // Untuk Custom Post Type (CPT)
        // Tambahkan tombol Duplicate di admin bar pada halaman edit post
        add_action('admin_bar_menu', array($this, 'add_admin_bar_duplicate_link'), 100);
    }

    public function duplicate_post_as_draft() {
        if (!isset($_GET['post']) || !isset($_GET['action']) || $_GET['action'] !== 'duplicate_post') {
            wp_die('No post to duplicate has been supplied!');
        }

        // Cek nonce untuk keamanan
        if (!isset($_GET['duplicate_nonce']) || !wp_verify_nonce($_GET['duplicate_nonce'], 'duplicate_post_nonce')) {
            wp_die('Security check failed');
        }

        $post_id = absint($_GET['post']);
        $post = get_post($post_id);

        if ($post) {
            // Buat post baru
            $new_post_id = wp_insert_post(array(
                'post_title'   => $post->post_title . ' Copy',
                'post_content' => $post->post_content,
                'post_status'  => 'draft',
                'post_type'    => $post->post_type,
                'post_author'  => get_current_user_id(),
            ));

            if (!is_wp_error($new_post_id)) {
                // Copy taxonomies
                $this->duplicate_taxonomies($post_id, $new_post_id, $post->post_type);

                // Copy custom post meta
                $this->duplicate_meta($post_id, $new_post_id);

                // Copy featured image
                $this->duplicate_featured_image($post_id, $new_post_id);

                // Redirect ke halaman edit post baru
                wp_safe_redirect(admin_url('post.php?action=edit&post=' . $new_post_id));
                exit;
            }
        } else {
            wp_die('Post creation failed, could not find original post: ' . $post_id);
        }
    }

    private function duplicate_taxonomies($post_id, $new_post_id, $post_type) {
        $taxonomies = get_object_taxonomies($post_type);
        foreach ($taxonomies as $taxonomy) {
            $post_terms = wp_get_object_terms($post_id, $taxonomy, array('fields' => 'slugs'));
            wp_set_object_terms($new_post_id, $post_terms, $taxonomy);
        }
    }

    private function duplicate_meta($post_id, $new_post_id) {
        $post_metas = get_post_meta($post_id);
        foreach ($post_metas as $meta_key => $meta_value) {
            // Lewati meta key internal WordPress
            if (!in_array($meta_key, array('_edit_lock', 'wp_old_slug'))) {
                update_post_meta($new_post_id, $meta_key, maybe_unserialize($meta_value[0]));
            }
        }
    }

    private function duplicate_featured_image($post_id, $new_post_id) {
        $thumbnail_id = get_post_thumbnail_id($post_id);
        if ($thumbnail_id) {
            set_post_thumbnail($new_post_id, $thumbnail_id);
        }
    }

    public function duplicate_post_link($actions, $post) {
        if (current_user_can('edit_posts')) {
            $actions['duplicate'] = '<a href="' . wp_nonce_url(admin_url('admin.php?action=duplicate_post&post=' . $post->ID), 'duplicate_post_nonce', 'duplicate_nonce') . '" title="Duplicate this post" rel="permalink">Duplicate</a>';
        }
        return $actions;
    }

    // Tambah link Duplicate di Admin Bar saat berada di halaman edit post (Classic & Gutenberg)
    public function add_admin_bar_duplicate_link($wp_admin_bar) {
        if ( ! is_admin() || ! current_user_can('edit_posts') ) {
            return;
        }

        // Berlaku untuk layar edit post (classic maupun Gutenberg)
        $post_id = isset($_GET['post']) ? absint($_GET['post']) : 0;
        $action  = isset($_GET['action']) ? sanitize_key($_GET['action']) : '';
        if ( $action !== 'edit' ) {
            // Fallback: cek current screen bila tersedia
            if ( function_exists('get_current_screen') ) {
                $screen = get_current_screen();
                if ( ! $screen || $screen->base !== 'post' ) {
                    return;
                }
            } else {
                return;
            }
        }
        if ( ! $post_id ) {
            return;
        }

        $url = wp_nonce_url(
            admin_url('admin.php?action=duplicate_post&post=' . $post_id),
            'duplicate_post_nonce',
            'duplicate_nonce'
        );

        $wp_admin_bar->add_node(array(
            'id'    => 'vd-duplicate-post',
            'title' => __('Duplicate', 'velocity-addons'),
            'href'  => $url,
            'meta'  => array('title' => __('Duplicate this post', 'velocity-addons')),
        ));
    }
}

// Inisialisasi class
new Velocity_Addons_Post_Duplicator();
