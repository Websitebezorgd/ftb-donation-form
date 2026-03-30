<?php
/**
 * Plugin Name: For The Better donatieformulier
 * Plugin URI: https://forthebetter.nl/
 * Description: A WCAG 2.2 compliant donation form plugin with Mollie payment integration.
 * Version: 1.0.0
 * Author: For The Better
 * License: GPL v2 or later
 * Text Domain: ftb-donation-form
 * Requires at least: 5.0
 * Tested up to: 6.4
 * Requires PHP: 7.4
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('FTB_DONATION_FORM_VERSION', '1.0.0');
define('FTB_DONATION_FORM_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('FTB_DONATION_FORM_PLUGIN_URL', plugin_dir_url(__FILE__));

// Include core files
require_once FTB_DONATION_FORM_PLUGIN_DIR . 'includes/class-ftb-donation-form.php';

// Initialize the plugin
function ftb_donation_form_init() {
    $plugin = new FTB_Donation_Form();
    $plugin->run();
}
add_action('plugins_loaded', 'ftb_donation_form_init');

// Activation hook
register_activation_hook(__FILE__, 'ftb_donation_form_activate');
function ftb_donation_form_activate() {
    // Create custom database table
    // Add default options
}

// Deactivation hook
register_deactivation_hook(__FILE__, 'ftb_donation_form_deactivate');
function ftb_donation_form_deactivate() {
    // Cleanup if needed
}