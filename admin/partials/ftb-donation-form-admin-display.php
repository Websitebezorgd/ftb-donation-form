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
<div class="wrap ftb-admin-wrap">

    <h1 class="ftb-admin-title">
        <span class="dashicons dashicons-heart"></span>
        <?php esc_html_e( 'Donatieformulier instellingen', 'ftb-donation-form' ); ?>
    </h1>

    <?php settings_errors(); ?>

    <form method="post" action="options.php" class="ftb-admin-form">
        <?php settings_fields( 'ftb_donation_form_settings' ); ?>

        <?php // ── Mollie ──────────────────────────────────────────────────── ?>
        <div class="ftb-admin-section">
            <h2 class="ftb-admin-section__title">
                <?php esc_html_e( 'Mollie instellingen', 'ftb-donation-form' ); ?>
            </h2>
            <div class="ftb-admin-section__body">
                <?php do_settings_fields( 'ftb_donation_form_settings', 'ftb_section_mollie' ); ?>
            </div>
        </div>

        <?php // ── Formuliervelden ─────────────────────────────────────────── ?>
        <div class="ftb-admin-section">
            <h2 class="ftb-admin-section__title">
                <?php esc_html_e( 'Formuliervelden', 'ftb-donation-form' ); ?>
            </h2>
            <p class="ftb-admin-section__description">
                <?php esc_html_e( 'Naam en e-mailadres zijn altijd verplicht. Vink de overige velden aan die je wilt tonen.', 'ftb-donation-form' ); ?>
            </p>
            <div class="ftb-admin-section__body ftb-admin-section__body--checkboxes">
                <?php do_settings_fields( 'ftb_donation_form_settings', 'ftb_section_fields' ); ?>
            </div>
        </div>

        <?php // ── Bedragopties ─────────────────────────────────────────────── ?>
        <div class="ftb-admin-section">
            <h2 class="ftb-admin-section__title">
                <?php esc_html_e( 'Bedragopties', 'ftb-donation-form' ); ?>
            </h2>
            <p class="ftb-admin-section__description">
                <?php esc_html_e( 'Kies maximaal drie vaste bedragen die de donateur kan selecteren. De donateur kan altijd ook een eigen bedrag invullen.', 'ftb-donation-form' ); ?>
            </p>
            <div class="ftb-admin-section__body">
                <?php do_settings_fields( 'ftb_donation_form_settings', 'ftb_section_amounts' ); ?>
            </div>
        </div>

        <?php // ── Na betaling ──────────────────────────────────────────────── ?>
        <div class="ftb-admin-section">
            <h2 class="ftb-admin-section__title">
                <?php esc_html_e( 'Na betaling', 'ftb-donation-form' ); ?>
            </h2>
            <div class="ftb-admin-section__body">
                <?php do_settings_fields( 'ftb_donation_form_settings', 'ftb_section_post_payment' ); ?>
            </div>
        </div>

        <div class="ftb-admin-submit">
            <?php submit_button( __( 'Instellingen opslaan', 'ftb-donation-form' ), 'primary', 'submit', false ); ?>
        </div>

    </form>

    <div class="ftb-admin-shortcode-info">
        <h2><?php esc_html_e( 'Shortcode', 'ftb-donation-form' ); ?></h2>
        <p>
            <?php esc_html_e( 'Plaats het donatieformulier op een pagina met deze shortcode:', 'ftb-donation-form' ); ?>
        </p>
        <code>[ftb_donation_form]</code>
    </div>

</div>
