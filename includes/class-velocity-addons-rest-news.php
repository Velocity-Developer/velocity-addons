<?php

/**
 * REST controller khusus import berita (News Scraper).
 */
class Velocity_Addons_REST_News
{
    /**
     * Namespace REST API.
     *
     * @var string
     */
    private $namespace = 'velocity-addons/v1';

    /**
     * Registrasi rute REST.
     */
    public function register_routes()
    {
        register_rest_route(
            $this->namespace,
            '/news',
            [
                [
                    'methods'             => WP_REST_Server::READABLE,
                    'callback'            => [$this, 'get_categories'],
                    'permission_callback' => [$this, 'permission_check'],
                ],
                [
                    'methods'             => WP_REST_Server::EDITABLE,
                    'callback'            => [$this, 'import_news'],
                    'permission_callback' => [$this, 'permission_check'],
                ],
            ]
        );
    }

    /**
     * Izinkan hanya admin.
     *
     * @return bool
     */
    public function permission_check()
    {
        return current_user_can('manage_options');
    }

    /**
     * Ambil kategori dari API Velocity.
     *
     * @return WP_REST_Response|WP_Error
     */
    public function get_categories()
    {
        $categories = Velocity_Addons_News::fetch_category();

        if (isset($categories['status']) && $categories['status'] === true) {
            return rest_ensure_response(['data' => $categories['data'] ?? []]);
        }

        $message = isset($categories['message']) ? $categories['message'] : __('Gagal mengambil kategori.', 'velocity-addons');
        return new WP_Error('velocity_news_category_failed', $message, ['status' => 500]);
    }

    /**
     * Import berita via API Velocity.
     *
     * @param WP_REST_Request $request
     * @return WP_REST_Response|WP_Error
     */
    public function import_news(WP_REST_Request $request)
    {
        $target   = sanitize_text_field($request->get_param('target'));
        $category = sanitize_text_field($request->get_param('category'));
        $count    = absint($request->get_param('count'));
        $status   = sanitize_text_field($request->get_param('status'));

        if (!$target || !$category || !$count || !$status) {
            return new WP_Error('velocity_news_invalid', __('Parameter tidak lengkap.', 'velocity-addons'), ['status' => 400]);
        }

        ob_start();
        $output = Velocity_Addons_News::fetch_news_scraper($target, $category, $count, $status);
        $buffer = ob_get_clean();

        return rest_ensure_response([
            'html' => $output . $buffer,
        ]);
    }
}
