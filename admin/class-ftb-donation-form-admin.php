<?php
/**
 * The admin-specific functionality of the plugin.
 *
 * @since      1.0.0
 * @package    FTB_Donation_Form
 * @subpackage FTB_Donation_Form/admin
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class FTB_Donation_Form_Admin {

    private $plugin_name;
    private $version;

    public function __construct( $plugin_name, $version ) {
        $this->plugin_name = $plugin_name;
        $this->version     = $version;
        add_filter( 'option_page_capability_ftb_donation_form_settings', function() {
            return 'ftb_manage_settings';
        } );
    }

    /**
     * Enqueue admin styles only on our plugin page.
     */
    public function enqueue_styles( $hook ) {
        if ( 'toplevel_page_ftb-donation-form' !== $hook ) {
            return;
        }
        wp_enqueue_style(
            $this->plugin_name,
            plugin_dir_url( __FILE__ ) . 'css/ftb-donation-form-admin.css',
            [],
            filemtime( plugin_dir_path( __FILE__ ) . 'css/ftb-donation-form-admin.css' )
        );
    }

    /**
     * Enqueue admin scripts only on our plugin page.
     */
    public function enqueue_scripts( $hook ) {
        if ( 'toplevel_page_ftb-donation-form' !== $hook ) {
            return;
        }
        wp_enqueue_script(
            $this->plugin_name,
            plugin_dir_url( __FILE__ ) . 'js/ftb-donation-form-admin.js',
            [ 'jquery' ],
            $this->version,
            true
        );
    }

    /**
     * Register the admin menu page.
     */
    public function add_plugin_admin_menu() {
        add_menu_page(
            __( 'Donatieformulier instellingen', 'ftb-donation-form' ),
            __( 'Donatieformulier', 'ftb-donation-form' ),
            'ftb_manage_settings',
            'ftb-donation-form',
            [ $this, 'display_plugin_setup_page' ],
            'dashicons-heart',
            30
        );
    }

    /**
     * Register all settings, sections and fields via the WordPress Settings API.
     */
    public function register_settings() {

        // ── Mollie ────────────────────────────────────────────────────────────

        register_setting( 'ftb_donation_form_settings', 'ftb_mollie_api_key', [
            'sanitize_callback' => 'sanitize_text_field',
        ] );
        register_setting( 'ftb_donation_form_settings', 'ftb_mollie_test_mode', [
            'sanitize_callback' => 'absint',
        ] );

        add_settings_section(
            'ftb_section_mollie',
            __( 'Mollie instellingen', 'ftb-donation-form' ),
            '__return_false',
            'ftb_donation_form_settings'
        );

        add_settings_field(
            'ftb_mollie_api_key',
            __( 'API sleutel', 'ftb-donation-form' ),
            [ $this, 'field_mollie_api_key' ],
            'ftb_donation_form_settings',
            'ftb_section_mollie',
            [ 'label_for' => 'ftb_mollie_api_key' ]
        );

        add_settings_field(
            'ftb_mollie_test_mode',
            __( 'Testmodus', 'ftb-donation-form' ),
            [ $this, 'field_mollie_test_mode' ],
            'ftb_donation_form_settings',
            'ftb_section_mollie'
        );

        // ── Titel ───────────────────────────────────────────────────────────────

        register_setting( 'ftb_donation_form_settings', 'ftb_form_heading', [
            'sanitize_callback' => 'sanitize_text_field',
        ] );

        add_settings_section(
            'ftb_section_kop',
            __( 'Titel', 'ftb-donation-form' ),
            '__return_false',
            'ftb_donation_form_settings'
        );

        add_settings_field(
            'ftb_form_heading',
            __( 'Titel formulier', 'ftb-donation-form' ),
            [ $this, 'field_form_heading' ],
            'ftb_donation_form_settings',
            'ftb_section_heading',
            [ 'label_for' => 'ftb_form_heading' ]
        );

        // ── Formuliervelden ───────────────────────────────────────────────────

        register_setting( 'ftb_donation_form_settings', 'ftb_form_fields', [
            'sanitize_callback' => [ $this, 'sanitize_form_fields' ],
        ] );

        add_settings_section(
            'ftb_section_fields',
            __( 'Formuliervelden', 'ftb-donation-form' ),
            [ $this, 'section_fields_description' ],
            'ftb_donation_form_settings'
        );

        add_settings_field(
            'ftb_form_fields',
            '',
            [ $this, 'field_form_field_toggles' ],
            'ftb_donation_form_settings',
            'ftb_section_fields'
        );

        // ── Bedragopties ──────────────────────────────────────────────────────

        register_setting( 'ftb_donation_form_settings', 'ftb_amount_options', [
            'sanitize_callback' => [ $this, 'sanitize_amount_options' ],
        ] );
        register_setting( 'ftb_donation_form_settings', 'ftb_allow_custom_amount', [
            'sanitize_callback' => 'absint',
        ] );

        add_settings_section(
            'ftb_section_amounts',
            __( 'Bedragopties', 'ftb-donation-form' ),
            [ $this, 'section_amounts_description' ],
            'ftb_donation_form_settings'
        );

        add_settings_field(
            'ftb_amount_options',
            __( 'Vaste bedragen (€)', 'ftb-donation-form' ),
            [ $this, 'field_amount_options' ],
            'ftb_donation_form_settings',
            'ftb_section_amounts',
            [ 'label_for' => 'ftb_amount_options_0' ]
        );

        add_settings_field(
            'ftb_allow_custom_amount',
            __( 'Eigen bedrag', 'ftb-donation-form' ),
            [ $this, 'field_allow_custom_amount' ],
            'ftb_donation_form_settings',
            'ftb_section_amounts'
        );

        // ── Frequentie ────────────────────────────────────────────────────────

        add_option( 'ftb_enable_recurring', '1' );

        register_setting( 'ftb_donation_form_settings', 'ftb_enable_recurring', [
            'sanitize_callback' => 'absint',
        ] );

        add_settings_section(
            'ftb_section_frequency',
            '',
            '__return_false',
            'ftb_donation_form_settings'
        );

        // ── Privacyverklaring ─────────────────────────────────────────────────

        register_setting( 'ftb_donation_form_settings', 'ftb_privacy_url', [
            'sanitize_callback' => 'esc_url_raw',
        ] );

        add_settings_section(
            'ftb_section_privacy',
            __( 'Privacyverklaring', 'ftb-donation-form' ),
            '__return_false',
            'ftb_donation_form_settings'
        );

        add_settings_field(
            'ftb_privacy_url',
            __( 'Link naar privacyverklaring', 'ftb-donation-form' ),
            [ $this, 'field_privacy_url' ],
            'ftb_donation_form_settings',
            'ftb_section_privacy',
            [ 'label_for' => 'ftb_privacy_url' ]
        );

        // ── Na betaling ───────────────────────────────────────────────────────

        register_setting( 'ftb_donation_form_settings', 'ftb_post_payment_behavior', [
            'sanitize_callback' => 'sanitize_text_field',
        ] );
        register_setting( 'ftb_donation_form_settings', 'ftb_post_payment_message', [
            'sanitize_callback' => 'wp_kses_post',
        ] );
        register_setting( 'ftb_donation_form_settings', 'ftb_post_payment_redirect_url', [
            'sanitize_callback' => 'esc_url_raw',
        ] );

        add_settings_section(
            'ftb_section_post_payment',
            __( 'Na betaling', 'ftb-donation-form' ),
            '__return_false',
            'ftb_donation_form_settings'
        );

        add_settings_field(
            'ftb_post_payment_behavior',
            __( 'Actie na betaling', 'ftb-donation-form' ),
            [ $this, 'field_post_payment_behavior' ],
            'ftb_donation_form_settings',
            'ftb_section_post_payment',
            [ 'label_for' => 'ftb_post_payment_behavior' ]
        );

        add_settings_field(
            'ftb_post_payment_message_field',
            __( 'Bedankbericht', 'ftb-donation-form' ),
            [ $this, 'field_post_payment_message' ],
            'ftb_donation_form_settings',
            'ftb_section_post_payment',
            [ 'label_for' => 'ftb_post_payment_message' ]
        );

        add_settings_field(
            'ftb_post_payment_redirect_field',
            __( 'Doorstuur-URL', 'ftb-donation-form' ),
            [ $this, 'field_post_payment_redirect_url' ],
            'ftb_donation_form_settings',
            'ftb_section_post_payment',
            [ 'label_for' => 'ftb_post_payment_redirect_url' ]
        );
    }

    // ── Section descriptions ───────────────────────────────────────────────────

    public function section_fields_description() {
        echo '<p>' . esc_html__( 'Naam en e-mailadres zijn altijd verplicht. Vink de overige velden aan die je wilt tonen.', 'ftb-donation-form' ) . '</p>';
    }

    public function section_amounts_description() {
        echo '<p>' . esc_html__( 'Voer de vaste bedragen in die de donateur kan kiezen. Scheid bedragen met een komma. De donateur kan altijd ook een eigen bedrag invullen.', 'ftb-donation-form' ) . '</p>';
    }

    // ── Field renderers ────────────────────────────────────────────────────────

    public function field_mollie_api_key() {
        $value = get_option( 'ftb_mollie_api_key', '' );
        ?>
        <div class="ftb-admin-form__field">
        <input
            type="password"
            id="ftb_mollie_api_key"
            name="ftb_mollie_api_key"
            value="<?php echo esc_attr( $value ); ?>"
            class="regular-text"
            autocomplete="new-password"
        />
        </div>
        <?php
    }

    public function field_mollie_test_mode() {
        $value = get_option( 'ftb_mollie_test_mode', '1' );
        ?>
        <div class="ftb-admin-form__field">
        <label for="ftb_mollie_test_mode">
            <input
                type="checkbox"
                id="ftb_mollie_test_mode"
                name="ftb_mollie_test_mode"
                value="1"
                <?php checked( '1', $value ); ?>
            />
            <?php esc_html_e( 'Testmodus inschakelen (gebruik test API sleutel)', 'ftb-donation-form' ); ?>
        </label>
        </div>
        <?php
    }

    public function field_form_field_toggles() {
        $fields  = get_option( 'ftb_form_fields', [] );
        $options = [
            'phone'        => __( 'Telefoonnummer', 'ftb-donation-form' ),
            'street'       => __( 'Straat', 'ftb-donation-form' ),
            'house_number' => __( 'Huisnummer', 'ftb-donation-form' ),
            'postal_code'  => __( 'Postcode', 'ftb-donation-form' ),
            'city'         => __( 'Plaats', 'ftb-donation-form' ),
        ];

        echo '<p class="ftb-admin-form__group-label">' . esc_html__( 'Optionele velden', 'ftb-donation-form' ) . '</p>';
        echo '<div class="ftb-admin-form__field"><ul class="ftb-checkbox-list">';
        foreach ( $options as $key => $label ) {
            $checked = ! empty( $fields[ $key ] );
            printf(
                '<li><label><input type="checkbox" name="ftb_form_fields[%s]" id="ftb_form_field_%s" value="1" %s> %s</label></li>',
                esc_attr( $key ),
                esc_attr( $key ),
                checked( $checked, true, false ),
                esc_html( $label )
            );
        }
        echo '</ul></div>';
    }

    public function field_amount_options() {
        $amounts  = array_values( (array) get_option( 'ftb_amount_options', [ '5', '10', '25' ] ) );
        $defaults = [ '5', '10', '25' ];

        echo '<div class="ftb-admin-form__field"><div class="ftb-amount-inputs">';
        for ( $i = 0; $i < 3; $i++ ) {
            $value = isset( $amounts[ $i ] ) ? $amounts[ $i ] : ( $defaults[ $i ] ?? '' );
            printf(
                '<div class="ftb-amount-input">
                    <span class="ftb-amount-input__prefix" aria-hidden="true">€</span>
                    <input
                        type="number"
                        name="ftb_amount_options[%d]"
                        id="ftb_amount_options_%d"
                        value="%s"
                        min="1"
                        step="1"
                        class="small-text"
                        aria-label="%s %d"
                    />
                </div>',
                absint( $i ),
                absint( $i ),
                esc_attr( $value ),
                esc_attr__( 'Bedragoptie', 'ftb-donation-form' ),
                absint( $i + 1 )
            );
        }
        echo '</div></div>';
    }

    public function field_allow_custom_amount() {
        $value = get_option( 'ftb_allow_custom_amount', '1' );
        ?>
        <div class="ftb-admin-form__field">
        <label for="ftb_allow_custom_amount">
            <input
                type="checkbox"
                id="ftb_allow_custom_amount"
                name="ftb_allow_custom_amount"
                value="1"
                <?php checked( '1', $value ); ?>
            />
            <?php esc_html_e( 'Donateur mag een eigen bedrag invullen', 'ftb-donation-form' ); ?>
        </label>
        </div>
        <?php
    }

    public function field_form_heading() {
        $value = get_option( 'ftb_form_heading', '' );
        ?>
        <div class="ftb-admin-form__field">
            <input
                type="text"
                id="ftb_form_heading"
                name="ftb_form_heading"
                value="<?php echo esc_attr( $value ); ?>"
                class="regular-text"
                placeholder="<?php esc_attr_e( 'Doneer nu', 'ftb-donation-form' ); ?>"
            />
            <p class="description"><?php esc_html_e( 'Laat leeg om de standaardtekst "Doneer nu" te gebruiken.', 'ftb-donation-form' ); ?></p>
        </div>
        <?php
    }

    public function field_privacy_url() {
        $value = get_option( 'ftb_privacy_url', '' );
        ?>
        <div class="ftb-admin-form__field">
        <input
            type="url"
            id="ftb_privacy_url"
            name="ftb_privacy_url"
            value="<?php echo esc_attr( $value ); ?>"
            class="regular-text"
            placeholder="https://jouwwebsite.nl/privacyverklaring"
        />
        </div>
        <?php
    }

    public function field_post_payment_behavior() {
        $value = get_option( 'ftb_post_payment_behavior', 'message' );
        ?>
        <div class="ftb-admin-form__field">
        <select id="ftb_post_payment_behavior" name="ftb_post_payment_behavior">
            <option value="message" <?php selected( $value, 'message' ); ?>>
                <?php esc_html_e( 'Toon een bedankbericht', 'ftb-donation-form' ); ?>
            </option>
            <option value="redirect" <?php selected( $value, 'redirect' ); ?>>
                <?php esc_html_e( 'Doorsturen naar een pagina', 'ftb-donation-form' ); ?>
            </option>
        </select>
        </div>
        <?php
    }

    public function field_post_payment_message() {
        $message = get_option( 'ftb_post_payment_message', __( 'Hartelijk dank voor je donatie!', 'ftb-donation-form' ) );
        ?>
        <div class="ftb-conditional ftb-admin-form__field" data-show-when="ftb_post_payment_behavior=message">
            <textarea
                id="ftb_post_payment_message"
                name="ftb_post_payment_message"
                rows="3"
                class="large-text"
            ><?php echo esc_textarea( $message ); ?></textarea>
        </div>
        <?php
    }

    public function field_post_payment_redirect_url() {
        $url = get_option( 'ftb_post_payment_redirect_url', '' );
        ?>
        <div class="ftb-conditional ftb-admin-form__field" data-show-when="ftb_post_payment_behavior=redirect">
            <input
                type="url"
                id="ftb_post_payment_redirect_url"
                name="ftb_post_payment_redirect_url"
                value="<?php echo esc_attr( $url ); ?>"
                class="regular-text"
                placeholder="https://jouwwebsite.nl/bedankt"
            />
        </div>
        <?php
    }

    // ── Sanitization callbacks ─────────────────────────────────────────────────

    public function sanitize_form_fields( $input ) {
        $allowed = [ 'phone', 'street', 'house_number', 'postal_code', 'city' ];
        $clean   = [];
        foreach ( $allowed as $key ) {
            $clean[ $key ] = ! empty( $input[ $key ] ) ? '1' : '0';
        }
        return $clean;
    }

    public function sanitize_amount_options( $input ) {
        $amounts = [];
        foreach ( array_slice( (array) $input, 0, 3 ) as $value ) {
            $amount = (float) $value;
            if ( $amount > 0 ) {
                $amounts[] = (string) $amount;
            }
        }
        return ! empty( $amounts ) ? $amounts : [ '5', '10', '25' ];
    }

    /**
     * Render the settings page.
     */
    public function display_plugin_setup_page() {
        if ( ! current_user_can( 'ftb_manage_settings' ) ) {
            wp_die( esc_html__( 'Je hebt onvoldoende rechten om deze pagina te bekijken.', 'ftb-donation-form' ) );
        }
        include_once 'partials/ftb-donation-form-admin-display.php';
    }
}
