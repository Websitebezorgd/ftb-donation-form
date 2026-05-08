<?php
if ( ! defined( 'ABSPATH' ) ) exit;

use Mollie\Api\MollieApiClient;
use Mollie\Api\Resources\Payment;
use Mollie\Api\Exceptions\ApiException;

/**
 * Thin wrapper around the Mollie PHP SDK.
 *
 * Reads the API key from WordPress options so callers don't need to
 * know where it is stored.
 *
 * @since      1.0.0
 * @package    FTB_Donation_Form
 * @subpackage FTB_Donation_Form/includes
 */
class FTB_Mollie_Service {

    private MollieApiClient $mollie;

    /**
     * @throws ApiException if the API key is empty or invalid.
     */
    public function __construct() {
        $api_key = (string) get_option( 'ftb_mollie_api_key', '' );

        $this->mollie = new MollieApiClient();
        $this->mollie->setApiKey( $api_key );
    }

    /**
     * Create a new Mollie payment and return the payment object.
     *
     * The checkout URL is available via $payment->getCheckoutUrl().
     * The Mollie payment ID is available via $payment->id.
     *
     * @param int    $donation_id  Local DB row ID — stored in Mollie metadata.
     * @param float  $amount       Amount in euros (e.g. 10.00).
     * @param string $donor_name   Shown in the payment description.
     * @param string $redirect_url Where Mollie sends the donor after payment.
     * @param string $webhook_url  Where Mollie sends status-change notifications.
     * @return Payment
     * @throws ApiException
     */
    /**
     * Create a Mollie customer.
     *
     * Required before creating a recurring (first) payment so Mollie can store
     * the mandate and attach a subscription later.
     *
     * @param string $name  Donor full name.
     * @param string $email Donor email address.
     * @return object Mollie Customer resource.
     * @throws ApiException
     */
    public function create_customer( string $name, string $email ): object {
        return $this->mollie->customers->create( [
            'name'  => $name,
            'email' => $email,
        ] );
    }

    /**
     * Create a new Mollie payment and return the payment object.
     *
     * The checkout URL is available via $payment->getCheckoutUrl().
     * The Mollie payment ID is available via $payment->id.
     *
     * @param int    $donation_id   Local DB row ID — stored in Mollie metadata.
     * @param float  $amount        Amount in euros (e.g. 10.00).
     * @param string $donor_name    Shown in the payment description.
     * @param string $redirect_url  Where Mollie sends the donor after payment.
     * @param string $webhook_url   Where Mollie sends status-change notifications.
     * @param string $sequence_type 'oneoff' (default), 'first', or 'recurring'.
     * @param string $customer_id   Mollie customer ID — required for recurring payments.
     * @return Payment
     * @throws ApiException
     */
    public function create_payment(
        int $donation_id,
        float $amount,
        string $donor_name,
        string $redirect_url,
        string $webhook_url,
        string $sequence_type = 'oneoff',
        string $customer_id = ''
    ): Payment {
        $params = [
            'amount'      => [
                'currency' => 'EUR',
                'value'    => number_format( $amount, 2, '.', '' ),
            ],
            /* translators: %s: donor full name */
            'description' => sprintf( __( 'Donatie van %s', 'ftb-donation-form' ), $donor_name ),
            'redirectUrl' => $redirect_url,
            'metadata'    => [
                'donation_id' => $donation_id,
            ],
        ];

        if ( 'oneoff' !== $sequence_type ) {
            $params['sequenceType'] = $sequence_type;
        }

        if ( ! empty( $customer_id ) ) {
            $params['customerId'] = $customer_id;
        }

        if ( ! empty( $webhook_url ) ) {
            $params['webhookUrl'] = $webhook_url;
        }

        return $this->mollie->payments->create( $params );
    }

    /**
     * Create a Mollie subscription on an existing customer.
     *
     * Called after the first recurring payment is confirmed as paid. Mollie will
     * then charge the donor automatically on each interval and fire the webhook.
     *
     * @param string $customer_id Mollie customer ID.
     * @param float  $amount      Amount in euros per interval.
     * @param string $interval    Billing interval, e.g. '1 month' or '1 year'.
     * @param string $start_date  ISO 8601 date (Y-m-d) for the first subscription charge.
     * @param string $description Shown on the donor's bank statement.
     * @param string $webhook_url Where Mollie sends notifications for each charge.
     * @return object Mollie Subscription resource.
     * @throws ApiException
     */
    public function create_subscription(
        string $customer_id,
        float $amount,
        string $interval,
        string $start_date,
        string $description,
        string $webhook_url
    ): object {
        $params = [
            'amount' => [
                'currency' => 'EUR',
                'value'    => number_format( $amount, 2, '.', '' ),
            ],
            'interval'    => $interval,
            'startDate'   => $start_date,
            'description' => $description,
        ];

        if ( ! empty( $webhook_url ) ) {
            $params['webhookUrl'] = $webhook_url;
        }

        $customer = $this->mollie->customers->get( $customer_id );
        return $customer->createSubscription( $params );
    }

    /**
     * Fetch a payment from Mollie by its ID.
     *
     * Used by the webhook handler to verify and read the actual status.
     *
     * @param string $payment_id Mollie payment ID (e.g. tr_xxxxx).
     * @return Payment
     * @throws ApiException
     */
    public function get_payment( string $payment_id ): Payment {
        return $this->mollie->payments->get( $payment_id );
    }
}
