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

 class Custom_Admin_Option_Page {
    public function __construct() {
        add_action('admin_menu', array($this, 'add_options_page'));
        add_action('admin_init', array($this, 'register_settings'));
    }
  
    public function add_options_page() {
        add_options_page('Pengaturan Admin', 'Pengaturan Admin', 'manage_options', 'custom_admin_options', array($this, 'options_page_callback'));
    }
  
    public function register_settings() {
        register_setting('custom_admin_options_group', 'fully_disable_comment');
        register_setting('custom_admin_options_group', 'hide_admin_notice');
        register_setting('custom_admin_options_group', 'limit_login_attempts');
        register_setting('custom_admin_options_group', 'maintenance_mode');
        register_setting('custom_admin_options_group', 'disable_xmlrpc');
        register_setting('custom_admin_options_group', 'disable_rest_api');
        register_setting('custom_admin_options_group', 'disable_gutenberg');
        register_setting('custom_admin_options_group', 'block_wp_login');
        register_setting('custom_admin_options_group', 'whitelist_country');
        register_setting('custom_admin_options_group', 'redirect_to');
    }
  
    public function options_page_callback() {
        ?>
        <div class="wrap">
            <h1>Pengaturan Admin</h1>
            <form method="post" action="options.php">
                <?php settings_fields('custom_admin_options_group'); ?>
                <?php do_settings_sections('custom_admin_options_group'); ?>
                <table class="form-table">
                    <tr>
                        <th scope="row">Disable Comment</th>
                        <td>
                            <input type="checkbox" id="fully_disable_comment" name="fully_disable_comment" value="1" <?php checked(get_option('fully_disable_comment','1'), 1); ?>>
                            <label for="fully_disable_comment">
                                <small>
                                    Opsi ini memungkinkan Anda untuk sepenuhnya menonaktifkan fitur komentar pada situs WordPress Anda. Ketika opsi ini diaktifkan, pengguna tidak akan dapat meninggalkan komentar di halaman atau postingan.
                                </small>
                            </label>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">Hide Admin Notice</th>
                        <td>
                            <input type="checkbox" id="hide_admin_notice" name="hide_admin_notice" value="1" <?php checked(get_option('hide_admin_notice','1'), 1); ?>>
                            <label for="hide_admin_notice">
                                <small>
                                Dengan opsi ini, Anda dapat menyembunyikan pemberitahuan admin di halaman admin WordPress. Pemberitahuan admin seringkali muncul untuk memberikan informasi atau peringatan kepada admin situs, dan dengan mengaktifkan opsi ini, Anda dapat menghilangkannya untuk mengurangi gangguan visual.
                                </small>
                            </label>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">Limit Login Attempts</th>
                        <td>
                            <input type="checkbox" id="limit_login_attempts" name="limit_login_attempts" value="1" <?php checked(get_option('limit_login_attempts','1'), 1); ?>>
                            <label for="limit_login_attempts">
                                <small>
                                Opsi ini memungkinkan Anda untuk membatasi jumlah percobaan login yang diizinkan untuk pengguna. Jika opsi ini diaktifkan, ketika pengguna melakukan percobaan login yang melebihi 5X dalam 24 Jam, mereka akan diblokir untuk sementara waktu sebagai tindakan keamanan.
                                </small>
                            </label>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">Maintenance Mode</th>
                        <td>
                            <input type="checkbox" id="maintenance_mode" name="maintenance_mode" value="1" <?php checked(get_option('maintenance_mode'), 1); ?>>
                            <label for="maintenance_mode">
                                <small>
                                Dengan opsi ini, Anda dapat mengaktifkan mode perawatan pada situs WordPress Anda. Saat mode perawatan diaktifkan, pengunjung situs akan melihat halaman pemberitahuan perawatan yang menunjukkan bahwa situs sedang dalam perbaikan atau tidak tersedia sementara waktu.
                                </small>
                            </label>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">Disable XML-RPC</th>
                        <td>
                            <input type="checkbox" id="disable_xmlrpc" name="disable_xmlrpc" value="1" <?php checked(get_option('disable_xmlrpc','1'), 1); ?>>
                            <label for="disable_xmlrpc">
                                <small>
                                Opsi ini memungkinkan Anda untuk menonaktifkan protokol XML-RPC pada situs WordPress. XML-RPC digunakan oleh beberapa aplikasi atau layanan pihak ketiga untuk berinteraksi dengan situs WordPress. Dengan mengaktifkan opsi ini, Anda dapat membatasi akses melalui XML-RPC untuk alasan keamanan.
                                </small>
                            </label>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">Disable REST API / JSON</th>
                        <td>
                            <input type="checkbox" id="disable_rest_api" name="disable_rest_api" value="1" <?php checked(get_option('disable_rest_api','1'), 1); ?>>
                            <label for="disable_rest_api">
                                <small>
                                Dengan opsi ini, Anda dapat menonaktifkan antarmuka pemrograman aplikasi (API) REST pada situs WordPress Anda. REST API memungkinkan aplikasi atau layanan eksternal untuk berkomunikasi dengan situs WordPress. Dengan mengaktifkan opsi ini, Anda dapat membatasi akses ke REST API untuk keperluan keamanan atau privasi.
                                </small>
                            </label>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">Disable Gutenberg</th>
                        <td>
                            <input type="checkbox" id="disable_gutenberg" name="disable_gutenberg" value="1" <?php checked(get_option('disable_gutenberg','1'), 1); ?>>
                            <label for="disable_gutenberg">
                                <small>
                                Opsi ini memungkinkan Anda untuk menonaktifkan editor blok Gutenberg pada situs WordPress. Jika opsi ini diaktifkan, Anda akan kembali menggunakan editor klasik WordPress. Gutenberg adalah editor konten visual yang diperkenalkan dalam versi WordPress 5.0.
                                </small>
                            </label>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">Block wp-login.php</th>
                        <td>
                            <input type="checkbox" id="block_wp_login" name="block_wp_login" value="1" <?php checked(get_option('block_wp_login','1'), 1); ?>>
                            <label for="block_wp_login">
                                <small>
                                "Block wp-login.php" adalah opsi yang memungkinkan Anda untuk mengaktifkan pemblokiran akses ke file wp-login.php pada situs WordPress.
                                </small>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">Whitelist Country</th>
                        <td>
                            <input type="text" id="whitelist_country" name="whitelist_country" value="<?php echo esc_attr(get_option('whitelist_country')); ?>">
                            <label for="whitelist_country">
                                <small>
                                Whitelist Country adalah opsi yang memungkinkan Anda membatasi akses ke situs WordPress hanya untuk negara-negara tertentu dengan menggunakan ID negara sebagai pemisah, seperti contoh ID,MY,US.
                                </small>
                            </label>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">Redirect To</th>
                        <td>
                            <input type="text" id="redirect_to" name="redirect_to" value="<?php echo esc_attr(get_option('redirect_to','http://127.0.0.1')); ?>">
                            <label for="redirect_to">
                                <small>
                                Fungsi ini hanya berjalan jika Block wp-login.php aktif. BVerfungsi untuk redirect wp-login.php
                                </small>
                            </label>
                        </td>
                    </tr>
                </table>
                <?php submit_button(); ?>
            </form>
        </div>
        <?php
    }
}

// Initialize the Pengaturan Admin page
$custom_admin_options_page = new Custom_Admin_Option_Page();