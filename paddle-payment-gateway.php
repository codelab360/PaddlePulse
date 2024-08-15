<?php
/*
Plugin Name: PaddlePulse
Description: A WordPress plugin for integrating with the Paddle payment gateway.
Version: 1.0
Author: CodeLab360
Author URI: https://codelab360.com
*/

// Include core classes
include_once(plugin_dir_path(__FILE__) . 'includes/class-paddle-payment-gateway.php');
include_once(plugin_dir_path(__FILE__) . 'includes/class-paddle-api.php');
include_once(plugin_dir_path(__FILE__) . 'includes/class-paddle-admin.php');
include_once(plugin_dir_path(__FILE__) . 'includes/class-paddle-frontend.php');
include_once(plugin_dir_path(__FILE__) . 'includes/class-paddle-utils.php');


// Initialize the plugin
$paddle_payment_gateway = new Paddle_Payment_Gateway();
