<?php
/**
 * Edit payment status page template.
 *
 * @since      1.0.0
 * @package    FTB_Donation_Form
 * @subpackage FTB_Donation_Form/admin
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// phpcs:ignore WordPress.Security.NonceVerification.Recommended
$id = isset( $_GET['id'] ) ? absint( $_GET['id'] ) : 0;

$db       = new FTB_DB();
$donation = $db->get_donation( $id );

if ( ! $donation ) {
    wp_die( esc_html__( 'Donatie niet gevonden.', 'ftb-donation-form' ) );
}

$statuses = [
    'pending'   => __( 'In afwachting', 'ftb-donation-form' ),
    'paid'      => __( 'Betaald', 'ftb-donation-form' ),
    'failed'    => __( 'Mislukt', 'ftb-donation-form' ),
    'cancelled' => __( 'Geannuleerd', 'ftb-donation-form' ),
];
?>
    <p>
        <a href="<?php echo esc_url( admin_url( 'admin.php?page=ftb-submissions' ) ); ?>">
            &larr; <?php esc_html_e( 'Terug naar donaties', 'ftb-donation-form' ); ?>
        </a>
    </p>

    <table class="form-table" role="presentation">
        <tr>
            <th scope="row"><?php esc_html_e( 'Naam', 'ftb-donation-form' ); ?></th>
            <td><?php echo esc_html( $donation->donor_name ); ?></td>
        </tr>
        <tr>
            <th scope="row"><?php esc_html_e( 'E-mailadres', 'ftb-donation-form' ); ?></th>
            <td><?php echo esc_html( $donation->donor_email ); ?></td>
        </tr>
        <tr>
            <th scope="row"><?php esc_html_e( 'Bedrag', 'ftb-donation-form' ); ?></th>
            <td>&euro;<?php echo esc_html( number_format( $donation->amount / 100, 2, ',', '.' ) ); ?></td>
        </tr>
        <tr>
            <th scope="row"><?php esc_html_e( 'Huidige status', 'ftb-donation-form' ); ?></th>
            <td><?php echo esc_html( $statuses[ $donation->payment_status ] ?? $donation->payment_status ); ?></td>
        </tr>
    </table>

    <form method="post" action="<?php echo esc_url( admin_url( 'admin.php' ) ); ?>">
        <input type="hidden" name="page" value="ftb-submissions">
        <input type="hidden" name="action" value="update_status">
        <input type="hidden" name="id" value="<?php echo absint( $donation->id ); ?>">
        <?php wp_nonce_field( 'ftb_update_status_' . $donation->id ); ?>

        <table class="form-table" role="presentation">
            <tr>
                <th scope="row">
                    <label for="payment_status"><?php esc_html_e( 'Nieuwe status', 'ftb-donation-form' ); ?></label>
                </th>
                <td>
                    <select id="payment_status" name="payment_status">
                        <?php foreach ( $statuses as $slug => $label ) : ?>
                        <option value="<?php echo esc_attr( $slug ); ?>" <?php selected( $donation->payment_status, $slug ); ?>>
                            <?php echo esc_html( $label ); ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </td>
            </tr>
        </table>

        <?php submit_button( __( 'Status opslaan', 'ftb-donation-form' ) ); ?>
    </form>
