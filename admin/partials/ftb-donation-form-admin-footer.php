<?php
/**
 * Shared admin footer partial for all plugin pages.
 *
 * @since      1.1.0
 * @package    FTB_Donation_Form
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
?>
<p class="ftb-admin__footer">
    <img src="<?php echo esc_url( FTB_DONATION_FORM_PLUGIN_URL . 'admin/images/for-the-better-favicon.png' ); ?>" alt="" width="16" height="16" class="ftb-admin__footer-icon" aria-hidden="true">
    <?php esc_html_e( 'Een plugin van', 'ftb-donation-form' ); ?>
    <a href="<?php echo esc_url( 'https://forthebetter.nl' ); ?>" target="_blank" rel="noopener noreferrer">For The Better</a>
</p>
