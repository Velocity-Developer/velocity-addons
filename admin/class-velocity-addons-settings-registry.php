<?php

/**
 * Central registry for Velocity Addons admin settings pages.
 *
 * @package Velocity_Addons
 * @subpackage Velocity_Addons/admin
 */
class Velocity_Addons_Settings_Registry
{
    /**
     * Submenu pages registry.
     *
     * @return array
     */
    public static function get_submenu_pages()
    {
        return array(
            array(
                'slug'          => 'velocity_seo_settings',
                'page_title'    => 'SEO',
                'menu_title'    => 'SEO',
                'callback'      => 'velocity_seo_page',
                'route'         => 'seo',
                'form_selector' => "form[data-velocity-settings='1']",
                'feature_toggle'=> array('option' => 'seo_velocity', 'enabled' => '1', 'default' => '1'),
            ),
            array(
                'slug'          => 'velocity_floating_whatsapp',
                'page_title'    => 'Floating Whatsapp',
                'menu_title'    => 'Floating Whatsapp',
                'callback'      => 'velocity_floating_whatsapp_page',
                'route'         => 'floating_whatsapp',
                'form_selector' => "form[data-velocity-settings='1']",
                'feature_toggle'=> array('option' => 'floating_whatsapp', 'enabled' => '1', 'default' => '1'),
            ),
            array(
                'slug'          => 'velocity_news_settings',
                'page_title'    => 'Import Artikel',
                'menu_title'    => 'Import Artikel',
                'callback'      => 'velocity_news_page',
                'feature_toggle'=> array('option' => 'news_generate', 'enabled' => '1', 'default' => '1'),
            ),
            array(
                'slug'          => 'velocity_duitku_settings',
                'page_title'    => 'Duitku',
                'menu_title'    => 'Duitku',
                'callback'      => 'velocity_duitku_page',
                'route'         => 'duitku',
                'form_selector' => "form[data-velocity-settings='1']",
                'feature_toggle'=> array('option' => 'velocity_duitku', 'enabled' => '1', 'default' => '0'),
            ),
            array(
                'slug'          => 'velocity_general_settings',
                'page_title'    => 'Pengaturan Umum',
                'menu_title'    => 'Pengaturan Umum',
                'callback'      => 'velocity_general_page',
                'route'         => 'general',
                'form_selector' => '#velocity-general-form',
                'with_reset'    => true,
            ),
            array(
                'slug'          => 'velocity_captcha_settings',
                'page_title'    => 'Captcha',
                'menu_title'    => 'Captcha',
                'callback'      => 'velocity_captcha_page',
                'route'         => 'captcha',
                'form_selector' => "form[data-velocity-settings='1']",
            ),
            array(
                'slug'          => 'velocity_maintenance_settings',
                'page_title'    => 'Maintenance Mode',
                'menu_title'    => 'Maintenance Mode',
                'callback'      => 'velocity_maintenance_page',
                'route'         => 'maintenance',
                'form_selector' => "form[data-velocity-settings='1']",
            ),
            array(
                'slug'          => 'velocity_license_settings',
                'page_title'    => 'License',
                'menu_title'    => 'License',
                'callback'      => 'velocity_license_page',
                'route'         => 'license',
                'form_selector' => "form[data-velocity-settings='1']",
                'with_license_check' => true,
            ),
            array(
                'slug'          => 'velocity_security_settings',
                'page_title'    => 'Security',
                'menu_title'    => 'Security',
                'callback'      => 'velocity_security_page',
                'route'         => 'security',
                'form_selector' => "form[data-velocity-settings='1']",
            ),
            array(
                'slug'          => 'velocity_auto_resize_settings',
                'page_title'    => 'Auto Resize',
                'menu_title'    => 'Auto Resize',
                'callback'      => 'velocity_auto_resize_page',
                'route'         => 'auto_resize',
                'form_selector' => "form[data-velocity-settings='1']",
            ),
            array(
                'slug'          => 'velocity_snippet_settings',
                'page_title'    => 'Code Snippet',
                'menu_title'    => 'Code Snippet',
                'callback'      => 'velocity_snippet_settings',
                'route'         => 'snippet',
                'form_selector' => "form[data-velocity-settings='1']",
            ),
            array(
                'slug'          => 'velocity_statistics',
                'page_title'    => 'Statistik Pengunjung',
                'menu_title'    => 'Statistik Pengunjung',
                'callback'      => 'visitor_stats_page_callback',
                'kind'          => 'action',
                'feature_toggle'=> array('option' => 'statistik_velocity', 'enabled' => '1', 'default' => '1'),
            ),
            array(
                'slug'          => 'velocity_optimize_db',
                'page_title'    => 'Optimize Database',
                'menu_title'    => 'Optimize Database',
                'callback'      => 'optimize_db_page_callback',
                'kind'          => 'action',
                'feature_toggle'=> array('option' => 'velocity_optimasi', 'enabled' => '1', 'default' => '0'),
            ),
        );
    }

    /**
     * Admin bindings consumed by Alpine settings bridge.
     *
     * @return array
     */
    public static function get_settings_bindings()
    {
        $bindings = array();
        foreach (self::get_submenu_pages() as $page) {
            if (empty($page['route']) || empty($page['slug'])) {
                continue;
            }

            $bindings[$page['slug']] = array(
                'route' => (string) $page['route'],
                'formSelector' => isset($page['form_selector']) ? (string) $page['form_selector'] : "form[data-velocity-settings='1']",
            );

            if (!empty($page['with_reset'])) {
                $bindings[$page['slug']]['withReset'] = true;
            }
            if (!empty($page['with_license_check'])) {
                $bindings[$page['slug']]['withLicenseCheck'] = true;
            }
        }

        return $bindings;
    }

    /**
     * Admin page slugs that use settings bridge (Alpine + REST).
     *
     * @return array
     */
    public static function get_settings_page_slugs()
    {
        return array_keys(self::get_settings_bindings());
    }

    /**
     * Admin page slugs that use REST action bridge.
     *
     * @return array
     */
    public static function get_action_page_slugs()
    {
        $slugs = array();
        foreach (self::get_submenu_pages() as $page) {
            if (isset($page['kind']) && $page['kind'] === 'action' && !empty($page['slug'])) {
                $slugs[] = (string) $page['slug'];
            }
        }
        return $slugs;
    }

    /**
     * Dynamic menu items controlled by General settings toggles.
     *
     * @return array
     */
    public static function get_general_dynamic_menu_items()
    {
        $items = array();
        foreach (self::get_submenu_pages() as $page) {
            if (empty($page['feature_toggle']) || !is_array($page['feature_toggle'])) {
                continue;
            }

            $option = isset($page['feature_toggle']['option']) ? (string) $page['feature_toggle']['option'] : '';
            $slug   = isset($page['slug']) ? (string) $page['slug'] : '';
            $label  = isset($page['menu_title']) ? (string) $page['menu_title'] : '';

            if ($option === '' || $slug === '' || $label === '') {
                continue;
            }

            $items[] = array(
                'option' => $option,
                'href'   => 'admin.php?page=' . $slug,
                'label'  => $label,
            );
        }

        return $items;
    }

    /**
     * REST settings schema registry.
     *
     * @return array
     */
    public static function get_rest_definitions()
    {
        return array(
            'general' => array(
                'options' => array(
                    'fully_disable_comment'         => array('type' => 'bool', 'default' => 1),
                    'hide_admin_notice'             => array('type' => 'bool', 'default' => 0),
                    'disable_gutenberg'             => array('type' => 'bool', 'default' => 0),
                    'classic_widget_velocity'       => array('type' => 'bool', 'default' => 1),
                    'remove_slug_category_velocity' => array('type' => 'bool', 'default' => 0),
                    'enable_xml_sitemap'            => array('type' => 'bool', 'default' => 1),
                    'seo_velocity'                  => array('type' => 'bool', 'default' => 1),
                    'statistik_velocity'            => array('type' => 'bool', 'default' => 1),
                    'floating_whatsapp'             => array('type' => 'bool', 'default' => 1),
                    'floating_scrollTop'            => array('type' => 'bool', 'default' => 1),
                    'news_generate'                 => array('type' => 'bool', 'default' => 1),
                    'velocity_gallery'              => array('type' => 'bool', 'default' => 0),
                    'velocity_optimasi'             => array('type' => 'bool', 'default' => 0),
                    'velocity_duitku'               => array('type' => 'bool', 'default' => 0),
                ),
            ),
            'captcha' => array(
                'options' => array(
                    'captcha_velocity' => array(
                        'type'       => 'object',
                        'properties' => array(
                            'provider'   => array('type' => 'select', 'allowed' => array('google', 'image'), 'default' => 'google'),
                            'aktif'      => array('type' => 'bool', 'default' => 1),
                            'difficulty' => array('type' => 'select', 'allowed' => array('easy', 'medium', 'hard'), 'default' => 'medium'),
                            'sitekey'    => array('type' => 'text', 'default' => ''),
                            'secretkey'  => array('type' => 'text', 'default' => ''),
                        ),
                    ),
                ),
            ),
            'maintenance' => array(
                'options' => array(
                    'maintenance_mode' => array('type' => 'bool', 'default' => 1),
                    'maintenance_mode_data' => array(
                        'type'       => 'object',
                        'properties' => array(
                            'header'     => array('type' => 'text', 'default' => 'Maintenance Mode'),
                            'body'       => array('type' => 'textarea', 'default' => 'We are currently performing maintenance. Please check back later.'),
                            'background' => array('type' => 'int', 'default' => 0),
                        ),
                    ),
                ),
            ),
            'license' => array(
                'options' => array(
                    'velocity_license' => array(
                        'type'       => 'object',
                        'properties' => array(
                            'key'         => array('type' => 'text', 'default' => ''),
                            'expire_date' => array('type' => 'text', 'default' => ''),
                            'status'      => array('type' => 'text', 'default' => ''),
                        ),
                    ),
                ),
            ),
            'security' => array(
                'options' => array(
                    'limit_login_attempts'      => array('type' => 'bool', 'default' => 1),
                    'disable_xmlrpc'            => array('type' => 'bool', 'default' => 1),
                    'block_wp_login'            => array('type' => 'bool', 'default' => 0),
                    'whitelist_block_wp_login'  => array('type' => 'text', 'default' => ''),
                    'whitelist_country'         => array('type' => 'text', 'default' => 'ID'),
                    'redirect_to'               => array('type' => 'text', 'default' => '127.0.0.1'),
                ),
            ),
            'auto_resize' => array(
                'options' => array(
                    'auto_resize_mode' => array('type' => 'bool', 'default' => 0),
                    'auto_resize_mode_data' => array(
                        'type'       => 'object',
                        'properties' => array(
                            'maxwidth'      => array('type' => 'int', 'min' => 0, 'default' => 1200),
                            'maxheight'     => array('type' => 'int', 'min' => 0, 'default' => 1200),
                            'quality'       => array('type' => 'int', 'min' => 10, 'max' => 100, 'default' => 90),
                            'output_format' => array('type' => 'select', 'allowed' => array('original', 'jpeg', 'webp', 'avif'), 'default' => 'original'),
                        ),
                    ),
                    'auto_resize_image_velocity' => array('type' => 'bool', 'default' => 0),
                ),
            ),
            'seo' => array(
                'options' => array(
                    'home_title'       => array('type' => 'text', 'default' => get_bloginfo('name')),
                    'home_description' => array('type' => 'textarea', 'default' => get_bloginfo('description')),
                    'home_keywords'    => array('type' => 'textarea', 'default' => ''),
                    'share_image'      => array('type' => 'url', 'default' => ''),
                    'seo_post_types'   => array('type' => 'post_types_array', 'default' => array('post', 'page')),
                ),
            ),
            'floating_whatsapp' => array(
                'options' => array(
                    'nomor_whatsapp_contacts' => array('type' => 'whatsapp_contacts', 'default' => array()),
                    'whatsapp_text'           => array('type' => 'text', 'default' => 'Butuh Bantuan?'),
                    'whatsapp_message'        => array('type' => 'whatsapp_message', 'default' => 'Hallo...'),
                    'whatsapp_position'       => array('type' => 'select', 'allowed' => array('right', 'left'), 'default' => 'right'),
                ),
            ),
            'snippet' => array(
                'options' => array(
                    'header_snippet' => array('type' => 'snippet', 'default' => ''),
                    'body_snippet'   => array('type' => 'snippet', 'default' => ''),
                    'footer_snippet' => array('type' => 'snippet', 'default' => ''),
                ),
            ),
            'duitku' => array(
                'options' => array(
                    'velocity_duitku_options' => array(
                        'type'       => 'object',
                        'properties' => array(
                            'mode'          => array('type' => 'select', 'allowed' => array('sandbox', 'production'), 'default' => 'sandbox'),
                            'kode_merchant' => array('type' => 'text', 'default' => ''),
                            'merchant_key'  => array('type' => 'text', 'default' => ''),
                            'callback_url'  => array('type' => 'url', 'default' => get_site_url() . '/wp-json/velocityaddons/v1/duitku_callback'),
                            'return_url'    => array('type' => 'url', 'default' => ''),
                        ),
                    ),
                ),
            ),
        );
    }

    /**
     * Defaults for general settings page.
     *
     * @return array
     */
    public static function get_general_defaults()
    {
        $definitions = self::get_rest_definitions();
        if (empty($definitions['general']['options']) || !is_array($definitions['general']['options'])) {
            return array();
        }

        $defaults = array();
        foreach ($definitions['general']['options'] as $option_name => $schema) {
            $defaults[$option_name] = self::schema_default($schema);
        }

        return $defaults;
    }

    /**
     * Resolve schema default recursively.
     *
     * @param array $schema Schema definition.
     * @return mixed
     */
    private static function schema_default($schema)
    {
        if (isset($schema['default'])) {
            return $schema['default'];
        }

        $type = isset($schema['type']) ? $schema['type'] : 'text';
        if ($type === 'bool' || $type === 'int') {
            return 0;
        }
        if ($type === 'string_array' || $type === 'post_types_array' || $type === 'whatsapp_contacts') {
            return array();
        }
        if ($type === 'object') {
            $defaults = array();
            $properties = isset($schema['properties']) && is_array($schema['properties']) ? $schema['properties'] : array();
            foreach ($properties as $key => $property_schema) {
                $defaults[$key] = self::schema_default($property_schema);
            }
            return $defaults;
        }

        return '';
    }
}
