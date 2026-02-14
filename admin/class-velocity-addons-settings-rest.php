<?php

/**
 * REST endpoints for Velocity Addons admin settings pages.
 *
 * @package Velocity_Addons
 * @subpackage Velocity_Addons/admin
 */

class Velocity_Addons_Admin_Settings_REST
{
    /**
     * Endpoint namespace.
     *
     * @var string
     */
    private $namespace = 'velocity-addons/v1';

    public function __construct()
    {
        add_action('rest_api_init', array($this, 'register_routes'));
    }

    public function register_routes()
    {
        register_rest_route(
            $this->namespace,
            '/settings/(?P<page>[a-z0-9_-]+)',
            array(
                array(
                    'methods'             => WP_REST_Server::READABLE,
                    'callback'            => array($this, 'get_settings'),
                    'permission_callback' => array($this, 'permissions_manage_options'),
                ),
                array(
                    'methods'             => WP_REST_Server::EDITABLE,
                    'callback'            => array($this, 'update_settings'),
                    'permission_callback' => array($this, 'permissions_manage_options'),
                ),
            )
        );

        register_rest_route(
            $this->namespace,
            '/settings/general/reset',
            array(
                array(
                    'methods'             => WP_REST_Server::EDITABLE,
                    'callback'            => array($this, 'reset_general_settings'),
                    'permission_callback' => array($this, 'permissions_manage_options'),
                ),
            )
        );

        register_rest_route(
            $this->namespace,
            '/license/check',
            array(
                array(
                    'methods'             => WP_REST_Server::EDITABLE,
                    'callback'            => array($this, 'check_license'),
                    'permission_callback' => array($this, 'permissions_manage_options'),
                ),
            )
        );
    }

    public function permissions_manage_options()
    {
        return current_user_can('manage_options');
    }

    public function get_settings(WP_REST_Request $request)
    {
        $page       = sanitize_key((string) $request['page']);
        $definition = $this->get_page_definition($page);

        if (empty($definition)) {
            return new WP_Error(
                'velocity_invalid_settings_page',
                __('Unknown settings page.', 'velocity-addons'),
                array('status' => 404)
            );
        }

        return rest_ensure_response(
            array(
                'success'  => true,
                'page'     => $page,
                'settings' => $this->get_page_settings($definition),
            )
        );
    }

    public function update_settings(WP_REST_Request $request)
    {
        $page       = sanitize_key((string) $request['page']);
        $definition = $this->get_page_definition($page);

        if (empty($definition)) {
            return new WP_Error(
                'velocity_invalid_settings_page',
                __('Unknown settings page.', 'velocity-addons'),
                array('status' => 404)
            );
        }

        $payload = $request->get_json_params();
        if (! is_array($payload)) {
            $payload = $request->get_params();
        }
        $settings_payload = isset($payload['settings']) && is_array($payload['settings']) ? $payload['settings'] : $payload;

        foreach ($definition['options'] as $option_name => $schema) {
            if (!array_key_exists($option_name, $settings_payload)) {
                continue;
            }

            $incoming_value = $settings_payload[$option_name];
            $sanitized      = $this->prepare_option_for_save($option_name, $incoming_value, $schema);

            update_option($option_name, $sanitized);

            // Keep legacy single WhatsApp number synchronized with contact list.
            if ($option_name === 'nomor_whatsapp_contacts') {
                $first_number = '';
                if (is_array($sanitized) && isset($sanitized[0]) && is_array($sanitized[0])) {
                    $first_number = isset($sanitized[0]['number']) ? (string) $sanitized[0]['number'] : '';
                }
                update_option('nomor_whatsapp', $first_number);
            }
        }

        return rest_ensure_response(
            array(
                'success'  => true,
                'page'     => $page,
                'message'  => __('Settings saved successfully.', 'velocity-addons'),
                'settings' => $this->get_page_settings($definition),
            )
        );
    }

    public function reset_general_settings()
    {
        foreach ($this->get_general_defaults() as $key => $value) {
            update_option($key, $value);
        }

        return rest_ensure_response(
            array(
                'success'  => true,
                'message'  => __('General settings restored to defaults.', 'velocity-addons'),
                'settings' => $this->get_page_settings($this->get_page_definition('general')),
            )
        );
    }

    public function check_license(WP_REST_Request $request)
    {
        $payload = $request->get_json_params();
        if (! is_array($payload)) {
            $payload = $request->get_params();
        }

        $license_key = '';
        if (isset($payload['license_key'])) {
            $license_key = sanitize_text_field((string) $payload['license_key']);
        }

        if ($license_key === '') {
            $saved = get_option('velocity_license', array());
            if (is_array($saved) && isset($saved['key'])) {
                $license_key = sanitize_text_field((string) $saved['key']);
            }
        }

        if ($license_key === '') {
            return new WP_Error(
                'velocity_license_required',
                __('Please enter a license key.', 'velocity-addons'),
                array('status' => 400)
            );
        }

        global $velocity_license;
        if (!($velocity_license instanceof Velocity_Addons_License)) {
            $velocity_license = new Velocity_Addons_License();
        }

        $result = $velocity_license->verify_license_key($license_key);

        if (empty($result['success'])) {
            return new WP_Error(
                'velocity_license_invalid',
                isset($result['message']) ? (string) $result['message'] : __('License check failed.', 'velocity-addons'),
                array(
                    'status'  => 400,
                    'details' => $result,
                )
            );
        }

        return rest_ensure_response(
            array(
                'success'  => true,
                'message'  => __('License verified.', 'velocity-addons'),
                'result'   => $result,
                'settings' => $this->get_page_settings($this->get_page_definition('license')),
            )
        );
    }

    private function get_page_settings($definition)
    {
        $result = array();
        foreach ($definition['options'] as $option_name => $schema) {
            $stored = get_option($option_name, null);
            if ($stored === null || $stored === false) {
                $stored = $this->get_default_for_schema($schema);
            }
            $result[$option_name] = $this->sanitize_value($stored, $schema);
        }
        return $result;
    }

    private function prepare_option_for_save($option_name, $incoming_value, $schema)
    {
        if ($schema['type'] === 'object') {
            $existing = get_option($option_name, array());
            if (!is_array($existing)) {
                $existing = array();
            }
            if (!is_array($incoming_value)) {
                $incoming_value = array();
            }
            $incoming_value = $this->deep_merge($existing, $incoming_value);
        }

        $sanitized = $this->sanitize_value($incoming_value, $schema);

        if ($option_name === 'velocity_license' && is_array($sanitized)) {
            $current = get_option('velocity_license', array());
            if (!is_array($current)) {
                $current = array();
            }

            if (!isset($sanitized['status']) && isset($current['status'])) {
                $sanitized['status'] = $current['status'];
            }
            if (!isset($sanitized['expire_date']) && isset($current['expire_date'])) {
                $sanitized['expire_date'] = $current['expire_date'];
            }
            if (isset($sanitized['key']) && isset($current['key']) && $sanitized['key'] !== $current['key']) {
                $sanitized['status'] = 'pending';
            }
        }

        return $sanitized;
    }

    private function deep_merge($base, $incoming)
    {
        if (!is_array($base) || !is_array($incoming)) {
            return $incoming;
        }

        foreach ($incoming as $key => $value) {
            if (array_key_exists($key, $base) && is_array($base[$key]) && is_array($value)) {
                $base[$key] = $this->deep_merge($base[$key], $value);
            } else {
                $base[$key] = $value;
            }
        }

        return $base;
    }

    private function sanitize_value($value, $schema)
    {
        $type = isset($schema['type']) ? $schema['type'] : 'text';

        switch ($type) {
            case 'bool':
                return $this->to_bool_int($value);

            case 'int':
                $number = absint($value);
                if (isset($schema['min']) && $number < (int) $schema['min']) {
                    $number = (int) $schema['min'];
                }
                if (isset($schema['max']) && $number > (int) $schema['max']) {
                    $number = (int) $schema['max'];
                }
                return $number;

            case 'text':
                return sanitize_text_field((string) $value);

            case 'textarea':
                return sanitize_textarea_field((string) $value);

            case 'whatsapp_message':
                return $this->sanitize_whatsapp_message($value);

            case 'url':
                return esc_url_raw((string) $value);

            case 'select':
                $value   = sanitize_text_field((string) $value);
                $allowed = isset($schema['allowed']) && is_array($schema['allowed']) ? $schema['allowed'] : array();
                if (!empty($allowed) && !in_array($value, $allowed, true)) {
                    return (string) $this->get_default_for_schema($schema);
                }
                return $value;

            case 'string_array':
                return $this->sanitize_string_array($value, isset($schema['mode']) ? (string) $schema['mode'] : 'text');

            case 'post_types_array':
                return $this->sanitize_post_types_array($value);

            case 'snippet':
                return $this->sanitize_snippet($value);

            case 'whatsapp_contacts':
                return $this->sanitize_whatsapp_contacts($value);

            case 'object':
                $value      = is_array($value) ? $value : array();
                $properties = isset($schema['properties']) && is_array($schema['properties']) ? $schema['properties'] : array();
                $sanitized  = array();
                foreach ($properties as $prop_key => $prop_schema) {
                    $raw = array_key_exists($prop_key, $value) ? $value[$prop_key] : $this->get_default_for_schema($prop_schema);
                    $sanitized[$prop_key] = $this->sanitize_value($raw, $prop_schema);
                }
                return $sanitized;

            default:
                return sanitize_text_field((string) $value);
        }
    }

    private function to_bool_int($value)
    {
        if (is_bool($value)) {
            return $value ? 1 : 0;
        }
        if (is_numeric($value)) {
            return ((int) $value) === 1 ? 1 : 0;
        }
        if (is_string($value)) {
            $value = strtolower(trim($value));
            return in_array($value, array('1', 'true', 'yes', 'on'), true) ? 1 : 0;
        }
        return 0;
    }

    private function sanitize_string_array($value, $mode = 'text')
    {
        if (!is_array($value)) {
            $value = array();
        }

        $clean = array();
        foreach ($value as $item) {
            if (!is_scalar($item)) {
                continue;
            }
            $item = (string) $item;
            if ($mode === 'key') {
                $item = sanitize_key($item);
            } else {
                $item = sanitize_text_field($item);
            }
            if ($item === '') {
                continue;
            }
            $clean[] = $item;
        }

        return array_values(array_unique($clean));
    }

    private function sanitize_post_types_array($value)
    {
        $selected = $this->sanitize_string_array($value, 'key');
        $all      = get_post_types(array(), 'names');
        $clean    = array_values(array_intersect($selected, $all));

        if (empty($clean)) {
            $clean = (array) $this->get_default_for_schema(array(
                'type'    => 'post_types_array',
                'default' => array('post', 'page'),
            ));
        }

        return $clean;
    }

    private function sanitize_snippet($value)
    {
        if (!is_string($value)) {
            return '';
        }

        $normalized = str_replace(array("\r\n", "\r"), "\n", $value);
        if (!current_user_can('unfiltered_html')) {
            return wp_kses_post($normalized);
        }

        return $normalized;
    }

    private function sanitize_whatsapp_contacts($value)
    {
        if (!is_array($value)) {
            $value = $value ? array(array('name' => '', 'number' => $value)) : array();
        }

        $clean = array();
        foreach ($value as $contact) {
            if (!is_array($contact)) {
                continue;
            }

            $name   = isset($contact['name']) ? sanitize_text_field((string) $contact['name']) : '';
            $number = isset($contact['number']) ? preg_replace('/[^0-9]/', '', (string) $contact['number']) : '';

            if ($number === '') {
                continue;
            }
            if (strpos($number, '0') === 0) {
                $number = substr_replace($number, '62', 0, 1);
            }

            $clean[] = array(
                'name'   => $name,
                'number' => $number,
            );
        }

        return array_values($clean);
    }

    private function sanitize_whatsapp_message($value)
    {
        if (class_exists('Velocity_Addons_Floating_Whatsapp') && method_exists('Velocity_Addons_Floating_Whatsapp', 'normalize_whatsapp_message')) {
            return Velocity_Addons_Floating_Whatsapp::normalize_whatsapp_message((string) $value);
        }

        $normalized = str_replace(array("\r\n", "\r"), "\n", (string) $value);
        $normalized = str_replace(array('\\r\\n', '\\n', '\\r'), "\n", $normalized);
        $normalized = str_ireplace(array('%0D%0A', '%0A', '%0D'), "\n", $normalized);

        return sanitize_textarea_field($normalized);
    }

    private function get_default_for_schema($schema)
    {
        if (isset($schema['default'])) {
            return $schema['default'];
        }

        $type = isset($schema['type']) ? $schema['type'] : 'text';
        if ($type === 'bool') {
            return 0;
        }
        if ($type === 'int') {
            return 0;
        }
        if ($type === 'string_array' || $type === 'post_types_array' || $type === 'whatsapp_contacts') {
            return array();
        }
        if ($type === 'object') {
            $defaults = array();
            if (isset($schema['properties']) && is_array($schema['properties'])) {
                foreach ($schema['properties'] as $key => $prop_schema) {
                    $defaults[$key] = $this->get_default_for_schema($prop_schema);
                }
            }
            return $defaults;
        }

        return '';
    }

    private function get_page_definition($page)
    {
        $definitions = $this->get_definitions();
        return isset($definitions[$page]) ? $definitions[$page] : null;
    }

    private function get_general_defaults()
    {
        return array(
            'fully_disable_comment'        => 1,
            'hide_admin_notice'            => 0,
            'disable_gutenberg'            => 0,
            'classic_widget_velocity'      => 1,
            'enable_xml_sitemap'           => 1,
            'seo_velocity'                 => 1,
            'statistik_velocity'           => 1,
            'floating_whatsapp'            => 1,
            'floating_scrollTop'           => 1,
            'remove_slug_category_velocity'=> 0,
            'news_generate'                => 1,
            'velocity_gallery'             => 0,
            'velocity_optimasi'            => 0,
            'velocity_duitku'              => 0,
        );
    }

    private function get_definitions()
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
                            'provider'  => array('type' => 'select', 'allowed' => array('google', 'image'), 'default' => 'google'),
                            'aktif'     => array('type' => 'bool', 'default' => 1),
                            'difficulty'=> array('type' => 'select', 'allowed' => array('easy', 'medium', 'hard'), 'default' => 'medium'),
                            'sitekey'   => array('type' => 'text', 'default' => ''),
                            'secretkey' => array('type' => 'text', 'default' => ''),
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
                            'maxwidth'  => array('type' => 'int', 'min' => 0, 'default' => 1200),
                            'maxheight' => array('type' => 'int', 'min' => 0, 'default' => 1200),
                            'quality'   => array('type' => 'int', 'min' => 10, 'max' => 100, 'default' => 90),
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
}

$velocity_addons_admin_settings_rest = new Velocity_Addons_Admin_Settings_REST();
