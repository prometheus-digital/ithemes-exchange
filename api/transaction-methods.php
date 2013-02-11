<?php
/**
 * API Functions for Transaction Method Add-ons
 *
 * In addition to the functions found below, Cart Buddy offers the following actions related to transactions
 * - it_cart_buddy_save_transaction_unvalidated                // Runs every time a cart buddy transaction is saved.
 * - it_cart_buddy_save_transaction_unavalidate-[transaction-method] // Runs every time a specific cart buddy transaction method is saved.
 * - it_cart_buddy_save_transaction                            // Runs every time a cart buddy transaction is saved if not an autosave and if user has permission to save post
 * - it_cart_buddy_save_transaction-[transaction-method]             // Runs every time a specific cart buddy transaction method is saved if not an autosave and if user has permission to save transaction
 *
 * @package IT_Cart_Buddy
 * @since 0.3.3
*/

/**
 * Grabs the transaction method of a transaction
 *
 * @since 0.3.3
 * @return string the transaction method
*/
function it_cart_buddy_get_transaction_method( $post=false ) {
	if ( ! $post )
		global $post;

	// Return value from IT_Cart_Buddy_Transaction if we are able to locate it
	$transaction = it_cart_buddy_get_transaction( $post );
	if ( is_object( $transaction ) && ! empty ( $transaction->transaction_method ) )
		return $transaction->transaction_method;

	// Return query arg if is present
	if ( ! empty ( $_GET['transaction_method'] ) )
		return $_GET['transaction_method'];

	return false;
}

/**
 * Returns the options array for a registered transaction-method
 *
 * @since 0.3.3
 * @param string $transaction_method  slug for the transaction-method
*/
function it_cart_buddy_get_transaction_method_options( $transaction_method ) {
	if ( $addon = it_cart_buddy_get_add_on( $transaction_method ) )
		return $addon['options'];
	
	return false;
}

/**
 * Retreives a transaction object by passing it the WP post object or post id
 *
 * @since 0.3.3
 * @param mixed $post  post object or post id
 * @rturn object IT_Cart_Buddy_Transaction object for passed post
*/
function it_cart_buddy_get_transaction( $post ) {
	return new IT_Cart_Buddy_Transaction( $post );
}

/**
 * Get IT_Cart_Buddy_Transactions
 *
 * @since 0.3.3
 * @return array  an array of IT_Cart_Buddy_Transaction objects
*/
function it_cart_buddy_get_transactions( $args=array() ) {
	$defaults = array(
		'post_type' => 'it_cart_buddy_tran',
	);

	$args = wp_parse_args( $args, $defaults );

	if ( ! empty( $args['transaction_method'] ) ) {
		$meta_query = empty( $args['meta_query'] ) ? array() : $args['meta_query'];
		$meta_query[] = array( 
			'key'   => '_it_cart_buddy_transaction_method',
			'value' => $args['transaction_method'],
		);
		$args['meta_query'] = $meta_query;
	}

	if ( $transactions = get_posts( $args ) ) {
		foreach( $transactions as $key => $transaction ) {
			$transactions[$key] = it_cart_buddy_get_transaction( $transaction );
		}
		return $transactions;
	}

	return array();
}
