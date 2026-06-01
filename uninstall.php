<?php
/**
 * Runs when the plugin is deleted via WP-admin.
 *
 * @package FTB_Donation_Form
 */

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

global $wpdb;

$designated_ids = (array) get_option( 'ftb_designated_managers', array() );

if ( '1' === get_option( 'ftb_delete_data_on_uninstall', '0' ) ) {

	// Drop donations table.
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.SchemaChange, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
	$wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}ftb_donations" );

	$options = array(
		'ftb_mollie_api_key',
		'ftb_mollie_test_mode',
		'ftb_form_heading',
		'ftb_enable_recurring',
		'ftb_form_fields',
		'ftb_amount_options',
		'ftb_show_preset_amounts',
		'ftb_allow_custom_amount',
		'ftb_min_custom_amount',
		'ftb_post_payment_behavior',
		'ftb_post_payment_redirect_url',
		'ftb_post_payment_message',
		'ftb_privacy_url',
		'ftb_email_donor_confirmation',
		'ftb_email_donor_subject',
		'ftb_email_donor_body',
		'ftb_email_admin_notification',
		'ftb_email_sender_address',
		'ftb_db_version',
		'ftb_editor_access_mode',
		'ftb_designated_managers',
		'ftb_delete_data_on_uninstall',
	);

	foreach ( $options as $option ) {
		delete_option( $option );
	}
}

// Always remove capabilities on uninstall.
$admin = get_role( 'administrator' );
if ( $admin ) {
	$admin->remove_cap( 'ftb_manage_settings' );
}

$editor = get_role( 'editor' );
if ( $editor ) {
	$editor->remove_cap( 'ftb_manage_settings' );
}

foreach ( $designated_ids as $user_id ) {
	$user = get_user_by( 'id', absint( $user_id ) );
	if ( $user ) {
		$user->remove_cap( 'ftb_manage_settings' );
	}
}
