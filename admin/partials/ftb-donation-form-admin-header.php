<?php
/**
 * Shared admin header partial for all plugin pages.
 *
 * Expects $page_title to be set by the including template.
 *
 * @since      1.1.0
 * @package    FTB_Donation_Form
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div class="ftb-admin__header">
	<div class="ftb-admin__header-start">
		<h1 class="ftb-admin__title">
			<span class="dashicons dashicons-heart" aria-hidden="true"></span>
			<?php echo esc_html( $page_title ); ?>
		</h1>
		<?php
		if ( ! empty( $page_action ) ) {
			echo $page_action;} // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped by caller 
		?>
	</div>
	<img
		src="<?php echo esc_url( FTB_DONATION_FORM_PLUGIN_URL . 'admin/images/for-the-better-logo.png' ); ?>"
		alt="For The Better"
		class="ftb-admin__logo"
	>
</div>
