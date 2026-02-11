<?php
/**
 * Register snippet code in the WordPress admin panel
 *
 * @link       https://velocitydeveloper.com
 * @since      1.0.0
 *
 * @package    Velocity_Addons
 * @subpackage Velocity_Addons/includes
 */

class Velocity_Addons_Snippet
{
    public function __construct()
    {
        // Setting untuk tiga opsi yang sudah ada
        add_action('admin_init', [$this, 'register_snippet_settings']);

        // Pasang snippet dengan prioritas aman
        add_action('wp_head',      [$this, 'velocity_header_snippet'],  99);
        add_action('wp_body_open', [$this, 'velocity_body_snippet'],    20);
        add_action('wp_footer',    [$this, 'velocity_footer_snippet'], 999);
    }

    public function register_snippet_settings()
    {
        $args = [
            'type'              => 'string',
            'sanitize_callback' => [$this, 'sanitize_snippet'],
            'default'           => '',
        ];

        register_setting('velocity_snippet_group', 'header_snippet', $args);
        register_setting('velocity_snippet_group', 'body_snippet', $args);
        register_setting('velocity_snippet_group', 'footer_snippet', $args);
    }
    /**
     * Allow admins to keep raw snippets while sanitizing fallback for other roles.
     */
    public function sanitize_snippet($value)
    {
        if (!is_string($value)) {
            return '';
        }

        $normalized = str_replace(["\r\n", "\r"], "\n", $value);

        if (!current_user_can('unfiltered_html')) {
            return wp_kses_post($normalized);
        }

        return $normalized;
    }
    public static function snippet_page()
    {
        ?>
        <div class="velocity-dashboard-wrapper">
            <div class="vd-header">
                <h1 class="vd-title">Code Snippet</h1>
                <p class="vd-subtitle">Masukkan snippet untuk Head, Body, dan Footer.</p>
            </div>
            <form method="post" data-velocity-settings="1">
                <div class="vd-section">
                    <div class="vd-section-header" style="padding: 1.25rem 1.5rem; border-bottom: 1px solid #e5e7eb; background-color: #f9fafb;">
                        <h3 style="margin:0; font-size:1.1rem; color:#374151;">Snippet</h3>
                    </div>
                    <div class="vd-section-body">
                        <?php
                        $fields = [
                            ['id' => 'header_snippet', 'type' => 'textarea', 'title' => 'Header Snippet', 'label' => 'Kode ditempatkan di dalam <head>.'],
                            ['id' => 'body_snippet', 'type' => 'textarea', 'title' => 'Body Snippet', 'label' => 'Kode ditempatkan tepat setelah tag pembuka <body>.'],
                            ['id' => 'footer_snippet', 'type' => 'textarea', 'title' => 'Footer Snippet', 'label' => 'Kode ditempatkan sebelum tag penutup </body>.'],
                        ];
                        foreach ($fields as $data) {
                            $id = $data['id'];
                            $title = $data['title'];
                            $label = isset($data['label']) ? $data['label'] : '';
                            $val = get_option($id, '');
                            echo '<div class="vd-form-group">';
                            echo '<div class="vd-form-left">';
                            echo '<label class="vd-form-label" for="' . esc_attr($id) . '">' . esc_html($title) . '</label>';
                            if ($label) echo '<small class="vd-form-hint">' . esc_html($label) . '</small>';
                            echo '</div>';
                            echo '<div class="vd-form-right">';
                            echo '<textarea class="large-text code" id="' . esc_attr($id) . '" name="' . esc_attr($id) . '" rows="10" cols="40">' . esc_textarea($val) . '</textarea>';
                            echo '</div>';
                            echo '</div>';
                        }
                        ?>
                    </div>
                </div>
                <?php submit_button(); ?>
            </form>
            <div class="vd-footer">
                <small>Powered by <a href="https://velocitydeveloper.com/" target="_blank">velocitydeveloper.com</a></small>
            </div>
        </div>
        <?php
    }

    /* ===================== Core Helpers ===================== */

    /**
     * Heuristik: apakah ini konteks editing/kerja (bukan pengunjung)?
     */
    private function is_editing_context(): bool
    {
        // Admin, AJAX, REST
        if (is_admin() || wp_doing_ajax() || (defined('REST_REQUEST') && REST_REQUEST)) {
            return true;
        }

        // Customizer / Preview
        if (is_customize_preview() || is_preview()) {
            return true;
        }

        // User login + admin bar + bisa edit (indikasi sedang kerja/editor/builder)
        /*
        if (is_user_logged_in()
            && function_exists('is_admin_bar_showing') && is_admin_bar_showing()
            && current_user_can('edit_posts')) {
            return true;
        }
        */
        return false;
    }

    /**
     * Processor snippet:
     * - MODE_EDITING: JS dimatikan, shortcode TIDAK dieksekusi, CSS/HTML boleh.
     * - MODE_VISITOR: JS boleh, shortcode dieksekusi.
     */
    private function process_snippet($raw, $area = 'footer', $mode = 'visitor')
    {
        $raw = (string) $raw;
        if (trim($raw) === '') return '';

        $allow_js      = ($mode === 'visitor');
        $expand_sc     = ($mode === 'visitor');

        // Expand shortcode hanya untuk pengunjung
        $content = $expand_sc ? do_shortcode($raw) : $raw;
        $out = trim($content);

        // Jika sudah ada tag umum, keluarkan sesuai izin
        if (preg_match('#<(script|style|link|meta|noscript|!--)#i', $out)) {
            if (!$allow_js) {
                // Hapus <script> di mode editing agar editor/builder aman
                $out = preg_replace('#<script\b[^>]*>.*?</script>#is', '', $out);
            }
            return $out;
        }

        // Deteksi CSS sederhana -> bungkus <style>
        $looks_like_css_block = preg_match('#[^\{\}]+\{[^}]+\}#s', $out);
        $looks_like_css_lines = (substr_count($out, ':') >= 1 && substr_count($out, ';') >= 1);
        if ($looks_like_css_block || $looks_like_css_lines) {
            return "<style id=\"velocity-snippet-{$area}\">\n{$out}\n</style>";
        }

        // Deteksi JS sederhana -> bungkus <script> (hanya untuk pengunjung)
        if ($allow_js && preg_match('#\b(function\s*\(|document\.|window\.|console\.|var\s|let\s|const\s)#', $out)) {
            // Bungkus try/catch agar error kecil tidak memutus eksekusi
            $wrapped = "try{\n{$out}\n}catch(e){if(window.console&&console.warn){console.warn('Velocity snippet error:',e);}}";
            return "<script id=\"velocity-snippet-{$area}\">\n{$wrapped}\n</script>";
        }

        // Default -> anggap HTML biasa
        return $out;
    }

    /* ===================== Output Hooks ===================== */

    // Output ke <head>
    public function velocity_header_snippet()
    {
        $snippet = get_option('header_snippet', '');
        if ($snippet === '') return;

        $mode = $this->is_editing_context() ? 'editing' : 'visitor';
        echo "<!-- velocity-snippet:header mode={$mode} -->\n";
        echo $this->process_snippet($snippet, 'header', $mode) . "\n";
    }

    // Output setelah <body> dibuka
    public function velocity_body_snippet()
    {
        $snippet = get_option('body_snippet', '');
        if ($snippet === '') return;

        $mode = $this->is_editing_context() ? 'editing' : 'visitor';
        echo "<!-- velocity-snippet:body mode={$mode} -->\n";
        echo $this->process_snippet($snippet, 'body', $mode) . "\n";
    }

    // Output sebelum </body>
    public function velocity_footer_snippet()
    {
        $snippet = get_option('footer_snippet', '');
        if ($snippet === '') return;

        $mode = $this->is_editing_context() ? 'editing' : 'visitor';
        echo "<!-- velocity-snippet:footer mode={$mode} -->\n";
        echo $this->process_snippet($snippet, 'footer', $mode) . "\n";
    }
}

// Inisialisasi class
$velocity_addons_Snippet = new Velocity_Addons_Snippet();
