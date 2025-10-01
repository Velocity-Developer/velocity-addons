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
        // Menambahkan submenu
        add_action('admin_init', [$this, 'register_snippet_settings']);

        // Hook untuk menambahkan snippet ke bagian yang sesuai
        add_action('wp_head', [$this, 'velocity_header_snippet']);
        add_action('wp_body_open', [$this, 'velocity_body_snippet']);
        add_action('wp_footer', [$this, 'velocity_footer_snippet']);
    }
    
    public function register_snippet_settings()
    {
        register_setting('velocity_snippet_group', 'header_snippet');
        register_setting('velocity_snippet_group', 'body_snippet');
        register_setting('velocity_snippet_group', 'footer_snippet');
    }
    
    public static function snippet_page()
    {
        ?>
        <div class="wrap">
            <h2>Snippet Settings</h2>
            <form method="post" action="options.php">
                <?php settings_fields('velocity_snippet_group'); ?>
                <?php do_settings_sections('velocity_snippet_group'); ?>
                <table class="form-table">
                    <tr valign="top">
                        <th scope="row">Header Snippet</th>
                        <td>
                            <textarea class="large-text code" name="header_snippet" rows="10" cols="40"><?php echo esc_textarea(get_option('header_snippet', '')); ?></textarea>
                            <br/>
                            <small for="header_snippet">Kode yang dimasukkan di sini akan ditempatkan di dalam tag &lt;head&gt; pada setiap halaman situs Anda.</small>
                        </td>
                    </tr>
                    <tr valign="top">
                        <th scope="row">Body Snippet</th>
                        <td>
                            <textarea class="large-text code" name="body_snippet" rows="10" cols="40"><?php echo esc_textarea(get_option('body_snippet', '')); ?></textarea>
                            <br/>
                            <small for="body_snippet">Kode yang dimasukkan di sini akan ditempatkan tepat setelah tag pembuka &lt;body&gt; pada setiap halaman situs Anda.</small>
                        </td>
                    </tr>
                    <tr valign="top">
                        <th scope="row">Footer Snippet</th>
                        <td>
                            <textarea class="large-text code" name="footer_snippet" rows="10" cols="40"><?php echo esc_textarea(get_option('footer_snippet', '')); ?></textarea>
                            <br/>
                            <small for="footer_snippet">Kode yang dimasukkan di sini akan ditempatkan tepat sebelum tag penutup &lt;/body&gt; pada setiap halaman situs Anda.</small>
                        </td>
                    </tr>
                </table>
                <?php submit_button(); ?>
            </form>
        </div>
        <?php
    }

    /* ===== Helper inti: fleksibel CSS/JS/Shortcode ===== */
    private function process_snippet($raw, $area = 'footer')
    {
        $raw = (string) $raw;
        if (trim($raw) === '') return '';

        // 1) Expand shortcode dulu (boleh kosong kalau tidak ada shortcode)
        $expanded = do_shortcode($raw);
        $out = trim($expanded);

        // 2) Kalau sudah mengandung tag yang umum di head/body/footer → keluarkan apa adanya
        if (preg_match('#<(script|style|link|meta|noscript|!--)#i', $out)) {
            return $out;
        }

        // 3) Deteksi CSS sederhana → bungkus <style>
        //    Pola: selector { ... } atau banyak deklarasi "prop: val;"
        $looks_like_css_block = preg_match('#[^\{\}]+\{[^}]+\}#s', $out);
        $looks_like_css_lines = (substr_count($out, ':') >= 1 && substr_count($out, ';') >= 1);

        if ($looks_like_css_block || $looks_like_css_lines) {
            return "<style id=\"velocity-snippet-{$area}\">\n{$out}\n</style>";
        }

        // 4) Deteksi JS sederhana → bungkus <script>
        //    Pola umum: function( / document. / window. / console. / var|let|const
        if (preg_match('#\b(function\s*\(|document\.|window\.|console\.|var\s|let\s|const\s)#', $out)) {
            return "<script id=\"velocity-snippet-{$area}\">\n{$out}\n</script>";
        }

        // 5) Default → anggap HTML biasa
        return $out;
    }

    // Output ke head
    public function velocity_header_snippet() {
        $snippet = get_option('header_snippet', '');
        if (!empty($snippet)) {
            echo $this->process_snippet($snippet, 'header');
        }
    }

    // Output setelah body open
    public function velocity_body_snippet() {
        $snippet = get_option('body_snippet', '');
        if (!empty($snippet)) {
            echo $this->process_snippet($snippet, 'body');
        }
    }

    // Output sebelum body close
    public function velocity_footer_snippet() {
        $snippet = get_option('footer_snippet', '');
        if (!empty($snippet)) {
            echo $this->process_snippet($snippet, 'footer');
        }
    }


}

 // Inisialisasi class Velocity_Addons_Snippet
 $velocity_addons_Snippet = new Velocity_Addons_Snippet();