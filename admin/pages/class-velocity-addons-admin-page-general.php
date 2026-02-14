<?php

/**
 * General settings page renderer.
 *
 * @package Velocity_Addons
 * @subpackage Velocity_Addons/admin/pages
 */
class Velocity_Addons_Admin_Page_General
{
    public static function render()
    {
        if (!current_user_can('manage_options')) {
            return;
        }
?>
        <div class="velocity-dashboard-wrapper">
            <div class="vd-header">
                <h1 class="vd-title">Pengaturan Umum</h1>
                <p class="vd-subtitle">Pengaturan dasar fitur Velocity Addons.</p>
            </div>
            <form id="velocity-general-form" method="post" data-velocity-settings="1">
                <div class="vd-section">
                    <div class="vd-section-header" style="padding: 1.25rem 1.5rem; border-bottom: 1px solid #e5e7eb; background-color: #f9fafb;">
                        <h3 style="margin:0; font-size:1.1rem; color:#374151;">Umum</h3>
                    </div>
                    <div class="vd-section-body">
                        <?php
                        $fields = array(
                            array('id' => 'fully_disable_comment', 'type' => 'checkbox', 'title' => 'Disable Comment', 'std' => 1, 'label' => 'Nonaktifkan fitur komentar pada situs.'),
                            array('id' => 'hide_admin_notice', 'type' => 'checkbox', 'title' => 'Hide Admin Notice', 'std' => 0, 'label' => 'Sembunyikan pemberitahuan admin di halaman admin. Pemberitahuan admin seringkali muncul untuk memberikan informasi atau peringatan kepada admin situs.'),
                            array('id' => 'disable_gutenberg', 'type' => 'checkbox', 'title' => 'Disable Gutenberg', 'std' => 0, 'label' => 'Aktifkan editor klasik WordPress menggantikan Gutenberg.'),
                            array('id' => 'classic_widget_velocity', 'type' => 'checkbox', 'title' => 'Classic Widget', 'std' => 1, 'label' => 'Aktifkan widget klasik.'),
                            array('id' => 'enable_xml_sitemap', 'type' => 'checkbox', 'title' => 'XML Sitemap', 'std' => 1, 'label' => 'Aktifkan XML Sitemap Generator (sitemap.xml).'),
                            array('id' => 'seo_velocity', 'type' => 'checkbox', 'title' => 'SEO', 'std' => 1, 'label' => 'Aktifkan SEO dari Velocity Developer.'),
                            array('id' => 'statistik_velocity', 'type' => 'checkbox', 'title' => 'Statistik Pengunjung', 'std' => 1, 'label' => 'Aktifkan statistik pengunjung dari Velocity Developer.'),
                            array('id' => 'floating_whatsapp', 'type' => 'checkbox', 'title' => 'Floating Whatsapp', 'std' => 1, 'label' => 'Aktifkan Whatsapp Floating.'),
                            array('id' => 'floating_scrollTop', 'type' => 'checkbox', 'title' => 'Floating Scrolltop', 'std' => 1, 'label' => 'Aktifkan scrollTop ke halaman atas.'),
                            array('id' => 'remove_slug_category_velocity', 'type' => 'checkbox', 'title' => 'Remove Slug Category', 'std' => 0, 'label' => 'Aktifkan untuk hapus slug /category/ dari URL.'),
                            array('id' => 'news_generate', 'type' => 'checkbox', 'title' => 'Import Artikel dari API', 'std' => 1, 'label' => 'Aktifkan fungsi untuk import artikel postingan.'),
                            array('id' => 'velocity_gallery', 'type' => 'checkbox', 'title' => 'Gallery Post Type', 'std' => 0, 'label' => 'Aktifkan fungsi untuk menggunakan Gallery Post Type.'),
                            array('id' => 'velocity_optimasi', 'type' => 'checkbox', 'title' => 'Optimize Database', 'std' => 0, 'label' => 'Aktifkan fungsi untuk mengoptimalkan situs dari database.'),
                            array('id' => 'velocity_duitku', 'type' => 'checkbox', 'title' => 'Payment Gateway Duitku', 'std' => 0, 'label' => 'Aktifkan payment gateway Duitku.'),
                        );
                        foreach ($fields as $data) {
                            $id      = $data['id'];
                            $std     = isset($data['std']) ? $data['std'] : '';
                            $val     = get_option($id, $std);
                            $checked = ($val == 1) ? 'checked' : '';
                            echo '<div class="vd-form-group">';
                            echo '<div class="vd-form-left">';
                            echo '<label class="vd-form-label" for="' . esc_attr($id) . '">' . esc_html($data['title']) . '</label>';
                            if (isset($data['label']) && !empty($data['label'])) {
                                echo '<small class="vd-form-hint">' . esc_html($data['label']) . '</small>';
                            }
                            echo '</div>';
                            echo '<label class="vd-switch">';
                            echo '<input type="checkbox" id="' . esc_attr($id) . '" name="' . esc_attr($id) . '" value="1" ' . $checked . '>';
                            echo '<span class="vd-switch-slider" aria-hidden="true"></span>';
                            echo '</label>';
                            echo '</div>';
                        }
                        ?>
                    </div>
                </div>
            </form>
            <div class="vd-actions">
                <button type="submit" class="button button-primary" form="velocity-general-form">Simpan Perubahan</button>
                <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
                    <?php wp_nonce_field('velocity_reset_general_defaults'); ?>
                    <input type="hidden" name="action" value="velocity_reset_general_defaults">
                    <button type="submit" class="button">Set ke Default</button>
                </form>
            </div>
            <div class="vd-footer">
                <small>Powered by <a href="https://velocitydeveloper.com/" target="_blank">velocitydeveloper.com</a></small>
            </div>
        </div>
<?php
    }

    public static function reset_defaults()
    {
        if (! current_user_can('manage_options')) {
            wp_die('');
        }

        check_admin_referer('velocity_reset_general_defaults');

        $defaults = class_exists('Velocity_Addons_Settings_Registry')
            ? Velocity_Addons_Settings_Registry::get_general_defaults()
            : array();

        foreach ($defaults as $key => $value) {
            update_option($key, $value);
        }

        wp_safe_redirect(add_query_arg('reset', '1', admin_url('admin.php?page=velocity_general_settings')));
        exit;
    }
}

