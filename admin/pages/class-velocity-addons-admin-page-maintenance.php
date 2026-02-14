<?php

/**
 * Maintenance settings page renderer.
 *
 * @package Velocity_Addons
 * @subpackage Velocity_Addons/admin/pages
 */
class Velocity_Addons_Admin_Page_Maintenance
{
    public static function render($field_renderer = null)
    {
        if (!current_user_can('manage_options')) {
            return;
        }
        if (function_exists('wp_enqueue_media')) {
            wp_enqueue_media();
        }
?>
        <div class="velocity-dashboard-wrapper">
            <div class="vd-header">
                <h1 class="vd-title">Maintenance Mode</h1>
                <p class="vd-subtitle">Pengaturan tampilan dan status maintenance situs.</p>
            </div>
            <form method="post" data-velocity-settings="1">
                <div class="vd-section">
                    <div class="vd-section-header" style="padding: 1.25rem 1.5rem; border-bottom: 1px solid #e5e7eb; background-color: #f9fafb;">
                        <h3 style="margin:0; font-size:1.1rem; color:#374151;">Maintenance Mode</h3>
                    </div>
                    <div class="vd-section-body">
                        <?php
                        $mm_val = get_option('maintenance_mode', 1);
                        $mm_checked = ($mm_val == 1) ? 'checked' : '';
                        echo '<div class="vd-form-group">';
                        echo '<div class="vd-form-left">';
                        echo '<label class="vd-form-label" for="maintenance_mode">Maintenance Mode</label>';
                        echo '<small class="vd-form-hint">Aktifkan mode perawatan pada situs.</small>';
                        echo '</div>';
                        echo '<div class="vd-form-right">';
                        echo '<label class="vd-switch">';
                        echo '<input type="checkbox" id="maintenance_mode" name="maintenance_mode" value="1" ' . $mm_checked . '>';
                        echo '<span class="vd-switch-slider" aria-hidden="true"></span>';
                        echo '</label>';
                        echo '</div>';
                        echo '</div>';

                        echo '<div class="vd-form-group">';
                        echo '<div class="vd-form-left">';
                        echo '<label class="vd-form-label" for="maintenance_mode__header">Header</label>';
                        echo '</div>';
                        echo '<div class="vd-form-right">';
                        if (is_callable($field_renderer)) {
                            call_user_func($field_renderer, array('id' => 'maintenance_mode_data', 'sub' => 'header', 'type' => 'text', 'std' => 'Maintenance Mode'));
                        }
                        echo '</div>';
                        echo '</div>';

                        echo '<div class="vd-form-group">';
                        echo '<div class="vd-form-left">';
                        echo '<label class="vd-form-label" for="maintenance_mode__body">Body</label>';
                        echo '</div>';
                        echo '<div class="vd-form-right">';
                        if (is_callable($field_renderer)) {
                            call_user_func($field_renderer, array('id' => 'maintenance_mode_data', 'sub' => 'body', 'type' => 'textarea', 'std' => 'We are currently performing maintenance. Please check back later.'));
                        }
                        echo '</div>';
                        echo '</div>';

                        echo '<div class="vd-form-group">';
                        echo '<div class="vd-form-left">';
                        echo '<label class="vd-form-label" for="maintenance_mode__background">Background Image</label>';
                        echo '<small class="vd-form-hint">Pilih gambar latar belakang untuk tampilan halaman maintenance.</small>';
                        echo '</div>';
                        echo '<div class="vd-form-right">';
                        if (is_callable($field_renderer)) {
                            call_user_func($field_renderer, array('id' => 'maintenance_mode_data', 'sub' => 'background', 'type' => 'media', 'title' => 'Background Image'));
                        }
                        echo '</div>';
                        echo '</div>';
                        ?>
                    </div>
                </div>
                <?php submit_button(); ?>
            </form>
            <script>
                jQuery(document).ready(function($) {
                    if (typeof wp !== 'undefined' && wp.media) {
                        $('.vd-media-upload').on('click', function(e) {
                            e.preventDefault();
                            var button = $(this);
                            var field = button.closest('.vd-media-field');
                            var mediaFrame = wp.media({
                                title: 'Pilih atau Upload Gambar',
                                button: {
                                    text: 'Gunakan Gambar Ini'
                                },
                                library: {
                                    type: 'image'
                                },
                                multiple: false
                            });
                            var currentId = field.find('input[type="hidden"]').val();
                            if (currentId) {
                                mediaFrame.on('open', function() {
                                    var selection = mediaFrame.state().get('selection');
                                    selection.reset();
                                    var attachment = wp.media.attachment(currentId);
                                    attachment.fetch();
                                    selection.add(attachment);
                                });
                            }
                            mediaFrame.on('select', function() {
                                var attachment = mediaFrame.state().get('selection').first().toJSON();
                                var imageUrl = attachment.sizes && attachment.sizes.medium ? attachment.sizes.medium.url : attachment.url;
                                field.find('input[type="hidden"]').val(attachment.id);
                                field.find('.vd-media-preview').html('<img src="' + imageUrl + '" alt="">');
                                field.find('.vd-media-remove').show();
                            });
                            mediaFrame.open();
                        });
                        $('.vd-media-remove').on('click', function(e) {
                            e.preventDefault();
                            var button = $(this);
                            var field = button.closest('.vd-media-field');
                            field.find('input[type="hidden"]').val('');
                            field.find('.vd-media-preview').html('<span class="vd-media-placeholder">Belum ada gambar yang dipilih.</span>');
                            button.hide();
                        });
                    }
                });
            </script>
            <div class="vd-footer">
                <small>Powered by <a href="https://velocitydeveloper.com/" target="_blank">velocitydeveloper.com</a></small>
            </div>
        </div>
<?php
    }
}

