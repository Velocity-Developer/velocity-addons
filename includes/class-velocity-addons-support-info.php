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
    }

    public function add_node($wp_admin_bar)
    {
        if (!current_user_can('manage_options')) {
            return;
        }

        $host = wp_parse_url(home_url(), PHP_URL_HOST);
        $text = 'Halo Tim Support Velocity, saya perlu bantuan untuk web ' . $host;

        $wp_admin_bar->add_node(array(
            'id'     => 'velocity-support',
            'parent' => 'top-secondary',
            'title'  => '<span class="ab-icon dashicons dashicons-whatsapp" aria-hidden="true"></span>'
                . '<span class="ab-label">Bantuan Support</span>',
            'href'   => 'https://wa.me/' . self::WA . '?text=' . rawurlencode($text),
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
