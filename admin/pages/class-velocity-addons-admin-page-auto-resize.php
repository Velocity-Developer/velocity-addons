<?php

/**
 * Auto resize settings page renderer.
 *
 * @package Velocity_Addons
 * @subpackage Velocity_Addons/admin/pages
 */
class Velocity_Addons_Admin_Page_Auto_Resize
{
    public static function render($field_renderer = null)
    {
        if (!current_user_can('manage_options')) {
            return;
        }
?>
        <div class="velocity-dashboard-wrapper">
            <div class="vd-header">
                <h1 class="vd-title">Auto Resize Image</h1>
                <p class="vd-subtitle">Pengaturan re-sizing otomatis untuk gambar.</p>
            </div>
            <form method="post" data-velocity-settings="1">
                <div class="vd-section">
                    <div class="vd-section-header" style="padding: 1.25rem 1.5rem; border-bottom: 1px solid #e5e7eb; background-color: #f9fafb;">
                        <h3 style="margin:0; font-size:1.1rem; color:#374151;">Auto Resize</h3>
                    </div>
                    <div class="vd-section-body">
                        <?php
                        $fields = array(
                            array('id' => 'auto_resize_mode', 'type' => 'checkbox', 'title' => 'Enable re-sizing', 'label' => 'Aktifkan re-sizing pada situs.'),
                            array('id' => 'auto_resize_mode_data', 'sub' => 'maxwidth', 'type' => 'number', 'title' => 'Max width', 'std' => 1200, 'step' => 1),
                            array('id' => 'auto_resize_mode_data', 'sub' => 'maxheight', 'type' => 'number', 'title' => 'Max height', 'std' => 1200, 'step' => 1),
                            array('id' => 'auto_resize_mode_data', 'sub' => 'quality', 'type' => 'number', 'title' => 'Quality', 'std' => 90, 'step' => 1, 'label' => 'Range 10-100. Direkomendasikan 80-90.'),
                            array('id' => 'auto_resize_mode_data', 'sub' => 'output_format', 'type' => 'select', 'title' => 'Output format', 'std' => 'original', 'options' => array('original' => 'Original', 'jpeg' => 'JPEG', 'webp' => 'WebP', 'avif' => 'AVIF'), 'label' => 'Jika format tidak didukung editor server, otomatis fallback ke format asli.'),
                        );
                        foreach ($fields as $data) {
                            $label_for = isset($data['sub']) ? ($data['id'] . '__' . $data['sub']) : $data['id'];
                            echo '<div class="vd-form-group">';
                            echo '<div class="vd-form-left">';
                            echo '<label class="vd-form-label" for="' . esc_attr($label_for) . '">' . esc_html($data['title']) . '</label>';
                            if (isset($data['label'])) {
                                echo '<small class="vd-form-hint">' . esc_html($data['label']) . '</small>';
                            }
                            echo '</div>';
                            if ($data['type'] == 'checkbox') {
                                $id = $data['id'];
                                $std = isset($data['std']) ? $data['std'] : '';
                                $val = get_option($id, $std);
                                $checked = ($val == 1) ? 'checked' : '';
                                echo '<div class="vd-form-right">';
                                echo '<label class="vd-switch">';
                                echo '<input type="checkbox" id="' . esc_attr($id) . '" name="' . esc_attr($id) . '" value="1" ' . $checked . '>';
                                echo '<span class="vd-switch-slider" aria-hidden="true"></span>';
                                echo '</label>';
                                echo '</div>';
                            } else {
                                echo '<div class="vd-form-right">';
                                if (is_callable($field_renderer)) {
                                    call_user_func($field_renderer, $data);
                                }
                                echo '</div>';
                            }
                            echo '</div>';
                        }
                        ?>
                    </div>
                </div>
                <?php submit_button(); ?>
            </form>
            <div class="vd-footer">
                <small>Powered by <a href="https://velocitydeveloper.com/" target="_blank">velocitydeveloper.com</a></small>
            </div>
        </div>
<?php
    }
}

