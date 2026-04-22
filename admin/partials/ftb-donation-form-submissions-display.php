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
$list_table->process_bulk_action();
$list_table->prepare_items();
?>
<div class="wrap">

    <h1 class="wp-heading-inline">
        <?php esc_html_e( 'Donaties', 'ftb-donation-form' ); ?>
    </h1>
    <a href="<?php echo esc_url( wp_nonce_url( admin_url( 'admin.php?page=ftb-submissions&action=export_csv' ), 'ftb_export_csv' ) ); ?>" class="page-title-action">
        <?php esc_html_e( 'Exporteer CSV', 'ftb-donation-form' ); ?>
    </a>
    <hr class="wp-header-end">

    <?php
    // phpcs:ignore WordPress.Security.NonceVerification.Recommended
    $deleted = isset( $_GET['deleted'] ) ? (int) $_GET['deleted'] : 0;
    if ( $deleted > 0 ) :
    ?>
    <div class="notice notice-success is-dismissible">
        <p>
        <?php
        printf(
            /* translators: %d: number of deleted donations */
            esc_html( _n( '%d donatie verwijderd.', '%d donaties verwijderd.', $deleted, 'ftb-donation-form' ) ),
            $deleted
        );
        ?>
        </p>
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

</div>
