<?php
/**
 * Database access layer for donation records.
 *
 * @since      1.0.0
 * @package    FTB_Donation_Form
 * @subpackage FTB_Donation_Form/includes
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class FTB_DB {

    /** @var string Full table name including WP prefix */
    private $table;

    public function __construct() {
        global $wpdb;
        $this->table = $wpdb->prefix . 'ftb_donations';
    }

    /**
     * Insert a new donation record.
     *
     * @param array $data Donation data.
     * @return int|false Inserted row ID or false on failure.
     */
    public function insert_donation( array $data ) {
        global $wpdb;

        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery
        $result = $wpdb->insert(
            $this->table,
            [
                'donor_name'       => sanitize_text_field( $data['donor_name'] ),
                'donor_email'      => sanitize_email( $data['donor_email'] ),
                'donor_phone'      => sanitize_text_field( $data['donor_phone'] ?? '' ),
                'donor_street'     => sanitize_text_field( $data['donor_street'] ?? '' ),
                'donor_house_number' => sanitize_text_field( $data['donor_house_number'] ?? '' ),
                'donor_postal_code'  => sanitize_text_field( $data['donor_postal_code'] ?? '' ),
                'donor_city'       => sanitize_text_field( $data['donor_city'] ?? '' ),
                'amount'           => (float) $data['amount'],
                'frequency'        => sanitize_text_field( $data['frequency'] ),
                'mollie_payment_id' => sanitize_text_field( $data['mollie_payment_id'] ?? '' ),
                'payment_status'   => 'pending',
            ],
            [ '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%f', '%s', '%s', '%s' ]
        );

        return $result ? $wpdb->insert_id : false;
    }

    /**
     * Find a donation by its Mollie payment ID.
     *
     * @param string $mollie_id Mollie payment ID.
     * @return object|null
     */
    public function get_donation_by_mollie_id( string $mollie_id ) {
        global $wpdb;

        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
        return $wpdb->get_row(
            $wpdb->prepare(
                // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- table name from $wpdb->prefix, not user input
                "SELECT * FROM {$this->table} WHERE mollie_payment_id = %s LIMIT 1",
                $mollie_id
            )
        );
    }

    /**
     * Find a donation by its local ID.
     *
     * @param int $id Row ID.
     * @return object|null
     */
    public function get_donation( int $id ) {
        global $wpdb;

        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
        return $wpdb->get_row(
            $wpdb->prepare(
                // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- table name from $wpdb->prefix, not user input
                "SELECT * FROM {$this->table} WHERE id = %d LIMIT 1",
                $id
            )
        );
    }

    /**
     * Update the payment status of a donation.
     *
     * @param string $mollie_id Mollie payment ID.
     * @param string $status    New status (pending, paid, failed, cancelled).
     * @return int|false Number of rows updated or false on failure.
     */
    public function update_payment_status( string $mollie_id, string $status ) {
        global $wpdb;

        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
        return $wpdb->update(
            $this->table,
            [ 'payment_status' => sanitize_text_field( $status ) ],
            [ 'mollie_payment_id' => $mollie_id ],
            [ '%s' ],
            [ '%s' ]
        );
    }

    /**
     * Fetch a paginated, filtered list of donations.
     *
     * @param array $args {
     *   @type int    $per_page Number of results per page. Default 20.
     *   @type int    $page     Page number. Default 1.
     *   @type string $status   Filter by payment_status. Default '' (all).
     *   @type string $search   Search donor name or email. Default ''.
     *   @type string $orderby  Column to order by. Default 'created_at'.
     *   @type string $order    ASC or DESC. Default 'DESC'.
     * }
     * @return array
     */
    public function get_donations( array $args = [] ): array {
        global $wpdb;

        $args = wp_parse_args( $args, [
            'per_page' => 20,
            'page'     => 1,
            'status'   => '',
            'search'   => '',
            'orderby'  => 'created_at',
            'order'    => 'DESC',
        ] );

        $where  = 'WHERE 1=1';
        $values = [];

        if ( ! empty( $args['status'] ) ) {
            $where   .= ' AND payment_status = %s';
            $values[] = $args['status'];
        }

        if ( ! empty( $args['search'] ) ) {
            $where   .= ' AND (donor_name LIKE %s OR donor_email LIKE %s)';
            $like     = '%' . $wpdb->esc_like( $args['search'] ) . '%';
            $values[] = $like;
            $values[] = $like;
        }

        $allowed_orderby = [ 'created_at', 'amount', 'donor_name', 'payment_status' ];
        $orderby  = in_array( $args['orderby'], $allowed_orderby, true ) ? $args['orderby'] : 'created_at';
        $order    = strtoupper( $args['order'] ) === 'ASC' ? 'ASC' : 'DESC';
        $per_page = (int) $args['per_page'];
        $offset   = ( max( 1, (int) $args['page'] ) - 1 ) * $per_page;

        // Prepare WHERE clause separately so the outer query has exactly 2 static placeholders (LIMIT/OFFSET).
        if ( ! empty( $values ) ) {
            $where = $wpdb->prepare( $where, ...$values ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
        }

        // $where is now either static or already prepared; $orderby/$order are validated; table name from $wpdb->prefix.
        return $wpdb->get_results( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, PluginCheck.Security.DirectDB.UnescapedDBParameter
            $wpdb->prepare( "SELECT * FROM {$this->table} {$where} ORDER BY {$orderby} {$order} LIMIT %d OFFSET %d", $per_page, $offset ) // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQL.NotPrepared
        ) ?: [];
    }

    /**
     * Count donations matching the given filters (used for pagination).
     *
     * @param array $args Same filter keys as get_donations(), minus pagination.
     * @return int
     */
    public function count_donations( array $args = [] ): int {
        global $wpdb;

        $where  = 'WHERE 1=1';
        $values = [];

        if ( ! empty( $args['status'] ) ) {
            $where   .= ' AND payment_status = %s';
            $values[] = $args['status'];
        }

        if ( ! empty( $args['search'] ) ) {
            $where   .= ' AND (donor_name LIKE %s OR donor_email LIKE %s)';
            $like     = '%' . $wpdb->esc_like( $args['search'] ) . '%';
            $values[] = $like;
            $values[] = $like;
        }

        // Prepare WHERE clause separately so no intermediate $sql variable is needed.
        if ( ! empty( $values ) ) {
            $where = $wpdb->prepare( $where, ...$values ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
        }

        // $where is now either static or already prepared; table name from $wpdb->prefix.
        return (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$this->table} {$where}" ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, PluginCheck.Security.DirectDB.UnescapedDBParameter
    }
}
