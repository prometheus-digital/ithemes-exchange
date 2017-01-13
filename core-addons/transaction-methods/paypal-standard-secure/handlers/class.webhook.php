<?php
/**
 * PayPal Standard Secure webhook handler.
 *
 * @since   2.0.0
 * @license GPLv2
 */

/**
 * Class ITE_PayPal_Standard_Secure_Webhook_Handler
 */
class ITE_PayPal_Standard_Secure_Webhook_Handler implements ITE_Gateway_Request_Handler {

	const METHOD = 'paypal-standard-secure';

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
			$custom = $webhook['custom'];
		} else if ( ! empty( $webhook['transaction_subject'] ) ) {
			$custom = $webhook['transaction_subject'];
		} else {
			$custom = false;
		}

		$cart = $lock = $parent = null;

		if ( $custom ) {

			if ( strpos( $custom, 'v2|' ) !== 0 ) {
				it_exchange_paypal_standard_secure_addon_process_webhook( $webhook );

				return new WP_HTTP_Response( '', 200 );
			}

			// Custom is in a format of 'v2|cart_id|parentID'
			list( , $cart_id, $parent ) = explode( '|', $custom );

			$cart = it_exchange_get_cart( $cart_id );
			$lock = "ppss-$cart_id";
		}

		if ( ! $this->validate_payload( $webhook ) ) {
			return new WP_HTTP_Response( '', 400 );
		}

		if ( $lock ) {
			$self = $this;
			$code = it_exchange_wait_for_lock( $lock, 5, function () use ( $self, $webhook, $cart, $parent ) {
				return $self->process( $webhook, $cart, $parent );
			} );
		} else {
			$code = $this->process( $webhook, $cart, $parent );
		}

		return new WP_REST_Response( null, $code );
	}

	/**
	 * Process a webhook.
	 *
	 * @since 2.0.0
	 *
	 * @param array         $webhook
	 * @param ITE_Cart|null $cart
	 * @param int           $parent
	 *
	 * @return int
	 * @throws \InvalidArgumentException
	 */
	public function process( $webhook, ITE_Cart $cart = null, $parent = 0 ) {

		$cart_id       = $cart ? $cart->get_id() : '';
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

					return 200;
				}

				if ( ! $cart ) {
					return 500;
				}

				$method_id = $webhook['txn_id'];
				$status    = $webhook['payment_status'];

				$this->add_transaction( $cart, $method_id, $status, $parent );

				return 200;
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
					$this->add_transaction( $cart, $method_id, $status, $parent );

					return 200;
				}
			}

			switch ( $webhook['txn_type'] ) {
				case 'subscr_payment':

					if ( $webhook['payment_status'] === 'Completed' ) {

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

					$free_trial_id = md5( $cart_id );

					if ( it_exchange_paypal_standard_secure_addon_get_ite_transaction_id( $free_trial_id ) ) {
						it_exchange_paypal_standard_secure_addon_update_subscriber_id( $free_trial_id, $subscriber_id );
						it_exchange_paypal_standard_secure_addon_update_transaction_status( $free_trial_id, 'Completed' );
					} elseif ( ! empty( $webhook['txn_id'] ) && it_exchange_paypal_standard_secure_addon_get_ite_transaction_id( $webhook['txn_id'] ) ) {
						it_exchange_paypal_standard_secure_addon_update_subscriber_id( $webhook['txn_id'], $subscriber_id );
					}

					it_exchange_paypal_standard_secure_addon_update_subscriber_status( $subscriber_id, 'active' );
					break;
				case 'recurring_payment_suspended':
					it_exchange_paypal_standard_secure_addon_update_subscriber_status( $subscriber_id, 'suspended' );
					break;

				case 'subscr_cancel':

					$subscription = it_exchange_get_subscription_by_subscriber_id( self::METHOD, $subscriber_id );

					if ( ! $subscription || $subscription->is_status( $subscription::STATUS_CANCELLED ) ) {
						break;
					}

					if ( $subscription->are_occurrences_limited() && $subscription->get_remaining_occurrences() === 0 ) {
						break;
					}

					it_exchange_lock( "ppss-cancel-subscription-{$subscriber_id}", 2 );
					$subscription->set_status( $subscription::STATUS_CANCELLED );
					it_exchange_release_lock( "ppss-cancel-subscription-{$subscriber_id}" );
					break;

				case 'subscr_eot':
					it_exchange_paypal_standard_secure_addon_update_subscriber_status( $subscriber_id, 'deactivated' );
					break;
			}
		} else {

			//These IPNs don't have txn_types, why PayPal!? WHY!?
			if ( ! empty( $webhook['reason_code'] ) && $webhook['reason_code'] === 'refund' ) {

				$refund_id   = $webhook['txn_id'];
				$transaction = it_exchange_get_transaction_by_method_id( self::METHOD, $webhook['parent_txn_id'] );

				if ( ! $transaction ) {
					return 200;
				}

				it_exchange_lock( "paypal-secure-refund-created-{$transaction->ID}", 2 );

				$transaction->update_status( $webhook['payment_status'] );

				it_exchange_paypal_standard_secure_addon_add_refund_to_transaction( $webhook['parent_txn_id'], $webhook['mc_gross'], $refund_id );

				if ( $subscriber_id && $transaction->get_total() ) {
					it_exchange_paypal_standard_secure_addon_update_subscriber_status( $subscriber_id, 'cancelled' );
				}

				it_exchange_release_lock( "paypal-secure-refund-created-{$transaction->ID}" );
			}
		}

		return 200;
	}

	/**
	 * Add the transaction in Exchange.
	 *
	 * @since 2.0.0
	 *
	 * @param ITE_Cart $cart
	 * @param string   $method_id
	 * @param string   $status
	 * @param int      $parent
	 * @param array    $args
	 *
	 * @return int|false
	 */
	protected function add_transaction( ITE_Cart $cart, $method_id, $status, $parent, $args = array() ) {

		if ( $parent ) {
			return it_exchange_add_child_transaction( self::METHOD, $method_id, $status, $cart, $parent, $args );
		}

		return it_exchange_add_transaction( self::METHOD, $method_id, $status, $cart, null, $args );
	}

	/**
	 * Validate the request payload.
	 *
	 * @since 2.0.0
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
