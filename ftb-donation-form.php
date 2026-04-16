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
 * Tested up to: 6.9
 * Requires PHP: 7.4
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

function ftb_donation_form_shortcode() {
    return '<p>Donatieformulier komt hier</p>';
}
add_shortcode( 'ftb_donation_form', 'ftb_donation_form_shortcode' );