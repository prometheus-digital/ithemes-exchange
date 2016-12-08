<?php
/**
 * Purchase Handler.
 *
 * @since   2.0.0
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
	}

	/**
	 * @inheritDoc
	 */
	public function render_payment_button( ITE_Gateway_Purchase_Request $request ) {

		if ( ! $this->get_gateway()->settings()->get( 'live-email-address' ) ) {
			return '';
		}

		return parent::render_payment_button( $request );
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

		$setting = $this->get_gateway()->settings()->get( 'sandbox-mode' ) ? 'test-email-address' : 'live-email-address';

		if ( ! ( $paypal_email = $this->get_gateway()->settings()->get( $setting ) ) ) {
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

		if ( $request->get_redirect_to() ) {
			$return_args['redirect_to'] = $request->get_redirect_to();
		}

		$return_url = add_query_arg( $return_args, it_exchange_get_page_url( 'transaction' ) );

		if ( $sub_args = $this->get_subscription_args( $request ) ) {
			$query = $sub_args;
		} else {
			$query = array(
				'cmd'      => '_xclick',
				'amount'   => number_format( it_exchange_get_cart_total( false, array( 'cart' => $cart ) ), 2, '.', '' ),
				'quantity' => 1,
			);
		}

		$query += array(
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
			'custom'        => 'v2|' . $cart->get_id(),
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

		$cart->mark_as_purchased();

		if ( $this->get_gateway()->is_sandbox_mode() ) {
			return PAYPAL_PAYMENT_SANDBOX_URL . '?' . http_build_query( $query );
		} else {
			return PAYPAL_PAYMENT_LIVE_URL . '?' . http_build_query( $query );
		}
	}

	/**
	 * Get the subscription args to pass to PayPal.
	 *
	 * @since 2.0.0
	 *
	 * @param ITE_Gateway_Purchase_Request $request
	 *
	 * @return array
	 */
	protected function get_subscription_args( ITE_Gateway_Purchase_Request $request ) {

		$cart = $request->get_cart();

		/** @var ITE_Cart_Product $cart_product */
		$cart_product = $cart->get_items( 'product' )->filter( function ( ITE_Cart_Product $product ) {
			return $product->get_product()->has_feature( 'recurring-payments', array( 'setting' => 'auto-renew' ) );
		} )->first();

		if ( ! $cart_product ) {
			return array();
		}

		$product = $cart_product->get_product();
		$bc      = $cart_product->bc();

		$interval       = $product->get_feature( 'recurring-payments', array( 'setting' => 'interval' ) );
		$interval_count = $product->get_feature( 'recurring-payments', array( 'setting' => 'interval-count' ) );

		$trial_enabled    = $product->get_feature( 'recurring-payments', array( 'setting' => 'trial-enabled' ) );
		$t_interval       = $product->get_feature( 'recurring-payments', array( 'setting' => 'trial-interval' ) );
		$t_interval_count = $product->get_feature( 'recurring-payments', array( 'setting' => 'trial-interval-count' ) );

		switch ( $interval ) {
			case 'year':
				$unit = 'Y';
				break;
			case 'week':
				$unit = 'W';
				break;
			case 'day':
				$unit = 'D';
				break;
			case 'month':
			default:
				$unit = 'M';
				break;

		}

		$duration = apply_filters( 'it_exchange_paypal_standard_addon_subscription_duration', $interval_count, $bc );

		$trial_unit = null;
		$t_duration = null;

		if ( $trial_enabled && function_exists( 'it_exchange_is_customer_eligible_for_trial' ) ) {
			$allow_trial = it_exchange_is_customer_eligible_for_trial( $product, $cart->get_customer() );
			$allow_trial = apply_filters( 'it_exchange_paypal_standard_addon_get_payment_url_allow_trial', $allow_trial, $product->ID );

			if ( $allow_trial && $t_interval_count > 0 ) {
				switch ( $t_interval ) {
					case 'year':
						$trial_unit = 'Y';
						break;
					case 'week':
						$trial_unit = 'W';
						break;
					case 'day':
						$trial_unit = 'D';
						break;
					case 'month':
					default:
						$trial_unit = 'M';
						break;
				}

				$t_duration = apply_filters( 'it_exchange_paypal_standard_addon_subscription_trial_duration', $t_interval_count, $bc );
			}
		}

		$total = $cart->get_total();
		$fee = $cart_product->get_line_items()->with_only( 'fee' )
		                    ->filter( function ( ITE_Fee_Line_Item $fee ) { return ! $fee->is_recurring(); } )
		                    ->first();

		if ( $fee ) {
			$total += $fee->get_total() * - 1;
		}

		// https://developer.paypal.com/docs/classic/paypal-payments-standard/integration-guide/Appx_websitestandard_htmlvariables/
		//a1, t1, p1 are for the first trial periods which is not supported with the Recurring Payments add-on
		//a2, t2, p2 are for the second trial period, which is not supported with the Recurring Payments add-on
		//a3, t3, p3 are required for the actual subscription details
		$args = array(
			'cmd' => '_xclick-subscriptions',
			'a3'  => number_format( $total, 2, '.', '' ),
			//Regular subscription price.
			'p3'  => $duration,
			//Subscription duration. Specify an int value in the allowed range for the duration units specified in t3
			't3'  => $unit,
			//Regular subscription units of duration. (D, W, M, Y) -- we only use M,Y by default
			'src' => 1,
			//Recurring payments.
		);

		if ( ! empty( $trial_unit ) && ! empty( $t_duration ) ) {
			$args['a1'] = 0;
			$args['p1'] = $t_duration;
			$args['t1'] = $trial_unit;
		}

		return $args;
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

		if ( ! empty( $pdt['cm'] ) ) {
			$cart_id = $pdt['cm'];
		} elseif ( ! empty( $pdt['custom'] ) ) {
			$cart_id = $pdt['custom'];
		}

		if ( $cart_id && strpos( $cart_id, 'v2|' ) !== 0 ) {
			$r = it_exchange_process_paypal_standard_addon_transaction(
				false,
				it_exchange_generate_transaction_object( $request->get_cart() )
			);

			return $r ? it_exchange_get_transaction( $r ) : null;
		}

		if ( ! empty( $pdt['tx'] ) ) { //if PDT is enabled
			$paypal_id = $pdt['tx'];
		} elseif ( ! empty( $pdt['txn_id'] ) ) { //if PDT is not enabled
			$paypal_id = $pdt['txn_id'];
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

			// Remove the v2| from the beginning
			$cart_id = substr( $cart_id, 3 );

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

				$txn_id = $this->add_transaction( $request, $paypal_id, $paypal_status );

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

				$txn_id = $this->add_transaction( $request, $cart_id, 'Completed' );

				it_exchange_release_lock( $lock );

				return it_exchange_get_transaction( $txn_id );
			} else {
				return null;
			}
		} catch ( IT_Exchange_Locking_Exception $e ) {
			throw $e;
		} catch ( Exception $e ) {

			if ( $cart ) {
				$cart->get_feedback()->add_error( $e->getMessage() );
			} else {
				it_exchange_add_message( 'error', $e->getMessage() );
			}

			return null;
		}
	}

	/**
	 * Add the transaction in Exchange.
	 *
	 * @since 2.0.0
	 *
	 * @param ITE_Gateway_Purchase_Request $request
	 * @param string                                 $method_id
	 * @param string                                 $status
	 * @param array                                  $args
	 *
	 * @return int|false
	 */
	protected function add_transaction( ITE_Gateway_Purchase_Request $request, $method_id, $status, $args = array() ) {

		if ( $p = $request->get_child_of() ) {
			return it_exchange_add_child_transaction( 'paypal-standard', $method_id, $status, $request->get_cart(), $p->get_ID(), $args );
		}

		return it_exchange_add_transaction( 'paypal-standard', $method_id, $status, $request->get_cart(), null, $args );
	}
}
