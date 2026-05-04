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
    public function create_payment(
        int $donation_id,
        float $amount,
        string $donor_name,
        string $redirect_url,
        string $webhook_url
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

        if ( ! empty( $webhook_url ) ) {
            $params['webhookUrl'] = $webhook_url;
        }

        return $this->mollie->payments->create( $params );
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
