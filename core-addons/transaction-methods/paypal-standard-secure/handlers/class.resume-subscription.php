<?php
/**
 * Resume Request Handler.
 *
 * @since   2.0.0
 * @license GPLv2
 */

/**
 * Class ITE_PayPal_Standard_Secure_Resume_Subscription_Handler
 */
class ITE_PayPal_Standard_Secure_Resume_Subscription_Handler implements ITE_Gateway_Request_Handler {

	/**
	 * @inheritDoc
	 *
	 * @param ITE_Resume_Subscription_Request $request
	 */
	public function handle( $request ) {

		$subscription = $request->get_subscription();
		$transaction  = $subscription->get_transaction();

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

		$body = array(
			'USER'      => trim( $paypal_api_username ),
			'PWD'       => trim( $paypal_api_password ),
			'SIGNATURE' => trim( $paypal_api_signature ),
			'VERSION'   => '96.0', //The PayPal API version
			'METHOD'    => 'ManageRecurringPaymentsProfileStatus',
			'PROFILEID' => $subscription->get_subscriber_id(),
			'ACTION'    => 'Reactivate',
		);

		$response = wp_remote_post( $paypal_api_url, array( 'body' => $body ) );

		if ( ! is_wp_error( $response ) ) {

			parse_str( wp_remote_retrieve_body( $response ), $response_array );

			if ( ! empty( $response_array['ACK'] ) && $response_array['ACK'] === 'Success' ) {

				$subscription->set_resumed_by( $request->get_resumed_by() );

				return true;
			}
		}

		return false;
	}

	/**
	 * @inheritDoc
	 */
	public static function can_handle( $request_name ) { return $request_name === 'resume-subscription'; }
}
