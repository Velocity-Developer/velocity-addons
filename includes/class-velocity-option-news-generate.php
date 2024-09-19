<?php

/**
 * Register News Generate settings in the WordPress admin panel
 *
 * @link       https://velocitydeveloper.com
 * @since      1.0.0
 *
 * @package    Velocity_Addons
 * @subpackage Velocity_Addons/includes
 */

class Velocity_Addons_News
{
    
    public function __construct()
    {

        $news_generate = get_option('news_generate', '1');

        if ($news_generate !== '1')
            return false;

        add_action('admin_menu', array($this, 'add_news_generate_menu'));
    }

    // Mengambil data dari API
    private function fetch_post($item=null, $cat_id=null, $count=5) {
        $data   = [
            'cat='.$cat_id,
            'number='.$count,
        ];
        $url        = 'https://api.velocitydeveloper.id/wp-json/news/v1/'.$item.'?'.implode("&",$data);
        
        $response   = wp_remote_get( $url );
        $response   = !is_wp_error( $response ) ? json_decode( wp_remote_retrieve_body( $response ), true ) : [];
        
        return $response; // Mengembalikan data dalam bentuk array
    }

    private function fetch_category() {
        $url        = 'https://api.velocitydeveloper.id/wp-json/news/v1/cat';
        
        $response   = wp_remote_get( $url );
        $response   = !is_wp_error( $response ) ? json_decode( wp_remote_retrieve_body( $response ), true ) : $response->get_error_message();
        
        return $response; // Mengembalikan data dalam bentuk array
    }

    public function fetch_news_scraper($target, $category, $count, $status) {
        ob_start();
        // Mengambil kategori dan post
        $posts_datas = $this->fetch_post('posts',$target, $count);
        
        foreach($posts_datas as $posts_data):
            $title = (string) $posts_data['title'];
            $content = (string) $posts_data['content'];
            $thumbnail = (string) $posts_data['thumb_url'] ?? '';

            echo $this->save_news_post($title, $content, $thumbnail, $category, $status);
        endforeach;

        return ob_get_clean();
    }

    // Fungsi untuk menyimpan artikel sebagai post di WordPress
    public function save_news_post($title, $content, $thumbnail, $category, $status) {
        ob_start();
        $existing_post = get_page_by_title($title, OBJECT, 'post');
        if ($existing_post) {
            // Jika post sudah ada, tampilkan pesan
            echo '<p><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="#dd0000" class="bi bi-x-circle-fill" viewBox="0 0 16 16">
                <path d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0M5.354 4.646a.5.5 0 1 0-.708.708L7.293 8l-2.647 2.646a.5.5 0 0 0 .708.708L8 8.707l2.646 2.647a.5.5 0 0 0 .708-.708L8.707 8l2.647-2.646a.5.5 0 0 0-.708-.708L8 7.293z"/>
                </svg> Gagal import! Post dengan judul "' . esc_html($title) . '" sudah ada.</p>';
        } else {
            $post_data = array(
                'post_title'    => wp_strip_all_tags($title),
                'post_content'  => $content,
                'post_status'   => $status,
                'post_type'     => 'post',
                'meta_input'    => array(
                    'thumbnail'   => esc_url($thumbnail),
                ),
            );

            // Insert post ke dalam WordPress
            $post_id = wp_insert_post($post_data);
            // set category
            if($category){
                wp_set_post_terms($post_id, $category, 'category');
            }
            // Jika ada thumbnail, set sebagai featured image
            if (!empty($thumbnail)) {
                $this->set_featured_image_from_url($post_id, $thumbnail);
            }

            // Mengecek apakah post berhasil disisipkan
            if (!is_wp_error($post_id)) {
                // Jika berhasil, tampilkan judul
                echo '<p><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="#51b20c" class="bi bi-check-circle" viewBox="0 0 16 16">
                    <path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14m0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16"/>
                    <path d="m10.97 4.97-.02.022-3.473 4.425-2.093-2.094a.75.75 0 0 0-1.06 1.06L6.97 11.03a.75.75 0 0 0 1.079-.02l3.992-4.99a.75.75 0 0 0-1.071-1.05"/>
                    </svg> Success import posts dengan judul: ' . esc_html(get_the_title($post_id)).'</p>';
            } else {
                // Jika gagal, tampilkan pesan error
                echo '<p><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="#dd0000" class="bi bi-x-circle-fill" viewBox="0 0 16 16">
                    <path d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0M5.354 4.646a.5.5 0 1 0-.708.708L7.293 8l-2.647 2.646a.5.5 0 0 0 .708.708L8 8.707l2.646 2.647a.5.5 0 0 0 .708-.708L8.707 8l2.647-2.646a.5.5 0 0 0-.708-.708L8 7.293z"/>
                    </svg> Gagal import post: ' . $post_id->get_error_message().'</p>';
            }
        }

        return ob_get_clean();
    }

    // Fungsi untuk menetapkan featured image dari URL
    public function set_featured_image_from_url($post_id, $image_url) {
        $upload_dir = wp_upload_dir();
        $image_data = file_get_contents($image_url);
        $filename = basename($image_url);
        $file = $upload_dir['path'] . '/' . $filename;
        file_put_contents($file, $image_data);

        $wp_filetype = wp_check_filetype($filename, null);
        $attachment = array(
            'post_mime_type' => $wp_filetype['type'],
            'post_title'     => sanitize_file_name($filename),
            'post_content'   => '',
            'post_status'    => 'inherit',
        );

        $attach_id = wp_insert_attachment($attachment, $file, $post_id);
        require_once(ABSPATH . 'wp-admin/includes/image.php');
        $attach_data = wp_generate_attachment_metadata($attach_id, $file);
        wp_update_attachment_metadata($attach_id, $attach_data);
        set_post_thumbnail($post_id, $attach_id);
    }

    public function add_news_generate_menu()
    {
        add_submenu_page(
            'options-general.php',
            'News Scraper',
            'News Scraper',
            'manage_options',
            'velocity_news_settings',
            array($this, 'render_news_settings_page'),
        );
    }

    public function render_news_settings_page()
    {
        // Mengambil kategori dan post
        $categories = $this->fetch_category();
        ?>
        <div class="wrap">
            <h2>News Scraper</h2>
            <h4>Ambil Artikel dari API Velocity</h4>
            <form method="post">
                <table class="form-table">
                    <tr>
                        <th scope="row">Ambil Target</th>
                        <td>
                            <select name="target" id="target">
                                <option>Pilih Target</option>
                                <?php foreach($categories as $category):
                                echo '<option value="'.$category['id'].'">'.$category['name'].'</option>';
                                endforeach;
                                ?>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">Tujuan Target</th>
                        <td><?php
                            wp_dropdown_categories(array(
                                'show_option_all' => 'Pilih Kategori', 
                                'name' => 'category',
                                'id' => 'category',
                                'exclude'   =>1,
                                'class' => 'postform',
                                'hide_empty' => 0,
                            ));?>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">Jumlah Artikel</th>
                        <td><input type="number" name="jml_target" id="jml_target" min="1" value="5" /></td>
                    </tr>
                    <tr>
                        <th>Status</th>
                        <td>
                            <select id="status" name="status">
                            <option value="publish">Publish</option>
                            <option value="draft">Draft</option>
                            </select>
                        </td>
                    </tr>
                    <tr><td colspan="2"><input class="button button-primary" type="submit" value="Ambil Artikel"></td></tr>
                </table>
            </form>
        </div>
        <?php
        if (isset($_POST['category']) && isset($_POST['jml_target'])) {
            $target = sanitize_text_field($_POST['target']);
            $category = sanitize_text_field($_POST['category']);
            $count = intval($_POST['jml_target']);
            $status = sanitize_text_field($_POST['status']);
            echo '<div class="vdaddons-notice">'.$this->fetch_news_scraper($target, $category, $count, $status).'</div>';
        }
    }
}

// Inisialisasi class Velocity_Addons_News
$velocity_news = new Velocity_Addons_News();
// $velocity_news->autoload();