<?php

/**
 * Register Gallery settings in the WordPress admin panel
 *
 * @link       https://velocitydeveloper.com
 * @since      1.0.0
 *
 * @package    Velocity_Addons
 * @subpackage Velocity_Addons/includes
 */

class Velocity_Addons_Gallery {
    public function __construct() {

        $gallery_velocity = get_option('velocity_gallery','1');
        if($gallery_velocity !== '1')
        return false;

        $this->vdgallery_dependency();

        // Menambahkan post type gallery
        // Hook JS & CSS
        add_action('admin_enqueue_scripts', [$this, 'admin_vdgallery_enqueue']);
        add_action( 'wp_enqueue_scripts', [$this, 'vdgallery_scripts_enqueue'] );

        add_action('init', [$this, 'vdgallery_post_type']);
        add_action('add_meta_boxes', [$this, 'vdgallery_dependency']);
        add_action('admin_enqueue_scripts', [$this, 'media_upload']);
        add_filter( 'manage_vdgallery_posts_columns', [$this, 'set_custom_edit_vdgallery_columns'] );
        add_action( 'manage_vdgallery_posts_custom_column' , [$this, 'custom_vdgallery_column'], 10, 2 );
    }
    
    public function vdgallery_dependency() {
        /**
         * Register meta boxes.
         * vdgallery-meta
         */
        require_once plugin_dir_path(dirname(__FILE__)) . 'admin/partials/vdgallery-metabox.php';
    }

    /**
        * Register script to dashboard area.
        * vdgallery-script
        */
    public function admin_vdgallery_enqueue($hook) {
        wp_enqueue_script('admin-vdgallery-script', plugin_dir_url(dirname(__FILE__)) . 'admin/js/vdgallery-admin.js');
        wp_enqueue_style( 'admin-vdgallery-style', plugin_dir_url(dirname(__FILE__)) . 'admin/css/vdgallery-admin.css');
    }

    /**
	 * Load plugin sources.
	 */
	public function vdgallery_scripts_enqueue() {
        //CSS
        wp_enqueue_style( 'flickity-styles', 'https://unpkg.com/flickity@2/dist/flickity.min.css', [], VELOCITY_ADDONS_VERSION, false );
		wp_enqueue_style( 'magnific-popup-styles', 'https://cdnjs.cloudflare.com/ajax/libs/magnific-popup.js/1.0.0/magnific-popup.min.css', [], VELOCITY_ADDONS_VERSION, false );
		wp_enqueue_style( 'vdgallery-styles', plugin_dir_url(dirname(__FILE__)) . 'public/css/vd-gallery.css', [], VELOCITY_ADDONS_VERSION, false );

        //JS
		wp_enqueue_script( 'flickity-script', 'https://unpkg.com/flickity@2/dist/flickity.pkgd.min.js', [], VELOCITY_ADDONS_VERSION, true );
		wp_enqueue_script( 'magnific-popup-script', 'https://cdnjs.cloudflare.com/ajax/libs/magnific-popup.js/1.0.0/jquery.magnific-popup.min.js', [], VELOCITY_ADDONS_VERSION, true );
        wp_enqueue_script( 'vdgallery-script', plugin_dir_url(dirname(__FILE__)) . 'public/js/vd-gallery.js', [], VELOCITY_ADDONS_VERSION, true );
	}

    public function vdgallery_post_type()
    {
        register_post_type('vdgallery', [
            'labels' => [
                'name' => 'VD Gallery',
                'singular_name' => 'vdgallery',
                'add_new' => 'Tambah Galeri Baru',
                'add_new_item' => 'Tambah Galeri Baru',
                'edit_item' => 'Edit Galeri',
                'view_item' => 'Lihat Galeri',
                'search_items' => 'Cari Galeri',
                'not_found' => 'Tidak ditemukan',
                'not_found_in_trash' => 'Tidak ada galeri di kotak sampah'
            ],
            'menu_icon' => 'dashicons-images-alt2',
            'public' => true,
            'exclude_from_search' => true,
            'show_in_admin_bar'   => false,
            'show_in_nav_menus'   => false,
            'publicly_queryable'  => false,
            'query_var'           => false,
            'supports' => ['title'],
       ]);
    }

    /**
     * Call file media Upload.
     * shortcode
     */
    public function media_upload() {
        global $post;

        wp_enqueue_script('media-upload');
        wp_enqueue_script('thickbox');
        wp_enqueue_style('thickbox');
        if(isset($post->ID)) {
            wp_enqueue_media(array(
                'post' => $post->ID,
            ));
        }
    }

    /**
     * Custom columns vdgallery.
     */
    public function set_custom_edit_vdgallery_columns($columns) {
        $columns['sgaleri']     = __( 'Shortcode Galeri', 'vdgallery' );
        $columns['sslideshow']  = __( 'Shortcode Slideshow', 'vdgallery' );
        return $columns;
    }
    
    public function custom_vdgallery_column( $column, $post_id ) {
        switch ( $column ) {
            case 'sgaleri' :
                echo '[vdgallery id="'.$post_id.'"]';
                break;
            case 'sslideshow' :
                echo '[vdgalleryslide id="'.$post_id.'"]';
                break;
        }
    }

}

// Inisialisasi class Velocity_Addons_Gallery
$velocity_gallery = new Velocity_Addons_Gallery();