<?php
/**
 * Cancellation Request Handler.
 *
 * @since   2.0.0
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
			it_exchange_log( 'No PayPal Secure credentials provided.', ITE_Log_Levels::ALERT, array(
				'_group' => 'subscription'
			) );

			return false;
		}

		$body = array(
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
		$lock = "ppss-cancel-subscription-{$subscription->get_subscriber_id()}";
		it_exchange_lock( $lock, 2 );
		it_exchange_log( 'Acquiring PayPal Secure cancel subscription #{sub_id} lock for transaction #{txn_id}', ITE_Log_Levels::DEBUG, array(
			'txn_id' => $subscription->get_transaction()->get_ID(),
			'sub_id' => $subscription->get_subscriber_id(),
			'_group' => 'subscription',
		) );

		$response = wp_remote_post( $paypal_api_url, array( 'body' => $body ) );

		if ( is_wp_error( $response ) ) {
			it_exchange_log( 'Network error while cancelling PayPal Secure subscription: {error}', ITE_Log_Levels::WARNING, array(
				'_group' => 'subscription',
				'error'  => $response->get_error_message()
			) );

			return false;
		}

		parse_str( wp_remote_retrieve_body( $response ), $response_array );

		if ( ! empty( $response_array['ACK'] ) && $response_array['ACK'] === 'Success' ) {

			if ( $request->should_set_status() ) {
				$subscription->set_status( IT_Exchange_Subscription::STATUS_CANCELLED );
			}

			if ( $request->get_cancelled_by() ) {
				$subscription->set_cancelled_by( $request->get_cancelled_by() );
			}

			if ( $request->get_reason() ) {
				$subscription->set_cancellation_reason( $request->get_reason() );
			}

			it_exchange_release_lock( $lock );
			it_exchange_log( 'Cancelled PayPal Secure subscription #{sub_id} for transaction {txn_id}.', ITE_Log_Levels::INFO, array(
				'sub_id' => $subscription->get_subscriber_id(),
				'txn_id' => $subscription->get_transaction()->get_ID(),
				'_group' => 'subscription',
			) );

			return true;
		}

		it_exchange_release_lock( $lock );
		it_exchange_log( 'Failed to cancel PayPal Secure subscription #{sub_id} for transaction {txn_id}: {response}', array(
			'sub_id'   => $subscription->get_subscriber_id(),
			'txn_id'   => $subscription->get_transaction()->get_ID(),
			'response' => wp_json_encode( $response_array ),
			'_group'   => 'subscription',
		) );

		return false;
	}

	/**
	 * @inheritDoc
	 */
	public static function can_handle( $request_name ) { return $request_name === 'cancel-subscription'; }
}
