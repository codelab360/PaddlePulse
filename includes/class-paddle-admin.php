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
            wp_send_json_error('Missing required parameters.');
        }
    
        $api_key = sanitize_text_field($_POST['api_key']);
        $seller_id = sanitize_text_field($_POST['seller_id']);
        $environment = sanitize_text_field($_POST['environment']);
    
        $this->paddle_utils->set_environment($environment);
        $result = $this->paddle_utils->test_connection($api_key, $seller_id);
    
        if ($result) {
            wp_send_json_success('Connection successful.');
        } else {
            wp_send_json_error('Connection failed.');
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
        register_setting('paddlepulse_options', 'paddle_api_key');
        register_setting('paddlepulse_options', 'paddle_seller_id'); // Register new setting for Seller ID
        register_setting('paddlepulse_options', 'paddle_environment'); // Register new setting for Environment

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
        </div>
        <?php
    }
    
    
}
