<?php
/*
Plugin Name: Invoice Manager
Description: Lightweight Invoice Plugin Generator
Version: 1.0.0
Author: Shishir
*/

define('WPIM_VERSION', '1.0.0');
define('WPIM_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('WPIM_PLUGIN_URL', plugin_dir_url(__FILE__));


// Require Composer autoload if available
if (file_exists(WPIM_PLUGIN_DIR . 'vendor/autoload.php')) {
    require_once WPIM_PLUGIN_DIR . 'vendor/autoload.php';
}

// Require main plugin logic
require_once WPIM_PLUGIN_DIR . 'invoice-generator.php';

// Action hook for plugin loaded
function wpim_plugin_loaded() {
    new WPIM_Invoice_Manager();
}
add_action('plugins_loaded', 'wpim_plugin_loaded');

// Plugin activation hook
function wp_invoice_manager_activate() {
    flush_rewrite_rules();
}
register_activation_hook(__FILE__, 'wp_invoice_manager_activate');

// Plugin deactivation hook
function wp_invoice_manager_deactivate() {
   flush_rewrite_rules();
}
register_deactivation_hook(__FILE__, 'wp_invoice_manager_deactivate');
