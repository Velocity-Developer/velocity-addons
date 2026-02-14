<?php

/**
 * Security settings page renderer.
 *
 * @package Velocity_Addons
 * @subpackage Velocity_Addons/admin/pages
 */
class Velocity_Addons_Admin_Page_Security
{
    public static function render($field_renderer = null)
    {
        if (!current_user_can('manage_options')) {
            return;
        }
?>
        <div class="velocity-dashboard-wrapper">
            <div class="vd-header">
                <h1 class="vd-title">Security</h1>
                <p class="vd-subtitle">Pengaturan keamanan akses dan login.</p>
            </div>
            <form method="post" data-velocity-settings="1">
                <div class="vd-section">
                    <div class="vd-section-header" style="padding: 1.25rem 1.5rem; border-bottom: 1px solid #e5e7eb; background-color: #f9fafb;">
                        <h3 style="margin:0; font-size:1.1rem; color:#374151;">Security</h3>
                    </div>
                    <div class="vd-section-body">
                        <?php
                        $fields = array(
                            array('id' => 'limit_login_attempts', 'type' => 'checkbox', 'title' => 'Limit Login Attempts', 'std' => 1, 'label' => 'Batasi jumlah percobaan login.'),
                            array('id' => 'disable_xmlrpc', 'type' => 'checkbox', 'title' => 'Disable XML-RPC', 'std' => 1, 'label' => 'Nonaktifkan protokol XML-RPC.'),
                            array('id' => 'block_wp_login', 'type' => 'checkbox', 'title' => 'Block wp-login.php', 'std' => 0, 'label' => 'Blokir akses wp-login.php.'),
                            array('id' => 'whitelist_block_wp_login', 'type' => 'text', 'title' => 'Whitelist IP Block wp-login.php', 'std' => '', 'label' => 'Daftar IP whitelist.'),
                            array('id' => 'whitelist_country', 'type' => 'text', 'title' => 'Whitelist Country', 'std' => 'ID', 'label' => 'Contoh: ID,MY,US'),
                            array('id' => 'redirect_to', 'type' => 'text', 'title' => 'Redirect To', 'std' => '127.0.0.1', 'label' => 'Tujuan redirect wp-login.php jika blokir aktif.'),
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

