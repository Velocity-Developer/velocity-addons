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

    public function __construct() {
        $captcha_velocity   = get_option('captcha_velocity',[]);
        $captcha_aktif      = isset($captcha_velocity['aktif'])?$captcha_velocity['aktif']:'';
        $this->sitekey      = isset($captcha_velocity['sitekey'])?$captcha_velocity['sitekey']:'';
        $this->secretkey    = isset($captcha_velocity['secretkey'])?$captcha_velocity['secretkey']:'';
        $this->size         = wp_is_mobile()?'compact':'normal';

        if($captcha_aktif && $this->sitekey && $this->secretkey) {

            // Tambahkan action captcha ke login_form
            add_action('login_form', array($this, 'display'));
            // Tambahkan Filter Auth untuk captcha
            add_filter( 'wp_authenticate_user', array($this, 'verify_login_form'), 10, 3 );

            // Panggil fungsi untuk menambahkan reCaptcha ke kolom komentar
            add_action('comment_form_after_fields', array($this, 'display')); 
            // Panggil fungsi untuk memvalidasi reCaptcha saat proses submit komentar
            add_action('pre_comment_on_post', array($this, 'verify_comment_form'), 10, 1);

            if (class_exists('WPCF7') ){
                add_action('wpcf7_init', array($this, 'wpcf7_form_captcha'));
            }
        }
        
    }

    public function wpcf7_form_captcha(){
        wpcf7_add_form_tag('velocity_captcha', array($this, 'wpcf7_display_captcha'));
    }

    public function display(){
        if($this->sitekey && $this->secretkey){
            wp_enqueue_script( 'g-recaptcha', 'https://www.google.com/recaptcha/api.js', array(), 1, true );
            echo '<div class="g-recaptcha" data-sitekey="'.$this->sitekey.'" data-size="'.$this->size.'" style="transform: scale(0.9);transform-origin: 0 0;">Load reCaptcha</div>';
        }
    }

    public function verify($gresponse = null){        
        
        if($this->sitekey && $this->secretkey){

            $gresponse = $gresponse?$gresponse:$_POST['g-recaptcha-response'];

            $result = [
                'success' => false,
                'message' => 'Harap validasi captcha yang ada',
            ];
            
            if($gresponse){
                $response = wp_remote_get( 'https://www.google.com/recaptcha/api/siteverify?secret='.$this->secretkey.'&response=' . $gresponse );
                $response = json_decode($response['body'], true); 

                if (true == $response['success']) {
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
        $verify = $this->verify($_POST['g-recaptcha-response']);
        
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


 }

 $captcha_handler = new Velocity_Addons_Captcha();