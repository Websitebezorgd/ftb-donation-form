<?php
/**
 * Admin settings page template.
 *
 * @since      1.0.0
 * @package    FTB_Donation_Form
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
?>
<div class="wrap ftb-admin__wrap">

    <h1 class="ftb-admin__title">
        <span class="dashicons dashicons-heart"></span>
        <?php esc_html_e( 'Donatieformulier instellingen', 'ftb-donation-form' ); ?>
    </h1>

    <?php settings_errors(); ?>

    <form method="post" action="options.php" class="ftb-admin-form">
        <?php settings_fields( 'ftb_donation_form_settings' ); ?>

        <?php // ── Top grid: API key + Shortcode ─────────────────────────── ?>
        <div class="ftb-admin-form__grid">

            <section class="ftb-admin-form__section">
                <h2 class="ftb-admin-form__title">
                    <?php esc_html_e( 'Mollie instellingen', 'ftb-donation-form' ); ?>
                </h2>
                <p class="ftb-admin-form__description">
                    <?php esc_html_e( 'Vind je API sleutel in je Mollie dashboard onder Ontwikkelaars → API-sleutels. Gebruik de live sleutel voor echte betalingen.', 'ftb-donation-form' ); ?>
                    <a href="<?php echo esc_url( 'https://my.mollie.com/dashboard/login' ); ?>" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'Inloggen bij Mollie', 'ftb-donation-form' ); ?><span class="dashicons dashicons-external" aria-hidden="true" style="font-size: 1em; width: 1em; height: 1em; vertical-align: text-bottom; text-decoration: none; display: inline-block;"></span></a>
                </p>
                <div class="ftb-admin-form__group">
                    <?php do_settings_fields( 'ftb_donation_form_settings', 'ftb_section_mollie' ); ?>
                    <?php
                    $transient_key = 'ftb_mollie_key_error_' . get_current_user_id();
                    if ( get_transient( $transient_key ) ) :
                        delete_transient( $transient_key );
                    ?>
                    <div class="ftb-notice ftb-notice--error" role="alert">
                        <span class="dashicons dashicons-warning" aria-hidden="true"></span>
                        <?php esc_html_e( 'Mollie API sleutel is ongeldig. Controleer de sleutel en probeer het opnieuw.', 'ftb-donation-form' ); ?>
                    </div>
                    <?php endif; ?>
                </div>
            </section>

            <div class="ftb-admin__shortcode">
                <h2><?php esc_html_e( 'Shortcode', 'ftb-donation-form' ); ?></h2>
                <p>
                    <?php esc_html_e( 'Plaats het donatieformulier op een pagina met deze shortcode:', 'ftb-donation-form' ); ?>
                </p>
                <code>[ftb_donation_form]</code>
            </div>

        </div>

        <?php // ── Formulier instellingen ────────────────────────────────── ?>
        <h2 class="ftb-admin-form__section-heading"><?php esc_html_e( 'Formulier instellingen', 'ftb-donation-form' ); ?></h2>

        <div class="ftb-admin-form__grid">

            <div class="ftb-admin-form__col">

                <?php // ── Titel ────────────────────────────────────────────── ?>
                <section class="ftb-admin-form__section">
                    <h3 class="ftb-admin-form__title">
                        <?php esc_html_e( 'Titel', 'ftb-donation-form' ); ?>
                    </h3>
                    <p class="ftb-admin-form__description">
                        <?php esc_html_e( 'Pas de titel van het formulier aan.', 'ftb-donation-form' ); ?>
                    </p>
                    <div class="ftb-admin-form__group">
                        <?php do_settings_fields( 'ftb_donation_form_settings', 'ftb_section_heading' ); ?>
                    </div>
                </section>

                <?php // ── Frequentie ───────────────────────────────────────── ?>
                <section class="ftb-admin-form__section">
                    <h3 class="ftb-admin-form__title">
                        <?php esc_html_e( 'Frequentie', 'ftb-donation-form' ); ?>
                    </h3>
                    <p class="ftb-admin-form__description">
                        <?php esc_html_e( 'Kies of donateurs alleen eenmalig kunnen doneren, of ook maandelijks en jaarlijks.', 'ftb-donation-form' ); ?>
                    </p>
                    <div class="ftb-admin-form__group">
                        <?php $recurring = get_option( 'ftb_enable_recurring', '1' ); ?>
                        <input type="hidden" name="ftb_enable_recurring" value="0">
                        <label class="ftb-toggle" for="ftb_enable_recurring">
                            <input class="ftb-toggle__input" type="checkbox" id="ftb_enable_recurring" name="ftb_enable_recurring" value="1" <?php checked( '1', $recurring ); ?>>
                            <span class="ftb-toggle__slider" aria-hidden="true"></span>
                            <span><?php esc_html_e( 'Terugkerende betalingen inschakelen', 'ftb-donation-form' ); ?></span>
                        </label>
                        <div class="ftb-conditional<?php echo '1' === $recurring ? ' is-visible' : ''; ?>" data-show-when="ftb_enable_recurring=1">
                            <div class="ftb-notice ftb-notice--info" role="note">
                                <span class="dashicons dashicons-info" aria-hidden="true"></span>
                                <?php esc_html_e( 'Zorg dat SEPA-incasso is ingeschakeld in je Mollie-dashboard. Activeer via je organisatienaam linksboven in het dashboard → Instellingen → Online betalingen → SEPA-incasso.', 'ftb-donation-form' ); ?>
                            </div>
                        </div>
                    </div>
                </section>

                <?php // ── Bedragopties ─────────────────────────────────────── ?>
                <section class="ftb-admin-form__section">
                    <h3 class="ftb-admin-form__title">
                        <?php esc_html_e( 'Bedragopties', 'ftb-donation-form' ); ?>
                    </h3>
                    <p class="ftb-admin-form__description">
                        <?php esc_html_e( 'Kies welke bedragopties beschikbaar zijn. Je moet minimaal één optie inschakelen.', 'ftb-donation-form' ); ?>
                    </p>
                    <div class="ftb-admin-form__group">
                        <?php
                        $show_presets = get_option( 'ftb_show_preset_amounts', '1' );
                        $allow_custom = get_option( 'ftb_allow_custom_amount', '1' );
                        $min_value    = get_option( 'ftb_min_custom_amount', '1' );
                        $amounts      = array_values( (array) get_option( 'ftb_amount_options', [ '5', '10', '25' ] ) );
                        $defaults     = [ '5', '10', '25' ];
                        ?>

                        <div class="ftb-toggle-group">
                            <input type="hidden" name="ftb_show_preset_amounts" value="0">
                            <label class="ftb-toggle" for="ftb_show_preset_amounts">
                                <input class="ftb-toggle__input" type="checkbox" id="ftb_show_preset_amounts" name="ftb_show_preset_amounts" value="1" <?php checked( '1', $show_presets ); ?>>
                                <span class="ftb-toggle__slider" aria-hidden="true"></span>
                                <span><?php esc_html_e( 'Vaste bedragen tonen', 'ftb-donation-form' ); ?></span>
                            </label>
                            <div class="ftb-conditional<?php echo $show_presets === '1' ? ' is-visible' : ''; ?>" data-show-when="ftb_show_preset_amounts=1">
                                <div class="ftb-admin-form__stacked-field">
                                    <label class="ftb-admin-form__label"><?php esc_html_e( 'Vaste bedragen (minimaal €1)', 'ftb-donation-form' ); ?></label>
                                    <div class="ftb-amount-inputs">
                                        <?php for ( $i = 0; $i < 3; $i++ ) :
                                            $val = isset( $amounts[ $i ] ) ? $amounts[ $i ] : ( $defaults[ $i ] ?? '' );
                                        ?>
                                        <div class="ftb-amount-input">
                                            <span class="ftb-amount-input__prefix" aria-hidden="true">€</span>
                                            <input
                                                type="number"
                                                name="ftb_amount_options[<?php echo absint( $i ); ?>]"
                                                id="ftb_amount_options_<?php echo absint( $i ); ?>"
                                                value="<?php echo esc_attr( $val ); ?>"
                                                min="1"
                                                step="1"
                                                class="small-text"
                                                aria-label="<?php /* translators: %d: amount option number */ echo esc_attr( sprintf( __( 'Bedragoptie %d', 'ftb-donation-form' ), $i + 1 ) ); ?>"
                                            />
                                        </div>
                                        <?php endfor; ?>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="ftb-toggle-group">
                            <input type="hidden" name="ftb_allow_custom_amount" value="0">
                            <label class="ftb-toggle" for="ftb_allow_custom_amount">
                                <input class="ftb-toggle__input" type="checkbox" id="ftb_allow_custom_amount" name="ftb_allow_custom_amount" value="1" <?php checked( '1', $allow_custom ); ?>>
                                <span class="ftb-toggle__slider" aria-hidden="true"></span>
                                <span><?php esc_html_e( 'Eigen bedrag toestaan', 'ftb-donation-form' ); ?></span>
                            </label>
                            <div class="ftb-conditional<?php echo $allow_custom === '1' ? ' is-visible' : ''; ?>" data-show-when="ftb_allow_custom_amount=1">
                                <div class="ftb-admin-form__stacked-field">
                                    <label class="ftb-admin-form__label" for="ftb_min_custom_amount"><?php esc_html_e( 'Minimumbedrag eigen bedrag (minimaal €1)', 'ftb-donation-form' ); ?></label>
                                    <div class="ftb-amount-inputs">
                                        <div class="ftb-amount-input">
                                            <span class="ftb-amount-input__prefix" aria-hidden="true">€</span>
                                            <input
                                                type="number"
                                                id="ftb_min_custom_amount"
                                                name="ftb_min_custom_amount"
                                                value="<?php echo esc_attr( $min_value ); ?>"
                                                min="1"
                                                step="1"
                                                class="small-text"
                                            />
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>
                </section>

                <?php // ── Formuliervelden ──────────────────────────────────── ?>
                <section class="ftb-admin-form__section">
                    <h3 class="ftb-admin-form__title">
                        <?php esc_html_e( 'Formuliervelden', 'ftb-donation-form' ); ?>
                    </h3>
                    <p class="ftb-admin-form__description">
                        <?php esc_html_e( 'Naam en e-mailadres zijn altijd verplicht. Vink de overige velden aan die je wilt tonen.', 'ftb-donation-form' ); ?>
                    </p>
                    <div class="ftb-admin-form__group ftb-admin-form__group--checkboxes">
                        <?php do_settings_fields( 'ftb_donation_form_settings', 'ftb_section_fields' ); ?>
                    </div>
                </section>

            </div>

            <div class="ftb-admin-form__col">

                <?php // ── Privacyverklaring ────────────────────────────────── ?>
                <section class="ftb-admin-form__section">
                    <h3 class="ftb-admin-form__title">
                        <?php esc_html_e( 'Privacyverklaring', 'ftb-donation-form' ); ?>
                    </h3>
                    <p class="ftb-admin-form__description">
                        <?php esc_html_e( 'Voeg de link in naar je privacyverklaring. Deze link wordt boven het toestemmingsveld in het formulier getoond.', 'ftb-donation-form' ); ?>
                    </p>
                    <div class="ftb-admin-form__group">
                        <?php do_settings_fields( 'ftb_donation_form_settings', 'ftb_section_privacy' ); ?>
                        <div class="ftb-conditional<?php echo ! get_option( 'ftb_privacy_url', '' ) ? ' is-visible' : ''; ?>" data-show-when="ftb_privacy_url=">
                            <div class="ftb-notice ftb-notice--error" role="note">
                                <span class="dashicons dashicons-warning" aria-hidden="true"></span>
                                <?php esc_html_e( 'Je verzamelt persoonsgegevens. Voeg een link naar je privacyverklaring toe om aan de AVG te voldoen.', 'ftb-donation-form' ); ?>
                            </div>
                        </div>
                    </div>
                </section>

                <?php // ── Na betaling ──────────────────────────────────────── ?>
                <section class="ftb-admin-form__section">
                    <h3 class="ftb-admin-form__title">
                        <?php esc_html_e( 'Na betaling', 'ftb-donation-form' ); ?>
                    </h3>
                    <p class="ftb-admin-form__description">
                        <?php esc_html_e( 'Kies wat er gebeurt nadat de donateur succesvol heeft betaald.', 'ftb-donation-form' ); ?>
                    </p>
                    <div class="ftb-admin-form__group">
                        <?php do_settings_fields( 'ftb_donation_form_settings', 'ftb_section_post_payment' ); ?>
                        <?php
                        $behavior     = get_option( 'ftb_post_payment_behavior', 'message' );
                        $message      = get_option( 'ftb_post_payment_message', 'Hartelijk dank voor je donatie!' );
                        $redirect_url = get_option( 'ftb_post_payment_redirect_url', '' );
                        ?>
                        <div class="ftb-conditional<?php echo $behavior === 'message' ? ' is-visible' : ''; ?>" data-show-when="ftb_post_payment_behavior=message">
                            <div class="ftb-admin-form__stacked-field">
                                <label class="ftb-admin-form__label" for="ftb_post_payment_message"><?php esc_html_e( 'Bedankbericht', 'ftb-donation-form' ); ?></label>
                                <textarea
                                    id="ftb_post_payment_message"
                                    name="ftb_post_payment_message"
                                    rows="3"
                                    class="large-text"
                                    placeholder="<?php esc_attr_e( 'Hartelijk dank voor je donatie!', 'ftb-donation-form' ); ?>"
                                ><?php echo esc_textarea( $message ); ?></textarea>
                            </div>
                        </div>
                        <div class="ftb-conditional<?php echo $behavior === 'redirect' ? ' is-visible' : ''; ?>" data-show-when="ftb_post_payment_behavior=redirect">
                            <div class="ftb-admin-form__stacked-field">
                                <label class="ftb-admin-form__label" for="ftb_post_payment_redirect_url"><?php esc_html_e( 'Doorstuur-URL', 'ftb-donation-form' ); ?></label>
                                <input
                                    type="url"
                                    id="ftb_post_payment_redirect_url"
                                    name="ftb_post_payment_redirect_url"
                                    value="<?php echo esc_attr( $redirect_url ); ?>"
                                    class="regular-text"
                                    placeholder="https://jouwwebsite.nl/bedankt"
                                />
                            </div>
                        </div>
                    </div>
                </section>

            </div>

        </div>

        <div class="ftb-admin-form__submit">
            <?php submit_button( __( 'Instellingen opslaan', 'ftb-donation-form' ), 'primary', 'submit', false ); ?>
        </div>

    </form>

</div>
