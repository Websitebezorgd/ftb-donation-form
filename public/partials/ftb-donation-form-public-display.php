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

<div class="ftb-donation-form" aria-describedby="ftb-donation-title">

    <?php if ($success) : ?>

        <div class="ftb-donation-form__success" role="status">
            <p tabindex="-1" autofocus><?php esc_html_e('Bedankt! Je donatie is ontvangen.', 'ftb-donation-form'); ?></p>
        </div>

    <?php else : ?>

        <h2 id="ftb-donation-title" class="ftb-donation-form__title"><?php echo esc_html($title); ?></h2>

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

        <ol class="ftb-donation-form__steps">
            <li data-step="1" class="<?php echo $start_on_step === 1 ? 'is-active' : ''; ?>" <?php echo $start_on_step === 1 ? 'aria-current="step"' : ''; ?>><?php esc_html_e('Jouw donatie', 'ftb-donation-form'); ?></li>
            <li data-step="2" class="<?php echo $start_on_step === 2 ? 'is-active' : ''; ?>" <?php echo $start_on_step === 2 ? 'aria-current="step"' : ''; ?>><?php esc_html_e('Jouw gegevens', 'ftb-donation-form'); ?></li>
        </ol>

        <div class="ftb-donation-form__error-summary" id="ftb-error-summary" role="alert" tabindex="-1" <?php echo empty($errors) ? 'hidden' : ''; ?>>
            <p class="ftb-donation-form__error-summary-title">
                <?php esc_html_e('Controleer de volgende fouten:', 'ftb-donation-form'); ?>
            </p>
            <ul class="ftb-donation-form__error-list">
                <?php foreach ($errors as $field => $message) : ?>
                    <li><a href="#ftb-<?php echo esc_attr($field); ?>"><?php echo esc_html($message); ?></a></li>
                <?php endforeach; ?>
            </ul>
        </div>

        <form
            class="ftb-donation-form__form"
            id="ftb-donation-form"
            method="post"
            novalidate
            data-start-step="<?php echo esc_attr($start_on_step); ?>"
            aria-label="<?php esc_attr_e('Donatieformulier', 'ftb-donation-form'); ?>">
            <?php wp_nonce_field('ftb_donation_submit', 'ftb_donation_nonce'); ?>

            <div id="ftb-step-1" class="ftb-donation-form__step is-active" data-step="1">
                <p class="ftb-donation-form__step-intro"><?php esc_html_e('Als iets verplicht is, staat dat erbij.', 'ftb-donation-form'); ?></p>
                <?php if ( $enable_recurring ) : ?>
                <!-- ── Frequentie ─────────────────────────────────────────── -->
                <fieldset class="ftb-donation-form__fieldset ftb-donation-form__fieldset--radio" aria-describedby="ftb-frequency-error">
                    <legend class="ftb-donation-form__legend"><?php esc_html_e('Frequentie (verplicht)', 'ftb-donation-form'); ?></legend>

                    <div class="ftb-donation-form__radio-group">
                        <?php
                        $frequencies = [
                            'one_time' => __('Eenmalig', 'ftb-donation-form'),
                            'monthly'  => __('Maandelijks', 'ftb-donation-form'),
                            'yearly'   => __('Jaarlijks', 'ftb-donation-form'),
                        ];
                        $selected_freq = $old('frequency');
                        foreach ($frequencies as $val => $label) :
                        ?>
                            <input
                                class="ftb-donation-form__radio"
                                type="radio"
                                id="ftb-frequency-<?php echo esc_attr($val); ?>"
                                name="ftb_frequency"
                                value="<?php echo esc_attr($val); ?>"
                                <?php checked($selected_freq, $val); ?>><label class="ftb-donation-form__radio-label" for="ftb-frequency-<?php echo esc_attr($val); ?>"><?php echo esc_html($label); ?></label>
                        <?php endforeach; ?>
                    </div>

                    <p class="ftb-donation-form__error" id="ftb-frequency-error" <?php echo empty($errors['frequency']) ? 'hidden' : ''; ?>><?php echo ! empty($errors['frequency']) ? esc_html($errors['frequency']) : esc_html__('Kies hoe vaak je wilt doneren', 'ftb-donation-form'); ?></p>
                </fieldset>
                <?php else : ?>
                <input type="hidden" name="ftb_frequency" value="one_time">
                <?php endif; ?>

                <!-- ── Bedrag ─────────────────────────────────────────────── -->
                <fieldset class="ftb-donation-form__fieldset ftb-donation-form__fieldset--radio" aria-describedby="ftb-amount-error">
                    <legend class="ftb-donation-form__legend"><?php esc_html_e('Bedrag (verplicht)', 'ftb-donation-form'); ?></legend>

                    <div class="ftb-donation-form__radio-group ftb-donation-form__radio-group--amounts">
                        <?php foreach ($amount_options as $preset) : ?>
                            <input
                                class="ftb-donation-form__radio"
                                type="radio"
                                id="ftb-amount-<?php echo esc_attr($preset); ?>"
                                name="ftb_amount"
                                value="<?php echo esc_attr($preset); ?>"
                                <?php checked($old('amount'), (string) $preset); ?>><label class="ftb-donation-form__radio-label ftb-donation-form__radio-label--amount" for="ftb-amount-<?php echo esc_attr($preset); ?>">€<?php echo esc_html(number_format((float) $preset, 0, ',', '.')); ?></label>
                        <?php endforeach; ?>

                        <?php if ($allow_custom) : ?>
                            <input
                                class="ftb-donation-form__radio"
                                type="radio"
                                id="ftb-amount-custom-radio"
                                name="ftb_amount"
                                value="custom"
                                aria-controls="ftb-custom-amount-wrapper"
                                <?php checked($old('amount'), 'custom'); ?>><label class="ftb-donation-form__radio-label ftb-donation-form__radio-label--custom" for="ftb-amount-custom-radio"><?php esc_html_e('Anders', 'ftb-donation-form'); ?></label>
                        <?php endif; ?>
                    </div>

                    <?php if ($allow_custom) : ?>
                        <div class="ftb-donation-form__custom-amount<?php echo $old('amount') !== 'custom' ? ' ftb-donation-form__custom-amount--hidden' : ''; ?>"
                            id="ftb-custom-amount-wrapper"
                            role="group"
                            aria-label="<?php esc_attr_e('Eigen bedrag', 'ftb-donation-form'); ?>">
                            <label class="ftb-donation-form__label" for="ftb-custom-amount">
                                <?php
                                /* translators: %s: minimum amount, e.g. "1" */
                                printf( esc_html__( 'Vul een bedrag in (minimaal €%s)', 'ftb-donation-form' ), esc_html( $min_custom_amount ) );
                                ?>
                            </label>
                            <div class="ftb-donation-form__input-wrapper">
                                <span class="ftb-donation-form__currency-prefix" aria-hidden="true">€</span>
                                <input
                                    class="ftb-donation-form__input ftb-donation-form__input--amount"
                                    type="number"
                                    name="ftb_custom_amount"
                                    id="ftb-custom-amount"
                                    min="<?php echo esc_attr( $min_custom_amount ); ?>"
                                    step="0.01"
                                    value="<?php echo esc_attr($old('custom_amount')); ?>"
                                    aria-label="<?php esc_attr_e('Eigen bedrag in euro', 'ftb-donation-form'); ?>"
                                    aria-required="<?php echo $old('amount') === 'custom' ? 'true' : 'false'; ?>"
                                    aria-invalid="false">
                            </div>
                        </div>
                    <?php endif; ?>

                    <p class="ftb-donation-form__error" id="ftb-amount-error" <?php echo empty($errors['amount']) ? 'hidden' : ''; ?>><?php echo ! empty($errors['amount']) ? esc_html($errors['amount']) : esc_html__('Kies een bedrag om te doneren', 'ftb-donation-form'); ?></p>
                </fieldset>

                <div class="ftb-donation-form__field ftb-donation-form__field--buttons">
                    <button type="button" class="ftb-donation-form__button ftb-donation-form__button--next" id="ftb-next-button"><?php esc_html_e('Volgende', 'ftb-donation-form'); ?></button>
                </div>
            </div>

            <div id="ftb-step-2" class="ftb-donation-form__step" data-step="2" hidden>

                <p class="ftb-donation-form__step-intro" tabindex="-1"><?php esc_html_e('Als iets verplicht is, staat dat erbij.', 'ftb-donation-form'); ?></p>

                <!-- ── Persoonsgegevens ───────────────────────────────────── -->
                <fieldset class="ftb-donation-form__fieldset ftb-donation-form__fieldset--personal">
                    <legend class="ftb-donation-form__legend"><h3><?php esc_html_e('Jouw gegevens', 'ftb-donation-form'); ?></h3></legend>

                    <div class="ftb-donation-form__field">
                        <label class="ftb-donation-form__label" for="ftb-name"><?php esc_html_e('Volledige naam (verplicht)', 'ftb-donation-form'); ?></label>
                        <input
                            class="ftb-donation-form__input<?php echo ! empty($errors['name']) ? ' ftb-donation-form__input--error' : ''; ?>"
                            type="text"
                            name="ftb_name"
                            id="ftb-name"
                            value="<?php echo esc_attr($old('name')); ?>"
                            autocomplete="name"
                            aria-required="true"
                            aria-invalid="<?php echo ! empty($errors['name']) ? 'true' : 'false'; ?>"
                            aria-describedby="ftb-name-error">
                        <p class="ftb-donation-form__error" id="ftb-name-error" <?php echo empty($errors['name']) ? 'hidden' : ''; ?>><?php echo ! empty($errors['name']) ? esc_html($errors['name']) : esc_html__('Vul je naam in', 'ftb-donation-form'); ?></p>
                    </div>

                    <div class="ftb-donation-form__field">
                        <label class="ftb-donation-form__label" for="ftb-email"><?php esc_html_e('E-mailadres (verplicht)', 'ftb-donation-form'); ?></label>
                        <input
                            class="ftb-donation-form__input<?php echo ! empty($errors['email']) ? ' ftb-donation-form__input--error' : ''; ?>"
                            type="email"
                            name="ftb_email"
                            id="ftb-email"
                            value="<?php echo esc_attr($old('email')); ?>"
                            autocomplete="email"
                            aria-required="true"
                            aria-invalid="<?php echo ! empty($errors['email']) ? 'true' : 'false'; ?>"
                            aria-describedby="ftb-email-error">
                        <p class="ftb-donation-form__error" id="ftb-email-error" <?php echo empty($errors['email']) ? 'hidden' : ''; ?>><?php echo ! empty($errors['email']) ? esc_html($errors['email']) : esc_html__('Vul een geldig e-mailadres in', 'ftb-donation-form'); ?></p>
                    </div>

                    <?php if ($field_enabled('phone')) : ?>
                        <div class="ftb-donation-form__field">
                            <label class="ftb-donation-form__label" for="ftb-phone"><?php esc_html_e('Telefoonnummer', 'ftb-donation-form'); ?></label>
                            <input
                                class="ftb-donation-form__input"
                                type="tel"
                                name="ftb_phone"
                                id="ftb-phone"
                                value="<?php echo esc_attr($old('phone')); ?>"
                                autocomplete="tel">
                        </div>
                    <?php endif; ?>

                    <?php if ($field_enabled('street') || $field_enabled('house_number')) : ?>
                        <div class="ftb-donation-form__field ftb-donation-form__field--row">
                            <?php if ($field_enabled('street')) : ?>
                                <div class="ftb-donation-form__field ftb-donation-form__field--street">
                                    <label class="ftb-donation-form__label" for="ftb-street"><?php esc_html_e('Straat', 'ftb-donation-form'); ?></label>
                                    <input
                                        class="ftb-donation-form__input"
                                        type="text"
                                        name="ftb_street"
                                        id="ftb-street"
                                        value="<?php echo esc_attr($old('street')); ?>"
                                        autocomplete="address-line1">
                                </div>
                            <?php endif; ?>
                            <?php if ($field_enabled('house_number')) : ?>
                                <div class="ftb-donation-form__field ftb-donation-form__field--house-number">
                                    <label class="ftb-donation-form__label" for="ftb-house-number"><?php esc_html_e('Huisnummer', 'ftb-donation-form'); ?></label>
                                    <input
                                        class="ftb-donation-form__input"
                                        type="text"
                                        name="ftb_house_number"
                                        id="ftb-house-number"
                                        value="<?php echo esc_attr($old('house_number')); ?>"
                                        autocomplete="address-line2">
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>

                    <?php if ($field_enabled('postal_code') || $field_enabled('city')) : ?>
                        <div class="ftb-donation-form__field ftb-donation-form__field--row">
                            <?php if ($field_enabled('postal_code')) : ?>
                                <div class="ftb-donation-form__field ftb-donation-form__field--postal-code">
                                    <label class="ftb-donation-form__label" for="ftb-postal-code"><?php esc_html_e('Postcode', 'ftb-donation-form'); ?></label>
                                    <input
                                        class="ftb-donation-form__input"
                                        type="text"
                                        name="ftb_postal_code"
                                        id="ftb-postal-code"
                                        value="<?php echo esc_attr($old('postal_code')); ?>"
                                        autocomplete="postal-code">
                                </div>
                            <?php endif; ?>
                            <?php if ($field_enabled('city')) : ?>
                                <div class="ftb-donation-form__field ftb-donation-form__field--city">
                                    <label class="ftb-donation-form__label" for="ftb-city"><?php esc_html_e('Plaats', 'ftb-donation-form'); ?></label>
                                    <input
                                        class="ftb-donation-form__input"
                                        type="text"
                                        name="ftb_city"
                                        id="ftb-city"
                                        value="<?php echo esc_attr($old('city')); ?>"
                                        autocomplete="address-level2">
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>

                </fieldset>

                <!-- ── GDPR ───────────────────────────────────────────────── -->
                <fieldset class="ftb-donation-form__fieldset">
                    <legend class="ftb-donation-form__legend"><h3><?php esc_html_e('Privacyverklaring', 'ftb-donation-form'); ?></h3></legend>

                    <?php if ($privacy_url) : ?>
                        <a class="ftb-donation-form__privacy-link" href="<?php echo esc_url($privacy_url); ?>" target="_blank" rel="noopener noreferrer"><?php esc_html_e('Lees onze privacyverklaring', 'ftb-donation-form'); ?></a>
                    <?php endif; ?>

                    <div class="ftb-donation-form__checkbox-wrapper">
                        <input
                            class="ftb-donation-form__checkbox"
                            type="checkbox"
                            name="ftb_gdpr"
                            id="ftb-gdpr"
                            value="1"
                            aria-required="true"
                            aria-invalid="<?php echo ! empty($errors['gdpr']) ? 'true' : 'false'; ?>"
                            aria-describedby="ftb-gdpr-error"
                            <?php checked($old('gdpr'), '1'); ?>>
                        <label class="ftb-donation-form__checkbox-label<?php echo ! empty($errors['gdpr']) ? ' ftb-donation-form__checkbox-label--error' : ''; ?>" for="ftb-gdpr"><?php esc_html_e('Ik ga akkoord met de privacyverklaring (verplicht)', 'ftb-donation-form'); ?></label>
                    </div>

                    <p class="ftb-donation-form__error" id="ftb-gdpr-error" <?php echo empty($errors['gdpr']) ? 'hidden' : ''; ?>><?php echo ! empty($errors['gdpr']) ? esc_html($errors['gdpr']) : esc_html__('Je moet akkoord gaan met de privacyverklaring', 'ftb-donation-form'); ?></p>
                </fieldset>

                <div class="ftb-donation-form__field ftb-donation-form__field--buttons">
                    <button type="button" class="ftb-donation-form__button ftb-donation-form__button--previous" id="ftb-previous-button"><?php esc_html_e('Vorige', 'ftb-donation-form'); ?></button>
                    <button class="ftb-donation-form__button ftb-donation-form__button--submit" type="submit"><?php esc_html_e( 'Doneer nu', 'ftb-donation-form' ); ?></button>
                </div>
            </div>
        </form>

    <?php endif; // success
    ?>

</div>
