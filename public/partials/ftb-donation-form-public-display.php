<div class="donation-form">
    <h2 class="donation-form__title"><?php _e('Make a Donation', 'ftb-donation-form'); ?></h2>
    <form class="donation-form__form" id="ftb-donation-form" method="post" action="">
        <!-- Form fields will be added here -->
        <div class="donation-form__field">
            <label class="donation-form__label" for="amount"><?php _e('Amount', 'ftb-donation-form'); ?></label>
            <input class="donation-form__input" type="text" id="amount" name="amount" placeholder="€10">
        </div>
        <button class="donation-form__submit" type="submit"><?php _e('Donate Now', 'ftb-donation-form'); ?></button>
    </form>
</div>