<?php

/**
 * REST controller untuk mengelola opsi Velocity Addons via React + REST API.
 */
class Velocity_Addons_REST_Options
{
    /**
     * Namespace REST API.
     *
     * @var string
     */
    private $namespace = 'velocity-addons/v1';

    /**
     * Definisi seluruh fields yang ditampilkan di halaman pengaturan.
     *
     * Gunakan struktur ini untuk menjaga kesamaan antara form klasik dan React/REST.
     *
     * @var array<int,array<string,mixed>>
     */
    private static $fields = [
        [
            'id'          => 'fully_disable_comment',
            'type'        => 'boolean',
            'default'     => 1,
            'label'       => 'Disable Comment',
            'description' => 'Nonaktifkan fitur komentar pada situs.',
        ],
        [
            'id'          => 'hide_admin_notice',
            'type'        => 'boolean',
            'default'     => 0,
            'label'       => 'Hide Admin Notice',
            'description' => 'Sembunyikan pemberitahuan admin di halaman admin.',
        ],
        [
            'id'          => 'disable_gutenberg',
            'type'        => 'boolean',
            'default'     => 0,
            'label'       => 'Disable Gutenberg',
            'description' => 'Aktifkan editor klasik WordPress menggantikan Gutenberg.',
        ],
        [
            'id'          => 'classic_widget_velocity',
            'type'        => 'boolean',
            'default'     => 1,
            'label'       => 'Classic Widget',
            'description' => 'Aktifkan widget klasik.',
        ],
        [
            'id'          => 'seo_velocity',
            'type'        => 'boolean',
            'default'     => 1,
            'label'       => 'SEO',
            'description' => 'Aktifkan SEO dari Velocity Developer.',
        ],
        [
            'id'          => 'statistik_velocity',
            'type'        => 'boolean',
            'default'     => 1,
            'label'       => 'Statistik Pengunjung',
            'description' => 'Aktifkan statistik pengunjung dari Velocity Developer.',
        ],
        [
            'id'          => 'floating_whatsapp',
            'type'        => 'boolean',
            'default'     => 1,
            'label'       => 'Floating Whatsapp',
            'description' => 'Aktifkan Whatsapp Floating.',
        ],
        [
            'id'          => 'floating_scrollTop',
            'type'        => 'boolean',
            'default'     => 1,
            'label'       => 'Floating Scrolltop',
            'description' => 'Aktifkan tombol scroll ke atas.',
        ],
        [
            'id'          => 'remove_slug_category_velocity',
            'type'        => 'boolean',
            'default'     => 0,
            'label'       => 'Remove Slug Category',
            'description' => 'Aktifkan untuk hapus slug /category/ dari URL.',
        ],
        [
            'id'          => 'news_generate',
            'type'        => 'boolean',
            'default'     => 1,
            'label'       => 'Import Artikel dari API',
            'description' => 'Aktifkan fungsi untuk import artikel postingan.',
        ],
        [
            'id'          => 'velocity_gallery',
            'type'        => 'boolean',
            'default'     => 0,
            'label'       => 'Gallery Post Type',
            'description' => 'Aktifkan fungsi Gallery Post Type.',
        ],
        [
            'id'          => 'velocity_optimasi',
            'type'        => 'boolean',
            'default'     => 0,
            'label'       => 'Optimize Database',
            'description' => 'Aktifkan fungsi optimasi database.',
        ],
        [
            'id'          => 'velocity_duitku',
            'type'        => 'boolean',
            'default'     => 0,
            'label'       => 'Payment Gateway Duitku',
            'description' => 'Aktifkan payment gateway Duitku.',
        ],
        [
            'id'          => 'captcha_velocity',
            'sub'         => 'aktif',
            'type'        => 'boolean',
            'default'     => 1,
            'label'       => 'Captcha',
            'description' => 'Aktifkan Google reCaptcha v2 (login, komentar, Velocity Toko).',
        ],
        [
            'id'          => 'captcha_velocity',
            'sub'         => 'sitekey',
            'type'        => 'text',
            'default'     => '',
            'label'       => 'Captcha Sitekey',
            'description' => 'Sitekey reCaptcha v2.',
        ],
        [
            'id'          => 'captcha_velocity',
            'sub'         => 'secretkey',
            'type'        => 'text',
            'default'     => '',
            'label'       => 'Captcha Secretkey',
            'description' => 'Secretkey reCaptcha v2.',
        ],
        [
            'id'          => 'maintenance_mode',
            'type'        => 'boolean',
            'default'     => 1,
            'label'       => 'Maintenance Mode',
            'description' => 'Aktifkan halaman perawatan.',
        ],
        [
            'id'          => 'maintenance_mode_data',
            'sub'         => 'header',
            'type'        => 'text',
            'default'     => 'Maintenance Mode',
            'label'       => 'Maintenance Header',
            'description' => 'Judul halaman maintenance.',
        ],
        [
            'id'          => 'maintenance_mode_data',
            'sub'         => 'body',
            'type'        => 'textarea',
            'default'     => 'We are currently performing maintenance. Please check back later.',
            'label'       => 'Maintenance Body',
            'description' => 'Konten utama halaman maintenance.',
        ],
        [
            'id'          => 'maintenance_mode_data',
            'sub'         => 'background',
            'type'        => 'media',
            'default'     => '',
            'label'       => 'Maintenance Background',
            'description' => 'Attachment ID gambar latar belakang.',
        ],
        [
            'id'          => 'velocity_license',
            'sub'         => 'key',
            'type'        => 'password',
            'default'     => '',
            'label'       => 'License Key',
            'description' => 'Masukkan license key Velocity Addons.',
        ],
        [
            'id'          => 'limit_login_attempts',
            'type'        => 'boolean',
            'default'     => 1,
            'label'       => 'Limit Login Attempts',
            'description' => 'Batasi percobaan login (blokir sementara jika melebihi 5x/24 jam).',
        ],
        [
            'id'          => 'disable_xmlrpc',
            'type'        => 'boolean',
            'default'     => 1,
            'label'       => 'Disable XML-RPC',
            'description' => 'Nonaktifkan protokol XML-RPC.',
        ],
        [
            'id'          => 'disable_rest_api',
            'type'        => 'boolean',
            'default'     => 0,
            'label'       => 'Disable REST API / JSON',
            'description' => 'Nonaktifkan akses REST API.',
        ],
        [
            'id'          => 'block_wp_login',
            'type'        => 'boolean',
            'default'     => 0,
            'label'       => 'Block wp-login.php',
            'description' => 'Aktifkan pemblokiran akses wp-login.php.',
        ],
        [
            'id'          => 'whitelist_block_wp_login',
            'type'        => 'text',
            'default'     => '',
            'label'       => 'Whitelist IP Block wp-login.php',
            'description' => 'Daftar IP di-whitelist, pisahkan dengan koma.',
        ],
        [
            'id'          => 'whitelist_country',
            'type'        => 'text',
            'default'     => 'ID',
            'label'       => 'Whitelist Country',
            'description' => 'Batasi akses hanya untuk country code tertentu (misal ID,MY,US).',
        ],
        [
            'id'          => 'redirect_to',
            'type'        => 'text',
            'default'     => '127.0.0.1',
            'label'       => 'Redirect To',
            'description' => 'Tujuan redirect wp-login.php ketika pemblokiran aktif.',
        ],
        [
            'id'          => 'auto_resize_mode',
            'type'        => 'boolean',
            'default'     => 0,
            'label'       => 'Enable re-sizing',
            'description' => 'Aktifkan re-sizing gambar.',
        ],
        [
            'id'          => 'auto_resize_mode_data',
            'sub'         => 'maxwidth',
            'type'        => 'number',
            'default'     => 1200,
            'label'       => 'Max width',
            'description' => 'Lebar maksimum gambar (px).',
        ],
        [
            'id'          => 'auto_resize_mode_data',
            'sub'         => 'maxheight',
            'type'        => 'number',
            'default'     => 1200,
            'label'       => 'Max height',
            'description' => 'Tinggi maksimum gambar (px).',
        ],
    ];

    /**
     * Registrasi rute REST.
     */
    public function register_routes()
    {
        register_rest_route(
            $this->namespace,
            '/options',
            [
                [
                    'methods'             => WP_REST_Server::READABLE,
                    'callback'            => [$this, 'get_options'],
                    'permission_callback' => [$this, 'permission_check'],
                ],
                [
                    'methods'             => WP_REST_Server::EDITABLE,
                    'callback'            => [$this, 'update_options'],
                    'permission_callback' => [$this, 'permission_check'],
                ],
            ]
        );
    }

    /**
     * Izinkan hanya admin yang boleh mengelola opsi.
     *
     * @return bool
     */
    public function permission_check()
    {
        return current_user_can('manage_options');
    }

    /**
     * Mengembalikan nilai opsi saat ini beserta skema field.
     *
     * @return WP_REST_Response
     */
    public function get_options()
    {
        return rest_ensure_response(
            [
                'options' => $this->get_prepared_options(),
                'fields'  => self::get_fields_schema_for_frontend(),
            ]
        );
    }

    /**
     * Memperbarui opsi yang dikirim dari React app.
     *
     * @param WP_REST_Request $request
     * @return WP_REST_Response|WP_Error
     */
    public function update_options(WP_REST_Request $request)
    {
        $params = $request->get_json_params();
        if (!is_array($params)) {
            return new WP_Error('velocity_addons_invalid_payload', __('Payload tidak valid', 'velocity-addons'), ['status' => 400]);
        }

        $defaults   = $this->get_default_options();
        $to_update  = [];
        $updated    = [];

        foreach (self::$fields as $field) {
            $id     = $field['id'];
            $sub    = isset($field['sub']) ? $field['sub'] : null;

            if (!array_key_exists($id, $params)) {
                continue;
            }

            $incoming = $params[$id];
            if ($sub !== null) {
                if (!is_array($incoming) || !array_key_exists($sub, $incoming)) {
                    continue;
                }

                $current = isset($to_update[$id]) ? $to_update[$id] : get_option($id, isset($defaults[$id]) ? $defaults[$id] : []);
                if (!is_array($current)) {
                    $current = is_array($defaults[$id]) ? $defaults[$id] : [];
                }

                $current[$sub] = $this->sanitize_value($incoming[$sub], $field);
                $to_update[$id] = $current;
                $updated[] = $id . ':' . $sub;
                continue;
            }

            $to_update[$id] = $this->sanitize_value($incoming, $field);
            $updated[] = $id;
        }

        foreach ($to_update as $id => $value) {
            update_option($id, $value);
        }

        return rest_ensure_response(
            [
                'options' => $this->get_prepared_options(),
                'updated' => array_values(array_unique($updated)),
            ]
        );
    }

    /**
     * Ambil opsi dengan tipe data yang sudah dinormalisasi.
     *
     * @return array<string,mixed>
     */
    private function get_prepared_options()
    {
        $defaults = $this->get_default_options();
        $data     = $defaults;

        foreach (self::$fields as $field) {
            $id  = $field['id'];
            $sub = isset($field['sub']) ? $field['sub'] : null;

            if ($sub !== null) {
                $value = get_option($id, isset($defaults[$id]) ? $defaults[$id] : []);
                $subVal = is_array($value) && array_key_exists($sub, $value) ? $value[$sub] : (isset($defaults[$id][$sub]) ? $defaults[$id][$sub] : null);

                if (!isset($data[$id]) || !is_array($data[$id])) {
                    $data[$id] = [];
                }

                $data[$id][$sub] = $this->normalize_value($subVal, $field);
                continue;
            }

            $value   = get_option($id, isset($defaults[$id]) ? $defaults[$id] : null);
            $data[$id] = $this->normalize_value($value, $field);
        }

        return $data;
    }

    /**
     * Normalisasi tipe data untuk respons REST.
     *
     * @param mixed $value
     * @param array $field
     * @return mixed
     */
    private function normalize_value($value, array $field)
    {
        $type = isset($field['type']) ? $field['type'] : 'text';

        switch ($type) {
            case 'boolean':
                return (bool) $value;
            case 'number':
                return is_numeric($value) ? intval($value) : 0;
            case 'media':
                return absint($value);
            default:
                return is_scalar($value) ? (string) $value : '';
        }
    }

    /**
     * Sanitasi nilai yang dikirim dari klien.
     *
     * @param mixed $value
     * @param array $field
     * @return mixed
     */
    private function sanitize_value($value, array $field)
    {
        $type = isset($field['type']) ? $field['type'] : 'text';

        if ($type === 'boolean') {
            if (is_string($value)) {
                $value = strtolower($value);
                if (in_array($value, ['0', 'false', 'no', 'off', ''], true)) {
                    return false;
                }
            }
            return (bool) $value;
        }

        if ($type === 'number' || $type === 'media') {
            return absint($value);
        }

        if ($type === 'textarea') {
            return function_exists('sanitize_textarea_field')
                ? sanitize_textarea_field((string) $value)
                : sanitize_text_field((string) $value);
        }

        return sanitize_text_field((string) $value);
    }

    /**
     * Data skema field untuk dikonsumsi React.
     *
     * @return array<int,array>
     */
    public static function get_fields_schema_for_frontend()
    {
        return array_map(function ($field) {
            $field['key'] = isset($field['key']) ? $field['key'] : $field['id'];
            return $field;
        }, self::$fields);
    }

    /**
     * Expose schema untuk dipakai di sisi admin.
     *
     * @return array<int,array>
     */
    public static function get_fields_schema()
    {
        return self::get_fields_schema_for_frontend();
    }

    /**
     * Nilai default setiap opsi (sudah disatukan untuk sub-field).
     *
     * @return array<string,mixed>
     */
    private function get_default_options()
    {
        $defaults = [];

        foreach (self::$fields as $field) {
            $id      = $field['id'];
            $sub     = isset($field['sub']) ? $field['sub'] : null;
            $default = array_key_exists('default', $field) ? $field['default'] : $this->default_by_type($field);

            if ($sub !== null) {
                if (!isset($defaults[$id]) || !is_array($defaults[$id])) {
                    $defaults[$id] = [];
                }
                $defaults[$id][$sub] = $default;
                continue;
            }

            $defaults[$id] = $default;
        }

        return $defaults;
    }

    /**
     * Default fallback per tipe data.
     *
     * @param array $field
     * @return mixed
     */
    private function default_by_type(array $field)
    {
        switch (isset($field['type']) ? $field['type'] : 'text') {
            case 'boolean':
                return false;
            case 'number':
            case 'media':
                return 0;
            default:
                return '';
        }
    }
}
