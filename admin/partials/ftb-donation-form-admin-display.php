<div class="wrap">
    <h1><?php _e('FTB Donation Form Settings', 'ftb-donation-form'); ?></h1>
    <form method="post" action="options.php">
        <?php
        settings_fields('ftb_donation_form_settings');
        do_settings_sections('ftb_donation_form_settings');
        submit_button();
        ?>
    </form>
</div>