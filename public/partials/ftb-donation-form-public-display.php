<?php if (! defined('ABSPATH')) exit; ?>
<?php
/**
 * Variables available from render_donation_form():
 *
 * @var array  $errors         Field errors: [ 'field' => 'message' ]
 * @var array  $old_values     Submitted values for re-populating the form
 * @var bool   $success        Whether the form was successfully submitted
 * @var array  $form_fields    Admin-configured optional fields
 * @var array  $amount_options Admin-configured preset amounts
 * @var bool   $allow_custom   Whether custom amount input is enabled
 * @var string $privacy_url    Privacy policy URL
 */

$field_enabled = static function ($key) use ($form_fields) {
    return ! empty($form_fields[$key]) && $form_fields[$key] === '1';
};

$old = static function ($key, $default = '') use ($old_values) {
    return $old_values[$key] ?? $default;
};
?>

<div class="donation-form" aria-describedby="ftb-donation-title">

    <?php if ($success) : ?>

        <div class="donation-form__success" role="status">
            <p><?php esc_html_e('Bedankt! Je donatie is ontvangen.', 'ftb-donation-form'); ?></p>
        </div>

    <?php else : ?>

        <h2 id="ftb-donation-title"
            tabindex="-1"
            aria-live="polite"
            class="donation-form__title"><?php esc_html_e('Doneer nu', 'ftb-donation-form'); ?></h2>

        <!-- ── Stappen ──────────────────────────────────────────────────── -->
        <nav aria-label="Formuliervoortgang">
            <ol class="donation-form__steps">
                <li data-step="1" aria-current="step">Stap 1: Jouw donatie</li>
                <li data-step="2">Stap 2: Jouw gegevens</li>
            </ol>
        </nav>

        <?php if (! empty($errors)) : ?>
            <div class="donation-form__error-summary" id="ftb-error-summary" role="alert" tabindex="-1">
                <p class="donation-form__error-summary-title">
                    <?php esc_html_e('Controleer de volgende fouten:', 'ftb-donation-form'); ?>
                </p>
                <ul class="donation-form__error-list">
                    <?php foreach ($errors as $field => $message) : ?>
                        <li><a href="#ftb-<?php echo esc_attr($field); ?>"><?php echo esc_html($message); ?></a></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <?php
        $step2_fields  = ['name', 'email', 'gdpr', 'phone', 'street', 'house_number', 'postal_code', 'city'];
        $start_on_step = 1;
        foreach ($step2_fields as $f) {
            if (! empty($errors[$f])) {
                $start_on_step = 2;
                break;
            }
        }
        ?>
        <form
            class="donation-form__form"
            id="ftb-donation-form"
            method="post"
            action=""
            novalidate
            data-start-step="<?php echo esc_attr($start_on_step); ?>"
            aria-label="<?php esc_attr_e('Donatieformulier', 'ftb-donation-form'); ?>">
            <?php wp_nonce_field('ftb_donation_submit', 'ftb_donation_nonce'); ?>

            <div id="ftb-step-1">
                <!-- ── Frequentie ──────────────────────────────────────────────────── -->
                <fieldset class="donation-form__fieldset" <?php echo ! empty($errors['frequency']) ? 'aria-describedby="ftb-frequency-error"' : ''; ?>>
                    <legend class="donation-form__legend">
                        <?php esc_html_e('Frequentie', 'ftb-donation-form'); ?>
                        <span class="donation-form__required" aria-hidden="true">*</span>
                    </legend>
                    <div class="donation-form__radio-group" id="ftb-frequency">
                        <?php
                        $frequencies = [
                            'one_time' => __('Eenmalig', 'ftb-donation-form'),
                            'weekly'   => __('Wekelijks', 'ftb-donation-form'),
                            'monthly'  => __('Maandelijks', 'ftb-donation-form'),
                            'yearly'   => __('Jaarlijks', 'ftb-donation-form'),
                        ];
                        $selected_freq = $old('frequency', 'one_time');
                        foreach ($frequencies as $val => $label) :
                        ?>
                            <label class="donation-form__radio-label">
                                <input
                                    class="donation-form__radio"
                                    type="radio"
                                    name="ftb_frequency"
                                    value="<?php echo esc_attr($val); ?>"
                                    <?php checked($selected_freq, $val); ?> />
                                <?php echo esc_html($label); ?>
                            </label>
                        <?php endforeach; ?>
                    </div>
                    <?php if (! empty($errors['frequency'])) : ?>
                        <p class="donation-form__error" id="ftb-frequency-error">
                            <?php echo esc_html($errors['frequency']); ?>
                        </p>
                    <?php endif; ?>
                </fieldset>

                <!-- ── Bedrag ──────────────────────────────────────────────────────── -->
                <fieldset class="donation-form__fieldset" <?php echo ! empty($errors['amount']) ? 'aria-describedby="ftb-amount-error"' : ''; ?>>
                    <legend class="donation-form__legend">
                        <?php esc_html_e('Bedrag', 'ftb-donation-form'); ?>
                        <span class="donation-form__required" aria-hidden="true">*</span>
                    </legend>
                    <div class="donation-form__radio-group donation-form__radio-group--amounts" id="ftb-amount">
                        <?php foreach ($amount_options as $preset) : ?>
                            <label class="donation-form__radio-label donation-form__radio-label--amount">
                                <input
                                    class="donation-form__radio"
                                    type="radio"
                                    name="ftb_amount"
                                    value="<?php echo esc_attr($preset); ?>"
                                    <?php checked($old('amount'), (string) $preset); ?> />
                                <span aria-hidden="true">€</span><?php echo esc_html(number_format((float) $preset, 0, ',', '.')); ?>
                            </label>
                        <?php endforeach; ?>

                        <?php if ($allow_custom) : ?>
                            <label class="donation-form__radio-label donation-form__radio-label--custom">
                                <input
                                    class="donation-form__radio"
                                    type="radio"
                                    name="ftb_amount"
                                    value="custom"
                                    id="ftb-amount-custom-radio"
                                    aria-expanded="<?php echo $old('amount') === 'custom' ? 'true' : 'false'; ?>"
                                    aria-controls="ftb-custom-amount-wrapper"
                                    <?php checked($old('amount'), 'custom'); ?> />
                                <?php esc_html_e('Anders', 'ftb-donation-form'); ?>
                            </label>
                            <div class="donation-form__custom-amount<?php echo $old('amount') !== 'custom' ? ' donation-form__custom-amount--hidden' : ''; ?>"
                                id="ftb-custom-amount-wrapper"
                                role="group"
                                aria-label="<?php esc_attr_e('Eigen bedrag', 'ftb-donation-form'); ?>">
                                <label class="donation-form__label" for="ftb-custom-amount">
                                    <?php esc_html_e('Vul een bedrag in', 'ftb-donation-form'); ?>
                                </label>
                                <div class="donation-form__input-wrapper">
                                    <span class="donation-form__currency-prefix" aria-hidden="true">€</span>
                                    <input
                                        class="donation-form__input donation-form__input--amount"
                                        type="number"
                                        name="ftb_custom_amount"
                                        id="ftb-custom-amount"
                                        min="0.01"
                                        step="0.01"
                                        value="<?php echo esc_attr($old('custom_amount')); ?>"
                                        aria-label="<?php esc_attr_e('Eigen bedrag in euro', 'ftb-donation-form'); ?>"
                                        <?php echo $old('amount') === 'custom' ? 'aria-required="true"' : ''; ?> />
                                </div>
                            <?php endif; ?>
                            </div>
                            <?php if (! empty($errors['amount'])) : ?>
                                <p class="donation-form__error" id="ftb-amount-error">
                                    <?php echo esc_html($errors['amount']); ?>
                                </p>
                            <?php endif; ?>
                </fieldset>

                <div class="donation-form__field donation-form__field--buttons">
                    <button type="button" class="donation-form__next-button" id="ftb-next-button">
                        <?php esc_html_e('Volgende', 'ftb-donation-form'); ?>
                    </button>
                </div>
            </div>

            <div id="ftb-step-2" class="donation-form__step-2" hidden>
                <!-- ── Persoonsgegevens ────────────────────────────────────────────── -->
                <fieldset class="donation-form__fieldset">
                    <legend class="donation-form__legend">
                        <?php esc_html_e('Uw gegevens', 'ftb-donation-form'); ?>
                    </legend>

                    <!-- Volledige naam -->
                    <div class="donation-form__field">
                        <label class="donation-form__label" for="ftb-name">
                            <?php esc_html_e('Volledige naam', 'ftb-donation-form'); ?>
                            <span class="donation-form__required" aria-hidden="true">*</span>
                        </label>
                        <input
                            class="donation-form__input<?php echo ! empty($errors['name']) ? ' donation-form__input--error' : ''; ?>"
                            type="text"
                            name="ftb_name"
                            id="ftb-name"
                            value="<?php echo esc_attr($old('name')); ?>"
                            autocomplete="name"
                            aria-required="true"
                            <?php if (! empty($errors['name'])) : ?>
                            aria-invalid="true"
                            aria-describedby="ftb-name-error"
                            <?php endif; ?> />
                        <?php if (! empty($errors['name'])) : ?>
                            <p class="donation-form__error" id="ftb-name-error">
                                <?php echo esc_html($errors['name']); ?>
                            </p>
                        <?php endif; ?>
                    </div>

                    <!-- E-mailadres -->
                    <div class="donation-form__field">
                        <label class="donation-form__label" for="ftb-email">
                            <?php esc_html_e('E-mailadres', 'ftb-donation-form'); ?>
                            <span class="donation-form__required" aria-hidden="true">*</span>
                        </label>
                        <input
                            class="donation-form__input<?php echo ! empty($errors['email']) ? ' donation-form__input--error' : ''; ?>"
                            type="email"
                            name="ftb_email"
                            id="ftb-email"
                            value="<?php echo esc_attr($old('email')); ?>"
                            autocomplete="email"
                            aria-required="true"
                            <?php if (! empty($errors['email'])) : ?>
                            aria-invalid="true"
                            aria-describedby="ftb-email-error"
                            <?php endif; ?> />
                        <?php if (! empty($errors['email'])) : ?>
                            <p class="donation-form__error" id="ftb-email-error">
                                <?php echo esc_html($errors['email']); ?>
                            </p>
                        <?php endif; ?>
                    </div>

                    <!-- Telefoonnummer (optioneel) -->
                    <?php if ($field_enabled('phone')) : ?>
                        <div class="donation-form__field">
                            <label class="donation-form__label" for="ftb-phone">
                                <?php esc_html_e('Telefoonnummer', 'ftb-donation-form'); ?>
                            </label>
                            <input
                                class="donation-form__input"
                                type="tel"
                                name="ftb_phone"
                                id="ftb-phone"
                                value="<?php echo esc_attr($old('phone')); ?>"
                                autocomplete="tel" />
                        </div>
                    <?php endif; ?>

                    <!-- Straat + Huisnummer -->
                    <?php if ($field_enabled('street') || $field_enabled('house_number')) : ?>
                        <div class="donation-form__field donation-form__field--row">
                            <?php if ($field_enabled('street')) : ?>
                                <div class="donation-form__field donation-form__field--street">
                                    <label class="donation-form__label" for="ftb-street">
                                        <?php esc_html_e('Straat', 'ftb-donation-form'); ?>
                                    </label>
                                    <input
                                        class="donation-form__input"
                                        type="text"
                                        name="ftb_street"
                                        id="ftb-street"
                                        value="<?php echo esc_attr($old('street')); ?>"
                                        autocomplete="address-line1" />
                                </div>
                            <?php endif; ?>
                            <?php if ($field_enabled('house_number')) : ?>
                                <div class="donation-form__field donation-form__field--house-number">
                                    <label class="donation-form__label" for="ftb-house-number">
                                        <?php esc_html_e('Huisnummer', 'ftb-donation-form'); ?>
                                    </label>
                                    <input
                                        class="donation-form__input"
                                        type="text"
                                        name="ftb_house_number"
                                        id="ftb-house-number"
                                        value="<?php echo esc_attr($old('house_number')); ?>"
                                        autocomplete="address-line2" />
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>

                    <!-- Postcode + Plaats -->
                    <?php if ($field_enabled('postal_code') || $field_enabled('city')) : ?>
                        <div class="donation-form__field donation-form__field--row">
                            <?php if ($field_enabled('postal_code')) : ?>
                                <div class="donation-form__field donation-form__field--postal-code">
                                    <label class="donation-form__label" for="ftb-postal-code">
                                        <?php esc_html_e('Postcode', 'ftb-donation-form'); ?>
                                    </label>
                                    <input
                                        class="donation-form__input"
                                        type="text"
                                        name="ftb_postal_code"
                                        id="ftb-postal-code"
                                        value="<?php echo esc_attr($old('postal_code')); ?>"
                                        autocomplete="postal-code" />
                                </div>
                            <?php endif; ?>
                            <?php if ($field_enabled('city')) : ?>
                                <div class="donation-form__field donation-form__field--city">
                                    <label class="donation-form__label" for="ftb-city">
                                        <?php esc_html_e('Plaats', 'ftb-donation-form'); ?>
                                    </label>
                                    <input
                                        class="donation-form__input"
                                        type="text"
                                        name="ftb_city"
                                        id="ftb-city"
                                        value="<?php echo esc_attr($old('city')); ?>"
                                        autocomplete="address-level2" />
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>

                </fieldset>

                <!-- ── GDPR ────────────────────────────────────────────────────────── -->
                <div class="donation-form__field">

                    <?php if ($privacy_url) : ?>
                        <a class="donation-form__privacy-link" href="<?php echo esc_url($privacy_url); ?>" target="_blank" rel="noopener noreferrer">
                            <?php esc_html_e('Lees onze privacyverklaring', 'ftb-donation-form'); ?>
                        </a>
                    <?php endif; ?>

                    <label class="donation-form__checkbox-label<?php echo ! empty($errors['gdpr']) ? ' donation-form__checkbox-label--error' : ''; ?>">
                        <input
                            class="donation-form__checkbox"
                            type="checkbox"
                            name="ftb_gdpr"
                            id="ftb-gdpr"
                            value="1"
                            aria-required="true"
                            <?php checked($old('gdpr'), '1'); ?>
                            <?php if (! empty($errors['gdpr'])) : ?>
                            aria-invalid="true"
                            aria-describedby="ftb-gdpr-error"
                            <?php endif; ?> />
                        <?php esc_html_e('Ik ga akkoord met de privacyverklaring', 'ftb-donation-form'); ?>
                        <span class="donation-form__required" aria-hidden="true">*</span>
                    </label>

                    <?php if (! empty($errors['gdpr'])) : ?>
                        <p class="donation-form__error" id="ftb-gdpr-error">
                            <?php echo esc_html($errors['gdpr']); ?>
                        </p>
                    <?php endif; ?>

                </div>

                <p class="donation-form__required-note">
                    <span class="donation-form__required" aria-hidden="true">*</span>
                    <?php esc_html_e('Verplicht veld', 'ftb-donation-form'); ?>
                </p>

                <div class="donation-form__field donation-form__field--buttons">
                    <button type="button" class="donation-form__previous-button" id="ftb-previous-button">
                        <?php esc_html_e('Vorige', 'ftb-donation-form'); ?>
                    </button>
                    <button class="donation-form__submit" type="submit">
                        <?php esc_html_e('Doneer nu', 'ftb-donation-form'); ?>
                    </button>
                </div>
            </div>
        </form>

    <?php endif; // success 
    ?>

</div>