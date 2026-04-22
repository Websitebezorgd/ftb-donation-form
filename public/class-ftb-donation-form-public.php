<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * The public-facing functionality of the plugin.
 *
 * @since      1.0.0
 * @package    FTB_Donation_Form
 * @subpackage FTB_Donation_Form/public
 */
class FTB_Donation_Form_Public {

    private $plugin_name;
    private $version;

    public function __construct( $plugin_name, $version ) {
        $this->plugin_name = $plugin_name;
        $this->version     = $version;
    }

    public function enqueue_styles() {
        wp_enqueue_style(
            $this->plugin_name,
            plugin_dir_url( __FILE__ ) . 'css/ftb-donation-form-public.css',
            [],
            $this->version,
            'all'
        );
    }

    public function enqueue_scripts() {
        wp_enqueue_script(
            $this->plugin_name,
            plugin_dir_url( __FILE__ ) . 'js/ftb-donation-form-public.js',
            [],
            $this->version,
            true
        );

        $min_amount = (float) get_option( 'ftb_min_custom_amount', '1' );

        wp_localize_script(
            $this->plugin_name,
            'ftbDonationForm',
            [
                'allowCustomAmount' => (bool) get_option( 'ftb_allow_custom_amount', '1' ),
                'minCustomAmount'   => $min_amount,
                'i18n'              => [
                    'errorFrequency'   => __( 'Kies een frequentie.', 'ftb-donation-form' ),
                    'errorAmount'      => __( 'Kies een bedrag.', 'ftb-donation-form' ),
                    /* translators: %s: minimum amount, e.g. "1" */
                    'errorCustom'      => sprintf( __( 'Je eigen bedrag moet minimaal €%s zijn.', 'ftb-donation-form' ), $min_amount ),
                    'errorName'        => __( 'Vul je volledige naam in.', 'ftb-donation-form' ),
                    'errorEmail'       => __( 'Vul een geldig e-mailadres in.', 'ftb-donation-form' ),
                    'errorGdpr'        => __( 'Je moet akkoord gaan met de privacyverklaring om te doneren.', 'ftb-donation-form' ),
                    'errorSummary'     => __( 'Controleer de volgende fouten:', 'ftb-donation-form' ),
                ],
            ]
        );
    }

    public function register_shortcodes() {
        add_shortcode( 'ftb_donation_form', [ $this, 'render_donation_form' ] );
    }

    public function render_donation_form( $atts = [] ) {
        $default_title = get_option( 'ftb_form_heading', '' ) ?: __( 'Doneer nu', 'ftb-donation-form' );
        $atts  = shortcode_atts( [ 'title' => $default_title ], $atts, 'ftb_donation_form' );
        $title = sanitize_text_field( $atts['title'] );

        $errors     = [];
        $old_values = [];
        $success    = false;

        // Read admin settings (needed by both processing and template)
        $form_fields      = get_option( 'ftb_form_fields', [] );
        $amount_options   = array_values( array_filter( (array) get_option( 'ftb_amount_options', [ '5', '10', '25' ] ) ) );
        $allow_custom      = (bool) get_option( 'ftb_allow_custom_amount', '1' );
        $min_custom_amount = (float) get_option( 'ftb_min_custom_amount', '1' );
        $enable_recurring  = (bool) get_option( 'ftb_enable_recurring', '1' );
        $privacy_url      = get_option( 'ftb_privacy_url', '' );

        if ( isset( $_POST['ftb_donation_nonce'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing -- verified on next line
            $nonce = sanitize_text_field( wp_unslash( $_POST['ftb_donation_nonce'] ) );

            if ( ! wp_verify_nonce( $nonce, 'ftb_donation_submit' ) ) {
                wp_die( esc_html__( 'Beveiligingscontrole mislukt. Probeer het opnieuw.', 'ftb-donation-form' ) );
            }

            // Sanitize all inputs
            // phpcs:disable WordPress.Security.ValidatedSanitizedInput.InputNotValidated
            $frequency     = sanitize_text_field( wp_unslash( $_POST['ftb_frequency'] ?? '' ) );
            $amount_raw    = sanitize_text_field( wp_unslash( $_POST['ftb_amount'] ?? '' ) );
            $custom_amount = sanitize_text_field( wp_unslash( $_POST['ftb_custom_amount'] ?? '' ) );
            $name          = sanitize_text_field( wp_unslash( $_POST['ftb_name'] ?? '' ) );
            $email         = sanitize_email( wp_unslash( $_POST['ftb_email'] ?? '' ) );
            $phone         = sanitize_text_field( wp_unslash( $_POST['ftb_phone'] ?? '' ) );
            $street        = sanitize_text_field( wp_unslash( $_POST['ftb_street'] ?? '' ) );
            $house_number  = sanitize_text_field( wp_unslash( $_POST['ftb_house_number'] ?? '' ) );
            $postal_code   = sanitize_text_field( wp_unslash( $_POST['ftb_postal_code'] ?? '' ) );
            $city          = sanitize_text_field( wp_unslash( $_POST['ftb_city'] ?? '' ) );
            $gdpr          = isset( $_POST['ftb_gdpr'] ) ? '1' : '0';
            // phpcs:enable

            // Validate frequency
            $allowed_frequencies = [ 'one_time', 'monthly', 'yearly' ];
            if ( ! in_array( $frequency, $allowed_frequencies, true ) ) {
                $errors['frequency'] = __( 'Kies een frequentie.', 'ftb-donation-form' );
            }

            // Validate amount
            $amount = 0.0;
            if ( $amount_raw === 'custom' && $allow_custom ) {
                $amount = (float) str_replace( ',', '.', $custom_amount );
                if ( $amount < $min_custom_amount ) {
                    /* translators: %s: minimum amount, e.g. "1" */
                    $errors['amount'] = sprintf( __( 'Je eigen bedrag moet minimaal €%s zijn.', 'ftb-donation-form' ), $min_custom_amount );
                }
            } elseif ( ! empty( $amount_raw ) ) {
                $amount = (float) $amount_raw;
            } else {
                $errors['amount'] = __( 'Kies een bedrag.', 'ftb-donation-form' );
            }

            // Validate name
            if ( empty( $name ) ) {
                $errors['name'] = __( 'Vul je volledige naam in.', 'ftb-donation-form' );
            }

            // Validate email
            if ( empty( $email ) || ! is_email( $email ) ) {
                $errors['email'] = __( 'Vul een geldig e-mailadres in.', 'ftb-donation-form' );
            }

            // Validate GDPR consent
            if ( $gdpr !== '1' ) {
                $errors['gdpr'] = __( 'Je moet akkoord gaan met de privacyverklaring om te doneren.', 'ftb-donation-form' );
            }

            // Preserve submitted values for re-rendering the form on error
            $old_values = [
                'frequency'    => $frequency,
                'amount'       => $amount_raw,
                'custom_amount'=> $custom_amount,
                'name'         => $name,
                'email'        => $email,
                'phone'        => $phone,
                'street'       => $street,
                'house_number' => $house_number,
                'postal_code'  => $postal_code,
                'city'         => $city,
                'gdpr'         => $gdpr,
            ];

            if ( empty( $errors ) ) {
                $db = new FTB_DB();
                $db->insert_donation( [
                    'donor_name'         => $name,
                    'donor_email'        => $email,
                    'donor_phone'        => $phone,
                    'donor_street'       => $street,
                    'donor_house_number' => $house_number,
                    'donor_postal_code'  => $postal_code,
                    'donor_city'         => $city,
                    'amount'             => $amount,
                    'frequency'          => $frequency,
                ] );

                // TODO Phase 4: initiate Mollie payment and redirect to payment URL.
                // $service = new FTB_Mollie_Service();
                // $payment = $service->create_payment( ... );
                // wp_redirect( $payment->getCheckoutUrl() ); exit;
                $success = true;
            }
        }

        ob_start();
        include 'partials/ftb-donation-form-public-display.php';
        return ob_get_clean();
    }

}
