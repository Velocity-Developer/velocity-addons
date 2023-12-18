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

         add_action('admin_menu', array($this, 'add_seo_menu'));
         add_action('admin_init', array($this, 'register_seo_settings'));
         add_action('admin_enqueue_scripts', array($this, 'enqueue_media_uploader'));
         add_action('wp_head', array($this, 'output_seo_meta_tags'));
     }
 
     public function add_seo_menu()
     {
         add_menu_page(
             'SEO Settings',
             'SEO Settings',
             'manage_options',
             'velocity_seo_settings',
             array($this, 'render_seo_settings_page'),
             'dashicons-search',
             20
         );
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
             wp_enqueue_script('velocity-seo-script', plugin_dir_url(__FILE__) . 'velocity-seo-script.js', array('jquery'), '1.0', true);
         }
     }
 
     public function render_seo_settings_page()
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
                         <td><input type="text" name="home_title" value="<?php echo esc_attr(get_option('home_title', $default_title)); ?>" /></td>
                     </tr>
                     <tr valign="top">
                         <th scope="row">Home Description</th>
                         <td><textarea name="home_description" rows="4" cols="40"><?php echo esc_textarea(get_option('home_description', $default_description)); ?></textarea></td>
                     </tr>
                     <tr valign="top">
                         <th scope="row">Home Keywords</th>
                         <td><textarea name="home_keywords" rows="4" cols="40"><?php echo esc_textarea(get_option('home_keywords')); ?></textarea></td>
                     </tr>
                     <tr valign="top">
                         <th scope="row">Share Image</th>
                         <td>
                             <input type="text" name="share_image" id="share_image" value="<?php echo esc_attr(get_option('share_image')); ?>" />
                             <button type="button" class="button button-secondary" id="upload_image_button">Upload Image</button>
                             <div>
                                <ul>
                                    <li><span class="dashicons dashicons-marker"></span> Dimensi gambar minimum yang diizinkan adalah 200 x 200 piksel.</li>
                                    <li><span class="dashicons dashicons-marker"></span> Ukuran file gambar tidak boleh lebih dari 8 MB.</li>
                                    <li><span class="dashicons dashicons-marker"></span> Gunakan gambar berukuran minimal 1200 x 630 piksel untuk tampilan terbaik pada perangkat beresolusi tinggi. Minimal, Anda harus menggunakan gambar berukuran 600 x 315 piksel untuk menampilkan postingan halaman tautan dengan gambar yang lebih besar.</li>
                                    <li><span class="dashicons dashicons-marker"></span> Jika gambar Anda lebih kecil dari 600 x 315 piksel, gambar akan tetap ditampilkan di postingan halaman tautan, tetapi ukurannya akan jauh lebih kecil.</li>
                                    <li><span class="dashicons dashicons-marker"></span> Kami juga telah mendesain ulang postingan halaman tautan sehingga rasio aspek untuk gambar sama di seluruh Kabar desktop dan seluler. Coba sebisa mungkin untuk mempertahankan rasio aspek gambar mendekati 1,91:1 untuk menampilkan gambar penuh di Kabar tanpa pemotongan.</li>
                                </ul>
                             </div>
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
         // Mendapatkan nilai dari pengaturan SEO
         $home_title = get_option('home_title');
         $home_description = get_option('home_description');
         $home_keywords = get_option('home_keywords');
         $share_image = get_option('share_image');
 
         // Menampilkan meta tag untuk SEO
         echo '<meta name="description" content="' . esc_attr($home_description) . '" />' . "\n";
         echo '<meta name="keywords" content="' . esc_attr($home_keywords) . '" />' . "\n";
 
         // Menampilkan og tags untuk Facebook
         echo '<meta property="og:title" content="' . esc_attr($home_title) . '" />' . "\n";
         echo '<meta property="og:description" content="' . esc_attr($home_description) . '" />' . "\n";
         echo '<meta property="og:image" content="' . esc_url($share_image) . '" />' . "\n";
     }

 }
 
 // Inisialisasi class Velocity_Addons_SEO
 $velocity_seo = new Velocity_Addons_SEO();
 