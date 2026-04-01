<?php if ( ! defined( 'ABSPATH' ) ) exit; ?>
<div class="donation-form">
    <h2 class="donation-form__title"><?php esc_html_e('Make a Donation', 'ftb-donation-form'); ?></h2>
    <form class="donation-form__form" id="ftb-donation-form" method="post" action="">
        <?php wp_nonce_field( 'ftb_donation_submit', 'ftb_donation_nonce' ); ?>
        <!-- Form fields will be added here -->
        <div class="donation-form__field">
            <label class="donation-form__label" for="amount"><?php esc_html_e('Amount', 'ftb-donation-form'); ?></label>
            <input class="donation-form__input" type="text" id="amount" name="amount" placeholder="€10">
        </div>
        <button class="donation-form__submit" type="submit"><?php esc_html_e('Donate Now', 'ftb-donation-form'); ?></button>
    </form>
</div>