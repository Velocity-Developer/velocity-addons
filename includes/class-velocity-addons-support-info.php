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

        // Bisa dimatikan di Velocity Addons > General (bawaan aktif).
        if (!get_option('floating_bantuan_support', 1)) {
            return;
        }

        $screen = function_exists('get_current_screen') ? get_current_screen() : null;
        if ($screen && method_exists($screen, 'is_block_editor') && $screen->is_block_editor()) {
            return;
        }
        ?>
        <a id="vd-support-float" href="<?php echo esc_url($this->wa_url()); ?>" target="_blank" rel="noopener"
            aria-label="Bantuan Support lewat WhatsApp 0877-4229-5809">
            <span class="vd-sf-ikon" aria-hidden="true"><span class="dashicons dashicons-whatsapp"></span></span>
            <span class="vd-sf-teks"><small>Ada kendala?</small><strong>Bantuan Support</strong></span>
        </a>
        <style>
            #vd-support-float {
                position: fixed; right: 20px; bottom: 20px; z-index: 9990;
                display: inline-flex; align-items: center; gap: 10px;
                padding: 6px 18px 6px 6px; border-radius: 999px;
                background: linear-gradient(135deg, #2e7d32 0%, #1b5e20 100%);
                color: #fff; text-decoration: none;
                box-shadow: 0 8px 22px rgba(27, 94, 32, .38), inset 0 1px 0 rgba(255, 255, 255, .25);
                animation: vd-sf-masuk .45s ease-out both .3s;
                transition: transform .18s ease, box-shadow .18s ease;
            }
            #vd-support-float .vd-sf-ikon {
                position: relative; flex: none; width: 34px; height: 34px; border-radius: 50%;
                background: #fff; display: grid; place-items: center;
            }
            #vd-support-float .vd-sf-ikon::after {
                content: ""; position: absolute; inset: 0; border-radius: 50%;
                box-shadow: 0 0 0 0 rgba(255, 255, 255, .7);
                animation: vd-sf-denyut 2.4s ease-out infinite 1s;
            }
            #vd-support-float .dashicons { color: #25d366; font-size: 21px; width: 21px; height: 21px; }
            #vd-support-float .vd-sf-teks { display: flex; flex-direction: column; line-height: 1.15; }
            #vd-support-float small { font-size: 11px; opacity: .88; }
            #vd-support-float strong { font-size: 14px; font-weight: 700; letter-spacing: .2px; }
            #vd-support-float:hover, #vd-support-float:focus-visible {
                color: #fff; transform: translateY(-3px);
                box-shadow: 0 12px 26px rgba(27, 94, 32, .45), inset 0 1px 0 rgba(255, 255, 255, .25);
            }
            #vd-support-float:focus-visible { outline: 2px solid #1d2327; outline-offset: 3px; }
            @keyframes vd-sf-masuk { from { opacity: 0; transform: translateY(16px); } to { opacity: 1; transform: none; } }
            @keyframes vd-sf-denyut { 0% { box-shadow: 0 0 0 0 rgba(255, 255, 255, .7); } 70%, 100% { box-shadow: 0 0 0 10px rgba(255, 255, 255, 0); } }
            @media (max-width: 782px) {
                #vd-support-float { right: 12px; bottom: 12px; padding-right: 14px; }
                #vd-support-float small { display: none; }
            }
            @media (prefers-reduced-motion: reduce) {
                #vd-support-float, #vd-support-float .vd-sf-ikon::after { animation: none; }
                #vd-support-float { transition: none; }
                #vd-support-float:hover { transform: none; }
            }
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
