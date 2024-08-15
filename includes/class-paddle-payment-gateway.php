<?php

class Paddle_Payment_Gateway {
    
    public function __construct() {
        new Paddle_API();
        new Paddle_Admin();
        new Paddle_Frontend();
    }
}
