<?php
/**
 * Self-check Velocity_Addons_Block_Wp_Login::get_country_code() tanpa WordPress.
 * Jalankan: php tests/test-block-wp-login.php
 */

define('DAY_IN_SECONDS', 86400);
define('HOUR_IN_SECONDS', 3600);

$GLOBALS['vd_transients'] = array();
$GLOBALS['vd_remote']     = null; // Respon palsu wp_remote_get().
$GLOBALS['vd_calls']      = array();

function get_option($name, $default = false) { return $default; }
function add_action() {}
function is_wp_error($thing) { return $thing === 'WP_ERROR'; }
function get_transient($key) { return array_key_exists($key, $GLOBALS['vd_transients']) ? $GLOBALS['vd_transients'][$key][0] : false; }
function set_transient($key, $value, $ttl) { $GLOBALS['vd_transients'][$key] = array($value, $ttl); return true; }
function wp_remote_get($url, $args = array()) { $GLOBALS['vd_calls'][] = $url; return $GLOBALS['vd_remote']; }
function wp_remote_retrieve_response_code($r) { return $r['response']['code']; }
function wp_remote_retrieve_body($r) { return $r['body']; }
function wp_die() { throw new Exception('wp_die dipanggil'); }

require __DIR__ . '/../includes/class-velocity-addons-block-wp-login.php';

$method = new ReflectionMethod('Velocity_Addons_Block_Wp_Login', 'get_country_code');
$method->setAccessible(true);
$obj  = new Velocity_Addons_Block_Wp_Login();
$fail = 0;

function check($label, $ok) {
    global $fail;
    echo ($ok ? 'OK   ' : 'GAGAL') . " $label\n";
    if (!$ok) $fail++;
}

function reset_state($remote) {
    $GLOBALS['vd_transients'] = array();
    $GLOBALS['vd_calls']      = array();
    $GLOBALS['vd_remote']     = $remote;
    $_COOKIE = array();
    unset($_SERVER['HTTP_CF_IPCOUNTRY']);
}

$ok_us = array('response' => array('code' => 200), 'body' => '{"ip":"8.8.8.8","country":"US"}');
$ip    = '8.8.8.8';

// 1. Cookie palsu VD_<ip>=ID tidak lagi dipercaya.
reset_state($ok_us);
$_COOKIE['VD_' . ip2long($ip)] = 'ID';
check('cookie palsu VD_<ip>=ID diabaikan', $method->invoke($obj, $ip) === 'US');

// 2. Lookup lewat HTTPS dan hasil di-cache di transient 7 hari.
check('lookup memakai HTTPS', strpos($GLOBALS['vd_calls'][0], 'https://') === 0);
$cache = $GLOBALS['vd_transients']['vd_geo_' . md5($ip)];
check('hasil di-cache 7 hari', $cache[0] === 'US' && $cache[1] === 7 * DAY_IN_SECONDS);

// 3. Permintaan kedua memakai cache, tanpa panggil API.
$method->invoke($obj, $ip);
check('permintaan kedua dari cache', count($GLOBALS['vd_calls']) === 1);

// 4. API gagal, tanpa header Cloudflare: tidak wp_die(), login diizinkan ('').
reset_state('WP_ERROR');
try {
    $cc = $method->invoke($obj, $ip);
    check("API gagal tanpa CF-IPCountry -> '' (login diizinkan)", $cc === '');
} catch (Exception $e) {
    check('API gagal tidak boleh wp_die()', false);
}
check('kegagalan di-cache 1 jam', $GLOBALS['vd_transients']['vd_geo_' . md5($ip)][1] === HOUR_IN_SECONDS);

// 5. API gagal, ada header CF-IPCountry: pakai header.
reset_state(array('response' => array('code' => 404), 'body' => '{"error":{"code":404}}'));
$_SERVER['HTTP_CF_IPCOUNTRY'] = 'sg';
check('API 404 + CF-IPCountry=sg -> SG', $method->invoke($obj, $ip) === 'SG');

// 6. Body aneh dari API tidak dipakai mentah.
reset_state(array('response' => array('code' => 200), 'body' => '{"country":"<script>"}'));
check('country tidak valid ditolak', $method->invoke($obj, $ip) === '');

echo $fail ? "\n$fail uji GAGAL\n" : "\nSemua uji lulus\n";
exit($fail ? 1 : 0);
