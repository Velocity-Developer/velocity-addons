<?php
/**
 * Register Floating Whatsapp in the WordPress admin panel
 *
 * @link       https://velocitydeveloper.com
 * @since      1.0.0
 *
 * @package    Velocity_Addons
 * @subpackage Velocity_Addons/includes
 */

 class Velocity_Addons_Floating_Whatsapp
 {
    public function __construct()
    {
        $floating_whatsapp = get_option('floating_whatsapp','1');
        if($floating_whatsapp !== '1')
        return false;
    
        // Menambahkan submenu
        add_action('admin_init', [$this, 'register_wafloat_settings']);
    }
 
    public function register_wafloat_settings()
    {
        register_setting('velocity_floating_whatsapp_group', 'nomor_whatsapp');
        register_setting('velocity_floating_whatsapp_group', 'whatsapp_message');
        register_setting('velocity_floating_whatsapp_group', 'whatsapp_text');
        register_setting('velocity_floating_whatsapp_group', 'whatsapp_position');
    }

    public static function floating_whatsapp_page()
    {
        ?>
        <div class="wrap">
            <h2>Whatsapp Settings</h2>
            <form method="post" action="options.php">
                <?php settings_fields('velocity_floating_whatsapp_group'); ?>
                <?php do_settings_sections('velocity_floating_whatsapp_group'); ?>
                <table class="form-table">
                    <tr valign="top">
                        <th scope="row">Nomor Whatsapp</th>
                        <td>
                        <input class="regular-text" type="text" name="nomor_whatsapp" value="<?php echo esc_attr(get_option('nomor_whatsapp', '')); ?>" placeholder="08xxx" /><br/>
                        <small for="nomor_whatsapp">Bisa diawali 62 atau 08</small>
                    </td>
                    <tr valign="top">
                        <th scope="row">Text Whatsapp</th>
                        <td>
                        <input class="regular-text" type="text" name="whatsapp_text" value="<?php echo esc_attr(get_option('whatsapp_text', 'Butuh Bantuan?')); ?>" /><br/>
                    </td>
                    </tr>
                    <tr valign="top">
                        <th scope="row">Pesan Whatsapp</th>
                        <td><textarea class="large-text" name="whatsapp_message" rows="4" cols="40"><?php echo esc_textarea(get_option('whatsapp_message', 'Hallo...')); ?></textarea></td>
                    </tr>
                    <tr valign="top">
                        <th scope="row">Whatsapp Position</th>
                        <td>
                            <select name="whatsapp_position">
                                <option value="right" <?php selected(get_option('whatsapp_position'), 'right'); ?>>Right</option>
                                <option value="left" <?php selected(get_option('whatsapp_position'), 'left'); ?>>Left</option>
                            </select>
                        </td>
                    </tr>
                </table>
                <?php submit_button(); ?>
            </form>
        </div>
        <?php
    }

    public static function justg_footer_whatsapp()
    {
        $whatsapp_number        = get_option('nomor_whatsapp', '');
        $whatsapp_text          = get_option('whatsapp_text', 'Butuh Bantuan?');
        $whatsapp_message       = get_option('whatsapp_message', 'Halo..');
        $whatsapp_position      = get_option('whatsapp_position', 'right');
        $scroll_to_top_enable   = 'scroll-active scroll-' . $whatsapp_position;

        // replace all except numbers
        $whatsapp_number        = $whatsapp_number ? preg_replace('/[^0-9]/', '', $whatsapp_number) : $whatsapp_number;
        // replace 0 with 62 if first digit is 0
        if (substr($whatsapp_number, 0, 1) == 0) {
            $whatsapp_number    = substr_replace($whatsapp_number, '62', 0, 1);
        }
        $floating_whatsapp = get_option('floating_whatsapp','1');
        if($floating_whatsapp == '1'){
        ?>
            <div class="whatsapp-floating floating-button <?php echo $whatsapp_position . ' ' . $scroll_to_top_enable; ?> ">
                <a href="https://wa.me/<?php echo $whatsapp_number; ?>?text=<?php echo $whatsapp_message; ?>" class="text-white d-flex align-items-center justify-content-center" title="Whatsapp" target="_blank">
                    <span class="pt-1">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" class="bi bi-whatsapp" viewBox="0 0 16 16">
                            <path d="M13.601 2.326A7.854 7.854 0 0 0 7.994 0C3.627 0 .068 3.558.064 7.926c0 1.399.366 2.76 1.057 3.965L0 16l4.204-1.102a7.933 7.933 0 0 0 3.79.965h.004c4.368 0 7.926-3.558 7.93-7.93A7.898 7.898 0 0 0 13.6 2.326zM7.994 14.521a6.573 6.573 0 0 1-3.356-.92l-.24-.144-2.494.654.666-2.433-.156-.251a6.56 6.56 0 0 1-1.007-3.505c0-3.626 2.957-6.584 6.591-6.584a6.56 6.56 0 0 1 4.66 1.931 6.557 6.557 0 0 1 1.928 4.66c-.004 3.639-2.961 6.592-6.592 6.592zm3.615-4.934c-.197-.099-1.17-.578-1.353-.646-.182-.065-.315-.099-.445.099-.133.197-.513.646-.627.775-.114.133-.232.148-.43.05-.197-.1-.836-.308-1.592-.985-.59-.525-.985-1.175-1.103-1.372-.114-.198-.011-.304.088-.403.087-.088.197-.232.296-.346.1-.114.133-.198.198-.33.065-.134.034-.248-.015-.347-.05-.099-.445-1.076-.612-1.47-.16-.389-.323-.335-.445-.34-.114-.007-.247-.007-.38-.007a.729.729 0 0 0-.529.247c-.182.198-.691.677-.691 1.654 0 .977.71 1.916.81 2.049.098.133 1.394 2.132 3.383 2.992.47.205.84.326 1.129.418.475.152.904.129 1.246.08.38-.058 1.171-.48 1.338-.943.164-.464.164-.86.114-.943-.049-.084-.182-.133-.38-.232z" />
                        </svg>
                    </span>
                    <?php if ($whatsapp_text) : ?>
                        <span class="d-none d-md-inline-block"><?php echo $whatsapp_text; ?></span>
                    <?php endif; ?>
                </a>
            </div>
        <?php
        }
    }

    public static function add_floating_scrolltop() {
        $enable_scrolltop       = get_option('floating_scrollTop','1');
        $whatsapp_position      = get_option('whatsapp_position', 'right');
        if($enable_scrolltop == '1'):
        ?>
        <div class="scroll-to-top floating-button <?php echo $whatsapp_position; ?>" style="display: none;">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-chevron-up" viewBox="0 0 16 16">
                <path fill-rule="evenodd" d="M7.646 4.646a.5.5 0 0 1 .708 0l6 6a.5.5 0 0 1-.708.708L8 5.707l-5.646 5.647a.5.5 0 0 1-.708-.708l6-6z" />
            </svg>
        </div>
        <?php endif;
    }
}

 // Inisialisasi class Velocity_Addons_Floating_Whatsapp
 $velocity_floating_whatsapp = new Velocity_Addons_Floating_Whatsapp();