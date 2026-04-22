<?php
/**
 * Plugin Name: For The Better donatieformulier
 * Plugin URI: https://forthebetter.nl/
 * Description: Een plugin voor donatieformulieren met Mollie-betaalintegratie  conform WCAG 2.2 en AVG-richtlijnen.
 * Version: 1.0.0
 * Author: For The Better
 * License: GPL v2 or later
 * Text Domain: ftb-donation-form
 * Domain Path: /languages
 * Requires at least: 5.0
 * Tested up to: 6.9
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
    $editor = get_role( 'editor' );
    if ( $editor ) {
        $editor->add_cap( 'ftb_manage_settings' );
    }
    $admin = get_role( 'administrator' );
    if ( $admin ) {
        $admin->add_cap( 'ftb_manage_settings' );
    }
    global $wpdb;

    $table_name      = $wpdb->prefix . 'ftb_donations';
    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE {$table_name} (
        id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
        donor_name varchar(100) NOT NULL DEFAULT '',
        donor_email varchar(100) NOT NULL DEFAULT '',
        donor_phone varchar(30) NOT NULL DEFAULT '',
        donor_street varchar(100) NOT NULL DEFAULT '',
        donor_house_number varchar(20) NOT NULL DEFAULT '',
        donor_postal_code varchar(20) NOT NULL DEFAULT '',
        donor_city varchar(100) NOT NULL DEFAULT '',
        amount decimal(10,2) NOT NULL DEFAULT 0.00,
        frequency varchar(20) NOT NULL DEFAULT 'one-time',
        mollie_payment_id varchar(100) NOT NULL DEFAULT '',
        payment_status varchar(20) NOT NULL DEFAULT 'pending',
        created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
        updated_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        KEY payment_status (payment_status),
        KEY mollie_payment_id (mollie_payment_id)
    ) {$charset_collate};";

    require_once ABSPATH . 'wp-admin/includes/upgrade.php';
    dbDelta( $sql );

    // Default plugin options
    add_option( 'ftb_mollie_api_key', '' );
    add_option( 'ftb_mollie_test_mode', '1' );
    add_option( 'ftb_form_fields', [
        'phone'        => '1',
        'street'       => '1',
        'house_number' => '1',
        'postal_code'  => '1',
        'city'         => '1',
    ] );
    add_option( 'ftb_amount_options', [ '5', '10', '25' ] );
    add_option( 'ftb_allow_custom_amount', '1' );
    add_option( 'ftb_post_payment_behavior', 'message' );
    add_option( 'ftb_post_payment_redirect_url', '' );
    add_option( 'ftb_post_payment_message', '' );
    add_option( 'ftb_privacy_url', '' );
    add_option( 'ftb_db_version', '1.0' );
}

// Deactivation hook
register_deactivation_hook(__FILE__, 'ftb_donation_form_deactivate');
function ftb_donation_form_deactivate() {
    $editor = get_role( 'editor' );
    if ( $editor ) {
        $editor->remove_cap( 'ftb_manage_settings' );
    }
    $admin = get_role( 'administrator' );
    if ( $admin ) {
        $admin->remove_cap( 'ftb_manage_settings' );
    }
}