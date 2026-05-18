<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Handles outgoing email notifications for donations.
 *
 * Both emails are optional and toggleable in admin settings.
 * Each email has an editable subject and optional intro message.
 * Donation details (naam, bedrag, frequentie, datum + enabled form fields)
 * are always appended automatically below the custom message.
 *
 * @since      1.1.0
 * @package    FTB_Donation_Form
 * @subpackage FTB_Donation_Form/includes
 */
class FTB_Email {

	/**
	 * Send a donation confirmation to the donor.
	 *
	 * @param object $donation Donation record from the database.
	 */
	public static function send_donor_confirmation( object $donation ): void {
		if ( ! get_option( 'ftb_email_donor_confirmation', '0' ) ) {
			return;
		}

		$to          = $donation->donor_email;
		$raw_subject = get_option( 'ftb_email_donor_subject', '' );
		$subject     = $raw_subject ? $raw_subject : __( 'Bedankt voor je donatie!', 'ftb-donation-form' );
		$body        = self::build_body(
			get_option( 'ftb_email_donor_body', '' ),
			self::details_block( $donation, false )
		);

		wp_mail( $to, $subject, $body, self::get_headers() );
	}

	/**
	 * Send a notification to the admin when a new donation is received.
	 *
	 * @param object $donation Donation record from the database.
	 */
	public static function send_admin_notification( object $donation ): void {
		if ( ! get_option( 'ftb_email_admin_notification', '0' ) ) {
			return;
		}

		$raw_to  = get_option( 'ftb_email_sender_address', '' );
		$to      = $raw_to ? $raw_to : get_bloginfo( 'admin_email' );
		$raw_sub = get_option( 'ftb_email_admin_subject', '' );
		$subject = $raw_sub ? $raw_sub : __( 'Je hebt een nieuwe donatie ontvangen', 'ftb-donation-form' );
		$body    = self::build_body(
			get_option( 'ftb_email_admin_body', '' ),
			self::details_block( $donation, true )
		);

		wp_mail( $to, $subject, $body, self::get_headers() );
	}

	/**
	 * Build the From header. Sender name is always the site title.
	 *
	 * @return array
	 */
	private static function get_headers(): array {
		$name      = get_bloginfo( 'name' );
		$raw_email = get_option( 'ftb_email_sender_address', '' );
		$email     = $raw_email ? $raw_email : get_bloginfo( 'admin_email' );

		return array( 'From: ' . $name . ' <' . $email . '>' );
	}

	/**
	 * Combine the admin-written intro with the always-appended details block.
	 *
	 * @param string $custom_message Admin-written intro (may be empty).
	 * @param string $details        Auto-generated donation details block.
	 * @return string
	 */
	private static function build_body( string $custom_message, string $details ): string {
		if ( ! empty( $custom_message ) ) {
			return $custom_message . "\n\n" . $details;
		}

		return $details;
	}

	/**
	 * Build the always-appended donation details block.
	 *
	 * Includes naam, bedrag, frequentie, datum, and any form fields that are
	 * enabled in admin settings and filled in by the donor.
	 *
	 * @param object $donation      Donation record.
	 * @param bool   $include_email Whether to include the donor's email address (admin email only).
	 * @return string
	 */
	private static function details_block( object $donation, bool $include_email ): string {
		$amount    = '€' . number_format( $donation->amount / 100, 2, ',', '.' );
		$frequency = self::format_frequency( $donation->frequency );
		$date      = wp_date( get_option( 'date_format' ) );
		$fields    = get_option( 'ftb_form_fields', array() );

		$lines = array();

		$lines[] = __( 'Naam:', 'ftb-donation-form' ) . ' ' . $donation->donor_name;

		if ( $include_email ) {
			$lines[] = __( 'E-mail:', 'ftb-donation-form' ) . ' ' . $donation->donor_email;
		}

		$lines[] = __( 'Bedrag:', 'ftb-donation-form' ) . ' ' . $amount;
		$lines[] = __( 'Frequentie:', 'ftb-donation-form' ) . ' ' . $frequency;
		$lines[] = __( 'Datum:', 'ftb-donation-form' ) . ' ' . $date;

		if ( ! empty( $fields['phone'] ) && ! empty( $donation->donor_phone ) ) {
			$lines[] = __( 'Telefoon:', 'ftb-donation-form' ) . ' ' . $donation->donor_phone;
		}
		if ( ! empty( $fields['street'] ) && ! empty( $donation->donor_street ) ) {
			$lines[] = __( 'Straat:', 'ftb-donation-form' ) . ' ' . $donation->donor_street;
		}
		if ( ! empty( $fields['house_number'] ) && ! empty( $donation->donor_house_number ) ) {
			$lines[] = __( 'Huisnummer:', 'ftb-donation-form' ) . ' ' . $donation->donor_house_number;
		}
		if ( ! empty( $fields['postal_code'] ) && ! empty( $donation->donor_postal_code ) ) {
			$lines[] = __( 'Postcode:', 'ftb-donation-form' ) . ' ' . $donation->donor_postal_code;
		}
		if ( ! empty( $fields['city'] ) && ! empty( $donation->donor_city ) ) {
			$lines[] = __( 'Plaats:', 'ftb-donation-form' ) . ' ' . $donation->donor_city;
		}

		return implode( "\n", $lines );
	}

	private static function format_frequency( string $frequency ): string {
		$labels = array(
			'one_time' => __( 'Eenmalig', 'ftb-donation-form' ),
			'monthly'  => __( 'Maandelijks', 'ftb-donation-form' ),
			'yearly'   => __( 'Jaarlijks', 'ftb-donation-form' ),
		);

		return $labels[ $frequency ] ?? $frequency;
	}
}
