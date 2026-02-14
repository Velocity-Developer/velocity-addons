<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://velocitydeveloper.com
 * @since      1.0.0
 *
 * @package    Velocity_Addons
 * @subpackage Velocity_Addons/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Velocity_Addons
 * @subpackage Velocity_Addons/admin
 * @author     Velocity <bantuanvelocity@gmail.com>
 */

class Custom_Admin_Option_Page
{
    private $status_lisensi;

    public function __construct()
    {
        // Ambil data lisensi dari option
        $this->status_lisensi = get_option('velocity_license', ['status' => false]);

        // Pastikan key 'status' ada, dan set default 'Check License' jika tidak ada atau tidak aktif
        $this->status_lisensi = !empty($this->status_lisensi['status']) && $this->status_lisensi['status'] === 'active'
            ? 'License Verified!'
            : 'Check License';

        // Daftarkan menu dan settings di admin
        add_action('admin_menu', array($this, 'add_menu_page'));
        add_action('admin_post_velocity_reset_general_defaults', array($this, 'reset_general_defaults'));
    }

    public function add_menu_page()
    {
        add_menu_page(
            'Velocity Addons',
            'Velocity Addons',
            'manage_options',
            'admin_velocity_addons',
            array($this, 'page_velocity_addons'),
            'data:image/svg+xml;base64,' . base64_encode('<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-gear-wide-connected" viewBox="0 0 16 16"><path d="M7.068.727c.243-.97 1.62-.97 1.864 0l.071.286a.96.96 0 0 0 1.622.434l.205-.211c.695-.719 1.888-.03 1.613.931l-.08.284a.96.96 0 0 0 1.187 1.187l.283-.081c.96-.275 1.65.918.931 1.613l-.211.205a.96.96 0 0 0 .434 1.622l.286.071c.97.243.97 1.62 0 1.864l-.286.071a.96.96 0 0 0-.434 1.622l.211.205c.719.695.03 1.888-.931 1.613l-.284-.08a.96.96 0 0 0-1.187 1.187l.081.283c.275.96-.918 1.65-1.613.931l-.205-.211a.96.96 0 0 0-1.622.434l-.071.286c-.243.97-1.62.97-1.864 0l-.071-.286a.96.96 0 0 0-1.622-.434l-.205.211c-.695.719-1.888.03-1.613-.931l.08-.284a.96.96 0 0 0-1.186-1.187l-.284.081c-.96.275-1.65-.918-.931-1.613l.211-.205a.96.96 0 0 0-.434-1.622l-.286-.071c-.97-.243-.97-1.62 0-1.864l.286-.071a.96.96 0 0 0 .434-1.622l-.211-.205c-.719-.695-.03-1.888.931-1.613l.284.08a.96.96 0 0 0 1.187-1.186l-.081-.284c-.275-.96.918-1.65 1.613-.931l.205.211a.96.96 0 0 0 1.622-.434zM12.973 8.5H8.25l-2.834 3.779A4.998 4.998 0 0 0 12.973 8.5m0-1a4.998 4.998 0 0 0-7.557-3.779l2.834 3.78zM5.048 3.967l-.087.065zm-.431.355A4.98 4.98 0 0 0 3.002 8c0 1.455.622 2.765 1.615 3.678L7.375 8zm.344 7.646.087.065z"/></svg>'),
            70
        );
        $submenus = class_exists('Velocity_Addons_Settings_Registry')
            ? Velocity_Addons_Settings_Registry::get_submenu_pages()
            : array();

        foreach ($submenus as $submenu) {
            if (!$this->is_submenu_enabled($submenu)) {
                continue;
            }

            $callback_method = isset($submenu['callback']) ? (string) $submenu['callback'] : '';
            if ($callback_method === '' || !method_exists($this, $callback_method)) {
                continue;
            }

            $page_title = isset($submenu['page_title']) ? (string) $submenu['page_title'] : '';
            $menu_title = isset($submenu['menu_title']) ? (string) $submenu['menu_title'] : $page_title;
            $slug       = isset($submenu['slug']) ? (string) $submenu['slug'] : '';
            $capability = isset($submenu['capability']) ? (string) $submenu['capability'] : 'manage_options';

            if ($page_title === '' || $menu_title === '' || $slug === '') {
                continue;
            }

            add_submenu_page(
                'admin_velocity_addons',
                $page_title,
                $menu_title,
                $capability,
                $slug,
                array($this, $callback_method)
            );
        }
    }

    private function is_submenu_enabled($submenu)
    {
        if (!isset($submenu['feature_toggle']) || !is_array($submenu['feature_toggle'])) {
            return true;
        }

        $toggle = $submenu['feature_toggle'];
        $option_name = isset($toggle['option']) ? (string) $toggle['option'] : '';
        if ($option_name === '') {
            return true;
        }

        $default_value = isset($toggle['default']) ? $toggle['default'] : '0';
        $enabled_value = isset($toggle['enabled']) ? $toggle['enabled'] : '1';
        $stored_value  = get_option($option_name, $default_value);

        return (string) $stored_value === (string) $enabled_value;
    }

    public function velocity_seo_page()
    {
        Velocity_Addons_SEO::render_seo_settings_page();
    }

    public function velocity_floating_whatsapp_page()
    {
        Velocity_Addons_Floating_Whatsapp::floating_whatsapp_page();
    }

    public function velocity_snippet_settings()
    {
        Velocity_Addons_Snippet::snippet_page();
    }

    public function velocity_news_page()
    {
        Velocity_Addons_News::render_news_settings_page();
    }

    public function velocity_duitku_page()
    {
        Velocity_Addons_Duitku::render_settings_page();
    }

    public function page_velocity_addons()
    {
        Velocity_Addons_Dashboard::render_dashboard_page();
    }

    public function register_settings()
    {
        if (function_exists('_deprecated_function')) {
            _deprecated_function(__METHOD__, '2.0.0', 'Velocity_Addons_Settings_Registrar::register_all');
        }

        if (class_exists('Velocity_Addons_Settings_Registrar')) {
            Velocity_Addons_Settings_Registrar::register_all();
        }
    }

    public function field($data)
    {

        $type   = isset($data['type']) ? $data['type'] : '';
        $id     = isset($data['id']) ? $data['id'] : '';
        $std    = isset($data['std']) ? $data['std'] : '';
        $step   = isset($data['step']) ? $data['step'] : '';
        $value  = get_option($id, $std);
        $name   = $id;

        // jika ada sub, sub array dari Value
        if (isset($data['sub']) && !empty($data['sub'])) {
            $sub    = $data['sub'];
            $value  = isset($value[$sub]) ? $value[$sub] : '';
            $name   = $id . '[' . $sub . ']';
            $id     = $id . '__' . $sub;
        }

        if ($std && empty($value) && $type != 'checkbox') {
            $value = $std;
        }

        //jika field checkbox
        if ($type == 'checkbox') {
            $checked = ($value == 1) ? 'checked' : '';
            echo '<input type="checkbox" id="' . $id . '" name="' . $name . '" value="1" ' . $checked . '> ';
        }
        //jika field text
        if ($type == 'text') {
            echo '<div><input type="text" id="' . $id . '" name="' . $name . '" value="' . $value . '" class="regular-text"></div>';
        }

        if ($type == 'password') {
            echo '<div><input type="password" id="' . $id . '" name="' . $name . '" value="' . $value . '" class="regular-text"></div>';
        }

        //jika field number
        if ($type == 'number') {
            echo '<div><input type="number" step="' . $step . '" min="0" id="' . $id . '" name="' . $name . '" value="' . $value . '" class="small-text"></div>';
        }
        //jika field textarea
        if ($type == 'textarea') {
            echo '<div>';
            echo '<textarea id="' . $id . '" name="' . $name . '" rows="6" cols="50" class="large-text">';
            echo $value;
            echo '</textarea>';
            echo '</div>';
        }

        if ($type == 'media') {
            $image_id     = absint($value);
            $image_url    = $image_id ? wp_get_attachment_image_url($image_id, 'medium') : '';
            $input_value  = $image_id ? $image_id : '';
            echo '<div class="vd-media-field">';
            echo '<div class="vd-media-preview">';
            if ($image_url) {
                echo '<img src="' . esc_url($image_url) . '" alt="">';
            } else {
                echo '<span class="vd-media-placeholder">Belum ada gambar yang dipilih.</span>';
            }
            echo '</div>';
            echo '<input type="hidden" id="' . esc_attr($id) . '" name="' . esc_attr($name) . '" value="' . esc_attr($input_value) . '">';
            echo '<div class="vd-media-actions">';
            echo '<button type="button" class="button vd-media-upload" data-target="' . esc_attr($id) . '">Pilih Gambar</button>';
            $remove_style = $image_id ? '' : ' style="display:none;"';
            echo '<button type="button" class="button vd-media-remove" data-target="' . esc_attr($id) . '"' . $remove_style . '>Hapus</button>';
            echo '</div>';
            echo '</div>';
        }

        if ($type == 'select') {
            $options = isset($data['options']) && is_array($data['options']) ? $data['options'] : [];
            echo '<div>';
            echo '<select id="' . $id . '" name="' . $name . '">';
            foreach ($options as $opt_val => $opt_label) {
                $selected = ((string)$value === (string)$opt_val) ? 'selected' : '';
                echo '<option value="' . esc_attr($opt_val) . '" ' . $selected . '>' . esc_html($opt_label) . '</option>';
            }
            echo '</select>';
            echo '</div>';
        }

        ///tampil label
        if (isset($data['label']) && !empty($data['label'])) {
            echo '<label for="' . $id . '">';
            echo '<small>' . $data['label'] . '</small>';
            echo '</label>';
        }

        ///tampil deskripsi
        if (isset($data['desc']) && !empty($data['desc'])) {
            echo '<div>';
            echo '<small>' . $data['desc'] . '</small>';
            echo '</div>';
        }
    }

    /**
     * Legacy generic options page callback.
     *
     * Catatan: method ini tidak lagi dipakai oleh alur menu aktif.
     * Dashboard utama sekarang dirender oleh `Velocity_Addons_Dashboard::render_dashboard_page()`.
     *
     * @deprecated 2.0.0 Tidak dipakai lagi setelah migrasi dashboard/settings.
     */
    public function options_page_callback()
    {
        if (function_exists('_deprecated_function')) {
            _deprecated_function(__METHOD__, '2.0.0', 'Velocity_Addons_Dashboard::render_dashboard_page');
        }

        if (function_exists('wp_enqueue_media')) {
            wp_enqueue_media();
        }
        $pages = [
            'captcha' => [
                'title'     => 'Captcha',
                'fields'    => [
                    [
                        'id'    => 'captcha_velocity',
                        'sub'   => 'aktif',
                        'type'  => 'checkbox',
                        'title' => 'Captcha',
                        'std'   => 1,
                        'label' => 'Aktifkan Google reCaptcha v2',
                        'desc'  => 'Gunakan reCaptcha v2 di Form Login, Komentar dan Velocity Toko <br>
                                Untuk <strong>Contact Form 7</strong> gunakan <code>[velocity_captcha]</code>'
                    ],
                    [
                        'id'    => 'captcha_velocity',
                        'sub'   => 'sitekey',
                        'type'  => 'text',
                        'title' => 'Sitekey'
                    ],
                    [
                        'id'    => 'captcha_velocity',
                        'sub'   => 'secretkey',
                        'type'  => 'text',
                        'title' => 'Secretkey'
                    ]
                ],
            ],
            'maintenance' => [
                'title'     => 'Maintenance Mode',
                'fields'    => [
                    [
                        'id'    => 'maintenance_mode',
                        'type'  => 'checkbox',
                        'title' => 'Maintenance Mode',
                        'std'   => 1,
                        'label' => 'Aktifkan mode perawatan pada situs. Saat mode perawatan diaktifkan, pengunjung situs akan melihat halaman pemberitahuan perawatan yang menunjukkan bahwa situs sedang dalam perbaikan atau tidak tersedia sementara waktu.',
                    ],
                    [
                        'id'    => 'maintenance_mode_data',
                        'sub'   => 'header',
                        'type'  => 'text',
                        'title' => 'Header',
                        'std'   => 'Maintenance Mode',
                    ],
                    [
                        'id'    => 'maintenance_mode_data',
                        'sub'   => 'body',
                        'type'  => 'textarea',
                        'title' => 'Body',
                        'std'   => 'We are currently performing maintenance. Please check back later.',
                    ],
                    [
                        'id'    => 'maintenance_mode_data',
                        'sub'   => 'background',
                        'type'  => 'media',
                        'title' => 'Background Image',
                        'label' => 'Pilih gambar latar belakang untuk tampilan halaman maintenance.',
                    ]
                ],
            ],
            'license' => [
                'title'     => 'License',
                'fields'    => [
                    [
                        'id'    => 'velocity_license',
                        'sub'   => 'key',
                        'type'  => 'password',
                        'title' => 'License Key',
                        'std'   => '',
                        'label' => '<br><a class="check-license button button-primary">' . $this->status_lisensi . '</a><br><span class="license-status"></span>',
                    ]
                ],
            ],
            'security' => [
                'title'     => 'Security',
                'fields'    => [
                    [
                        'id'    => 'limit_login_attempts',
                        'type'  => 'checkbox',
                        'title' => 'Limit Login Attempts',
                        'std'   => 1,
                        'label' => 'Batasi jumlah percobaan login yang diizinkan untuk pengguna, ketika pengguna melakukan percobaan login yang melebihi 5X dalam 24 Jam, mereka akan diblokir untuk sementara waktu sebagai tindakan keamanan.',
                    ],
                    [
                        'id'    => 'disable_xmlrpc',
                        'type'  => 'checkbox',
                        'title' => 'Disable XML-RPC',
                        'std'   => 1,
                        'label' => 'Nonaktifkan protokol XML-RPC pada situs. XML-RPC digunakan oleh beberapa aplikasi atau layanan pihak ketiga untuk berinteraksi dengan situs WordPress.',
                    ],
                    [
                        'id'    => 'block_wp_login',
                        'type'  => 'checkbox',
                        'title' => 'Block wp-login.php',
                        'std'   => 0,
                        'label' => 'Aktifkan pemblokiran akses ke file wp-login.php pada situs.',
                    ],
                    [
                        'id'    => 'whitelist_block_wp_login',
                        'type'  => 'text',
                        'title' => 'Whitelist IP Block wp-login.php',
                        'std'   => '',
                        'label' => 'Tambahkan daftar IP yang di Whitelist proses pemblokiran akses ke file wp-login.php.',
                    ],
                    [
                        'id'    => 'whitelist_country',
                        'type'  => 'text',
                        'title' => 'Whitelist Country',
                        'std'   => 'ID',
                        'label' => 'Batasi akses ke situs WordPress hanya untuk negara-negara tertentu dengan menggunakan ID negara sebagai pemisah, seperti contoh ID,MY,US.',
                    ],
                    [
                        'id'    => 'redirect_to',
                        'type'  => 'text',
                        'title' => 'Redirect To',
                        'std'   => '127.0.0.1',
                        'label' => 'Tujuan redirect wp-login.php, jika Block wp-login.php aktif.',
                    ],
                ],
            ],
            'auto_resize' => [
                'title'     => 'Auto Resize Image',
                'fields'    => [
                    [
                        'id'    => 'auto_resize_mode',
                        'type'  => 'checkbox',
                        'title' => 'Enable re-sizing',
                        // 'std'   => 0,
                        'label' => 'Aktifkan re-sizing pada situs.',
                    ],
                    [
                        'id'    => 'auto_resize_mode_data',
                        'sub'   => 'maxwidth',
                        'type'  => 'number',
                        'title' => 'Max width',
                        'std'   => 1200,
                        'step'  => 1,
                    ],
                    [
                        'id'    => 'auto_resize_mode_data',
                        'sub'   => 'maxheight',
                        'type'  => 'number',
                        'title' => 'Max height',
                        'std'   => 1200,
                        'step'  => 1,
                    ],
                    [
                        'id'    => 'auto_resize_mode_data',
                        'sub'   => 'quality',
                        'type'  => 'number',
                        'title' => 'Quality',
                        'std'   => 90,
                        'step'  => 1,
                    ],
                    [
                        'id'      => 'auto_resize_mode_data',
                        'sub'     => 'output_format',
                        'type'    => 'select',
                        'title'   => 'Output format',
                        'std'     => 'original',
                        'options' => [
                            'original' => 'Original',
                            'jpeg'     => 'JPEG',
                            'webp'     => 'WebP',
                            'avif'     => 'AVIF',
                        ],
                    ]
                ],
            ],
        ];
        $pages_tabs = $pages;
        unset($pages_tabs['umum']);
?>
        <div class="velocity-dashboard-wrapper vd-ons">
            <div class="vd-header">
                <h1 class="vd-title">Pengaturan Admin</h1>
                <p class="vd-subtitle">Gunakan submenu di bawah "Velocity Addons" untuk mengakses masing-masing pengaturan.</p>
            </div>
            <div class="vd-section">
                <div class="vd-section-header" style="padding: 1.25rem 1.5rem; border-bottom: 1px solid #e5e7eb; background-color: #f9fafb;">
                    <h3 style="margin:0; font-size:1.1rem; color:#374151;">Quick Links</h3>
                </div>
                <div class="vd-section-body">
                    <ul class="vd-list">
                        <li><a href="<?php echo admin_url('admin.php?page=velocity_general_settings'); ?>">Pengaturan Umum</a></li>
                        <li><a href="<?php echo admin_url('admin.php?page=velocity_captcha_settings'); ?>">Captcha</a></li>
                        <li><a href="<?php echo admin_url('admin.php?page=velocity_maintenance_settings'); ?>">Maintenance Mode</a></li>
                        <li><a href="<?php echo admin_url('admin.php?page=velocity_license_settings'); ?>">License</a></li>
                        <li><a href="<?php echo admin_url('admin.php?page=velocity_security_settings'); ?>">Security</a></li>
                        <li><a href="<?php echo admin_url('admin.php?page=velocity_auto_resize_settings'); ?>">Auto Resize</a></li>
                    </ul>
                </div>
            </div>
            <div class="vd-footer">
                <small>Powered by <a href="https://velocitydeveloper.com/" target="_blank">velocitydeveloper.com</a></small>
            </div>
        </div>
    <?php
    }

    public function velocity_general_page()
    {
        Velocity_Addons_Admin_Page_General::render();
    }

    public function reset_general_defaults()
    {
        Velocity_Addons_Admin_Page_General::reset_defaults();
    }

    public function velocity_captcha_page()
    {
        Velocity_Addons_Admin_Page_Captcha::render(array($this, 'field'));
    }

    public function velocity_maintenance_page()
    {
        Velocity_Addons_Admin_Page_Maintenance::render(array($this, 'field'));
    }

    public function velocity_license_page()
    {
        Velocity_Addons_Admin_Page_License::render(array($this, 'field'), $this->status_lisensi);
    }

    public function velocity_security_page()
    {
        Velocity_Addons_Admin_Page_Security::render(array($this, 'field'));
    }

    public function velocity_auto_resize_page()
    {
        Velocity_Addons_Admin_Page_Auto_Resize::render(array($this, 'field'));
    }
    public function visitor_stats_page_callback()
    {
        Velocity_Addons_Admin_Page_Statistics::render();
    }

    public function optimize_db_page_callback()
    {
        Velocity_Addons_Admin_Page_Optimize::render();
    }
}

// Initialize the Pengaturan Admin page
$custom_admin_options_page = new Custom_Admin_Option_Page();

