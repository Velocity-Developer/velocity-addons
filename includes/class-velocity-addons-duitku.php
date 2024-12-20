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

    public function __construct()
    {    
        // Menambahkan submenu
        add_action('admin_init', [$this, 'register_settings']);
    }
    
    public function register_settings()
    {
        register_setting('velocity_duitku_group', 'velocity_duitku_options');
    }
    
    public static function options() {        
        $options = get_option('velocity_duitku_options');

        $data = array(
            'mode'          => 'sandbox',
            'kode_merchant' => '',
            'merchant_key'  => '',
            'callback_url'  => '',
            'return_url'    => '',
        );

        if($data['mode'] == 'sandbox') {
            $data['url_createinvoice'] = 'https://api-sandbox.duitku.com/api/merchant/createinvoice'; 
        } else {
            $data['url_createinvoice'] = 'https://api-prod.duitku.com/api/merchant/createinvoice';
        }
        
        return wp_parse_args( $options, $data);
    }

    public static function render_settings_page() {

        $mode = self::options()['mode'];
        $kode_merchant = self::options()['kode_merchant'];
        $merchant_key = self::options()['merchant_key'];
        $callback_url = self::options()['callback_url'];
        $return_url = self::options()['return_url'];

        ?>
        <div class="wrap">
            
            <h2>Pengaturan DUITKU</h2>
            <h4>Pengaturan akun payment gateway Duitku</h4>
            
            <form method="post" action="options.php">
                <?php                   
                settings_fields('velocity_duitku_group');
                do_settings_sections('velocity_duitku_settings');
                ?>
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

        </div>
        <?php
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
    public function createInvoice($params) {

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
            'expiryPeriod'
        );

        // Array untuk menyimpan pesan error
        $missing_params = array();

        // Cek apakah ada parameter yang belum diisi
        foreach ($required_params as $param) {
            if (empty($params[$param])) {
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
         $merchantCode = self::options()['merchant_code'];

         // Headers untuk request
        $headers = array(
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
            'sslverify' => false,  // Menonaktifkan SSL verification (untuk sandbox)
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
            return $result;  // Mengembalikan hasil response
        } else {
            // Jika status code bukan 200, return error
            return new WP_Error('request_error', 'Request gagal dengan status code: ' . $http_code . ' - ' . wp_remote_retrieve_body($response));
        }

    }

}

// Inisialisasi class Velocity_Addons_Duitku
$velocity_duitku = new Velocity_Addons_Duitku();
// $velocity_news->autoload();