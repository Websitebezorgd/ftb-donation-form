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
		wp_enqueue_style(
			$this->plugin_name,
			plugin_dir_url( __FILE__ ) . 'css/ftb-donation-form-admin.css',
			array(),
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
			array( 'jquery' ),
			filemtime( plugin_dir_path( __FILE__ ) . 'js/ftb-donation-form-admin.js' ),
			true
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

	}

	// ── Section descriptions ───────────────────────────────────────────────────

	public function section_fields_description() {
		echo '<p>' . esc_html__( 'Naam en e-mailadres zijn altijd verplicht. Vink de overige velden aan die je wilt tonen.', 'ftb-donation-form' ) . '</p>';
	}

	public function section_amounts_description() {
		echo '<p>' . esc_html__( 'Voer drie vaste bedragen in die de donateur kan kiezen. De donateur kan ook een eigen bedrag invullen als deze optie is ingeschakeld.', 'ftb-donation-form' ) . '</p>';
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
				autocomplete="new-password" />
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

	public function field_amount_options() {
		$amounts  = array_values( (array) get_option( 'ftb_amount_options', array( '5', '10', '25' ) ) );
		$defaults = array( '5', '10', '25' );

		echo '<div class="ftb-admin-form__field"><div class="ftb-amount-inputs">';
		for ( $i = 0; $i < 3; $i++ ) {
			$value = isset( $amounts[ $i ] ) ? $amounts[ $i ] : $defaults[ $i ];
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
					<?php checked( '1', $value ); ?> />
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

	public function sanitize_mollie_api_key( string $value ): string {
		$value = sanitize_text_field( $value );

		if ( empty( $value ) ) {
			return $value;
		}

		try {
			$mollie = new \Mollie\Api\MollieApiClient();
			$mollie->setApiKey( $value );
			$mollie->methods->allActive();
		} catch ( \Throwable $e ) {
			// Store per-user so the notice renders with ftb-notice styling in the template.
			set_transient( 'ftb_mollie_key_error_' . get_current_user_id(), true, 60 );
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

		$old_mode = get_option( 'ftb_editor_access_mode', 'all' );
		$allowed  = array( 'all', 'specific', 'admin_only' );
		$raw_mode = isset( $_POST['ftb_editor_access_mode'] ) ? sanitize_key( wp_unslash( $_POST['ftb_editor_access_mode'] ) ) : '';
		$new_mode = in_array( $raw_mode, $allowed, true ) ? $raw_mode : 'all';

		$editor = get_role( 'editor' );

		if ( 'all' === $new_mode ) {
			if ( $editor ) {
				$editor->add_cap( 'ftb_manage_settings' );
			}
			// Remove user-level caps from previously designated managers.
			foreach ( array_map( 'absint', (array) get_option( 'ftb_designated_managers', array() ) ) as $id ) {
				$user = get_user_by( 'id', $id );
				if ( $user && ! $user->has_cap( 'manage_options' ) ) {
					$user->remove_cap( 'ftb_manage_settings' );
				}
			}
			update_option( 'ftb_designated_managers', array() );
		} elseif ( 'admin_only' === $new_mode ) {
			if ( $editor ) {
				$editor->remove_cap( 'ftb_manage_settings' );
			}
			foreach ( array_map( 'absint', (array) get_option( 'ftb_designated_managers', array() ) ) as $id ) {
				$user = get_user_by( 'id', $id );
				if ( $user && ! $user->has_cap( 'manage_options' ) ) {
					$user->remove_cap( 'ftb_manage_settings' );
				}
			}
			update_option( 'ftb_designated_managers', array() );
		} else {
			if ( 'all' === $old_mode && $editor ) {
				$editor->remove_cap( 'ftb_manage_settings' );
			}

			$old_ids = array_map( 'absint', (array) get_option( 'ftb_designated_managers', array() ) );
			$new_ids = isset( $_POST['ftb_designated_managers'] )
				? array_map( 'absint', (array) $_POST['ftb_designated_managers'] ) // phpcs:ignore WordPress.Security.ValidatedSanitizedInput
				: array();

			foreach ( array_diff( $new_ids, $old_ids ) as $id ) {
				$user = get_user_by( 'id', $id );
				if ( $user && ! $user->has_cap( 'manage_options' ) && in_array( 'editor', (array) $user->roles, true ) ) {
					$user->add_cap( 'ftb_manage_settings' );
				}
			}
			foreach ( array_diff( $old_ids, $new_ids ) as $id ) {
				$user = get_user_by( 'id', $id );
				if ( $user && ! $user->has_cap( 'manage_options' ) ) {
					$user->remove_cap( 'ftb_manage_settings' );
				}
			}

			update_option( 'ftb_designated_managers', $new_ids );
		}

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

		$allowed = array( 'pending', 'paid', 'failed', 'cancelled' );
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
}
