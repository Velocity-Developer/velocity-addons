<?php

/**
 * Register Auto Resize settings in the WordPress admin panel
 *
 * @link       https://velocitydeveloper.com
 * @since      1.0.0
 *
 * @package    Velocity_Addons
 * @subpackage Velocity_Addons/includes
 */

class Velocity_Addons_Auto_Resize_Image
{
    public function __construct()
    {
        if (get_option('auto_resize_mode') == 1) {
            add_filter('wp_handle_upload', array($this, 'resize_uploaded_image'));
        }
    }

    public function resize_uploaded_image($upload)
    {
        $is_image = strpos($upload['type'], 'image') !== false;
        if ($is_image) {
            $upload = $this->resize_image($upload);
        }
        return $upload;
    }

    public function resize_image($file)
    {
        $opt        = get_option('auto_resize_mode_data', []);
        $maxwidth   = isset($opt['maxwidth']) && !empty($opt['maxwidth']) ? $opt['maxwidth'] : '';
        $maxheight  = isset($opt['maxheight']) && !empty($opt['maxheight']) ? $opt['maxheight'] : '';

        if ($maxwidth > 0 || $maxheight > 0) {
            // Mendapatkan path direktori upload gambar
            $file_path = $file['file'];

            $image_editor = wp_get_image_editor($file_path);

            if (!is_wp_error($image_editor)) {
                $size = $image_editor->get_size();
                $orig_width = $size['width'];
                $orig_height = $size['height'];

                if ($orig_width > $maxwidth || $orig_height > $maxheight) {

                    $image_editor->resize($maxwidth, $maxheight, false);

                    // Setting quality gambar
                    $image_editor->set_quality(90);

                    // Menyimpan gambar yang sudah dimanipulasi
                    $saved_image = $image_editor->save($file_path);

                    if (!is_wp_error($saved_image)) {
                        // Mengembalikan URL gambar yang sudah diresize
                        $image_editor->multi_resize(array('full' => array($maxwidth, $maxheight)));
                    } else {
                        // Terjadi kesalahan saat menyimpan gambar
                        error_log('Terjadi kesalahan saat menyimpan gambar: ' . $saved_image->get_error_message());
                    }
                }
            } else {
                $error_message = $image_editor->get_error_message();
                error_log("Image Resize Error: $error_message");
            }
        }
        return $file;
    }
}

// Inisialisasi class Velocity_Addons_Auto_Resize_Image
$velocity_auto_resize_image = new Velocity_Addons_Auto_Resize_Image();
