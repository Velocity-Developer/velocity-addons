<?php

/**
 * REST endpoints for optimize database actions in Velocity Addons admin.
 *
 * @package Velocity_Addons
 * @subpackage Velocity_Addons/admin
 */
class Velocity_Addons_Admin_Optimize_REST
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
            '/optimize-db/stats',
            array(
                array(
                    'methods'             => WP_REST_Server::READABLE,
                    'callback'            => array($this, 'get_optimize_db_stats'),
                    'permission_callback' => array($this, 'permissions_manage_options'),
                ),
            )
        );

        register_rest_route(
            $this->namespace,
            '/optimize-db/run',
            array(
                array(
                    'methods'             => WP_REST_Server::EDITABLE,
                    'callback'            => array($this, 'run_optimize_db'),
                    'permission_callback' => array($this, 'permissions_manage_options'),
                ),
            )
        );
    }

    public function permissions_manage_options()
    {
        return current_user_can('manage_options');
    }

    public function get_optimize_db_stats()
    {
        if (get_option('velocity_optimasi', '0') !== '1') {
            return new WP_Error(
                'velocity_optimize_disabled',
                __('Optimize Database feature is disabled.', 'velocity-addons'),
                array('status' => 400)
            );
        }

        return rest_ensure_response(
            array(
                'success' => true,
                'stats'   => Velocity_Addons_Optimasi::get_stats_payload(),
            )
        );
    }

    public function run_optimize_db(WP_REST_Request $request)
    {
        if (get_option('velocity_optimasi', '0') !== '1') {
            return new WP_Error(
                'velocity_optimize_disabled',
                __('Optimize Database feature is disabled.', 'velocity-addons'),
                array('status' => 400)
            );
        }

        $payload = $request->get_json_params();
        if (!is_array($payload)) {
            $payload = $request->get_params();
        }

        $do    = isset($payload['do']) ? sanitize_text_field((string) $payload['do']) : 'selected';
        $items = isset($payload['items']) && is_array($payload['items']) ? $payload['items'] : array();

        $result = Velocity_Addons_Optimasi::run_optimization($do, $items);

        return rest_ensure_response(
            array(
                'success' => true,
                'message' => __('Optimize database selesai.', 'velocity-addons'),
                'result'  => $result,
                'stats'   => Velocity_Addons_Optimasi::get_stats_payload(),
            )
        );
    }
}

$velocity_addons_admin_optimize_rest = new Velocity_Addons_Admin_Optimize_REST();
