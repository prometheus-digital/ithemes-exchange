<?php
/**
 * This file contains functions intended for theme developers to interact with the active shopping cart plugin
 *
 * The active shopping cart plugin should add the needed hooks below within its codebase.
 *
 * @since 0.3.7
 * @package IT_Cart_Buddy
*/

/**
 * Returns an array of all data in the cart
 *
 * @since 0.3.7
 * @return array
*/
function it_cart_buddy_get_cart_data() {
	$data = it_cart_buddy_get_session_data();
	return apply_filters( 'it_cart_buddy_get_cart_data', $data );
}

/**
 * Returns an array of all products in the cart
 *
 * @since 0.3.7
 * @return array
*/
function it_cart_buddy_get_cart_products() {
    $products = it_cart_buddy_get_session_products();
	return ( empty( $products ) || ! array( $products ) ) ? array() : $products;
}

/**
 * Returns a specific product from the cart.
 *
 * The returned data is not a Cart Buddy Product object. It is a cart-product
 *
 * @since 0.3.7
 * @param mixed $id id for the cart's product data
 * @return mixed
*/
function it_cart_buddy_get_cart_product( $id ) {
    if ( ! $products = it_cart_buddy_get_cart_products() )
        return false;

    if ( empty( $products[$id] ) )
        return false;

	return apply_filters( 'it_cart_buddy_get_cart_product', $products[$id], $id );
}

/**
 * Returns columns for the shopping cart table
 *
 * @since 0.3.8
 * @return array column slugs / labels
*/
function it_cart_buddy_get_cart_table_columns() {
    $columns = array(
        'product-remove'   => '', 
        'product-title'    => __( 'Product', 'LION' ),
        'product-cost'     => __( 'Price', 'LION' ),
        'product-quantity' => __( 'Quantity', 'LION' ),
        'product-subtotal' => __( 'Total', 'LION' ),
    );  
    return apply_filters( 'it_cart_buddy_get_cart_table_columns', $columns );
}

/**
 * Listens for $_REQUESTs to add a product to the cart and processes
 *
 * @todo move this function into lib/framework
 *
 * @since 0.3.8
 * @return void
*/
function it_cart_buddy_handle_add_product_to_cart_request() {
	
	$add_to_cart_var = it_cart_buddy_get_action_var( 'add_product_to_cart' );
	$product_id = empty( $_REQUEST[$add_to_cart_var] ) ? 0 : $_REQUEST[$add_to_cart_var];
	$product    = it_cart_buddy_get_product( $product_id );

	// Vefify legit product
	if ( ! $product )
		$error = 'bad-product';

	// Verify nonce
	$nonce_var = apply_filters( 'it_cart_buddy_add_product_to_cart_nonce_var', '_wpnonce' );
	if ( empty( $_REQUEST[$nonce_var] ) || ! wp_verify_nonce( $_REQUEST[$nonce_var], 'it_cart_buddy_add_product_to_cart-' . $product_id ) )
		$error = 'product-not-added-to-cart';
	
	// Add product
	if ( empty( $error ) && it_cart_buddy_add_product_to_shopping_cart( $product_id ) ) {
		$url = add_query_arg( array( it_cart_buddy_get_action_var( 'alert_message' ) => 'product-added-to-cart' ) );
		wp_redirect( $url );
		die();
	}

	$error_var = it_cart_buddy_get_action_var( 'error_message' );
	$error = empty( $error ) ? 'product-not-added-to-cart' : $error;
	$url  = add_query_arg( array( $error_var => $error ), $cart );
	wp_redirect( $url );
	die();

}
add_action( 'it_cart_buddy_add_product_to_cart', 'it_cart_buddy_handle_add_product_to_cart_request', 9 );

/**
 * Adds a product to the shopping cart based on the product_id
 *
 * @since 0.3.7
 * @param $product_id a valid wp post id with a cart buddy product post_typp
 * return boolean 
*/
function it_cart_buddy_add_product_to_shopping_cart( $product_id ) {

	if ( ! $product_id )
		return;

	if ( ! $product = it_cart_buddy_get_product( $product_id ) )
		return;

	/**
	 * The default shopping cart organizes products in the cart by product_id and a hash of 'itemized_data'.
	 * Any data like product variants or pricing mods that should separate products in the cart can be passed through this filter.
	*/
	$itemized_data = apply_filters( 'it_cart_buddy_add_itemized_data_to_cart_product', array(), $product_id );

	if ( ! is_serialized( $itemized_data ) )
		$itemized_data = maybe_serialize( $itemized_data );
	$itemized_hash = md5( $itemized_data );

	/**
	 * Any data that needs to be stored in the cart for this product but that should not trigger a new itemized row in the cart
	*/
	$additional_data = apply_filters( 'it_cart_buddy_add_additional_data_to_cart_product', array(), $product_id );
	if ( ! is_serialized( $additional_data ) )
		$additional_data = maybe_serialize( $additional_data );

	// If product is in cart already, bump the quanity. Otherwise, add it to the cart
	$session_products = it_cart_buddy_get_session_products();
	if ( ! empty ($session_products[$product_id . '-' . $itemized_hash] ) ) {
		$product = $session_products[$product_id . '-' . $itemized_hash];
		$product['count']++;
		// Bump the quantity
		it_cart_buddy_update_session_product( $product_id . '-' . $itemized_hash, $product );
		do_action( 'it_cart_buddy_cart_prouduct_count_updated', $product_id );
		return true;
	} else {
		$product = array(
			'product_cart_id' => $product_id . '-' . $itemized_hash,
			'product_id'      => $product_id,
			'itemized_data'   => $itemized_data,
			'additional_data' => $additional_data,
			'itemized_hash'   => $itemized_hash,
			'count'           => 1,
		);

		it_cart_buddy_add_session_product( $product, $product_id . '-' . $itemized_hash );
		do_action( 'it_cart_buddy_product_added_to_cart', $product_id );
		return true;
	}
	return false;
}

/**
 * Empty the Cart Buddy shopping cart
 *
 * @todo Move this function to /lib/framework
 *
 * @since 0.3.7
 * @return void
*/
function it_cart_buddy_handle_empty_shopping_cart_request() {

	// Verify nonce
	$nonce_var   = apply_filters( 'it_cart_buddy_cart_action_nonce_var', '_wpnonce' );
	$error_var   = it_cart_buddy_get_action_var( 'error_message' );
	$message_var = it_cart_buddy_get_action_var( 'alert_message' );
	$cart        = it_cart_buddy_get_page_url( 'cart' );
	if ( empty( $_REQUEST[$nonce_var] ) || ! wp_verify_nonce( $_REQUEST[$nonce_var], 'it_cart_buddy_cart_action-' . session_id() ) || ! it_cart_buddy_empty_shopping_cart() ) {
		$url  = add_query_arg( array( $error_var => 'cart-not-emptied' ), $cart );
		wp_redirect( $url );
		die();
	} else {
		$url = remove_query_arg( $error_var, $cart );
		$url = add_query_arg( array( $message_var => 'cart-emptied' ), $url );
		wp_redirect( $url );
		die();
	}
}
add_action( 'it_cart_buddy_empty_cart', 'it_cart_buddy_handle_empty_shopping_cart_request', 9 );

/**
 * Empties the cart
 *
 * @since 0.3.7
 * @return boolean
*/
function it_cart_buddy_empty_shopping_cart() {
	if ( it_cart_buddy_clear_session_products() ) {
		do_action( 'it_cart_buddy_cart_emptied' );
		return true;
	}
	return false;
}

/**
 * Removes a single product from the shopping cart
 *
 * This listens for REQUESTS to remove a product from the cart, verifies the request, and passes it along to the correct function
 *
 * @todo Move to /lib/framework dir
 *
 * @since 0.3.8
 * @return void
*/
function it_cart_buddy_handle_remove_product_from_cart_request() {
	$var        = it_cart_buddy_get_action_var( 'remove_product_from_cart' );
	$product_id = empty( $_REQUEST[$var] ) ? false : $_REQUEST[$var];
	$cart_url   = it_cart_buddy_get_page_url( 'cart' );

	// Verify nonce
	$nonce_var = apply_filters( 'it_cart_buddy_remove_product_from_cart_nonce_var', '_wpnonce' );
	if ( empty( $_REQUEST[$nonce_var] ) || ! wp_verify_nonce( $_REQUEST[$nonce_var], 'it_cart_buddy_remove_product_from_cart-' . $product_id ) || ! it_cart_buddy_remove_product_from_shopping_cart( $product_id ) ) {
		$var = it_cart_buddy_get_action_var( 'error_message' );
		$url  = add_query_arg( array( $var => 'product-not-removed' ), $cart_url );
		wp_redirect( $url );
		die();
	}

	$var = it_cart_buddy_get_action_var( 'alert_message' );
	$url = add_query_arg( array( $var => 'product-removed' ), $cart_url );
	wp_redirect( $url );
	die();
}
add_action( 'it_cart_buddy_remove_product_from_cart', 'it_cart_buddy_handle_remove_product_from_cart_request', 9 );

/**
 * Removes a product from the cart
 *
 * @since 0.3.7
 * @param integer $product_id the shopping_cart_product_id (different from the DB product id)
 * @return boolean
*/
function it_cart_buddy_remove_product_from_shopping_cart( $product_id ) {

	if ( it_cart_buddy_remove_session_product( $product_id ) ) {
		do_action( 'it_cart_buddy_product_removed_from_cart', $product_id );
		return true;
	}
	return false;
}

/**
 * Listens for the REQUEST to update the shopping cart, verifies it, and calls the correct function
 *
 * @todo Move to /lib/framework director
 *
 * @since 0.3.8
*/
function it_cart_buddy_handle_update_cart_request() {
	// Verify nonce
	$nonce_var = apply_filters( 'it_cart_buddy_cart_action_nonce_var', '_wpnonce' );
	if ( empty( $_REQUEST[$nonce_var] ) || ! wp_verify_nonce( $_REQUEST[$nonce_var], 'it_cart_buddy_cart_action-' . session_id() ) || ! it_cart_buddy_update_shopping_cart() ) {
		$var = it_cart_buddy_get_action_var( 'error_message' );
		$cart = it_cart_buddy_get_page_url( 'cart' );
		$url  = add_query_arg( array( $var => 'cart-not-updated' ), $cart );
		wp_redirect( $url );
		die();
	}

	$message_var = it_cart_buddy_get_action_var( 'alert_message' );
	if ( ! empty ( $message_var ) ) {
		$page = it_cart_buddy_get_page_url( 'cart' );
		$url = add_query_arg( array( $message_var => 'cart-updated' ), $page );
		wp_redirect( $url );
		die();
	}
}
add_action( 'it_cart_buddy_update_cart_action', 'it_cart_buddy_handle_update_cart_request', 9 );

/**
 * Updates the shopping cart
 *
 * This doesn't actually do anything. Add-ons need to hook into here to perform updates. 
 * Core calls it when the update cart button or the proceed to checkout button is triggered.
 *
 * - The only core action hooked to it is the update of product quantity
 *
 * @since 0.3.8
 * @return boolean
*/
function it_cart_buddy_update_shopping_cart() {
	do_action( 'it_cart_buddy_update_cart' );
	do_action( 'it_cart_buddy_cart_updated' );
	return true;
}

/**
 * Updates the quantity of a product on the update_cart (and proceed to checkout) actions
 *
 * @todo move to /lib/framework
 *
 * @since 0.3.8
 * @return void
*/
function it_cart_buddy_handle_update_cart_quantity_request() {

	// Get Quantities form REQUEST
	$quantities = empty( $_POST['product_quantity'] ) ? false : (array) $_POST['product_quantity'];
	if ( ! $quantities )
		return;

	// Get cart products
	$cart_products = it_cart_buddy_get_session_products();

	// Update quantities
	foreach( $quantities as $product => $quantity ) {
		if ( ! empty( $cart_products[$product] ) && is_numeric( $quantity ) ) {
			$cart_product = $cart_products[$product];
			if ( empty( $quantity ) || $quantity < 1 ) {
				it_cart_buddy_remove_session_product( $product );
			} else {
				$cart_product['count'] = $quantity;
				it_cart_buddy_update_session_product( $product, $cart_product );
			}
		}
	}
}
add_action( 'it_cart_buddy_update_cart', 'it_cart_buddy_handle_update_cart_quantity_request', 9 );

/**
 * Advances the user to the checkout screen after updating the cart
 *
 * @todo move to lib/framework directory
 *
 * @since 0.3.7
 * @return void
*/
function it_cart_buddy_proceed_to_checkout() {

    // Update cart info
    do_action( 'it_cart_buddy_update_cart', false );

    // Redirect to Checkout
    if ( $checkout = it_cart_buddy_get_page_url( 'checkout' ) ) {
        wp_redirect( $checkout );
        die();
    }
}
add_action( 'it_cart_buddy_proceed_to_checkout', 'it_cart_buddy_proceed_to_checkout', 9 );

/**
 * Returns the title for a cart product
 *
 * Other add-ons may need to modify the DB title to reflect variants / etc
 *
 * @since 0.3.7
 * @param array $product cart product
 * @return string product title
*/
function it_cart_buddy_get_cart_product_title( $product ) {
    if ( ! $db_product = it_cart_buddy_get_product( $product['product_id'] ) )
        return false;

    $title = get_the_title( $db_product->ID );
    return apply_filters( 'it_cart_buddy_get_cart_product_title', $title, $product );
}

/**
 * Returns the quantity for a cart product
 *
 * @since 0.3.7
 * @param array $product cart product
 * @return integer quantity 
*/
function it_cart_buddy_get_cart_product_quantity( $product ) {
    $count = empty( $product['count'] ) ? 0 : $product['count'];
    return apply_filters( 'it_cart_buddy_get_cart_product_quantity', $count, $product );
}

/**
 * Returns the base_price for the cart product
 *
 * Other add-ons may modify this on the fly based on the product's itemized_data and additional_data arrays
 *
 * @since 0.3.7
 * @param array $product cart product
 * @return integer quantity 
*/
function it_cart_buddy_get_cart_product_base_price( $product ) {
    if ( ! $db_product = it_cart_buddy_get_product( $product['product_id'] ) )
        return false;

    // Get the price from the DB
    $db_base_price = it_cart_buddy_get_product_feature( $db_product->ID, 'base_price' );

    return apply_filters( 'it_cart_buddy_get_cart_product_base_price', $db_base_price, $product );
}

/**
 * Returns the subtotal for a cart product
 *
 * Base price multiplied by quantity and then passed through a filter
 *
 * @since 0.3.7
 * @param array $product cart product
 * @return mixed subtotal
*/
function it_cart_buddy_get_cart_product_subtotal( $product ) {
    $base_price = it_cart_buddy_get_product_feature( $product['product_id'], 'base_price' );
    $base_price = apply_filters( 'it_cart_buddy_get_cart_product_base_price', $base_price, $product );
    $subtotal_price = apply_filters( 'it_cart_buddy_get_cart_product_subtotal', $base_price * $product['count'], $product );
    return $subtotal_price;
}

/**
 * Returns the cart subtotal
 *
 * @since 0.3.7
 * @return mixed subtotal of cart
*/
function it_cart_buddy_get_cart_subtotal() {
    $subtotal = 0;
    if ( ! $products = it_cart_buddy_get_cart_products() )
        return 0;

    foreach( (array) $products as $product ) {
        $subtotal += it_cart_buddy_get_cart_product_subtotal( $product );
    }
    return apply_filters( 'it_cart_buddy_get_cart_subtotal', $subtotal );
}

/**
 * Returns the cart total
 *
 * The cart total is essentailly going to be the sub_total plus whatever motifications other add-ons make to it.
 * eg: taxes, shipping, discounts, etc.
 *
 * @since 0.3.7
 * @return mixed total of cart
*/
function it_cart_buddy_get_cart_total() {
    $total = it_cart_buddy_get_cart_subtotal();
    return apply_filters( 'it_cart_buddy_get_cart_total', $total );
}

/**
 * Process checkout
 *
 * Formats data and hands it off to the appropriate tranaction method
 * @todo move to /lib/framework directory
 *
 * @since 0.3.7
 * @return void
*/
function it_cart_buddy_handle_purchase_cart_request() {

	// Verify nonce
	$nonce_var = apply_filters( 'it_cart_buddy_checkout_action_nonce_var', '_wpnonce' );
	if ( empty( $_REQUEST[$nonce_var] ) || ! wp_verify_nonce( $_REQUEST[$nonce_var], 'it_cart_buddy_checkout_action-' . session_id() ) ) {
		it_cart_buddy_notify_failed_transaction( 'failed-transaction' );
		return false;
	}

    // Verify products exist
    $products = it_cart_buddy_get_cart_products();
    if ( count( $products ) < 1 ) {
        do_action( 'it_cart_buddy_error-no_products_to_purchase' );
        it_cart_buddy_notify_failed_transaction( 'no-products-in-cart' );
        return false;
    }

    // Verify transaction method exists
    $method_var = it_cart_buddy_get_action_var( 'transaction_method' );
    $requested_transaction_method = empty( $_REQUEST[$method_var] ) ? false : $_REQUEST[$method_var];
    $enabled_addons = it_cart_buddy_get_enabled_addons( array( 'category' => 'transaction-methods' ) );
    if ( ! $requested_transaction_method || empty( $enabled_addons[$requested_transaction_method] ) ) {
        do_action( 'it_cart_buddy_error-bad_transaction_method_at_purchase', $requested_transaction_method );
        it_cart_buddy_notify_failed_transaction( 'bad-transaction-method' );
        return false;
    }

    // Verify cart total is a positive number
    $cart_total = number_format( it_cart_buddy_get_cart_total(), 2);
    if ( $cart_total < 0.01 ) {
        do_action( 'it_cart_buddy_error-negative_cart_total_on_checkout', $cart_total );
        it_cart_buddy_notify_failed_transaction( 'negative-cart-total' );
        return false;
    }

    // Add subtotal to each product
    foreach( $products as $key => $product ) {
        $products[$key]['product_baseline'] = it_cart_buddy_get_cart_product_base_price( $product );
        $products[$key]['product_subtotal'] = it_cart_buddy_get_cart_product_subtotal( $product );
        $products[$key]['product_name']     = it_cart_buddy_get_cart_product_title( $product );
    }

    // Package it up and send it to the transaction method add-on
    $transaction_object = new stdClass();
    $transaction_object->products = $products;
    $transaction_object->data     = it_cart_buddy_get_cart_data();
    $transaction_object->total    = $cart_total;

    // Setup actions for success / failure
    add_action( 'it_cart_buddy_add_transaction_success-' . $requested_transaction_method, 'it_cart_buddy_empty_shopping_cart' );
    add_action( 'it_cart_buddy_add_transaction_success-' . $requested_transaction_method, 'it_cart_buddy_do_confirmation_redirect' );
    add_action( 'it_cart_buddy_add_transaction_failed-' . $requested_transaction_method, 'it_cart_buddy_notify_failed_transaction' );

    // Do the transaction
    it_cart_buddy_do_transaction( $requested_transaction_method, $transaction_object );

    // If we made it this far, the transaction failed or the transaction-method add-on did not hook into success/fail actions
    it_cart_buddy_notify_failed_transaction();
}
add_action( 'it_cart_buddy_purchase_cart', 'it_cart_buddy_handle_purchase_cart_request' );

/**
 * Redirect to confirmation page after successfull transaction
 *
 * @since 0.3.7
 * @param integer $transaction_id the transaction id
 * @return void
*/
function it_cart_buddy_do_confirmation_redirect( $transaction_id ) {
        $confirmation_url = it_cart_buddy_get_page_url( 'confirmation' );
        $transaction_var  = it_cart_buddy_get_action_var( 'transaction_id' );
        $confirmation_url = add_query_arg( array( $transaction_var => $transaction_id ), $confirmation_url );
        wp_redirect( $confirmation_url );
        die();
}

/**
 * Redirects to cart with failed transaction message
 *
 * @since 0.3.7
 * @return void
*/
function it_cart_buddy_notify_failed_transaction( $message=false ) {
    $cart_url = it_cart_buddy_get_page_url( 'checkout' );
    $message_var = it_cart_buddy_get_action_var( 'error_message' );
    $message = empty( $message ) ? 'failed-transaction' : $message;
    $url = add_query_arg( array( $message_var => $message ) );
    wp_redirect( $url );
    die();
}
