<?php

/**
 * Login wp-admin satu klik dari CRM Velocity (new.velocitydeveloper.net).
 *
 * CRM menandatangani token pendek (Ed25519) dengan kunci rahasia yang hanya ada di CRM;
 * plugin hanya menyimpan kunci publik, jadi tidak ada rahasia per situs.
 * Token: base64url(json payload) . "." . base64url(tanda tangan), dikirim sebagai ?velocity_sso=...
 * Payload: v=1, aud=host situs, iat, exp (maks. 5 menit), jti (sekali pakai), sub (nama staf CRM).
 *
 * ?velocity_sso=ping -> JSON {velocity_sso:1, home} supaya CRM tahu situs ini mendukung SSO.
 * Matikan: define('VELOCITY_CRM_SSO_DISABLED', true) di wp-config.php.
 *
 * @package Velocity_Addons
 */

defined('ABSPATH') || exit;

class Velocity_Addons_Crm_Sso
{
    const VERSI = 1;

    /** Kunci publik CRM (base64). Lebih dari satu = masa rotasi kunci. */
    const KUNCI_PUBLIK = array(
        'jJOVJajQkRpHjZQtkEl4txZnQDWmHZUcdMAslKhdu5c=',
    );

    const UMUR_MAKS = 300;

    const OPSI_LOG = 'velocity_crm_sso_log';

    public function __construct()
    {
        if (defined('VELOCITY_CRM_SSO_DISABLED') && VELOCITY_CRM_SSO_DISABLED) {
            return;
        }
        // prioritas awal: sebelum maintenance mode / block wp-login mengalihkan permintaan
        add_action('init', array($this, 'tangani'), 1);
    }

    public function tangani()
    {
        if (!isset($_GET['velocity_sso']) || !is_string($_GET['velocity_sso'])) {
            return;
        }
        nocache_headers();
        $token = wp_unslash($_GET['velocity_sso']);

        if ($token === 'ping') {
            wp_send_json(array('velocity_sso' => self::VERSI, 'home' => home_url('/')));
        }

        $hasil = self::verifikasi($token, self::host_situs(), time());
        if (is_wp_error($hasil)) {
            self::gagal($hasil->get_error_message());
        }

        $jti_key = 'velocity_sso_' . md5($hasil['jti']);
        // jti sekali pakai: disimpan melebihi umur token
        if (get_transient($jti_key) !== false || !set_transient($jti_key, 1, self::UMUR_MAKS * 2)) {
            self::gagal('Link login sudah dipakai. Klik ulang tombol di CRM.');
        }

        $admin = self::admin_tujuan();
        if (!$admin) {
            self::gagal('Tidak ada user administrator di situs ini.');
        }

        wp_clear_auth_cookie();
        wp_set_current_user($admin->ID);
        wp_set_auth_cookie($admin->ID, false, is_ssl());
        do_action('wp_login', $admin->user_login, $admin);
        self::catat($hasil, $admin);

        wp_safe_redirect(admin_url());
        exit;
    }

    /**
     * Verifikasi token tanpa efek samping (bisa diuji terpisah).
     *
     * @return array|WP_Error payload yang sah
     */
    public static function verifikasi($token, $host, $sekarang)
    {
        if (!function_exists('sodium_crypto_sign_verify_detached')) {
            return new WP_Error('sso_sodium', 'Server tidak mendukung verifikasi tanda tangan (sodium).');
        }
        $bagian = explode('.', (string) $token);
        if (count($bagian) !== 2 || strlen($token) > 2048) {
            return new WP_Error('sso_format', 'Format link login tidak dikenali.');
        }
        $payload_raw = self::b64url_decode($bagian[0]);
        $tanda       = self::b64url_decode($bagian[1]);
        if ($payload_raw === false || $tanda === false || strlen($tanda) !== 64) {
            return new WP_Error('sso_format', 'Format link login tidak dikenali.');
        }

        $sah = false;
        foreach (self::KUNCI_PUBLIK as $kunci) {
            $pk = base64_decode($kunci, true);
            try {
                if ($pk !== false && strlen($pk) === 32 && sodium_crypto_sign_verify_detached($tanda, $payload_raw, $pk)) {
                    $sah = true;
                    break;
                }
            } catch (Exception $e) {
                // kunci/tanda tangan rusak = tidak sah
            }
        }
        if (!$sah) {
            return new WP_Error('sso_tanda', 'Tanda tangan link login tidak sah.');
        }

        $p = json_decode($payload_raw, true);
        if (!is_array($p) || (int) ($p['v'] ?? 0) !== self::VERSI || empty($p['jti']) || !is_string($p['jti'])) {
            return new WP_Error('sso_isi', 'Isi link login tidak dikenali.');
        }
        if (self::normal_host((string) ($p['aud'] ?? '')) !== self::normal_host($host)) {
            return new WP_Error('sso_aud', 'Link login ini untuk situs lain.');
        }
        $iat = (int) ($p['iat'] ?? 0);
        $exp = (int) ($p['exp'] ?? 0);
        if ($exp < $sekarang || $iat > $sekarang + 60 || $exp <= $iat || $exp - $iat > self::UMUR_MAKS) {
            return new WP_Error('sso_waktu', 'Link login kedaluwarsa. Klik ulang tombol di CRM.');
        }

        return $p;
    }

    private static function admin_tujuan()
    {
        $user = get_users(array('role' => 'administrator', 'orderby' => 'ID', 'order' => 'ASC', 'number' => 1));
        return $user ? $user[0] : null;
    }

    private static function host_situs()
    {
        return (string) wp_parse_url(home_url(), PHP_URL_HOST);
    }

    private static function normal_host($host)
    {
        return preg_replace('/^www\./', '', strtolower(trim($host)));
    }

    private static function b64url_decode($s)
    {
        if (!preg_match('/^[A-Za-z0-9_-]+$/', $s)) {
            return false;
        }
        return base64_decode(strtr($s, '-_', '+/') . str_repeat('=', (4 - strlen($s) % 4) % 4), true);
    }

    /** 20 login SSO terakhir (waktu, staf CRM, IP) untuk jejak audit di situs. */
    private static function catat($p, $admin)
    {
        $log = get_option(self::OPSI_LOG, array());
        $log = is_array($log) ? $log : array();
        array_unshift($log, array(
            'waktu' => current_time('mysql'),
            'staf'  => sanitize_text_field((string) ($p['sub'] ?? '')),
            'user'  => $admin->user_login,
            'ip'    => isset($_SERVER['REMOTE_ADDR']) ? sanitize_text_field(wp_unslash($_SERVER['REMOTE_ADDR'])) : '',
        ));
        update_option(self::OPSI_LOG, array_slice($log, 0, 20), false);
    }

    private static function gagal($pesan)
    {
        wp_die(esc_html($pesan), 'Login CRM gagal', array('response' => 403));
    }
}

$velocity_addons_crm_sso = new Velocity_Addons_Crm_Sso();
