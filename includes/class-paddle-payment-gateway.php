<?php

class Paddle_Payment_Gateway {
    
    public function __construct() {
        new Paddle_API();
        new Paddle_Admin();
        new Paddle_Frontend();
        add_action('init', array($this, 'handle_paddle_webhook_request'));
    }

    public function handle_paddle_webhook_request() {
        if (isset($_GET['paddle-webhook']) && $_GET['paddle-webhook'] === 'true') {
            $this->process_webhook();
            exit; // Important to prevent WordPress from rendering the rest of the page
        }
    }

    private function process_webhook() {
        // 1. Get Webhook Secret Key
        $webhook_secret_key = get_option('paddle_webhook_secret_key');
        if (empty($webhook_secret_key)) {
            Paddle_Utils::log('Webhook processing failed: Secret key not configured.', 'error');
            wp_die('Webhook secret key not configured.', 'PaddlePulse Webhook Error', array('response' => 400));
            return;
        }

        // 2. Get Signature from header
        // Paddle sends the signature in 'Paddle-Signature' header.
        // Note: HTTP headers might be prefixed with HTTP_ in $_SERVER.
        $signature_header = isset($_SERVER['HTTP_PADDLE_SIGNATURE']) ? $_SERVER['HTTP_PADDLE_SIGNATURE'] : '';
        if (empty($signature_header)) {
            Paddle_Utils::log('Webhook processing failed: Missing Paddle-Signature header.', 'error');
            wp_die('Missing signature.', 'PaddlePulse Webhook Error', array('response' => 400));
            return;
        }

        // 3. Get Raw POST Data
        $payload = file_get_contents('php://input');
        if (empty($payload)) {
            Paddle_Utils::log('Webhook processing failed: Empty payload.', 'error');
            wp_die('Empty payload.', 'PaddlePulse Webhook Error', array('response' => 400));
            return;
        }

        // 4. Verify Signature
        // Paddle's signature is a string like: ts=<timestamp>;h1=<hash>
        // We need to parse this.
        $timestamp = '';
        $h1_hash = '';
        parse_str(str_replace(';', '&', $signature_header), $signature_parts);

        if (isset($signature_parts['ts']) && isset($signature_parts['h1'])) {
            $timestamp = $signature_parts['ts'];
            $h1_hash = $signature_parts['h1'];
        } else {
            Paddle_Utils::log('Webhook processing failed: Malformed Paddle-Signature header.', 'error', array('header' => $signature_header));
            wp_die('Malformed signature.', 'PaddlePulse Webhook Error', array('response' => 400));
            return;
        }

        // Check timestamp to prevent replay attacks (e.g., within 5 minutes)
        // Paddle's recommendation is 5 minutes (300 seconds)
        if (abs(time() - (int)$timestamp) > 300) {
             Paddle_Utils::log('Webhook processing failed: Timestamp validation failed.', 'error', array('timestamp' => $timestamp, 'current_time' => time()));
             wp_die('Timestamp validation failed.', 'PaddlePulse Webhook Error', array('response' => 400));
             return;
        }

        // Create the signed content string
        $signed_payload = $timestamp . ':' . $payload;

        // Generate the expected signature
        $expected_signature = hash_hmac('sha256', $signed_payload, $webhook_secret_key);

        // 5. Compare signatures
        if (hash_equals($expected_signature, $h1_hash)) {
            // Signature is valid
            http_response_code(200); // Send a 200 OK status back to Paddle
            Paddle_Utils::log('Webhook verified successfully.', 'info', array('payload_preview' => substr($payload, 0, 200))); // Log a preview of the payload
            
            // TODO: Add actual event processing logic here based on $payload
            // For now, we just log it.
            // Example: $event_data = json_decode($payload, true);
            // do_action('paddlepulse_webhook_event_received', $event_data);

            echo 'Webhook processed.'; // Optional: send a simple message
        } else {
            // Signature is invalid
            Paddle_Utils::log('Webhook verification failed: Signature mismatch.', 'error');
            wp_die('Invalid signature.', 'PaddlePulse Webhook Error', array('response' => 403)); // Forbidden
        }
    }
}
