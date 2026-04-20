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

        <div class="ftb-admin-form__columns">

            <div class="ftb-admin-form__main">

                <?php // ── Kop ──────────────────────────────────────────────── ?>
                <section class="ftb-admin-form__section">
                    <h2 class="ftb-admin-form__title">
                        <?php esc_html_e( 'Titel', 'ftb-donation-form' ); ?>
                    </h2>
                    <p class="ftb-admin-form__description">
                        <?php esc_html_e( 'Pas de titel van het formulier aan.', 'ftb-donation-form' ); ?>
                    </p>
                    <div class="ftb-admin-form__group">
                        <?php do_settings_fields( 'ftb_donation_form_settings', 'ftb_section_heading' ); ?>
                    </div>
                </section>

                <?php // ── Formuliervelden ──────────────────────────────────── ?>
                <section class="ftb-admin-form__section">
                    <h2 class="ftb-admin-form__title">
                        <?php esc_html_e( 'Formuliervelden', 'ftb-donation-form' ); ?>
                    </h2>
                    <p class="ftb-admin-form__description">
                        <?php esc_html_e( 'Naam en e-mailadres zijn altijd verplicht. Vink de overige velden aan die je wilt tonen.', 'ftb-donation-form' ); ?>
                    </p>
                    <div class="ftb-admin-form__group ftb-admin-form__group--checkboxes">
                        <?php do_settings_fields( 'ftb_donation_form_settings', 'ftb_section_fields' ); ?>
                    </div>
                </section>

                <?php // ── Bedragopties ──────────────────────────────────────── ?>
                <section class="ftb-admin-form__section">
                    <h2 class="ftb-admin-form__title">
                        <?php esc_html_e( 'Bedragopties', 'ftb-donation-form' ); ?>
                    </h2>
                    <p class="ftb-admin-form__description">
                        <?php esc_html_e( 'Kies maximaal drie vaste bedragen die de donateur kan selecteren. De donateur kan altijd ook een eigen bedrag invullen.', 'ftb-donation-form' ); ?>
                    </p>
                    <div class="ftb-admin-form__group">
                        <?php do_settings_fields( 'ftb_donation_form_settings', 'ftb_section_amounts' ); ?>
                    </div>
                </section>

                <?php // ── Na betaling ───────────────────────────────────────── ?>
                <section class="ftb-admin-form__section">
                    <h2 class="ftb-admin-form__title">
                        <?php esc_html_e( 'Na betaling', 'ftb-donation-form' ); ?>
                    </h2>
                    <p class="ftb-admin-form__description">
                        <?php esc_html_e( 'Kies wat er gebeurt nadat de donateur succesvol heeft betaald.', 'ftb-donation-form' ); ?>
                    </p>
                    <div class="ftb-admin-form__group">
                        <?php do_settings_fields( 'ftb_donation_form_settings', 'ftb_section_post_payment' ); ?>
                    </div>
                </section>

            </div>

            <div class="ftb-admin-form__sidebar">

                <?php // ── Mollie ────────────────────────────────────────────── ?>
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

                <?php // ── Privacyverklaring ─────────────────────────────────── ?>
                <section class="ftb-admin-form__section">
                    <h2 class="ftb-admin-form__title">
                        <?php esc_html_e( 'Privacyverklaring', 'ftb-donation-form' ); ?>
                    </h2>
                    <p class="ftb-admin-form__description">
                        <?php esc_html_e( 'Voeg de link in naar je privacyverklaring. Deze link wordt boven het toestemmingsveld in het formulier getoond.', 'ftb-donation-form' ); ?>
                    </p>
                    <div class="ftb-admin-form__group">
                        <?php do_settings_fields( 'ftb_donation_form_settings', 'ftb_section_privacy' ); ?>
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

        </div>

        <div class="ftb-admin-form__submit">
            <?php submit_button( __( 'Instellingen opslaan', 'ftb-donation-form' ), 'primary', 'submit', false ); ?>
        </div>

    </form>

</div>
