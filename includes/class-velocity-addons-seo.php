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
        add_action('admin_init', [$this, 'register_seo_settings']);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_media_uploader']);
        add_action('wp_head', [$this, 'output_seo_meta_tags'], 2);
        
        // Tambahkan action untuk menambahkan metabox
        add_action('add_meta_boxes', [$this, 'custom_meta_seo']);
        
        // Tambahkan action untuk menyimpan data
        add_action('save_post', [$this, 'save_meta_box_data']);
    }
 
     public function register_seo_settings()
     {
         register_setting('velocity_seo_group', 'home_title');
         register_setting('velocity_seo_group', 'home_description');
         register_setting('velocity_seo_group', 'home_keywords');
         register_setting('velocity_seo_group', 'share_image');
         register_setting('velocity_seo_group', 'seo_post_types');
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
        $seo_post_types = get_option('seo_post_types');
        
        // Cek apakah kosong atau tidak valid
        if (empty($seo_post_types) || !is_array($seo_post_types)) {
            // Set nilai default
            $seo_post_types = ['post', 'page'];

            // Simpan ke database untuk penggunaan berikutnya
            update_option('seo_post_types', $seo_post_types);
        }
        
        // Ambil semua post type yang terdaftar
        $all_post_types = get_post_types();
        
        // Post type Beaver Builder yang perlu dikecualikan
        $excluded_post_types = ['wp_block', 'wp_template', 'wp_template_part', 'wp_global_styles', 'wp_navigation', 'wp_font_family', 'wp_font_face', 'fl-builder-template', 'fl-builder-history', 'fl-builder-template', 'fl-theme-layout', 'attachment', 'revision', 'nav_menu_item', 'custom_css', 'customize_changeset', 'oembed_cache', 'user_request'];
    
        // Filter post type untuk menghapus Beaver Builder dan attachment
        $post_types = array_diff($all_post_types, $excluded_post_types);
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
                    <tr valign="top">
                        <th scope="row">SEO Single</th>
                        <td>
                            <?php foreach ($post_types as $post_type) :?>
                            <?php $label_post_type = str_replace('_', ' ', $post_type);?>
                            <div style="padding: 5px 0;">
                                <label>
                                    <input type="checkbox" name="seo_post_types[]" value="<?php echo $post_type;?>"
                                    <?php 
                                    if(!empty($seo_post_types)) :
                                        echo in_array($post_type, $seo_post_types) ? 'checked' : '';
                                    endif; ?>>
                                    <span style="text-transform: capitalize;"><?php echo $label_post_type;?></span>
                                </label>
                            </div>
                            <?php endforeach;?>
                        </td>
                    </tr>
                </table>
                <?php submit_button(); ?>
            </form>
        </div>
        <?php
    }
    
    public function output_seo_meta_tags() {
        echo "\n".' <!-- SEO by Velocity Developer -->' . "\n";

            // Mendapatkan nilai dari pengaturan SEO
            $home_title         = get_option('home_title');
            $home_description   = get_option('home_description');
            $home_keywords      = get_option('home_keywords');
            $share_image        = get_option('share_image');
      
            // Mendapatkan ID gambar berdasarkan kondisi yang dijelaskan
            $image_id = $this->get_seo_image_id();
            // Mendapatkan URL gambar
            $meta_img = $image_id ? wp_get_attachment_image_src($image_id, 'large')[0] : $share_image;

            ///jika halaman HOME
            if ( is_home() || is_front_page() ) {
                $meta_url       = get_home_url();
                $meta_title     = $home_title;
                $meta_desc      = $home_description;
                $meta_keywords  = $home_keywords;
                $meta_type      = 'website';
            } else if(is_archive()) { 
                //jika halaman ARCHIVE
                $meta_url       = get_the_permalink();
                $meta_title     = get_the_archive_title();
                $meta_desc      = get_the_archive_description();
                $meta_desc      = $meta_desc?$meta_desc:$home_description;
                $meta_keywords  = $home_keywords;
                $meta_img       = $share_image;
                $meta_type      = 'article';
            } else {
                $meta_url       = get_permalink();
                $meta_title     = get_post_meta(get_the_ID(), 'seo_post_title',true);
                $meta_desc      = get_post_meta(get_the_ID(), 'seo_post_description',true);
                $meta_keywords  = get_post_meta(get_the_ID(), 'seo_post_keyword',true);
                $meta_type      = 'article';
                $meta_excerpt   = get_the_excerpt();
                
                // Jika $meta_title kosong, ambil title post
                if(empty($meta_title)) {
                    $meta_title = get_the_title();
                }
                
                // Jika $meta_keywords kosong, ambil home keywords
                if(empty($meta_keywords)) {
                    $meta_keywords = $home_keywords;
                }
                // Jika excerpt kosong, potong dari konten
                if (empty($meta_desc)) {
                    $meta_desc = $meta_excerpt;
                } else if(empty($meta_excerpt)) {
                    $meta_desc = wp_trim_words(wp_strip_all_tags(get_the_content()), 10);
                }
            }

            // Menampilkan og tags
            echo '<meta property="og:type" content="'.$meta_type.'" />' . "\n";
            echo '<meta property="og:site_name" content="'.get_bloginfo( 'name' ).'" />' . "\n";

            echo '<meta property="url" content="' . esc_attr($meta_url) . '" />' . "\n";
            echo '<meta name="description" content="' . esc_attr($meta_desc) . '" />' . "\n";
            echo '<meta name="keywords" content="' . esc_attr($meta_keywords) . '" />' . "\n";

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
     
     public function custom_meta_seo(){
         $seo_post_types = get_option('seo_post_types');

         // Untuk Post
        add_meta_box(
            'metabox_seo', // ID unik untuk metabox
            'Velocity Post SEO', // Judul metabox
            [$this, 'seo_meta_box_callback'], // Fungsi callback untuk output
            $seo_post_types, // Post types yang mendukung
            'normal', // Lokasi (normal, side, atau advanced)
            'high' // Prioritas,
        );
     }
     
     // Callback untuk menampilkan input di metabox
    public function seo_meta_box_callback($post) {
        global $post;
        
        // Ambil data meta jika tersedia
        $post_title = get_post_meta($post->ID, 'seo_post_title', true);
        $post_title = empty($post_title) ? get_the_title($post->ID) : $post_title;
        $content = get_the_content($post->ID);
        if (preg_match('/https?:\/\/(www\.)?youtube\.com|youtu\.be/', $content)) {
            $content = ''; // Kosongkan konten jika ada link YouTube
        }
        $post_description   = get_post_meta($post->ID, 'seo_post_description', true);
        $post_description   = empty($post_description) ? wp_trim_words( $content, 10, '' ) : $post_description;
        
        $post_keyword = get_post_meta($post->ID, 'seo_post_keyword', true);
    
        // Nonce untuk keamanan
        wp_nonce_field('custom_meta_seo_nonce', 'custom_meta_seo_nonce_field');
    
        // Form input
        echo '<p><label for="seo_post_title">Post Title</label></p>';
        echo '<input type="text" id="seo_post_title" name="seo_post_title" value="' . esc_attr($post_title) . '" style="width:100%;"/>';
        
        echo '<p><label for="seo_post_description">Post Description</label></p>';
        echo '<textarea id="seo_post_description" name="seo_post_description" style="width:100%;">' . esc_textarea($post_description) . '</textarea>';
        
        echo '<p><label for="seo_post_keyword">Post Keyword <br/><small>Pisahkan keyword dengan koma (,)</small></label></p>';
        echo '<textarea type="text" id="seo_post_keyword" name="seo_post_keyword"style="width:100%;"/>' . esc_attr($post_keyword) . '</textarea>';
    }
    
    // Simpan data dari custom fields
    public function save_meta_box_data($post_id) {
        // Verifikasi nonce
        if (!isset($_POST['custom_meta_seo_nonce_field']) || 
            !wp_verify_nonce($_POST['custom_meta_seo_nonce_field'], 'custom_meta_seo_nonce')) {
            return;
        }

        // Jangan simpan jika autosave
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }

        // Periksa izin user
        if (!current_user_can('edit_post', $post_id)) {
            return;
        }

        // Simpan data meta
        if (isset($_POST['seo_post_title'])) {
            update_post_meta($post_id, 'seo_post_title', sanitize_text_field($_POST['seo_post_title']));
        }
        if (isset($_POST['seo_post_description'])) {
            update_post_meta($post_id, 'seo_post_description', sanitize_textarea_field($_POST['seo_post_description']));
        }
        if (isset($_POST['seo_post_keyword'])) {
            update_post_meta($post_id, 'seo_post_keyword', sanitize_text_field($_POST['seo_post_keyword']));
        }
    }

 }
 
 // Inisialisasi class Velocity_Addons_SEO
 $velocity_seo = new Velocity_Addons_SEO();
 