<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * The public-facing functionality of the plugin.
 *
 * @since      1.0.0
 * @package    FTB_Donation_Form
 * @subpackage FTB_Donation_Form/public
 */
class FTB_Donation_Form_Public {

	private $plugin_name;

	public function __construct( $plugin_name, $version ) {
		$this->plugin_name = $plugin_name;
		unset( $version ); // Version unused: filemtime() used for cache-busting instead.
	}

	public function enqueue_styles() {
		wp_enqueue_style(
			$this->plugin_name,
			plugin_dir_url( __FILE__ ) . 'css/ftb-donation-form-public.css',
			array(),
			filemtime( plugin_dir_path( __FILE__ ) . 'css/ftb-donation-form-public.css' ),
			'all'
		);
	}

	public function enqueue_scripts() {
		wp_enqueue_script(
			$this->plugin_name,
			plugin_dir_url( __FILE__ ) . 'js/ftb-donation-form-public.js',
			array(),
			filemtime( plugin_dir_path( __FILE__ ) . 'js/ftb-donation-form-public.js' ),
			true
		);

		$min_amount = (float) get_option( 'ftb_min_custom_amount', '1' );

		wp_localize_script(
			$this->plugin_name,
			'ftbDonationForm',
			array(
				'allowCustomAmount' => (bool) get_option( 'ftb_allow_custom_amount', '1' ),
				'showPresetAmounts' => (bool) get_option( 'ftb_show_preset_amounts', '1' ),
				'minCustomAmount'   => $min_amount,
				'i18n'              => array(
					'errorFrequency' => __( 'Kies een frequentie.', 'ftb-donation-form' ),
					'errorAmount'    => __( 'Kies een bedrag.', 'ftb-donation-form' ),
					/* translators: %s: minimum amount, e.g. "1" */
					'errorCustom'    => sprintf( __( 'Je eigen bedrag moet minimaal €%s zijn.', 'ftb-donation-form' ), $min_amount ),
					'errorName'      => __( 'Vul je volledige naam in.', 'ftb-donation-form' ),
					'errorEmail'     => __( 'Vul een geldig e-mailadres in.', 'ftb-donation-form' ),
					'errorGdpr'      => __( 'Je moet akkoord gaan met de privacyverklaring om te doneren.', 'ftb-donation-form' ),
					'errorSummary'   => __( 'Controleer de volgende fouten:', 'ftb-donation-form' ),
				),
			)
		);
	}

	public function restrict_rest_namespace(): void {
		add_filter(
			'rest_pre_dispatch',
			static function ( $result, $server, WP_REST_Request $request ) {
				if ( strpos( $request->get_route(), '/ftb/v1' ) === 0 && $request->get_method() !== 'POST' ) {
					return new WP_Error( 'rest_forbidden', '', array( 'status' => 403 ) );
				}
				return $result;
			},
			10,
			3
		);
	}

	public function register_webhook_route(): void {
		register_rest_route(
			'ftb/v1',
			'/webhook',
			array(
				'methods'             => 'POST',
				'callback'            => array( $this, 'handle_webhook' ),
				'permission_callback' => '__return_true',
				'args'                => array(
					'id' => array(
						'type'              => 'string',
						'sanitize_callback' => 'sanitize_text_field',
					),
				),
			)
		);
	}

	/**
	 * Handle a Mollie webhook call.
	 *
	 * Mollie POSTs the payment ID as `id` in the request body. We re-fetch
	 * the payment from Mollie (never trust the raw POST data) and update our
	 * database. Always returns 200 so Mollie does not keep retrying.
	 *
	 * Note: webhooks require a publicly accessible URL. On localhost, Mollie
	 * cannot reach this endpoint — update payment status manually for local testing.
	 */
	public function handle_webhook( WP_REST_Request $request ): WP_REST_Response {
		$mollie_id = sanitize_text_field( (string) ( $request->get_param( 'id' ) ?? '' ) );

		if ( empty( $mollie_id ) ) {
			return new WP_REST_Response( null, 200 );
		}

		// Fetch the payment from Mollie before touching the DB — never trust raw POST data.
		try {
			$service = new FTB_Mollie_Service();
			$payment = $service->get_payment( $mollie_id );
		} catch ( \Mollie\Api\Exceptions\MollieException $e ) {
			return new WP_REST_Response( null, 200 );
		}

		$db = new FTB_DB();

		// For one-time payments the ID is stored directly. For subscription charges
		// Mollie sends a new payment ID each time — match via the subscription ID instead.
		$donation               = $db->get_donation_by_mollie_id( $mollie_id );
		$is_subscription_charge = false;

		if ( ! $donation && ! empty( $payment->subscriptionId ) ) {
			$donation               = $db->get_donation_by_subscription_id( $payment->subscriptionId );
			$is_subscription_charge = true;
		}

		if ( ! $donation ) {
			return new WP_REST_Response( null, 200 );
		}

		// Update status — by payment ID for one-time/first payments, by row ID for subscription charges.
		if ( $is_subscription_charge ) {
			$db->update_payment_status_by_id( (int) $donation->id, $payment->status );
		} else {
			$db->update_payment_status( $mollie_id, $payment->status );
		}

		// Send email notifications on successful payment.
		if ( 'paid' === $payment->status ) {
			FTB_Email::send_donor_confirmation( $donation );
			FTB_Email::send_admin_notification( $donation );
		}

		// After the first payment of a recurring donation is paid, create the subscription.
		// Mollie will then handle all future charges automatically.
		if (
			'paid' === $payment->status
			&& ! $is_subscription_charge
			&& ! empty( $donation->mollie_customer_id )
			&& empty( $donation->mollie_subscription_id )
			&& in_array( $donation->frequency, array( 'monthly', 'yearly' ), true )
		) {
			$interval   = 'monthly' === $donation->frequency ? '1 month' : '1 year';
			$start_date = ( new \DateTime() )->modify( 'monthly' === $donation->frequency ? '+1 month' : '+1 year' )->format( 'Y-m-d' );
			$amount_eur = $donation->amount / 100;
			/* translators: %s: donor full name */
			$description = sprintf( __( 'Donatie van %s', 'ftb-donation-form' ), $donation->donor_name );

			$sub_webhook_url  = rest_url( 'ftb/v1/webhook' );
			$sub_webhook_host = (string) wp_parse_url( $sub_webhook_url, PHP_URL_HOST );
			$is_local         = in_array( $sub_webhook_host, array( 'localhost', '127.0.0.1', '::1' ), true )
				|| substr( $sub_webhook_host, -6 ) === '.local';

			try {
				$subscription = $service->create_subscription(
					$donation->mollie_customer_id,
					$amount_eur,
					$interval,
					$start_date,
					$description,
					$is_local ? '' : $sub_webhook_url
				);
				$db->update_mollie_subscription_id( (int) $donation->id, $subscription->id );
			} catch ( \Mollie\Api\Exceptions\MollieException $e ) {
				if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
                    // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
					error_log( 'FTB Mollie subscription error: ' . $e->getMessage() );
				}
			}
		}

		return new WP_REST_Response( null, 200 );
	}

	public function register_shortcodes() {
		add_shortcode( 'ftb_donation_form', array( $this, 'render_donation_form' ) );
	}

	public function render_donation_form( $atts = array() ) {
		$form_heading = get_option( 'ftb_form_heading', '' );
		$title        = $form_heading ? $form_heading : __( 'Doneer nu', 'ftb-donation-form' );

		$errors     = array();
		$old_values = array();
		$success    = false;

		// Read admin settings (needed by both processing and template).
		$form_fields           = get_option( 'ftb_form_fields', array() );
		$amount_options        = array_values( array_filter( (array) get_option( 'ftb_amount_options', array( '5', '10', '25' ) ) ) );
		$show_presets          = (bool) get_option( 'ftb_show_preset_amounts', '1' );
		$allow_custom          = (bool) get_option( 'ftb_allow_custom_amount', '1' );
		$min_custom_amount     = (float) get_option( 'ftb_min_custom_amount', '1' );
		$enable_recurring      = (bool) get_option( 'ftb_enable_recurring', '1' );
		$privacy_url           = get_option( 'ftb_privacy_url', '' );
		$post_payment_behavior = get_option( 'ftb_post_payment_behavior', 'message' );
		$saved_message         = get_option( 'ftb_post_payment_message', '' );
		$post_payment_message  = $saved_message ? $saved_message : __( 'Hartelijk dank voor je donatie!', 'ftb-donation-form' );
		$post_payment_redirect = get_option( 'ftb_post_payment_redirect_url', '' );

		// Return from Mollie checkout — verify the one-time token before showing the thank-you.
		if ( isset( $_GET['ftb_return'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
            // phpcs:disable WordPress.Security.NonceVerification.Recommended
			$return_id    = isset( $_GET['ftb_did'] ) ? absint( $_GET['ftb_did'] ) : 0;
			$return_token = isset( $_GET['ftb_token'] ) ? sanitize_text_field( wp_unslash( $_GET['ftb_token'] ) ) : '';
            // phpcs:enable
			$stored_token = $return_id ? get_transient( 'ftb_return_' . $return_id ) : false;

			if ( $return_id && $return_token && $stored_token && hash_equals( (string) $stored_token, $return_token ) ) {
				delete_transient( 'ftb_return_' . $return_id );
				$success = true;
			}
			// Invalid or missing token: $success stays false and the form renders normally.

			ob_start();
			include 'partials/ftb-donation-form-public-display.php';
			return ob_get_clean();
		}

		if ( isset( $_POST['ftb_donation_nonce'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing -- verified on next line
			$nonce = sanitize_text_field( wp_unslash( $_POST['ftb_donation_nonce'] ) );

			if ( ! wp_verify_nonce( $nonce, 'ftb_donation_submit' ) ) {
				wp_die( esc_html__( 'Beveiligingscontrole mislukt. Probeer het opnieuw.', 'ftb-donation-form' ) );
			}

			// Sanitize all inputs.
            // phpcs:disable WordPress.Security.ValidatedSanitizedInput.InputNotValidated
			$frequency     = mb_substr( sanitize_text_field( wp_unslash( $_POST['ftb_frequency'] ?? '' ) ), 0, 20 );
			$amount_raw    = sanitize_text_field( wp_unslash( $_POST['ftb_amount'] ?? '' ) );
			$custom_amount = sanitize_text_field( wp_unslash( $_POST['ftb_custom_amount'] ?? '' ) );
			$name          = mb_substr( sanitize_text_field( wp_unslash( $_POST['ftb_name'] ?? '' ) ), 0, 100 );
			$email         = mb_substr( sanitize_email( wp_unslash( $_POST['ftb_email'] ?? '' ) ), 0, 100 );
			$phone         = mb_substr( sanitize_text_field( wp_unslash( $_POST['ftb_phone'] ?? '' ) ), 0, 30 );
			$street        = mb_substr( sanitize_text_field( wp_unslash( $_POST['ftb_street'] ?? '' ) ), 0, 100 );
			$house_number  = mb_substr( sanitize_text_field( wp_unslash( $_POST['ftb_house_number'] ?? '' ) ), 0, 20 );
			$postal_code   = mb_substr( sanitize_text_field( wp_unslash( $_POST['ftb_postal_code'] ?? '' ) ), 0, 20 );
			$city          = mb_substr( sanitize_text_field( wp_unslash( $_POST['ftb_city'] ?? '' ) ), 0, 100 );
			$gdpr          = isset( $_POST['ftb_gdpr'] ) ? '1' : '0';
            // phpcs:enable

			// Validate frequency.
			$allowed_frequencies = $enable_recurring ? array( 'one_time', 'monthly', 'yearly' ) : array( 'one_time' );
			if ( ! in_array( $frequency, $allowed_frequencies, true ) ) {
				$errors['frequency'] = __( 'Kies een frequentie.', 'ftb-donation-form' );
			}

			// Validate amount.
			$amount = 0.0;
			if ( 'custom' === $amount_raw && $allow_custom ) {
				$amount = (float) str_replace( ',', '.', $custom_amount );
				if ( $amount < $min_custom_amount ) {
					/* translators: %s: minimum amount, e.g. "1" */
					$errors['amount'] = sprintf( __( 'Je eigen bedrag moet minimaal €%s zijn.', 'ftb-donation-form' ), $min_custom_amount );
				}
			} elseif ( $show_presets && in_array( $amount_raw, $amount_options, true ) ) {
				$amount = (float) $amount_raw;
			} else {
				$errors['amount'] = __( 'Kies een bedrag.', 'ftb-donation-form' );
			}

			// Validate name.
			if ( empty( $name ) ) {
				$errors['name'] = __( 'Vul je volledige naam in.', 'ftb-donation-form' );
			}

			// Validate email.
			if ( empty( $email ) || ! is_email( $email ) ) {
				$errors['email'] = __( 'Vul een geldig e-mailadres in.', 'ftb-donation-form' );
			}

			// Validate GDPR consent.
			if ( '1' !== $gdpr ) {
				$errors['gdpr'] = __( 'Je moet akkoord gaan met de privacyverklaring om te doneren.', 'ftb-donation-form' );
			}

			// Preserve submitted values for re-rendering the form on error.
			$old_values = array(
				'frequency'     => $frequency,
				'amount'        => $amount_raw,
				'custom_amount' => $custom_amount,
				'name'          => $name,
				'email'         => $email,
				'phone'         => $phone,
				'street'        => $street,
				'house_number'  => $house_number,
				'postal_code'   => $postal_code,
				'city'          => $city,
				'gdpr'          => $gdpr,
			);

			if ( empty( $errors ) ) {
				$db          = new FTB_DB();
				$donation_id = $db->insert_donation(
					array(
						'donor_name'         => $name,
						'donor_email'        => $email,
						'donor_phone'        => $phone,
						'donor_street'       => $street,
						'donor_house_number' => $house_number,
						'donor_postal_code'  => $postal_code,
						'donor_city'         => $city,
						'amount'             => $amount,
						'frequency'          => $frequency,
					)
				);

				// After a successful payment Mollie sends the donor back here.
				// For the 'redirect' behavior we send them straight to the configured URL.
				// For the 'message' behavior we generate a one-time token so the shortcode
				// can verify this is a genuine return from Mollie, not a bookmarked URL.
				if ( 'redirect' === $post_payment_behavior && ! empty( $post_payment_redirect ) ) {
					$return_url = $post_payment_redirect;
				} else {
					$return_token = wp_generate_password( 32, false );
					set_transient( 'ftb_return_' . $donation_id, $return_token, HOUR_IN_SECONDS );
					$return_url = add_query_arg(
						array(
							'ftb_return' => '1',
							'ftb_did'    => $donation_id,
							'ftb_token'  => $return_token,
						),
						get_permalink()
					);
				}

				try {
					$service      = new FTB_Mollie_Service();
					$webhook_url  = rest_url( 'ftb/v1/webhook' );
					$webhook_host = (string) wp_parse_url( $webhook_url, PHP_URL_HOST );

					// On local dev environments Mollie cannot reach us, so omit the webhook URL.
					// On a live site without HTTPS, the webhook URL is included and Mollie will
					// reject the payment — SSL is required before going live.
					$is_local = in_array( $webhook_host, array( 'localhost', '127.0.0.1', '::1' ), true )
						|| substr( $webhook_host, -6 ) === '.local';

					if ( $is_local ) {
						$webhook_url = '';
					}

					// For recurring donations, create a Mollie customer first so Mollie can
					// store the mandate and attach a subscription after the first payment.
					$sequence_type = 'oneoff';
					$customer_id   = '';

					if ( 'one_time' !== $frequency ) {
						$customer      = $service->create_customer( $name, $email );
						$customer_id   = $customer->id;
						$sequence_type = 'first';
						$db->update_mollie_customer_id( (int) $donation_id, $customer_id );
					}

					$payment = $service->create_payment(
						(int) $donation_id,
						$amount,
						$name,
						$return_url,
						$webhook_url,
						$sequence_type,
						$customer_id
					);

					$db->update_mollie_payment_id( (int) $donation_id, $payment->id );

					$checkout_url  = $payment->getCheckoutUrl();
					$checkout_host = wp_parse_url( $checkout_url, PHP_URL_HOST );
					add_filter(
						'allowed_redirect_hosts',
						static function ( $hosts ) use ( $checkout_host ) {
							if ( $checkout_host ) {
								$hosts[] = $checkout_host;
							}
							return $hosts;
						}
					);
					wp_safe_redirect( $checkout_url );
					exit;
				} catch ( \Mollie\Api\Exceptions\MollieException $e ) {
					if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
                        // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
						error_log( 'FTB Mollie payment error: ' . $e->getMessage() );
					}
					wp_die(
						esc_html__( 'De betalingsservice is tijdelijk niet beschikbaar. Probeer het later opnieuw.', 'ftb-donation-form' ),
						esc_html__( 'Betaling mislukt', 'ftb-donation-form' ),
						array(
							'back_link' => true,
							'response'  => 503,
						)
					);
				}
			}
		}

		ob_start();
		include 'partials/ftb-donation-form-public-display.php';
		return ob_get_clean();
	}
}
