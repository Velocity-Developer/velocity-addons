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

    // Properti statis
    protected static $api_url = 'https://api.velocitydeveloper.id/wp-json/news/v1';
    
    public function __construct()
    {
        $news_generate = get_option('news_generate', '1');

        if ($news_generate !== '1')
            return false;

        add_action('wp_ajax_velocity_news_import', array($this, 'ajax_import_news'));
    }

    // Fungsi statis untuk mengakses $api_url
    public static function get_api_url() {
        return self::$api_url;  // Menggunakan self:: untuk mengakses properti statis
    }

    // Mengambil data dari API
    public static function fetch_post($item=null, $cat_id=null, $count=5) {
        $license = new Velocity_Addons_License;
        $args = array(
            'headers' => $license->headers_api(),
        );
        $data = [
            'cat='.$cat_id,
            'number='.$count,
        ];
        $url = self::$api_url.'/'.$item.'?'.implode("&",$data);
        
        $response = wp_remote_get($url, $args);
        if (is_wp_error($response)) {
            return array(
                'status'  => false,
                'message' => $response->get_error_message(),
            );
        }

        $decoded = json_decode(wp_remote_retrieve_body($response), true);
        if (!is_array($decoded)) {
            return array(
                'status'  => false,
                'message' => 'Invalid response from API.',
            );
        }

        return $decoded; // Mengembalikan data dalam bentuk array
    }

    public static function fetch_category() {
        // Mengambil data dari transient cache
        $name_cache = 'vd_api_news_category';
        $cached_data = get_transient($name_cache);

        if ($cached_data === false) {
            $license = new Velocity_Addons_License;
            $args = array(
                'headers' => $license->headers_api(),
            );
            $url        = self::$api_url.'/cat';
            
            $response = wp_remote_get($url, $args);
            if (is_wp_error($response)) {
                return array(
                    'status'  => false,
                    'message' => $response->get_error_message(),
                );
            }

            $response = json_decode(wp_remote_retrieve_body($response), true);
            if (!is_array($response)) {
                return array(
                    'status'  => false,
                    'message' => 'Invalid response from API.',
                );
            }
            
            // jika sukses Simpan data dalam transient selama 5 menit (300 detik)
            if(isset($response['status']) && $response['status'] == true){
                set_transient($name_cache, $response, 300); // 300 detik = 5 menit
            }

            return $response; // Mengembalikan data dalam bentuk array
        }

        // Jika ada cache, kembalikan data cache
        return $cached_data;
    }

    public static function fetch_news_scraper($target, $category, $count, $status) {
        ob_start();
        // Mengambil kategori dan post
        $get_datas = self::fetch_post('posts',$target, $count);

        if(isset($get_datas['status']) && $get_datas['status'] == true ){

            $posts_datas = $get_datas['data'];

            // Mengatur jumlah menit yang ingin dikurangi
            $num_time = 1;

            foreach($posts_datas as $posts_data):
                $title = (string) $posts_data['title'];
                $content = (string) $posts_data['content'];
                $thumbnail = (string) $posts_data['thumb_url'] ?? '';
                $thumbcaption = (string) $posts_data['thumb_caption'] ?? '';
                $tags = (string) $posts_data['post_tag'] ?? '';

            // Mendapatkan waktu sekarang (local time) dikurangi 1 menit
            $current_time = current_time('mysql');
            $date = date('Y-m-d H:i:s', strtotime($current_time . " -{$num_time} minute"));

                echo self::save_news_post($title, $content, $thumbnail, $thumbcaption, $category, $status, $tags, $date);

                $num_time++;
            endforeach;
        } else {
            $error_message = 'Gagal mengambil data dari API.';
            if (is_array($get_datas) && isset($get_datas['message']) && is_scalar($get_datas['message'])) {
                $error_message = (string) $get_datas['message'];
            } elseif (is_string($get_datas) && $get_datas !== '') {
                $error_message = $get_datas;
            }

            $btn = '<a href="'.esc_url(admin_url('admin.php?page=velocity_license_settings')).'" class="button button-primary" style="margin-left:8px">Atur Lisensi</a>';
            echo '<p><svg xmlns="XXXXXXXXXXXXXXXXXXXXXXXXXX" width="16" height="16" fill="#dd0000" class="bi bi-x-circle-fill" viewBox="0 0 16 16">
                <path d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0M5.354 4.646a.5.5 0 1 0-.708.708L7.293 8l-2.647 2.646a.5.5 0 0 0 .708.708L8 8.707l2.646 2.647a.5.5 0 0 0 .708-.708L8.707 8l2.647-2.646a.5.5 0 0 0-.708-.708L8 7.293z"/>
                </svg> Gagal import! '.esc_html($error_message).($error_message === 'License Key is required' ? $btn : '').'</p>';
        }

        return ob_get_clean();
    }

    //fungsi cek posts by title
    public static function cek_posts_by_title($title) {
        global $wpdb;

        // Prepare the SQL query
        $query = $wpdb->prepare("
            SELECT ID 
            FROM $wpdb->posts 
            WHERE post_title = %s 
            AND post_type = 'post' 
            AND post_status = 'publish'
        ", $title);

        // Execute the query and get the result
        $post_id = absint($wpdb->get_var($query));

        if ($post_id > 0) {
            return $post_id;
        }

        return false;
    }

    // Fungsi untuk menyimpan artikel sebagai post di WordPress
    public static function save_news_post($title, $content, $thumbnail, $thumbcaption, $category, $status, $tags, $date) {
        ob_start();
        $existing_post_id = self::cek_posts_by_title($title);
        if ($existing_post_id) {
            // Update category kalau disediakan
            if ($category) {
                wp_set_post_terms((int) $existing_post_id, $category, 'category');
                echo '<p><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="#51b20c" class="bi bi-check-circle" viewBox="0 0 16 16">
                    <path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14m0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16"/>
                    <path d="m10.97 4.97-.02.022-3.473 4.425-2.093-2.094a.75.75 0 0 0-1.06 1.06L6.97 11.03a.75.75 0 0 0 1.079-.02l3.992-4.99a.75.75 0 0 0-1.071-1.05"/>
                    </svg> Post dengan judul "' . esc_html($title) . '" sudah ada, kategori diperbarui.</p>';
            } else {
                echo '<p><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="#dd0000" class="bi bi-x-circle-fill" viewBox="0 0 16 16">
                    <path d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0M5.354 4.646a.5.5 0 1 0-.708.708L7.293 8l-2.647 2.646a.5.5 0 0 0 .708.708L8 8.707l2.646 2.647a.5.5 0 0 0 .708-.708L8.707 8l2.647-2.646a.5.5 0 0 0-.708-.708L8 7.293z"/>
                    </svg> Gagal import! Post dengan judul "' . esc_html($title) . '" sudah ada.</p>';
            }
        } else {
            $post_data = array(
                'post_title'    => wp_strip_all_tags($title),
                'post_content'  => $content,
                'post_status'   => $status,
                'post_type'     => 'post',
                'meta_input'    => array(
                    'thumbnail'   => esc_url($thumbnail),
                ),
                'post_date'     => $date,
                'post_date_gmt' => get_gmt_from_date($date),
                'tags_input'    => $tags,
            );

            // Insert post ke dalam WordPress
            $post_id = wp_insert_post($post_data);
            // set category
            if($category){
                wp_set_post_terms($post_id, $category, 'category');
            }
            // Jika ada thumbnail, set sebagai featured image
            if (!empty($thumbnail)) {
                self::set_featured_image_from_url($post_id, $thumbnail, $thumbcaption);
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
    public static function set_featured_image_from_url($post_id, $image_url, $caption) {
        $upload_dir = wp_upload_dir();
        $image_data = file_get_contents($image_url);
        $filename = basename($image_url);
        $file = $upload_dir['path'] . '/' . $filename;
        file_put_contents($file, $image_data);

        $wp_filetype = wp_check_filetype($filename, null);
        $attachment = array(
            'post_mime_type' => $wp_filetype['type'],
            'post_title'     => sanitize_file_name($filename),
            'post_content'   => $caption,
            'post_excerpt'   => $caption,
            'post_status'    => 'inherit',
        );

        $attach_id = wp_insert_attachment($attachment, $file, $post_id);
        require_once(ABSPATH . 'wp-admin/includes/image.php');
        $attach_data = wp_generate_attachment_metadata($attach_id, $file);
        wp_update_attachment_metadata($attach_id, $attach_data);
        set_post_thumbnail($post_id, $attach_id);
    }

    public static function render_news_settings_page()
    {
        ?>
        <div class="velocity-dashboard-wrapper">
            <div class="vd-header">
                <h1 class="vd-title">News Scraper</h1>
                <p class="vd-subtitle">Ambil artikel dari API Velocity.</p>
            </div>
            <form method="post" id="velocity-news-import-form">
                <?php wp_nonce_field('velocity_news_import_action', 'velocity_news_import_nonce'); ?>
                <div class="vd-grid-2">
                    <div class="vd-section">
                        <div class="vd-section-header" style="padding: 1.25rem 1.5rem; border-bottom: 1px solid #e5e7eb; background-color: #f9fafb;">
                            <h3 style="margin:0; font-size:1.1rem; color:#374151;">Pengaturan Import</h3>
                        </div>
                        <div class="vd-section-body">
                            <?php
                            $get_categories = self::fetch_category();
                            if(isset($get_categories['status']) && $get_categories['status'] == true){
                                $categories = $get_categories['data']??[];
                            } else {
                                if (is_array($get_categories) && isset($get_categories['message']) && is_scalar($get_categories['message'])) {
                                    $msg = (string) $get_categories['message'];
                                } elseif (is_string($get_categories) && $get_categories !== '') {
                                    $msg = $get_categories;
                                } else {
                                    $msg = 'Gagal mengambil kategori.';
                                }
                                $btn = '<a href="'.esc_url(admin_url('admin.php?page=velocity_license_settings')).'" class="button button-primary" style="margin-left:8px">Atur Lisensi</a>';
                                echo '<p>'.esc_html($msg).($msg === 'License Key is required' ? $btn : '').'</p>';
                                $categories = [];
                            }
                            ?>
                            <div class="vd-form-group">
                                <div class="vd-form-left">
                                    <label for="target" class="vd-form-label">Ambil Target</label>
                                    <small class="vd-form-hint">Pilih sumber kategori dari API Velocity.</small>
                                </div>
                                <div class="vd-form-right">
                                    <select name="target" id="target" required>
                                        <option value="">Pilih Target</option>
                                        <?php foreach($categories as $category){ echo '<option value="'.$category['id'].'">'.$category['name'].'</option>'; } ?>
                                    </select>
                                </div>
                            </div>
                            <div class="vd-form-group">
                                <div class="vd-form-left">
                                    <label for="category" class="vd-form-label">Tujuan Target</label>
                                    <small class="vd-form-hint">Kategori WordPress yang akan diisi.</small>
                                </div>
                                <div class="vd-form-right">
                                    <?php
                                    wp_dropdown_categories(array(
                                        'show_option_none' => 'Pilih Kategori',
                                        'option_none_value' => '',
                                        'name' => 'category',
                                        'id' => 'category',
                                        'exclude'   => 1,
                                        'class' => 'postform',
                                        'hide_empty' => 0,
                                        'required' => 'required',
                                    ));
                                    ?>
                                </div>
                            </div>
                            <div class="vd-form-group">
                                <div class="vd-form-left">
                                    <label for="jml_target" class="vd-form-label">Jumlah Artikel</label>
                                    <small class="vd-form-hint">Jumlah artikel yang akan diimport.</small>
                                </div>
                                <div class="vd-form-right">
                                    <input type="number" name="jml_target" id="jml_target" min="1" value="5" required/>
                                </div>
                            </div>
                            <div class="vd-form-group">
                                <div class="vd-form-left">
                                    <label for="status" class="vd-form-label">Status</label>
                                    <small class="vd-form-hint">Status publikasi untuk artikel hasil import.</small>
                                </div>
                                <div class="vd-form-right">
                                    <select id="status" name="status" required>
                                        <option value="publish">Publish</option>
                                        <option value="draft">Draft</option>
                                    </select>
                                </div>
                            </div>
                            <?php submit_button('Ambil Artikel', 'primary', 'submit', false, array('id' => 'velocity-news-submit')); ?>
                        </div>
                    </div>
                    <div class="vd-section">
                        <div class="vd-section-header" style="padding: 1.25rem 1.5rem; border-bottom: 1px solid #e5e7eb; background-color: #f9fafb;">
                            <h3 style="margin:0; font-size:1.1rem; color:#374151;">Hasil Import</h3>
                        </div>
                        <div class="vd-section-body" id="velocity-news-import-result">
                            <?php
                            if (isset($_POST['category']) && isset($_POST['jml_target'])) {
                                $target = sanitize_text_field($_POST['target']);
                                $category = sanitize_text_field($_POST['category']);
                                $count = intval($_POST['jml_target']);
                                $status = sanitize_text_field($_POST['status']);
                                echo self::fetch_news_scraper($target, $category, $count, $status);
                            } else {
                                echo '<p>Belum ada proses import.</p>';
                            }
                            ?>
                        </div>
                    </div>
                </div>
            </form>
            <script>
                jQuery(function($) {
                    var $form = $('#velocity-news-import-form');
                    if (!$form.length) return;

                    var $result = $('#velocity-news-import-result');
                    var $submit = $('#velocity-news-submit');

                    function escHtml(text) {
                        return $('<div/>').text(text || '').html();
                    }

                    $form.on('submit', function(e) {
                        e.preventDefault();

                        var originalLabel = $submit.text();
                        var payload = {
                            action: 'velocity_news_import',
                            nonce: $form.find('input[name="velocity_news_import_nonce"]').val(),
                            target: $form.find('#target').val(),
                            category: $form.find('#category').val(),
                            jml_target: $form.find('#jml_target').val(),
                            status: $form.find('#status').val()
                        };

                        $submit.prop('disabled', true).text('Memproses...');
                        $result.html('<p>Sedang memproses import...</p>');

                        $.ajax({
                            url: ajaxurl,
                            type: 'POST',
                            dataType: 'json',
                            data: payload
                        }).done(function(response) {
                            if (response && response.success && response.data && typeof response.data.html === 'string') {
                                $result.html(response.data.html);
                                return;
                            }

                            var msg = (response && response.data && response.data.message) ? response.data.message : 'Import gagal.';
                            $result.html('<p style="color:#b32d2e;">' + escHtml(msg) + '</p>');
                        }).fail(function(xhr) {
                            var msg = 'Import gagal. Coba lagi.';
                            if (xhr && xhr.responseJSON && xhr.responseJSON.data && xhr.responseJSON.data.message) {
                                msg = xhr.responseJSON.data.message;
                            }
                            $result.html('<p style="color:#b32d2e;">' + escHtml(msg) + '</p>');
                        }).always(function() {
                            $submit.prop('disabled', false).text(originalLabel);
                        });
                    });
                });
            </script>
            <div class="vd-footer">
                <small>Powered by <a href="https://velocitydeveloper.com/" target="_blank">velocitydeveloper.com</a></small>
            </div>
        </div>
        <?php
    }

    public function ajax_import_news()
    {
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => 'Akses ditolak.'), 403);
        }

        check_ajax_referer('velocity_news_import_action', 'nonce');

        $target   = isset($_POST['target']) ? absint($_POST['target']) : 0;
        $category = isset($_POST['category']) ? absint($_POST['category']) : 0;
        $count    = isset($_POST['jml_target']) ? absint($_POST['jml_target']) : 0;
        $status   = isset($_POST['status']) ? sanitize_key($_POST['status']) : 'publish';

        if ($target < 1 || $category < 1 || $count < 1) {
            wp_send_json_error(array('message' => 'Parameter import belum lengkap.'), 400);
        }

        if (!in_array($status, array('publish', 'draft'), true)) {
            $status = 'publish';
        }

        $count = min($count, 50);

        $html = self::fetch_news_scraper((string) $target, (string) $category, $count, $status);

        wp_send_json_success(array('html' => $html));
    }
}

// Inisialisasi class Velocity_Addons_News
$velocity_news = new Velocity_Addons_News();
// $velocity_news->autoload();
