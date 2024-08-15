<?php

class Paddle_Utils {

    // Base URLs for API endpoints
    private $api_urls = array(
        'sandbox' => 'https://sandbox.paddle.com/api/2.0/',
        'live'    => 'https://api.paddle.com/api/2.0/',
    );

    private $current_env;

    public function __construct() {
        // Set default environment
        $this->current_env = 'live'; // Default to live
    }

    // Set the environment (sandbox or live)
    public function set_environment($env) {
        if (array_key_exists($env, $this->api_urls)) {
            $this->current_env = $env;
        }
    }

    // Get the base URL for the current environment
    public function get_api_url() {
        return $this->api_urls[$this->current_env];
    }

    // Make a GET request to the Paddle API
    public function make_get_request($endpoint, $api_key) {
        $api_url = $this->get_api_url() . $endpoint;
        $response = wp_remote_get($api_url, array(
            'headers' => array(
                'Authorization' => 'Bearer ' . $api_key,
                'Content-Type'  => 'application/json',
            ),
        ));

        if (is_wp_error($response)) {
            return array('error' => $response->get_error_message());
        }

        $body = wp_remote_retrieve_body($response);
        return json_decode($body, true);
    }

    // Example of testing the connection
    public function test_connection($api_key) {
        $response = $this->make_get_request('event-types', $api_key);
        
        if (isset($response['error'])) {
            return "Connection failed: " . $response['error'];
        }

        if (!empty($response)) {
            return "Connection successful!";
        } else {
            return "Connection failed: Invalid response from API.";
        }
    }
}

?>
