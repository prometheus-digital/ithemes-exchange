<?php
/**
 * Cancellation Request Handler.
 *
 * @since   1.36.0
 * @license GPLv2
 */

/**
 * Class ITE_PayPal_Standard_Secure_Cancel_Subscription_Handler
 */
class ITE_PayPal_Standard_Secure_Cancel_Subscription_Handler implements ITE_Gateway_Request_Handler {

	/**
	 * @inheritDoc
	 *
	 * @param ITE_Cancel_Subscription_Request $request
	 */
	public function handle( $request ) {

		$subscription = $request->get_subscription();

		if ( ! $subscription->get_subscriber_id() ) {
			return false;
		}

		$paypal_settings = it_exchange_get_option( 'addon_paypal_standard_secure' );

		if ( $transaction->is_sandbox_purchase() ) {
			$use_sandbox = true;
		} elseif ( $transaction->is_live_purchase() ) {
			$use_sandbox = false;
		} else {
			$use_sandbox = $paypal_settings['sandbox-mode'];
		}

		$paypal_api_url = $use_sandbox ? PAYPAL_NVP_API_SANDBOX_URL : PAYPAL_NVP_API_LIVE_URL;

		$paypal_api_username  = $use_sandbox ? $paypal_settings['sandbox-api-username'] : $paypal_settings['live-api-username'];
		$paypal_api_password  = $use_sandbox ? $paypal_settings['sandbox-api-password'] : $paypal_settings['live-api-password'];
		$paypal_api_signature = $use_sandbox ? $paypal_settings['sandbox-api-signature'] : $paypal_settings['live-api-signature'];

		if ( ! $paypal_api_username || ! $paypal_api_password || ! $paypal_api_signature ) {
			return false;
		}

		$request = array(
			'USER'      => trim( $paypal_api_username ),
			'PWD'       => trim( $paypal_api_password ),
			'SIGNATURE' => trim( $paypal_api_signature ),
			'VERSION'   => '96.0', //The PayPal API version
			'METHOD'    => 'ManageRecurringPaymentsProfileStatus',
			'PROFILEID' => $subscription->get_subscriber_id(),
			'ACTION'    => 'CANCEL',
			'NOTE'      => $request->get_reason(),
		);

		// Make sure we update the subscription before the webhook handler does.
		it_exchange_lock( "ppss-cancel-subscription-{$subscription->get_subscriber_id()}", 2 );

		$response = wp_remote_post( $paypal_api_url, array( 'body' => $request ) );

		if ( ! is_wp_error( $response ) ) {

			parse_str( wp_remote_retrieve_body( $response ), $response_array );

			if ( ! empty( $response_array['PROFILEID'] ) ) {
				$subscription->set_status( IT_Exchange_Subscription::STATUS_CANCELLED );

				if ( $request->get_cancelled_by() ) {
					$subscription->set_cancelled_by( $request->get_cancelled_by() );
				}

				if ( $request->get_reason() ) {
					$subscription->set_cancellation_reason( $request->get_reason() );
				}

				it_exchange_release_lock( "ppss-cancel-subscription-{$subscription->get_subscriber_id()}" );

				return true;
			}
		}

		return false;
	}

	/**
	 * @inheritDoc
	 */
	public static function can_handle( $request_name ) { return $request_name === 'cancel-subscription'; }
}
