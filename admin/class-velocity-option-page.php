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
        add_action('admin_init', array($this, 'register_settings'));
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

        $seo_velocity = get_option('seo_velocity', '1');
        if ($seo_velocity == '1') {
            add_submenu_page(
                'admin_velocity_addons',
                'SEO',
                'SEO',
                'manage_options',
                'velocity_seo_settings',
                array($this, 'velocity_seo_page'),
            );
        }

        $floating_whatsapp = get_option('floating_whatsapp', '1');
        if ($floating_whatsapp == '1') {
            add_submenu_page(
                'admin_velocity_addons',
                'Floating Whatsapp',
                'Floating Whatsapp',
                'manage_options',
                'velocity_floating_whatsapp',
                [$this, 'velocity_floating_whatsapp_page'],
            );
        }

        $news_generate = get_option('news_generate', '1');
        if ($news_generate == '1') {
            add_submenu_page(
                'admin_velocity_addons',
                'Import Artikel',
                'Import Artikel',
                'manage_options',
                'velocity_news_settings',
                array($this, 'velocity_news_page'),
            );
        }

        $velocity_duitku = get_option('velocity_duitku', '0');
        if ($velocity_duitku == '1') {
            add_submenu_page(
                'admin_velocity_addons',
                'Duitku',
                'Duitku',
                'manage_options',
                'velocity_duitku_settings',
                array($this, 'velocity_duitku_page'),
            );
        }

        add_submenu_page(
            'admin_velocity_addons',
            'Pengaturan Umum',
            'Pengaturan Umum',
            'manage_options',
            'velocity_general_settings',
            array($this, 'velocity_general_page'),
        );
        add_submenu_page(
            'admin_velocity_addons',
            'Captcha',
            'Captcha',
            'manage_options',
            'velocity_captcha_settings',
            array($this, 'velocity_captcha_page'),
        );
        add_submenu_page(
            'admin_velocity_addons',
            'Maintenance Mode',
            'Maintenance Mode',
            'manage_options',
            'velocity_maintenance_settings',
            array($this, 'velocity_maintenance_page'),
        );
        add_submenu_page(
            'admin_velocity_addons',
            'License',
            'License',
            'manage_options',
            'velocity_license_settings',
            array($this, 'velocity_license_page'),
        );
        add_submenu_page(
            'admin_velocity_addons',
            'Security',
            'Security',
            'manage_options',
            'velocity_security_settings',
            array($this, 'velocity_security_page'),
        );
        add_submenu_page(
            'admin_velocity_addons',
            'Auto Resize',
            'Auto Resize',
            'manage_options',
            'velocity_auto_resize_settings',
            array($this, 'velocity_auto_resize_page'),
        );

        add_submenu_page(
            'admin_velocity_addons',
            'Code Snippet',
            'Code Snippet',
            'manage_options',
            'velocity_snippet_settings',
            array($this, 'velocity_snippet_settings'),
        );

        $statistik_velocity = get_option('statistik_velocity', '1');
        if ($statistik_velocity == '1') {
            add_submenu_page(
                'admin_velocity_addons',
                'Statistik Pengunjung',
                'Statistik Pengunjung',
                'manage_options',
                'velocity_statistics',
                array($this, 'visitor_stats_page_callback')
            );
        }

        $optimasi_velocity = get_option('velocity_optimasi', '0');
        if ($optimasi_velocity == '1') {
            add_submenu_page(
                'admin_velocity_addons',
                'Optimize Database',
                'Optimize Database',
                'manage_options',
                'velocity_optimize_db',
                array($this, 'optimize_db_page_callback')
            );
        }
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
        // General
        register_setting('velocity_general_options_group', 'fully_disable_comment');
        register_setting('velocity_general_options_group', 'hide_admin_notice');
        register_setting('velocity_general_options_group', 'disable_gutenberg');
        register_setting('velocity_general_options_group', 'classic_widget_velocity');
        register_setting('velocity_general_options_group', 'remove_slug_category_velocity');
        register_setting('velocity_general_options_group', 'enable_xml_sitemap');
        register_setting('velocity_general_options_group', 'seo_velocity');
        register_setting('velocity_general_options_group', 'statistik_velocity');
        register_setting('velocity_general_options_group', 'floating_whatsapp');
        register_setting('velocity_general_options_group', 'floating_scrollTop');
        register_setting('velocity_general_options_group', 'news_generate');
        register_setting('velocity_general_options_group', 'velocity_gallery');
        register_setting('velocity_general_options_group', 'velocity_optimasi');
        register_setting('velocity_general_options_group', 'velocity_duitku');

        // Security
        register_setting('velocity_security_options_group', 'limit_login_attempts');
        register_setting('velocity_security_options_group', 'disable_xmlrpc');
        register_setting('velocity_security_options_group', 'block_wp_login');
        register_setting('velocity_security_options_group', 'whitelist_block_wp_login');
        register_setting('velocity_security_options_group', 'whitelist_country');
        register_setting('velocity_security_options_group', 'redirect_to');

        // Maintenance
        register_setting('velocity_maintenance_options_group', 'maintenance_mode');
        register_setting('velocity_maintenance_options_group', 'maintenance_mode_data');

        // License
        register_setting('velocity_license_options_group', 'license_key');
        register_setting('velocity_license_options_group', 'velocity_license');

        // Auto Resize
        register_setting('velocity_auto_resize_options_group', 'auto_resize_mode');
        register_setting('velocity_auto_resize_options_group', 'auto_resize_mode_data');
        register_setting('velocity_auto_resize_options_group', 'auto_resize_image_velocity');

        // Captcha
        register_setting('velocity_captcha_options_group', 'captcha_velocity');
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

    public function options_page_callback()
    {
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
        if (!current_user_can('manage_options')) return;
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
                        $fields = [
                            ['id' => 'fully_disable_comment', 'type' => 'checkbox', 'title' => 'Disable Comment', 'std' => 1, 'label' => 'Nonaktifkan fitur komentar pada situs.'],
                            ['id' => 'hide_admin_notice', 'type' => 'checkbox', 'title' => 'Hide Admin Notice', 'std' => 0, 'label' => 'Sembunyikan pemberitahuan admin di halaman admin. Pemberitahuan admin seringkali muncul untuk memberikan informasi atau peringatan kepada admin situs.'],
                            ['id' => 'disable_gutenberg', 'type' => 'checkbox', 'title' => 'Disable Gutenberg', 'std' => 0, 'label' => 'Aktifkan editor klasik WordPress menggantikan Gutenberg.'],
                            ['id' => 'classic_widget_velocity', 'type' => 'checkbox', 'title' => 'Classic Widget', 'std' => 1, 'label' => 'Aktifkan widget klasik.'],
                            ['id' => 'enable_xml_sitemap', 'type' => 'checkbox', 'title' => 'XML Sitemap', 'std' => 1, 'label' => 'Aktifkan XML Sitemap Generator (sitemap.xml).'],
                            ['id' => 'seo_velocity', 'type' => 'checkbox', 'title' => 'SEO', 'std' => 1, 'label' => 'Aktifkan SEO dari Velocity Developer.'],
                            ['id' => 'statistik_velocity', 'type' => 'checkbox', 'title' => 'Statistik Pengunjung', 'std' => 1, 'label' => 'Aktifkan statistik pengunjung dari Velocity Developer.'],
                            ['id' => 'floating_whatsapp', 'type' => 'checkbox', 'title' => 'Floating Whatsapp', 'std' => 1, 'label' => 'Aktifkan Whatsapp Floating.'],
                            ['id' => 'floating_scrollTop', 'type' => 'checkbox', 'title' => 'Floating Scrolltop', 'std' => 1, 'label' => 'Aktifkan scrollTop ke halaman atas.'],
                            ['id' => 'remove_slug_category_velocity', 'type' => 'checkbox', 'title' => 'Remove Slug Category', 'std' => 0, 'label' => 'Aktifkan untuk hapus slug /category/ dari URL.'],
                            ['id' => 'news_generate', 'type' => 'checkbox', 'title' => 'Import Artikel dari API', 'std' => 1, 'label' => 'Aktifkan fungsi untuk import artikel postingan.'],
                            ['id' => 'velocity_gallery', 'type' => 'checkbox', 'title' => 'Gallery Post Type', 'std' => 0, 'label' => 'Aktifkan fungsi untuk menggunakan Gallery Post Type.'],
                            ['id' => 'velocity_optimasi', 'type' => 'checkbox', 'title' => 'Optimize Database', 'std' => 0, 'label' => 'Aktifkan fungsi untuk mengoptimalkan situs dari database.'],
                            ['id' => 'velocity_duitku', 'type' => 'checkbox', 'title' => 'Payment Gateway Duitku', 'std' => 0, 'label' => 'Aktifkan payment gateway Duitku.'],
                        ];
                        foreach ($fields as $data) {
                            $id   = $data['id'];
                            $std  = isset($data['std']) ? $data['std'] : '';
                            $val  = get_option($id, $std);
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

    public function reset_general_defaults()
    {
        if (! current_user_can('manage_options')) {
            wp_die('');
        }
        check_admin_referer('velocity_reset_general_defaults');
        $defaults = [
            'fully_disable_comment' => 1,
            'hide_admin_notice' => 0,
            'disable_gutenberg' => 0,
            'classic_widget_velocity' => 1,
            'enable_xml_sitemap' => 1,
            'seo_velocity' => 1,
            'statistik_velocity' => 1,
            'floating_whatsapp' => 1,
            'floating_scrollTop' => 1,
            'remove_slug_category_velocity' => 0,
            'news_generate' => 1,
            'velocity_gallery' => 0,
            'velocity_optimasi' => 0,
            'velocity_duitku' => 0,
        ];
        foreach ($defaults as $k => $v) {
            update_option($k, $v);
        }
        wp_safe_redirect(add_query_arg('reset', '1', admin_url('admin.php?page=velocity_general_settings')));
        exit;
    }

    public function velocity_captcha_page()
    {
        if (!current_user_can('manage_options')) return;
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
                        $opt = get_option('captcha_velocity', []);
                        $providerVal = isset($opt['provider']) ? $opt['provider'] : 'google';
                        $fields = [
                            ['id' => 'captcha_velocity', 'sub' => 'provider', 'type' => 'select', 'title' => 'Provider', 'label' => 'Pilih jenis captcha yang digunakan.', 'options' => ['google' => 'Google reCaptcha v2', 'image' => 'Captcha Gambar']],
                            ['id' => 'captcha_velocity', 'sub' => 'aktif', 'type' => 'checkbox', 'title' => 'Captcha', 'std' => 1, 'label' => 'Aktifkan Captcha', 'desc' => 'Gunakan Captcha di Form Login, Komentar dan Velocity Toko. Untuk Contact Form 7 gunakan [velocity_captcha]'],
                            ['id' => 'captcha_velocity', 'sub' => 'difficulty', 'type' => 'select', 'title' => 'Tingkat Kesulitan', 'label' => 'Untuk Captcha Gambar', 'options' => ['easy' => 'Mudah', 'medium' => 'Sedang', 'hard' => 'Sulit']],
                            ['id' => 'captcha_velocity', 'sub' => 'sitekey', 'type' => 'text', 'title' => 'Sitekey'],
                            ['id' => 'captcha_velocity', 'sub' => 'secretkey', 'type' => 'text', 'title' => 'Secretkey'],
                        ];
                        foreach ($fields as $data) {
                            $labelFor = isset($data['sub']) ? ($data['id'] . '__' . $data['sub']) : $data['id'];
                            $visibilityExpr = '';
                            $visibilityStyle = '';
                            if (isset($data['sub']) && in_array($data['sub'], ['sitekey', 'secretkey'], true)) {
                                $visibilityExpr = "(model['captcha_velocity'] && model['captcha_velocity']['provider'] === 'google')";
                                if ($providerVal !== 'google') {
                                    $visibilityStyle = 'display:none;';
                                }
                            }
                            if (isset($data['sub']) && $data['sub'] === 'difficulty') {
                                $visibilityExpr = "(model['captcha_velocity'] && model['captcha_velocity']['provider'] === 'image')";
                                if ($providerVal !== 'image') {
                                    $visibilityStyle = 'display:none;';
                                }
                            }

                            $groupAttrs = ' class="vd-form-group"';
                            if ($visibilityExpr !== '') {
                                $groupAttrs .= ' x-show="' . esc_attr($visibilityExpr) . '"';
                            }
                            if ($visibilityStyle !== '') {
                                $groupAttrs .= ' style="' . esc_attr($visibilityStyle) . '"';
                            }

                            echo '<div' . $groupAttrs . '>';
                            echo '<div class="vd-form-left">';
                            echo '<label class="vd-form-label" for="' . esc_attr($labelFor) . '">' . esc_html($data['title']) . '</label>';
                            if (isset($data['desc'])) echo '<small class="vd-form-hint">' . esc_html($data['desc']) . '</small>';
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
                                echo '<input type="checkbox" id="' . esc_attr($labelFor) . '" name="' . esc_attr($data['id']) . (isset($data['sub']) ? '[' . esc_attr($data['sub']) . ']' : '') . '" value="1" ' . $checked . '>';
                                echo '<span class="vd-switch-slider" aria-hidden="true"></span>';
                                echo '</label>';
                                echo '</div>';
                            } else {
                                echo '<div class="vd-form-right">';
                                $this->field($data);
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

    public function velocity_maintenance_page()
    {
        if (!current_user_can('manage_options')) return;
        if (function_exists('wp_enqueue_media')) wp_enqueue_media();
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
                        // Switch for Maintenance Mode
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

                        // Header text
                        echo '<div class="vd-form-group">';
                        echo '<div class="vd-form-left">';
                        echo '<label class="vd-form-label" for="maintenance_mode__header">Header</label>';
                        echo '</div>';
                        echo '<div class="vd-form-right">';
                        $this->field(['id' => 'maintenance_mode_data', 'sub' => 'header', 'type' => 'text', 'std' => 'Maintenance Mode']);
                        echo '</div>';
                        echo '</div>';

                        // Body textarea
                        echo '<div class="vd-form-group">';
                        echo '<div class="vd-form-left">';
                        echo '<label class="vd-form-label" for="maintenance_mode__body">Body</label>';
                        echo '</div>';
                        echo '<div class="vd-form-right">';
                        $this->field(['id' => 'maintenance_mode_data', 'sub' => 'body', 'type' => 'textarea', 'std' => 'We are currently performing maintenance. Please check back later.']);
                        echo '</div>';
                        echo '</div>';

                        // Background media
                        echo '<div class="vd-form-group">';
                        echo '<div class="vd-form-left">';
                        echo '<label class="vd-form-label" for="maintenance_mode__background">Background Image</label>';
                        echo '<small class="vd-form-hint">Pilih gambar latar belakang untuk tampilan halaman maintenance.</small>';
                        echo '</div>';
                        echo '<div class="vd-form-right">';
                        $this->field(['id' => 'maintenance_mode_data', 'sub' => 'background', 'type' => 'media', 'title' => 'Background Image']);
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

    public function velocity_license_page()
    {
        if (!current_user_can('manage_options')) return;
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
                        $fields = [
                            ['id' => 'velocity_license', 'sub' => 'key', 'type' => 'password', 'title' => 'License Key', 'std' => '', 'label' => ''],
                        ];
                        foreach ($fields as $data) {
                            $labelFor = isset($data['sub']) ? ($data['id'] . '__' . $data['sub']) : $data['id'];
                            echo '<div class="vd-form-group">';
                            echo '<div class="vd-form-left">';
                            echo '<label class="vd-form-label" for="' . esc_attr($labelFor) . '">' . esc_html($data['title']) . '</label>';
                            echo '<small class="vd-form-hint">Masukkan kunci lisensi Anda lalu klik verifikasi.</small>';
                            echo '</div>';
                            $this->field($data);
                            echo '<a class="check-license button button-primary" style="margin-left:12px">' . $this->status_lisensi . '</a><span class="license-status" style="margin-left:8px"></span>';
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

    public function velocity_security_page()
    {
        if (!current_user_can('manage_options')) return;
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
                        $fields = [
                            ['id' => 'limit_login_attempts', 'type' => 'checkbox', 'title' => 'Limit Login Attempts', 'std' => 1, 'label' => 'Batasi jumlah percobaan login.'],
                            ['id' => 'disable_xmlrpc', 'type' => 'checkbox', 'title' => 'Disable XML-RPC', 'std' => 1, 'label' => 'Nonaktifkan protokol XML-RPC.'],
                            ['id' => 'block_wp_login', 'type' => 'checkbox', 'title' => 'Block wp-login.php', 'std' => 0, 'label' => 'Blokir akses wp-login.php.'],
                            ['id' => 'whitelist_block_wp_login', 'type' => 'text', 'title' => 'Whitelist IP Block wp-login.php', 'std' => '', 'label' => 'Daftar IP whitelist.'],
                            ['id' => 'whitelist_country', 'type' => 'text', 'title' => 'Whitelist Country', 'std' => 'ID', 'label' => 'Contoh: ID,MY,US'],
                            ['id' => 'redirect_to', 'type' => 'text', 'title' => 'Redirect To', 'std' => '127.0.0.1', 'label' => 'Tujuan redirect wp-login.php jika blokir aktif.'],
                        ];
                        foreach ($fields as $data) {
                            $labelFor = isset($data['sub']) ? ($data['id'] . '__' . $data['sub']) : $data['id'];
                            echo '<div class="vd-form-group">';
                            echo '<div class="vd-form-left">';
                            echo '<label class="vd-form-label" for="' . esc_attr($labelFor) . '">' . esc_html($data['title']) . '</label>';
                            if (isset($data['label'])) echo '<small class="vd-form-hint">' . esc_html($data['label']) . '</small>';
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
                                $this->field($data);
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

    public function velocity_auto_resize_page()
    {
        if (!current_user_can('manage_options')) return;
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
                        $fields = [
                            ['id' => 'auto_resize_mode', 'type' => 'checkbox', 'title' => 'Enable re-sizing', 'label' => 'Aktifkan re-sizing pada situs.'],
                            ['id' => 'auto_resize_mode_data', 'sub' => 'maxwidth', 'type' => 'number', 'title' => 'Max width', 'std' => 1200, 'step' => 1],
                            ['id' => 'auto_resize_mode_data', 'sub' => 'maxheight', 'type' => 'number', 'title' => 'Max height', 'std' => 1200, 'step' => 1],
                            ['id' => 'auto_resize_mode_data', 'sub' => 'quality', 'type' => 'number', 'title' => 'Quality', 'std' => 90, 'step' => 1, 'label' => 'Range 10-100. Direkomendasikan 80-90.'],
                            ['id' => 'auto_resize_mode_data', 'sub' => 'output_format', 'type' => 'select', 'title' => 'Output format', 'std' => 'original', 'options' => ['original' => 'Original', 'jpeg' => 'JPEG', 'webp' => 'WebP', 'avif' => 'AVIF'], 'label' => 'Jika format tidak didukung editor server, otomatis fallback ke format asli.'],
                        ];
                        foreach ($fields as $data) {
                            $labelFor = isset($data['sub']) ? ($data['id'] . '__' . $data['sub']) : $data['id'];
                            echo '<div class="vd-form-group">';
                            echo '<div class="vd-form-left">';
                            echo '<label class="vd-form-label" for="' . esc_attr($labelFor) . '">' . esc_html($data['title']) . '</label>';
                            if (isset($data['label'])) echo '<small class="vd-form-hint">' . esc_html($data['label']) . '</small>';
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
                                $this->field($data);
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
    public function visitor_stats_page_callback()
    {
        if (! current_user_can('manage_options')) {
            wp_die(__('You do not have sufficient permissions to access this page.'));
        }

        static $stats_handler = null;
        if (! $stats_handler) {
            $stats_handler = new Velocity_Addons_Statistic();
        }

        $rebuild_message = '';
        if (isset($_POST['reset_stats']) && check_admin_referer('reset_stats')) {
            $stats_handler->reset_statistics();
            $rebuild_message = 'Statistik berhasil di-reset. Semua data statistik dan meta hit telah dihapus.';
        }

        $summary_stats = $stats_handler->get_summary_stats();
        $page_stats    = $stats_handler->get_page_stats(30);
        $referer_stats = $stats_handler->get_referer_stats(30);

    ?>
        <div class="velocity-dashboard-wrapper vd-ons" id="velocity-statistics-page">
            <div class="vd-header">
                <h1 class="vd-title">Statistik Pengunjung</h1>
                <p class="vd-subtitle">Ringkasan trafik dan halaman populer situs.</p>
            </div>
            <div id="velocity-statistics-notice" class="notice <?php echo $rebuild_message ? 'notice-success' : ''; ?>" style="display:<?php echo $rebuild_message ? 'block' : 'none'; ?>">
                <?php if ($rebuild_message): ?>
                    <p><?php echo esc_html($rebuild_message); ?></p>
                <?php endif; ?>
            </div>
            <div class="vd-section">
                <div class="vd-section-header" style="padding: 1.25rem 1.5rem; border-bottom: 1px solid #e5e7eb; background-color: #f9fafb;">
                    <h3 style="margin:0; font-size:1.1rem; color:#374151;">Reset & Tools</h3>
                </div>
                <div class="vd-section-body">
                    <form method="post" style="display:inline;" id="velocity-statistics-reset-form">
                        <?php wp_nonce_field('reset_stats'); ?>
                        <input type="hidden" name="reset_stats" value="1">
                        <button type="submit" class="button button-secondary"
                            id="velocity-statistics-reset-button"
                            data-confirm-message="Apakah Anda yakin ingin me-reset statistik? Tindakan ini akan menghapus semua data statistik dan meta hit secara permanen.">
                            Reset Statistik
                        </button>
                        <span style="vertical-align:middle;margin-left:10px;color:#666;font-size:13px;">Gunakan ini untuk mengosongkan seluruh data statistik</span>
                    </form>
                </div>
            </div>
            <div class="vd-section">
                <div class="vd-section-header" style="padding: 1.25rem 1.5rem; border-bottom: 1px solid #e5e7eb; background-color: #f9fafb;">
                    <h3 style="margin:0; font-size:1.1rem; color:#374151;">Ringkasan</h3>
                </div>
                <div class="vd-section-body">
                    <div class="vd-grid">
                        <?php
                        $cards = array(
                            'today'      => array('label' => 'Hari Ini', 'data' => $summary_stats['today']),
                            'this_week'  => array('label' => 'Minggu Ini', 'data' => $summary_stats['this_week']),
                            'this_month' => array('label' => 'Bulan Ini', 'data' => $summary_stats['this_month']),
                            'all_time'   => array('label' => 'All Time', 'data' => $summary_stats['all_time']),
                        );
                        foreach ($cards as $card_key => $card):
                            $label = $card['label'];
                            $obj   = $card['data'];
                        ?>
                            <div class="vd-card" style="text-align:center" data-stat-card="<?php echo esc_attr($card_key); ?>">
                                <h3 style="margin:0 0 10px;color:#0073aa;"><?php echo esc_html($label); ?></h3>
                                <div style="font-size:24px;font-weight:700;color:#23282d;" data-stat-unique><?php echo number_format_i18n((int) ($obj->unique_visitors ?? 0)); ?></div>
                                <div style="color:#666;font-size:14px;">Pengunjung Unik</div>
                                <div style="color:#999;font-size:12px;"><span data-stat-total><?php echo number_format_i18n((int) ($obj->total_visits ?? 0)); ?></span> total visits</div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
            <div class="vd-grid-2">
                <div class="vd-section">
                    <div class="vd-section-header" style="padding: 1.25rem 1.5rem; border-bottom: 1px solid #e5e7eb; background-color: #f9fafb;">
                        <h3 style="margin:0; font-size:1.1rem; color:#374151;">Daily Visits (Last 30 Days)</h3>
                    </div>
                    <div class="vd-section-body">
                        <canvas id="dailyVisitsChart" style="width:100%;height:220px"></canvas>
                    </div>
                </div>
                <div class="vd-section">
                    <div class="vd-section-header" style="padding: 1.25rem 1.5rem; border-bottom: 1px solid #e5e7eb; background-color: #f9fafb;">
                        <h3 style="margin:0; font-size:1.1rem; color:#374151;">Halaman Teratas</h3>
                    </div>
                    <div class="vd-section-body">
                        <canvas id="topPagesChart" style="width:100%;height:220px"></canvas>
                    </div>
                </div>
            </div>
            <div class="vd-section">
                <div class="vd-section-header" style="padding: 1.25rem 1.5rem; border-bottom: 1px solid #e5e7eb; background-color: #f9fafb;">
                    <h3 style="margin:0; font-size:1.1rem; color:#374151;">Shortcode: [velocity-statistics]</h3>
                </div>
                <div class="vd-section-body">
                    <p>Tampilkan statistik visitor di halaman, post, atau widget.</p>
                    <ul class="vd-list">
                        <li><span class="vd-code">style</span>: pilih tampilan statistik. <span class="vd-code">list</span> atau <span class="vd-code">inline</span></li>
                        <li><span class="vd-code">show</span>: filter data yang ditampilkan. <span class="vd-code">all</span>, <span class="vd-code">today</span>, atau <span class="vd-code">total</span></li>
                        <li><span class="vd-code">with_online</span>: tampilkan jumlah pengunjung online saat ini</li>
                        <li><span class="vd-code">label_*</span>: ganti label baris counter</li>
                    </ul>
                    <div class="vd-grid-2">
                        <div>
                            <h6>Basic</h6>
                            <div style="background:#f1f1f1;padding:12px;border-radius:6px;font-family:monospace;margin-bottom:10px;overflow:hidden;">
                                <span style="color:#0073aa;cursor:pointer;" onclick="copyToClipboard('[velocity-statistics]')">[velocity-statistics]</span>
                                <button onclick="copyToClipboard('[velocity-statistics]')" class="button button-secondary" style="float: right;background: #0073aa;color: white;border: none;padding: 1px 8px;border-radius: 4px;font-size: 11px;cursor: pointer;line-height: 13px;min-height: 20px;">Copy</button>
                            </div>
                            <div style="font-size:13px;color:#666;">Semua statistik</div>
                            <div style="background:#f1f1f1;padding:12px;border-radius:6px;font-family:monospace;margin:10px 0;overflow:hidden;">
                                <span style="color:#0073aa;cursor:pointer;" onclick="copyToClipboard('[velocity-statistics show=&quot;today&quot;]')">[velocity-statistics show="today"]</span>
                                <button onclick="copyToClipboard('[velocity-statistics show=&quot;today&quot;]')" class="button button-secondary" style="float: right;background: #0073aa;color: white;border: none;padding: 1px 8px;border-radius: 4px;font-size: 11px;cursor: pointer;line-height: 13px;min-height: 20px;">Copy</button>
                            </div>
                            <div style="font-size:13px;color:#666;">Hanya hari ini</div>
                        </div>
                        <div>
                            <h6>Advanced</h6>
                            <div style="background:#f1f1f1;padding:12px;border-radius:6px;font-family:monospace;margin-bottom:10px;overflow:hidden;">
                                <span style="color:#0073aa;cursor:pointer;" onclick="copyToClipboard('[velocity-statistics with_online=&quot;0&quot;]')">[velocity-statistics with_online="0"]</span>
                                <button onclick="copyToClipboard('[velocity-statistics with_online=&quot;0&quot;]')" class="button button-secondary" style="float: right;background: #0073aa;color: white;border: none;padding: 1px 8px;border-radius: 4px;font-size: 11px;cursor: pointer;line-height: 13px;min-height: 20px;">Copy</button>
                            </div>
                            <div style="font-size:13px;color:#666;">Sembunyikan baris Pengunjung Online</div>
                            <div style="background:#f1f1f1;padding:12px;border-radius:6px;font-family:monospace;margin:10px 0;overflow:hidden;">
                                <span style="color:#0073aa;cursor:pointer;" onclick="copyToClipboard('[velocity-statistics label_today_visits=&quot;Traffic Hari Ini&quot; label_today_visitors=&quot;Visitor Hari Ini&quot; label_total_visits=&quot;Total Traffic&quot; label_total_visitors=&quot;Total Visitor&quot;]')">[velocity-statistics label_today_visits="Traffic Hari Ini" label_today_visitors="Visitor Hari Ini" label_total_visits="Total Traffic" label_total_visitors="Total Visitor"]</span>
                                <button onclick="copyToClipboard('[velocity-statistics label_today_visits=&quot;Traffic Hari Ini&quot; label_today_visitors=&quot;Visitor Hari Ini&quot; label_total_visits=&quot;Total Traffic&quot; label_total_visitors=&quot;Total Visitor&quot;]')" class="button button-secondary" style="float: right;background: #0073aa;color: white;border: none;padding: 1px 8px;border-radius: 4px;font-size: 11px;cursor: pointer;line-height: 13px;min-height: 20px;">Copy</button>
                            </div>
                            <div style="font-size:13px;color:#666;">Custom label counter</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="vd-section">
                <div class="vd-section-header" style="padding: 1.25rem 1.5rem; border-bottom: 1px solid #e5e7eb; background-color: #f9fafb;">
                    <h3 style="margin:0; font-size:1.1rem; color:#374151;">Shortcode: [velocity-hits]</h3>
                </div>
                <div class="vd-section-body">
                    <p>Tampilkan nilai meta hit pada posting.</p>
                    <ul class="vd-list">
                        <li><span class="vd-code">post_id</span>: ID posting (opsional)</li>
                        <li><span class="vd-code">format</span>: <span class="vd-code">number</span> atau <span class="vd-code">compact</span></li>
                        <li><span class="vd-code">before</span>/<span class="vd-code">after</span>: teks sebelum/sesudah angka</li>
                        <li><span class="vd-code">class</span>: CSS class untuk elemen angka</li>
                    </ul>
                    <div class="vd-grid-2">
                        <div>
                            <h6>Basic</h6>
                            <div style="background:#f1f1f1;padding:12px;border-radius:6px;font-family:monospace;margin-bottom:10px;overflow:hidden;">
                                <span style="color:#0073aa;cursor:pointer;" onclick="copyToClipboard('[velocity-hits]')">[velocity-hits]</span>
                                <button onclick="copyToClipboard('[velocity-hits]')" class="button button-secondary" style="float: right;background: #0073aa;color: white;border: none;padding: 1px 8px;border-radius: 4px;font-size: 11px;cursor: pointer;line-height: 13px;min-height: 20px;">Copy</button>
                            </div>
                            <div style="font-size:13px;color:#666;">Memakai get_the_ID()</div>
                        </div>
                        <div>
                            <h6>Advanced</h6>
                            <div style="background:#f1f1f1;padding:12px;border-radius:6px;font-family:monospace;margin-bottom:10px;overflow:hidden;">
                                <span style="color:#0073aa;cursor:pointer;" onclick="copyToClipboard('[velocity-hits post_id=&quot;123&quot; format=&quot;compact&quot; before=&quot;&quot; after=&quot; views&quot;]')">[velocity-hits post_id="123" format="compact" before="" after=" views"]</span>
                                <button onclick="copyToClipboard('[velocity-hits post_id=&quot;123&quot; format=&quot;compact&quot; before=&quot;&quot; after=&quot; views&quot;]')" class="button button-secondary" style="float: right;background: #0073aa;color: white;border: none;padding: 1px 8px;border-radius: 4px;font-size: 11px;cursor: pointer;line-height: 13px;min-height: 20px;">Copy</button>
                            </div>
                            <div style="font-size:13px;color:#666;">Pakai ID tertentu + format singkat + label</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="vd-grid-2">
                <div class="vd-section">
                    <div class="vd-section-header" style="padding: 1.25rem 1.5rem; border-bottom: 1px solid #e5e7eb; background-color: #f9fafb;">
                        <h3 style="margin:0; font-size:1.1rem; color:#374151;">Halaman Teratas (30 Hari)</h3>
                    </div>
                    <div class="vd-section-body">
                        <table class="widefat striped" style="margin-top:5px;">
                            <thead>
                                <tr>
                                    <th>Page URL</th>
                                    <th>Pengunjung Unik</th>
                                    <th>Total Tampilan</th>
                                </tr>
                            </thead>
                            <tbody id="velocity-statistics-pages-body">
                                <?php if (empty($page_stats)) : ?>
                                    <tr>
                                        <td colspan="3" style="text-align:center;color:#666;">No data available</td>
                                    </tr>
                                    <?php else: foreach ($page_stats as $page): ?>
                                        <tr>
                                            <td>
                                                <?php
                                                $full = home_url($page->page_url);
                                                echo '<a href="' . esc_url($full) . '" target="_blank" rel="noopener noreferrer"><code>' . esc_html($page->page_url) . '</code></a>';
                                                ?>
                                            </td>
                                            <td><?php echo number_format_i18n((int) $page->unique_visitors); ?></td>
                                            <td><?php echo number_format_i18n((int) $page->total_views); ?></td>
                                        </tr>
                                <?php endforeach;
                                endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="vd-section">
                    <div class="vd-section-header" style="padding: 1.25rem 1.5rem; border-bottom: 1px solid #e5e7eb; background-color: #f9fafb;">
                        <h3 style="margin:0; font-size:1.1rem; color:#374151;">Rujukan Teratas (30 Hari)</h3>
                    </div>
                    <div class="vd-section-body">
                        <table class="widefat striped" style="margin-top:5px;">
                            <thead>
                                <tr>
                                    <th>Referrer</th>
                                    <th>Visits</th>
                                </tr>
                            </thead>
                            <tbody id="velocity-statistics-referrers-body">
                                <?php if (empty($referer_stats)) : ?>
                                    <tr>
                                        <td colspan="2" style="text-align:center;color:#666;">No data available</td>
                                    </tr>
                                    <?php else: foreach ($referer_stats as $ref): ?>
                                        <tr>
                                            <td><code><?php echo esc_html(parse_url($ref->referer, PHP_URL_HOST) ?: $ref->referer); ?></code></td>
                                            <td><?php echo number_format_i18n((int) $ref->visits); ?></td>
                                        </tr>
                                <?php endforeach;
                                endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div class="vd-footer">
                <small>Powered by <a href="https://velocitydeveloper.com/" target="_blank">velocitydeveloper.com</a></small>
            </div>
        </div>
<?php
    }

    public function optimize_db_page_callback()
    {
        if (!current_user_can('manage_options')) return;
        Velocity_Addons_Optimasi::render_optimize_db_page();
    }
}

// Initialize the Pengaturan Admin page
$custom_admin_options_page = new Custom_Admin_Option_Page();
