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
 * Adds a product to the shopping cart based on the cart_buddy_add_to_cart query arg
 *
 * @todo Decouple API function from REQUEST action
 * 
 * @since 0.3.7
 * @param $product_id a valid wp post id with a cart buddy product post_typp
 * return void
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
		$url = add_query_arg( array( it_cart_buddy_get_action_var( 'alert_message' ) => 'product-added-to-cart' ) );
		wp_redirect( $url );
		die();
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
		$url = add_query_arg( array( it_cart_buddy_get_action_var( 'alert_message' ) => 'product-added-to-cart' ) );
		wp_redirect( $url );
		die();
	}
}
add_action( 'it_cart_buddy_add_product_to_cart', 'it_cart_buddy_add_product_to_shopping_cart', 9 );

/**
 * Empty the Cart Buddy shopping cart
 *
 * @todo Decouple API function from REQUEST action
 *
 * @since 0.3.7
 * @return void
*/
function it_cart_buddy_empty_shopping_cart() {
    it_cart_buddy_clear_session_products();
    do_action( 'it_cart_buddy_cart_emptied' );
}
add_action( 'it_cart_buddy_empty_cart', 'it_cart_buddy_empty_shopping_cart', 9 );

/**
 * Removes a single product from the shopping cart
 *
 * This function removes a product from the cart. It is called via template_redirect and looks for the product ID in REQUEST
 * Optionally, theme developers may invoke it directly with the products cart_id
 *
 * @todo Decouple API function from REQUEST action
 *
 * @since 0.3.7
 * @param string $product_id optional param to specifcy which product gets deleted
*/
function it_cart_buddy_remove_product_from_shopping_cart( $product_id=false ) {
	$var = it_cart_buddy_get_action_var( 'remove_product_from_cart' );
	if ( ! $product_id ) {
		$product_id = empty( $_REQUEST[$var] ) ? false : $_REQUEST[$var];
	}

	// Remove from the Session
	if ( $product_id ) {
		it_cart_buddy_remove_session_product( $product_id );
		do_action( 'it_cart_buddy_default_cart-removed_product_from_cart', $product_id );
		$var = it_cart_buddy_get_action_var( 'alert_message' );
		$cart = it_cart_buddy_get_page_url( 'cart' );
		$url = add_query_arg( array( $var => 'product-removed' ), $cart );
		wp_redirect( $url );
		die();
	}
}
add_action( 'it_cart_buddy_remove_product_from_cart', 'it_cart_buddy_remove_product_from_shopping_cart', 9 );

/**
 * Updates the shopping cart
 *
 * This method gets called on template_redirect and fires when the update_cart button has been triggered
 *
 * @todo Decouple from form action
 * @todo Decouple messages. Add with hook
 *
 * @since 0.3.7
*/
function it_cart_buddy_update_shopping_cart( $show_message=true ) {
	// Get cart products
	$cart_products = it_cart_buddy_get_session_products();

	// Update quantities
	$quantities = empty( $_POST['product_quantity'] ) ? false : (array) $_POST['product_quantity'];

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

	$message_var = it_cart_buddy_get_action_var( 'alert_message' );
	if ( ! empty ( $message_var ) && $show_message ) {
		$page = it_cart_buddy_get_page_url( 'cart' );
		$url = add_query_arg( array( $message_var => 'cart-updated' ), $page );
		wp_redirect( $url );
		die();
	}
}
add_action( 'it_cart_buddy_update_cart', 'it_cart_buddy_update_shopping_cart', 9 );

/**
 * Advances the user to the checkout screen after updating the cart
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
 *
 * @todo decouple REQUEST Action from api function
 *
 * @since 0.3.7
 * @return void
*/
function it_cart_buddy_purchase_cart() {
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
add_action( 'it_cart_buddy_purchase_cart', 'it_cart_buddy_purchase_cart' );

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
