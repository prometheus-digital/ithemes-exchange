<?php
/**
 * API Functions for Transaction Method Add-ons
 *
 * In addition to the functions found below, iThemes Exchange offers the following actions related to transactions
 * - it_exchange_save_transaction_unvalidated		                 // Runs every time an iThemes Exchange transaction is saved.
 * - it_exchange_save_transaction_unavalidate-[transaction-method] // Runs every time a specific iThemes Exchange transaction method is saved.
 * - it_exchange_save_transaction                                  // Runs every time an iThemes Exchange transaction is saved if not an autosave and if user has permission to save post
 * - it_exchange_save_transaction-[transaction-method]             // Runs every time a specific iThemes Exchange transaction method is saved if not an autosave and if user has permission to save transaction
 *
 * @package IT_Exchange
 * @since 0.3.3
*/

/**
 * Grabs the transaction method of a transaction
 *
 * @since 0.3.3
 * @return string the transaction method
*/
function it_exchange_get_transaction_method( $transaction=false ) {
	if ( is_object( $transaction ) && 'IT_Exchange_Transaction' == get_class( $transaction ) )
		return $transaction->transaction_method;

	if ( ! $transaction ) {
		global $post;
		$transaction = $post;
	}

	// Return value from IT_Exchange_Transaction if we are able to locate it
	$transaction = it_exchange_get_transaction( $transaction );
	if ( is_object( $transaction ) && ! empty ( $transaction->transaction_method ) )
		return $transaction->transaction_method;

	// Return query arg if is present
	if ( ! empty ( $_GET['transaction-method'] ) )
		return $_GET['transaction-method'];

	return false;
}

/**
 * Returns the options array for a registered transaction-method
 *
 * @since 0.3.3
 * @param string $transaction_method  slug for the transaction-method
*/
function it_exchange_get_transaction_method_options( $transaction_method ) {
	if ( $addon = it_exchange_get_addon( $transaction_method ) )
		return $addon['options'];
	
	return false;
}

/**
 * Retreives a transaction object by passing it the WP post object or post id
 *
 * @since 0.3.3
 * @param mixed $post  post object or post id
 * @rturn object IT_Exchange_Transaction object for passed post
*/
function it_exchange_get_transaction( $post ) {
	return new IT_Exchange_Transaction( $post );
}

/**
 * Get IT_Exchange_Transactions
 *
 * @since 0.3.3
 * @return array  an array of IT_Exchange_Transaction objects
*/
function it_exchange_get_transactions( $args=array() ) {
	$defaults = array(
		'post_type' => 'it_exchange_tran',
	);

	$args = wp_parse_args( $args, $defaults );
	$meta_query = empty( $args['meta_query'] ) ? array() : $args['meta_query'];

	// Fold in transaction_method
	if ( ! empty( $args['transaction_method'] ) ) {
		$args['meta_query'][] = array( 
			'key'   => '_it_exchange_transaction_method',
			'value' => $args['transaction_method'],
		);
		unset( $args['transaction_method'] );
	}

	// Fold in transaction_status
	if ( ! empty( $args['transaction_status'] ) ) {
		$args['meta_query'][] = array( 
			'key'   => '_it_exchange_transaction_status',
			'value' => $args['transaction_status'],
		);
		unset( $args['transaction_status'] );
	}

	if ( $transactions = get_posts( $args ) ) {
		foreach( $transactions as $key => $transaction ) {
			$transactions[$key] = it_exchange_get_transaction( $transaction );
		}
		return $transactions;
	}

	return array();
}

/**
 * Adds a transaction post_type to WP
 *
 * @since 0.3.3
 * @param array $args same args passed to wp_insert_post plus any additional needed
 * @param object $transaction_object passed cart object
 * @return mixed post id or false
*/
function it_exchange_add_transaction( $method, $method_id, $status = 'pending', $customer_id = false, $transaction_object, $args = array() ) {
	$defaults = array(
		'post_type'          => 'it_exchange_tran',
		'post_status'        => 'publish',
	);
	$args = wp_parse_args( $args, $defaults );
	
	if ( !$customer_id )
		$customer_id = it_exchange_get_current_customer_id();

	// If we don't have a title, create one
	if ( empty( $args['post_title'] ) )
		$args['post_title'] = $method . '-' . $method_id . '-' . date_i18n( 'Y-m-d-H:i:s' );

	if ( $transaction_id = wp_insert_post( $args ) ) {
		update_post_meta( $transaction_id, '_it_exchange_transaction_method',    $method );
		update_post_meta( $transaction_id, '_it_exchange_transaction_method_id', $method_id );
		update_post_meta( $transaction_id, '_it_exchange_transaction_status',    $status );
		update_post_meta( $transaction_id, '_it_exchange_customer_id',           $customer_id );
		update_post_meta( $transaction_id, '_it_exchange_transaction_object',    $transaction_object );
		
		$transaction_object = apply_filters( 'it_exchange_add_transaction_success', $transaction_object, $transaction_id );
		return $transaction_id;
	}
	do_action( 'it_exchange_add_transaction_failed', $args, $transaction_object );
	return false;
}

/**
 * Updates a transaction
 *
 * @since 0.3.3
 * @param array transaction args. Must include ID of a valid transaction post
 * @return object transaction object
*/
function it_exchange_update_transaction( $args ) {
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
 * @param mixed $transaction the transaction id or object
 * @param string $status the new transaction status
*/
function it_exchange_update_transaction_status( $transaction, $status ) {

	if ( 'IT_Exchange_Transaction' != get_class( $transaction ) ) {
		$transaction = it_exchange_get_transaction( $transaction );
	}

	if ( ! $transaction->ID )
		return false;

	$old_status = $transaction->transaction_data['_it_exchange_transaction_status'];
	update_post_meta( $transaction->ID, '_it_exchange_transaction_status', $status );
	$transaction = it_exchange_get_transaction( $transaction->ID );

	do_action( 'it_exchange_update_transaction_status', $transaction, $old_status );
	do_action( 'it_exchange_update_transaction_status_' . $transaction->transaction_method, $transaction, $old_status );
	return $transaction->transaction_data['_it_exchange_transaction_status'];
}

/**
 * Returns the transaction status for a specific transaction
 *
 * @since 0.3.3
 * @param mixed $transaction the transaction id or object
 * @return string the transaction status
*/
function it_exchange_get_transaction_status( $transaction ) {
	if ( is_object( $transaction) && 'IT_Exchange_Transaction' == get_class( $transaction ) )
		return $transaction->transaction_data['_it_exchange_transaction_status'];

	if ( 'it_exchange_tran' != get_post_type( $transaction ) )
		return;

	$transaction = it_exchange_get_transaction( $transaction );
	return empty( $transaction->transaction_data['_it_exchange_transaction_status'] ) ? false : $transaction->transaction_data['_it_exchange_transaction_status'];
}

/**
 * Returns the transaction method name
 *
 * @since 0.3.7
 * @return string
*/
function it_exchange_get_transaction_method_name( $slug ) {
	if ( ! $method = it_exchange_get_addon( $slug ) )
		return false;

	$name = apply_filters( 'it_exchange_get_transaction_method_name_' . $method['slug'], $method['name'] );
	return $name;
}

/**
 * For processing a transaction
 *
 * @since 0.3.7
 * @return mixed
*/
function it_exchange_do_transaction( $method, $transaction_object ) {
	return apply_filters( 'it_exchange_do_transaction_' . $method, false, $transaction_object );
}

/**
 * Returns an array of transaction statuses that translate as good for delivery
 *
 * @since 0.4.0
 *
 * @return array
*/
function it_exchange_get_successfull_transaction_stati( $transaction_method ) {
	return apply_filters( 'it_exchange_get_successufll_transaction_stati_' . $transaction_method, array() );
}

/**
 * Returns the make-payment action
 *
 * Leans on tranasction_method to actually provide it.
 *
 * @since 0.4.0
 *
 * @param string $tranasction_method slug registered with addon
 * @param array $options
 * @return mixed
*/
function it_exchange_get_transaction_method_make_payment_button ( $transaction_method, $options=array() ) {
	return apply_filters( 'it_exchange_get_' . $transaction_method . '_make_payment_button', '', $options );
}
