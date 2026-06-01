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
							<strong><?php esc_html_e( 'Donaties en instellingen bewaren', 'ftb-donation-form' ); ?></strong>
							<p class="description"><?php esc_html_e( 'De pluginbestanden worden verwijderd, maar alle donaties en instellingen blijven in de database bewaard. Je kunt de plugin opnieuw installeren zonder gegevens te verliezen.', 'ftb-donation-form' ); ?></p>
						</label>
						<br><br>
						<label>
							<input type="radio" name="ftb_delete_data" value="1">
							<strong><?php esc_html_e( 'Alle donaties en instellingen verwijderen', 'ftb-donation-form' ); ?></strong>
							<p class="description"><?php esc_html_e( 'De donatiestabel en alle instellingen worden permanent verwijderd. Dit kan niet ongedaan worden gemaakt.', 'ftb-donation-form' ); ?></p>
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
