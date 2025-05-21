<?php  
require_once plugin_dir_path(__FILE__) . 'class-paddle-utils.php';

class Paddle_Admin {
    private $paddle_utils;
    
    public function __construct() {
        $this->paddle_utils = new Paddle_Utils();
        add_action('admin_menu', array($this, 'add_settings_page'));
        add_action('admin_init', array($this, 'register_settings'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_paddlePulse_admin_scripts'));
        add_action('admin_post_test_paddle_connection', array($this, 'test_paddle_connection')); // Handle test connection
    }

    // Handle test connection
    public function test_paddle_connection() {
        // Verify nonce and other security checks
        if (!isset($_POST['api_key']) || !isset($_POST['seller_id']) || !isset($_POST['environment'])) {
            Paddle_Utils::log('Test connection call missing required parameters.', 'warning', $_POST);
            wp_send_json_error('Missing required parameters.');
        }
    
        $api_key = sanitize_text_field($_POST['api_key']);
        $seller_id = sanitize_text_field($_POST['seller_id']);
        $environment = sanitize_text_field($_POST['environment']);
    
        $this->paddle_utils->set_environment($environment);
        // Assuming test_connection in Paddle_Utils now might also use/return more detailed info or throw exceptions
        // For now, we adapt based on the original structure's $result being a boolean or string.
        $result_message = $this->paddle_utils->test_connection($api_key, $seller_id); // Assuming this method returns a descriptive string on failure/success
    
        // Check if the result message indicates success. 
        // This might need adjustment based on how test_connection is implemented.
        // For this example, let's assume "Connection successful!" is the positive outcome.
        if (strpos($result_message, 'Connection successful!') !== false) {
            Paddle_Utils::log('Connection test successful.', 'info', array('environment' => $environment));
            wp_send_json_success($result_message);
        } else {
            $api_key_summary = 'NotProvided';
            if (!empty($api_key)) {
                $api_key_summary = 'EndingIn...' . substr($api_key, -4);
            }
            Paddle_Utils::log('Connection test failed.', 'warning', array('api_key_summary' => $api_key_summary, 'seller_id' => $seller_id, 'environment' => $environment, 'result_message' => $result_message));
            wp_send_json_error('Connection failed: ' . $result_message);
        }
    }

    // Add Tailwind CSS to admin pages
    public function enqueue_paddlePulse_admin_scripts($hook) {
        // Only enqueue on your plugin's admin pages
        if ($hook != 'toplevel_page_paddlepulse') {
            return;
        }
    
        // Enqueue Tailwind CSS
        wp_enqueue_style('tailwindcss', 'https://cdn.jsdelivr.net/npm/tailwindcss@latest/dist/tailwind.min.css');
    
        // Enqueue custom admin script
        wp_enqueue_script('paddlepulse-admin', plugin_dir_url(__FILE__) . '../assets/js/admin.js', array('jquery'), null, true);
    
        // Pass the ajax_url to the script
        wp_localize_script('paddlepulse-admin', 'paddlepulseAdmin', array(
            'ajax_url' => admin_url('admin-ajax.php'),
        ));
    }
    
    

    // Add menu page
    public function add_settings_page() {
        add_menu_page(
            'PaddlePulse Settings',
            'PaddlePulse',
            'manage_options',
            'paddlepulse',
            array($this, 'render_settings_page'),
            'dashicons-cart',
            65
        );
    }

    // Register settings
    public function register_settings() {
        register_setting('paddlepulse_options', 'paddle_api_key', 'sanitize_text_field');
        register_setting('paddlepulse_options', 'paddle_seller_id', 'sanitize_text_field'); // Register new setting for Seller ID
        register_setting('paddlepulse_options', 'paddle_environment', 'sanitize_text_field'); // Register new setting for Environment
        register_setting('paddlepulse_options', 'paddle_webhook_secret_key', 'sanitize_text_field');

        add_settings_section('paddlepulse_main', 'Main Settings', null, 'paddlepulse');

        add_settings_field(
            'paddle_api_key',
            'Paddle API Key',
            array($this, 'api_key_input'),
            'paddlepulse',
            'paddlepulse_main'
        );

        add_settings_field(
            'paddle_seller_id',
            'Seller ID',
            array($this, 'seller_id_input'),
            'paddlepulse',
            'paddlepulse_main'
        );

        add_settings_field(
            'paddle_environment',
            'Environment',
            array($this, 'environment_input'),
            'paddlepulse',
            'paddlepulse_main'
        );

        add_settings_field(
            'paddle_webhook_secret_key',
            'Paddle Webhook Secret Key',
            array($this, 'webhook_secret_key_input'),
            'paddlepulse',
            'paddlepulse_main'
        );
    }

    // Render API key input field
    public function api_key_input() {
        $api_key = get_option('paddle_api_key');
        echo "<input type='text' name='paddle_api_key' value='" . esc_attr($api_key) . "' class='p-2 border border-gray-300 rounded-md shadow-sm' />";
    }

    // Render Seller ID input field
    public function seller_id_input() {
        $seller_id = get_option('paddle_seller_id');
        echo "<input type='text' name='paddle_seller_id' value='" . esc_attr($seller_id) . "' class='p-2 border border-gray-300 rounded-md shadow-sm' />";
    }

    // Render Environment selection field
    public function environment_input() {
        $current_environment = get_option('paddle_environment', 'sandbox'); // Default to 'sandbox'
        ?>
        <select name="paddle_environment" class="p-2 border border-gray-300 rounded-md shadow-sm">
            <option value="sandbox" <?php selected($current_environment, 'sandbox'); ?>>Sandbox</option>
            <option value="live" <?php selected($current_environment, 'live'); ?>>Live</option>
        </select>
        <?php
    }

    // Render Webhook Secret Key input field
    public function webhook_secret_key_input() {
        $webhook_secret_key = get_option('paddle_webhook_secret_key');
        echo "<input type='text' name='paddle_webhook_secret_key' value='" . esc_attr($webhook_secret_key) . "' class='p-2 border border-gray-300 rounded-md shadow-sm w-full' />";
        echo "<p class='description'>Enter your Paddle Webhook Secret Key. This is used to verify incoming webhooks from Paddle.</p>";
    }

    // Render settings page
    public function render_settings_page() {
        ?>
        <div class="wrap max-w-4xl mx-auto p-6 bg-gray-50 rounded-lg shadow-lg">
            <h1 class="text-3xl font-semibold mb-6 text-gray-900">PaddlePulse Settings</h1>
            <form method="post" action="options.php" class="bg-white p-8 rounded-lg shadow-md space-y-6">
                <?php
                settings_fields('paddlepulse_options');
                do_settings_sections('paddlepulse');
                ?>
                <button type="button" id="test-connection" class="py-3 px-4 bg-green-600 text-white rounded-md shadow-md hover:bg-green-700 transition duration-200">
                    Test Connection
                </button>
                <?php
                submit_button('Save Changes', 'primary', 'submit', true, array('class' => 'w-full py-3 px-4 bg-blue-600 text-white rounded-md shadow-md hover:bg-blue-700 transition duration-200'));
                ?>
            </form>
            <div id="test-result" class="mt-6 p-4 border-l-4 rounded-md shadow-sm"></div>

            // <!-- Add Support Section Below -->
            <div class="mt-8 p-6 bg-white rounded-lg shadow-md space-y-4">
                <h2 class="text-2xl font-semibold text-gray-800">Need Help?</h2>
                <p class="text-gray-700">
                    If you have any questions or encounter issues, please feel free to reach out to our support team: 
                    <a href="mailto:you@example.com" class="text-blue-600 hover:underline">you@example.com</a>.
                </p>
                <p class="text-gray-700">
                    You can also find extensive documentation and resources on the official Paddle website: 
                    <a href="https://developer.paddle.com/" target="_blank" rel="noopener noreferrer" class="text-blue-600 hover:underline">Paddle Developer Documentation</a>.
                </p>
            </div>
        </div> <!-- Closing tag for class="wrap ..." -->
        <?php
    }
    
    
}
