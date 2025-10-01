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
                    });
                </script>


            </form>
        </div>
<?php
    }
}

// Initialize the Pengaturan Admin page
$custom_admin_options_page = new Custom_Admin_Option_Page();
