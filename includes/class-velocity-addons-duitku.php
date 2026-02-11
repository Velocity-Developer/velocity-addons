<?php

/**
 * Register Duiku settings in the WordPress admin panel
 *
 * @link       https://velocitydeveloper.com
 * @since      1.0.0
 *
 * @package    Velocity_Addons
 * @subpackage Velocity_Addons/includes
 */

class Velocity_Addons_Duitku {
    
    public $wpdb;
    public $tb_invoice;
    public $tb_callback;

    public function __construct()
    {    
        global $wpdb;
        $this->wpdb = $wpdb;
        $this->tb_invoice = $wpdb->prefix . 'vd_duitku_invoice';
        $this->tb_callback = $wpdb->prefix . 'vd_duitku_callback';
    }

    public function init()
    {
        // Menambahkan submenu
        add_action('admin_init', [$this, 'admin_init']);
        add_shortcode( 'tombol_bayar_duitku', [$this, 'tombol_bayar'] );
        add_action('rest_api_init',[$this,'register_rest']);
    }
    
    public function admin_init()
    {
        if(get_option('velocity_duitku', '0') == '1') {
            register_setting('velocity_duitku_group', 'velocity_duitku_options');
            $this->create_tables();
        }
    }

    public function create_tables()
    {
        if (get_option('velocity_db_duitku_invoice', 1) < 12) {
            global $wpdb;
            update_option('velocity_db_duitku_invoice', 13);
            require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

            $sql = "CREATE TABLE IF NOT EXISTS $this->tb_invoice 
            (
                id INT UNSIGNED NOT NULL AUTO_INCREMENT,
                invoice varchar(255) NOT NULL,
                merchant_code varchar(114) NOT NULL,
                reference varchar(255) NOT NULL,
                payment_url text NOT NULL,
                status_code varchar(5) NOT NULL,
                status_message varchar(5) NOT NULL,
                created_at varchar(114) NOT NULL,
                PRIMARY KEY  (id)
            );
            ";
            dbDelta($sql);
            
            ///tambahkan kolom ke tabel invoice
            $sql = "ALTER TABLE $this->tb_invoice
            ADD COLUMN update_at varchar(114) NOT NULL";
            $wpdb->query($sql);

            //ubah panjang kolom status_message
            $sql = "ALTER TABLE $this->tb_invoice
            MODIFY COLUMN status_message varchar(255) NOT NULL";
            $wpdb->query($sql);
            
            ///tambahkan kolom ke tabel invoice
            $sql = "ALTER TABLE $this->tb_invoice
            ADD COLUMN amount varchar(114) DEFAULT NULL";
            $wpdb->query($sql);

            //buat table callback
            $sql = "CREATE TABLE IF NOT EXISTS $this->tb_callback 
            (
                id INT UNSIGNED NOT NULL AUTO_INCREMENT,
                invoice varchar(255) NOT NULL,
                merchant_code varchar(114) NOT NULL,
                amount varchar(255) NOT NULL,
                payment_code varchar(255) NOT NULL,
                result_code varchar(255) NOT NULL,
                reference varchar(255) NOT NULL,
                created_at varchar(114) NOT NULL,
                update_at varchar(114) NOT NULL,
                detail text DEFAULT NULL,
                PRIMARY KEY  (id)
            );
            ";
            dbDelta($sql);

        }
    }

    public static function payment_methods($kode=null) {

        $paymentMethods = [
            "VC" => [
                "jenis" => "Credit Card",
                "keterangan" => "(Visa / Master Card / JCB)"
            ],
            "BC" => [
                "jenis" => "Virtual Account",
                "keterangan" => "BCA Virtual Account"
            ],
            "M2" => [
                "jenis" => "Virtual Account",
                "keterangan" => "Mandiri Virtual Account"
            ],
            "VA" => [
                "jenis" => "Virtual Account",
                "keterangan" => "Maybank Virtual Account"
            ],
            "I1" => [
                "jenis" => "Virtual Account",
                "keterangan" => "BNI Virtual Account"
            ],
            "B1" => [
                "jenis" => "Virtual Account",
                "keterangan" => "CIMB Niaga Virtual Account"
            ],
            "BT" => [
                "jenis" => "Virtual Account",
                "keterangan" => "Permata Bank Virtual Account"
            ],
            "A1" => [
                "jenis" => "Virtual Account",
                "keterangan" => "ATM Bersama"
            ],
            "AG" => [
                "jenis" => "Virtual Account",
                "keterangan" => "Bank Artha Graha"
            ],
            "NC" => [
                "jenis" => "Virtual Account",
                "keterangan" => "Bank Neo Commerce/BNC"
            ],
            "BR" => [
                "jenis" => "Virtual Account",
                "keterangan" => "BRIVA"
            ],
            "S1" => [
                "jenis" => "Virtual Account",
                "keterangan" => "Bank Sahabat Sampoerna"
            ],
            "DM" => [
                "jenis" => "Virtual Account",
                "keterangan" => "Danamon Virtual Account"
            ],
            "BV" => [
                "jenis" => "Virtual Account",
                "keterangan" => "BSI Virtual Account"
            ],
            "FT" => [
                "jenis" => "Ritel",
                "keterangan" => "Pegadaian/ALFA/Pos"
            ],
            "IR" => [
                "jenis" => "Ritel",
                "keterangan" => "Indomaret"
            ],
            "OV" => [
                "jenis" => "E-Wallet",
                "keterangan" => "OVO (Support Void)"
            ],
            "SA" => [
                "jenis" => "E-Wallet",
                "keterangan" => "ShopeePay Apps (Support Void)"
            ],
            "LF" => [
                "jenis" => "E-Wallet",
                "keterangan" => "LinkAja Apps (Fixed Fee)"
            ],
            "LA" => [
                "jenis" => "E-Wallet",
                "keterangan" => "LinkAja Apps (Percentage Fee)"
            ],
            "DA" => [
                "jenis" => "E-Wallet",
                "keterangan" => "DANA"
            ],
            "SL" => [
                "jenis" => "E-Wallet",
                "keterangan" => "ShopeePay Account Link"
            ],
            "OL" => [
                "jenis" => "E-Wallet",
                "keterangan" => "OVO Account Link"
            ],
            "JP" => [
                "jenis" => "E-Wallet",
                "keterangan" => "Jenius Pay"
            ],
            "SP" => [
                "jenis" => "QRIS",
                "keterangan" => "ShopeePay"
            ],
            "LQ" => [
                "jenis" => "QRIS",
                "keterangan" => "LinkAja"
            ],
            "NQ" => [
                "jenis" => "QRIS",
                "keterangan" => "Nobu"
            ],
            "DQ" => [
                "jenis" => "QRIS",
                "keterangan" => "Dana"
            ],
            "GQ" => [
                "jenis" => "QRIS",
                "keterangan" => "Gudang Voucher"
            ],
            "SQ" => [
                "jenis" => "QRIS",
                "keterangan" => "Nusapay"
            ],
            "DN" => [
                "jenis" => "Credit",
                "keterangan" => "Indodana Paylater"
            ],
            "AT" => [
                "jenis" => "Credit",
                "keterangan" => "ATOME"
            ]
        ];

        return $kode?$paymentMethods[$kode]:$paymentMethods;

    }
    
    public static function options() {        
        $options = get_option('velocity_duitku_options');

        $data = array(
            'mode'          => 'sandbox',
            'kode_merchant' => '',
            'merchant_key'  => '',
            'callback_url'  => get_site_url().'/wp-json/velocityaddons/v1/duitku_callback',
            'return_url'    => '',
        );

        $data = wp_parse_args( $options, $data);

        if($data['mode'] == 'sandbox') {
            $data['url_createinvoice'] = 'https://api-sandbox.duitku.com/api/merchant/createinvoice'; 
        } else {
            $data['url_createinvoice'] = 'https://api-prod.duitku.com/api/merchant/createinvoice';
        }
        
        return $data;
    }

    public static function render_settings_page() {

        $mode = self::options()['mode'];
        $kode_merchant = self::options()['kode_merchant'];
        $merchant_key = self::options()['merchant_key'];
        $callback_url = self::options()['callback_url'];
        $return_url = self::options()['return_url'];

        $url = get_admin_url().'admin.php?page=velocity_duitku_settings';
        $tab_active = isset($_GET['tab'])?$_GET['tab']:'pengaturan';

        ?>
        <div class="wrap">
            
            <h2>Payment Gateway DUITKU</h2>

            <div class="nav-tab-wrapper">
                <a href="<?php echo $url; ?>&tab=pengaturan" class="<?php echo $tab_active=='pengaturan'?'nav-tab nav-tab-active':'nav-tab'; ?>">
                    Pengaturan
                </a>
                <a href="<?php echo $url; ?>&tab=invoice" class="<?php echo $tab_active=='invoice'?'nav-tab nav-tab-active':'nav-tab'; ?>">
                    Riwayat Invoice
                </a>
                <a href="<?php echo $url; ?>&tab=callback" class="<?php echo $tab_active=='callback'?'nav-tab nav-tab-active':'nav-tab'; ?>">
                    Riwayat Callback
                </a>
            </div>
            
            <?php if($tab_active == 'pengaturan'): ?>
                <h4>Pengaturan akun payment gateway Duitku</h4>
                <form method="post" data-velocity-settings="1">
                    <table class="form-table">
                        <tr valign="top">
                            <th scope="row">Mode</th>
                            <td>                            
                                <select name="velocity_duitku_options[mode]">
                                    <option value="sandbox" <?php selected($mode, 'sandbox'); ?>>Sandbox</option>
                                    <option value="production" <?php selected($mode, 'production'); ?>>Production</option>
                                </select> <br>
                                <small for="mode">Pilih mode "sandbox" atau "production", sandbox untuk testing/uji coba </small>
                            </td>
                        </tr>
                        <tr valign="top">
                            <th scope="row">Kode Merchant</th>
                            <td>
                                <input class="regular-text" type="text" name="velocity_duitku_options[kode_merchant]" value="<?php echo esc_attr($kode_merchant); ?>" placeholder="XXXXXXX" /><br/>
                                <small for="kode_merchant">Ambil dari proyek Duitku, di halaman "Proyek Saya" </small>
                            </td>
                        </tr>
                        <tr valign="top">
                            <th scope="row">API Key (Merchant Key)</th>
                            <td>
                                <input class="regular-text" type="text" name="velocity_duitku_options[merchant_key]" value="<?php echo esc_attr($merchant_key); ?>" placeholder="XXXXXXX" /><br/>
                                <small for="merchant_key">Ambil dari proyek Duitku, di halaman "Proyek Saya" </small>
                            </td>
                        </tr>
                        <tr valign="top">
                            <th scope="row">Callback Url</th>
                            <td>
                                <input class="regular-text" type="text" name="velocity_duitku_options[callback_url]" value="<?php echo esc_attr($callback_url); ?>" placeholder="http://example.com/api-pop/backend/callback.php" /><br/>
                                <small for="callback_url">URL untuk transaksi callback.</small>
                            </td>
                        </tr>
                        <tr valign="top">
                            <th scope="row">Return Url</th>
                            <td>
                                <input class="regular-text" type="text" name="velocity_duitku_options[return_url]" value="<?php echo esc_attr($return_url); ?>" placeholder="http://example.com/api-pop/backend/redirect.php" /><br/>
                                <small for="return_url">URL untuk redirect apabila transaksi telah selesai atau dibatalkan..</small>
                            </td>
                        </tr>
                    </table>
                    <?php submit_button(); ?>
                </form>
            <?php elseif($tab_active == 'invoice'): ?>
                <?php echo self::table_riwayat_invoice(); ?>
            <?php elseif($tab_active == 'callback'): ?>
                <?php echo self::table_riwayat_callback(); ?>
            <?php endif; ?>

            <div class="alert">
                Default callback url: <code><?php echo get_site_url().'/wp-json/velocityaddons/v1/duitku_callback'; ?></code> 
            </div>

        </div>
        <?php
    }

    public static function is_active() {
        if(get_option('velocity_duitku', '1') == '1' && self::options()['kode_merchant'] && self::options()['merchant_key']) {
            return true;
        } else {
            return false;
        }
    }

    // fungsi untuk membuat timestamp milidetik zona waktu Jakarta
    public function timestamp() {
        
        // Set zona waktu Jakarta
        date_default_timezone_set('Asia/Jakarta');

        return round(microtime(true) * 1000);
    }

    // Fungsi untuk membuat signature
    public function signature() {
        return hash('sha256', self::options()['kode_merchant'].$this->timestamp().self::options()['merchant_key']);
    }

    /**
	 * Create Invoice Duitku Pop
	 *
	 * Example:
     * $paymentAmount      = 10000; // Amount
	 * $email              = "customer@gmail.com"; // your customer email
	 * $phoneNumber        = "081234567890"; // your customer phone number (optional)
	 * $productDetails     = "Test Payment";
	 * $merchantOrderId    = time(); // from merchant, unique   
	 * $additionalParam    = ''; // optional
	 * $merchantUserInfo   = ''; // optional
	 * $customerVaName     = 'John Doe'; // display name on bank confirmation display
	 * $callbackUrl        = 'http://YOUR_SERVER/callback'; // url for callback
	 * $returnUrl          = 'http://YOUR_SERVER/return'; // url for redirect
	 * $expiryPeriod       = 60; // set the expired time in minutes
	 * 
	 * // Customer Detail
	 * $firstName          = "John";
	 * $lastName           = "Doe";
	 * 
	 * // Address
	 * $alamat             = "Jl. Kembangan Raya";
	 * $city               = "Jakarta";
	 * $postalCode         = "11530";
	 * $countryCode        = "ID";
	 * 
	 * $address = array(
	 * 	'firstName'     => $firstName,
	 * 	'lastName'      => $lastName,
	 * 	'address'       => $alamat,
	 * 	'city'          => $city,
	 * 	'postalCode'    => $postalCode,
	 * 	'phone'         => $phoneNumber,
	 * 	'countryCode'   => $countryCode
	 * );
	 * 
	 * $customerDetail = array(
	 * 	'firstName'         => $firstName,
	 * 	'lastName'          => $lastName,
	 * 	'email'             => $email,
	 * 	'phoneNumber'       => $phoneNumber,
	 * 	'billingAddress'    => $address,
	 * 	'shippingAddress'   => $address
	 * );
	 * 
	 * // Item Details
	 * $item1 = array(
	 * 	'name'      => $productDetails,
	 * 	'price'     => $paymentAmount,
	 * 	'quantity'  => 1
	 * );
	 * 
	 * $itemDetails = array(
	 * 	$item1
	 * );
	 * 
	 * $params = array(
	 * 	'paymentAmount'     => $paymentAmount,
	 * 	'merchantOrderId'   => $merchantOrderId,
	 * 	'productDetails'    => $productDetails,
	 * 	'additionalParam'   => $additionalParam,
	 * 	'merchantUserInfo'  => $merchantUserInfo,
	 * 	'customerVaName'    => $customerVaName,
	 * 	'email'             => $email,
	 * 	'phoneNumber'       => $phoneNumber,
	 * 	'itemDetails'       => $itemDetails,
	 * 	'customerDetail'    => $customerDetail,
	 * 	'callbackUrl'       => $callbackUrl,
	 * 	'returnUrl'         => $returnUrl,
	 * 	'expiryPeriod'      => $expiryPeriod
	 * );
    */
    public function createInvoice($params = null) {

        if(empty($params)) {
            return new WP_Error('invalid_params', 'Parameter tidak valid');
        }

        if(!isset($params['callbackUrl']) || isset($params['callbackUrl']) && empty($params['callbackUrl'])) {
            $params['callbackUrl'] = self::options()['callback_url'];
        }
        if(!isset($params['returnUrl']) || isset($params['returnUrl']) && empty($params['returnUrl'])) {
            $params['returnUrl'] = self::options()['return_url'];
        }

        // Daftar parameter yang wajib diisi
        $required_params = array(
            'paymentAmount', 
            'merchantOrderId', 
            'productDetails', 
            'additionalParam', 
            'merchantUserInfo', 
            'customerVaName', 
            'email', 
            'phoneNumber', 
            'itemDetails', 
            'customerDetail', 
            'callbackUrl', 
            'returnUrl', 
            // 'expiryPeriod'
        );

        // Array untuk menyimpan pesan error
        $missing_params = array();

        // Cek apakah ada parameter yang belum diisi
        foreach ($required_params as $param) {
            if (!isset($params[$param])) {
                $missing_params[] = $param;
            }
        }

        // Jika ada parameter yang belum diisi, kembalikan pesan error
        if (!empty($missing_params)) {
            $missing_params_list = implode(', ', $missing_params);
            return new WP_Error('missing_params', 'Parameter yang belum diisi: ' . $missing_params_list);
        }

        //ubah ke json parameters
        $params_string = json_encode($params);

         //kelengkapan headers
         $url = self::options()['url_createinvoice'];
         $signature = $this->signature();
         $timestamp = $this->timestamp();
         $merchantCode = self::options()['kode_merchant'];

         // Headers untuk request
        $headers = array(
            'Accept'              => 'application/json',
            'Content-Type'        => 'application/json',
            'Content-Length'      => strlen($params_string),
            'x-duitku-signature'  => $signature,
            'x-duitku-timestamp'  => $timestamp,
            'x-duitku-merchantcode' => $merchantCode,
        );

        // Melakukan request POST menggunakan wp_remote_post
        $response = wp_remote_post($url, array(
            'method'    => 'POST',
            'body'      => $params_string,
            'headers'   => $headers,
            'timeout'   => 15,  // Timeout (dalam detik)
            'sslverify' => true,  // Menonaktifkan SSL verification (untuk sandbox)
        ));

        // Mengecek apakah request berhasil
        if (is_wp_error($response)) {
            // Jika ada error pada request
            return new WP_Error('request_failed', 'Request API gagal: ' . $response->get_error_message());
        }

        // Mengambil status code HTTP dari response
        $http_code = wp_remote_retrieve_response_code($response);


        if ($http_code == 200) {
            // Jika status code 200, ambil body dari response
            $result = json_decode(wp_remote_retrieve_body($response), true);

            //simpan hasil invoice
            $save_request = $this->save_invoice($params['merchantOrderId'],$result);

            return $result;  // Mengembalikan hasil response
        } else {
            // Jika status code bukan 200, return error
            return new WP_Error('request_error', 'Request gagal dengan status code: ' . $http_code . ' - ' . wp_remote_retrieve_body($response));
        }

    }

    public function save_invoice($invoice,$result_request,$amount=null) {

        if(is_wp_error($result_request)) {
            return false;
        }

        //cek apakah invoice sudah ada
        $cek_invoice = $this->get_by_invoice($invoice);

        $amount = $result_request['amount'] ?? $amount;

        if($cek_invoice) {

            //update invoice
            $this->wpdb->update($this->tb_invoice, array(
                'invoice'           => $invoice,
                'merchant_code'     => $result_request['merchantCode'],
                'reference'         => $result_request['reference'],
                'payment_url'       => $result_request['paymentUrl'],
                'status_code'       => $result_request['statusCode'],
                'status_message'    => $result_request['statusMessage'],
                'update_at'         => date( 'Y-m-d H:i:s', current_time( 'timestamp', 1 ) ),
                'amount'            => $amount,
            ), array(
                'id' => $cek_invoice->id
            ));

            $id = $cek_invoice->id;

        } else {
        
            //insert invoice
            $this->wpdb->insert($this->tb_invoice, array(
                'invoice'           => $invoice,
                'merchant_code'     => $result_request['merchantCode'],
                'reference'         => $result_request['reference'],
                'payment_url'       => $result_request['paymentUrl'],
                'status_code'       => $result_request['statusCode'],
                'status_message'    => $result_request['statusMessage'],
                'created_at'        => date( 'Y-m-d H:i:s', current_time( 'timestamp', 1 ) ),
                'update_at'         => date( 'Y-m-d H:i:s', current_time( 'timestamp', 1 ) ),
                'amount'            => $amount,
            ));

            $id = $this->wpdb->insert_id;
            
        }
        
        return [
            'id'        => $id,
            'invoice'   => $invoice,
            'reference' => $result_request['reference'],
        ];

    }

    public function get_by_invoice($invoice) {

        $data = $this->wpdb->get_row("SELECT * FROM $this->tb_invoice WHERE invoice = '$invoice'");

        return $data;
    }

    public function tombol_bayar($atts) {
        ob_start();
        
        $atribut = shortcode_atts(array(
            'invoice'   => '',
            'class'     => 'btn btn-primary',
        ), $atts);
        $invoice    = $atribut['invoice'];
        $class      = $atribut['class'];

        //cek apakah invoice sudah ada
        $cek_invoice = $this->get_by_invoice($invoice);

        if(!$cek_invoice){

            echo '<div class="alert alert-danger">Data Duitku Invoice Tidak Ditemukan, silahkan request invoice terlebih dahulu</div>';

            return ob_get_clean();

        }

        $invoice    = $cek_invoice->invoice;
        $reference  = $cek_invoice->reference;
        $amount     = $cek_invoice->amount;

        //jika berhasil,        
        $mode = self::options()['mode'];
        $js = $mode=='sandbox' ? 'https://app-sandbox.duitku.com/lib/js/duitku.js' : 'https://app-prod.duitku.com/lib/js/duitku.js';

        ?>
            <button id="bayarduitku<?php echo $invoice; ?>" class="<?php echo $class; ?>">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-credit-card" viewBox="0 0 16 16">
                    <path d="M0 4a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2v8a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2zm2-1a1 1 0 0 0-1 1v1h14V4a1 1 0 0 0-1-1zm13 4H1v5a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1z"/>
                    <path d="M2 10a1 1 0 0 1 1-1h1a1 1 0 0 1 1 1v1a1 1 0 0 1-1 1H3a1 1 0 0 1-1-1z"/>
                </svg>
                Bayar Sekarang <?php echo $amount; ?>
            </button>
            <script src="<?php echo $js; ?>"></script>
            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    // Get the button element
                    const button = document.getElementById('bayarduitku<?php echo $invoice; ?>');

                    // Add a click event listener to the button
                    button.addEventListener('click', function() {
                        checkout.process("<?php echo $reference; ?>", {
                            defaultLanguage: "id", //opsional pengaturan bahasa
                            successEvent: function(result){
                                console.log('duitku success');
                                //sembunyikan tombol bayar
                                button.style.display = 'none';
                            },
                            pendingEvent: function(result){
                                console.log('duitku pending');
                            },
                            errorEvent: function(result){
                                console.log('duitkuerror');
                            },
                            closeEvent: function(result){
                                console.log('customer closed the popup without finishing the payment');
                            }
                        });
                    });
                });
            </script>
        <?php
        return ob_get_clean();
    }

    public function save_callback($post_callback) {

        $invoice = $post_callback['merchantOrderId'];

        //cek apakah callback berdasarkan invoice sudah ada
        $available = $this->wpdb->get_row("SELECT * FROM $this->tb_callback WHERE invoice = '$invoice'");
       
        if($available) {

            //update 
            $this->wpdb->update($this->tb_callback, array(                
                'invoice'           => $post_callback['merchantOrderId'],
                'merchant_code'     => $post_callback['merchantCode'],
                'amount'            => $post_callback['amount'],
                'payment_code'      => $post_callback['paymentCode'],
                'result_code'       => $post_callback['resultCode'],
                'reference'         => $post_callback['reference'],
                'detail'            => json_encode($post_callback),
                'update_at'         => date( 'Y-m-d H:i:s', current_time( 'timestamp', 1 ) ),
            ), array(
                'id' => $available->id
            ));

            $id = $available->id;

        } else {
        
            //insert invoice
            $this->wpdb->insert($this->tb_callback, array(
                'invoice'           => $post_callback['merchantOrderId'],
                'merchant_code'     => $post_callback['merchantCode'],
                'amount'            => $post_callback['amount'],
                'payment_code'      => $post_callback['paymentCode'],
                'result_code'       => $post_callback['resultCode'],
                'reference'         => $post_callback['reference'],
                'detail'            => json_encode($post_callback),
                'created_at'        => date( 'Y-m-d H:i:s', current_time( 'timestamp', 1 ) ),
                'update_at'         => date( 'Y-m-d H:i:s', current_time( 'timestamp', 1 ) ),
            ));

            $id = $this->wpdb->insert_id;
            
        }
        
        return [
            'id'        => $id,
            'invoice'   => $invoice,
            'reference' => $result_request['reference'],
        ];

    }
    
    /**
     * Callback function for duitku payment
     * 
     * This function will be called by duitku when the payment is finished
     * 
     * @return void
     */
    public function callback() {

        if(!isset($_POST) || !isset($_POST['merchantCode']) && !isset($_POST['signature'])) {
            return false;
        }

        $apiKey             = self::options()['merchant_key']; // API key anda
        $merchantCode       = isset($_POST['merchantCode']) ? $_POST['merchantCode'] : null; 
        $amount             = isset($_POST['amount']) ? $_POST['amount'] : null; 
        $merchantOrderId    = isset($_POST['merchantOrderId']) ? $_POST['merchantOrderId'] : null; 
        $productDetail      = isset($_POST['productDetail']) ? $_POST['productDetail'] : null; 
        $additionalParam    = isset($_POST['additionalParam']) ? $_POST['additionalParam'] : null; 
        $paymentCode        = isset($_POST['paymentCode']) ? $_POST['paymentCode'] : null; 
        $resultCode         = isset($_POST['resultCode']) ? $_POST['resultCode'] : null; 
        $merchantUserId     = isset($_POST['merchantUserId']) ? $_POST['merchantUserId'] : null; 
        $reference          = isset($_POST['reference']) ? $_POST['reference'] : null; 
        $signature          = isset($_POST['signature']) ? $_POST['signature'] : null; 
        $publisherOrderId   = isset($_POST['publisherOrderId']) ? $_POST['publisherOrderId'] : null; 
        $spUserHash         = isset($_POST['spUserHash']) ? $_POST['spUserHash'] : null; 
        $settlementDate     = isset($_POST['settlementDate']) ? $_POST['settlementDate'] : null; 
        $issuerCode         = isset($_POST['issuerCode']) ? $_POST['issuerCode'] : null; 

        if(!empty($merchantCode) && !empty($amount) && !empty($merchantOrderId) && !empty($signature)) {

            $params = $merchantCode . $amount . $merchantOrderId . $apiKey;
            $calcSignature = md5($params);

            if($signature == $calcSignature) {
                //Callback tervalidasi

                //simpan data callback
                $this->save_callback($_POST);

                //jalankan action
                do_action('velocity_duitku_callback', $_POST);

                return $_POST;
            } else {
                //Callback tidak tervalidasi
                return new WP_Error('missing_params', 'Bad Signature');
            }

        } else {
            //eror parameter tidak lengkap
            return new WP_Error('missing_params', 'Parameter tidak lengkap');
        }

    }

    //regsiter restapi endpoint
    public function register_rest(){
        register_rest_route('velocityaddons/v1', '/duitku_callback', array(
            'methods'               => 'POST',
            'callback'              => [$this,'rest_callback'],
        ));
    }

    //callback restapi
    public function rest_callback(WP_REST_Request $request){

        /// Pastikan ini adalah permintaan POST
        if ($request->get_method() !== 'POST') {
            return new WP_Error('method_not_allowed', 'Metode tidak diizinkan', array('status' => 405));
        }
        
        //jalankan callback
        $callback = $this->callback();

        return $callback;

    }

    public static function table_riwayat_invoice() {
        $instance = new self(); 
        $datas = $instance->wpdb->get_results("SELECT * FROM $instance->tb_invoice ORDER BY created_at DESC");

        if ($datas) {
           ?>
           <div style="padding: 1rem .05rem;">
            <table class="wp-list-table widefat fixed striped table-view-list">
                <thead>
                    <tr>
                        <th>Invoice</th>
                        <th>Reference</th>
                        <th>Mechant Code</th>
                        <th>Amount</th>
                        <th>Status Code</th>
                        <th>Status Message</th>
                        <th>Update At</th>
                        <th>Created At</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($datas as $data): ?>
                        <tr>
                            <td><?php echo $data->invoice; ?></td>
                            <td><?php echo $data->reference; ?></td>
                            <td><?php echo $data->merchant_code; ?></td>
                            <td><?php echo $data->amount; ?></td>
                            <td><?php echo $data->status_code; ?></td>
                            <td><?php echo $data->status_message; ?></td>
                            <td><?php echo $data->update_at; ?></td>
                            <td><?php echo $data->created_at; ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
           </div>
           <?php
        }
    }

    public static function table_riwayat_callback() {
        $instance = new self(); 
        $datas = $instance->wpdb->get_results("SELECT * FROM $instance->tb_callback");

        if ($datas) {
           ?>
           <div style="padding: 1rem .05rem;">
            <table class="wp-list-table widefat fixed striped table-view-list">
                <thead>
                    <tr>
                        <th>Invoice</th>
                        <th>Reference</th>
                        <th>Mechant Code</th>
                        <th>Amount</th>
                        <th>Payment Code</th>
                        <th>Result Code</th>
                        <th>Update At</th>
                        <th>Created At</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($datas as $data): ?>
                        <tr>
                            <td><?php echo $data->invoice; ?></td>
                            <td><?php echo $data->reference; ?></td>
                            <td><?php echo $data->merchant_code; ?></td>
                            <td><?php echo $data->amount; ?></td>
                            <td><?php echo $data->payment_code; ?></td>
                            <td><?php echo $data->result_code; ?></td>
                            <td><?php echo $data->update_at; ?></td>
                            <td><?php echo $data->created_at; ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
           </div>
           <?php
        }
    }


}

// Inisialisasi class Velocity_Addons_Duitku
$velocity_duitku = new Velocity_Addons_Duitku();
$velocity_duitku->init();
