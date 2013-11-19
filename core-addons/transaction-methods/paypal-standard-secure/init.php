<?php
/**
 * Hooks for PayPal Standard (secure) add-on
 *
 * @package IT_Exchange
 * @since 0.2.0
*/

if ( !defined( 'PAYPAL_LIVE_URL' ) )
	define( 'PAYPAL_LIVE_URL', 'https://www.paypal.com/' );
if ( !defined( 'PAYPAL_SANDBOX_URL' ) )
	define( 'PAYPAL_SANDBOX_URL', 'https://www.sandbox.paypal.com/' );
if ( !defined( 'PAYPAL_PAYMENT_SANDBOX_URL' ) )

	define( 'PAYPAL_PAYMENT_SANDBOX_URL', 'https://www.sandbox.paypal.com/cgi-bin/webscr' );
if ( !defined( 'PAYPAL_PAYMENT_LIVE_URL' ) )
	define( 'PAYPAL_PAYMENT_LIVE_URL', 'https://www.paypal.com/cgi-bin/webscr' );
if ( !defined( 'PAYPAL_NVP_API_SANDBOX_URL' ) )
	define( 'PAYPAL_NVP_API_SANDBOX_URL', 'https://api-3t.sandbox.paypal.com/nvp' );
if ( !defined( 'PAYPAL_NVP_API_LIVE_URL' ) )
	define( 'PAYPAL_NVP_API_LIVE_URL', 'https://api-3t.paypal.com/nvp' );

/**
 * Outputs wizard settings for PayPal
 *
 * @since 0.4.0
 * @todo make this better, probably
 * @param object $form Current IT Form object
 * @return void
*/
function it_exchange_print_paypal_standard_secure_wizard_settings( $form ) {
	$IT_Exchange_paypal_standard_secure_Add_On = new IT_Exchange_paypal_standard_secure_Add_On();
	$settings = it_exchange_get_option( 'addon_paypal_standard_secure', true );
	$form_values = ITUtility::merge_defaults( ITForm::get_post_data(), $settings );

	// Alter setting keys for wizard
	foreach( $form_values as $key => $value ) {
		$form_values['paypal-standard-secure-' . $key] = $value;
		unset( $form_values[$key] );
	}

	$hide_if_js =  it_exchange_is_addon_enabled( 'paypal-standard-secure' ) ? '' : 'hide-if-js';
	?>
	<div class="field paypal-standard-secure-wizard <?php echo $hide_if_js; ?>">
	<?php if ( empty( $hide_if_js ) ) { ?>
        <input class="enable-paypal-standard-secure" type="hidden" name="it-exchange-transaction-methods[]" value="paypal-standard-secure" />
    <?php } ?>
	<?php $IT_Exchange_paypal_standard_secure_Add_On->get_paypal_standard_secure_payment_form_table( $form, $form_values ); ?>
	</div>
	<?php
}
add_action( 'it_exchange_print_paypal-standard-secure_wizard_settings', 'it_exchange_print_paypal_standard_secure_wizard_settings' );

/**
 * Stripe URL to perform refunds
 *
 * @since 0.4.0
 *
 * @param string $url passed by WP filter.
 * @param string $url transaction URL
*/
function it_exchange_refund_url_for_paypal_standard_secure( $url ) {

	return 'https://paypal.com/';

}
add_filter( 'it_exchange_refund_url_for_paypal-standard-secure', 'it_exchange_refund_url_for_paypal_standard_secure' );

/**
 * This proccesses a paypal transaction.
 *
 * @since 0.4.0
 *
 * @param string $status passed by WP filter.
 * @param object $transaction_object The transaction object
*/
function it_exchange_process_paypal_standard_secure_addon_transaction( $status, $transaction_object ) {

	if ( $status ) //if this has been modified as true already, return.
		return $status;

	if ( !empty( $_REQUEST['it-exchange-transaction-method'] ) && 'paypal-standard-secure' === $_REQUEST['it-exchange-transaction-method'] ) {

		if ( !empty( $_REQUEST['tx'] ) ) //if PDT is enabled
			$transaction_id = $_REQUEST['tx'];
		else if ( !empty( $_REQUEST['txn_id'] ) ) //if PDT is not enabled
			$transaction_id = $_REQUEST['txn_id'];
		else
			$transaction_id = NULL;

		if ( !empty( $_REQUEST['amt'] ) ) //if PDT is enabled
			$transaction_amount = $_REQUEST['amt'];
		else if ( !empty( $_REQUEST['mc_gross'] ) ) //if PDT is not enabled
			$transaction_amount = $_REQUEST['mc_gross'];
		else
			$transaction_amount = NULL;

		if ( !empty( $_REQUEST['st'] ) ) //if PDT is enabled
			$transaction_status = $_REQUEST['st'];
		else if ( !empty( $_REQUEST['payment_status'] ) ) //if PDT is not enabled
			$transaction_status = $_REQUEST['payment_status'];
		else
			$transaction_status = NULL;

		if ( !empty( $transaction_id ) && !empty( $transaction_amount ) && !empty( $transaction_status ) ) {

			try {

				$general_settings = it_exchange_get_option( 'settings_general' );
				$paypal_settings = it_exchange_get_option( 'addon_paypal_standard_secure' );

				$it_exchange_customer = it_exchange_get_current_customer();

				$paypal_api_url       = ( $paypal_settings['paypal-standard-secure-sandbox-mode'] ) ? PAYPAL_NVP_API_SANDBOX_URL : PAYPAL_NVP_API_LIVE_URL;
				$paypal_api_username  = ( $paypal_settings['paypal-standard-secure-sandbox-mode'] ) ? $paypal_settings['paypal-standard-secure-sandbox-api-username'] : $paypal_settings['paypal-standard-secure-live-api-username'];
				$paypal_api_password  = ( $paypal_settings['paypal-standard-secure-sandbox-mode'] ) ? $paypal_settings['paypal-standard-secure-sandbox-api-password'] : $paypal_settings['paypal-standard-secure-live-api-password'];
				$paypal_api_signature = ( $paypal_settings['paypal-standard-secure-sandbox-mode'] ) ? $paypal_settings['paypal-standard-secure-sandbox-api-signature'] : $paypal_settings['paypal-standard-secure-live-api-signature'];

				$request = array(
					'USER'          => trim( $paypal_api_username ),
					'PWD'           => trim( $paypal_api_password ),
					'SIGNATURE'     => trim( $paypal_api_signature ),
					'VERSION'       => '96.0', //The PayPal API version
					'METHOD'        => 'GetTransactionDetails',
					'TRANSACTIONID' => $transaction_id,
				);

				$response = wp_remote_post( $paypal_api_url, array( 'body' => $request ) );

				if ( !is_wp_error( $response ) ) {

					$array = array();
					parse_str( wp_remote_retrieve_body( $response ) );

					it_exchange_set_paypal_standard_secure_addon_customer_id( $it_exchange_customer->id, $PAYERID );
					it_exchange_set_paypal_standard_secure_addon_customer_email( $it_exchange_customer->id, $EMAIL );
					$transaction_status = $PAYMENTSTATUS;

					if ( $transaction_id != $TRANSACTIONID )
						throw new Exception( __( 'Error: Transaction IDs do not match! %s, %s', 'LION' ) );

					if ( number_format( $AMT, '2', '', '' ) != number_format( $transaction_object->total, '2', '', '' ) )
						throw new Exception( sprintf( __( 'Error: Amount charged is not the same as the cart total! %s | %s', 'LION' ), $AMT, $transaction_object->total ) );

				} else {

					throw new Exception( $response->get_error_message() );

				}

			}
			catch ( Exception $e ) {

				it_exchange_add_message( 'error', $e->getMessage() );
				return false;

			}

			return it_exchange_add_transaction( 'paypal-standard-secure', $transaction_id, $transaction_status, $it_exchange_customer->id, $transaction_object );

		}

		it_exchange_add_message( 'error', __( 'Unknown error while processing with PayPal. Please try again later.', 'LION' ) );

	}
	return false;

}
add_action( 'it_exchange_do_transaction_paypal-standard-secure', 'it_exchange_process_paypal_standard_secure_addon_transaction', 10, 2 );

/**
 * Grab the paypal customer ID for a WP user
 *
 * @since 0.4.0
 *
 * @param integer $customer_id the WP customer ID
 * @return string
*/
function it_exchange_get_paypal_standard_secure_addon_customer_id( $customer_id ) {
	return get_user_meta( $customer_id, '_it_exchange_paypal_standard_secure_id', true );
}

/**
 * Add the paypal customer email as user meta on a WP user
 *
 * @since 0.4.0
 *
 * @param integer $customer_id the WP user ID
 * @param integer $paypal_standard_secure_id the paypal customer ID
 * @return boolean
*/
function it_exchange_set_paypal_standard_secure_addon_customer_id( $customer_id, $paypal_standard_secure_id ) {
	return update_user_meta( $customer_id, '_it_exchange_paypal_standard_secure_id', $paypal_standard_secure_id );
}

/**
 * Grab the paypal customer email for a WP user
 *
 * @since 0.4.0
 *
 * @param integer $customer_id the WP customer ID
 * @return string
*/
function it_exchange_get_paypal_standard_secure_addon_customer_email( $customer_id ) {
	return get_user_meta( $customer_id, '_it_exchange_paypal_standard_secure_email', true );
}

/**
 * Add the paypal customer email as user meta on a WP user
 *
 * @since 0.4.0
 *
 * @param integer $customer_id the WP user ID
 * @param string $paypal_standard_secure_email the paypal customer email
 * @return boolean
*/
function it_exchange_set_paypal_standard_secure_addon_customer_email( $customer_id, $paypal_standard_secure_email ) {
	return update_user_meta( $customer_id, '_it_exchange_paypal_standard_secure_email', $paypal_standard_secure_email );
}

/**
 * This is the function registered in the options array when it_exchange_register_addon was called for paypal
 *
 * It tells Exchange where to find the settings page
 *
 * @return void
*/
function it_exchange_paypal_standard_secure_settings_callback() {
	$IT_Exchange_paypal_standard_secure_Add_On = new IT_Exchange_paypal_standard_secure_Add_On();
	$IT_Exchange_paypal_standard_secure_Add_On->print_settings_page();
}

/**
 * This is the function prints the payment form on the Wizard Settings screen
 *
 * @return void
*/
function paypal_standard_secure_print_wizard_settings( $form ) {
	$IT_Exchange_paypal_standard_secure_Add_On = new IT_Exchange_paypal_standard_secure_Add_On();
	$settings = it_exchange_get_option( 'addon_paypal_standard_secure', true );
	?>
	<div class="field paypal_standard_secure-wizard hide-if-js">
	<?php $IT_Exchange_paypal_standard_secure_Add_On->get_paypal_standard_secure_payment_form_table( $form, $settings ); ?>
	</div>
	<?php
}

/**
 * Saves paypal settings when the Wizard is saved
 *
 * @since 0.4.0
 *
 * @return void
*/
function it_exchange_save_paypal_standard_secure_wizard_settings( $errors ) {
	if ( ! empty( $errors ) )
		return $errors;

	$IT_Exchange_paypal_standard_secure_Add_On = new IT_Exchange_paypal_standard_secure_Add_On();
	return $IT_Exchange_paypal_standard_secure_Add_On->paypal_standard_secure_save_wizard_settings();
}
add_action( 'it_exchange_save_paypal-standard-secure_wizard_settings', 'it_exchange_save_paypal_standard_secure_wizard_settings' );

/**
 * Default settings for paypal_standard_secure
 *
 * @since 0.4.0
 *
 * @param array $values
 * @return array
*/
function it_exchange_paypal_standard_secure_addon_default_settings( $values ) {
	$defaults = array(
		'live-email-address'    => '',
		'live-api-username'     => '',
		'live-api-password'     => '',
		'live-api-signature'    => '',
		'sandbox-email-address' => '',
		'sandbox-api-username'  => '',
		'sandbox-api-password'  => '',
		'sandbox-api-signature' => '',
		'sandbox-mode'          => false,
		'purchase-button-label' => __( 'Pay with PayPal', 'LION' ),
	);
	$values = ITUtility::merge_defaults( $values, $defaults );
	return $values;
}
add_filter( 'it_storage_get_defaults_exchange_addon_paypal_standard_secure', 'it_exchange_paypal_standard_secure_addon_default_settings' );

/**
 * Returns the button for making the PayPal faux payment button
 *
 * @since 0.4.19
 *
 * @param array $options
 * @return string HTML button
*/
function it_exchange_paypal_standard_secure_addon_make_payment_button( $options ) {

	if ( 0 >= it_exchange_get_cart_total( false ) )
		return;

	$general_settings = it_exchange_get_option( 'settings_general' );
	$paypal_settings  = it_exchange_get_option( 'addon_paypal_standard_secure' );

	$payment_form = '';

	$it_exchange_customer = it_exchange_get_current_customer();

	$payment_form .= '<form action="' . get_site_url() . '/?paypal-standard-secure-form=1" method="post">';
	$payment_form .= '<input type="submit" class="it-exchange-paypal-standard-button" name="paypal_standard_secure_purchase" value="' . $paypal_settings['paypal-standard-secure-purchase-button-label'] .'" />';
	$payment_form .= '</form>';

	return $payment_form;

}
add_filter( 'it_exchange_get_paypal-standard-secure_make_payment_button', 'it_exchange_paypal_standard_secure_addon_make_payment_button', 10, 2 );

/**
 * Process the faux PayPal Standard secure form
 *
 * @since 0.4.19
 *
 * @param array $options
 * @return string HTML button
*/
function it_exchange_process_paypal_standard_secure_form() {

	$paypal_settings  = it_exchange_get_option( 'addon_paypal_standard_secure' );

	if ( ! empty( $_REQUEST['paypal_standard_secure_purchase'] ) ) {

		if ( $url = it_exchange_paypal_standard_secure_addon_get_payment_url() ) {

			wp_redirect( $url );

		} else {

			it_exchange_add_message( 'error', __( 'Error processing PayPal form. Missing valid PayPal information.', 'LION' ) );
			wp_redirect( it_exchange_get_page_url( 'checkout' ) );

		}

	}

}
add_action( 'wp', 'it_exchange_process_paypal_standard_secure_form' );

/**
 * Returns the button for making the payment
 *
 * @since 0.4.0
 *
 * @return string PayPal payment URL
*/
function it_exchange_paypal_standard_secure_addon_get_payment_url() {

	if ( 0 >= it_exchange_get_cart_total( false ) )
		return;

	$general_settings = it_exchange_get_option( 'settings_general' );
	$paypal_settings  = it_exchange_get_option( 'addon_paypal_standard_secure' );

	$payment_form = '';

	$paypal_api_url       = ( $paypal_settings['paypal-standard-secure-sandbox-mode'] ) ? PAYPAL_NVP_API_SANDBOX_URL : PAYPAL_NVP_API_LIVE_URL;
	$paypal_payment_url   = ( $paypal_settings['paypal-standard-secure-sandbox-mode'] ) ? 'https://www.sandbox.paypal.com/cgi-bin/webscr' : 'https://www.paypal.com/cgi-bin/webscr';
	$paypal_email         = ( $paypal_settings['paypal-standard-secure-sandbox-mode'] ) ? $paypal_settings['paypal-standard-secure-sandbox-email-address'] : $paypal_settings['paypal-standard-secure-live-email-address'];
	$paypal_api_username  = ( $paypal_settings['paypal-standard-secure-sandbox-mode'] ) ? $paypal_settings['paypal-standard-secure-sandbox-api-username'] : $paypal_settings['paypal-standard-secure-live-api-username'];
	$paypal_api_password  = ( $paypal_settings['paypal-standard-secure-sandbox-mode'] ) ? $paypal_settings['paypal-standard-secure-sandbox-api-password'] : $paypal_settings['paypal-standard-secure-live-api-password'];
	$paypal_api_signature = ( $paypal_settings['paypal-standard-secure-sandbox-mode'] ) ? $paypal_settings['paypal-standard-secure-sandbox-api-signature'] : $paypal_settings['paypal-standard-secure-live-api-signature'];

	if ( ! empty( $paypal_email )
		&& ! empty( $paypal_api_username )
		&& ! empty( $paypal_api_password )
		&& ! empty( $paypal_api_signature ) ) {

		$subscription = false;
		$it_exchange_customer = it_exchange_get_current_customer();

		remove_filter( 'the_title', 'wptexturize' ); // remove this because it screws up the product titles in PayPal

		if ( 1 === it_exchange_get_cart_products_count() ) {
			$cart = it_exchange_get_cart_products();
			foreach( $cart as $product ) {
				if ( it_exchange_product_supports_feature( $product['product_id'], 'recurring-payments', array( 'setting' => 'auto-renew' ) ) ) {
					if ( it_exchange_product_has_feature( $product['product_id'], 'recurring-payments', array( 'setting' => 'auto-renew' ) ) ) {
						$time = it_exchange_get_product_feature( $product['product_id'], 'recurring-payments', array( 'setting' => 'time' ) );
						switch( $time ) {

							case 'yearly':
								$unit = 'Y';
								break;

							case 'monthly':
							default:
								$unit = 'M';
								break;

						}
						$unit = apply_filters( 'it_exchange_paypal-standard_subscription_unit', $unit, $time );
						$duration = apply_filters( 'it_exchange_paypal-standard_subscription_duration', 1, $time );
						$subscription = true;
					}
				}
			}
		}

		$button_request = array(
			'USER'           => trim( $paypal_api_username ),
			'PWD'            => trim( $paypal_api_password ),
			'SIGNATURE'      => trim( $paypal_api_signature ),
			'VERSION'        => '96.0', //The PayPal API version
			'METHOD'         => 'BMCreateButton',
			'BUTTONCODE'     => 'ENCRYPTED',
			'BUTTONIMAGE'    => 'REG',
			'BUYNOWTEXT'     => 'PAYNOW',
		);

		if ( $subscription ) {
		//https://developer.paypal.com/webapps/developer/docs/classic/paypal-payments-standard/integration-guide/Appx_websitestandard_htmlvariables/#id08A6HI00JQU
			//a1, t1, p1 are for the first trial periods which is not supported with the Recurring Payments add-on
			//a2, t2, p2 are for the second trial period, which is not supported with the Recurring Payments add-on
			//a3, t3, p3 are required for the actual subscription details
			$button_request['BUTTONTYPE'] = 'SUBSCRIBE';
			$L_BUTTONVARS[] = 'a3=' . number_format( it_exchange_get_cart_total( false ), 2, '.', '' ); //Regular subscription price.
			$L_BUTTONVARS[] = 'p3=' . $duration; //Subscription duration. Specify an integer value in the allowable range for the units of duration that you specify with t3.
			$L_BUTTONVARS[] = 't3=' . $unit; //Regular subscription units of duration. (D, W, M, Y) -- we only use M,Y by default
			$L_BUTTONVARS[] = 'src=1'; //Recurring payments.

		} else {
			$button_request['BUTTONTYPE'] = 'BUYNOW';
			$L_BUTTONVARS[] = 'amount=' . number_format( it_exchange_get_cart_total( false ), 2, '.', '' );
			$L_BUTTONVARS[] = 'quantity=1';

		}

		$L_BUTTONVARS[] = 'business=' . $paypal_email;
		$L_BUTTONVARS[] = 'item_name=' . it_exchange_get_cart_description();
		$L_BUTTONVARS[] = 'return=' . add_query_arg( 'it-exchange-transaction-method', 'paypal-standard-secure', it_exchange_get_page_url( 'transaction' ) );
		$L_BUTTONVARS[] = 'currency_code=' . $general_settings['default-currency'];
		$L_BUTTONVARS[] = 'notify_url=' . get_site_url() . '/?' . it_exchange_get_webhook( 'paypal-standard-secure' ) . '=1';
		$L_BUTTONVARS[] = 'no_note=1';
		$L_BUTTONVARS[] = 'no_shipping=1';
		$L_BUTTONVARS[] = 'shipping=0';
		$L_BUTTONVARS[] = 'email=' . $it_exchange_customer->data->user_email;
		$L_BUTTONVARS[] = 'rm=2'; //Return  Method - https://developer.paypal.com/webapps/developer/docs/classic/button-manager/integration-guide/ButtonManagerHTMLVariables/
		$L_BUTTONVARS[] = 'cancel_return=' . it_exchange_get_page_url( 'cart' );

		$count = 0;
		foreach( $L_BUTTONVARS as $L_BUTTONVAR ) {

			$button_request['L_BUTTONVAR' . $count] = $L_BUTTONVAR;
			$count++;

		}

		$response = wp_remote_post( $paypal_api_url, array( 'body' => $button_request ) );

		if ( !is_wp_error( $response ) ) {

			parse_str( wp_remote_retrieve_body( $response ) );

			if ( !empty( $ACK ) && 'Success' === $ACK ) {

				if ( !empty( $WEBSITECODE ) )
					$payment_form = str_replace( array( "\r\n", "\r", "\n" ), '', stripslashes( $WEBSITECODE ) );
					//Strip out the newline characters because parse_str/PayPal adds a \n to the encrypted code, whic breaks the digital ID

			}

		}

		if ( preg_match( '/-----BEGIN PKCS7-----.*-----END PKCS7-----/i', $payment_form, $matches ) ) {

			$query = array(
				'cmd'           => '_s-xclick',
				'encrypted'     => $matches[0],
			);

			$paypal_payment_url = $paypal_payment_url . '?' .  http_build_query( $query );

			return $paypal_payment_url;

		}

	}

	return false;
}

/**
 * Adds the paypal webhook to the global array of keys to listen for
 *
 * @since 0.4.0
 *
 * @param array $webhooks existing
 * @return array
*/
function it_exchange_paypal_standard_secure_addon_register_webhook() {
	$key   = 'paypal-standard-secure';
	$param = apply_filters( 'it_exchange_paypal-standard-secure_webhook', 'it_exchange_paypal-standard-secure' );
	it_exchange_register_webhook( $key, $param );
}
add_filter( 'init', 'it_exchange_paypal_standard_secure_addon_register_webhook' );

/**
 * Processes webhooks for PayPal Web Standard
 *
 * @since 0.4.0
 * @todo actually handle the exceptions
 *
 * @param array $request really just passing  $_REQUEST
 */
function it_exchange_paypal_standard_secure_addon_process_webhook( $request ) {

	$general_settings = it_exchange_get_option( 'settings_general' );
	$settings = it_exchange_get_option( 'addon_paypal_standard_secure' );

	$subscriber_id = !empty( $request['subscr_id'] ) ? $request['subscr_id'] : false;
	$subscriber_id = !empty( $request['recurring_payment_id'] ) ? $request['recurring_payment_id'] : $subscriber_id;

	if ( !empty( $request['txn_type'] ) ) {

		switch( $request['txn_type'] ) {

			case 'web_accept':
				switch( strtolower( $request['payment_status'] ) ) {

					case 'completed' :
						it_exchange_paypal_standard_secure_addon_update_transaction_status( $request['txn_id'], $request['payment_status'] );
						break;
					case 'reversed' :
						it_exchange_paypal_standard_secure_addon_update_transaction_status( $request['parent_txn_id'], $request['reason_code'] );
						break;
				}
				break;

			case 'subscr_payment':
				switch( strtolower( $request['payment_status'] ) ) {
					case 'completed' :
						if ( !it_exchange_paypal_standard_secure_addon_update_transaction_status( $request['txn_id'], $request['payment_status'] ) ) {
							//If the transaction isn't found, we've got a new payment
							it_exchange_paypal_standard_secure_addon_add_child_transaction( $request['txn_id'], $request['payment_status'], $subscriber_id, $request['mc_gross'] );
						} else {
							//If it is found, make sure the subscriber ID is attached to it
							it_exchange_paypal_standard_secure_addon_update_subscriber_id( $request['txn_id'], $subscriber_id );
						}
						it_exchange_paypal_standard_secure_addon_update_subscriber_status( $subscriber_id, 'active' );
						break;
				}
				break;

			case 'subscr_signup':
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

	} else {

		//These IPNs don't have txn_types, why PayPal!? WHY!?
		if ( !empty( $request['reason_code'] ) ) {

			switch( $request['reason_code'] ) {

				case 'refund' :
					it_exchange_paypal_standard_secure_addon_update_transaction_status( $request['parent_txn_id'], $request['payment_status'] );
					it_exchange_paypal_standard_secure_addon_add_refund_to_transaction( $request['parent_txn_id'], $request['mc_gross'] );
					if ( $subscriber_id )
						it_exchange_paypal_standard_secure_addon_update_subscriber_status( $subscriber_id, 'refunded' );
					break;

			}

		}

	}

}
add_action( 'it_exchange_webhook_it_exchange_paypal-standard-secure', 'it_exchange_paypal_standard_secure_addon_process_webhook' );

/**
 * Grab a transaction from the paypal transaction ID
 *
 * @since 0.4.0
 *
 * @param integer $paypal_standard_secure_id id of paypal transaction
 * @return transaction object
*/
function it_exchange_paypal_standard_secure_addon_get_transaction_id( $paypal_standard_secure_id ) {
	$args = array(
		'meta_key'    => '_it_exchange_transaction_method_id',
		'meta_value'  => $paypal_standard_secure_id,
		'numberposts' => 1, //we should only have one, so limit to 1
	);
	return it_exchange_get_transactions( $args );
}

/**
 * Grab a transaction from the paypal subscriber ID
 *
 * @since 0.4.0
 *
 * @param integer $paypal_standard_secure_id id of paypal transaction
 * @return transaction object
*/
function it_exchange_paypal_standard_secure_addon_get_transaction_id_by_subscriber_id( $subscriber_id ) {
	$args = array(
		'meta_key'    => '_it_exchange_transaction_subscriber_id',
		'meta_value'  => $subscriber_id,
		'numberposts' => 1, //we should only have one, so limit to 1
	);
	return it_exchange_get_transactions( $args );
}

/**
 * Updates a paypals transaction status based on paypal ID
 *
 * @since 0.4.0
 *
 * @param integer $paypal_standard_secure_id id of paypal transaction
 * @param string $new_status new status
 * @return bool
*/
function it_exchange_paypal_standard_secure_addon_update_transaction_status( $paypal_standard_secure_id, $new_status ) {
	$transactions = it_exchange_paypal_standard_secure_addon_get_transaction_id( $paypal_standard_secure_id );
	foreach( $transactions as $transaction ) { //really only one
		$current_status = it_exchange_get_transaction_status( $transaction );
		if ( $new_status !== $current_status )
			it_exchange_update_transaction_status( $transaction, $new_status );
		return true;
	}
	return false;
}

/**
 * Add a new transaction, really only used for subscription payments.
 * If a subscription pays again, we want to create another transaction in Exchange
 * This transaction needs to be linked to the parent transaction.
 *
 * @since 1.3.0
 *
 * @param integer $paypal_standard_secure_id id of paypal transaction
 * @param string $payment_status new status
 * @param string $subscriber_id from PayPal (optional)
 * @return bool
*/
function it_exchange_paypal_standard_secure_addon_add_child_transaction( $paypal_standard_secure_id, $payment_status, $subscriber_id=false, $amount ) {
	$transactions = it_exchange_paypal_standard_secure_addon_get_transaction_id( $paypal_standard_secure_id );
	if ( !empty( $transactions ) ) {
		//this transaction DOES exist, don't try to create a new one, just update the status
		it_exchange_paypal_standard_secure_addon_update_transaction_status( $paypal_standard_secure_id, $payment_status );
	} else {

		if ( !empty( $subscriber_id ) ) {

			$transactions = it_exchange_paypal_standard_secure_addon_get_transaction_id_by_subscriber_id( $subscriber_id );
			foreach( $transactions as $transaction ) { //really only one
				$parent_tx_id = $transaction->ID;
				$customer_id = get_post_meta( $transaction->ID, '_it_exchange_customer_id', true );
			}

		} else {
			$parent_tx_id = false;
			$customer_id = false;
		}

		if ( $parent_tx_id && $customer_id ) {
			$transaction_object = new stdClass;
			$transaction_object->total = $amount;
			it_exchange_add_child_transaction( 'paypal-standard-secure', $paypal_standard_secure_id, $payment_status, $customer_id, $parent_tx_id, $transaction_object );
			return true;
		}
	}
	return false;
}

/**
 * Adds a refund to post_meta for a paypal transaction
 *
 * @since 0.4.0
 * @param string $paypal_standard_secure_id PayPal Transaction ID
 * @param string $refund Refund Amount
*/
function it_exchange_paypal_standard_secure_addon_add_refund_to_transaction( $paypal_standard_secure_id, $refund ) {
	$transactions = it_exchange_paypal_standard_secure_addon_get_transaction_id( $paypal_standard_secure_id );
	foreach( $transactions as $transaction ) { //really only one
		it_exchange_add_refund_to_transaction( $transaction, number_format( abs( $refund ), '2', '.', '' ) );
	}
}

/**
 * Updates a subscription ID to post_meta for a paypal transaction
 *
 * @since 1.3.0
 * @param string $paypal_standard_id PayPal Transaction ID
 * @param string $subscriber_id PayPal Subscriber ID
*/
function it_exchange_paypal_standard_secure_addon_update_subscriber_id( $paypal_standard_secure_id, $subscriber_id ) {
	$transactions = it_exchange_paypal_standard_secure_addon_get_transaction_id( $paypal_standard_secure_id );
	foreach( $transactions as $transaction ) { //really only one
		do_action( 'it_exchange_update_transaction_subscription_id', $transaction, $subscriber_id );
	}
}

/**
 * Updates a subscription status to post_meta for a paypal transaction
 *
 * @since 1.3.0
 * @param string $subscriber_id PayPal Subscriber ID
 * @param string $status Status of Subscription
*/
function it_exchange_paypal_standard_secure_addon_update_subscriber_status( $subscriber_id, $status ) {
	$transactions = it_exchange_paypal_standard_secure_addon_get_transaction_id_by_subscriber_id( $subscriber_id );
	foreach( $transactions as $transaction ) { //really only one
		// If the subscription has been cancelled/suspended and fully refunded, they need to be deactivated
		if ( !in_array( $status, array( 'active', 'deactivated' ) ) ) {
			if ( $transaction->has_refunds() && 0 === it_exchange_get_transaction_total( $transaction, false ) )
				$status = 'deactivated';

			if ( $transaction->has_children() ) {
				//Get the last child and make sure it hasn't been fully refunded
				$args = array(
					'numberposts' => 1,
					'order'       => 'ASC',
				);
				$last_child_transaction = $transaction->get_children( $args );
				foreach( $last_child_transaction as $last_transaction ) { //really only one
					$last_transaction = it_exchange_get_transaction( $last_transaction );
					if ( $last_transaction->has_refunds() && 0 === it_exchange_get_transaction_total( $last_transaction, false ) )
						$status = 'deactivated';
				}
			}
		}
		do_action( 'it_exchange_update_transaction_subscription_status', $transaction, $subscriber_id, $status );
	}
}

/**
 * Gets the interpretted transaction status from valid paypal transaction statuses
 *
 * @since 0.4.0
 *
 * @param string $status the string of the paypal transaction
 * @return string translaction transaction status
*/
function it_exchange_paypal_standard_secure_addon_transaction_status_label( $status ) {

	switch ( strtolower( $status ) ) {

		case 'completed':
		case 'success':
		case 'canceled_reversal':
		case 'processed' :
			return __( 'Paid', 'LION' );
			break;
		case 'refunded':
		case 'refund':
			return __( 'Refund', 'LION' );
			break;
		case 'reversed':
			return __( 'Reversed', 'LION' );
			break;
		case 'buyer_complaint':
			return __( 'Buyer Complaint', 'LION' );
			break;
		case 'denied' :
			return __( 'Denied', 'LION' );
			break;
		case 'expired' :
			return __( 'Expired', 'LION' );
			break;
		case 'failed' :
			return __( 'Failed', 'LION' );
			break;
		case 'pending' :
			return __( 'Pending', 'LION' );
			break;
		case 'voided' :
			return __( 'Voided', 'LION' );
			break;
		default:
			return __( 'Unknown', 'LION' );
	}

}
add_filter( 'it_exchange_transaction_status_label_paypal-standard-secure', 'it_exchange_paypal_standard_secure_addon_transaction_status_label' );

/**
 * Returns a boolean. Is this transaction a status that warrants delivery of any products attached to it?
 *
 * @since 0.4.2
 *
 * @param boolean $cleared passed in through WP filter. Ignored here.
 * @param object $transaction
 * @return boolean
*/
function it_exchange_paypal_standard_secure_transaction_is_cleared_for_delivery( $cleared, $transaction ) {
    $valid_stati = array(
		'completed',
		'success',
		'canceled_reversal',
		'processed',
	);
    return in_array( strtolower( it_exchange_get_transaction_status( $transaction ) ), $valid_stati );
}
add_filter( 'it_exchange_paypal-standard-secure_transaction_is_cleared_for_delivery', 'it_exchange_paypal_standard_secure_transaction_is_cleared_for_delivery', 10, 2 );

/*
 * Returns the unsubscribe action for PayPal autorenewing payments
 *
 * @since 1.3.0
 *
 * @param string $output Should be an empty string
 * @param array $options Array of options passed from Recurring Payments add-on
 * @return string $output Unsubscribe action
*/
function it_exchange_paypal_standard_secure_unsubscribe_action( $output, $options ) {
	$paypal_settings      = it_exchange_get_option( 'addon_paypal_standard_secure' );
	$paypal_url           = ( $paypal_settings['paypal-standard-secure-sandbox-mode'] ) ? PAYPAL_PAYMENT_SANDBOX_URL : PAYPAL_PAYMENT_LIVE_URL;
	$paypal_email         = ( $paypal_settings['paypal-standard-secure-sandbox-mode'] ) ? $paypal_settings['paypal-standard-secure-sandbox-email-address'] : $paypal_settings['paypal-standard-secure-live-email-address'];

	$output  = '<a class="button" href="' . $paypal_url . '?cmd=_subscr-find&alias=' . urlencode( $paypal_email ) . '">';
	$output .= $options['label'];
	$output .= '</a>';

	return $output;
}
add_filter( 'it_exchange_paypal-standard-secure_unsubscribe_action', 'it_exchange_paypal_standard_secure_unsubscribe_action', 10, 2 );

/**
 * Output the Cancel URL for the Payments screen
 *
 * @since 1.3.1
 *
 * @param object $transaction iThemes Transaction object
 * @return void
*/
function it_exchange_paypal_standard_secure_after_payment_details_cancel_url( $transaction ) {
	$paypal_settings      = it_exchange_get_option( 'addon_paypal_standard_secure' );
	$paypal_url           = ( $paypal_settings['paypal-standard-secure-sandbox-mode'] ) ? PAYPAL_SANDBOX_URL : PAYPAL_LIVE_URL;
	$cart_object = get_post_meta( $transaction->ID, '_it_exchange_cart_object', true );
	foreach ( $cart_object->products as $product ) {
		$autorenews = $transaction->get_transaction_meta( 'subscription_autorenew_' . $product['product_id'], true );
		if ( $autorenews ) {
			$subscriber_id = $transaction->get_transaction_meta( 'subscriber_id', true );
			$status = $transaction->get_transaction_meta( 'subscriber_status', true );
			switch( $status ) {

				case 'deactivated':
					$output = __( 'Recurring payment has been deactivated', 'LION' );
					break;

				case 'cancelled':
					$output = __( 'Recurring payment has been cancelled', 'LION' );
					break;

				case 'suspended':
					$output = __( 'Recurring payment has been suspended', 'LION' );
					break;

				case 'active':
				default:
					$output = '<a href="' . $paypal_url . '">' . __( 'Cancel Recurring Payment', 'LION' ) . ' (' . __( 'Profile ID', 'LION' ) . ': ' . $subscriber_id . ')</a>';
					break;
			}
			?>
			<div class="transaction-autorenews clearfix spacing-wrapper">
				<div class="recurring-payment-cancel-options left">
					<div class="recurring-payment-status-name"><?php echo $output; ?></div>
				</div>
			</div>
			<?php
			continue;
		}
	}
}
add_action( 'it_exchange_after_payment_details_cancel_url_for_paypal-standard-secure', 'it_exchange_paypal_standard_secure_after_payment_details_cancel_url' );

/**
 * Convert old option keys to new option keys
 *
 * Our original option keys for this plugin were generating form field names 80+ chars in length
 *
 * @since CHANGEME
 *
 * @param  array   $options         options as pulled from the DB
 * @param  string  $key             the key for the options
 * @param  boolean $break_cache     was the flag to break cache passed?
 * @param  boolean $merge_defaults  was the flag to merge defaults passed?
 * @return array
*/
function it_exchange_paypal_standard_secure_convert_option_keys( $options, $key, $break_cache, $merge_defaults ) {
	if ( 'addon_paypal_standard_secure' != $key )
		return $options;

	foreach( $options as $key => $value ) {
		if ( 'paypal-standard-secure-' == substr( $key, 0, 23 ) && empty( $opitons[substr( $key, 23 )] ) ) {
			$options[substr( $key, 23 )] = $value;
			unset( $options[$key] );
		}
	}
	return $options;
}
add_filter( 'it_exchange_get_option', 'it_exchange_paypal_standard_secure_convert_option_keys', 10, 4 );

/**
 * Class for Stripe
 * @since 0.4.0
*/
class IT_Exchange_paypal_standard_secure_Add_On {

	/**
	 * @var boolean $_is_admin true or false
	 * @since 0.4.0
	*/
	var $_is_admin;

	/**
	 * @var string $_current_page Current $_GET['page'] value
	 * @since 0.4.0
	*/
	var $_current_page;

	/**
	 * @var string $_current_add_on Current $_GET['add-on-settings'] value
	 * @since 0.4.0
	*/
	var $_current_add_on;

	/**
	 * @var string $status_message will be displayed if not empty
	 * @since 0.4.0
	*/
	var $status_message;

	/**
	 * @var string $error_message will be displayed if not empty
	 * @since 0.4.0
	*/
	var $error_message;

	/**
	 * Class constructor
	 *
	 * Sets up the class.
	 * @since 0.4.0
	 * @return void
	*/
	function IT_Exchange_paypal_standard_secure_Add_On() {
		$this->_is_admin       = is_admin();
		$this->_current_page   = empty( $_GET['page'] ) ? false : $_GET['page'];
		$this->_current_add_on = empty( $_GET['add-on-settings'] ) ? false : $_GET['add-on-settings'];

		if ( ! empty( $_POST ) && $this->_is_admin && 'it-exchange-addons' == $this->_current_page && 'paypal-standard-secure' == $this->_current_add_on ) {
			$this->save_settings();
		}

	}

	function print_settings_page() {
		$settings = it_exchange_get_option( 'addon_paypal_standard_secure', true );
		$form_values  = empty( $this->error_message ) ? $settings : ITForm::get_post_data();
		$form_options = array(
			'id'      => apply_filters( 'it_exchange_add_on_paypal-standard-secure', 'it-exchange-add-on-paypal-standard-secure-settings' ),
			'enctype' => apply_filters( 'it_exchange_add_on_paypal-standard-secure_settings_form_enctype', false ),
			'action'  => 'admin.php?page=it-exchange-addons&add-on-settings=paypal-standard-secure',
		);
		$form         = new ITForm( $form_values, array( 'prefix' => 'it-exchange-add-on-paypal_standard_secure' ) );

		if ( ! empty ( $this->status_message ) )
			ITUtility::show_status_message( $this->status_message );
		if ( ! empty( $this->error_message ) )
			ITUtility::show_error_message( $this->error_message );

		?>
		<div class="wrap">
			<?php screen_icon( 'it-exchange' ); ?>
			<h2><?php _e( 'PayPal Standard Settings - Secure', 'LION' ); ?></h2>

			<?php do_action( 'it_exchange_paypal-standard-secure_settings_page_top' ); ?>
			<?php do_action( 'it_exchange_addon_settings_page_top' ); ?>

			<?php $form->start_form( $form_options, 'it-exchange-paypal-standard-secure-settings' ); ?>
				<?php do_action( 'it_exchange_paypal-standard-secure_settings_form_top' ); ?>
				<?php $this->get_paypal_standard_secure_payment_form_table( $form, $form_values ); ?>
				<?php do_action( 'it_exchange_paypal-standard-secure_settings_form_bottom' ); ?>
				<p class="submit">
					<?php $form->add_submit( 'submit', array( 'value' => __( 'Save Changes', 'LION' ), 'class' => 'button button-primary button-large' ) ); ?>
				</p>
			<?php $form->end_form(); ?>
			<?php do_action( 'it_exchange_paypal-standard-secure_settings_page_bottom' ); ?>
			<?php do_action( 'it_exchange_addon_settings_page_bottom' ); ?>
		</div>
		<?php
	}

	function get_paypal_standard_secure_payment_form_table( $form, $settings = array() ) {

		$general_settings = it_exchange_get_option( 'settings_general' );

		if ( ! empty( $_GET['page'] ) && 'it-exchange-setup' == $_GET['page'] ) : ?>
			<h3><?php _e( 'PayPal Standard - Secure (Highly Recommended)', 'LION' ); ?></h3>
		<?php endif;

		if ( !empty( $settings ) )
			foreach ( $settings as $key => $var )
				$form->set_option( $key, $var );

		?>
		<div class="it-exchange-addon-settings it-exchange-paypal-addon-settings">
			<p>
				<?php _e( 'Although this PayPal version for iThemes Exchange takes more effort and time, it is well worth it for the security options for your store. To get PayPal set up for use with Exchange, you\'ll need to add the following information from your PayPal account.', 'LION' ); ?><br /><br />
				<?php _e( 'Video:', 'LION' ); ?>&nbsp;<a href="http://ithemes.com/tutorials/setting-up-paypal-standard-secure/" target="_blank"><?php _e( 'Setting Up PayPal Standard Secure', 'LION' ); ?></a>
			</p>
			<p><?php _e( 'Don\'t have a PayPal account yet?', 'LION' ); ?> <a href="http://paypal.com" target="_blank"><?php _e( 'Go set one up here', 'LION' ); ?></a>.</p>
			<h4><?php _e( 'Step 1. Fill out your PayPal email address', 'LION' ); ?></h4>
			<p>
				<label for="live-email-address"><?php _e( 'PayPal Email Address', 'LION' ); ?> <span class="tip" title="<?php _e( 'We need this to tie payments to your account.', 'LION' ); ?>">i</span></label>
				<?php
				if ( ! empty( $_GET['page'] ) && 'it-exchange-setup' == $_GET['page'] )
					$form->add_text_box( 'paypal-standard-secure-live-email-address' );
				else
					$form->add_text_box( 'live-email-address' );
				?>
			</p>
			<h4><?php _e( 'Step 2. Fill out your PayPal API credentials', 'LION' ); ?></h4>
			<p>
				<label for="live-api-username"><?php _e( 'PayPal API Username', 'LION' ); ?> <span class="tip" title="<?php _e( 'At PayPal, see: Profile &rarr; My Selling Tools &rarr; API Access &rarr; Update &rarr; View API Signature (or Request API Credentials).', 'LION' ); ?>">i</span></label>
				<?php
				if ( ! empty( $_GET['page'] ) && 'it-exchange-setup' == $_GET['page'] )
					$form->add_text_box( 'paypal-standard-secure-live-api-username' );
				else
					$form->add_text_box( 'live-api-username' );
				?>
			</p>
			<p>
				<label for="live-api-password"><?php _e( 'PayPal API Password', 'LION' ); ?> <span class="tip" title="<?php _e( 'At PayPal, see: Profile &rarr; My Selling Tools &rarr; API Access &rarr; Update &rarr; View API Signature (or Request API Credentials).', 'LION' ); ?>">i</span></label>
				<?php
				if ( ! empty( $_GET['page'] ) && 'it-exchange-setup' == $_GET['page'] )
					$form->add_text_box( 'paypal-standard-secure-live-api-password' );
				else
					$form->add_text_box( 'live-api-password' );
				?>
			</p>
			<p>
				<label for="live-api-signature"><?php _e( 'PayPal API Signature', 'LION' ); ?> <span class="tip" title="<?php _e( 'At PayPal, see: Profile &rarr; My Selling Tools &rarr; API Access &rarr; Update &rarr; View API Signature (or Request API Credentials).', 'LION' ); ?>">i</span></label>
				<?php
				if ( ! empty( $_GET['page'] ) && 'it-exchange-setup' == $_GET['page'] )
					$form->add_text_box( 'paypal-standard-secure-live-api-signature' );
				else
					$form->add_text_box( 'live-api-signature' );
				?>
			</p>
			<h4><?php _e( 'Step 3. Setup PayPal Instant Payment Notifications (IPN)', 'LION' ); ?></h4>
			<p><?php _e( 'PayPal IPN must be configured in Account Profile -› Instant Payment Notification Preferences in your PayPal Account', 'LION' ); ?></p>
			<p><?php _e( 'Please log into your account and add this URL to your IPN Settings so iThemes Exchange is notified of things like refunds, payments, etc.', 'LION' ); ?></p>
			<code><?php echo get_site_url(); ?>/?<?php esc_attr_e( it_exchange_get_webhook( 'paypal-standard-secure' ) ); ?>=1</code>
			<h4><?php _e( 'Step 4. Setup PayPal Auto Return', 'LION' ); ?></h4>
			<p><?php _e( 'PayPal Auto Return must be configured in Account Profile -› Website Payment Preferences in your PayPal Account', 'LION' ); ?></p>
			<p><?php _e( 'Please log into your account, set Auto Return to ON and add this URL to your Return URL Settings so your customers are redirected to your site to complete the transactions.', 'LION' ); ?></p>
			<code><?php echo it_exchange_get_page_url( 'transaction' ); ?></code>
			<h4><?php _e( 'Step 5. Setup PayPal Payment Data Transfer (PDT)', 'LION' ); ?></h4>
			<p><?php _e( 'PayPal PDT must be turned <strong>ON</strong> in Account Profile -› Website Payment Preferences in your PayPal Account', 'LION' ); ?></p>
			<h4><?php _e( 'Optional: Edit Paypal Button Label', 'LION' ); ?></h4>
			<p>
				<label for="purchase-button-label"><?php _e( 'Purchase Button Label', 'LION' ); ?> <span class="tip" title="<?php _e( 'This is the text inside the button your customers will press to purchase with PayPal Standard (secure)', 'LION' ); ?>">i</span></label>
				<?php
				if ( ! empty( $_GET['page'] ) && 'it-exchange-setup' == $_GET['page'] )
					$form->add_text_box( 'paypal-standard-secure-purchase-button-label' );
				else
					$form->add_text_box( 'purchase-button-label' );
				?>
			</p>
			<h4 class="hide-if-wizard"><?php _e( 'Optional: Enable Paypal Testing Mode', 'LION' ); ?></h4>
			<p class="hide-if-wizard">
				<?php $form->add_check_box( 'sandbox-mode', array( 'class' => 'show-test-mode-options' ) ); ?>
				<label for="sandbox-mode"><?php _e( 'Enable PayPal Sandbox Mode?', 'LION' ); ?> <span class="tip" title="<?php _e( 'Use this mode for testing your store. This mode will need to be disabled when the store is ready to process customer payments.', 'LION' ); ?>">i</span></label>
			</p>
			<?php
			if ( ! empty( $_GET['page'] ) && 'it-exchange-setup' == $_GET['page'] )
				$hidden_class = ( $settings['paypal-standard-secure-sandbox-mode'] ) ? '' : 'hide-if-live-mode';
			else
				$hidden_class = ( $settings['sandbox-mode'] ) ? '' : 'hide-if-live-mode';
			?>
			<p class="test-mode-options hide-if-wizard <?php echo $hidden_class; ?>">
				<label for="sandbox-email-address"><?php _e( 'PayPal Sandbox Email Address', 'LION' ); ?> <span class="tip" title="<?php _e( 'We need this to tie payments to your account.', 'LION' ); ?>">i</span></label>
				<?php $form->add_text_box( 'sandbox-email-address' ); ?>
			</p>
			<p class="test-mode-options hide-if-wizard <?php echo $hidden_class; ?>">
				<label for="sandbox-api-username"><?php _e( 'PayPal Sandbox API Username', 'LION' ); ?> <span class="tip" title="<?php _e( 'View tutorial: ', 'LION' ); ?>http://ithemes.com/tutorials/creating-a-paypal-sandbox-test-account">i</span></label>
				<?php $form->add_text_box( 'sandbox-api-username' ); ?>
			</p>
			<p class="test-mode-options hide-if-wizard <?php echo $hidden_class; ?>">
				<label for="sandbox-api-password"><?php _e( 'PayPal Sandbox API Password', 'LION' ); ?> <span class="tip" title="<?php _e( 'View tutorial: ', 'LION' ); ?>http://ithemes.com/tutorials/creating-a-paypal-sandbox-test-account">i</span></label>
				<?php $form->add_text_box( 'sandbox-api-password' ); ?>
			</p>
			<p class="test-mode-options hide-if-wizard <?php echo $hidden_class; ?>">
				<label for="sandbox-api-signature"><?php _e( 'PayPal Sandbox API Signature', 'LION' ); ?> <span class="tip" title="<?php _e( 'View tutorial: ', 'LION' ); ?>http://ithemes.com/tutorials/creating-a-paypal-sandbox-test-account">i</span></label>
				<?php $form->add_text_box( 'sandbox-api-signature' ); ?>
			</p>
		</div>
		<?php
	}

	/**
	 * Save settings
	 *
	 * @since 0.4.0
	 * @return void
	*/
	function save_settings() {
		$defaults = it_exchange_get_option( 'addon_paypal_standard_secure' );
		$new_values = wp_parse_args( ITForm::get_post_data(), $defaults );

		// Check nonce
		if ( ! wp_verify_nonce( $_POST['_wpnonce'], 'it-exchange-paypal-standard-secure-settings' ) ) {
			$this->error_message = __( 'Error. Please try again', 'LION' );
			return;
		}

		$errors = apply_filters( 'it_exchange_add_on_paypal_standard_secure_validate_settings', $this->get_form_errors( $new_values ), $new_values );
		if ( ! $errors && it_exchange_save_option( 'addon_paypal_standard_secure', $new_values ) ) {
			ITUtility::show_status_message( __( 'Settings saved.', 'LION' ) );
		} else if ( $errors ) {
			$errors = implode( '<br />', $errors );
			$this->error_message = $errors;
		} else {
			$this->status_message = __( 'Settings not saved.', 'LION' );
		}

		do_action( 'it_exchange_save_add_on_settings_paypal-standard-secure' );

	}

	function paypal_standard_secure_save_wizard_settings() {
		if ( empty( $_REQUEST['it_exchange_settings-wizard-submitted'] ) )
			return;

		$paypal_standard_secure_settings = array();

		$fields = array(
			'live-email-address',
			'live-api-username',
			'live-api-password',
			'live-api-signature',
			'sandbox-mode',
			'sandbox-email-address',
			'sandbox-api-username',
			'sandbox-api-password',
			'sandbox-api-signature',
			'purchase-button-label',
		);
		$default_wizard_paypal_standard_secure_settings = apply_filters( 'default_wizard_paypal-standard-secure_settings', $fields );

		foreach( (array) $default_wizard_paypal_standard_secure_settings as $var ) {

			if ( isset( $_REQUEST['it_exchange_settings-paypal-standard-secure-' . $var] ) ) {
				$paypal_standard_secure_settings[$var] = $_REQUEST['it_exchange_settings-paypal-standard-secure-' . $var];
			}

		}

		$settings = wp_parse_args( $paypal_standard_secure_settings, it_exchange_get_option( 'addon_paypal_standard_secure' ) );

		if ( $error_msg = $this->get_form_errors( $settings ) ) {

			return $error_msg;

		} else {
			it_exchange_save_option( 'addon_paypal_standard_secure', $settings );
			$this->status_message = __( 'Settings Saved.', 'LION' );
		}

		return;

	}

	/**
	 * Validates for values
	 *
	 * Returns string of errors if anything is invalid
	 *
	 * @since 0.4.0
	 * @return void
	*/
	function get_form_errors( $values ) {

		$errors = array();
		if ( empty( $values['live-email-address'] ) )
			$errors[] = __( 'Please include your PayPal Email Address', 'LION' );
		if ( empty( $values['live-api-username'] ) )
			$errors[] = __( 'Please include your PayPal API Username', 'LION' );
		if ( empty( $values['live-api-password'] ) )
			$errors[] = __( 'Please include your PayPal API password', 'LION' );
		if ( empty( $values['live-api-signature'] ) )
			$errors[] = __( 'Please include your PayPal API signature', 'LION' );

		if ( !empty( $values['sandbox-mode' ] ) ) {
			if ( empty( $values['sandbox-email-address'] ) )
				$errors[] = __( 'Please include your PayPal Sandbox Email Address', 'LION' );
			if ( empty( $values['sandbox-api-username'] ) )
				$errors[] = __( 'Please include your PayPal Sandbox API Username', 'LION' );
			if ( empty( $values['sandbox-api-password'] ) )
				$errors[] = __( 'Please include your PayPal Sandbox API password', 'LION' );
			if ( empty( $values['sandbox-api-signature'] ) )
				$errors[] = __( 'Please include your PayPal Sandbox API signature', 'LION' );
		}

		return $errors;
	}
}
