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
	private $settings_hook    = '';
	private $submissions_hook = '';

	public function __construct( $plugin_name, $version ) {
		$this->plugin_name = $plugin_name;
		unset( $version ); // Version unused: filemtime() used for cache-busting instead.
		add_filter(
			'option_page_capability_ftb_donation_form_settings',
			function () {
				return 'ftb_manage_settings';
			}
		);
		add_filter(
			'option_page_capability_ftb_donation_form_danger_settings',
			function () {
				return 'manage_options';
			}
		);
	}

	/**
	 * Enqueue admin styles only on our plugin page.
	 */
	private function is_plugin_page( $hook ) {
		return in_array( $hook, array( $this->settings_hook, $this->submissions_hook ), true );
	}

	public function enqueue_styles( $hook ) {
		if ( ! $this->is_plugin_page( $hook ) ) {
			return;
		}
		wp_enqueue_style( 'wp-color-picker' );
		wp_enqueue_style(
			$this->plugin_name,
			plugin_dir_url( __FILE__ ) . 'css/ftb-donation-form-admin.css',
			array( 'wp-color-picker' ),
			filemtime( plugin_dir_path( __FILE__ ) . 'css/ftb-donation-form-admin.css' )
		);
	}

	/**
	 * Enqueue admin scripts only on our plugin page.
	 */
	public function enqueue_scripts( $hook ) {
		if ( ! $this->is_plugin_page( $hook ) ) {
			return;
		}
		wp_enqueue_script(
			$this->plugin_name,
			plugin_dir_url( __FILE__ ) . 'js/ftb-donation-form-admin.js',
			array( 'jquery', 'wp-color-picker' ),
			filemtime( plugin_dir_path( __FILE__ ) . 'js/ftb-donation-form-admin.js' ),
			true
		);
		wp_localize_script(
			$this->plugin_name,
			'ftbAdmin',
			array(
				'showApiKey'          => __( 'API sleutel tonen', 'ftb-donation-form' ),
				'hideApiKey'          => __( 'API sleutel verbergen', 'ftb-donation-form' ),
				'confirmDeleteApiKey' => __( 'Weet je zeker dat je de Mollie API sleutel wilt verwijderen? Zonder sleutel kunnen er geen donaties worden ontvangen.', 'ftb-donation-form' ),
			)
		);
	}

	/**
	 * Register the admin menu page.
	 */
	public function add_plugin_admin_menu() {
		$this->settings_hook = add_menu_page(
			__( 'Donatieformulier instellingen', 'ftb-donation-form' ),
			__( 'Donatieformulier', 'ftb-donation-form' ),
			'ftb_manage_settings',
			'ftb-donation-form',
			array( $this, 'display_plugin_setup_page' ),
			'dashicons-heart',
			30
		);

		add_submenu_page(
			'ftb-donation-form',
			__( 'Instellingen', 'ftb-donation-form' ),
			__( 'Instellingen', 'ftb-donation-form' ),
			'ftb_manage_settings',
			'ftb-donation-form',
			array( $this, 'display_plugin_setup_page' )
		);

		$this->submissions_hook = add_submenu_page(
			'ftb-donation-form',
			__( 'Donaties', 'ftb-donation-form' ),
			__( 'Donaties', 'ftb-donation-form' ),
			'ftb_manage_settings',
			'ftb-submissions',
			array( $this, 'display_submissions_page' )
		);

	}

	/**
	 * Register all settings, sections and fields via the WordPress Settings API.
	 */
	public function register_settings() {

		// ── Mollie ────────────────────────────────────────────────────────────

		register_setting(
			'ftb_donation_form_settings',
			'ftb_mollie_api_key',
			array(
				'sanitize_callback' => array( $this, 'sanitize_mollie_api_key' ),
			)
		);
		register_setting(
			'ftb_donation_form_settings',
			'ftb_mollie_test_mode',
			array(
				'sanitize_callback' => 'absint',
			)
		);

		add_settings_section(
			'ftb_section_mollie',
			__( 'Mollie instellingen', 'ftb-donation-form' ),
			'__return_false',
			'ftb_donation_form_settings'
		);

		add_settings_field(
			'ftb_mollie_api_key',
			__( 'API sleutel', 'ftb-donation-form' ),
			array( $this, 'field_mollie_api_key' ),
			'ftb_donation_form_settings',
			'ftb_section_mollie',
			array( 'label_for' => 'ftb_mollie_api_key' )
		);

		add_settings_field(
			'ftb_mollie_test_mode',
			__( 'Testmodus', 'ftb-donation-form' ),
			array( $this, 'field_mollie_test_mode' ),
			'ftb_donation_form_settings',
			'ftb_section_mollie'
		);

		// ── Titel ───────────────────────────────────────────────────────────────

		register_setting(
			'ftb_donation_form_settings',
			'ftb_form_heading',
			array(
				'sanitize_callback' => 'sanitize_text_field',
			)
		);

		add_settings_section(
			'ftb_section_heading',
			__( 'Titel', 'ftb-donation-form' ),
			'__return_false',
			'ftb_donation_form_settings'
		);

		add_settings_field(
			'ftb_form_heading',
			__( 'Titel formulier', 'ftb-donation-form' ),
			array( $this, 'field_form_heading' ),
			'ftb_donation_form_settings',
			'ftb_section_heading',
			array( 'label_for' => 'ftb_form_heading' )
		);

		// ── Formuliervelden ───────────────────────────────────────────────────

		register_setting(
			'ftb_donation_form_settings',
			'ftb_form_fields',
			array(
				'sanitize_callback' => array( $this, 'sanitize_form_fields' ),
			)
		);

		add_settings_section(
			'ftb_section_fields',
			__( 'Formuliervelden', 'ftb-donation-form' ),
			array( $this, 'section_fields_description' ),
			'ftb_donation_form_settings'
		);

		add_settings_field(
			'ftb_form_fields',
			'',
			array( $this, 'field_form_field_toggles' ),
			'ftb_donation_form_settings',
			'ftb_section_fields'
		);

		// ── Bedragopties ──────────────────────────────────────────────────────

		register_setting(
			'ftb_donation_form_settings',
			'ftb_amount_options',
			array(
				'sanitize_callback' => array( $this, 'sanitize_amount_options' ),
			)
		);
		register_setting(
			'ftb_donation_form_settings',
			'ftb_show_preset_amounts',
			array(
				'sanitize_callback' => array( $this, 'sanitize_show_preset_amounts' ),
			)
		);
		register_setting(
			'ftb_donation_form_settings',
			'ftb_allow_custom_amount',
			array(
				'sanitize_callback' => 'absint',
			)
		);
		register_setting(
			'ftb_donation_form_settings',
			'ftb_min_custom_amount',
			array(
				'sanitize_callback' => array( $this, 'sanitize_min_custom_amount' ),
			)
		);

		// ── Frequentie ────────────────────────────────────────────────────────

		register_setting(
			'ftb_donation_form_settings',
			'ftb_enable_recurring',
			array(
				'sanitize_callback' => 'absint',
			)
		);

		// ── Privacyverklaring ─────────────────────────────────────────────────

		register_setting(
			'ftb_donation_form_settings',
			'ftb_privacy_url',
			array(
				'sanitize_callback' => 'esc_url_raw',
			)
		);

		add_settings_section(
			'ftb_section_privacy',
			__( 'Privacyverklaring', 'ftb-donation-form' ),
			'__return_false',
			'ftb_donation_form_settings'
		);

		add_settings_field(
			'ftb_privacy_url',
			__( 'Link naar privacyverklaring', 'ftb-donation-form' ),
			array( $this, 'field_privacy_url' ),
			'ftb_donation_form_settings',
			'ftb_section_privacy',
			array( 'label_for' => 'ftb_privacy_url' )
		);

		// ── E-mailnotificaties ────────────────────────────────────────────────

		register_setting(
			'ftb_donation_form_settings',
			'ftb_email_donor_confirmation',
			array(
				'sanitize_callback' => 'absint',
			)
		);
		register_setting(
			'ftb_donation_form_settings',
			'ftb_email_donor_subject',
			array(
				'sanitize_callback' => 'sanitize_text_field',
			)
		);
		register_setting(
			'ftb_donation_form_settings',
			'ftb_email_donor_body',
			array(
				'sanitize_callback' => 'sanitize_textarea_field',
			)
		);
		register_setting(
			'ftb_donation_form_settings',
			'ftb_email_admin_notification',
			array(
				'sanitize_callback' => 'absint',
			)
		);
		register_setting(
			'ftb_donation_form_settings',
			'ftb_email_sender_address',
			array(
				'sanitize_callback' => 'sanitize_email',
			)
		);

		// ── Na betaling ───────────────────────────────────────────────────────

		register_setting(
			'ftb_donation_form_settings',
			'ftb_post_payment_behavior',
			array(
				'sanitize_callback' => function ( $value ) {
					return in_array( $value, array( 'message', 'redirect' ), true ) ? $value : 'message';
				},
			)
		);
		register_setting(
			'ftb_donation_form_settings',
			'ftb_post_payment_message',
			array(
				'sanitize_callback' => 'sanitize_textarea_field',
			)
		);
		register_setting(
			'ftb_donation_form_settings',
			'ftb_post_payment_redirect_url',
			array(
				'sanitize_callback' => 'esc_url_raw',
			)
		);

		add_settings_section(
			'ftb_section_post_payment',
			__( 'Na betaling', 'ftb-donation-form' ),
			'__return_false',
			'ftb_donation_form_settings'
		);

		add_settings_field(
			'ftb_post_payment_behavior',
			__( 'Actie na betaling', 'ftb-donation-form' ),
			array( $this, 'field_post_payment_behavior' ),
			'ftb_donation_form_settings',
			'ftb_section_post_payment',
			array( 'label_for' => 'ftb_post_payment_behavior' )
		);

		// ── Stijl ─────────────────────────────────────────────────────────────

		register_setting(
			'ftb_donation_form_settings',
			'ftb_form_style',
			array(
				'sanitize_callback' => function ( $value ) {
					return in_array( $value, array( 'card', 'flat', 'minimal' ), true ) ? $value : 'card';
				},
			)
		);

		register_setting(
			'ftb_donation_form_settings',
			'ftb_form_primary_color',
			array(
				'sanitize_callback' => 'sanitize_hex_color',
			)
		);

		// ── Bij verwijderen (eigen groep: alleen beheerders zien dit formulier) ──

		register_setting(
			'ftb_donation_form_danger_settings',
			'ftb_delete_data_on_uninstall',
			array(
				'sanitize_callback' => 'absint',
			)
		);
	}

	// ── Section descriptions ───────────────────────────────────────────────────

	public function section_fields_description() {
		echo '<p>' . esc_html__( 'Naam en e-mailadres zijn altijd verplicht. Vink de overige velden aan die je wilt tonen.', 'ftb-donation-form' ) . '</p>';
	}

	// ── Field renderers ────────────────────────────────────────────────────────

	/**
	 * Render the Mollie API key field.
	 *
	 * The stored key is never written into the page: only a masked version is shown.
	 * The input always starts empty; the eye button can therefore only reveal a key
	 * the user is typing or pasting, never the stored one.
	 */
	public function field_mollie_api_key() {
		$stored = (string) get_option( 'ftb_mollie_api_key', '' );
		?>
		<div class="ftb-admin-form__field">
			<?php if ( $stored ) : ?>
				<p class="ftb-api-key-current" id="ftb_mollie_api_key_current">
					<?php esc_html_e( 'Huidige sleutel:', 'ftb-donation-form' ); ?>
					<code><?php echo esc_html( self::mask_api_key( $stored ) ); ?></code>
					<a
						class="button ftb-api-key-delete"
						href="<?php echo esc_url( wp_nonce_url( admin_url( 'admin.php?page=ftb-donation-form&ftb_action=delete_api_key' ), 'ftb_delete_api_key' ) ); ?>">
						<span class="dashicons dashicons-trash" aria-hidden="true"></span>
						<?php esc_html_e( 'Verwijderen', 'ftb-donation-form' ); ?>
					</a>
				</p>
			<?php endif; ?>
			<div class="ftb-password-wrap">
				<input
					type="password"
					id="ftb_mollie_api_key"
					name="ftb_mollie_api_key"
					value=""
					class="regular-text"
					autocomplete="new-password"
					placeholder="<?php echo $stored ? esc_attr__( 'Plak hier je nieuwe API sleutel', 'ftb-donation-form' ) : esc_attr__( 'Plak hier je Mollie API sleutel', 'ftb-donation-form' ); ?>"
					<?php echo $stored ? 'aria-describedby="ftb_mollie_api_key_current"' : ''; ?> />
				<button
					type="button"
					class="ftb-toggle-password"
					aria-controls="ftb_mollie_api_key"
					aria-label="<?php esc_attr_e( 'API sleutel tonen', 'ftb-donation-form' ); ?>"
					aria-pressed="false">
					<span class="dashicons dashicons-visibility" aria-hidden="true"></span>
				</button>
			</div>
		</div>
		<?php
	}

	/**
	 * Masked version of an API key: the prefix (test_ / live_) and the last 4 characters.
	 *
	 * @param string $key Full API key.
	 * @return string E.g. "live_••••••••Ab3x".
	 */
	private static function mask_api_key( string $key ): string {
		$prefix = preg_match( '/^[a-z]+_/', $key, $m ) ? $m[0] : '';
		return $prefix . str_repeat( '•', 8 ) . substr( $key, -4 );
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
					<?php checked( '1', $value ); ?> />
				<?php esc_html_e( 'Testmodus inschakelen (gebruik test API sleutel)', 'ftb-donation-form' ); ?>
			</label>
		</div>
		<?php
	}

	public function field_form_field_toggles() {
		$fields  = get_option( 'ftb_form_fields', array() );
		$options = array(
			'phone'        => __( 'Telefoonnummer', 'ftb-donation-form' ),
			'street'       => __( 'Straat', 'ftb-donation-form' ),
			'house_number' => __( 'Huisnummer', 'ftb-donation-form' ),
			'postal_code'  => __( 'Postcode', 'ftb-donation-form' ),
			'city'         => __( 'Plaats', 'ftb-donation-form' ),
		);

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
				placeholder="<?php esc_attr_e( 'Doneer nu', 'ftb-donation-form' ); ?>" />
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
				placeholder="https://jouwwebsite.nl/privacyverklaring" />
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

	// ── Sanitization callbacks ─────────────────────────────────────────────────

	/**
	 * Save the Mollie API key.
	 *
	 * The field is always empty when the page loads (the key is never sent to the browser),
	 * so an empty value means "keep the stored key". Deleting is a separate action
	 * (handle_delete_api_key).
	 */
	public function sanitize_mollie_api_key( $value ): string {
		$value = sanitize_text_field( (string) $value );

		if ( '' === $value ) {
			return (string) get_option( 'ftb_mollie_api_key', '' );
		}

		// Notices are stored per user so they render with ftb-notice styling in the template.
		try {
			$mollie = new \Mollie\Api\MollieApiClient();
			$mollie->setApiKey( $value );
			$mollie->methods->allActive();
		} catch ( \Throwable $e ) {
			// Mollie unreachable or down: the key may be fine, so save it but say it was not checked.
			// (Checked with instanceof: the SDK's docblocks don't declare network errors, but the
			// curl adapter throws them.)
			if ( $e instanceof \Mollie\Api\Exceptions\NetworkRequestException || $e instanceof \Mollie\Api\Exceptions\ServerException ) {
				set_transient( 'ftb_mollie_key_unverified_' . get_current_user_id(), true, 60 );
				return $value;
			}

			// Invalid key (wrong format or refused by Mollie): don't save it, keep the previous key.
			set_transient( 'ftb_mollie_key_error_' . get_current_user_id(), true, 60 );
			return (string) get_option( 'ftb_mollie_api_key', '' );
		}

		return $value;
	}

	public function sanitize_min_custom_amount( $input ) {
		$value = (float) $input;
		return $value >= 1 ? number_format( $value, 2, '.', '' ) : '1.00';
	}

	public function sanitize_form_fields( $input ) {
		$allowed = array( 'phone', 'street', 'house_number', 'postal_code', 'city' );
		$clean   = array();
		foreach ( $allowed as $key ) {
			$clean[ $key ] = ! empty( $input[ $key ] ) ? '1' : '0';
		}
		return $clean;
	}

	public function sanitize_amount_options( $input ) {
		$amounts = array();
		foreach ( array_slice( (array) $input, 0, 3 ) as $value ) {
			$amount = (float) $value;
			if ( $amount > 0 ) {
				$amounts[] = (string) $amount;
			}
		}
		return ! empty( $amounts ) ? $amounts : array( '5', '10', '25' );
	}

	public function sanitize_show_preset_amounts( $input ) {
		$value  = absint( $input );
		$custom = isset( $_POST['ftb_allow_custom_amount'] ) ? absint( wp_unslash( $_POST['ftb_allow_custom_amount'] ) ) : 0; // phpcs:ignore WordPress.Security.NonceVerification.Missing -- nonce verified by Settings API before sanitize callbacks run
		if ( ! $value && ! $custom ) {
			add_settings_error(
				'ftb_donation_form_settings',
				'ftb_amounts_both_hidden',
				__( 'Je moet minimaal één bedragoptie inschakelen. Vaste bedragen zijn hersteld.', 'ftb-donation-form' ),
				'error'
			);
			return 1;
		}
		return $value;
	}

	/**
	 * Render a full admin page: wrap div, shared header, hr, content, shared footer.
	 *
	 * @param string   $title   Page title shown in the header.
	 * @param callable $content Callback that renders the page body.
	 * @param string   $action  Optional escaped HTML for an action button next to the title.
	 * @param bool     $wide    When true, removes the max-width constraint on the wrap.
	 */
	private function render_admin_page( string $title, callable $content, string $action = '', bool $wide = false ): void {
		$page_title  = $title;
		$page_action = $action;
		$partials    = plugin_dir_path( __FILE__ ) . 'partials/';
		?>
		<div class="wrap ftb-admin__wrap<?php echo $wide ? ' ftb-admin__wrap--wide' : ''; ?>">
			<?php require $partials . 'ftb-donation-form-admin-header.php'; ?>
			<hr class="wp-header-end">
			<?php $content(); ?>
			<?php require $partials . 'ftb-donation-form-admin-footer.php'; ?>
		</div>
		<?php
	}

	/**
	 * Render the settings page.
	 */
	public function display_plugin_setup_page() {
		if ( ! current_user_can( 'ftb_manage_settings' ) ) {
			wp_die( esc_html__( 'Je hebt onvoldoende rechten om deze pagina te bekijken.', 'ftb-donation-form' ) );
		}
		$this->render_admin_page(
			__( 'Donatieformulier instellingen', 'ftb-donation-form' ),
			function () {
				include_once plugin_dir_path( __FILE__ ) . 'partials/ftb-donation-form-admin-display.php';
			}
		);
	}

	/**
	 * Save editor access mode and designated managers (admin-only).
	 */
	public function handle_save_managers(): void {
		if ( empty( $_POST['ftb_save_managers'] ) ) {
			return;
		}
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'Je hebt onvoldoende rechten om deze actie uit te voeren.', 'ftb-donation-form' ) );
		}
		check_admin_referer( 'ftb_save_managers' );

		$raw_mode = isset( $_POST['ftb_editor_access_mode'] ) ? sanitize_key( wp_unslash( $_POST['ftb_editor_access_mode'] ) ) : '';
		$new_mode = in_array( $raw_mode, FTB_Access::MODES, true ) ? $raw_mode : FTB_Access::DEFAULT_MODE;

		$old_ids = array_map( 'absint', (array) get_option( 'ftb_designated_managers', array() ) );
		$new_ids = isset( $_POST['ftb_designated_managers'] )
			? array_map( 'absint', (array) $_POST['ftb_designated_managers'] ) // phpcs:ignore WordPress.Security.ValidatedSanitizedInput
			: array();

		// Same function as on activation, so the screen and the actual access always match.
		$granted = FTB_Access::apply( $new_mode, $new_ids, $old_ids );

		update_option( 'ftb_designated_managers', $granted );
		update_option( 'ftb_editor_access_mode', $new_mode );
		wp_safe_redirect( add_query_arg( 'managers_saved', '1', admin_url( 'admin.php?page=ftb-donation-form' ) ) );
		exit;
	}

	/**
	 * Handle bulk delete on admin_init — before any output is sent.
	 */
	public function handle_bulk_delete() {
        // phpcs:disable WordPress.Security.NonceVerification.Missing
		if ( empty( $_POST['page'] ) || 'ftb-submissions' !== $_POST['page'] ) {
			return;
		}
		$action  = isset( $_POST['action'] ) ? sanitize_text_field( wp_unslash( $_POST['action'] ) ) : '';
		$action2 = isset( $_POST['action2'] ) ? sanitize_text_field( wp_unslash( $_POST['action2'] ) ) : '';
        // phpcs:enable
		if ( 'delete' !== $action && 'delete' !== $action2 ) {
			return;
		}
		if ( ! current_user_can( 'ftb_manage_settings' ) ) {
			wp_die( esc_html__( 'Je hebt onvoldoende rechten om deze actie uit te voeren.', 'ftb-donation-form' ) );
		}

		check_admin_referer( 'bulk-donaties' );

		$ids     = array_map( 'absint', (array) ( $_POST['donation'] ?? array() ) ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
		$db      = new FTB_DB();
		$deleted = 0;
		foreach ( $ids as $id ) {
			if ( $id > 0 && $db->delete_donation( $id ) ) {
				++$deleted;
			}
		}

		wp_safe_redirect( add_query_arg( 'deleted', $deleted, admin_url( 'admin.php?page=ftb-submissions' ) ) );
		exit;
	}

	/**
	 * Handle individual row delete on admin_init — before any output is sent.
	 */
	/**
	 * Delete the Mollie API key via the "Verwijderen" link next to the masked key.
	 *
	 * Runs on admin_init. Same access as the settings page; protected with a nonce.
	 */
	public function handle_delete_api_key() {
        // phpcs:disable WordPress.Security.NonceVerification.Recommended
		if ( empty( $_GET['page'] ) || 'ftb-donation-form' !== $_GET['page'] ) {
			return;
		}
		if ( empty( $_GET['ftb_action'] ) || 'delete_api_key' !== $_GET['ftb_action'] ) {
			return;
		}
        // phpcs:enable

		if ( ! current_user_can( 'ftb_manage_settings' ) ) {
			wp_die( esc_html__( 'Je hebt onvoldoende rechten om deze actie uit te voeren.', 'ftb-donation-form' ) );
		}

		check_admin_referer( 'ftb_delete_api_key' );

		// delete_option, not update_option: the sanitize callback keeps the key on an empty value.
		delete_option( 'ftb_mollie_api_key' );

		// One-time notice per user (not a URL parameter, which would survive the next save).
		set_transient( 'ftb_mollie_key_deleted_' . get_current_user_id(), true, 60 );

		wp_safe_redirect( admin_url( 'admin.php?page=ftb-donation-form' ) );
		exit;
	}

	public function handle_row_delete() {
        // phpcs:disable WordPress.Security.NonceVerification.Recommended
		if ( empty( $_GET['page'] ) || 'ftb-submissions' !== $_GET['page'] ) {
			return;
		}
		if ( empty( $_GET['action'] ) || 'delete' !== $_GET['action'] ) {
			return;
		}
		$id = isset( $_GET['id'] ) ? absint( $_GET['id'] ) : 0;
        // phpcs:enable

		if ( ! $id ) {
			return;
		}

		if ( ! current_user_can( 'ftb_manage_settings' ) ) {
			wp_die( esc_html__( 'Je hebt onvoldoende rechten om deze actie uit te voeren.', 'ftb-donation-form' ) );
		}

		check_admin_referer( 'ftb_delete_donation_' . $id );

		$db      = new FTB_DB();
		$deleted = $db->delete_donation( $id ) ? 1 : 0;

		wp_safe_redirect( add_query_arg( 'deleted', $deleted, admin_url( 'admin.php?page=ftb-submissions' ) ) );
		exit;
	}

	/**
	 * Handle payment status update on admin_init — before any output is sent.
	 */
	public function handle_status_update() {
        // phpcs:disable WordPress.Security.NonceVerification.Missing
		if ( empty( $_POST['page'] ) || 'ftb-submissions' !== $_POST['page'] ) {
			return;
		}
		if ( empty( $_POST['action'] ) || 'update_status' !== $_POST['action'] ) {
			return;
		}
		$id = isset( $_POST['id'] ) ? absint( $_POST['id'] ) : 0;
        // phpcs:enable

		if ( ! $id ) {
			return;
		}

		if ( ! current_user_can( 'ftb_manage_settings' ) ) {
			wp_die( esc_html__( 'Je hebt onvoldoende rechten om deze actie uit te voeren.', 'ftb-donation-form' ) );
		}

		check_admin_referer( 'ftb_update_status_' . $id );

		$allowed = array( 'pending', 'paid', 'failed', 'cancelled', 'expired' );
		$status  = isset( $_POST['payment_status'] ) ? sanitize_text_field( wp_unslash( $_POST['payment_status'] ) ) : ''; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotValidated

		if ( ! in_array( $status, $allowed, true ) ) {
			wp_die( esc_html__( 'Ongeldige status.', 'ftb-donation-form' ) );
		}

		$db = new FTB_DB();
		$db->update_payment_status_by_id( $id, $status );

		wp_safe_redirect( add_query_arg( 'updated', '1', admin_url( 'admin.php?page=ftb-submissions' ) ) );
		exit;
	}

	/**
	 * Handle CSV export on admin_init — before any output is sent.
	 */
	public function handle_csv_export() {
        // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		if ( ! is_admin() || ! isset( $_GET['action'] ) || 'export_csv' !== $_GET['action'] ) {
			return;
		}
		if ( ! isset( $_GET['page'] ) || 'ftb-submissions' !== $_GET['page'] ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			return;
		}
		if ( ! current_user_can( 'ftb_manage_settings' ) ) {
			wp_die( esc_html__( 'Je hebt onvoldoende rechten om deze actie uit te voeren.', 'ftb-donation-form' ) );
		}

		check_admin_referer( 'ftb_export_csv' );
		$this->stream_csv_export();
		exit;
	}

	/**
	 * Render the donations submissions page (list or edit-status form).
	 */
	public function display_submissions_page() {
		if ( ! current_user_can( 'ftb_manage_settings' ) ) {
			wp_die( esc_html__( 'Je hebt onvoldoende rechten om deze pagina te bekijken.', 'ftb-donation-form' ) );
		}

		require_once plugin_dir_path( __FILE__ ) . 'class-ftb-donations-list-table.php';

        // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$action = isset( $_GET['action'] ) ? sanitize_text_field( wp_unslash( $_GET['action'] ) ) : '';

		if ( 'edit_status' === $action ) {
            // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			$id = isset( $_GET['id'] ) ? absint( $_GET['id'] ) : 0;
			if ( ! $id ) {
				wp_safe_redirect( admin_url( 'admin.php?page=ftb-submissions' ) );
				exit;
			}
			$this->render_admin_page(
				__( 'Status wijzigen', 'ftb-donation-form' ),
				function () {
					include_once plugin_dir_path( __FILE__ ) . 'partials/ftb-donation-form-edit-status-display.php';
				}
			);
		} else {
			$csv_action = '<a href="' . esc_url( wp_nonce_url( admin_url( 'admin.php?page=ftb-submissions&action=export_csv' ), 'ftb_export_csv' ) ) . '" class="page-title-action">' . esc_html__( 'Exporteer CSV', 'ftb-donation-form' ) . '</a>';
			$this->render_admin_page(
				__( 'Donaties', 'ftb-donation-form' ),
				function () {
					include_once plugin_dir_path( __FILE__ ) . 'partials/ftb-donation-form-submissions-display.php';
				},
				$csv_action,
				true
			);
		}
	}

	private function stream_csv_export() {
		$db        = new FTB_DB();
		$donations = $db->get_all_donations();

		header( 'Content-Type: text/csv; charset=utf-8' );
		header( 'Content-Disposition: attachment; filename="donaties-' . gmdate( 'Y-m-d' ) . '.csv"' );

		$output = fopen( 'php://output', 'w' ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fopen
		// BOM so Excel opens UTF-8 correctly.
		fwrite( $output, "\xEF\xBB\xBF" ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fwrite

		fputcsv( $output, array( 'Naam', 'E-mail', 'Telefoon', 'Straat', 'Huisnummer', 'Postcode', 'Plaats', 'Bedrag', 'Frequentie', 'Status', 'Datum' ), ';' );

		$frequency_labels = array(
			'one_time' => 'Eenmalig',
			'monthly'  => 'Maandelijks',
			'yearly'   => 'Jaarlijks',
		);

		foreach ( $donations as $donation ) {
			fputcsv(
				$output,
				array( // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fputcsv
				$donation->donor_name,
				$donation->donor_email,
				$donation->donor_phone,
				$donation->donor_street,
				$donation->donor_house_number,
				$donation->donor_postal_code,
				$donation->donor_city,
				number_format( $donation->amount / 100, 2, ',', '.' ),
				$frequency_labels[ $donation->frequency ] ?? $donation->frequency,
				$donation->payment_status,
				$donation->created_at,
				),
				';'
			);
		}

		fclose( $output ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fclose
	}

	/**
	 * Render the "delete data on uninstall" toggle with a warning shown while it is on.
	 */
	public function field_delete_data_on_uninstall() {
		$value = (string) get_option( 'ftb_delete_data_on_uninstall', '0' );
		?>
		<div class="ftb-toggle-group">
			<input type="hidden" name="ftb_delete_data_on_uninstall" value="0">
			<label class="ftb-toggle" for="ftb_delete_data_on_uninstall">
				<input class="ftb-toggle__input" type="checkbox" role="switch" id="ftb_delete_data_on_uninstall" name="ftb_delete_data_on_uninstall" value="1" <?php checked( '1', $value ); ?>>
				<span class="ftb-toggle__slider" aria-hidden="true"></span>
				<span><?php esc_html_e( 'Alle gegevens wissen bij verwijderen', 'ftb-donation-form' ); ?></span>
			</label>
			<div class="ftb-conditional<?php echo '1' === $value ? ' is-visible' : ''; ?>" data-show-when="ftb_delete_data_on_uninstall=1">
				<div class="ftb-notice ftb-notice--error" role="note">
					<span class="dashicons dashicons-warning" aria-hidden="true"></span>
					<?php esc_html_e( 'Let op: zodra de plugin wordt verwijderd, worden alle donaties en instellingen definitief gewist. Dit kan niet ongedaan worden gemaakt. Exporteer eerst je donaties als je ze wilt bewaren.', 'ftb-donation-form' ); ?>
				</div>
			</div>
		</div>
		<?php
	}
}
