<?php
/**
 * Purchase Handler for PayPal Standard Secure.
 *
 * @since   2.0.0
 * @license GPLv2
 */

/**
 * Class ITE_PayPal_Standard_Secure_Purchase_Handler
 */
class ITE_PayPal_Standard_Secure_Purchase_Handler extends ITE_POST_Redirect_Purchase_Request_Handler {

	/**
	 * @inheritDoc
	 */
	protected function get_vars_to_post( ITE_Gateway_Purchase_Request $request ) {
		return array(
			'cmd'       => '_s-xclick',
			'encrypted' => $this->generate_encrypted_id( $request ),
		);
	}

	/**
	 * @inheritDoc
	 */
	public function get_payment_button_label() {

		if ( $this->get_gateway()->settings()->has( 'purchase-button-label' ) ) {
			return $this->get_gateway()->settings()->get( 'purchase-button-label' );
		}

		return parent::get_payment_button_label();
	}

	/**
	 * @inheritDoc
	 */
	public function get_redirect_url( ITE_Gateway_Purchase_Request $request ) {
		return $this->get_gateway()->is_sandbox_mode() ? PAYPAL_PAYMENT_SANDBOX_URL : PAYPAL_PAYMENT_LIVE_URL;
	}

	/**
	 * Generate an encrypted PayPal button.
	 *
	 * @since 2.0.0
	 *
	 * @param ITE_Gateway_Purchase_Request $request
	 *
	 * @return string|false
	 */
	protected function generate_encrypted_id( ITE_Gateway_Purchase_Request $request ) {

		$paypal_settings = $this->get_gateway()->settings()->all();
		$is_sandbox      = $this->get_gateway()->is_sandbox_mode();

		$paypal_email = $is_sandbox ? $paypal_settings['sandbox-email-address'] : $paypal_settings['live-email-address'];

		$api_username  = $is_sandbox ? $paypal_settings['sandbox-api-username'] : $paypal_settings['live-api-username'];
		$api_password  = $is_sandbox ? $paypal_settings['sandbox-api-password'] : $paypal_settings['live-api-password'];
		$api_signature = $is_sandbox ? $paypal_settings['sandbox-api-signature'] : $paypal_settings['live-api-signature'];
		$api_url       = $is_sandbox ? PAYPAL_NVP_API_SANDBOX_URL : PAYPAL_NVP_API_LIVE_URL;

		if ( empty( $paypal_email ) || empty( $api_username ) || empty( $api_password ) || empty( $api_signature ) ) {
			it_exchange_log( 'No PayPal Secure credentials provided.', ITE_Log_Levels::ALERT, array(
				'_group' => 'gateway'
			) );

			return false;
		}

		remove_filter( 'the_title', 'wptexturize' );

		$cart = $request->get_cart();

		$button_request = array(
			'USER'        => trim( $api_username ),
			'PWD'         => trim( $api_password ),
			'SIGNATURE'   => trim( $api_signature ),
			'VERSION'     => '96.0', //The PayPal API version
			'METHOD'      => 'BMCreateButton',
			'BUTTONCODE'  => 'ENCRYPTED',
			'BUTTONIMAGE' => 'REG',
			'BUYNOWTEXT'  => 'PAYNOW',
			'bn'          => 'iThemes_SP',
		);

		if ( $button_vars = $this->get_subscription_button_vars( $request ) ) {
			$button_request['BUTTONTYPE'] = 'SUBSCRIBE';
		} else {
			$button_request['BUTTONTYPE'] = 'BUYNOW';

			$button_vars['amount']   = number_format( it_exchange_get_cart_total( false, array( 'cart' => $cart ) ), 2, '.', '' );
			$button_vars['quantity'] = 1;
		}

		$return_args = array(
			'it-exchange-transaction-method'  => 'paypal-standard-secure',
			'_wpnonce'                        => $this->get_nonce(),
			'auto_return'                     => true,
			'paypal-standard-secure_purchase' => 1,
		);

		if ( ! $cart->is_current() ) {
			$return_args['cart_id']   = $cart->get_id();
			$return_args['cart_auth'] = $cart->generate_auth_secret();
		}

		if ( $request->get_redirect_to() ) {
			$return_args['redirect_to'] = $request->get_redirect_to();
		}

		$return_url = add_query_arg( $return_args, it_exchange_get_page_url( 'transaction' ) );

		$button_vars['return']        = $return_url;
		$button_vars['business']      = $paypal_email;
		$button_vars['item_name']     = it_exchange_get_cart_description( array( 'cart' => $cart ) );
		$button_vars['currency_code'] = $cart->get_currency_code();
		$button_vars['notify_url']    = it_exchange_get_webhook_url( 'paypal-standard-secure' );
		$button_vars['no_note']       = 1;
		$button_vars['shipping']      = 0;
		$button_vars['email']         = ( $cart->get_customer() ? $cart->get_customer()->get_email() : '' );

		// https://developer.paypal.com/webapps/developer/docs/classic/button-manager/integration-guide/ButtonManagerHTMLVariables/
		$button_vars['rm']            = 2;
		$button_vars['cancel_return'] = it_exchange_get_page_url( 'cart' );

		$custom = "v2|{$cart->get_id()}|";
		$custom .= ( $request->get_child_of() ? $request->get_child_of()->get_ID() : 0 );

		$button_vars['custom'] = $custom;

		/**
		 * Filter the Button Vars that are passed to PayPal.
		 *
		 * @since 2.0.0
		 *
		 * @param array                        $button_vars
		 * @param ITE_Gateway_Purchase_Request $request
		 */
		$button_vars = apply_filters( 'it_exchange_paypal_standard_secure_get_button_vars', $button_vars, $request );

		$L_VARS = array();

		foreach ( $button_vars as $var => $value ) {
			$L_VARS[] = "{$var}={$value}";
		}

		/**
		 * Filter the Button Vars that are passed to PayPal.
		 *
		 * @deprecated 2.0.0
		 *
		 * @param array $L_VARS
		 */
		$L_VARS = apply_filters_deprecated(
			'it_exchange_paypal_standard_secure_button_vars', array( $L_VARS ), '2.0.0',
			'it_exchange_paypal_standard_secure_get_button_vars'
		);

		$count = 0;

		foreach ( $L_VARS as $L_VAR ) {
			$button_request[ 'L_BUTTONVAR' . $count ] = $L_VAR;
			$count ++;
		}

		/**
		 * Filter the full button request that is passed to PayPal.
		 *
		 * @since 2.0.0 Added the `$request` parameter.
		 *
		 * @param array                        $button_request
		 * @param ITE_Gateway_Purchase_Request $request
		 */
		$button_request = apply_filters( 'it_exchange_paypal_standard_secure_button_request', $button_request, $request );

		add_filter( 'the_title', 'wptexturize' );

		$response = wp_remote_post( $api_url, array( 'body' => $button_request, 'httpversion' => '1.1' ) );

		if ( is_wp_error( $response ) ) {
			$cart->get_feedback()->add_error( $response->get_error_message() );
			it_exchange_log( 'Network error while encrypting a PayPal Secure button: {error}', ITE_Log_Levels::WARNING, array(
				'error'  => $response->get_error_message(),
				'_group' => 'gateway',
			) );

			return false;
		}

		parse_str( wp_remote_retrieve_body( $response ), $response_array );

		if ( empty( $response_array['ACK'] ) || ! 'Success' === $response_array['ACK'] || empty( $response_array['WEBSITECODE'] ) ) {
			$cart->get_feedback()->add_error( __( 'Unable to make a request to PayPal', 'it-l10n-ithemes-exchange' ) );

			it_exchange_log( 'PayPal Secure NVP credentials error', ITE_Log_Levels::ALERT, array(
				'_group' => 'gateway',
			) );

			return false;
		}

		$payment_form = stripslashes( $response_array['WEBSITECODE'] );

		if ( preg_match( '/-----BEGIN PKCS7-----.*-----END PKCS7-----/i', $payment_form, $matches ) ) {

			$cart->mark_as_purchased();

			return trim( $matches[0] );
		}

		it_exchange_log( 'PayPal Secure encrypted button is invalid.', ITE_Log_Levels::ALERT, array(
			'_group' => 'gateway',
		) );

		return false;
	}

	/**
	 * Get the subscription button request to pass to PayPal.
	 *
	 * @since 2.0.0
	 *
	 * @param ITE_Gateway_Purchase_Request $request
	 *
	 * @return array
	 */
	protected function get_subscription_button_vars( ITE_Gateway_Purchase_Request $request ) {

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

		$duration = apply_filters( 'it_exchange_paypal_standard_secure_addon_subscription_duration', $interval_count, $bc );

		$trial_unit = null;
		$t_duration = null;

		if ( $request instanceof ITE_Gateway_Prorate_Purchase_Request && ( $prorates = $request->get_prorate_requests() ) ) {
			if ( isset( $prorates[ $product->ID ] ) && $prorates[ $product->ID ]->get_credit_type() === 'days' ) {
				$t_duration = $prorates[ $product->ID ]->get_free_days();
			}
		}

		if ( $trial_enabled && ! $t_duration && function_exists( 'it_exchange_is_customer_eligible_for_trial' ) ) {
			$allow_trial = it_exchange_is_customer_eligible_for_trial( $product, $cart->get_customer() );
			$allow_trial = apply_filters( 'it_exchange_paypal_standard_secure_addon_get_payment_url_allow_trial', $allow_trial, $product->ID );

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

				$t_duration = apply_filters( 'it_exchange_paypal_standard_secure_addon_subscription_trial_duration', $t_interval_count, $bc );
			}
		}

		$button_vars = array();

		// https://developer.paypal.com/docs/classic/paypal-payments-standard/integration-guide/Appx_websitestandard_htmlvariables/
		//a1, t1, p1 are for the first trial periods which is not supported with the Recurring Payments add-on
		//a2, t2, p2 are for the second trial period, which is not supported with the Recurring Payments add-on
		//a3, t3, p3 are required for the actual subscription details
		$trial_duration_1 = $t_duration;
		$trial_duration_2 = 0;

		$trial_unit_1 = ( ! empty( $trial_unit ) ) ? $trial_unit : 'D'; // Days by default
		$trial_unit_2 = 'D';

		if ( 90 < $trial_duration_1 ) { //If greater than 90 days, we need to modify
			$years            = floor( $trial_duration_1 / 365 );
			$years_remainder  = $trial_duration_1 % 365;
			$months           = floor( $trial_duration_1 / 30 );
			$months_remainder = $trial_duration_1 % 30;
			$weeks            = floor( $trial_duration_1 / 7 );
			$weeks_remainder  = $trial_duration_1 % 7;

			if ( 10 == $years ) { //the most we can do
				$trial_unit_1     = 'Y';
				$trial_duration_1 = 5;
				$trial_unit_2     = 'Y';
				$trial_duration_2 = 5;
			} else if ( ! empty( $years ) && 5 >= $years ) {
				$trial_unit_1     = 'Y';
				$trial_duration_1 = $years;
				if ( ! empty( $years_remainder ) ) {
					$trial_duration_2 = $years_remainder;
				}
			} else if ( ! empty( $months ) && 24 >= $months ) {
				$trial_unit_1     = 'M';
				$trial_duration_1 = $months;
				if ( ! empty( $months_remainder ) ) {
					$trial_duration_2 = $months_remainder;
				}
			} else if ( ! empty( $weeks ) && 52 >= $weeks ) {
				$trial_unit_1     = 'W';
				$trial_duration_1 = $weeks;
				if ( ! empty( $weeks_remainder ) ) {
					$trial_duration_2 = $weeks_remainder;
				}
			} else {
				$trial_duration_1 = 0;
				$trial_duration_2 = 0;
			}
		}

		if ( 90 < $trial_duration_2 ) { //If greater than 90 days, we need to modify
			$weeks  = floor( $trial_duration_2 / 7 );
			$months = floor( $trial_duration_2 / 30 );
			$years  = floor( $trial_duration_2 / 365 );

			if ( ! empty( $weeks ) && 52 >= $weeks ) {
				$trial_unit_2     = 'W';
				$trial_duration_2 = $weeks;
			} else if ( ! empty( $months ) && 24 >= $months ) {
				$trial_unit_2     = 'M';
				$trial_duration_2 = $months;
			} else if ( ! empty( $years ) && 5 >= $years ) {
				$trial_unit_2     = 'Y';
				$trial_duration_2 = $years;
			} else {
				$trial_duration_2 = 0;
			}
		}

		$total    = $cart->get_total();
		$one_time = $cart->get_items( 'fee', true )->filter( function ( ITE_Fee_Line_Item $fee ) { return ! $fee->is_recurring(); } );

		if ( $trial_duration_1 ) {
			$button_vars['a1'] = $total ? number_format( $total, 2, '.', '' ) : '0';
			$button_vars['p1'] = $trial_duration_1; //Trial period.
			$button_vars['t1'] = $trial_unit_1;
		} elseif ( $one_time->total() ) {
			$button_vars['a1'] = number_format( $total, 2, '.', '' );
			$button_vars['p1'] = $duration;
			$button_vars['t1'] = $unit;
		}

		if ( $trial_duration_2 ) {
			$button_vars['a2'] = '0.01'; //Free trial subscription price. (needs to be greater than 0)
			$button_vars['p2'] = $trial_duration_2; //Trial period.
			$button_vars['t2'] = $trial_unit_2;
		}

		// Remove any one time fees ( that should only be charged on first payment ) from the recurring amount
		$otf_total        = $one_time->total();
		$otf_sum          = $one_time->flatten()->summary_only()->total();
		$recurring_amount = $total - ( $otf_total + $otf_sum );

		// Regular subscription price.
		$button_vars['a3'] = number_format( $recurring_amount, 2, '.', '' );

		// Subscription duration. Specify an integer value in the allowable range for the units of duration that you specify with t3.
		$button_vars['p3'] = $duration;

		// Regular subscription units of duration. (D, W, M, Y) -- we only use M,Y by default
		$button_vars['t3'] = $unit;

		// Recurring payments.
		$button_vars['src'] = 1;

		return $button_vars;
	}

	/**
	 * @inheritDoc
	 *
	 * @param ITE_Gateway_Purchase_Request $request
	 *
	 * @throws \IT_Exchange_Locking_Exception
	 */
	public function handle( $request ) {

		$pdt    = $request->get_http_request();
		$custom = $lock = null;

		if ( ! empty( $pdt['cm'] ) ) {
			$custom = $pdt['cm'];
		} elseif ( ! empty( $pdt['custom'] ) ) {
			$custom = $pdt['custom'];
		}

		if ( $custom && strpos( $custom, 'v2|' ) !== 0 ) {
			$r = it_exchange_process_paypal_standard_secure_addon_transaction(
				false,
				it_exchange_generate_transaction_object( $request->get_cart() )
			);

			return $r ? it_exchange_get_transaction( $r ) : null;
		}

		$cart = $request->get_cart();
		$lock = "ppss-{$cart->get_id()}";

		if ( ! wp_verify_nonce( $request->get_nonce(), $this->get_nonce_action() ) ) {

			$error = __( 'Request expired. Please try again.', 'it-l10n-ithemes-exchange' );

			if ( $cart ) {
				$cart->get_feedback()->add_error( $error );
			} else {
				it_exchange_add_message( 'error', $error );
			}

			it_exchange_log( 'PayPal Secure payment nonce verification failed', ITE_Log_Levels::INFO, array(
				'_group' => 'gateway'
			) );

			return null;
		}

		try {

			it_exchange_log( 'PayPal Secure waiting for lock for {cart_id}', ITE_Log_Levels::DEBUG, array(
				'cart_id' => $cart->get_id(),
				'_group'  => 'gateway',
			) );

			$self        = $this;
			$transaction = it_exchange_wait_for_lock( $lock, 5, function () use ( $self, $request, $pdt ) {
				return $self->process_pdt( $request, $pdt );
			} );

			if ( $transaction ) {
				it_exchange_log( 'PayPal Secure payment for cart {cart_id} resulted in transaction {txn_id}', ITE_Log_Levels::INFO, array(
					'txn_id'  => $transaction,
					'cart_id' => $request->get_cart()->get_id(),
					'_group'  => 'gateway',
				) );

				return it_exchange_get_transaction( $transaction );
			}

			it_exchange_log( 'PayPal Secure payment for cart {cart_id} failed to create a transaction.', ITE_Log_Levels::WARNING, array(
				'cart_id' => $request->get_cart()->get_id(),
				'_group'  => 'gateway',
			) );

			return null;
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
	 * Process a PDT request.
	 *
	 * @since 2.0.0
	 *
	 * @param ITE_Gateway_Purchase_Request $request
	 * @param array                        $pdt
	 *
	 * @return false|int|IT_Exchange_Transaction|null
	 * @throws Exception
	 */
	public function process_pdt( ITE_Gateway_Purchase_Request $request, $pdt ) {

		it_exchange_log( 'PayPal Secure processing cart {cart_id} PDT: {pdt}', ITE_Log_Levels::DEBUG, array(
			'pdt'     => wp_json_encode( $pdt ),
			'cart_id' => $request->get_cart()->get_id(),
		) );

		$cart    = $request->get_cart();
		$cart_id = $cart->get_id();

		$paypal_id = $paypal_total = $paypal_status = $subscriber_id = null;

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

		$customer = $request->get_customer();

		if ( isset( $paypal_id, $paypal_total, $paypal_status, $cart_id, $cart ) ) {

			$response_array = $this->get_paypal_transaction_details( $paypal_id );

			it_exchange_set_paypal_standard_secure_addon_customer_id( $customer->id, $response_array['PAYERID'] );
			it_exchange_set_paypal_standard_secure_addon_customer_email( $customer->id, $response_array['EMAIL'] );

			$paypal_status = $response_array['PAYMENTSTATUS'];

			if ( $paypal_id !== $response_array['TRANSACTIONID'] ) {
				throw new Exception( sprintf(
					__( 'Error: Transaction IDs do not match! %s, %s', 'it-l10n-ithemes-exchange' ),
					$paypal_id,
					$response_array['TRANSACTIONID']
				) );
			}

			$cart_total = $cart->get_total( true );

			if ( number_format( $response_array['AMT'], '2', '', '' ) !== number_format( $cart_total, '2', '', '' ) ) {
				throw new Exception( __( 'Error: Amount charged is not the same as the cart total.', 'it-l10n-ithemes-exchange' ) );
			}

			if ( ! empty( $response_array['SUBSCRIPTIONID'] ) ) {
				$subscriber_id = $response_array['SUBSCRIPTIONID'];
			}

			if ( $transaction = it_exchange_get_transaction_by_method_id( 'paypal-standard-secure', $cart_id ) ) {
				$transaction->update_method_id( $paypal_id );

				return $transaction;
			}

			if ( $transaction = it_exchange_get_transaction_by_cart_id( $cart_id ) ) {
				return $transaction;
			}

			$txn_id = $this->add_transaction( $request, $paypal_id, $paypal_status );

			if ( $subscriber_id ) {
				it_exchange_paypal_standard_secure_addon_update_subscriber_id( $paypal_id, $subscriber_id );
			}

			return $txn_id;
		} else if ( $transaction = it_exchange_get_transaction_by_cart_id( $cart_id ) ) {
			return $transaction;
		} elseif ( 0.00 === $cart->get_total( true ) ) {
			// This occurs if we just made a free trial payment
			return $this->add_transaction( $request, md5( $cart_id ), 'Completed' );
		} else {
			return null;
		}
	}

	/**
	 * Add the transaction in Exchange.
	 *
	 * @since 2.0.0
	 *
	 * @param ITE_Gateway_Purchase_Request $request
	 * @param string                       $method_id
	 * @param string                       $status
	 * @param array                        $args
	 *
	 * @return int|false
	 */
	protected function add_transaction( ITE_Gateway_Purchase_Request $request, $method_id, $status, $args = array() ) {

		if ( $p = $request->get_child_of() ) {
			return it_exchange_add_child_transaction( 'paypal-standard-secure', $method_id, $status, $request->get_cart(), $p->get_ID(), $args );
		}

		return it_exchange_add_transaction( 'paypal-standard-secure', $method_id, $status, $request->get_cart(), null, $args );
	}

	/**
	 * Get the details of a transaction in PayPal.
	 *
	 * @since 2.0.0
	 *
	 * @param string $paypal_id
	 *
	 * @return array
	 *
	 * @throws \UnexpectedValueException
	 */
	protected function get_paypal_transaction_details( $paypal_id ) {

		$paypal_settings = $this->get_gateway()->settings()->all();

		$is_sandbox    = $this->get_gateway()->is_sandbox_mode();
		$api_url       = $is_sandbox ? PAYPAL_NVP_API_SANDBOX_URL : PAYPAL_NVP_API_LIVE_URL;
		$api_username  = $is_sandbox ? $paypal_settings['sandbox-api-username'] : $paypal_settings['live-api-username'];
		$api_password  = $is_sandbox ? $paypal_settings['sandbox-api-password'] : $paypal_settings['live-api-password'];
		$api_signature = $is_sandbox ? $paypal_settings['sandbox-api-signature'] : $paypal_settings['live-api-signature'];

		$get_transaction_details = array(
			'USER'          => trim( $api_username ),
			'PWD'           => trim( $api_password ),
			'SIGNATURE'     => trim( $api_signature ),
			'VERSION'       => '96.0', //The PayPal API version
			'METHOD'        => 'GetTransactionDetails',
			'TRANSACTIONID' => $paypal_id,
		);

		$response = wp_remote_post( $api_url, array(
			'body'        => $get_transaction_details,
			'httpversion' => '1.1'
		) );

		if ( is_wp_error( $response ) ) {
			throw new UnexpectedValueException( $response->get_error_message() );
		}

		parse_str( wp_remote_retrieve_body( $response ), $response_array );

		if ( ! isset( $response_array['PAYERID'] ) ) {
			it_exchange_log( 'Invalid PayPal Secure transaction details {paypal_id} response: {response}', array(
				'paypal_id' => $paypal_id,
				'response'  => wp_json_encode( $response ),
				'_group'    => 'gateway',
			) );

			throw new UnexpectedValueException( __( 'Invalid PayPal response. Please try again later.', 'it-l10n-ithemes-exchange' ) );
		}

		return $response_array;
	}

	/**
	 * @inheritDoc
	 */
	public function supports_feature( ITE_Optionally_Supported_Feature $feature ) {

		switch ( $feature->get_feature_slug() ) {
			case 'recurring-payments':
			case 'one-time-fee':
				return true;
		}

		return parent::supports_feature( $feature );
	}

	/**
	 * @inheritDoc
	 */
	public function supports_feature_and_detail( ITE_Optionally_Supported_Feature $feature, $slug, $detail ) {

		switch ( $feature->get_feature_slug() ) {
			case 'one-time-fee':
				switch ( $slug ) {
					case 'discount':
						return true;
					default:
						return false;
				}
			case 'recurring-payments':
				switch ( $slug ) {
					case 'profile':

						/** @var $detail IT_Exchange_Recurring_Profile */
						switch ( $detail->get_interval_type() ) {
							case IT_Exchange_Recurring_Profile::TYPE_DAY:
								return $detail->get_interval_count() <= 90;
							case IT_Exchange_Recurring_Profile::TYPE_WEEK:
								return $detail->get_interval_count() <= 52;
							case IT_Exchange_Recurring_Profile::TYPE_MONTH:
								return $detail->get_interval_count() <= 24;
							case IT_Exchange_Recurring_Profile::TYPE_YEAR:
								return $detail->get_interval_count() <= 5;
							default:
								return false;
						}
					case 'trial-profile':

						/** @var $detail IT_Exchange_Recurring_Profile */
						switch ( $detail->get_interval_type() ) {
							case IT_Exchange_Recurring_Profile::TYPE_DAY:
								return $detail->get_interval_count() <= 10 * 365;
							case IT_Exchange_Recurring_Profile::TYPE_WEEK:
								return $detail->get_interval_count() <= 52;
							case IT_Exchange_Recurring_Profile::TYPE_MONTH:
								return $detail->get_interval_count() <= 24;
							case IT_Exchange_Recurring_Profile::TYPE_YEAR:
								return $detail->get_interval_count() <= 5;
							default:
								return false;
						}

					case 'auto-renew':
					case 'trial':
					case 'max-occurrences':
						return true;
					default:
						return false;
				}
		}

		return parent::supports_feature( $feature );
	}
}
