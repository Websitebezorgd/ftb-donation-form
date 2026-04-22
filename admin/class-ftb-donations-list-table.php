<?php
/**
 * WP_List_Table subclass for displaying donation submissions.
 *
 * @since      1.0.0
 * @package    FTB_Donation_Form
 * @subpackage FTB_Donation_Form/admin
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if ( ! class_exists( 'WP_List_Table' ) ) {
    require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

class FTB_Donations_List_Table extends WP_List_Table {

    private $db;

    public function __construct() {
        parent::__construct( [
            'singular' => 'donatie',
            'plural'   => 'donaties',
            'ajax'     => false,
        ] );
        $this->db = new FTB_DB();
    }

    public function get_columns() {
        return [
            'cb'             => '<input type="checkbox" />',
            'donor_name'     => __( 'Naam', 'ftb-donation-form' ),
            'donor_email'    => __( 'E-mailadres', 'ftb-donation-form' ),
            'donor_phone'    => __( 'Telefoonnummer', 'ftb-donation-form' ),
            'donor_address'  => __( 'Adres', 'ftb-donation-form' ),
            'amount'         => __( 'Bedrag', 'ftb-donation-form' ),
            'frequency'      => __( 'Frequentie', 'ftb-donation-form' ),
            'payment_status' => __( 'Status', 'ftb-donation-form' ),
            'created_at'     => __( 'Datum', 'ftb-donation-form' ),
        ];
    }

    protected function get_sortable_columns() {
        return [
            'donor_name'     => [ 'donor_name', false ],
            'amount'         => [ 'amount', false ],
            'payment_status' => [ 'payment_status', false ],
            'created_at'     => [ 'created_at', true ],
        ];
    }

    protected function column_cb( $item ) {
        return sprintf( '<input type="checkbox" name="donation[]" value="%d" />', absint( $item->id ) );
    }

    protected function get_bulk_actions() {
        return [
            'delete' => __( 'Verwijderen', 'ftb-donation-form' ),
        ];
    }

    protected function column_default( $item, $column_name ) {
        switch ( $column_name ) {
            case 'donor_name':
                return esc_html( $item->donor_name );
            case 'donor_email':
                return '<a href="mailto:' . esc_attr( $item->donor_email ) . '">' . esc_html( $item->donor_email ) . '</a>';
            case 'donor_phone':
                return $item->donor_phone ? esc_html( $item->donor_phone ) : '—';
            case 'donor_address':
                return $this->render_address( $item );
            case 'amount':
                return '&euro;' . number_format( (float) $item->amount, 2, ',', '.' );
            case 'frequency':
                $labels = [
                    'one_time' => __( 'Eenmalig', 'ftb-donation-form' ),
                    'monthly'  => __( 'Maandelijks', 'ftb-donation-form' ),
                    'yearly'   => __( 'Jaarlijks', 'ftb-donation-form' ),
                ];
                return esc_html( $labels[ $item->frequency ] ?? $item->frequency );
            case 'payment_status':
                return $this->render_status_badge( $item->payment_status );
            case 'created_at':
                $timestamp = strtotime( $item->created_at );
                return esc_html( wp_date( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), $timestamp ) );
            default:
                return esc_html( $item->$column_name ?? '' );
        }
    }

    private function render_address( $item ) {
        $street = trim( $item->donor_street . ' ' . $item->donor_house_number );
        $city   = trim( $item->donor_postal_code . ' ' . $item->donor_city );

        if ( ! $street && ! $city ) {
            return '—';
        }

        $parts = array_filter( [ $street, $city ] );
        return implode( '<br>', array_map( 'esc_html', $parts ) );
    }

    private function render_status_badge( $status ) {
        $labels = [
            'pending'   => __( 'In afwachting', 'ftb-donation-form' ),
            'paid'      => __( 'Betaald', 'ftb-donation-form' ),
            'failed'    => __( 'Mislukt', 'ftb-donation-form' ),
            'cancelled' => __( 'Geannuleerd', 'ftb-donation-form' ),
        ];
        $label = esc_html( $labels[ $status ] ?? $status );
        return '<span class="ftb-status ftb-status--' . esc_attr( $status ) . '">' . $label . '</span>';
    }

    public function prepare_items() {
        $per_page     = 20;
        $current_page = $this->get_pagenum();
        // phpcs:disable WordPress.Security.NonceVerification.Recommended
        $search  = isset( $_REQUEST['s'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['s'] ) ) : '';
        $status  = isset( $_REQUEST['status'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['status'] ) ) : '';
        $orderby = isset( $_REQUEST['orderby'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['orderby'] ) ) : 'created_at';
        $order   = isset( $_REQUEST['order'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['order'] ) ) : 'DESC';
        // phpcs:enable

        $args = [
            'per_page' => $per_page,
            'page'     => $current_page,
            'search'   => $search,
            'status'   => $status,
            'orderby'  => $orderby,
            'order'    => $order,
        ];

        $this->items         = $this->db->get_donations( $args );
        $total_items         = $this->db->count_donations( $args );

        $this->set_pagination_args( [
            'total_items' => $total_items,
            'per_page'    => $per_page,
            'total_pages' => (int) ceil( $total_items / $per_page ),
        ] );

        $this->_column_headers = [
            $this->get_columns(),
            [],
            $this->get_sortable_columns(),
        ];
    }

    protected function get_views() {
        // phpcs:ignore WordPress.Security.NonceVerification.Recommended
        $current  = isset( $_REQUEST['status'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['status'] ) ) : '';
        $base_url = admin_url( 'admin.php?page=ftb-submissions' );

        $all_count = $this->db->count_donations();
        $statuses  = [
            'pending'   => __( 'In afwachting', 'ftb-donation-form' ),
            'paid'      => __( 'Betaald', 'ftb-donation-form' ),
            'failed'    => __( 'Mislukt', 'ftb-donation-form' ),
            'cancelled' => __( 'Geannuleerd', 'ftb-donation-form' ),
        ];

        $views          = [];
        $views['all']   = sprintf(
            '<a href="%s"%s>%s <span class="count">(%d)</span></a>',
            esc_url( $base_url ),
            '' === $current ? ' class="current"' : '',
            __( 'Alle', 'ftb-donation-form' ),
            $all_count
        );

        foreach ( $statuses as $slug => $label ) {
            $count = $this->db->count_donations( [ 'status' => $slug ] );
            if ( 0 === $count ) {
                continue;
            }
            $views[ $slug ] = sprintf(
                '<a href="%s"%s>%s <span class="count">(%d)</span></a>',
                esc_url( add_query_arg( 'status', $slug, $base_url ) ),
                $current === $slug ? ' class="current"' : '',
                esc_html( $label ),
                $count
            );
        }

        return $views;
    }
}
