<?php

/**
 * Admin settings page template.
 *
 * @since      1.0.0
 * @package    FTB_Donation_Form
 */

if (! defined('ABSPATH')) {
    exit;
}
?>
<?php settings_errors(); ?>

<form method="post" action="options.php" class="ftb-admin-form">
    <?php settings_fields('ftb_donation_form_settings'); ?>

    <?php // ── Top grid: API key + Shortcode ─────────────────────────── 
    ?>
    <div class="ftb-admin-form__grid">

        <section class="ftb-admin-form__section">
            <h2 class="ftb-admin-form__title">
                <?php esc_html_e('Mollie instellingen', 'ftb-donation-form'); ?>
            </h2>
            <p class="ftb-admin-form__description">
                <?php esc_html_e('Vind je API sleutel in je Mollie dashboard onder Ontwikkelaars → API-sleutels. Gebruik de live sleutel voor echte betalingen.', 'ftb-donation-form'); ?>
                <a href="<?php echo esc_url('https://my.mollie.com/dashboard/login'); ?>" target="_blank" rel="noopener noreferrer"><?php esc_html_e('Inloggen bij Mollie', 'ftb-donation-form'); ?><span class="dashicons dashicons-external" aria-hidden="true" style="font-size: 1em; width: 1em; height: 1em; vertical-align: text-bottom; text-decoration: none; display: inline-block;"></span></a>
            </p>
            <div class="ftb-admin-form__group">
                <?php do_settings_fields('ftb_donation_form_settings', 'ftb_section_mollie'); ?>
                <?php
                $transient_key = 'ftb_mollie_key_error_' . get_current_user_id();
                if (get_transient($transient_key)) :
                    delete_transient($transient_key);
                ?>
                    <div class="ftb-notice ftb-notice--error" role="alert">
                        <span class="dashicons dashicons-warning" aria-hidden="true"></span>
                        <?php esc_html_e('Mollie API sleutel is ongeldig. Controleer de sleutel en probeer het opnieuw.', 'ftb-donation-form'); ?>
                    </div>
                <?php endif; ?>
            </div>
        </section>

        <div class="ftb-admin__shortcode">
            <h2><?php esc_html_e('Shortcode', 'ftb-donation-form'); ?></h2>
            <p>
                <?php esc_html_e('Plaats het donatieformulier op een pagina met deze shortcode:', 'ftb-donation-form'); ?>
            </p>
            <code>[ftb_donation_form]</code>
        </div>

    </div>

    <?php // ── Formulier instellingen ────────────────────────────────── 
    ?>
    <h2 class="ftb-admin-form__section-heading"><?php esc_html_e('Formulier instellingen', 'ftb-donation-form'); ?></h2>

    <div class="ftb-admin-form__grid">

        <div class="ftb-admin-form__col">

            <?php // ── Titel ────────────────────────────────────────────── 
            ?>
            <section class="ftb-admin-form__section">
                <h3 class="ftb-admin-form__title">
                    <?php esc_html_e('Titel', 'ftb-donation-form'); ?>
                </h3>
                <p class="ftb-admin-form__description">
                    <?php esc_html_e('Pas de titel van het formulier aan.', 'ftb-donation-form'); ?>
                </p>
                <div class="ftb-admin-form__group">
                    <?php do_settings_fields('ftb_donation_form_settings', 'ftb_section_heading'); ?>
                </div>
            </section>

            <?php // ── Frequentie ───────────────────────────────────────── 
            ?>
            <section class="ftb-admin-form__section">
                <h3 class="ftb-admin-form__title">
                    <?php esc_html_e('Frequentie', 'ftb-donation-form'); ?>
                </h3>
                <p class="ftb-admin-form__description">
                    <?php esc_html_e('Kies of donateurs alleen eenmalig kunnen doneren, of ook maandelijks en jaarlijks.', 'ftb-donation-form'); ?>
                </p>
                <div class="ftb-admin-form__group">
                    <?php $recurring = get_option('ftb_enable_recurring', '1'); ?>
                    <input type="hidden" name="ftb_enable_recurring" value="0">
                    <label class="ftb-toggle" for="ftb_enable_recurring">
                        <input class="ftb-toggle__input" type="checkbox" id="ftb_enable_recurring" name="ftb_enable_recurring" value="1" <?php checked('1', $recurring); ?>>
                        <span class="ftb-toggle__slider" aria-hidden="true"></span>
                        <span><?php esc_html_e('Terugkerende betalingen inschakelen', 'ftb-donation-form'); ?></span>
                    </label>
                    <div class="ftb-conditional<?php echo '1' === $recurring ? ' is-visible' : ''; ?>" data-show-when="ftb_enable_recurring=1">
                        <div class="ftb-notice ftb-notice--info" role="note">
                            <span class="dashicons dashicons-info" aria-hidden="true"></span>
                            <?php esc_html_e('Zorg dat SEPA-incasso is ingeschakeld in je Mollie-dashboard. Activeer via je organisatienaam linksboven in het dashboard → Instellingen → Online betalingen → SEPA-incasso.', 'ftb-donation-form'); ?>
                        </div>
                    </div>
                </div>
            </section>

            <?php // ── Bedragopties ─────────────────────────────────────── 
            ?>
            <section class="ftb-admin-form__section">
                <h3 class="ftb-admin-form__title">
                    <?php esc_html_e('Bedragopties', 'ftb-donation-form'); ?>
                </h3>
                <p class="ftb-admin-form__description">
                    <?php esc_html_e('Kies welke bedragopties beschikbaar zijn. Je moet minimaal één optie inschakelen.', 'ftb-donation-form'); ?>
                </p>
                <div class="ftb-admin-form__group">
                    <?php
                    $show_presets = get_option('ftb_show_preset_amounts', '1');
                    $allow_custom = get_option('ftb_allow_custom_amount', '1');
                    $min_value    = get_option('ftb_min_custom_amount', '1');
                    $amounts      = array_values((array) get_option('ftb_amount_options', ['5', '10', '25']));
                    $defaults     = ['5', '10', '25'];
                    ?>

                    <div class="ftb-toggle-group">
                        <input type="hidden" name="ftb_show_preset_amounts" value="0">
                        <label class="ftb-toggle" for="ftb_show_preset_amounts">
                            <input class="ftb-toggle__input" type="checkbox" id="ftb_show_preset_amounts" name="ftb_show_preset_amounts" value="1" <?php checked('1', $show_presets); ?>>
                            <span class="ftb-toggle__slider" aria-hidden="true"></span>
                            <span><?php esc_html_e('Vaste bedragen tonen', 'ftb-donation-form'); ?></span>
                        </label>
                        <div class="ftb-conditional<?php echo $show_presets === '1' ? ' is-visible' : ''; ?>" data-show-when="ftb_show_preset_amounts=1">
                            <div class="ftb-admin-form__stacked-field">
                                <label class="ftb-admin-form__label"><?php esc_html_e('Vaste bedragen (minimaal €1)', 'ftb-donation-form'); ?></label>
                                <div class="ftb-amount-inputs">
                                    <?php for ($i = 0; $i < 3; $i++) :
                                        $val = isset($amounts[$i]) ? $amounts[$i] : ($defaults[$i] ?? '');
                                    ?>
                                        <div class="ftb-amount-input">
                                            <span class="ftb-amount-input__prefix" aria-hidden="true">€</span>
                                            <input
                                                type="number"
                                                name="ftb_amount_options[<?php echo absint($i); ?>]"
                                                id="ftb_amount_options_<?php echo absint($i); ?>"
                                                value="<?php echo esc_attr($val); ?>"
                                                min="1"
                                                step="1"
                                                class="small-text"
                                                aria-label="<?php /* translators: %d: amount option number */ echo esc_attr(sprintf(__('Bedragoptie %d', 'ftb-donation-form'), $i + 1)); ?>" />
                                        </div>
                                    <?php endfor; ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="ftb-toggle-group">
                        <input type="hidden" name="ftb_allow_custom_amount" value="0">
                        <label class="ftb-toggle" for="ftb_allow_custom_amount">
                            <input class="ftb-toggle__input" type="checkbox" id="ftb_allow_custom_amount" name="ftb_allow_custom_amount" value="1" <?php checked('1', $allow_custom); ?>>
                            <span class="ftb-toggle__slider" aria-hidden="true"></span>
                            <span><?php esc_html_e('Eigen bedrag toestaan', 'ftb-donation-form'); ?></span>
                        </label>
                        <div class="ftb-conditional<?php echo $allow_custom === '1' ? ' is-visible' : ''; ?>" data-show-when="ftb_allow_custom_amount=1">
                            <div class="ftb-admin-form__stacked-field">
                                <label class="ftb-admin-form__label" for="ftb_min_custom_amount"><?php esc_html_e('Minimumbedrag eigen bedrag (minimaal €1)', 'ftb-donation-form'); ?></label>
                                <div class="ftb-amount-inputs">
                                    <div class="ftb-amount-input">
                                        <span class="ftb-amount-input__prefix" aria-hidden="true">€</span>
                                        <input
                                            type="number"
                                            id="ftb_min_custom_amount"
                                            name="ftb_min_custom_amount"
                                            value="<?php echo esc_attr($min_value); ?>"
                                            min="1"
                                            step="1"
                                            class="small-text" />
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </section>

            <?php // ── Formuliervelden ──────────────────────────────────── 
            ?>
            <section class="ftb-admin-form__section">
                <h3 class="ftb-admin-form__title">
                    <?php esc_html_e('Formuliervelden', 'ftb-donation-form'); ?>
                </h3>
                <p class="ftb-admin-form__description">
                    <?php esc_html_e('Naam en e-mailadres zijn altijd verplicht. Vink de overige velden aan die je wilt tonen.', 'ftb-donation-form'); ?>
                </p>
                <div class="ftb-admin-form__group ftb-admin-form__group--checkboxes">
                    <?php do_settings_fields('ftb_donation_form_settings', 'ftb_section_fields'); ?>
                </div>
            </section>

            <?php // ── Privacyverklaring ────────────────────────────────── 
            ?>
            <section class="ftb-admin-form__section">
                <h3 class="ftb-admin-form__title">
                    <?php esc_html_e('Privacyverklaring', 'ftb-donation-form'); ?>
                </h3>
                <p class="ftb-admin-form__description">
                    <?php esc_html_e('Voeg de link in naar je privacyverklaring. Deze link wordt boven het toestemmingsveld in het formulier getoond.', 'ftb-donation-form'); ?>
                </p>
                <div class="ftb-admin-form__group">
                    <?php do_settings_fields('ftb_donation_form_settings', 'ftb_section_privacy'); ?>
                    <div class="ftb-conditional<?php echo ! get_option('ftb_privacy_url', '') ? ' is-visible' : ''; ?>" data-show-when="ftb_privacy_url=">
                        <div class="ftb-notice ftb-notice--error" role="note">
                            <span class="dashicons dashicons-warning" aria-hidden="true"></span>
                            <?php esc_html_e('Je verzamelt persoonsgegevens. Voeg een link naar je privacyverklaring toe om aan de AVG te voldoen.', 'ftb-donation-form'); ?>
                        </div>
                    </div>
                    <?php
                    // Build suggested privacy text based on enabled fields.
                    $prv_fields  = get_option('ftb_form_fields', []);
                    $prv_items = [__('naam', 'ftb-donation-form'), __('e-mailadres', 'ftb-donation-form')];
                    if (! empty($prv_fields['phone'])) {
                        $prv_items[] = __('telefoonnummer', 'ftb-donation-form');
                    }
                    foreach (['street', 'house_number', 'postal_code', 'city'] as $f) {
                        if (! empty($prv_fields[$f])) {
                            $prv_items[] = __('adresgegevens', 'ftb-donation-form');
                            break;
                        }
                    }
                    $prv_items[] = __('donatiebedrag', 'ftb-donation-form');
                    $prv_items[] = __('frequentie (eenmalig, maandelijks of jaarlijks)', 'ftb-donation-form');
                    $prv_items[] = __('datum van de donatie', 'ftb-donation-form');
                    $prv_items[] = __('betalingsstatus', 'ftb-donation-form');
                    $prv_sender    = get_option('ftb_email_sender_address', '');
                    $prv_heading   = __('Donatieformulier', 'ftb-donation-form');
                    $prv_intro     = __('Via het donatieformulier op onze website verwerken wij persoonsgegevens om donaties te kunnen ontvangen en administreren. De volgende gegevens worden verzameld:', 'ftb-donation-form');
                    $prv_mollie    = __('Betalingen worden verwerkt via Mollie Payments B.V. Wij delen jouw gegevens uitsluitend met Mollie voor zover dat noodzakelijk is voor de uitvoering van de betaling. Wij verkopen jouw gegevens niet aan derden en delen deze niet voor andere doeleinden, tenzij wij daartoe wettelijk verplicht zijn.', 'ftb-donation-form');
                    $prv_grondslag = __('Wij verwerken jouw gegevens om de donatie te kunnen uitvoeren en bij te houden. De juridische basis hiervoor is de uitvoering van de overeenkomst en ons gerechtvaardigd belang.', 'ftb-donation-form');
                    if ($prv_sender) {
                        /* translators: %s: e-mail address */
                        $prv_rights = sprintf(__('Jouw gegevens worden niet langer bewaard dan noodzakelijk is voor de doeleinden waarvoor ze zijn verzameld, of zolang de wet dit vereist. Op grond van de AVG heb je het recht op inzage, correctie, verwijdering, beperking van de verwerking en overdraagbaarheid van jouw gegevens. Voor vragen of verzoeken kun je contact met ons opnemen via %s.', 'ftb-donation-form'), $prv_sender);
                    } else {
                        $prv_rights = __('Jouw gegevens worden niet langer bewaard dan noodzakelijk is voor de doeleinden waarvoor ze zijn verzameld, of zolang de wet dit vereist. Op grond van de AVG heb je het recht op inzage, correctie, verwijdering, beperking van de verwerking en overdraagbaarheid van jouw gegevens.', 'ftb-donation-form');
                    }
                    $prv_copy = $prv_heading . "\n\n"
                        . $prv_intro . "\n"
                        . implode("\n", array_map(fn($i) => '- ' . $i, $prv_items)) . "\n\n"
                        . $prv_mollie . "\n\n"
                        . $prv_grondslag . "\n\n"
                        . $prv_rights;
                    ?>
                    <div id="ftb-privacy-suggestion" class="ftb-admin__suggestion" <?php echo get_option('ftb_privacy_url', '') ? '' : 'hidden'; ?>>
                        <h2><?php esc_html_e('Tekst voor privacyverklaring', 'ftb-donation-form'); ?></h2>
                        <p class="description"><?php esc_html_e('Kopieer deze tekst naar je privacyverklaring:', 'ftb-donation-form'); ?></p>
                        <p><strong><?php echo esc_html($prv_heading); ?></strong></p>
                        <p><?php echo esc_html($prv_intro); ?></p>
                        <ul>
                            <?php foreach ($prv_items as $prv_item) : ?>
                                <li><?php echo esc_html($prv_item); ?></li>
                            <?php endforeach; ?>
                        </ul>
                        <p><?php echo esc_html($prv_mollie); ?></p>
                        <p><?php echo esc_html($prv_grondslag); ?></p>
                        <p><?php echo esc_html($prv_rights); ?></p>
                        <textarea id="ftb-privacy-copy-text" style="display:none;" tabindex="-1" aria-hidden="true" readonly><?php echo esc_textarea($prv_copy); ?></textarea>
                        <button
                            type="button"
                            id="ftb-copy-privacy-text"
                            class="button button-small"
                            data-label-copied="<?php esc_attr_e('Gekopieerd ✓', 'ftb-donation-form'); ?>"><?php esc_html_e('Kopiëren', 'ftb-donation-form'); ?></button>
                    </div>
                </div>
            </section>

        </div>

        <div class="ftb-admin-form__col">

            <?php // ── Na betaling ──────────────────────────────────────── 
            ?>
            <section class="ftb-admin-form__section">
                <h3 class="ftb-admin-form__title">
                    <?php esc_html_e('Na betaling', 'ftb-donation-form'); ?>
                </h3>
                <p class="ftb-admin-form__description">
                    <?php esc_html_e('Kies wat er gebeurt nadat de donateur succesvol heeft betaald.', 'ftb-donation-form'); ?>
                </p>
                <div class="ftb-admin-form__group">
                    <?php do_settings_fields('ftb_donation_form_settings', 'ftb_section_post_payment'); ?>
                    <?php
                    $behavior     = get_option('ftb_post_payment_behavior', 'message');
                    $message      = get_option('ftb_post_payment_message', 'Hartelijk dank voor je donatie!');
                    $redirect_url = get_option('ftb_post_payment_redirect_url', '');
                    ?>
                    <div class="ftb-conditional<?php echo $behavior === 'message' ? ' is-visible' : ''; ?>" data-show-when="ftb_post_payment_behavior=message">
                        <div class="ftb-admin-form__stacked-field">
                            <label class="ftb-admin-form__label" for="ftb_post_payment_message"><?php esc_html_e('Bedankbericht', 'ftb-donation-form'); ?></label>
                            <textarea
                                id="ftb_post_payment_message"
                                name="ftb_post_payment_message"
                                rows="3"
                                class="large-text"
                                placeholder="<?php esc_attr_e('Hartelijk dank voor je donatie!', 'ftb-donation-form'); ?>"><?php echo esc_textarea($message); ?></textarea>
                        </div>
                    </div>
                    <div class="ftb-conditional<?php echo $behavior === 'redirect' ? ' is-visible' : ''; ?>" data-show-when="ftb_post_payment_behavior=redirect">
                        <div class="ftb-admin-form__stacked-field">
                            <label class="ftb-admin-form__label" for="ftb_post_payment_redirect_url"><?php esc_html_e('Doorstuur-URL', 'ftb-donation-form'); ?></label>
                            <input
                                type="url"
                                id="ftb_post_payment_redirect_url"
                                name="ftb_post_payment_redirect_url"
                                value="<?php echo esc_attr($redirect_url); ?>"
                                class="regular-text"
                                placeholder="https://jouwwebsite.nl/bedankt" />
                        </div>
                    </div>
                </div>
            </section>

            <?php // ── E-mailnotificaties ───────────────────────────────────── 
            ?>
            <section class="ftb-admin-form__section">
                <h3 class="ftb-admin-form__title">
                    <?php esc_html_e('E-mailnotificaties', 'ftb-donation-form'); ?>
                </h3>
                <p class="ftb-admin-form__description">
                    <?php esc_html_e('Stuur automatisch e-mails na een geslaagde betaling.', 'ftb-donation-form'); ?>
                </p>
                <div class="ftb-admin-form__group">
                    <?php
                    $email_donor    = get_option('ftb_email_donor_confirmation', '0');
                    $email_admin    = get_option('ftb_email_admin_notification', '0');
                    $email_from     = get_option('ftb_email_sender_address', '');
                    $donor_subject  = get_option('ftb_email_donor_subject', '');
                    $donor_body     = get_option('ftb_email_donor_body', '');
                    // Dummy data for the inline email preview
                    $preview_fields = get_option('ftb_form_fields', []);
                    $preview_date   = wp_date(get_option('date_format'));
                    $preview_optional = [];
                    if (! empty($preview_fields['phone']))        $preview_optional[] = __('Telefoon:', 'ftb-donation-form') . ' 06 12345678';
                    if (! empty($preview_fields['street']))       $preview_optional[] = __('Straat:', 'ftb-donation-form') . ' Dorpstraat';
                    if (! empty($preview_fields['house_number'])) $preview_optional[] = __('Huisnummer:', 'ftb-donation-form') . ' 12';
                    if (! empty($preview_fields['postal_code']))  $preview_optional[] = __('Postcode:', 'ftb-donation-form') . ' 1234 AB';
                    if (! empty($preview_fields['city']))         $preview_optional[] = __('Plaats:', 'ftb-donation-form') . ' Amsterdam';

                    $preview_recurring = get_option('ftb_enable_recurring', '1');
                    $preview_frequency = '1' === $preview_recurring
                        ? [__('Frequentie:', 'ftb-donation-form') . ' ' . __('Maandelijks', 'ftb-donation-form')]
                        : [];

                    $dummy_details_donor = implode("\n", array_merge([
                        __('Naam:', 'ftb-donation-form') . ' Alex de Vries',
                        __('Bedrag:', 'ftb-donation-form') . ' €10,00',
                    ], $preview_frequency, [
                        __('Datum:', 'ftb-donation-form') . ' ' . $preview_date,
                    ], $preview_optional));

                    ?>

                    <div class="ftb-toggle-group">
                        <input type="hidden" name="ftb_email_admin_notification" value="0">
                        <label class="ftb-toggle" for="ftb_email_admin_notification">
                            <input class="ftb-toggle__input" type="checkbox" id="ftb_email_admin_notification" name="ftb_email_admin_notification" value="1" <?php checked('1', $email_admin); ?>>
                            <span class="ftb-toggle__slider" aria-hidden="true"></span>
                            <span><?php esc_html_e('Melding bij nieuwe donatie', 'ftb-donation-form'); ?></span>
                        </label>
                    </div>

                    <div class="ftb-toggle-group">
                        <input type="hidden" name="ftb_email_donor_confirmation" value="0">
                        <label class="ftb-toggle" for="ftb_email_donor_confirmation">
                            <input class="ftb-toggle__input" type="checkbox" id="ftb_email_donor_confirmation" name="ftb_email_donor_confirmation" value="1" <?php checked('1', $email_donor); ?>>
                            <span class="ftb-toggle__slider" aria-hidden="true"></span>
                            <span><?php esc_html_e('Bevestigingsmail naar donateur', 'ftb-donation-form'); ?></span>
                        </label>
                        <div class="ftb-conditional<?php echo '1' === $email_donor ? ' is-visible' : ''; ?>" data-show-when="ftb_email_donor_confirmation=1">
                            <div class="ftb-admin-form__stacked-field">
                                <label class="ftb-admin-form__label" for="ftb_email_donor_subject"><?php esc_html_e('Onderwerp', 'ftb-donation-form'); ?></label>
                                <input
                                    type="text"
                                    id="ftb_email_donor_subject"
                                    name="ftb_email_donor_subject"
                                    value="<?php echo esc_attr($donor_subject); ?>"
                                    class="regular-text"
                                    placeholder="<?php esc_attr_e('Bedankt voor je donatie!', 'ftb-donation-form'); ?>" />
                            </div>
                            <div class="ftb-admin-form__stacked-field">
                                <label class="ftb-admin-form__label" for="ftb_email_donor_body"><?php esc_html_e('Bericht (optioneel)', 'ftb-donation-form'); ?></label>
                                <textarea
                                    id="ftb_email_donor_body"
                                    name="ftb_email_donor_body"
                                    rows="3"
                                    class="large-text"
                                    placeholder="<?php esc_attr_e('Hartelijk dank voor je steun!', 'ftb-donation-form'); ?>"><?php echo esc_textarea($donor_body); ?></textarea>
                                <p class="description"><?php esc_html_e('Naam, bedrag, frequentie, datum en de ingevulde formuliervelden worden automatisch toegevoegd.', 'ftb-donation-form'); ?></p>
                            </div>
                            <div class="ftb-email-preview">
                                <p class="ftb-email-preview__label"><?php esc_html_e('Voorbeeld', 'ftb-donation-form'); ?></p>
                                <div class="ftb-email-preview__content">
                                    <p class="ftb-email-preview__subject">
                                        <span class="ftb-email-preview__meta"><?php esc_html_e('Onderwerp:', 'ftb-donation-form'); ?></span>
                                        <span id="ftb_donor_preview_subject"><?php echo esc_html($donor_subject ?: __('Bedankt voor je donatie!', 'ftb-donation-form')); ?></span>
                                    </p>
                                    <pre id="ftb_donor_preview_body" class="ftb-email-preview__body" data-details="<?php echo esc_attr($dummy_details_donor); ?>"><?php echo esc_html($donor_body ? $donor_body . "\n\n" . $dummy_details_donor : $dummy_details_donor); ?></pre>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="ftb-admin-form__stacked-field ftb-admin-form__stacked-field--bordered">
                        <label class="ftb-admin-form__label" for="ftb_email_sender_address"><?php esc_html_e('E-mailadres afzender', 'ftb-donation-form'); ?></label>
                        <input
                            type="email"
                            id="ftb_email_sender_address"
                            name="ftb_email_sender_address"
                            value="<?php echo esc_attr($email_from); ?>"
                            class="regular-text"
                            placeholder="info@jouwwebsite.nl" />
                    </div>

                </div>
            </section>

        </div>

    </div>

    <div class="ftb-admin-form__submit">
        <?php submit_button(__('Instellingen opslaan', 'ftb-donation-form'), 'primary', 'submit', false); ?>
    </div>

</form>

<?php if (current_user_can('manage_options')) :
    $managers_saved     = isset($_GET['managers_saved']); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
    $editor_access_mode = get_option('ftb_editor_access_mode', 'all');
    $designated_ids     = array_map('absint', (array) get_option('ftb_designated_managers', []));
    $editor_users       = get_users(['role' => 'editor', 'orderby' => 'display_name']);
?>
    <h2 class="ftb-admin-form__section-heading"><?php esc_html_e('Toegang', 'ftb-donation-form'); ?></h2>

    <?php if ($managers_saved) : ?>
        <div class="notice notice-success is-dismissible">
            <p><?php esc_html_e('Toegang opgeslagen.', 'ftb-donation-form'); ?></p>
        </div>
    <?php endif; ?>

    <form method="post" action="<?php echo esc_url(admin_url('admin.php')); ?>">
        <?php wp_nonce_field('ftb_save_managers'); ?>
        <input type="hidden" name="ftb_save_managers" value="1">

        <section class="ftb-admin-form__section">
            <h3 class="ftb-admin-form__title"><?php esc_html_e('Formulierbeheerders', 'ftb-donation-form'); ?></h3>
            <p class="ftb-admin-form__description">
                <?php esc_html_e('Bepaal welke editors toegang hebben tot de plugin-instellingen en donaties. Administrators hebben altijd toegang.', 'ftb-donation-form'); ?>
            </p>
            <div class="ftb-admin-form__group">
                <div class="ftb-toggle-group">
                    <label class="ftb-toggle" for="ftb_editor_access_mode_admin_only">
                        <input class="ftb-toggle__input" type="radio" id="ftb_editor_access_mode_admin_only" name="ftb_editor_access_mode" value="admin_only" <?php checked($editor_access_mode, 'admin_only'); ?>>
                        <span class="ftb-toggle__slider" aria-hidden="true"></span>
                        <span><?php esc_html_e('Alleen administrators', 'ftb-donation-form'); ?></span>
                    </label>
                </div>
                <div class="ftb-toggle-group">
                    <label class="ftb-toggle" for="ftb_editor_access_mode_all">
                        <input class="ftb-toggle__input" type="radio" id="ftb_editor_access_mode_all" name="ftb_editor_access_mode" value="all" <?php checked($editor_access_mode, 'all'); ?>>
                        <span class="ftb-toggle__slider" aria-hidden="true"></span>
                        <span><?php esc_html_e('Alle editors', 'ftb-donation-form'); ?></span>
                    </label>
                </div>
                <div class="ftb-toggle-group">
                    <label class="ftb-toggle" for="ftb_editor_access_mode_specific">
                        <input class="ftb-toggle__input" type="radio" id="ftb_editor_access_mode_specific" name="ftb_editor_access_mode" value="specific" <?php checked($editor_access_mode, 'specific'); ?>>
                        <span class="ftb-toggle__slider" aria-hidden="true"></span>
                        <span><?php esc_html_e('Specifieke editors', 'ftb-donation-form'); ?></span>
                    </label>
                    <div id="ftb-specific-editors" class="ftb-admin-form__stacked-field"<?php echo 'specific' !== $editor_access_mode ? ' style="display:none;"' : ''; ?>>
                        <?php if ($editor_users) : ?>
                            <p class="ftb-admin-form__group-label"><?php esc_html_e('Selecteer editors met toegang', 'ftb-donation-form'); ?></p>
                            <div class="ftb-admin-form__field">
                                <ul class="ftb-checkbox-list">
                                    <?php foreach ($editor_users as $u) : ?>
                                        <li>
                                            <label>
                                                <input
                                                    type="checkbox"
                                                    name="ftb_designated_managers[]"
                                                    value="<?php echo absint($u->ID); ?>"
                                                    <?php checked(in_array($u->ID, $designated_ids, true)); ?>>
                                                <?php echo esc_html($u->display_name); ?>
                                            </label>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        <?php else : ?>
                            <p class="description"><?php esc_html_e('Geen editors gevonden.', 'ftb-donation-form'); ?></p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </section>

        <div class="ftb-admin-form__submit">
            <?php submit_button(__('Toegang opslaan', 'ftb-donation-form'), 'secondary', 'submit', false); ?>
        </div>
    </form>
    <script>
        (function() {
            var radios = document.querySelectorAll('[name="ftb_editor_access_mode"]');
            var list = document.getElementById('ftb-specific-editors');
            radios.forEach(function(r) {
                r.addEventListener('change', function() {
                    list.style.display = this.value === 'specific' ? '' : 'none';
                });
            });
        }());
    </script>
<?php endif; ?>