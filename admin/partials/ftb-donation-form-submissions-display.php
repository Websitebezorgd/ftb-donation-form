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
<div class="wrap">

    <h1 class="wp-heading-inline">
        <?php esc_html_e( 'Donaties', 'ftb-donation-form' ); ?>
    </h1>
    <hr class="wp-header-end">

    <form method="get">
        <input type="hidden" name="page" value="ftb-submissions">
        <?php if ( ! empty( $_GET['status'] ) ) : // phpcs:ignore WordPress.Security.NonceVerification.Recommended ?>
            <input type="hidden" name="status" value="<?php echo esc_attr( sanitize_text_field( wp_unslash( $_GET['status'] ) ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- read-only view filter, no data is modified ?>">
        <?php endif; ?>
        <?php $list_table->views(); ?>
        <?php $list_table->search_box( __( 'Zoeken', 'ftb-donation-form' ), 'ftb-donations-search' ); ?>
        <?php $list_table->display(); ?>
    </form>

</div>
