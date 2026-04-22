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
                </p>
                <div class="ftb-admin-form__group">
                    <?php do_settings_fields( 'ftb_donation_form_settings', 'ftb_section_mollie' ); ?>
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
                    </div>
                </section>

                <?php // ── Bedragopties ─────────────────────────────────────── ?>
                <section class="ftb-admin-form__section">
                    <h3 class="ftb-admin-form__title">
                        <?php esc_html_e( 'Bedragopties', 'ftb-donation-form' ); ?>
                    </h3>
                    <p class="ftb-admin-form__description">
                        <?php esc_html_e( 'Kies maximaal drie vaste bedragen die de donateur kan selecteren. De donateur kan altijd ook een eigen bedrag invullen.', 'ftb-donation-form' ); ?>
                    </p>
                    <div class="ftb-admin-form__group">
                        <?php do_settings_fields( 'ftb_donation_form_settings', 'ftb_section_amounts' ); ?>
                        <?php
                        $allow_custom = get_option( 'ftb_allow_custom_amount', '1' );
                        $min_value    = get_option( 'ftb_min_custom_amount', '1' );
                        ?>
                        <div class="ftb-conditional<?php echo $allow_custom === '1' ? ' is-visible' : ''; ?>" data-show-when="ftb_allow_custom_amount=1">
                            <table class="form-table"><tbody><tr>
                                <th scope="row"><label for="ftb_min_custom_amount"><?php esc_html_e( 'Minimumbedrag eigen bedrag (minimaal €1)', 'ftb-donation-form' ); ?></label></th>
                                <td>
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
                                </td>
                            </tr></tbody></table>
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
                        $message      = get_option( 'ftb_post_payment_message', __( 'Hartelijk dank voor je donatie!', 'ftb-donation-form' ) );
                        $redirect_url = get_option( 'ftb_post_payment_redirect_url', '' );
                        ?>
                        <div class="ftb-conditional<?php echo $behavior === 'message' ? ' is-visible' : ''; ?>" data-show-when="ftb_post_payment_behavior=message">
                            <table class="form-table"><tbody><tr>
                                <th scope="row"><label for="ftb_post_payment_message"><?php esc_html_e( 'Bedankbericht', 'ftb-donation-form' ); ?></label></th>
                                <td>
                                    <textarea
                                        id="ftb_post_payment_message"
                                        name="ftb_post_payment_message"
                                        rows="3"
                                        class="large-text"
                                    ><?php echo esc_textarea( $message ); ?></textarea>
                                </td>
                            </tr></tbody></table>
                        </div>
                        <div class="ftb-conditional<?php echo $behavior === 'redirect' ? ' is-visible' : ''; ?>" data-show-when="ftb_post_payment_behavior=redirect">
                            <table class="form-table"><tbody><tr>
                                <th scope="row"><label for="ftb_post_payment_redirect_url"><?php esc_html_e( 'Doorstuur-URL', 'ftb-donation-form' ); ?></label></th>
                                <td>
                                    <input
                                        type="url"
                                        id="ftb_post_payment_redirect_url"
                                        name="ftb_post_payment_redirect_url"
                                        value="<?php echo esc_attr( $redirect_url ); ?>"
                                        class="regular-text"
                                        placeholder="https://jouwwebsite.nl/bedankt"
                                    />
                                </td>
                            </tr></tbody></table>
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
