<?php

/**
 * Register all actions and filters for the plugin
 *
 * @link       https://velocitydeveloper.com
 * @since      1.0.0
 *
 * @package    Velocity_Addons
 * @subpackage Velocity_Addons/includes
 */

/**
 * Register all actions and filters for the plugin.
 *
 * Maintain a list of all hooks that are registered throughout
 * the plugin, and register them with the WordPress API. Call the
 * run function to execute the list of actions and filters.
 *
 * @package    Velocity_Addons
 * @subpackage Velocity_Addons/includes
 * @author     Velocity <bantuanvelocity@gmail.com>
 */

 class Velocity_Addons_Captcha {

    /**
    * Sitekey reCaptcha v2
    */
    private $sitekey;
        
    /**
    * Secretkey reCaptcha v2
    */
    private $secretkey;

    /**
    * data-size reCaptcha v2
    * compact, normal
    */
    private $size;

    private $active = false;

    public function __construct() {
        $captcha_velocity   = get_option('captcha_velocity',[]);
        $captcha_aktif      = isset($captcha_velocity['aktif'])?$captcha_velocity['aktif']:'';
        $this->sitekey      = isset($captcha_velocity['sitekey'])?$captcha_velocity['sitekey']:'';
        $this->secretkey    = isset($captcha_velocity['secretkey'])?$captcha_velocity['secretkey']:'';
        $this->size         = wp_is_mobile()?'compact':'normal';

        if($captcha_aktif && $this->sitekey && $this->secretkey) {

            $this->active = true;

            // Tambahkan filter timeout
            add_filter( 'http_request_timeout', function( $timeout ) { return 60; });

            // Tambahkan action captcha ke login_form (skip jika addon UM reCAPTCHA aktif untuk hindari duplikasi)
            if ( ! defined('UM_RECAPTCHA_VERSION') ) {
                add_action('login_form', array($this, 'display'));
                add_action('login_form_middle', array($this, 'display_login_form'));
            }

            // Tambahkan Filter Auth untuk captcha (hanya jika UM tidak aktif)
            if( ! function_exists('UM') ){
                add_filter( 'wp_authenticate_user', array($this, 'verify_login_form'), 10, 3 );
            }

            // Panggil fungsi untuk menambahkan reCaptcha ke kolom komentar
            add_action('comment_form_after_fields', array($this, 'display')); 
            // Panggil fungsi untuk memvalidasi reCaptcha saat proses submit komentar
            add_action('pre_comment_on_post', array($this, 'verify_comment_form'), 10, 1);

            // Panggil fungsi untuk menambahkan reCaptcha ke kolom lostpassword (skip jika addon UM reCAPTCHA aktif)
            if ( ! defined('UM_RECAPTCHA_VERSION') ) {
                add_action('lostpassword_form', array($this, 'display'));
                // Validasi lostpassword gunakan filter resmi agar kompatibel dengan WP
                add_filter('lostpassword_errors', array($this, 'lostpassword_errors'), 10, 1);
            }

            // Panggil fungsi untuk menambahkan reCaptcha ke kolom register (skip jika addon UM reCAPTCHA aktif)
            if ( ! defined('UM_RECAPTCHA_VERSION') ) {
                add_action('register_form', array($this, 'display'));
                add_action('signup_extra_fields', array($this, 'display'));
                add_filter('registration_errors', array($this, 'verify_register_form'), 10, 3);
            }

            if (class_exists('WPCF7') ){
                add_action('wpcf7_init', array($this, 'wpcf7_form_captcha'));
            }
            
            add_shortcode('velocity_recaptcha', array($this, 'display_login_form'));

            // Integrasi dengan Ultimate Member (UM) kecuali jika addon resmi UM reCAPTCHA aktif
            if ( function_exists('UM') && ! class_exists('UM_ReCAPTCHA') && ! defined('UM_RECAPTCHA_VERSION') ) {
                // Tampilkan captcha di UM login, register & password reset
                add_action('um_after_login_fields', array($this, 'um_display_captcha'), 500, 1);
                add_action('um_after_register_fields', array($this, 'um_display_captcha'), 500, 1);
                add_action('um_after_password_reset_fields', array($this, 'um_display_captcha'), 500, 1);
                // Tempatkan captcha di form Change Password pada hook khusus
                add_action('um_change_password_form', array($this, 'um_display_captcha'), 500, 1);
                // Validasi login/register via hook umum (sesuai addon resmi)
                add_action('um_submit_form_errors_hook', array($this, 'um_verify_um_form'), 20, 2);
                // Validasi UM reset password
                add_action('um_reset_password_errors_hook', array($this, 'um_verify_password_reset'), 10, 2);
            }
            // Validasi UM change password (Account > Password)
            add_action('um_change_password_errors_hook', array($this, 'um_verify_change_password'), 10, 1);

            // Setelah semua plugin aktif, sesuaikan hook bila addon UM reCAPTCHA aktif
            add_action('plugins_loaded', array($this, 'maybe_detach_for_um_recaptcha'), 20);
        }
        
    }

    /**
     * Jika addon UM reCAPTCHA aktif, hindari duplikasi dengan menonaktifkan hook kita
     * Menghormati pengaturan addon UM untuk halaman wp-login/wp-register/wp-lostpassword
     */
    public function maybe_detach_for_um_recaptcha(){
        if( function_exists('UM') && ( class_exists('UM_ReCAPTCHA') || defined('UM_RECAPTCHA_VERSION') ) ){
            // Lepas hooks di UM forms (render & validasi) agar tidak double
            remove_action('um_after_login_fields', array($this, 'um_display_captcha'), 500);
            remove_action('um_after_register_fields', array($this, 'um_display_captcha'), 500);
            remove_action('um_after_password_reset_fields', array($this, 'um_display_captcha'), 500);
            remove_action('um_change_password_form', array($this, 'um_display_captcha'), 500);
            remove_action('um_submit_form_errors_hook', array($this, 'um_verify_um_form'), 20);
            remove_action('um_reset_password_errors_hook', array($this, 'um_verify_password_reset'), 10);
            remove_action('um_change_password_errors_hook', array($this, 'um_verify_change_password'), 10);

            // WP core pages: baca setting addon UM, jika aktif pada halaman tertentu maka lepas hook kita
            $um_login = UM()->options()->get( 'g_recaptcha_wp_login_form' );
            $um_lost  = UM()->options()->get( 'g_recaptcha_wp_lostpasswordform' );
            $um_reg   = UM()->options()->get( 'g_recaptcha_wp_register_form' );

            if( $um_login ){
                remove_action('login_form', array($this, 'display'));
                remove_action('login_form_middle', array($this, 'display_login_form'));
                remove_filter('wp_authenticate_user', array($this, 'verify_login_form'), 10);
            }
            if( $um_lost ){
                remove_action('lostpassword_form', array($this, 'display'));
                remove_action('lostpassword_post', array($this, 'lostpassword_post'));
            }
            if( $um_reg ){
                remove_action('register_form', array($this, 'display'));
                remove_action('signup_extra_fields', array($this, 'display'));
                remove_filter('registration_errors', array($this, 'verify_register_form'), 10);
            }
        } elseif ( function_exists('UM') ) {
            // Jika UM aktif tapi addon reCAPTCHA tidak, pastikan tidak bentrok dengan auth filter WP
            remove_filter('wp_authenticate_user', array($this, 'verify_login_form'), 10);
        }
    }

    public function wpcf7_form_captcha(){
        wpcf7_add_form_tag('velocity_captcha', array($this, 'wpcf7_display_captcha'));
    }
    public function wpcf7_display_captcha(){
        ob_start();
        echo $this->display();
        return ob_get_clean();
    }

    public function isActive(){ 
        return $this->active;
    }

    public function display(){
        if($this->active){
            $node = 'rr'.uniqid();
            echo '<div class="'.$node.'">';
                echo '<div id="g'.$node.'" data-size="'.$this->size.'" style="transform: scale(0.9);transform-origin: 0 0;"></div>';
                ?>
                <script type="text/javascript">
                    function onloadCallback<?php echo $node;?>() {
                        grecaptcha.render('g<?php echo $node;?>', {
                            'sitekey' : '<?php echo $this->sitekey;?>',
                            'callback': callback<?php echo $node;?>,
                            'expired-callback' : expired<?php echo $node;?>
                        });
                    };
                    function callback<?php echo $node;?>() {
                        if (typeof jQuery !== 'undefined') {
                            (function ($) {
                                var form = $('.<?php echo $node;?>').parent().closest('form');
                                form.find('input[type="submit"]').attr('disabled', false).addClass('um-has-recaptcha');
                                form.find('button[type="submit"]').attr('disabled', false).addClass('um-has-recaptcha');
                            })(jQuery);
                        }
                    };
                    if (typeof jQuery !== 'undefined') {
                        (function ($) {
                            $( document ).ready(function() {
                                var form = $('.<?php echo $node;?>').parent().closest('form');
                                form.find('input[type="submit"]').attr('disabled', 'disabled').addClass('um-has-recaptcha');
                                form.find('button[type="submit"]').attr('disabled', 'disabled').addClass('um-has-recaptcha');
                            });
                        })(jQuery);
                    }
                    function expired<?php echo $node;?>() {
                        alert('Captcha Kadaluarsa, silahkan refresh halaman');
                    };
                </script>
                <?php
                echo '<script src="https://www.google.com/recaptcha/api.js?onload=onloadCallback'.$node.'&render=explicit" async defer></script>'; 
            echo '</div>';
        }
    }

    public function verify($gresponse = null){        
        if($this->active){

            $gresponse = $gresponse?$gresponse:'0';
            if(empty($gresponse) && isset($_POST['g-recaptcha-response'])){
                $gresponse = $_POST['g-recaptcha-response'];
            }

            $result = [
                'success' => false,
                'message' => 'Harap validasi captcha yang ada',
            ];
            
            if($gresponse){
                // Gunakan POST & tangani error jaringan agar tidak mengunci login ketika koneksi gagal
                $remote_ip = isset($_SERVER['REMOTE_ADDR']) ? sanitize_text_field($_SERVER['REMOTE_ADDR']) : '';
                $response = wp_remote_post( 'https://www.google.com/recaptcha/api/siteverify', [
                    'timeout' => 15,
                    'body'    => [
                        'secret'   => $this->secretkey,
                        'response' => $gresponse,
                        'remoteip' => $remote_ip,
                    ],
                ] );

                if ( is_wp_error( $response ) ) {
                    // Jika gagal koneksi ke Google, jangan memblokir proses (longgar agar form tetap bisa jalan)
                    $result = [
                        'success' => true,
                        'message' => 'Lewati verifikasi captcha (koneksi gagal)',
                    ];
                } else {
                    $code = wp_remote_retrieve_response_code( $response );
                    $body = wp_remote_retrieve_body( $response );
                    $decoded = json_decode( $body, true );
                    if ( 200 === (int) $code && is_array($decoded) && !empty($decoded['success']) ) {
                        $result = [
                            'success' => true,
                            'message' => 'Validasi captcha berhasil',
                        ];
                    } else {
                        $result = [
                            'success' => false,
                            'message' => 'Captcha salah',
                        ];
                    }
                }
            }
            
        } else {
            $result = [
                'success' => true,
                'message' => 'Validasi captcha tidak aktif',
            ];
        }

        return $result;
    }
    
    public function verify_login_form($user, $password){

        // Periksa apakah reCaptcha valid saat proses login
        $respon = isset($_POST['g-recaptcha-response'])?$_POST['g-recaptcha-response']:'0';
        $verify = $this->verify($respon);
        
        if (!$verify['success']) {
            // Jika reCaptcha tidak valid, hentikan proses login
            remove_action('authenticate', 'wp_authenticate_username_password', 20);
            // wp_die('reCaptcha verification failed. Please try again.');
            return new WP_Error( 'Captcha Invalid', __($verify['message']) );
        } else {            
            return $user;
        }

    }

    public function verify_comment_form($comment_data) {
        // Periksa apakah reCaptcha valid saat proses submit komentar
        $verify = $this->verify($_POST['g-recaptcha-response']);
        
        if (!$verify['success']) {
            // Jika reCaptcha tidak valid, hentikan proses submit komentar
            wp_die($verify['message']);
        }
        
        return $comment_data;
    }

    public function lostpassword_errors( $errors ){
        if ( is_wp_error( $errors ) && !is_user_logged_in() ) {
            $g = isset($_POST['g-recaptcha-response']) ? sanitize_textarea_field( wp_unslash($_POST['g-recaptcha-response']) ) : '';
            $verify = $this->verify($g);
            if ( ! $verify['success'] ) {
                $errors->add('recaptcha_error', __($verify['message']));
            }
        }
        return $errors;
    }

    public function verify_register_form($errors, $sanitized_user_login, $user_email){
        // Selalu wajibkan reCAPTCHA pada register bawaan WP
        $g = isset($_POST['g-recaptcha-response']) ? sanitize_text_field( wp_unslash($_POST['g-recaptcha-response']) ) : '';
        $verify = $this->verify($g);
        if ( ! $verify['success'] ) {
            $errors->add('recaptcha_error', __($verify['message']));
        }
        return $errors;
    }
    
    public function display_login_form(){
        if($this->active){
            $html = '<div>';
                $html .= '<div class="g-recaptcha" data-sitekey="'.$this->sitekey.'"></div>';
                $html .= '<script src="https://www.google.com/recaptcha/api.js" async defer></script>'; 
            $html .= '</div>';

            return $html;

        } else {
            return '';
        }
    }

    // ====== Ultimate Member Integration ======
    public function um_display_captcha( $args = array() ){
        if( ! $this->active ) return;
        $mode = isset($args['mode']) ? $args['mode'] : '';
        if( empty($mode) && function_exists('UM') ){
            // gunakan mode dari UM fields jika tersedia (contoh: change password)
            if( isset( UM()->fields()->set_mode ) ){
                $mode = UM()->fields()->set_mode;
            }
        }
        if( in_array( $mode, array('login','register','password'), true ) ){
            echo $this->display();
        }
    }

    public function um_verify_um_form( $submitted_data, $form_data ){
        if( ! $this->active ) return;
        $mode = isset($form_data['mode']) ? $form_data['mode'] : '';
        if( ! in_array( $mode, array('login','register'), true ) ) return;
        $respon = isset($_POST['g-recaptcha-response']) ? $_POST['g-recaptcha-response'] : '';
        $verify = $this->verify($respon);
        if( ! $verify['success'] ){
            if( function_exists('UM') ){
                UM()->form()->add_error( 'recaptcha', __($verify['message']) );
            }
        }
    }

    public function um_verify_password_reset( $submitted_data, $form_data ){
        if( ! $this->active ) return;
        $respon = isset($_POST['g-recaptcha-response']) ? $_POST['g-recaptcha-response'] : '';
        $verify = $this->verify($respon);
        if( ! $verify['success'] ){
            if( function_exists('UM') ){
                UM()->form()->add_error( 'recaptcha', __($verify['message']) );
            }
        }
    }

    public function um_verify_change_password( $submitted_data ){
        if( ! $this->active ) return;
        $respon = isset($_POST['g-recaptcha-response']) ? $_POST['g-recaptcha-response'] : '';
        $verify = $this->verify($respon);
        if( ! $verify['success'] ){
            if( function_exists('UM') ){
                UM()->form()->add_error( 'recaptcha', __($verify['message']) );
            }
        }
    }

 }

 $captcha_handler = new Velocity_Addons_Captcha();
