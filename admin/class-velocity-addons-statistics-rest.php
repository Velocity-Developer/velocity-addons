<?php

/**
 * REST endpoints for statistics actions in Velocity Addons admin.
 *
 * @package Velocity_Addons
 * @subpackage Velocity_Addons/admin
 */
class Velocity_Addons_Admin_Statistics_REST
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
            '/statistics',
            array(
                array(
                    'methods'             => WP_REST_Server::READABLE,
                    'callback'            => array($this, 'get_statistics'),
                    'permission_callback' => array($this, 'permissions_manage_options'),
                ),
            )
        );

        register_rest_route(
            $this->namespace,
            '/statistics/reset',
            array(
                array(
                    'methods'             => WP_REST_Server::EDITABLE,
                    'callback'            => array($this, 'reset_statistics'),
                    'permission_callback' => array($this, 'permissions_manage_options'),
                ),
            )
        );
    }

    public function permissions_manage_options()
    {
        return current_user_can('manage_options');
    }

    public function get_statistics()
    {
        if (get_option('statistik_velocity', '1') !== '1') {
            return new WP_Error(
                'velocity_statistics_disabled',
                __('Statistics feature is disabled.', 'velocity-addons'),
                array('status' => 400)
            );
        }

        return rest_ensure_response(
            array(
                'success' => true,
                'data'    => $this->build_statistics_payload(),
            )
        );
    }

    public function reset_statistics()
    {
        if (get_option('statistik_velocity', '1') !== '1') {
            return new WP_Error(
                'velocity_statistics_disabled',
                __('Statistics feature is disabled.', 'velocity-addons'),
                array('status' => 400)
            );
        }

        $statistics = $this->get_statistics_handler();
        $statistics->reset_statistics();

        return rest_ensure_response(
            array(
                'success' => true,
                'message' => __('Statistik berhasil di-reset. Semua data statistik dan meta hit telah dihapus.', 'velocity-addons'),
                'data'    => $this->build_statistics_payload(),
            )
        );
    }

    private function get_statistics_handler()
    {
        static $statistics = null;
        if (!($statistics instanceof Velocity_Addons_Statistic)) {
            $statistics = new Velocity_Addons_Statistic();
        }

        return $statistics;
    }

    private function build_statistics_payload()
    {
        $statistics    = $this->get_statistics_handler();
        $summary       = $statistics->get_summary_stats();
        $daily_stats   = $statistics->get_daily_stats(30);
        $page_stats    = $statistics->get_page_stats(30);
        $referer_stats = $statistics->get_referer_stats(30);

        $daily = array_map(function ($stat) {
            return array(
                'date'          => isset($stat->visit_date) ? (string) $stat->visit_date : '',
                'unique_visits' => isset($stat->unique_visits) ? (int) $stat->unique_visits : 0,
                'total_visits'  => isset($stat->total_visits) ? (int) $stat->total_visits : 0,
            );
        }, (array) $daily_stats);

        $pages = array_map(function ($page) {
            $url = isset($page->page_url) ? (string) $page->page_url : '';
            return array(
                'url'             => $url,
                'full_url'        => home_url($url),
                'unique_visitors' => isset($page->unique_visitors) ? (int) $page->unique_visitors : 0,
                'total_views'     => isset($page->total_views) ? (int) $page->total_views : 0,
            );
        }, (array) $page_stats);

        $referrers = array_map(function ($ref) {
            $raw  = isset($ref->referer) ? (string) $ref->referer : '';
            $host = parse_url($raw, PHP_URL_HOST);
            return array(
                'referer' => $raw,
                'host'    => $host ? (string) $host : $raw,
                'visits'  => isset($ref->visits) ? (int) $ref->visits : 0,
            );
        }, (array) $referer_stats);

        return array(
            'summary' => array(
                'today' => array(
                    'unique_visitors' => isset($summary['today']->unique_visitors) ? (int) $summary['today']->unique_visitors : 0,
                    'total_visits'    => isset($summary['today']->total_visits) ? (int) $summary['today']->total_visits : 0,
                ),
                'this_week' => array(
                    'unique_visitors' => isset($summary['this_week']->unique_visitors) ? (int) $summary['this_week']->unique_visitors : 0,
                    'total_visits'    => isset($summary['this_week']->total_visits) ? (int) $summary['this_week']->total_visits : 0,
                ),
                'this_month' => array(
                    'unique_visitors' => isset($summary['this_month']->unique_visitors) ? (int) $summary['this_month']->unique_visitors : 0,
                    'total_visits'    => isset($summary['this_month']->total_visits) ? (int) $summary['this_month']->total_visits : 0,
                ),
                'all_time' => array(
                    'unique_visitors' => isset($summary['all_time']->unique_visitors) ? (int) $summary['all_time']->unique_visitors : 0,
                    'total_visits'    => isset($summary['all_time']->total_visits) ? (int) $summary['all_time']->total_visits : 0,
                ),
            ),
            'daily'      => $daily,
            'page_chart' => array_slice(array_map(function ($page) {
                return array(
                    'url'   => isset($page['url']) ? (string) $page['url'] : '',
                    'views' => isset($page['total_views']) ? (int) $page['total_views'] : 0,
                );
            }, $pages), 0, 8),
            'pages'      => $pages,
            'referrers'  => $referrers,
        );
    }
}

$velocity_addons_admin_statistics_rest = new Velocity_Addons_Admin_Statistics_REST();
