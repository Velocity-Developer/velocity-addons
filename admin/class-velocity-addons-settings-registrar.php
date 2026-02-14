<?php

/**
 * Registers plugin settings based on centralized registry definitions.
 *
 * @package Velocity_Addons
 * @subpackage Velocity_Addons/admin
 */
class Velocity_Addons_Settings_Registrar
{
    /**
     * Prevent duplicate registration in same request.
     *
     * @var bool
     */
    private static $registered = false;

    public function __construct()
    {
        add_action('admin_init', array(__CLASS__, 'register_all'), 10);
    }

    /**
     * Register all known settings from registry to WordPress Settings API.
     *
     * @return void
     */
    public static function register_all()
    {
        if (self::$registered || !class_exists('Velocity_Addons_Settings_Registry')) {
            return;
        }

        $definitions = Velocity_Addons_Settings_Registry::get_rest_definitions();
        if (!is_array($definitions) || empty($definitions)) {
            self::$registered = true;
            return;
        }

        global $wp_registered_settings;
        if (!is_array($wp_registered_settings)) {
            $wp_registered_settings = array();
        }

        foreach ($definitions as $route => $definition) {
            $group   = self::resolve_group_for_route((string) $route);
            $options = isset($definition['options']) && is_array($definition['options']) ? $definition['options'] : array();

            foreach ($options as $option_name => $schema) {
                if (!is_string($option_name) || $option_name === '') {
                    continue;
                }

                // Do not override settings already registered with dedicated callbacks.
                if (isset($wp_registered_settings[$option_name])) {
                    continue;
                }

                register_setting($group, $option_name, self::build_register_args($schema));
            }
        }

        self::$registered = true;
    }

    /**
     * Map route key to legacy WP settings group.
     *
     * @param string $route REST route key.
     * @return string
     */
    private static function resolve_group_for_route($route)
    {
        $map = array(
            'general'           => 'velocity_general_options_group',
            'captcha'           => 'velocity_captcha_options_group',
            'maintenance'       => 'velocity_maintenance_options_group',
            'license'           => 'velocity_license_options_group',
            'security'          => 'velocity_security_options_group',
            'auto_resize'       => 'velocity_auto_resize_options_group',
            'seo'               => 'velocity_seo_group',
            'floating_whatsapp' => 'velocity_floating_whatsapp_group',
            'snippet'           => 'velocity_snippet_group',
            'duitku'            => 'velocity_duitku_group',
        );

        return isset($map[$route]) ? $map[$route] : 'velocity_addons_settings_group';
    }

    /**
     * Build register_setting args from schema.
     *
     * @param array $schema Option schema.
     * @return array
     */
    private static function build_register_args($schema)
    {
        $type = isset($schema['type']) ? (string) $schema['type'] : 'text';
        $args = array(
            'type'         => self::map_wp_type($type),
            'show_in_rest' => false,
        );

        if (isset($schema['default'])) {
            $args['default'] = $schema['default'];
        } elseif ($args['type'] === 'array') {
            $args['default'] = array();
        }

        return $args;
    }

    /**
     * Map custom schema type to WP setting type.
     *
     * @param string $type Schema type.
     * @return string
     */
    private static function map_wp_type($type)
    {
        if ($type === 'bool') {
            return 'boolean';
        }
        if ($type === 'int') {
            return 'integer';
        }
        if ($type === 'object' || $type === 'string_array' || $type === 'post_types_array' || $type === 'whatsapp_contacts') {
            return 'array';
        }

        return 'string';
    }
}

$velocity_addons_settings_registrar = new Velocity_Addons_Settings_Registrar();

