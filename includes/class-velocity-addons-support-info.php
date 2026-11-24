<?php

/**
 * Info kontak Tim Support di admin bar (pojok kanan atas, kiri "Howdy").
 *
 * Tampil untuk admin yang login, di wp-admin maupun front-end,
 * tanpa menimpa konten halaman.
 *
 * @package    Velocity_Addons
 * @subpackage Velocity_Addons/includes
 */

class Velocity_Addons_Support_Info
{
    const WA = '6287742295809';

    public function __construct()
    {
        add_action('admin_bar_menu', array($this, 'add_node'), 100);
        add_action('admin_bar_init', array($this, 'add_style'));
        add_action('admin_footer', array($this, 'floating_button'));
    }

    private function wa_url()
    {
        $host = wp_parse_url(home_url(), PHP_URL_HOST);
        $text = 'Halo Tim Support Velocity, saya perlu bantuan untuk web ' . $host;

        return 'https://wa.me/' . self::WA . '?text=' . rawurlencode($text);
    }

    /**
     * Tombol WA melayang kanan bawah, hanya di wp-admin untuk admin.
     * Tidak tampil di editor blok agar tidak menutupi panel editor.
     */
    public function floating_button()
    {
        if (!current_user_can('manage_options')) {
            return;
        }

        $screen = function_exists('get_current_screen') ? get_current_screen() : null;
        if ($screen && method_exists($screen, 'is_block_editor') && $screen->is_block_editor()) {
            return;
        }
        ?>
        <a id="vd-support-float" href="<?php echo esc_url($this->wa_url()); ?>" target="_blank" rel="noopener"
            title="Jika perlu bantuan, hubungi Tim Support kami lewat WhatsApp 0877-4229-5809">
            <span class="dashicons dashicons-whatsapp" aria-hidden="true"></span>
            <span>Bantuan Support</span>
        </a>
        <style>
            #vd-support-float {
                position: fixed; right: 20px; bottom: 20px; z-index: 9990;
                display: inline-flex; align-items: center; gap: 6px;
                padding: 8px 14px 8px 11px; border-radius: 999px;
                background: #25d366; color: #fff; text-decoration: none;
                font-size: 13px; font-weight: 600; line-height: 1;
                box-shadow: 0 4px 14px rgba(0, 0, 0, .18);
                transition: transform .15s ease, box-shadow .15s ease, background .15s ease;
            }
            #vd-support-float .dashicons { font-size: 18px; width: 18px; height: 18px; }
            #vd-support-float:hover, #vd-support-float:focus {
                background: #1ebe5b; color: #fff; transform: translateY(-2px);
                box-shadow: 0 6px 18px rgba(0, 0, 0, .22);
            }
            #vd-support-float:focus { outline: 2px solid #1d2327; outline-offset: 2px; }
            @media (max-width: 782px) { #vd-support-float { right: 12px; bottom: 12px; } }
            @media (prefers-reduced-motion: reduce) { #vd-support-float { transition: none; } #vd-support-float:hover { transform: none; } }
        </style>
        <?php
    }

    public function add_node($wp_admin_bar)
    {
        if (!current_user_can('manage_options')) {
            return;
        }

        $wp_admin_bar->add_node(array(
            'id'     => 'velocity-support',
            'parent' => 'top-secondary',
            'title'  => '<span class="ab-icon dashicons dashicons-whatsapp" aria-hidden="true"></span>'
                . '<span class="ab-label">Bantuan Support</span>',
            'href'   => $this->wa_url(),
            'meta'   => array(
                'target' => '_blank',
                'rel'    => 'noopener',
                'title'  => 'Jika perlu bantuan, hubungi Tim Support kami lewat WhatsApp 0877-4229-5809',
            ),
        ));
    }

    public function add_style()
    {
        if (!current_user_can('manage_options')) {
            return;
        }

        wp_add_inline_style('admin-bar', '
            #wpadminbar #wp-admin-bar-velocity-support > .ab-item { color: #7ee2a8; }
            #wpadminbar #wp-admin-bar-velocity-support > .ab-item:hover { color: #fff; }
            #wpadminbar #wp-admin-bar-velocity-support .ab-icon:before { content: "\f19a"; color: #25d366; top: 2px; }
            @media screen and (max-width: 782px) {
                #wpadminbar li#wp-admin-bar-velocity-support { display: block; }
                #wpadminbar #wp-admin-bar-velocity-support .ab-label { display: none; }
                #wpadminbar #wp-admin-bar-velocity-support > .ab-item { width: 52px; text-align: center; }
            }
        ');
    }
}

new Velocity_Addons_Support_Info();
