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
        $opt = $this->get_resize_options();

        $maxwidth      = (int) $opt['maxwidth'];
        $maxheight     = (int) $opt['maxheight'];
        $quality       = (int) $opt['quality'];
        $output_format = (string) $opt['output_format'];

        $file_path = isset($file['file']) ? (string) $file['file'] : '';
        if ($file_path === '' || !file_exists($file_path)) {
            return $file;
        }

        $image_editor = wp_get_image_editor($file_path);
        if (is_wp_error($image_editor)) {
            error_log('Image Resize Error: ' . $image_editor->get_error_message());
            return $file;
        }

        $size = $image_editor->get_size();
        if (is_wp_error($size) || !is_array($size)) {
            return $file;
        }

        $orig_width  = isset($size['width']) ? (int) $size['width'] : 0;
        $orig_height = isset($size['height']) ? (int) $size['height'] : 0;

        $needs_resize = false;
        if ($maxwidth > 0 && $orig_width > $maxwidth) {
            $needs_resize = true;
        }
        if ($maxheight > 0 && $orig_height > $maxheight) {
            $needs_resize = true;
        }

        $target_mime = $this->format_to_mime($output_format);
        if ($target_mime !== '' && !wp_image_editor_supports(array('mime_type' => $target_mime))) {
            $target_mime = '';
        }
        $needs_convert = ($target_mime !== '');

        if (!$needs_resize && !$needs_convert) {
            return $file;
        }

        $resize_width  = $maxwidth > 0 ? $maxwidth : 0;
        $resize_height = $maxheight > 0 ? $maxheight : 0;

        if ($needs_resize) {
            $resized = $image_editor->resize($resize_width, $resize_height, false);
            if (is_wp_error($resized)) {
                error_log('Image Resize Error: ' . $resized->get_error_message());
                return $file;
            }
        }

        $image_editor->set_quality($quality);

        $saved_image = $target_mime !== ''
            ? $image_editor->save(null, $target_mime)
            : $image_editor->save($file_path);

        if (is_wp_error($saved_image)) {
            error_log('Image Save Error: ' . $saved_image->get_error_message());
            return $file;
        }

        if (isset($saved_image['path']) && !empty($saved_image['path'])) {
            $new_path = (string) $saved_image['path'];
            $old_path = $file_path;

            $file['file'] = $new_path;
            if (!empty($file['url'])) {
                $file['url'] = str_replace(wp_basename($file['url']), wp_basename($new_path), $file['url']);
            }
            if (!empty($saved_image['mime-type'])) {
                $file['type'] = (string) $saved_image['mime-type'];
            }

            if ($new_path !== $old_path && file_exists($old_path)) {
                @unlink($old_path);
            }
        }

        return $file;
    }

    private function get_resize_options()
    {
        $opt = get_option('auto_resize_mode_data', array());
        if (!is_array($opt)) {
            $opt = array();
        }

        $maxwidth = isset($opt['maxwidth']) ? absint($opt['maxwidth']) : 1200;
        $maxheight = isset($opt['maxheight']) ? absint($opt['maxheight']) : 1200;
        $quality = isset($opt['quality']) ? absint($opt['quality']) : 90;
        if ($quality < 10) {
            $quality = 10;
        }
        if ($quality > 100) {
            $quality = 100;
        }

        $output_format = isset($opt['output_format']) ? sanitize_key((string) $opt['output_format']) : 'original';
        $allowed_formats = array('original', 'jpeg', 'webp', 'avif');
        if (!in_array($output_format, $allowed_formats, true)) {
            $output_format = 'original';
        }

        return array(
            'maxwidth'      => $maxwidth,
            'maxheight'     => $maxheight,
            'quality'       => $quality,
            'output_format' => $output_format,
        );
    }

    private function format_to_mime($format)
    {
        switch ($format) {
            case 'jpeg':
                return 'image/jpeg';
            case 'webp':
                return 'image/webp';
            case 'avif':
                return 'image/avif';
            default:
                return '';
        }
    }
}

// Inisialisasi class Velocity_Addons_Auto_Resize_Image
$velocity_auto_resize_image = new Velocity_Addons_Auto_Resize_Image();
