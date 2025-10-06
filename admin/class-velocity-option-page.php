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
            'Pengaturan Admin',
            'Pengaturan Admin',
            'manage_options',
            'custom_admin_options',
            array($this, 'options_page_callback'),
        );

        add_submenu_page(
            'admin_velocity_addons',
            'Code Snippet',
            'Code Snippet',
            'manage_options',
            'velocity_snippet_settings',
            array($this, 'velocity_snippet_settings'),
        );

        $statistik_velocity = get_option('statistik_velocity', '0');
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
        register_setting('custom_admin_options_group', 'fully_disable_comment');
        register_setting('custom_admin_options_group', 'hide_admin_notice');
        register_setting('custom_admin_options_group', 'limit_login_attempts');
        register_setting('custom_admin_options_group', 'maintenance_mode');
        register_setting('custom_admin_options_group', 'maintenance_mode_data');
        register_setting('custom_admin_options_group', 'license_key');
        register_setting('custom_admin_options_group', 'auto_resize_mode');
        register_setting('custom_admin_options_group', 'auto_resize_mode_data');
        register_setting('custom_admin_options_group', 'disable_xmlrpc');
        register_setting('custom_admin_options_group', 'disable_rest_api');
        register_setting('custom_admin_options_group', 'disable_gutenberg');
        register_setting('custom_admin_options_group', 'block_wp_login');
        register_setting('custom_admin_options_group', 'whitelist_block_wp_login');
        register_setting('custom_admin_options_group', 'whitelist_country');
        register_setting('custom_admin_options_group', 'redirect_to');
        // register_setting('custom_admin_options_group', 'standar_editor_velocity');
        register_setting('custom_admin_options_group', 'classic_widget_velocity');
        register_setting('custom_admin_options_group', 'remove_slug_category_velocity');
        register_setting('custom_admin_options_group', 'seo_velocity');
        register_setting('custom_admin_options_group', 'statistik_velocity');
        register_setting('custom_admin_options_group', 'auto_resize_image_velocity');
        register_setting('custom_admin_options_group', 'captcha_velocity');
        register_setting('custom_admin_options_group', 'news_generate');
        register_setting('custom_admin_options_group', 'velocity_gallery');
        register_setting('custom_admin_options_group', 'velocity_duitku');
        register_setting('custom_admin_options_group', 'floating_whatsapp');
        register_setting('custom_admin_options_group', 'floating_scrollTop');
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
            'umum' => [
                'title'     => 'Umum',
                'fields'    => [
                    [
                        'id'    => 'fully_disable_comment',
                        'type'  => 'checkbox',
                        'title' => 'Disable Comment',
                        'std'   => 1,
                        'label' => 'Nonaktifkan fitur komentar pada situs.',
                    ],
                    [
                        'id'    => 'hide_admin_notice',
                        'type'  => 'checkbox',
                        'title' => 'Hide Admin Notice',
                        'std'   => 0,
                        'label' => 'Sembunyikan pemberitahuan admin di halaman admin. Pemberitahuan admin seringkali muncul untuk memberikan informasi atau peringatan kepada admin situs.',
                    ],
                    [
                        'id'    => 'disable_gutenberg',
                        'type'  => 'checkbox',
                        'title' => 'Disable Gutenberg',
                        'std'   => 0,
                        'label' => 'Aktifkan untuk menggunakan editor klasik WordPress menggantikan Gutenberg.',
                    ],
                    [
                        'id'    => 'classic_widget_velocity',
                        'type'  => 'checkbox',
                        'title' => 'Classic Widget',
                        'std'   => 1,
                        'label' => 'Aktifkan untuk menggunakan widget klasik.',
                    ],
                    [
                        'id'    => 'seo_velocity',
                        'type'  => 'checkbox',
                        'title' => 'SEO',
                        'std'   => 1,
                        'label' => 'Aktifkan gunakan SEO dari Velocity.',
                    ],
                    [
                        'id'    => 'statistik_velocity',
                        'type'  => 'checkbox',
                        'title' => 'Statistik Pengunjung',
                        'std'   => 1,
                        'label' => 'Aktifkan gunakan statistik pengunjung dari Velocity.',
                    ],
                    [
                        'id'    => 'floating_whatsapp',
                        'type'  => 'checkbox',
                        'title' => 'Floating Whatsapp',
                        'std'   => 1,
                        'label' => 'Aktifkan gunakan untuk Whatsapp Floating pada halaman utama.',
                    ],
                    [
                        'id'    => 'floating_scrollTop',
                        'type'  => 'checkbox',
                        'title' => 'Floating Scrolltop',
                        'std'   => 1,
                        'label' => 'Aktifkan untuk menambahkan scrollTop ke halaman utama.',
                    ],
                    [
                        'id'    => 'remove_slug_category_velocity',
                        'type'  => 'checkbox',
                        'title' => 'Remove Slug Category',
                        'std'   => 0,
                        'label' => 'Aktifkan untuk hapus slug /category/ dari URL.',
                    ],
                    [
                        'id'    => 'news_generate',
                        'type'  => 'checkbox',
                        'title' => 'Import Artikel dari API',
                        'std'   => 1,
                        'label' => 'Aktifkan gunakan untuk import artikel post.',
                    ],
                    [
                        'id'    => 'velocity_gallery',
                        'type'  => 'checkbox',
                        'title' => 'Gallery Post Type',
                        'std'   => 0,
                        'label' => 'Aktifkan untuk gunakan Gallery Post Type.',
                    ],
                    [
                        'id'    => 'velocity_duitku',
                        'type'  => 'checkbox',
                        'title' => 'Payment Gateway Duitku',
                        'std'   => 0,
                        'label' => 'Aktifkan untuk gunakan payment gateway Duitku.',
                    ],
                ],
            ],
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
                        'id'    => 'disable_rest_api',
                        'type'  => 'checkbox',
                        'title' => 'Disable REST API / JSON',
                        'std'   => 0,
                        'label' => 'Nonaktifkan akses ke REST API untuk keperluan keamanan atau privasi.',
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
                    ]
                ],
            ],
        ];
?>
        <div class="wrap vd-ons">
            <h1>Pengaturan Admin</h1>

            <form method="post" action="options.php">
                <?php settings_fields('custom_admin_options_group'); ?>
                <?php do_settings_sections('custom_admin_options_group'); ?>

                <div class="nav-tab-wrapper">
                    <?php foreach ($pages as $tab => $tabs) : ?>
                        <a href="#<?php echo $tab; ?>" class="nav-tab">
                            <?php echo $tabs['title']; ?>
                        </a>
                    <?php endforeach; ?>
                </div>

                <div class="tab-content">
                    <?php foreach ($pages as $tab => $tabs) : ?>
                        <div id="<?php echo $tab; ?>" class="content">
                            <table class="form-table">
                                <?php
                                foreach ($tabs['fields'] as $ky => $data) :
                                    echo '<tr>';
                                    echo '<th scope="row">';
                                    echo $data['title'];
                                    echo '</th>';
                                    echo '<td>';
                                    $this->field($data);
                                    echo '</td>';
                                    echo '</tr>';
                                endforeach;
                                ?>
                            </table>
                            <hr>
                        </div>
                    <?php endforeach; ?>
                </div>

                <div style="float:right;">
                    <?php submit_button(); ?>
                </div>

                <script>
                    jQuery(document).ready(function($) {
                        $('.check-license').click(function(e) {
                            e.preventDefault();

                            var licenseKey = $('#velocity_license__key').val();

                            // Check if license key is not empty
                            if (licenseKey === '') {
                                alert('Please enter a license key.');
                                return;
                            }

                            $('.check-license.button').html('Loading..');

                            $.ajax({
                                url: '<?php echo admin_url('admin-ajax.php'); ?>',
                                type: 'POST',
                                data: {
                                    action: 'check_license',
                                    license_key: licenseKey
                                },
                                success: function(response) {
                                    if (response.success) {} else {
                                        $('.license-status').html(response.data);
                                        $('#velocity_license__key').val('');
                                    }
                                    $('.check-license.button').html('License Verified!');
                                },
                                error: function() {
                                    $('.license-status').html('Server not reachable');
                                    $('#velocity_license__key').val('');
                                    $('.check-license.button').html('Check License');
                                }
                            });
                        });

                        function activeTab(id) {
                            $('.vd-ons .nav-tab').removeClass('nav-tab-active');
                            $('.vd-ons .nav-tab[href="' + id + '"]').addClass('nav-tab-active');
                            $('.vd-ons .tab-content .content').hide();
                            $('.vd-ons .tab-content ' + id).show();
                        }
                        $('.vd-ons .nav-tab').on('click', function(event) {
                            activeTab($(this).attr('href'));
                            localStorage.setItem('vdons-tabs', $(this).attr('href'));
                            event.preventDefault();
                        });
                        var act = localStorage.getItem('vdons-tabs');
                        act = act ? act : '#umum';
                        activeTab(act);

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
                <style>
                    .vd-media-field{display:flex;flex-direction:column;gap:10px;max-width:320px;}
                    .vd-media-field .vd-media-preview{border:1px dashed #c3c4c7;min-height:120px;display:flex;align-items:center;justify-content:center;background:#f6f7f7;border-radius:4px;overflow:hidden;padding:12px;}
                    .vd-media-field .vd-media-preview img{width:100%;height:auto;display:block;border-radius:4px;}
                    .vd-media-field .vd-media-placeholder{color:#6c7781;font-style:italic;text-align:center;}
                    .vd-media-field .vd-media-actions{display:flex;gap:8px;flex-wrap:wrap;}
                </style>


            </form>
        </div>
<?php
    }


    public function visitor_stats_page_callback() {
        if ( ! current_user_can('manage_options') ) {
            wp_die(__('You do not have sufficient permissions to access this page.'));
        }

        // Gunakan satu instance per request (hindari double hook)
        static $stats_handler = null;
        if ( ! $stats_handler ) {
            $stats_handler = new Velocity_Addons_Statistic();
        }

        // Handle rebuild request (POST + nonce)
        $rebuild_message = '';
        if ( isset($_POST['rebuild_stats']) && check_admin_referer('rebuild_stats') ) {
            $daily_count = (int) $stats_handler->rebuild_daily_stats();
            $page_count  = (int) $stats_handler->rebuild_page_stats();
            $rebuild_message = "<div class='notice notice-success is-dismissible'><p>✅ Statistik berhasil dibangun ulang! Memproses {$daily_count} data harian dan {$page_count} data halaman.</p></div>";
        }

        // Ambil data
        $summary_stats = $stats_handler->get_summary_stats();
        $daily_stats   = $stats_handler->get_daily_stats(30);
        $page_stats    = $stats_handler->get_page_stats(30);
        $referer_stats = $stats_handler->get_referer_stats(30);

        // Siapkan data untuk Chart.js (gunakan wp_json_encode)
        $daily_payload = array_map(function($stat){
            return array(
                'date'          => $stat->visit_date,
                'unique_visits' => (int) $stat->unique_visits,
                'total_visits'  => (int) $stat->total_visits,
            );
        }, $daily_stats);

        $page_payload = array_map(function($p){
            return array(
                'url'   => $p->page_url,
                'views' => (int) $p->total_views,
            );
        }, array_slice($page_stats, 0, 8));

        ?>
        <div class="wrap vd-ons">
            <h1>📊 Statistik Pengunjung</h1>

            <?php echo $rebuild_message; ?>

            <div style="margin: 20px 0;">
                <form method="post" style="display:inline;">
                    <?php wp_nonce_field('rebuild_stats'); ?>
                    <input type="hidden" name="rebuild_stats" value="1">
                    <button type="submit" class="button button-secondary"
                        onclick="return confirm('Apakah Anda yakin ingin membangun ulang statistik? Ini akan menghitung ulang semua data dari log yang ada.')">
                        🔄 Bangun Ulang Statistik
                    </button>
                    <span style="margin-left:10px;color:#666;font-size:13px;">Use this if visitor counts appear incorrect</span>
                </form>
            </div>

            <!-- Summary Cards -->
            <div class="stats-summary" style="display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:20px;margin:20px 0;">
                <?php
                $cards = array(
                    'Hari Ini'  => $summary_stats['today'],
                    'Minggu Ini'=> $summary_stats['this_week'],
                    'Bulan Ini' => $summary_stats['this_month'],
                    'All Time'  => $summary_stats['all_time'],
                );
                foreach ($cards as $label => $obj): ?>
                    <div class="stat-card" style="background:#fff;padding:20px;border:1px solid #ddd;border-radius:8px;text-align:center;box-shadow:0 2px 4px rgba(0,0,0,0.1);">
                        <h3 style="margin:0 0 10px;color:#0073aa;"><?php echo esc_html($label); ?></h3>
                        <div style="font-size:24px;font-weight:700;color:#23282d;"><?php echo number_format_i18n((int)($obj->unique_visitors ?? 0)); ?></div>
                        <div style="color:#666;font-size:14px;">Pengunjung Unik</div>
                        <div style="color:#999;font-size:12px;"><?php echo number_format_i18n((int)($obj->total_visits ?? 0)); ?> total visits</div>
                    </div>
                <?php endforeach; ?>
            </div>

            <!-- Charts -->
            <div class="charts-section" style="display:grid;grid-template-columns:1fr 1fr;gap:20px;margin:20px 0;">
                <div class="chart-container" style="background:#fff;padding:20px;border:1px solid #ddd;border-radius:8px;box-shadow:0 2px 4px rgba(0,0,0,0.1);">
                    <h3 style="margin-top:0;color:#23282d;">📈 Daily Visits (Last 30 Days)</h3>
                    <canvas id="dailyVisitsChart" width="400" height="200"></canvas>
                </div>
                <div class="chart-container" style="background:#fff;padding:20px;border:1px solid #ddd;border-radius:8px;box-shadow:0 2px 4px rgba(0,0,0,0.1);">
                    <h3 style="margin-top:0;color:#23282d;">📄 Halaman Teratas</h3>
                    <canvas id="topPagesChart" width="400" height="200"></canvas>
                </div>
            </div>

            <!-- Shortcode block (STATISTICS) -->
            <div class="shortcode-section" style="background:#fff;padding:30px;border:1px solid #ddd;border-radius:8px;box-shadow:0 2px 4px rgba(0,0,0,0.1);margin:20px 0;">
                <h3 style="margin-top:0;color:#23282d;">📋 Shortcode Usage — [velocity-statistics]</h3>
                <p style="color:#666;margin-bottom:25px;">Tampilkan statistik visitor di halaman, post, atau widget.</p>
                <ul style="margin:0 0 20px 18px; color:#444; line-height:1.6;">
                    <li><code>style</code> — pilih tampilan statistik. <code>list</code> atau <code>inline</code> (default <code>list</code>)</li>
                    <li><code>show</code> — filter data yang ditampilkan. <code>all</code>, <code>today</code>, atau <code>total</code> (default <code>all</code>)</li>
                    <li><code>label_today_visits</code> — ganti label "Kunjungan Hari Ini" (opsional)</li>
                     <li><code>label_today_visitors</code> — ganti label "Pengunjung Hari Ini" (opsional)</li>
                    <li><code>label_total_visits</code> — ganti label "Total Kunjungan" (opsional)</li>
                    <li><code>label_total_visitors</code> — ganti label "Total Pengunjung" (opsional)</li>
                </ul>

                <div class="shortcode-examples" style="display:grid;grid-template-columns:1fr 1fr;gap:30px;">
                    <div>
                        <h4 style="color:#23282d;margin-bottom:15px;">🎯 Basic</h4>
                        <div style="margin-bottom:20px;">
                            <div style="background:#f1f1f1;padding:12px;border-radius:6px;font-family:monospace;margin-bottom:10px;overflow:hidden;">
                                <span style="color:#0073aa;cursor:pointer;" onclick="copyToClipboard('[velocity-statistics]')">[velocity-statistics]</span>
                                <button onclick="copyToClipboard('[velocity-statistics]')" class="button button-secondary" style="float: right;background: #0073aa;color: white;border: none;padding: 1px 8px;border-radius: 4px;font-size: 11px;cursor: pointer;line-height: 13px;min-height: 20px;">Copy</button>
                            </div>
                            <div style="font-size:13px;color:#666;">Semua statistik (default)</div>
                        </div>
                        <div style="margin-bottom:20px;">
                            <div style="background:#f1f1f1;padding:12px;border-radius:6px;font-family:monospace;margin-bottom:10px;overflow:hidden;">
                                <span style="color:#0073aa;cursor:pointer;" onclick="copyToClipboard('[velocity-statistics show=&quot;today&quot;]')">[velocity-statistics show="today"]</span>
                                <button onclick="copyToClipboard('[velocity-statistics show=&quot;today&quot;]')" class="button button-secondary" style="float: right;background: #0073aa;color: white;border: none;padding: 1px 8px;border-radius: 4px;font-size: 11px;cursor: pointer;line-height: 13px;min-height: 20px;">Copy</button>
                            </div>
                            <div style="font-size:13px;color:#666;">Hanya hari ini</div>
                        </div>
                    </div>
                    <div>
                        <h4 style="color:#23282d;margin-bottom:15px;">⚙️ Advanced</h4>
                        <div style="margin-bottom:20px;">
                            <div style="background:#f1f1f1;padding:12px;border-radius:6px;font-family:monospace;margin-bottom:10px;overflow:hidden;">
                                <span style="color:#0073aa;cursor:pointer;" onclick="copyToClipboard('[velocity-statistics style=&quot;list&quot;]')">[velocity-statistics style="list"]</span>
                                <button onclick="copyToClipboard('[velocity-statistics style=&quot;list&quot; ]')" class="button button-secondary" style="float: right;background: #0073aa;color: white;border: none;padding: 1px 8px;border-radius: 4px;font-size: 11px;cursor: pointer;line-height: 13px;min-height: 20px;">Copy</button>
                            </div>
                            <div style="font-size:13px;color:#666;">Style: <code>list</code> atau <code>inline</code></div>
                        </div>
                        <div style="margin-bottom:20px;">
                            <div style="background:#f1f1f1;padding:12px;border-radius:6px;font-family:monospace;margin-bottom:10px;overflow:hidden;">
                                <span style="color:#0073aa;cursor:pointer;" onclick="copyToClipboard('[velocity-statistics label_today_visits=&quot;Traffic Hari Ini&quot; label_today_visitors=&quot;Visitor Hari Ini&quot; label_total_visits=&quot;Total Traffic&quot; label_total_visitors=&quot;Total Visitor&quot;]')">[velocity-statistics label_today_visits="Traffic Hari Ini" label_today_visitors="Visitor Hari Ini" label_total_visits="Total Traffic" label_total_visitors="Total Visitor"]</span>
                                <button onclick="copyToClipboard('[velocity-statistics label_today_visits=&quot;Traffic Hari Ini&quot; label_today_visitors=&quot;Visitor Hari Ini&quot; label_total_visits=&quot;Total Traffic&quot; label_total_visitors=&quot;Total Visitor&quot;]')" class="button button-secondary" style="float: right;background: #0073aa;color: white;border: none;padding: 1px 8px;border-radius: 4px;font-size: 11px;cursor: pointer;line-height: 13px;min-height: 20px;">Copy</button>
                            </div>
                            <div style="font-size:13px;color:#666;">Custom label counter (gunakan atribut <code>label_today_visits</code>, <code>label_today_visitors</code>, <code>label_total_visits</code>, <code>label_total_visitors</code>)</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Shortcode block (HITS) -->
            <div class="shortcode-section" style="background:#fff;padding:30px;border:1px solid #ddd;border-radius:8px;box-shadow:0 2px 4px rgba(0,0,0,0.1);margin:20px 0;">
                <h3 style="margin-top:0;color:#23282d;">👁️ Shortcode Menampilkan Hit — [velocity-hits]</h3>
                <p style="color:#666;margin-bottom:20px;">
                    Gunakan shortcode ini untuk menampilkan nilai meta <code>hit</code> pada posting. Default-nya
                    akan memakai <code>get_the_ID()</code> (jadi ideal ditaruh pada Single Post). Atribut yang tersedia:
                </p>
                <ul style="margin:0 0 20px 18px; color:#444; line-height:1.6;">
                    <li><code>post_id</code> — ID posting (opsional; default <code>get_the_ID()</code>)</li>
                    <li><code>format</code> — <code>number</code> atau <code>compact</code> (misal 1.2K, 3.4M)</li>
                    <li><code>before</code> — HTML/text di depan angka</li>
                    <li><code>after</code> — HTML/text di belakang angka</li>
                    <li><code>class</code> — CSS class untuk wrapper <code>&lt;span&gt;</code></li>
                </ul>

                <div class="shortcode-examples" style="display:grid;grid-template-columns:1fr 1fr;gap:30px;">
                    <div>
                        <h4 style="color:#23282d;margin-bottom:15px;">🎯 Basic (di Single Post)</h4>
                        <div style="background:#f1f1f1;padding:12px;border-radius:6px;font-family:monospace;margin-bottom:10px;overflow:hidden;">
                            <span style="color:#0073aa;cursor:pointer;" onclick="copyToClipboard('[velocity-hits]')">[velocity-hits]</span>
                            <button onclick="copyToClipboard('[velocity-hits]')" class="button button-secondary" style="float: right;background: #0073aa;color: white;border: none;padding: 1px 8px;border-radius: 4px;font-size: 11px;cursor: pointer;line-height: 13px;min-height: 20px;">
                                Copy
                            </button>
                        </div>
                        <div style="font-size:13px;color:#666;">Memakai <code>get_the_ID()</code> sebagai target.</div>
                    </div>
                    <div>
                        <h4 style="color:#23282d;margin-bottom:15px;">⚙️ Advanced</h4>
                        <div style="background:#f1f1f1;padding:12px;border-radius:6px;font-family:monospace;margin-bottom:10px;overflow:hidden;">
                            <span style="color:#0073aa;cursor:pointer;" onclick="copyToClipboard('[velocity-hits post_id=&quot;123&quot; format=&quot;compact&quot; before=&quot;&quot; after=&quot; views&quot;]')">
                                [velocity-hits post_id="123" format="compact" before="" after=" views"]
                            </span>
                            <button onclick="copyToClipboard('[velocity-hits post_id=&quot;123&quot; format=&quot;compact&quot; before=&quot;&quot; after=&quot; views&quot;]')" class="button button-secondary" style="float: right;background: #0073aa;color: white;border: none;padding: 1px 8px;border-radius: 4px;font-size: 11px;cursor: pointer;line-height: 13px;min-height: 20px;">
                                Copy
                            </button>
                        </div>
                        <div style="font-size:13px;color:#666;">Pakai ID tertentu + format singkat + label.</div>
                    </div>
                </div>
            </div>

            <!-- Data Tables -->
            <div class="tables-section" style="display:grid;grid-template-columns:1fr 1fr;gap:20px;margin:20px 0;">
                <div class="table-container" style="background:#fff;padding:20px;border:1px solid #ddd;border-radius:8px;box-shadow:0 2px 4px rgba(0,0,0,0.1);">
                    <h3 style="margin-top:0;color:#23282d;">🏆 Halaman Teratas (30 Hari Terakhir)</h3>
                    <table class="widefat striped" style="margin-top:15px;">
                        <thead>
                            <tr><th>Page URL</th><th>Pengunjung Unik</th><th>Total Tampilan</th></tr>
                        </thead>
                        <tbody>
                        <?php if (empty($page_stats)) : ?>
                            <tr><td colspan="3" style="text-align:center;color:#666;">No data available</td></tr>
                        <?php else: foreach ($page_stats as $page): ?>
                            <tr>
                                <td>
                                    <?php
                                    $full = home_url($page->page_url);
                                    echo '<a href="'.esc_url($full).'" target="_blank" rel="noopener noreferrer"><code>'.esc_html($page->page_url).'</code></a>';
                                    ?>
                                </td>
                                <td><?php echo number_format_i18n((int)$page->unique_visitors); ?></td>
                                <td><?php echo number_format_i18n((int)$page->total_views); ?></td>
                            </tr>
                        <?php endforeach; endif; ?>
                        </tbody>
                    </table>
                </div>

                <div class="table-container" style="background:#fff;padding:20px;border:1px solid #ddd;border-radius:8px;box-shadow:0 2px 4px rgba(0,0,0,0.1);">
                    <h3 style="margin-top:0;color:#23282d;">🔗 Rujukan Teratas (30 Hari Terakhir)</h3>
                    <table class="widefat striped" style="margin-top:15px;">
                        <thead>
                            <tr><th>Referrer</th><th>Visits</th></tr>
                        </thead>
                        <tbody>
                        <?php if (empty($referer_stats)) : ?>
                            <tr><td colspan="2" style="text-align:center;color:#666;">No data available</td></tr>
                        <?php else: foreach ($referer_stats as $ref): ?>
                            <tr>
                                <td><code><?php echo esc_html(parse_url($ref->referer, PHP_URL_HOST) ?: $ref->referer); ?></code></td>
                                <td><?php echo number_format_i18n((int)$ref->visits); ?></td>
                            </tr>
                        <?php endforeach; endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Chart.js -->
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
        <script>
        (function(){
            const dailyData = <?php echo wp_json_encode($daily_payload); ?>;
            const pageData  = <?php echo wp_json_encode($page_payload); ?>;

            const dailyLabels = dailyData.map(i => i.date);
            const uniqueVisitsData = dailyData.map(i => i.unique_visits);
            const totalVisitsData  = dailyData.map(i => i.total_visits);

            const dailyCtx = document.getElementById('dailyVisitsChart').getContext('2d');
            new Chart(dailyCtx, {
                type: 'line',
                data: {
                    labels: dailyLabels,
                    datasets: [
                        { label: 'Pengunjung Unik', data: uniqueVisitsData, borderColor: '#0073aa', backgroundColor: 'rgba(0,115,170,0.1)', tension: .4, fill: true },
                        { label: 'Total Kunjungan', data: totalVisitsData,  borderColor: '#00a32a', backgroundColor: 'rgba(0,163,42,0.1)', tension: .4, fill: false }
                    ]
                },
                options: { responsive:true, maintainAspectRatio:false, scales:{ y:{ beginAtZero:true } }, plugins:{ legend:{ position:'top' } } }
            });

            const pageLabels = pageData.map(i => i.url);
            const pageViews  = pageData.map(i => i.views);

            const pageCtx = document.getElementById('topPagesChart').getContext('2d');
            new Chart(pageCtx, {
                type: 'bar',
                data: { labels: pageLabels, datasets: [{ label:'Page Views', data: pageViews, backgroundColor:['#0073aa','#00a32a','#d63638','#ff922b','#7c3aed','#db2777','#059669','#dc2626'] }] },
                options: {
                    responsive:true, maintainAspectRatio:false,
                    scales:{ y:{ beginAtZero:true }, x:{ ticks:{ maxRotation:45, callback:(v,i)=>{ const l=pageLabels[i]||''; return l.length>20?l.substring(0,20)+'…':l; } } } },
                    plugins:{ legend:{ display:false } }
                }
            });

            window.copyToClipboard = function(text){
                if (navigator.clipboard && window.isSecureContext) {
                    navigator.clipboard.writeText(text).then(showCopySuccess);
                } else {
                    const ta = document.createElement('textarea');
                    ta.value = text; ta.style.position='fixed'; ta.style.left='-9999px';
                    document.body.appendChild(ta); ta.select();
                    try { document.execCommand('copy'); showCopySuccess(); } catch(e){}
                    document.body.removeChild(ta);
                }
            };
            function showCopySuccess(){
                const el=document.createElement('div');
                el.style.cssText='position:fixed;top:50px;right:20px;background:#00a32a;color:#fff;padding:12px 20px;border-radius:6px;font-size:14px;z-index:9999;box-shadow:0 4px 12px rgba(0,0,0,.2);transition:all .3s';
                el.textContent='✅ Shortcode copied to clipboard!';
                document.body.appendChild(el);
                setTimeout(()=>{el.style.opacity='0';el.style.transform='translateY(-20px)';setTimeout(()=>document.body.removeChild(el),300)},2000);
            }
        })();
        </script>

        <style>
        @media (max-width: 768px){
            .stats-summary,.charts-section,.tables-section,.shortcode-examples{grid-template-columns:1fr!important}
        }
        .chart-container canvas{height:200px!important}
        .table-container table{font-size:14px}
        .table-container code{background:#f1f1f1;padding:2px 6px;border-radius:4px;font-size:12px}
        </style>
        <?php
    }



}

// Initialize the Pengaturan Admin page
$custom_admin_options_page = new Custom_Admin_Option_Page();

