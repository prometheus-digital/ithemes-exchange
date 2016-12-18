<?php
/**
 * API Functions for Transaction Method Add-ons
 *
 * In addition to the functions found below, iThemes Exchange offers the following actions related to transactions
 * - it_exchange_save_transaction_unvalidated                       // Runs every time a transaction is saved.
 * - it_exchange_save_transaction_unavalidated-[txn-method] // Runs every time a specific transaction method is saved.
 * - it_exchange_save_transaction                           // Runs every time a transaction is saved if not an autosave and if user has permission to save post
 * - it_exchange_save_transaction-[txn-method]             // Runs every time a specific transaction method is saved if not an autosave and if user has permission to save transaction
 *
 * @package IT_Exchange
 * @since 0.3.3
 */


/**
 * Grabs the transaction method of a transaction
 *
 * @since 0.3.3
 * @param WP_Post|int|IT_Exchange_Transaction|bool $transaction ID or IT_Exchange_Transaction object
 *
 * @return string the transaction method
 */
function it_exchange_get_transaction_method( $transaction=false ) {
	if ( $transaction instanceof IT_Exchange_Transaction )
		return $transaction->transaction_method;

	if ( ! $transaction ) {
		global $post;
		$transaction = $post;
	}

	// Return value from IT_Exchange_Transaction if we are able to locate it
	$transaction = it_exchange_get_transaction( $transaction );
	if ( is_object( $transaction ) && ! empty ( $transaction->transaction_method ) && ! is_null( $transaction->transaction_method ) )
		return apply_filters( 'it_exchange_get_transaction_method', $transaction->transaction_method, $transaction );

	// Return query arg if is present
	if ( ! empty ( $_GET['transaction-method'] ) )
		return apply_filters( 'it_exchange_get_transaction_method', $_GET['transaction-method'], $transaction );

	return apply_filters( 'it_exchange_get_transaction_method', false, $transaction );
}

/**
 * Retreives a transaction object by passing it the WP post object or post id
 *
 * @since 0.3.3
 * @param WP_Post|int|IT_Exchange_Transaction $post  post object or post id
 *
 * @return IT_Exchange_Transaction|bool IT_Exchange_Transaction object for passed post
*/
function it_exchange_get_transaction( $post ) {
	if ( $post instanceof IT_Exchange_Transaction )
		return apply_filters( 'it_exchange_get_transaction', $post );

	try {
		$transaction = new IT_Exchange_Transaction( $post );
	} catch ( Exception $e ) {
		return false;
	}

	return apply_filters( 'it_exchange_get_transaction', $transaction );
}

/**
 * Get IT_Exchange_Transactions
 *
 * @since 0.3.3
 *
 * @param array $args
 *
 * @return IT_Exchange_Transaction[]  an array of IT_Exchange_Transaction objects
*/
function it_exchange_get_transactions( $args=array() ) {
	$defaults = array(
		'post_type' => 'it_exchange_tran',
	);

	// Different defaults depending on where we are.
	$confirmation_slug = it_exchange_get_page_slug( 'confirmation' );
	if ( it_exchange_is_page( 'confirmation' ) ) {
		if ( $transaction_hash = get_query_var( $confirmation_slug ) ) {
			if ( $transaction_id = it_exchange_get_transaction_id_from_hash( $transaction_hash ) )
				$defaults['p'] = $transaction_id;
		}
		if ( empty( $transaction_id ) )
			return array();
	}

	$args = wp_parse_args( $args, $defaults );
	$args['meta_query'] = empty( $args['meta_query'] ) ? array() : $args['meta_query'];

	// Fold in transaction_method
	if ( ! empty( $args['transaction_method'] ) ) {
		$meta_query = array(
			'key'   => '_it_exchange_transaction_method',
			'value' => $args['transaction_method'],
		);
		$args['meta_query'][] = $meta_query;
	}

	// Fold in transaction_status
	if ( ! empty( $args['transaction_status'] ) ) {
		$meta_query = array(
			'key'   => '_it_exchange_transaction_status',
			'value' => $args['transaction_status'],
		);
		$args['meta_query'][] = $meta_query;
	}

	// Fold in customer
	if ( ! empty( $args['customer_id'] ) ) {
		$meta_query = array(
			'key'   => '_it_exchange_customer_id',
			'value' => $args['customer_id'],
			'type'  => 'NUMERIC',
		);
		$args['meta_query'][] = $meta_query;
	}

	$args = apply_filters( 'it_exchange_get_transactions_get_posts_args', $args );

	if ( $transactions = get_posts( $args ) ) {
		
		if ( empty( $args['fields'] ) || $args['fields'] !== 'ids' ) {
			foreach ( $transactions as $key => $transaction ) {
				$transactions[ $key ] = it_exchange_get_transaction( $transaction );
			}
		}
	}

	return apply_filters( 'it_exchange_get_transactions', $transactions, $args );
}

/**
 * Get a transaction by method ID.
 *
 * @since 1.33
 *
 * @param string $method
 * @param string $method_id
 *
 * @return IT_Exchange_Transaction|null
 */
function it_exchange_get_transaction_by_method_id( $method, $method_id ) {

	$transactions = it_exchange_get_transactions( array(
		'numberposts' => 1,
		'meta_query' => array(
			'relation' => 'AND',
			array(
				'key' => '_it_exchange_transaction_method',
				'value' => $method
			),
			array(
				'key' => '_it_exchange_transaction_method_id',
				'value' => $method_id
			)
		)
	) );

	foreach ( $transactions as $transaction ) {
		return $transaction;
	}

	return null;
}

/**
 * Get a transaction by its cart ID.
 *
 * @since 1.32.1
 *
 * @param string $cart_id
 *
 * @return IT_Exchange_Transaction|null
 */
function it_exchange_get_transaction_by_cart_id( $cart_id ) {

	$transactions = it_exchange_get_transactions(array(
		'meta_query' => array(
			'key'   => '_it_exchange_cart_id',
			'value' => $cart_id
		)
	) );

	foreach ( $transactions as $transaction ) {
		return $transaction;
	}

	return null;
}

/**
 * Generates the transaction object used by the transaction methods
 *
 * @since 0.4.20
 *
 * @return stdClass Transaction object not an IT_Exchange_Transaction
*/
function it_exchange_generate_transaction_object() {

	// Verify products exist
	$products = it_exchange_get_cart_products();
	if ( count( $products ) < 1 ) {
		do_action( 'it_exchange_error-no_products_to_purchase' );
		it_exchange_add_message( 'error', __( 'You cannot checkout without any items in your cart.', 'it-l10n-ithemes-exchange' ) );
		return false;
	}

	// Verify cart total is a positive number
	$cart_total    = number_format( it_exchange_get_cart_total( false ), 2, '.', '' );
	$cart_sub_total = number_format( it_exchange_get_cart_subtotal( false ), 2, '.', '' );
	if ( number_format( $cart_total, 2, '', '' ) < 0 ) {
		do_action( 'it_exchange_error_negative_cart_total_on_checkout', $cart_total );
		it_exchange_add_message( 'error', __( 'The cart total must be greater than 0 for you to checkout. Please try again.', 'it-l10n-ithemes-exchange' ) );
		return false;
	}

	// Grab default currency
	$settings = it_exchange_get_option( 'settings_general' );
	$currency = $settings['default-currency'];
	unset( $settings );

	// Grab cart ID
	$cart_id = it_exchange_get_cart_data( 'cart_id' );
	$cart_id = empty( $cart_id[0] ) ? 0 : $cart_id[0];

	// Add totals to each product
	foreach( $products as $key => $product ) {
		$products[$key]['product_base_price'] = it_exchange_get_cart_product_base_price( $product, false );
		$products[$key]['product_subtotal']   = it_exchange_get_cart_product_subtotal( $product, false );
		$products[$key]['product_name']       = it_exchange_get_cart_product_title( $product );
		$products = apply_filters( 'it_exchange_generate_transaction_object_products', $products, $key, $product );
	}

	// Package it up and send it to the transaction method add-on
	$transaction_object = new stdClass();
	$transaction_object->cart_id                = $cart_id;
	$transaction_object->customer_id            = it_exchange_get_current_customer_id();
	$transaction_object->total                  = $cart_total;
	$transaction_object->sub_total              = $cart_sub_total;
	$transaction_object->currency               = $currency;
	$transaction_object->description            = it_exchange_get_cart_description();
	$transaction_object->products               = $products;

	$coupons = array();

	/** @var IT_Exchange_Coupon[] $coupon_objects */
	$coupon_objects = array();

	foreach ( it_exchange_get_applied_coupons() as $type => $type_coupons ) {

		foreach ( $type_coupons as $coupon ) {
			if ( $coupon instanceof IT_Exchange_Coupon ) {
				$coupon_objects[] = $coupon;
				$coupons[$type][] = $coupon->get_data_for_transaction_object();
			} else {
				$coupons[$type][] = $coupon;
			}
		}
	}

	$transaction_object->coupons                = $coupons;
	$transaction_object->coupons_total_discount = it_exchange_get_total_coupons_discount( 'cart', array( 'format_price' => false ));
	$transaction_object->customer_ip            = it_exchange_get_ip();

	// Tack on Tax information
	$transaction_object->taxes_formated         = apply_filters( 'it_exchange_set_transaction_objet_cart_taxes_formatted', false );
	$transaction_object->taxes_raw              = apply_filters( 'it_exchange_set_transaction_objet_cart_taxes_raw', false );

	// Tack on Shipping and Billing address
	$transaction_object->shipping_address       = it_exchange_get_cart_shipping_address();
	$transaction_object->billing_address        = apply_filters( 'it_exchange_billing_address_purchase_requirement_enabled', false ) ? it_exchange_get_cart_billing_address() : false;

	// Shipping Method and total
	$transaction_object->shipping_method        = it_exchange_get_cart_shipping_method();
	$transaction_object->shipping_method_multi  = it_exchange_get_cart_data( 'multiple-shipping-methods' );
	$transaction_object->shipping_total         = it_exchange_convert_to_database_number( it_exchange_get_cart_shipping_cost( false, false ) );

	$transaction_object = apply_filters( 'it_exchange_generate_transaction_object', $transaction_object );

	foreach ( $coupon_objects as $coupon_object ) {
		$coupon_object->use_coupon( $transaction_object );
	}

	return $transaction_object;
}

/**
 * Update a transient transaction, default expiry set to 4 hours
 *
 * @since CHANGEME
 * @param string $method name of method that created the transient
 * @param string $temp_id temporary transaction ID created by the transient
 * @param int|bool $customer_id ID of current customer
 * @param stdClass $transaction_object Object used to pass to transaction methods
 * @param string|bool $transaction_id Transaction ID of real transaction or false if no real transaction created yet
 *
 * @return bool true or false depending on success
*/
function it_exchange_update_transient_transaction( $method, $temp_id, $customer_id = false, $transaction_object, $transaction_id = false ) {

	$expires = current_time( 'timestamp' ) + apply_filters( 'it_exchange_transient_transaction_expiry', 60 * 60 * 4 );

    update_option( 'ite_temp_tnx_expires_' . $method . '_' . $temp_id, $expires, false );
    update_option( 'ite_temp_tnx_' . $method . '_' . $temp_id, array(
		    'customer_id' => $customer_id,
		    'transaction_object' => $transaction_object,
		    'transaction_id' => $transaction_id
    ), false );
    return true;
}

/**
 * Add a transient transaction
 *
 * @since 0.4.20
 *
 * @deprecated
 *
 * @param string $method name of method that created the transient
 * @param string $temp_id temporary transaction ID created by the transient
 * @param int|bool $customer_id ID of current customer
 * @param stdClass $transaction_object Object used to pass to transaction methods
 *
 * @return bool true or false depending on success
*/
function it_exchange_add_transient_transaction( $method, $temp_id, $customer_id = false, $transaction_object ) {

	_deprecated_function( 'it_exchange_add_transient_transaction', '1.31', 'it_exchange_update_transient_transaction' );

	return it_exchange_update_transient_transaction( $method, $temp_id, $customer_id, $transaction_object );
}

/**
 * Gets a transient transaction
 *
 * @since 0.4.20
 * @param string $method name of method that created the transient
 * @param string $temp_id temporary transaction ID created by the transient
 *
 * @return array|bool of customer_id and transaction_object False if expired.
*/
function it_exchange_get_transient_transaction( $method, $temp_id ) {
	$expires = get_option( 'ite_temp_tnx_expires_' . $method . '_' . $temp_id, false );
	$txn_details = get_option( 'ite_temp_tnx_' . $method . '_' . $temp_id, false );
	$now = current_time( 'timestamp' );
    if ( !empty( $txn_details ) && $now > intval( $expires ) ) {
		it_exchange_delete_transient_transaction( $method, $temp_id );
		return false;
	}
	return $txn_details;
}

/**
 * Deletes a transient transaction
 *
 * @since 0.4.20
 * @param string $method name of method that created the transient
 * @param string $temp_id temporary transaction ID created by the transient
 *
 * @return bool true or false depending on success
*/
function it_exchange_delete_transient_transaction( $method, $temp_id ) {
	delete_option( 'ite_temp_tnx_expires_' . $method . '_' . $temp_id );
	delete_option( 'ite_temp_tnx_' . $method . '_' . $temp_id );
	return true;
}

/**
 * Adds a transaction post_type to WP
 *
 * @since 0.3.3
 * @param string $method Transaction method (e.g. paypal, stripe, etc)
 * @param string $method_id ID from transaction method
 * @param string $status Transaction status
 * @param int|bool $customer Customer ID
 * @param stdClass $cart_object passed cart object
 * @param array $args same args passed to wp_insert_post plus any additional needed
 *
 * @return mixed post id or false
*/
function it_exchange_add_transaction( $method, $method_id, $status = 'pending', $customer = false, $cart_object, $args = array() ) {
	$defaults = array(
		'post_type'          => 'it_exchange_tran',
		'post_status'        => 'publish',
	);
	$args = wp_parse_args( $args, $defaults );

	if ( ! $customer ) {
		$customer = it_exchange_get_current_customer();
	} else {
		$customer = it_exchange_get_customer( $customer );
	}

	if ( it_exchange_get_transaction_by_method_id( $method, $method_id ) ) {

		do_action( 'it_exchange_add_transaction_failed', $method, $method_id, $status, $customer, $cart_object, $args );

		return apply_filters( 'it_exchange_add_transaction', false, $method, $method_id, $status, $customer, $cart_object, $args );
	}

	// If we don't have a title, create one
	if ( empty( $args['post_title'] ) )
		$args['post_title'] = $method . '-' . $method_id . '-' . date_i18n( 'Y-m-d-H:i:s' );

	// todo move to RP
	if ( $subscription_details = it_exchange_get_session_data( 'cancel_subscription' ) ) {
		foreach( $subscription_details as $cancel_subscription ) {
			if ( !empty( $cancel_subscription['old_transaction_method'] ) )
				do_action( 'it_exchange_cancel_' . $cancel_subscription['old_transaction_method'] . '_subscription', $cancel_subscription );
		}
	} else {
		it_exchange_clear_session_data( 'cancel_subscription' ); // just in case, we don't want any lingering
	}

	if ( $transaction_id = wp_insert_post( $args ) ) {
		update_post_meta( $transaction_id, '_it_exchange_transaction_method',    $method );
		update_post_meta( $transaction_id, '_it_exchange_transaction_method_id', $method_id );
		update_post_meta( $transaction_id, '_it_exchange_transaction_status',    $status );
		update_post_meta( $transaction_id, '_it_exchange_customer_id',           $customer ? $customer->id : false );
		update_post_meta( $transaction_id, '_it_exchange_customer_ip',           ! empty( $cart_object->customer_ip ) ? $cart_object->customer_ip : it_exchange_get_ip() );
		update_post_meta( $transaction_id, '_it_exchange_cart_object',           $cart_object );
		update_post_meta( $transaction_id, '_it_exchange_cart_id',               $cart_object->cart_id );

		// Transaction Hash for confirmation lookup
		$hash =  it_exchange_generate_transaction_hash( $transaction_id, $customer ? $customer->id  : false );
		update_post_meta( $transaction_id, '_it_exchange_transaction_hash', $hash );

		do_action( 'it_exchange_add_transaction_success', $transaction_id );
		if ( $products = it_exchange_get_transaction_products( $transaction_id ) ) {
			// Loop through products
			foreach( $products as $cart_id => $data ) {
				$product = new IT_Exchange_Product( $data['product_id'] );
				$product->add_transaction_to_product( $transaction_id );

			}
		}

		if ( $customer instanceof IT_Exchange_Customer ) {
			$customer->add_transaction_to_user( $transaction_id );
		}

		return apply_filters( 'it_exchange_add_transaction', $transaction_id, $method, $method_id, $status, $customer, $cart_object, $args );
	}
	do_action( 'it_exchange_add_transaction_failed', $method, $method_id, $status, $customer, $cart_object, $args );

	return apply_filters( 'it_exchange_add_transaction', false, $method, $method_id, $status, $customer, $cart_object, $args);
}

/**
 * Adds a transaction post_type to WP
 * Slimmed down "child" of a parent transaction
 *
 * @since 1.3.0
 * @param string $method Transaction method (e.g. paypal, stripe, etc)
 * @param string $method_id ID from transaction method
 * @param string $status Transaction status
 * @param int $customer_id Customer ID
 * @param int $parent_tx_id Parent Transaction ID
 * @param stdClass $cart_object really just a dummy array to store the price information
 * @param array $args same args passed to wp_insert_post plus any additional needed
 *
 * @return int|bool post id or false
*/
function it_exchange_add_child_transaction( $method, $method_id, $status = 'pending', $customer_id, $parent_tx_id, $cart_object, $args = array() ) {
	$defaults = array(
		'post_type'          => 'it_exchange_tran',
		'post_status'        => 'publish',
	);
	$args = wp_parse_args( $args, $defaults );

	// If we don't have a title, create one
	if ( empty( $args['post_title'] ) )
		$args['post_title'] = $method . '-' . $method_id . '-' . date_i18n( 'Y-m-d-H:i:s' );

	$args['post_parent'] = $parent_tx_id;

	if ( $transaction_id = wp_insert_post( $args ) ) {
		update_post_meta( $transaction_id, '_it_exchange_transaction_method',    $method );
		update_post_meta( $transaction_id, '_it_exchange_transaction_method_id', $method_id );
		update_post_meta( $transaction_id, '_it_exchange_transaction_status',    $status );
		update_post_meta( $transaction_id, '_it_exchange_customer_id',           $customer_id );
		update_post_meta( $transaction_id, '_it_exchange_parent_tx_id',          $parent_tx_id );
		update_post_meta( $transaction_id, '_it_exchange_cart_object',           $cart_object );

		do_action( 'it_exchange_add_child_transaction_success', $transaction_id );
		return apply_filters( 'it_exchange_add_child_transaction', $transaction_id, $method, $method_id, $status, $customer_id, $parent_tx_id, $cart_object, $args );
	}
	do_action( 'it_exchange_add_child_transaction_failed', $method, $method_id, $status, $customer_id, $parent_tx_id, $cart_object, $args );
	return apply_filters( 'it_exchange_add_child_transaction', false, $method, $method_id, $status, $customer_id, $parent_tx_id, $cart_object, $args );
}

/**
 * Generates a unique transaction ID for receipts
 *
 * @since 0.4.0
 *
 * @param integer   $transaction_id the wp_post ID for the transaction
 * @param integer  $customer_id the wp_users ID for the customer
 *
 * @return string
*/
function it_exchange_generate_transaction_hash( $transaction_id, $customer_id ) {
	// Targeted hash
	$hash = wp_hash( time() . $transaction_id . $customer_id );
	if ( it_exchange_get_transaction_id_from_hash( $hash ) )
		$hash = it_exchange_generate_transaction_hash( $transaction_id, $customer_id );

	return apply_filters( 'it_exchange_generate_transaction_hash', $hash, $transaction_id, $customer_id );
}

/**
 * Return the transaction ID provided by the gateway (transaction method)
 *
 * @since 0.4.0
 *
 * @param WP_Post|int|IT_Exchange_Transaction $transaction ID or object
 *
 * @return string|void
*/
function it_exchange_get_gateway_id_for_transaction( $transaction ) {
	if ( ! $transaction = it_exchange_get_transaction( $transaction ) )
		return;

	$gateway_transaction_id = $transaction->get_gateway_id_for_transaction();
	return apply_filters( 'it_exchange_get_gateway_id_for_transaction', $gateway_transaction_id, $transaction );
}

/**
 * Returns a transaction ID based on the hash
 *
 * @since 0.4.0
 *
 * @param string $hash
 *
 * @return integer transaction id
*/
function it_exchange_get_transaction_id_from_hash( $hash ) {
	global $wpdb;
	if ( $transaction_id = $wpdb->get_var( $wpdb->prepare( "SELECT post_id FROM $wpdb->postmeta WHERE meta_key = %s AND meta_value = %s LIMIT 1;", '_it_exchange_transaction_hash', $hash ) ) )
		return apply_filters( 'it_exchange_get_transaction_id_from_hash', $transaction_id, $hash );

	return apply_filters( 'it_exchange_get_transaction_id_from_hash', false, $hash );
}

/**
 * Returns the transaction hash from an ID
 *
 * @since 0.4.0
 *
 * @param integer $id transaction_id
 *
 * @return string|bool ID or false
*/
function it_exchange_get_transaction_hash( $id ) {
	return apply_filters( 'it_exchange_get_transaction_hash', get_post_meta( $id, '_it_exchange_transaction_hash', true ), $id );
}

/**
 * Updates a transaction
 *
 * @since 0.3.3
 *
 * @deprecated 1.35
 *
 * @param array $args transaction args. Must include ID of a valid transaction post
 *
 * @return WP_Post transaction object
*/
function it_exchange_update_transaction( $args ) {

	_deprecated_function( 'it_exchange_update_transaction', '1.35' );

	$id = empty( $args['id'] ) ? false : $args['id'];
	$id = ( empty( $id ) && ! empty( $args['ID'] ) ) ? $args['ID']: $id;

	if ( 'it_exchange_tran' != get_post_type( $id ) )
		return false;

	$args['ID'] = $id;

	$result = wp_update_post( $args );
	$transaction_method = it_exchange_get_transaction_method( $id );

	do_action( 'it_exchange_update_transaction', $args );
	do_action( 'it_exchange_update_transaction_' . $transaction_method, $args );

	if ( ! empty( $args['_it_exchange_transaction_status'] ) )
		it_exchange_update_transaction_status( $id, $args['_it_exchange_transaction_status'] );

	return $result;
}

/**
 * Updates the transaction status of a transaction
 *
 * @since 0.3.3
 * @param WP_Post|int|IT_Exchange_Transaction $transaction the transaction id or object
 * @param string $status the new transaction status
 *
 * @return string|bool Status or false
*/
function it_exchange_update_transaction_status( $transaction, $status ) {

	$transaction = it_exchange_get_transaction( $transaction );

	if ( ! $transaction )
		return false;

	$old_status         = $transaction->get_status();
	$old_status_cleared = it_exchange_transaction_is_cleared_for_delivery( $transaction );
	$transaction->update_status( $status );

	do_action( 'it_exchange_update_transaction_status', $transaction, $old_status, $old_status_cleared, $status );
	do_action( 'it_exchange_update_transaction_status_' . $transaction->transaction_method, $transaction, $old_status, $old_status_cleared, $status );
	return $transaction->get_status();
}

/**
 * Returns the transaction status for a specific transaction
 *
 * @since 0.3.3
 * @param WP_Post|int|IT_Exchange_Transaction $transaction the transaction id or object
 *
 * @return string the transaction status
*/
function it_exchange_get_transaction_status( $transaction ) {
    $transaction = it_exchange_get_transaction( $transaction );
	$transaction_status = empty( $transaction->status ) ? false : $transaction->status;
    return apply_filters( 'it_exchange_get_transaction_status', $transaction_status, $transaction );
}

/**
 * Grab a list of all possible transactions stati
 *
 * @since 0.4.11
 *
 * @param WP_Post|int|IT_Exchange_Transaction $transaction transaction id or object
 *
 * @return array
*/
function it_exchange_get_status_options_for_transaction( $transaction ) {
	if ( ! $method = it_exchange_get_transaction_method( $transaction ) )
		return array();
	return apply_filters( 'it_exchange_get_status_options_for_' . $method . '_transaction', array(), $transaction );
}

/**
 * Return the default transaction status for a transaction
 *
 * Leans on transaction methods to do the work
 *
 * @since 0.4.11
 *
 * @param WP_Post|int|IT_Exchange_Transaction $transaction id or object
 *
 * @return string|bool Status or false
*/
function it_exchange_get_default_transaction_status( $transaction ) {
	if ( ! $method = it_exchange_get_transaction_method( $transaction ) )
		return false;
	return apply_filters( 'it_exchange_get_default_transaction_status_for_' . $method, false );
}

/**
 * Returns the label for a transaction status (provided by addon)
 *
 * @since 0.4.0
 *
 * @param WP_Post|int|IT_Exchange_Transaction $transaction transaction object or ID
 * @param array $options
 *
 * @return string
*/
function it_exchange_get_transaction_status_label( $transaction, $options=array() ){
	$transaction = it_exchange_get_transaction( $transaction );
	$defaults = array(
		'status' => it_exchange_get_transaction_status( $transaction ),
	);
	$options = ITUtility::merge_defaults( $options, $defaults );
	return apply_filters( 'it_exchange_transaction_status_label_' . $transaction->transaction_method, $options['status'], $options );
}

/**
 * Returns the instructions for a transaction instructions (provided by addon)
 *
 * @since 0.4.0
 *
 * @param WP_Post|int|IT_Exchange_Transaction $transaction transaction object or ID
 *
 * @return string
*/
function it_exchange_get_transaction_instructions( $transaction ){
	$transaction = it_exchange_get_transaction( $transaction );
	return apply_filters( 'it_exchange_transaction_instructions_' . $transaction->transaction_method, '' );
}

/**
 * Return the transaction date
 *
 * @since 0.4.0
 *
 * @param WP_Post|int|IT_Exchange_Transaction $transaction ID or object
 * @param string|bool  $format php date format
 * @param boolean $gmt return the gmt date?
 *
 * @return string date
*/
function it_exchange_get_transaction_date( $transaction, $format=false, $gmt=false ) {
	$format = empty( $format ) ? get_option( 'date_format' ) : $format;

	// Try to locate the IT_Exchange_Transaction object from the var
	if ( $transaction = it_exchange_get_transaction( $transaction ) ) {
		if ( $date = $transaction->get_date() )
			return apply_filters( 'it_exchange_get_transaction_date', date_i18n( $format, strtotime( $date ), $gmt ), $transaction, $format, $gmt );
	}

	return apply_filters( 'it_exchange_get_transaction_date', false, $transaction, $format, $gmt );
}

/**
 * Return the transaction subtotal
 *
 * @since 0.4.0
 *
 * @param WP_Post|int|IT_Exchange_Transaction $transaction ID or object
 * @param bool $format_currency Format the price or just the raw value
 *
 * @return string date
*/
function it_exchange_get_transaction_subtotal( $transaction, $format_currency=true ) {

	$subtotal = false;

	// Try to locate the IT_Exchange_Transaction object from the var
	if ( $transaction = it_exchange_get_transaction( $transaction ) ) {
		$subtotal = $transaction->get_subtotal();

		$subtotal = $format_currency ? it_exchange_format_price( $subtotal ) : $subtotal;
	}

	return apply_filters( 'it_exchange_get_transaction_subtotal', $subtotal, $transaction, $format_currency );
}

/**
 * Return the transaction total
 *
 * @since 0.4.0
 *
 * @param WP_Post|int|IT_Exchange_Transaction $transaction ID or object
 * @param boolean $format_currency format the price?
 * @param boolean $subtract_refunds if refunds are present, subtract the difference?
 *
 * @return string total
*/
function it_exchange_get_transaction_total( $transaction, $format_currency=true, $subtract_refunds=true ) {
	// Try to locate the IT_Exchange_Transaction object from the var
	if ( $transaction = it_exchange_get_transaction( $transaction ) ) {
		$total = $transaction->get_total( $subtract_refunds );
		$total = $format_currency ? it_exchange_format_price( $total ) : $total;
		return apply_filters( 'it_exchange_get_transaction_total', $total, $transaction, $format_currency, $subtract_refunds );
	}

	return apply_filters( 'it_exchange_get_transaction_total', false, $transaction, $format_currency, $subtract_refunds );
}

/**
 * Return the currency used in the transaction
 *
 * @since 0.4.0
 *
 * @param WP_Post|int|IT_Exchange_Transaction   $transaction ID or object
 *
 * @return string|bool Currency
*/
function it_exchange_get_transaction_currency( $transaction ) {
	// Try to locate the IT_Exchange_Transaction object from the var
	if ( $transaction = it_exchange_get_transaction( $transaction ) )
		return apply_filters( 'it_exchange_get_transaction_currency', $transaction->get_currency(), $transaction );

	return apply_filters( 'it_exchange_get_transaction_currency', false, $transaction );
}

/**
 * Returns an array of all coupons applied to a given transaction
 *
 * @since 0.4.0
 *
 * @param WP_Post|int|IT_Exchange_Transaction   $transaction ID or object
 *
 * @return string date
*/
function it_exchange_get_transaction_coupons( $transaction ) {
	// Try to locate the IT_Exchange_Transaction object from the var
	if ( $transaction = it_exchange_get_transaction( $transaction ) )
		return apply_filters( 'it_exchange_get_transaction_coupons', $transaction->get_coupons(), $transaction );

	return apply_filters( 'it_exchange_get_transaction_coupons', false, $transaction );
}

/**
 * Return the total discount of all coupons applied to a given transaction
 *
 * @since 0.4.0
 *
 * @param WP_Post|int|IT_Exchange_Transaction $transaction ID or object
 * @param bool $format Format the price
 *
 * @return string date
*/
function it_exchange_get_transaction_coupons_total_discount( $transaction, $format = true ) {
	if ( $transaction = it_exchange_get_transaction( $transaction ) ) {

		$total_discount = $transaction->get_coupons_total_discount();

		if ( $format ) {
			$total_discount = it_exchange_format_price( $total_discount );
		}

		return apply_filters( 'it_exchange_get_transaction_coupons_total_discount', $total_discount, $transaction, $format );
	}

	return apply_filters( 'it_exchange_get_transaction_coupons_total_discount', false, $transaction, $format );
}

/**
 * Adds a refund to a transaction
 *
 * @since 0.4.0
 *
 * @param WP_Post|int|IT_Exchange_Transaction $transaction transaction ID or object
 * @param string $amount
 * @param bool|string $date Date in Y-m-d H:i:s format
 * @param array $options
*/
function it_exchange_add_refund_to_transaction( $transaction, $amount, $date=false, $options=array() ) {
	if ( $transaction = it_exchange_get_transaction( $transaction ) )
		$transaction->add_refund( $amount, $date, $options );
	do_action( 'it_exchange_add_refund_to_transaction', $transaction, $amount, $date, $options );
}

/**
 * Grab refunds for a transaction
 *
 * @since 0.4.0
 *
 * @param WP_Post|int|IT_Exchange_Transaction $transaction ID or object
 *
 * @return array
*/
function it_exchange_get_transaction_refunds( $transaction ) {
	if ( $transaction = it_exchange_get_transaction( $transaction ) )
		return apply_filters( 'it_exchange_get_transaction_refunds', $transaction->get_transaction_refunds(), $transaction );

	return apply_filters( 'it_exchange_get_transaction_refunds', false, $transaction );
}

/**
 * Checks if there are refunds for a transaction
 *
 * @since 0.4.0
 *
 * @param WP_Post|int|IT_Exchange_Transaction $transaction ID or object
 *
 * @return bool
*/
function it_exchange_has_transaction_refunds( $transaction ) {

	$refunds = (bool) it_exchange_get_transaction_refunds( $transaction );

	return apply_filters( 'it_exchange_has_transaction_refunds', $refunds, $transaction );
}

/**
 * Returns the a sum of all the applied refund amounts for this transaction
 *
 * @since 0.4.0
 *
 * @param WP_Post|int|IT_Exchange_Transaction $transaction ID or object
 * @param bool $format Format the price
 *
 * @return mixed
*/
function it_exchange_get_transaction_refunds_total( $transaction, $format = true ) {
	$refunds = it_exchange_get_transaction_refunds( $transaction );
	$total_refund = 0;
	foreach ( $refunds as $refund ) {
		$total_refund += $refund['amount'];
	}
	$total_refund = ( $format ) ? it_exchange_format_price( $total_refund ) : $total_refund;
	return apply_filters( 'it_exchange_get_transaction_refunds_total', $total_refund, $transaction, $format );
}

/**
 * Returns the transaction description
 *
 * @since 0.4.0
 *
 * @param WP_Post|int|IT_Exchange_Transaction $transaction ID or object
 *
 * @return string
*/
function it_exchange_get_transaction_description( $transaction ) {
	if ( $transaction = it_exchange_get_transaction( $transaction ) )
		return apply_filters( 'it_exchange_get_transaction_description', $transaction->get_description(), $transaction );

	return apply_filters( 'it_exchange_get_transaction_description', __( 'Unknown', 'it-l10n-ithemes-exchange' ), $transaction );
}

/**
 * Returns the customer object associated with a transaction
 *
 * @since 0.4.0
 *
 * @param WP_Post|int|IT_Exchange_Transaction $transaction ID or object
 *
 * @return IT_Exchange_Customer|false
*/
function it_exchange_get_transaction_customer( $transaction ) {

	if ( $transaction = it_exchange_get_transaction( $transaction ) ) {
		$customer = empty( $transaction->customer_id ) ? false : it_exchange_get_customer( $transaction->customer_id );
		return apply_filters( 'it_exchange_get_transaction_customer', $customer, $transaction );
	}

	return apply_filters( 'it_exchange_get_transaction_customer', false, $transaction );
}

/**
 * Returns the transaction customer's Display Name
 *
 * @since 0.4.0
 *
 * @param WP_Post|int|IT_Exchange_Transaction $transaction ID or object
 *
 * @return string
*/
function it_exchange_get_transaction_customer_display_name( $transaction ) {
	$unknown = __( 'Deleted Customer', 'it-l10n-ithemes-exchange' );

	if ( $customer = it_exchange_get_transaction_customer( $transaction ) ) {
		$display_name = empty( $customer->wp_user->display_name ) ? $unknown : $customer->wp_user->display_name;
		return apply_filters( 'it_exchange_get_transaction_customer_display_name', $display_name, $transaction );
	}

	return apply_filters( 'it_exchange_get_transaction_customer_display_name', $unknown, $transaction );
}

/**
 * Returns the transaction customer's ID
 *
 * @since 0.4.0
 *
 * @param WP_Post|int|IT_Exchange_Transaction $transaction ID or object
 *
 * @return int|false
*/
function it_exchange_get_transaction_customer_id( $transaction ) {
	$unknown = 0;

	if ( $customer = it_exchange_get_transaction_customer( $transaction ) )
		return apply_filters( 'it_exchange_get_transaction_customer_id', empty( $customer->wp_user->ID ) ? $unknown : $customer->wp_user->ID, $transaction );

	return apply_filters( 'it_exchange_get_transaction_customer_id', $unknown, $transaction );
}

/**
 * Returns the transaction customer's email
 *
 * @since 0.4.0
 *
 * @param WP_Post|int|IT_Exchange_Transaction $transaction ID or object
 *
 * @return string
*/
function it_exchange_get_transaction_customer_email( $transaction ) {
	$unknown = __( 'Unknown', 'it-l10n-ithemes-exchange' );

	if ( $customer = it_exchange_get_transaction_customer( $transaction ) )
		return apply_filters( 'it_exchange_get_transaction_customer_email', empty( $customer->wp_user->user_email ) ? $unknown : $customer->wp_user->user_email, $transaction );

	return apply_filters( 'it_exchange_get_transaction_customer_email', $unknown, $transaction );
}

/**
 * Returns the transaction customer's IP Address
 *
 * @since 1.11.5
 *
 * @param WP_Post|int|IT_Exchange_Transaction $transaction ID or object
 * @param bool                                $label Label or raw value.
 *
 * @return string
*/
function it_exchange_get_transaction_customer_ip_address( $transaction, $label = true ) {

	$transaction = it_exchange_get_transaction( $transaction );

	$return = $label ? __( 'IP Address: %s', 'it-l10n-ithemes-exchange' ) : '%s';

	$ip = get_post_meta( $transaction->ID, '_it_exchange_customer_ip', true );

	if ( ! empty( $ip ) ) {
		return sprintf( $return, $ip );
	}

	return sprintf( $return, __( 'Unknown', 'it-l10n-ithemes-exchange' ) );
}

/**
 * Returns the transaction customer's profile URL
 *
 * @since 0.4.0
 *
 * @param WP_Post|int|IT_Exchange_Transaction $transaction ID or object
 * @param array $options
 *
 * @return string
*/
function it_exchange_get_transaction_customer_admin_profile_url( $transaction, $options=array() ) {
	if ( ! $customer = it_exchange_get_transaction_customer( $transaction ) )
		return false;

	$defaults = array(
		'tab' => 'transactions',
	);
	$options = ITUtility::merge_defaults( $options, $defaults );

	$url = add_query_arg( array(
		'user_id' => $customer->id,
		'it_exchange_customer_data' => 1,
		'tab' => $options['tab'] ),
		admin_url( 'user-edit.php' ) );

	return apply_filters( 'it_exchange_get_transaction_customer_admin_profile_url', $url, $transaction, $options );
}

/**
 * Get Transaction Order Number
 *
 * @since 0.4.0
 *
 * @param WP_Post|int|IT_Exchange_Transaction $transaction id or object
 * @param string $prefix What to prefix the order number with, defaults to #
 *
 * @return string
*/
function it_exchange_get_transaction_order_number( $transaction, $prefix='#' ) {
	if ( ! $transaction = it_exchange_get_transaction( $transaction ) )
		return false;

	// Translate default prefix
	$prefix = ( '#' == $prefix ) ? __( '#', 'it-l10n-ithemes-exchange' ) : $prefix;

	$order_number = sprintf( '%06d', $transaction->ID );
	$order_number = empty( $prefix ) ? $order_number : $prefix . $order_number;

	return apply_filters( 'it_exchange_get_transaction_order_number', $order_number, $transaction, $prefix );
}

/**
 * Returns the shipping addresss saveed with the transaction
 *
 * @since 1.4.0
 *
 * @param WP_Post|int|IT_Exchange_Transaction $transaction transaction object or ID
 *
 * @return array|bool shipping address
*/
function it_exchange_get_transaction_shipping_address( $transaction ) {
	if ( ! $transaction = it_exchange_get_transaction( $transaction ) )
		return false;

	$shipping_address = empty( $transaction->cart_details->shipping_address ) ? false: $transaction->cart_details->shipping_address;

	return apply_filters( 'it_exchange_get_transaction_shipping_address', $shipping_address, $transaction );
}

/**
 * Returns the billing addresss saveed with the transaction
 *
 * @since 1.3.0
 *
 * @param WP_Post|int|IT_Exchange_Transaction $transaction transaction object or ID
 *
 * @return array|bool billing address
*/
function it_exchange_get_transaction_billing_address( $transaction ) {
	if ( ! $transaction = it_exchange_get_transaction( $transaction ) )
		return false;

	$billing_address = empty( $transaction->cart_details->billing_address ) ? false: $transaction->cart_details->billing_address;

	return apply_filters( 'it_exchange_get_transaction_billing_address', $billing_address, $transaction );
}

/**
 * Returns an array of product objects as they existed when added to the transaction
 *
 * @since 0.4.0
 *
 * @param WP_Post|int|IT_Exchange_Transaction $transaction id or object
 *
 * @return array
*/
function it_exchange_get_transaction_products( $transaction ) {
	if ( ! $transaction = it_exchange_get_transaction( $transaction ) )
		return apply_filters( 'it_exchange_get_transaction_products', array(), $transaction );

	return $transaction->get_products();
}

/**
 * Returns a specific product from a transaction based on the product_cart_id
 *
 * @since 0.4.0
 *
 * @param WP_Post|int|IT_Exchange_Transaction $transaction
 * @param string $product_cart_id
 *
 * @return object
*/
function it_exchange_get_transaction_product( $transaction, $product_cart_id ) {
	if ( $products = it_exchange_get_transaction_products( $transaction ) )
		return apply_filters( 'it_exchange_get_transaction_product', empty( $products[$product_cart_id] ) ? false : $products[$product_cart_id], $transaction, $product_cart_id );

	return apply_filters( 'it_exchange_get_transaction_product', false, $transaction, $product_cart_id );
}

/**
 * Returns data from the transaction product
 *
 * @since 0.4.0
 *
 * @param array $product
 * @param string $feature
 *
 * @return mixed
*/
function it_exchange_get_transaction_product_feature( $product, $feature ) {

	if ( 'title' == $feature || 'name' == $feature )
		$feature = 'product_name';

	$feature_value = isset( $product[$feature] ) ? $product[$feature] : '';

	return apply_filters( 'it_exchange_get_transaction_product_feature', $feature_value, $product, $feature );
}

/**
 * Returns the transaction method name from the add-on's slug
 *
 * @since 0.3.7
 *
 * @param string $slug
 *
 * @return string
*/
function it_exchange_get_transaction_method_name_from_slug( $slug ) {
	if ( $method = it_exchange_get_addon( $slug ) )
		return apply_filters( 'it_exchange_get_transaction_method_name_' . $slug, $method['name'] );

	return apply_filters( 'it_exchange_get_transaction_method_name_' . $slug, $slug );
}

/**
 * Returns the name of a transaction method used for a specific transaction
 *
 * @since 0.4.0
 *
 * @param WP_Post|int|IT_Exchange_Transaction $transaction ID or object
 *
 * @return string
*/
function it_exchange_get_transaction_method_name( $transaction ) {
	if ( $slug = it_exchange_get_transaction_method( $transaction ) )
		return apply_filters( 'it_exchange_get_transaction_method_name', it_exchange_get_transaction_method_name_from_slug( $slug ), $transaction );

	return apply_filters( 'it_exchange_get_transaction_method_name', false, $transaction );
}

/**
 * Updates the ID of a transaction method used for a specific transaction
 *
 * @since 0.4.0
 *
 * @param WP_Post|int|IT_Exchange_Transaction $transaction ID or object
 * @param string $method_id ID from the transaction method
 *
 * @return string
*/
function it_exchange_update_transaction_method_id( $transaction, $method_id ) {
	$transaction = it_exchange_get_transaction( $transaction );
	return update_post_meta( $transaction->ID, '_it_exchange_transaction_method_id', $method_id );
}

/**
 * Updates the Cart Object of a transaction
 *
 * @since 0.4.0
 *
 * @param WP_Post|int|IT_Exchange_Transaction $transaction ID or object
 * @param object $cart_object Cart Object for specific transaction
 *
 * @return string
*/
function it_exchange_update_transaction_cart_object( $transaction, $cart_object ) {
	$transaction = it_exchange_get_transaction( $transaction );
	return update_post_meta( $transaction->ID, '_it_exchange_cart_object', $cart_object );
}

/**
 * Returns the ID of a transaction method used for a specific transaction
 *
 * @since 0.4.0
 *
 * @param WP_Post|int|IT_Exchange_Transaction $transaction ID or object
 *
 * @return string
*/
function it_exchange_get_transaction_method_id( $transaction ) {

	$transaction = it_exchange_get_transaction( $transaction );

	return apply_filters( 'it_exchange_get_transaction_method_id', $transaction->get_gateway_id_for_transaction(), $transaction );
}

/**
 * For processing a transaction
 *
 * @since 0.3.7
 *
 * @param string $method
 * @param object $transaction_object
 *
 * @return mixed
*/
function it_exchange_do_transaction( $method, $transaction_object ) {
	return apply_filters( 'it_exchange_do_transaction_' . $method, false, $transaction_object );
}

/**
 * Does the given transaction have a status that warants delivery of product(s)
 *
 * Returns true/false. Rely on transaction method addon to give us that. Default is false.
 *
 * @since 0.4.2
 *
 * @param WP_Post|int|IT_Exchange_Transaction $transaction id or object
 *
 * @return boolean
*/
function it_exchange_transaction_is_cleared_for_delivery( $transaction ) {
	if ( ! $transaction = it_exchange_get_transaction( $transaction ) )
		return false;

	$transaction_method = it_exchange_get_transaction_method( $transaction );
	return apply_filters( 'it_exchange_' . $transaction_method . '_transaction_is_cleared_for_delivery', false, $transaction );
}

/**
 * Returns the make-payment action
 *
 * Leans on tranasction_method to actually provide it.
 *
 * @since 0.4.0
 *
 * @param string $transaction_method slug registered with addon
 * @param array $options
 *
 * @return mixed
*/
function it_exchange_get_transaction_method_make_payment_button ( $transaction_method, $options=array() ) {
	return apply_filters( 'it_exchange_get_' . $transaction_method . '_make_payment_button', '', $options );
}

/**
 * Grab all registered webhook / IPN keys
 *
 * @since 0.4.0
 *
 * @return array
*/
function it_exchange_get_webhooks() {
	$webhooks = empty( $GLOBALS['it_exchange']['webhooks'] ) ? array() : (array) $GLOBALS['it_exchange']['webhooks'];
	return apply_filters( 'it_exchange_get_webhooks', $webhooks );
}

/**
 * Register a webhook / IPN key
 *
 * @since 0.4.0
 *
 * @param string $key   the addon slug or ID
 * @param string $param the REQUEST param we are listening for
 *
 * @return void
*/
function it_exchange_register_webhook( $key, $param ) {
	$GLOBALS['it_exchange']['webhooks'][$key] = $param;
	do_action( 'it_exchange_register_webhook', $key, $param );
}

/**
 * Grab a specific registered webhook / IPN param
 *
 * @since 0.4.0
 *
 * @param string $key the key for the param we are looking for
 *
 * @return string|bool or false
*/
function it_exchange_get_webhook( $key ) {
	$webhooks = it_exchange_get_webhooks();
	$webhook = empty( $GLOBALS['it_exchange']['webhooks'][$key] ) ? false : $GLOBALS['it_exchange']['webhooks'][$key];
	return apply_filters( 'it_exchange_get_webhook', $webhook, $key );
}

/**
 * Check what webhook is being processed.
 *
 * @since 1.34
 *
 * @param string $webhook Optionally, specify the webhook to compare against.
 *
 * @return bool|string
 */
function it_exchange_doing_webhook( $webhook = '' ) {

	if ( $webhook ) {
		$param = it_exchange_get_webhook( $webhook );

		if ( ! empty( $_REQUEST[$param] ) ) {
			return true;
		}

		return false;
	}

	foreach ( it_exchange_get_webhooks() as $key => $param ) {

		if ( ! empty( $_REQUEST[$param] ) ) {
			return $key;
		}
	}

	return false;
}

/**
 * Get the confirmation URL for a transaction
 *
 * @since 0.4.0
 *
 * @param integer $transaction_id id of the transaction
 *
 * @return string url
*/
function it_exchange_get_transaction_confirmation_url( $transaction_id ) {
	// If we can't grab the hash, return false
	if ( ! $transaction_hash = it_exchange_get_transaction_hash( $transaction_id ) )
		return apply_filters( 'it_exchange_get_transaction_confirmation_url', false, $transaction_id );

	// Get base page URL
	$confirmation_url = it_exchange_get_page_url( 'confirmation' );

	if ( '' != get_option( 'permalink_structure' ) ) {
		$confirmation_url = trailingslashit( $confirmation_url ) . $transaction_hash;
	} else {
		$slug             = it_exchange_get_page_slug( 'confirmation' );
		$confirmation_url = remove_query_arg( $slug, $confirmation_url );
		$confirmation_url = add_query_arg( $slug, $transaction_hash, $confirmation_url );
	}

	return apply_filters( 'it_exchange_get_transaction_confirmation_url', $confirmation_url, $transaction_id );
}

/**
 * Can this transaction status be manually updated?
 *
 * @since 0.4.11
 *
 * @param WP_Post|int|IT_Exchange_Transaction $transaction the id or object
 *
 * @return boolean
*/
function it_exchange_transaction_status_can_be_manually_changed( $transaction ) {
	if( ! $method = it_exchange_get_transaction_method( $transaction ) )
		return false;
	return apply_filters( 'it_exchange_' . $method . '_transaction_status_can_be_manually_changed', false );
}

/**
 * Does this transaction include shipping details
 *
 * @since 1.4.0
 *
 * @param WP_Post|int|IT_Exchange_Transaction $transaction the id or object
 *
 * @return boolean
*/
function it_exchange_transaction_includes_shipping( $transaction ) {
	$includes_shipping = it_exchange_get_transaction_shipping_method( $transaction );
	$includes_shipping = ! empty( $includes_shipping->label );
	return apply_filters( 'it_exchange_transaction_includes_shipping', $includes_shipping, $transaction );
}

/**
 * Return the total for shipping for this transaction
 *
 * @since 1.4.0
 *
 * @param WP_Post|int|IT_Exchange_Transaction $transaction the id or object
 * @param bool $format_price
 *
 * @return string
*/
function it_exchange_get_transaction_shipping_total( $transaction, $format_price=false ) {
	if( ! $transaction= it_exchange_get_transaction( $transaction ) )
		return false;

	$shipping_total = empty( $transaction->cart_details->shipping_total ) ? false : it_exchange_convert_from_database_number( $transaction->cart_details->shipping_total );
	if ( ! empty( $shipping_total ) && $format_price )
		$shipping_total = it_exchange_format_price( $shipping_total );

	return apply_filters( 'it_exchange_get_transaction_shipping_total', $shipping_total, $transaction );
}

/**
 * Returns the shipping method object used with this transaction
 *
 * If Multiple Methods was used, returns a stdClass with slug and label properties
 *
 * @since 1.4.0
 *
 * @param WP_Post|int|IT_Exchange_Transaction $transaction the id or object
 *
 * @return object|boolean
*/
function it_exchange_get_transaction_shipping_method( $transaction ) {
	if( ! $transaction= it_exchange_get_transaction( $transaction ) )
		return false;

	$shipping_method = empty( $transaction->cart_details->shipping_method ) ? false : $transaction->cart_details->shipping_method;

	// If Multiple, Just return the string since its not a registered method
	if ( 'multiple-methods' == $shipping_method ) {
		$method = new stdClass();
		$method->slug  = 'multiple-methods';
		$method->label = __( 'Multiple Shipping Methods', 'it-l10n-ithemes-exchange' );
		return apply_filters( 'it_exchange_get_transaction_shipping_method', $method, $transaction );
	}

	$shipping_method = it_exchange_get_registered_shipping_method( $shipping_method );
	return apply_filters( 'it_exchange_get_transaction_shipping_method', $shipping_method, $transaction );
}

/**
 * Prints Shipping Method used for a specific product in the transaction
 *
 * @since 1.4.0
 *
 * @param WP_Post|int|IT_Exchange_Transaction $transaction
 * @param int $product_cart_id
 *
 * @return string
*/
function it_exchange_get_transaction_shipping_method_for_product( $transaction, $product_cart_id ) {
	if( ! $transaction= it_exchange_get_transaction( $transaction ) )
		return false;

	$transaction_method = it_exchange_get_transaction_shipping_method( $transaction );
	if ( 'multiple-methods' == $transaction_method->slug ) {
		$product_method = empty( $transaction->cart_details->shipping_method_multi[$product_cart_id] ) ? false : $transaction->cart_details->shipping_method_multi[$product_cart_id];
		$product_method = it_exchange_get_registered_shipping_method( $product_method );
		$method = empty( $product_method->label ) ? __( 'Unknown Method', 'it-l10n-ithemes-exchange' ) : $product_method->label;
	} else {
		$method = $transaction_method->label;
	}

	return apply_filters( 'it_exchange_get_transaction_shipping_method_for_product', $method, $transaction, $product_cart_id );

}
