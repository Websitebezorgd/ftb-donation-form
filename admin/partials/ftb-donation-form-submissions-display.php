<?php
/**
 * Donations submissions page template.
 *
 * @since      1.0.0
 * @package    FTB_Donation_Form
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$list_table = new FTB_Donations_List_Table();
$list_table->prepare_items();
?>

	<?php
    // phpcs:disable WordPress.Security.NonceVerification.Recommended
	$deleted = isset( $_GET['deleted'] ) ? (int) $_GET['deleted'] : 0;
	$updated = isset( $_GET['updated'] ) && '1' === $_GET['updated'];
    // phpcs:enable
	if ( $deleted > 0 ) :
		?>
	<div class="notice notice-success is-dismissible">
		<p>
		<?php
		printf(
			/* translators: %d: number of deleted donations */
			esc_html( _n( '%d donatie verwijderd.', '%d donaties verwijderd.', $deleted, 'ftb-donation-form' ) ),
			absint( $deleted )
		);
		?>
		</p>
	</div>
	<?php endif; ?>

	<?php if ( $updated ) : ?>
	<div class="notice notice-success is-dismissible">
		<p><?php esc_html_e( 'Status bijgewerkt.', 'ftb-donation-form' ); ?></p>
	</div>
	<?php endif; ?>

	<form method="post">
		<input type="hidden" name="page" value="ftb-submissions">
		<?php if ( ! empty( $_GET['status'] ) ) : // phpcs:ignore WordPress.Security.NonceVerification.Recommended ?>
			<input type="hidden" name="status" value="<?php echo esc_attr( sanitize_text_field( wp_unslash( $_GET['status'] ) ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- read-only view filter, no data is modified ?>">
		<?php endif; ?>
		<?php $list_table->views(); ?>
		<?php $list_table->search_box( __( 'Zoeken', 'ftb-donation-form' ), 'ftb-donations-search' ); ?>
		<?php $list_table->display(); ?>
	</form>
