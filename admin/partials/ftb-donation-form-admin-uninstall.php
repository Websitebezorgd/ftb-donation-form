<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
/**
 * @var string $return_url  The WordPress plugin-deletion URL to redirect to after choice.
 */
?>
<div class="wrap">
	<h1><?php esc_html_e( 'Plugin verwijderen', 'ftb-donation-form' ); ?></h1>

	<div class="notice notice-warning">
		<p><?php esc_html_e( 'Je staat op het punt de For The Better donatieformulier plugin te verwijderen. Wat moet er gebeuren met de donaties en instellingen?', 'ftb-donation-form' ); ?></p>
	</div>

	<form method="post">
		<?php wp_nonce_field( 'ftb_uninstall_confirm', 'ftb_uninstall_nonce' ); ?>
		<input type="hidden" name="ftb_return_url" value="<?php echo esc_attr( $return_url ); ?>">

		<table class="form-table" role="presentation">
			<tr>
				<td>
					<fieldset>
						<legend class="screen-reader-text"><?php esc_html_e( 'Gegevens bij verwijdering', 'ftb-donation-form' ); ?></legend>
						<label>
							<input type="radio" name="ftb_delete_data" value="0" checked>
							<?php esc_html_e( 'Instellingen en gegevens van donateurs bewaren', 'ftb-donation-form' ); ?>
						</label>
						<br><br>
						<label>
							<input type="radio" name="ftb_delete_data" value="1">
							<?php esc_html_e( 'Alle instellingen en gegevens verwijderen', 'ftb-donation-form' ); ?>
						</label>
					</fieldset>
				</td>
			</tr>
		</table>

		<p>
			<?php submit_button( __( 'Verwijder plugin', 'ftb-donation-form' ), 'primary', 'submit', false ); ?>
			&nbsp;
			<a href="<?php echo esc_url( admin_url( 'plugins.php' ) ); ?>" class="button"><?php esc_html_e( 'Annuleren', 'ftb-donation-form' ); ?></a>
		</p>
	</form>
</div>
