<?php

/**
 * REST controller untuk Optimize Database (membaca statistik kandidat & menjalankan pembersihan).
 */
class Velocity_Addons_REST_Optimasi
{
    private $namespace = 'velocity-addons/v1';

    public function register_routes()
    {
        register_rest_route(
            $this->namespace,
            '/optimize',
            [
                [
                    'methods'             => WP_REST_Server::READABLE,
                    'callback'            => [$this, 'get_stats'],
                    'permission_callback' => [$this, 'permission_check'],
                ],
                [
                    'methods'             => WP_REST_Server::EDITABLE,
                    'callback'            => [$this, 'run_optimize'],
                    'permission_callback' => [$this, 'permission_check'],
                ],
            ]
        );
    }

    public function permission_check()
    {
        return current_user_can('manage_options');
    }

    public function get_stats()
    {
        if (!class_exists('Velocity_Addons_Optimasi')) {
            return new WP_Error('velocity_optimize_missing', __('Class Optimasi tidak ditemukan.', 'velocity-addons'), ['status' => 500]);
        }

        $stats = Velocity_Addons_Optimasi::stats();
        $rows_total = 0;
        $size_total = 0;
        foreach ($stats as $it) {
            $rows_total += isset($it['count']) ? (int) $it['count'] : 0;
            $size_total += isset($it['size']) ? (int) $it['size'] : 0;
        }

        return rest_ensure_response([
            'stats'      => $stats,
            'rows_total' => $rows_total,
            'size_total' => $size_total,
        ]);
    }

    public function run_optimize(WP_REST_Request $request)
    {
        if (!class_exists('Velocity_Addons_Optimasi')) {
            return new WP_Error('velocity_optimize_missing', __('Class Optimasi tidak ditemukan.', 'velocity-addons'), ['status' => 500]);
        }

        $items = $request->get_param('items');
        if (!is_array($items) || empty($items)) {
            return new WP_Error('velocity_optimize_invalid', __('Tidak ada item yang dipilih.', 'velocity-addons'), ['status' => 400]);
        }

        $items = array_map('sanitize_text_field', $items);
        $results = (new Velocity_Addons_Optimasi())->delete_items($items);

        return rest_ensure_response(['results' => $results]);
    }
}
