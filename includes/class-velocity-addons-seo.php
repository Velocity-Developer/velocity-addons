<?php
/**
 * Register SEO settings in the WordPress admin panel
 *
 * @link       https://velocitydeveloper.com
 * @since      1.0.0
 *
 * @package    Velocity_Addons
 * @subpackage Velocity_Addons/includes
 */

 class Velocity_Addons_SEO
 {
    public function __construct()
    {
    $seo_velocity = get_option('seo_velocity','1');
    if($seo_velocity !== '1')
    return false;

    // Menambahkan submenu
    add_action('admin_init', array($this, 'register_seo_settings'));
    add_action('admin_enqueue_scripts', array($this, 'enqueue_media_uploader'));
    add_action('wp_head', array($this, 'output_seo_meta_tags'), 2);
    }
 
     public function register_seo_settings()
     {
         register_setting('velocity_seo_group', 'home_title');
         register_setting('velocity_seo_group', 'home_description');
         register_setting('velocity_seo_group', 'home_keywords');
         register_setting('velocity_seo_group', 'share_image');
     }
 
     public function enqueue_media_uploader()
     {
         if (isset($_GET['page']) && $_GET['page'] == 'velocity_seo_settings') {
             wp_enqueue_media();
         }
     }
 
    public static function render_seo_settings_page()
    {
        // Mendapatkan nilai default dari setting umum (general settings)
        $default_title = get_bloginfo('name');
        $default_description = get_bloginfo('description');

        ?>
        <div class="wrap">
            <h2>SEO Settings</h2>
            <form method="post" action="options.php">
                <?php settings_fields('velocity_seo_group'); ?>
                <?php do_settings_sections('velocity_seo_group'); ?>
                <table class="form-table">
                    <tr valign="top">
                        <th scope="row">Home Title</th>
                        <td><input class="regular-text" type="text" name="home_title" value="<?php echo esc_attr(get_option('home_title', $default_title)); ?>" /></td>
                    </tr>
                    <tr valign="top">
                        <th scope="row">Home Description</th>
                        <td><textarea class="large-text" name="home_description" rows="4" cols="40"><?php echo esc_textarea(get_option('home_description', $default_description)); ?></textarea></td>
                    </tr>
                    <tr valign="top">
                        <th scope="row">Home Keywords</th>
                        <td><textarea class="large-text" name="home_keywords" rows="4" cols="40"><?php echo esc_textarea(get_option('home_keywords')); ?></textarea></td>
                    </tr>
                    <tr valign="top">
                        <th scope="row">Share Image</th>
                        <td>
                            <input type="text" class="regular-text" name="share_image" id="share_image" value="<?php echo esc_attr(get_option('share_image')); ?>" />
                            <button type="button" class="button button-secondary" id="upload_image_button">Upload Image</button>
                            <br>
                            <div class="preview_share_image">
                            <?php if(get_option('share_image')): ?>
                                <br>
                                <img id="preview_image" width="300" src="<?php echo esc_attr(get_option('share_image')); ?>" />
                                <br><span class="delete_share_image button">Delete</span>
                            <?php endif; ?>
                            </div>
                            <br>
                            <span class="dashicons dashicons-info-outline"></span> <a href="https://developers.facebook.com/docs/sharing/best-practices#gambar" target="_blank">Pelajari tentang praktik terbaik untuk menerapkan <strong>"Berbagi di Facebook"</strong></a>
                        </td>
                    </tr>
                </table>
                <?php submit_button(); ?>
            </form>
        </div>
        <?php
    }
     public function output_seo_meta_tags()
     {
        echo "\n".' <!-- SEO by Velocity Developer -->' . "\n";

            // Mendapatkan nilai dari pengaturan SEO
            $home_title        = get_option('home_title');
            $home_description  = get_option('home_description');
            $home_keywords     = get_option('home_keywords');
            $share_image       = get_option('share_image');
      
            // Mendapatkan ID gambar berdasarkan kondisi yang dijelaskan
            $image_id = $this->get_seo_image_id();
            // Mendapatkan URL gambar
            $meta_img = $image_id ? wp_get_attachment_image_src($image_id, 'large')[0] : $share_image;

            ///jika halaman HOME
            if ( is_home() || is_front_page() ) {
                $meta_url       = get_home_url();
                $meta_title     = $home_title;
                $meta_desc      = $home_description;
                $meta_type      = 'website';
            } else if(is_archive()) { 
                //jika halaman ARCHIVE
                $meta_url       = get_the_permalink();
                $meta_title     = get_the_archive_title();
                $meta_desc      = get_the_archive_description();
                $meta_desc      = $meta_desc?$meta_desc:$home_description;
                $meta_img       = $share_image;
                $meta_type      = 'article';
            } else {
                $meta_url       = get_permalink();
                $meta_title     = get_the_title();
                $meta_desc      = get_the_excerpt();
                $meta_type      = 'article';

                // Jika excerpt kosong, potong dari konten
                if (empty($meta_desc)) {
                    $meta_desc = wp_trim_words(wp_strip_all_tags(get_the_content()), 20);
                }
            }

            // Menampilkan og tags
            echo '<meta property="og:type" content="'.$meta_type.'" />' . "\n";
            echo '<meta property="og:site_name" content="'.get_bloginfo( 'name' ).'" />' . "\n";

            echo '<meta property="url" content="' . esc_attr($meta_url) . '" />' . "\n";
            echo '<meta name="description" content="' . esc_attr($meta_desc) . '" />' . "\n";
            echo '<meta name="keywords" content="' . esc_attr($home_keywords) . '" />' . "\n";

            echo '<meta property="og:image" content="' . esc_url($meta_img) . '" />' . "\n";
            echo '<meta property="og:title" content="' . esc_attr($meta_title) . '" />' . "\n";
            echo '<meta property="og:description" content="' . esc_attr($meta_desc) . '" />' . "\n";

        echo ' <!-- / SEO by Velocity Developer -->' . "\n\n";
     }
 
     // Function untuk mendapatkan ID gambar berdasarkan kondisi
     private function get_seo_image_id() {
         global $post;
 
         if (is_page()) {
             // Jika post type page, ambil dari featured image,
             $image_id = get_post_thumbnail_id();
         } else {
             // Selain page, ambil dari featured image, jika tidak ada, ambil dari post pertama, jika tidak ada, ambil dari share image
             $image_id = get_post_thumbnail_id();
             if (!$image_id) {
                 $args = array(
                     'post_type' => 'post',
                     'posts_per_page' => 1,
                 );
                 $recent_posts = get_posts($args);
                 if ($recent_posts) {
                     $image_id = get_post_thumbnail_id($recent_posts[0]->ID);
                 }
             }
             if (!$image_id) {
                 $image_id = $this->get_first_image_id_from_content($recent_posts[0]->post_content);
             }
         }
 
         return $image_id;
     }
 
     // Function untuk mendapatkan ID gambar pertama dari konten
     private function get_first_image_id_from_content($content) {
         $first_image_id = 0;
 
         preg_match_all('/<img[^>]+>/', $content, $matches);
 
         if (isset($matches[0][0])) {
             preg_match_all('/src=[\'"]([^\'"]+)[\'"]/i', $matches[0][0], $image);
             if (isset($image[1][0])) {
                 $image_url = $image[1][0];
                 $first_image_id = attachment_url_to_postid($image_url);
             }
         }
 
         return $first_image_id;
     }

 }
 
 // Inisialisasi class Velocity_Addons_SEO
 $velocity_seo = new Velocity_Addons_SEO();
 