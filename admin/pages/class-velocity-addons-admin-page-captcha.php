<?php

/**
 * Captcha settings page renderer.
 *
 * @package Velocity_Addons
 * @subpackage Velocity_Addons/admin/pages
 */
class Velocity_Addons_Admin_Page_Captcha
{
    public static function render($field_renderer = null)
    {
        if (!current_user_can('manage_options')) {
            return;
        }
?>
        <div class="velocity-dashboard-wrapper">
            <div class="vd-header">
                <h1 class="vd-title">Captcha</h1>
                <p class="vd-subtitle">Pengaturan Captcha (Google reCaptcha v2 atau Gambar).</p>
            </div>
            <form method="post" data-velocity-settings="1">
                <div class="vd-section">
                    <div class="vd-section-header" style="padding: 1.25rem 1.5rem; border-bottom: 1px solid #e5e7eb; background-color: #f9fafb;">
                        <h3 style="margin:0; font-size:1.1rem; color:#374151;">Captcha</h3>
                    </div>
                    <div class="vd-section-body">
                        <?php
                        $opt = get_option('captcha_velocity', array());
                        $provider_val = isset($opt['provider']) ? $opt['provider'] : 'google';
                        $fields = array(
                            array('id' => 'captcha_velocity', 'sub' => 'provider', 'type' => 'select', 'title' => 'Provider', 'label' => 'Pilih jenis captcha yang digunakan.', 'options' => array('google' => 'Google reCaptcha v2', 'image' => 'Captcha Gambar')),
                            array('id' => 'captcha_velocity', 'sub' => 'aktif', 'type' => 'checkbox', 'title' => 'Captcha', 'std' => 1, 'label' => 'Aktifkan Captcha', 'desc' => 'Gunakan Captcha di Form Login, Komentar dan Velocity Toko. Untuk Contact Form 7 gunakan [velocity_captcha]'),
                            array('id' => 'captcha_velocity', 'sub' => 'difficulty', 'type' => 'select', 'title' => 'Tingkat Kesulitan', 'label' => 'Untuk Captcha Gambar', 'options' => array('easy' => 'Mudah', 'medium' => 'Sedang', 'hard' => 'Sulit')),
                            array('id' => 'captcha_velocity', 'sub' => 'sitekey', 'type' => 'text', 'title' => 'Sitekey'),
                            array('id' => 'captcha_velocity', 'sub' => 'secretkey', 'type' => 'text', 'title' => 'Secretkey'),
                        );
                        foreach ($fields as $data) {
                            $label_for = isset($data['sub']) ? ($data['id'] . '__' . $data['sub']) : $data['id'];
                            $visibility_expr = '';
                            $visibility_style = '';
                            if (isset($data['sub']) && in_array($data['sub'], array('sitekey', 'secretkey'), true)) {
                                $visibility_expr = "(model['captcha_velocity'] && model['captcha_velocity']['provider'] === 'google')";
                                if ($provider_val !== 'google') {
                                    $visibility_style = 'display:none;';
                                }
                            }
                            if (isset($data['sub']) && $data['sub'] === 'difficulty') {
                                $visibility_expr = "(model['captcha_velocity'] && model['captcha_velocity']['provider'] === 'image')";
                                if ($provider_val !== 'image') {
                                    $visibility_style = 'display:none;';
                                }
                            }

                            $group_attrs = ' class="vd-form-group"';
                            if ($visibility_expr !== '') {
                                $group_attrs .= ' x-show="' . esc_attr($visibility_expr) . '"';
                            }
                            if ($visibility_style !== '') {
                                $group_attrs .= ' style="' . esc_attr($visibility_style) . '"';
                            }

                            echo '<div' . $group_attrs . '>';
                            echo '<div class="vd-form-left">';
                            echo '<label class="vd-form-label" for="' . esc_attr($label_for) . '">' . esc_html($data['title']) . '</label>';
                            if (isset($data['desc'])) {
                                echo '<small class="vd-form-hint">' . esc_html($data['desc']) . '</small>';
                            }
                            echo '</div>';
                            if ($data['type'] == 'checkbox') {
                                $id = isset($data['sub']) ? ($data['id']) : $data['id'];
                                $std = isset($data['std']) ? $data['std'] : '';
                                $val = get_option($data['id'], $std);
                                if (isset($data['sub']) && is_array($val)) {
                                    $val = isset($val[$data['sub']]) ? $val[$data['sub']] : '';
                                }
                                $checked = ($val == 1) ? 'checked' : '';
                                echo '<div class="vd-form-right">';
                                echo '<label class="vd-switch">';
                                echo '<input type="checkbox" id="' . esc_attr($label_for) . '" name="' . esc_attr($data['id']) . (isset($data['sub']) ? '[' . esc_attr($data['sub']) . ']' : '') . '" value="1" ' . $checked . '>';
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

