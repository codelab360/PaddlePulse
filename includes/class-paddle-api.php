<?php

class Paddle_API {
    private $api_key;
    private $seller_id;
    private $api_url;

    public function __construct() {
        $this->api_key = get_option('paddle_api_key');
        $this->seller_id = get_option('paddle_seller_id'); // Get Seller ID
        $environment = get_option('paddle_environment', 'sandbox'); // Default to 'sandbox'

        if ($environment === 'live') {
            $this->api_url = 'https://vendors.paddle.com/api/2.0/';
        } else {
            $this->api_url = 'https://sandbox.paddle.com/api/2.0/';
        }
    }

    public function make_request($endpoint, $params = array()) {
        // Set up headers for Bearer authentication
        $headers = array(
            'Authorization' => 'Bearer ' . $this->api_key,
            'Content-Type'  => 'application/x-www-form-urlencoded',
        );

        // If Seller ID is needed in the request, add it to the params
        if ($this->seller_id) {
            $params['seller_id'] = $this->seller_id;
        }

        $response = wp_remote_post($this->api_url . $endpoint, array(
            'body'    => $params,
            'headers' => $headers,
        ));

        if (is_wp_error($response)) {
            return $response->get_error_message();
        }

        return wp_remote_retrieve_body($response);
    }
}

