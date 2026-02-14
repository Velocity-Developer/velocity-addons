<?php

/**
 * License settings page renderer.
 *
 * @package Velocity_Addons
 * @subpackage Velocity_Addons/admin/pages
 */
class Velocity_Addons_Admin_Page_License
{
    public static function render($field_renderer = null, $status_label = 'Check License')
    {
        if (!current_user_can('manage_options')) {
            return;
        }
?>
        <div class="velocity-dashboard-wrapper">
            <div class="vd-header">
                <h1 class="vd-title">License</h1>
                <p class="vd-subtitle">Verifikasi lisensi Velocity Addons.</p>
            </div>
            <form method="post" data-velocity-settings="1">
                <div class="vd-section">
                    <div class="vd-section-header" style="padding: 1.25rem 1.5rem; border-bottom: 1px solid #e5e7eb; background-color: #f9fafb;">
                        <h3 style="margin:0; font-size:1.1rem; color:#374151;">License Key</h3>
                    </div>
                    <div class="vd-section-body">
                        <?php
                        $fields = array(
                            array('id' => 'velocity_license', 'sub' => 'key', 'type' => 'password', 'title' => 'License Key', 'std' => '', 'label' => ''),
                        );
                        foreach ($fields as $data) {
                            $label_for = isset($data['sub']) ? ($data['id'] . '__' . $data['sub']) : $data['id'];
                            echo '<div class="vd-form-group">';
                            echo '<div class="vd-form-left">';
                            echo '<label class="vd-form-label" for="' . esc_attr($label_for) . '">' . esc_html($data['title']) . '</label>';
                            echo '<small class="vd-form-hint">Masukkan kunci lisensi Anda lalu klik verifikasi.</small>';
                            echo '</div>';
                            if (is_callable($field_renderer)) {
                                call_user_func($field_renderer, $data);
                            }
                            echo '<a class="check-license button button-primary" style="margin-left:12px">' . esc_html($status_label) . '</a><span class="license-status" style="margin-left:8px"></span>';
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

