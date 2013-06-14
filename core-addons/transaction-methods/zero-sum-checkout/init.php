<?php
/**
 * Zero Sum Transaction Method
 * For situations when the Cart Total is 0 (free), we still want to record the transaction!
 *
 * @since 0.4.0
 * @package IT_Exchange
*/

/**
 * This proccesses a zer-sum transaction.
 *
 * @since 0.4.0
 *
 * @param string $status passed by WP filter.
 * @param object $transaction_object The transaction object
*/
function it_exchange_zero_sum_checkout_addon_process_transaction( $status, $transaction_object ) {
	// If this has been modified as true already, return.
	if ( $status )
		return $status;

	// Verify nonce
	if ( ! empty( $_REQUEST['_zero_sum_checkout_nonce'] ) && ! wp_verify_nonce( $_REQUEST['_zero_sum_checkout_nonce'], 'zero-sum-checkout-checkout' ) ) {
		it_exchange_add_message( 'error', __( 'Transaction Failed, unable to verify security token.', 'LION' ) );
		return false;
	} else {
		$uniqid = it_exchange_get_zero_sum_checkout_transaction_uniqid();

		// Get customer ID data
		$it_exchange_customer = it_exchange_get_current_customer();

		return it_exchange_add_transaction( 'zero-sum-checkout', $uniqid, 'Completed', $it_exchange_customer->id, $transaction_object );
	}
	
	return false;
}
add_action( 'it_exchange_do_transaction_zero-sum-checkout', 'it_exchange_zero_sum_checkout_addon_process_transaction', 10, 2 );

/**
 * Returns a boolean. Is this transaction a status that warrants delivery of any products attached to it?
 *
 * @since 0.4.2
 *
 * @param boolean $cleared passed in through WP filter. Ignored here.
 * @param object $transaction
 * @return boolean
*/
function it_exchange_zero_sum_checkout_transaction_is_cleared_for_delivery( $cleared, $transaction ) { 
	$valid_stati = array( 'Completed' );
	return in_array( it_exchange_get_transaction_status( $transaction ), $valid_stati );
}
add_filter( 'it_exchange_zero-sum-checkout_transaction_is_cleared_for_delivery', 'it_exchange_zero_sum_checkout_transaction_is_cleared_for_delivery', 10, 2 );

function it_exchange_get_zero_sum_checkout_transaction_uniqid() {
	$uniqid = uniqid( '', true );

	if( !it_exchange_verify_zero_sum_checkout_transaction_unique_uniqid( $uniqid ) )
		$uniqid = it_exchange_get_zero_sum_checkout_transaction_uniqid();

	return $uniqid;
}

function it_exchange_verify_zero_sum_checkout_transaction_unique_uniqid( $uniqid ) {
	if ( !empty( $uniqid ) ) { //verify we get a valid 32 character md5 hash
		
		$args = array(
			'post_type' => 'it_exchange_tran',
			'meta_query' => array(
				array(
					'key' => '_it_exchange_transaction_method',
					'value' => 'zero-sum-checkout',
				),
				array(
					'key' => '_it_exchange_transaction_method_id',
					'value' => $uniqid ,
				),
			),
		);
		
		$query = new WP_Query( $args );
		
		return ( !empty( $query ) );
	}
	
	return false;
}

/**
 * Returns the button for making the payment
 *
 * @since 0.4.0
 *
 * @param array $options
 * @return string
*/
function it_exchange_zero_sum_checkout_addon_make_payment_button( $options ) {
	
	if ( 0 < it_exchange_get_cart_total( false ) )
		return;

	$general_settings = it_exchange_get_option( 'settings_general' );
	$stripe_settings = it_exchange_get_option( 'addon_zero_sum_checkout' );
	
	$products = it_exchange_get_cart_data( 'products' );

	$payment_form = '<form id="zero_sum_checkout_form" action="' . it_exchange_get_page_url( 'transaction' ) . '" method="post">';
	$payment_form .= '<input type="hidden" name="it-exchange-transaction-method" value="zero-sum-checkout" />';
	$payment_form .= wp_nonce_field( 'zero-sum-checkout-checkout', '_zero_sum_checkout_nonce', true, false );

	$payment_form .= '<input type="submit" id="zero-sum-checkout-button" name="zero_sum_checkout_purchase" value="' . apply_filters( 'zero_sum_checkout_button_label', 'Complete Purchase' ) .'" />';

	$payment_form .= '</form>';

	/*
	 * Going to remove this for now. It should be
	 * the responsibility of the site owner to
	 * notify if Javascript is disabled, but I will
	 * revisit this in case we want to a notifications.
	 *
	$payment_form .= '<div class="hide-if-js">';

	$payment_form .= '<h3>' . __( 'JavaScript disabled: Stripe Payment Gateway cannot be loaded!', 'LION' ) . '</h3>';

	$payment_form .= '</div>';
	*/

	return $payment_form;
	
}
add_filter( 'it_exchange_get_zero-sum-checkout_make_payment_button', 'it_exchange_zero_sum_checkout_addon_make_payment_button', 10, 2 );
