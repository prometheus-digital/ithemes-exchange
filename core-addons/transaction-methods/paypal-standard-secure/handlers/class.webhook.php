<?php
/**
 * PayPal Standard Secure webhook handler.
 *
 * @since   1.36.0
 * @license GPLv2
 */

/**
 * Class ITE_PayPal_Standard_Secure_Webhook_Handler
 */
class ITE_PayPal_Standard_Secure_Webhook_Handler implements ITE_Gateway_Request_Handler {

	/**
	 * @inheritDoc
	 *
	 * @param ITE_Webhook_Gateway_Request $request
	 */
	public function handle( $request ) {

		if ( ! static::can_handle( $request::get_name() ) ) {
			throw new InvalidArgumentException( sprintf( 'Handler cannot process %s requests.', $request::get_name() ) );
		}

		$webhook = $request->get_webhook_data();

		if ( ! empty( $webhook['custom'] ) ) {
			$cart_id = $webhook['custom'];
		} else if ( ! empty( $webhook['transaction_subject'] ) ) {
			$cart_id = $webhook['transaction_subject'];
		} else {
			$cart_id = false;
		}

		$cart = $lock = null;

		if ( $cart_id ) {

			if ( strpos( $cart_id, 'v2|' ) !== 0 ) {
				it_exchange_paypal_standard_secure_addon_process_webhook( $webhook );

				return new WP_HTTP_Response( '', 200 );
			}

			// Remove the v2| from the beginning
			$cart_id = substr( $cart_id, 3 );

			$cart = it_exchange_get_cart( $cart_id );
			$lock = "ppss-$cart_id";
			it_exchange_lock( $lock, 2 );
		}

		if ( ! $this->validate_payload( $webhook ) ) {
			return new WP_HTTP_Response( '', 400 );
		}

		$subscriber_id = ! empty( $webhook['subscr_id'] ) ? $webhook['subscr_id'] : false;
		$subscriber_id = ! empty( $webhook['recurring_payment_id'] ) ? $webhook['recurring_payment_id'] : $subscriber_id;

		if ( ! empty( $webhook['txn_type'] ) ) {

			if ( 'web_accept' === $webhook['txn_type'] ) {

				if ( $transaction = it_exchange_get_transaction_by_cart_id( $cart_id ) ) {

					switch ( strtolower( $webhook['payment_status'] ) ) {
						case 'completed':
							$transaction->update_status( $webhook['payment_status'] );
							break;
						case 'reversed':
							$transaction->update_status( $webhook['reason_code'] );
							break;
					}

					return new WP_HTTP_Response( '', 200 );
				}

				if ( ! $cart ) {
					return new WP_HTTP_Response( '', 500 );
				}

				$method_id = $webhook['txn_id'];
				$status    = $webhook['payment_status'];

				$txn_id = it_exchange_add_transaction( 'paypal-standard-secure', $method_id, $status, $cart );

				it_exchange_release_lock( $txn_id );

				return new WP_HTTP_Response( '', 200 );
			}

			if ( $cart && ! it_exchange_get_transaction_by_cart_id( $cart_id ) ) {
				if ( 'subscr_signup' === $webhook['txn_type'] ) {
					$status    = 'Completed';
					$method_id = $cart_id;
				} elseif ( ! empty( $webhook['payment_status'] ) ) {
					$status    = $webhook['payment_status'];
					$method_id = $webhook['txn_id'];
				}

				if ( isset( $status, $method_id ) ) {
					it_exchange_add_transaction( 'paypal-standard-secure', $method_id, $status, $cart );
				}
			}

			switch ( $webhook['txn_type'] ) {
				case 'subscr_payment':

					if ( $webhook['payment_status'] === 'Completed' ) {

						// if we can still retrieve the transaction by its temporary transaction ID ( cart ID )
						// then this payment is a free trial being converted to a full subscription
						if ( $transaction = it_exchange_get_transaction_by_method_id( 'paypal-standard-secure', $cart_id ) ) {
							$transaction->update_method_id( md5( $cart_id ) );
						}

						// attempt to update the payment status for a transaction
						if ( ! it_exchange_paypal_standard_secure_addon_update_transaction_status( $webhook['txn_id'], $webhook['payment_status'] ) ) {
							//If the transaction isn't found, we've got a new payment
							$GLOBALS['it_exchange']['child_transaction'] = true;
							it_exchange_paypal_standard_secure_addon_add_child_transaction( $webhook['txn_id'], $webhook['payment_status'], $subscriber_id, $webhook['mc_gross'] );
						} else {
							//If it is found, make sure the subscriber ID is attached to it
							it_exchange_paypal_standard_secure_addon_update_subscriber_id( $webhook['txn_id'], $subscriber_id );
						}

						// if we have a good payment, make sure to keep the subscription status as active
						it_exchange_paypal_standard_secure_addon_update_subscriber_status( $subscriber_id, 'active' );
					}

					break;
				case 'subscr_signup':
					/* We need to do some free trial magic! */
					if ( it_exchange_paypal_standard_secure_addon_get_ite_transaction_id( $webhook['custom'] ) ) {
						it_exchange_paypal_standard_secure_addon_update_subscriber_id( $webhook['custom'], $subscriber_id );
						it_exchange_paypal_standard_secure_addon_update_transaction_status( $webhook['custom'], 'Completed' );
					} elseif ( ! empty( $webhook['txn_id'] ) && it_exchange_paypal_standard_secure_addon_get_ite_transaction_id( $webhook['txn_id'] ) ) {
						it_exchange_paypal_standard_secure_addon_update_subscriber_id( $webhook['txn_id'], $subscriber_id );
					}

					it_exchange_paypal_standard_secure_addon_update_subscriber_status( $subscriber_id, 'active' );
					break;
				case 'recurring_payment_suspended':
					it_exchange_paypal_standard_secure_addon_update_subscriber_status( $subscriber_id, 'suspended' );
					break;

				case 'subscr_cancel':
					it_exchange_paypal_standard_secure_addon_update_subscriber_status( $subscriber_id, 'cancelled' );
					break;

				case 'subscr_eot':
					it_exchange_paypal_standard_secure_addon_update_subscriber_status( $subscriber_id, 'deactivated' );
					break;
			}

			if ( $lock ) {
				it_exchange_release_lock( $lock );
			}
		} else {

			//These IPNs don't have txn_types, why PayPal!? WHY!?
			if ( ! empty( $webhook['reason_code'] ) && $webhook['reason_code'] === 'refund' ) {

				$refund_id   = $webhook['txn_id'];
				$transaction = it_exchange_get_transaction_by_method_id( 'paypal-standard-secure', $webhook['parent_txn_id'] );

				if ( ! $transaction ) {
					return new WP_HTTP_Response( '', 200 );
				}

				it_exchange_lock( "paypal-secure-refund-created-{$transaction->ID}", 2 );

				$transaction->update_status( $webhook['payment_status'] );

				$existing = ITE_Refund::query()
					->and_where( 'gateway_id', '=', $refund_id )
					->and_where('transaction', '=', $transaction->ID )
					->first();

				if ( ! $refund_id || ! $existing ) {
					it_exchange_paypal_standard_secure_addon_add_refund_to_transaction( $webhook['parent_txn_id'], $webhook['mc_gross'] );
				}

				if ( $subscriber_id ) {
					it_exchange_paypal_standard_secure_addon_update_subscriber_status( $subscriber_id, 'cancelled' );
				}

				it_exchange_release_lock( "paypal-secure-refund-created-{$transaction->ID}" );
			}
		}


		return new WP_HTTP_Response( '', 200 );
	}

	/**
	 * Validate the request payload.
	 *
	 * @since 1.36.0
	 *
	 * @param array $webhook
	 *
	 * @return bool
	 */
	protected function validate_payload( $webhook ) {

		$payload = array( 'cmd' => '_notify-validate' );

		foreach ( $webhook as $key => $value ) {
			$payload[ $key ] = stripslashes( $value );
		}

		$paypal_api_url = ! empty( $webhook['test_ipn'] ) ? PAYPAL_PAYMENT_SANDBOX_URL : PAYPAL_PAYMENT_LIVE_URL;
		$response       = wp_remote_post( $paypal_api_url, array( 'body' => $payload, 'httpversion' => '1.1' ) );
		$body           = wp_remote_retrieve_body( $response );

		if ( 'VERIFIED' !== $body ) {

			error_log( sprintf( __( 'Invalid IPN sent from PayPal - PayLoad: %s', 'it-l10n-ithemes-exchange' ), maybe_serialize( $payload ) ) );
			error_log( sprintf( __( 'Invalid IPN sent from PayPal - Response: %s', 'it-l10n-ithemes-exchange' ), maybe_serialize( $response ) ) );

			return false;
		}

		return true;
	}

	/**
	 * @inheritDoc
	 */
	public static function can_handle( $request_name ) { return $request_name === 'webhook'; }
}