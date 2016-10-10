<?php
/**
 * PayPal Standard Secure Refund Request Handler.
 *
 * @since   1.36.0
 * @license GPLv2
 */

/**
 * Class ITE_PayPal_Standard_Secure_Refund_Request_Handler
 */
class ITE_PayPal_Standard_Secure_Refund_Request_Handler implements ITE_Gateway_Request_Handler {

	/** @var ITE_Gateway */
	private $gateway;

	/**
	 * ITE_PayPal_Standard_Secure_Refund_Request_Handler constructor.
	 *
	 * @param \ITE_Gateway $gateway
	 */
	public function __construct( \ITE_Gateway $gateway ) { $this->gateway = $gateway; }

	/**
	 * @inheritDoc
	 *
	 * @param ITE_Gateway_Refund_Request $request
	 *
	 * @throws \UnexpectedValueException
	 */
	public function handle( $request ) {

		$transaction = $request->get_transaction();
		$method_id   = $transaction->get_method_id();
		$amount      = $request->get_amount();
		$is_full     = $amount >= $transaction->get_total();

		$general_settings = it_exchange_get_option( 'settings_general' );

		$paypal_settings = $this->get_gateway()->settings()->all();
		$is_sandbox      = $this->get_gateway()->is_sandbox_mode();

		$paypal_email = $is_sandbox ? $paypal_settings['sandbox-email-address'] : $paypal_settings['live-email-address'];

		$api_username  = $is_sandbox ? $paypal_settings['sandbox-api-username'] : $paypal_settings['live-api-username'];
		$api_password  = $is_sandbox ? $paypal_settings['sandbox-api-password'] : $paypal_settings['live-api-password'];
		$api_signature = $is_sandbox ? $paypal_settings['sandbox-api-signature'] : $paypal_settings['live-api-signature'];
		$api_url       = $is_sandbox ? PAYPAL_NVP_API_SANDBOX_URL : PAYPAL_NVP_API_LIVE_URL;

		if ( empty( $paypal_email ) || empty( $api_username ) || empty( $api_password ) || empty( $api_signature ) ) {
			throw new UnexpectedValueException( __( 'PayPal API Credentials not set.', 'it-l10n-ithemes-exchange' ) );
		}

		$paypal_request = array(
			'USER'          => trim( $api_username ),
			'PWD'           => trim( $api_password ),
			'SIGNATURE'     => trim( $api_signature ),
			'VERSION'       => '204.0',
			'METHOD'        => 'RefundTransaction',
			'TRANSACTIONID' => $method_id,
			'CURRENCYCODE'  => $general_settings['default-currency'],
		);

		if ( $is_full ) {
			$paypal_request['REFUNDTYPE'] = 'Full';
		} else {
			$paypal_request['REFUNDTYPE'] = 'Partial';
			$paypal_request['AMT']        = number_format( $amount, 2, '.', '' );
		}

		if ( $request->get_reason() ) {
			$paypal_request['NOTE'] = $request->get_reason();
		}

		// Stripe sends webhooks insanely quick. Make sure we create the refund before the webhook handler does.
		it_exchange_lock( "paypal-secure-refund-created-{$transaction->ID}", 2 );

		$response = wp_remote_post( $api_url, array( 'body' => $paypal_request, 'httpversion' => '1.1' ) );

		if ( is_wp_error( $response ) ) {
			it_exchange_release_lock( "paypal-secure-refund-created-{$transaction->ID}" );

			throw new UnexpectedValueException( $response->get_error_message() );
		}

		parse_str( wp_remote_retrieve_body( $response ), $response_array );

		if ( empty( $response_array['REFUNDTRANSACTIONID'] ) ) {
			it_exchange_release_lock( "paypal-secure-refund-created-{$transaction->ID}" );

			throw new UnexpectedValueException( __( 'Unable to create refund in PayPal.', 'it-l10n-ithemes-exchange' ) );
		}

		$refund = ITE_Refund::create( array(
			'transaction' => $transaction,
			'amount'      => $request->get_amount(),
			'gateway_id'  => $response_array['REFUNDTRANSACTIONID'],
			'reason'      => $request->get_reason(),
			'issued_by'   => $request->issued_by(),
		) );

		it_exchange_release_lock( "paypal-secure-refund-created-{$transaction->ID}" );

		return $refund;
	}

	/**
	 * Get the gateway.
	 *
	 * @since 1.36.0
	 *
	 * @return \ITE_Gateway
	 */
	public function get_gateway() {
		return $this->gateway;
	}

	/**
	 * @inheritDoc
	 */
	public static function can_handle( $request_name ) { return $request_name === 'refund'; }
}