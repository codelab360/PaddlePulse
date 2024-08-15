<?php 

class Paddle_Frontend {
    public function __construct() {
        add_shortcode('paddle_checkout_button', array($this, 'render_checkout_button'));
    }

    public function render_checkout_button() {
        // Output the checkout button HTML
    }
}
