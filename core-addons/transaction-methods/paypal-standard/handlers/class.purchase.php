<?php
/**
 * Purchase Handler.
 *
 * @since   1.36.0
 * @license GPLv2
 */

/**
 * Class ITE_PayPal_Standard_Purchase_Handler
 */
class ITE_PayPal_Standard_Purchase_Handler extends ITE_Redirect_Purchase_Request_Handler {

	/**
	 * @inheritDoc
	 */
	public function __construct( \ITE_Gateway $gateway, \ITE_Gateway_Request_Factory $factory ) {
		parent::__construct( $gateway, $factory );

		//add_action();
	}

	/**
	 * @inheritDoc
	 */
	public function render_payment_button() {

		if ( ! $this->get_gateway()->settings()->get( 'live-email-address' ) ) {
			return '';
		}

		return parent::render_payment_button();
	}

	/**
	 * @inheritDoc
	 */
	public function get_redirect_url( ITE_Gateway_Purchase_Request $request ) {

		$cart = $request->get_cart();

		if ( ! wp_verify_nonce( $request->get_nonce(), $this->get_nonce_action() ) ) {
			$cart->get_feedback()->add_error( __( 'Request expired. Please try again.', 'it-l10n-ithemes-exchange' ) );

			return null;
		}

		if ( ! ( $paypal_email = $this->get_gateway()->settings()->get( 'live-email-address' ) ) ) {
			$cart->get_feedback()->add_error( __( 'Invalid PayPal setup.', 'it-l10n-ithemes-exchange' ) );

			return null;
		}

		remove_filter( 'the_title', 'wptexturize' );

		$general_settings = it_exchange_get_option( 'settings_general' );

		$return_args = array(
			'it-exchange-transaction-method' => 'paypal-standard',
			'_wpnonce'                       => $this->get_nonce(),
			'auto_return'                    => true,
			'paypal-standard_purchase'       => 1,
		);

		if ( ! $cart->is_current() ) {
			$return_args['cart_id']   = $cart->get_id();
			$return_args['cart_auth'] = $cart->generate_auth_secret();
		}

		$return_url = add_query_arg( $return_args, it_exchange_get_page_url( 'transaction' ) );

		$query = array(
			'cmd'           => '_xclick',
			'amount'        => number_format( it_exchange_get_cart_total( false, array( 'cart' => $cart ) ), 2, '.', '' ),
			'quantity'      => 1,
			'business'      => $paypal_email,
			'item_name'     => strip_tags( it_exchange_get_cart_description( array( 'cart' => $cart ) ) ),
			'currency_code' => $general_settings['default-currency'],
			'return'        => $return_url,
			'notify_url'    => it_exchange_get_webhook_url( $this->get_gateway()->get_webhook_param() ),
			'no_note'       => 1,
			'shipping'      => 0,
			'email'         => $cart->get_customer() ? $cart->get_customer()->get_email() : '',
			'rm'            => 2,
			'cancel_return' => it_exchange_get_page_url( 'cart' ),
			'custom'        => $cart->get_id(),
			'bn'            => 'iThemes_SP',
		);

		if ( $shipping = $cart->get_shipping_address() ) {
			$query['address_override'] = '1';
			$query['no_shipping']      = '2';

			$query['first_name'] = ! empty( $shipping['first-name'] ) ? $shipping['first-name'] : '';
			$query['last_name']  = ! empty( $shipping['last-name'] ) ? $shipping['last-name'] : '';
			$query['address1']   = ! empty( $shipping['address1'] ) ? $shipping['address1'] : '';
			$query['address2']   = ! empty( $shipping['address2'] ) ? $shipping['address2'] : '';
			$query['city']       = ! empty( $shipping['city'] ) ? $shipping['city'] : '';
			$query['state']      = ! empty( $shipping['state'] ) ? $shipping['state'] : '';
			$query['zip']        = ! empty( $shipping['zip'] ) ? $shipping['zip'] : '';
			$query['country']    = ! empty( $shipping['country'] ) ? $shipping['country'] : '';
		}

		$query = apply_filters( 'it_exchange_paypal_standard_query', $query, $cart );

		add_filter( 'the_title', 'wptexturize' );

		if ( $this->get_gateway()->is_sandbox_mode() ) {
			return PAYPAL_PAYMENT_SANDBOX_URL . '?' . http_build_query( $query );
		} else {
			return PAYPAL_PAYMENT_LIVE_URL . '?' . http_build_query( $query );
		}
	}

	/**
	 * @inheritDoc
	 *
	 * @param ITE_Gateway_Purchase_Request $request
	 *
	 * @throws \IT_Exchange_Locking_Exception
	 */
	public function handle( $request ) {

		$pdt = $request->get_http_request();

		$paypal_id = $cart_id = $cart = $paypal_total = $paypal_status = $lock = null;

		if ( ! empty( $pdt['tx'] ) ) { //if PDT is enabled
			$paypal_id = $pdt['tx'];
		} elseif ( ! empty( $pdt['txn_id'] ) ) { //if PDT is not enabled
			$paypal_id = $pdt['txn_id'];
		}

		if ( ! empty( $pdt['cm'] ) ) {
			$cart_id = $pdt['cm'];
		} elseif ( ! empty( $pdt['custom'] ) ) {
			$cart_id = $pdt['custom'];
		}

		if ( ! empty( $pdt['amt'] ) ) { //if PDT is enabled
			$paypal_total = $pdt['amt'];
		} elseif ( ! empty( $pdt['mc_gross'] ) ) { //if PDT is not enabled
			$paypal_total = $pdt['mc_gross'];
		}

		if ( ! empty( $pdt['st'] ) ) { //if PDT is enabled
			$paypal_status = $pdt['st'];
		} elseif ( ! empty( $pdt['payment_status'] ) ) { //if PDT is not enabled
			$paypal_status = $pdt['payment_status'];
		}

		if ( $cart_id ) {
			$lock = "pps-$cart_id";
			$cart = it_exchange_get_cart( $cart_id );
		}

		if ( ! wp_verify_nonce( $request->get_nonce(), $this->get_nonce_action() ) ) {

			$error = __( 'Request expired. Please try again.', 'it-l10n-ithemes-exchange' );

			if ( $cart ) {
				$cart->get_feedback()->add_error( $error );
			} else {
				it_exchange_add_message( 'error', $error );
			}

			return null;
		}

		try {

			if ( isset( $paypal_id, $cart_id, $cart, $paypal_total, $paypal_status ) ) {

				it_exchange_lock( $lock, 2 );

				$cart_total = it_exchange_get_cart_total( false, array( 'cart' => $cart ) );

				if ( number_format( $paypal_total, 2, '', '' ) !== number_format( $cart_total, 2, '', '' ) ) {
					throw new Exception( __( 'Error: Amount charged is not the same as the cart total.', 'it-l10n-ithemes-exchange' ) );
				}

				if ( $transaction = it_exchange_get_transaction_by_method_id( 'paypal-standard', $cart_id ) ) {
					$transaction->update_method_id( $paypal_id );

					it_exchange_release_lock( $lock );

					return $transaction;
				}

				if ( $transaction = it_exchange_get_transaction_by_cart_id( $cart_id ) ) {
					it_exchange_release_lock( $lock );

					return $transaction;
				}

				$txn_id = it_exchange_add_transaction( 'paypal-standard', $paypal_id, $paypal_status, $cart );

				it_exchange_release_lock( $lock );

				return it_exchange_get_transaction( $txn_id );
			} elseif ( null === $paypal_id && null === $cart_id && null === $cart && null === $paypal_total && null === $paypal_status ) {

				$cart_id = it_exchange_get_session_data( 'pps_transient_transaction_id' );
				$cart_id = $cart_id[0];
				it_exchange_clear_session_data( 'pps_transient_transaction_id' );

				$lock = "pps-$cart_id";
				it_exchange_lock( $lock, 2 );

				$cart = it_exchange_get_cart( $cart_id );

				if ( ! $cart ) {
					throw new Exception( __( 'Unable to retrieve cart.', 'it-l10n-ithemes-exchange' ) );
				}

				if ( $transaction = it_exchange_get_transaction_by_cart_id( $cart_id ) ) {
					it_exchange_release_lock( $lock );

					return $transaction;
				}

				$txn_id = it_exchange_add_transaction( 'paypal-standard', $cart_id, 'Completed', $cart );

				it_exchange_release_lock( $lock );

				return it_exchange_get_transaction( $txn_id );
			} else {
				return null;
			}
		}
		catch ( IT_Exchange_Locking_Exception $e ) {
			throw $e;
		}
		catch ( Exception $e ) {

			if ( $cart ) {
				$cart->get_feedback()->add_error( $e->getMessage() );
			} else {
				it_exchange_add_message( 'error', $e->getMessage() );
			}

			return null;
		}
	}
}