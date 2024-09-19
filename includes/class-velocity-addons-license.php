<?php

class Velocity_Addons_License
{
    private $api_url;

    public function __construct()
    {
        $this->api_url = 'https://api.velocitydeveloper.id/wp-json/api/v1/license';

        // Schedule a weekly check for license status
        if (!wp_next_scheduled('check_license_status')) {
            wp_schedule_event(time(), 'weekly', 'check_license_status');
        }

        add_action('check_license_status', array($this, 'check_license'));
        add_action('wp_ajax_check_license', array($this, 'ajax_check_license'));
        add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts'));
    }

    public function activate_license($license_key)
    {
        $response = $this->send_request($license_key);
    
        if ($response && $response['success'] === true) {
            $this->store_license_data($response['data']['data']); // Akses data yang benar
            return true;
        } else {
            if ($response) {
                $this->handle_license_error($response['data']['message']); // Akses message yang benar
            } else {
                $this->handle_server_error();
            }
            return false;
        }
    }

    private function send_request($license_key)
    {
        $args = array(
            'headers' => array(
                'Content-Type' => 'application/json',
                'license_key' => $license_key,
            ),
        );
    
        $response = wp_remote_get($this->api_url, $args); // Ganti ke wp_remote_get
    
        if (is_wp_error($response)) {
            error_log('Error: ' . $response->get_error_message());
            return null; // Kembali jika ada error
        }
    
        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);
    
        if (json_last_error() !== JSON_ERROR_NONE) {
            error_log('JSON Decode Error: ' . json_last_error_msg());
            return null; // Kembali jika ada error
        }
    
        return $data;
    }

    private function store_license_data($data)
    {
        // Save license data in WordPress options
        update_option('license_id', $data['license_id']);
        update_option('license_key', $data['license_key']);
        update_option('license_status', $data['status']);
        update_option('license_expire_date', $data['expire_date']);
    }

    public function check_license()
    {
        $license_key = get_option('license_key');
        $response = $this->send_request($license_key);

        if ($response) {
            if ($response['status'] !== 200) {
                $this->handle_license_error($response['message']);
            }
        } else {
            $this->handle_server_error();
        }
    }

    public function ajax_check_license()
    {

        // Check if license key is provided
        if (isset($_POST['license_key'])) {
            $license_key = sanitize_text_field($_POST['license_key']);
            $response = $this->send_request($license_key);

            if ($response && $response['status'] === 200) {
                // Success response
                wp_send_json_success($response);
            } else {
                // Error response
                wp_send_json_error($response ? $response : 'Server not reachable');
            }
        }
        wp_die(); // Required to properly terminate AJAX requests
    }

    private function handle_license_error($message)
    {
        add_settings_error('license_activation', 'license_error', $message, 'error');
        error_log('License Check Error: ' . $message);
    }

    private function handle_server_error()
    {
        add_settings_error('license_activation', 'server_error', 'Tidak dapat menghubungi server. Silakan coba lagi nanti.', 'error');
        error_log('License Check Error: Server not reachable.');
    }

    public function enqueue_scripts()
    {
        // Enqueue jQuery
        wp_enqueue_script('jquery');
        // Enqueue your custom script
        wp_enqueue_script('license-check', plugin_dir_url(__FILE__) . 'js/license-check.js', array('jquery'), null, true);
        // Localize script to pass the AJAX URL
        wp_localize_script('license-check', 'ajax_object', array('ajax_url' => admin_url('admin-ajax.php')));
    }
}

// Initialize the class
$velocity_license = new Velocity_Addons_License();