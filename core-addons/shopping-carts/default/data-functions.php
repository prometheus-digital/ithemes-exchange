<?php
/**
 * Default Cart Buddy Shopping Cart actions
 *
 * @since 0.3.0
 * @package IT_Cart_Buddy
*/

/**
 * Returns all cart data
 *
 * @since 0.3.7
 * @return array
*/
function it_cart_buddy_default_cart_get_data() {
	return it_cart_buddy_get_session_data();
}

/**
 * Adds a product to the shopping cart based on the cart_buddy_add_to_cart query arg
 *
 * @since 0.3.7
 * return void
*/
function it_cart_buddy_default_cart_add_product_to_shopping_cart( $product_id ) {

	if ( ! $product_id )
		return;

	if ( ! $product = it_cart_buddy_get_product( $product_id ) )
		return;

	/**
	 * The default shopping cart organizes products in the cart by product_id and a hash of 'itemized_data'.
	 * Any data like product variants or pricing mods that should separate products in the cart can be passed through this filter.
	*/
	$itemized_data = apply_filters( 'it_cart_buddy_default_cart_add_itemized_data_to_cart_product', array(), $product_id );

	if ( ! is_serialized( $itemized_data ) )
		$itemized_data = maybe_serialize( $itemized_data );
	$itemized_hash = md5( $itemized_data );

	/**
	 * Any data that needs to be stored in the cart for this product but that should not trigger a new itemized row in the cart
	*/
	$additional_data = apply_filters( 'it_cart_buddy_default_cart_add_additional_data_to_cart_product', array(), $product_id );
	if ( ! is_serialized( $additional_data ) )
		$additional_data = maybe_serialize( $additional_data );

	// If product is in cart already, bump the quanity. Otherwise, add it to the cart
	$session_products = it_cart_buddy_get_session_products();
	if ( ! empty ($session_products[$product_id . '-' . $itemized_hash] ) ) {
		$product = $session_products[$product_id . '-' . $itemized_hash];
		$product['count']++;
		// Bump the quantity
		it_cart_buddy_update_session_product( $product_id . '-' . $itemized_hash, $product );
		do_action( 'it_cart_buddy_default_cart-prouduct_count_updated', $product_id );
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
		do_action( 'it_cart_buddy_default_cart-product_added', $product_id );
	}
}

/**
 * Empty the default Cart Buddy shopping cart add-on
 *
 * @since 0.3.7
 * @return void
*/
function it_cart_buddy_default_cart_empty_shopping_cart() {
	it_cart_buddy_clear_session_products();
	do_action( 'it_cart_buddy_default_cart-empty_cart' );
}

/**
 * Removes a single product from the shopping cart
 *
 * This function removes a product from the cart. It is called via template_redirect and looks for the product ID in REQUEST
 * Optionally, theme developers may invoke it directly with the products cart_id
 *
 * @since 0.3.7
 * @param string $product_id optional param to specifcy which product gets deleted
*/
function it_cart_buddy_default_cart_remove_product_from_shopping_cart( $product_id=false ) {
	$var = it_cart_buddy_get_action_var( 'remove_product_from_cart' );
	if ( ! $product_id ) {
		$product_id = empty( $_REQUEST[$var] ) ? false : $_REQUEST[$var];
	}

	// Remove from the Session
	if ( $product_id ) {
		it_cart_buddy_remove_session_product( $product_id );
		do_action( 'it_cart_buddy_default_cart-removed_product_from_cart', $product_id );
	}
}

/**
 * Updates the shopping cart
 *
 * This method gets called on template_redirect and fires when the update_cart button has been triggered
 *
 * @since 0.3.7
*/
function it_cart_buddy_default_cart_update_shopping_cart( $show_message=true ) {

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

	$message_var = it_cart_buddy_get_action_var( 'display_message' );
	if ( ! empty ( $message_var ) && $show_message ) {
		$page = it_cart_buddy_get_page_url( 'cart' );
		$url = add_query_arg( array( $message_var => 1 ), $page );
		wp_redirect( $url );
		die();
	}

}

/**
 * Advances the user to the checkout screen after updating the cart
 *
 * @since 0.3.7
 * @return void
*/
function it_cart_buddy_default_cart_proceed_to_checkout() {

	// Update cart info
	do_action( 'update_cart', false );

	// Redirect to Checkout
	if ( $checkout = it_cart_buddy_get_page_url( 'checkout' ) ) {
		wp_redirect( $checkout );
		die();
	}
}

/**
 * Returns the title for a cart product
 *
 * Some shopping cart add-ons may modify this from the DB title to reflect variants / etc
 *
 * @since 0.3.7
 * @param mixed $existing values passed through by WP filter API. Discarded here.
 * @param array $product cart product
 * @return string product title
*/
function it_cart_buddy_default_cart_get_cart_product_title( $existing, $product ) {
	if ( ! $db_product = it_cart_buddy_get_product( $product['product_id'] ) )
		return false;
	
	$title = apply_filters( 'the_title', $db_product->post_title );
	$title = apply_filters( 'it_cart_buddy_default_shopping_cart_get_cart_product_title', $title, $product );
	return $title;
}

/**
 * Returns the quantity for a cart product
 *
 * @since 0.3.7
 * @param mixed $existing values passed through by WP filter API. Discarded here.
 * @param array $product cart product
 * @return integer quantity 
*/
function it_cart_buddy_default_cart_get_cart_product_quantity( $existing, $product ) {
	$count    = empty( $product['count'] ) ? 0 : $product['count'];
	$quantity = apply_filters( 'it_cart_buddy_default_shopping_cart_get_cart_product_quantity', $count, $product );
	return $quantity;
}

/**
 * Returns the base_price for the cart product
 *
 * Other add-ons may modify this on the fly based on the product's itemized_data and additional_data arrays
 *
 * @since 0.3.7
 * @param mixed $existing values passed through by WP filter API. Discarded here.
 * @param array $product cart product
 * @return integer quantity 
*/
function it_cart_buddy_default_cart_get_cart_product_base_price( $existing, $product ) {
	if ( ! $db_product = it_cart_buddy_get_product( $product['product_id'] ) )
		return false;

	// Get the price from the DB
	$db_base_price = it_cart_buddy_get_product_feature( $db_product, 'base_price' );

	// Pass it through a filter (though it is already in a larger-scoped filter)
	return apply_filters( 'it_cart_buddy_default_shopping_cart_get_cart_product_base_price', $db_base_price, $product ); 
}

/**
 * Returns the subtotal for a cart product
 *
 * Base price multiplied by quantity and then passed through a filter
 *
 * @since 0.3.7
 * @param mixex $existing values passed through by WP filter API. Discarded here.
 * @param array $product cart product
 * @return mixed subtotal
*/
function it_cart_buddy_default_cart_get_cart_product_subtotal( $existing, $product ) {
	$base_price = it_cart_buddy_get_product_feature( $product['product_id'], 'base_price' );
	$base_price = apply_filters( 'it_cart_buddy_default_shopping_cart_get_cart_product_base_price', $base_price, $product );
	$subtotal_price = apply_filters( 'it_cart_buddy_default_shopping_cart_get_cart_product_subtotal', $base_price * $product['count'], $product );
	return $subtotal_price;
}

/**
 * Returns the cart subtotal
 *
 * @since 0.3.7
 * @param mixed $existing existing total passed through by WP filter. Not used here.
 * @return mixed subtotal of cart
*/
function it_cart_buddy_default_cart_get_cart_subtotal( $existing ) {
	$subtotal = 0;
	if ( ! $products = it_cart_buddy_get_cart_products() )
		return 0;

	foreach( (array) $products as $product ) {
		$subtotal += it_cart_buddy_get_cart_product_subtotal( $product );
	}
	return apply_filters( 'it_cart_buddy_default_shopping_cart_get_cart_subtotal', $subtotal );
}

/**
 * Returns the cart total
 *
 * The cart total is essentailly going to be the sub_total plus whatever motifications other add-ons make to it.
 * eg: taxes, shipping, discounts, etc.
 *
 * @since 0.3.7
 * @param mixed $existing existing total passed through by WP filter. Not used here.
 * @return mixed total of cart
*/
function it_cart_buddy_default_cart_get_cart_total( $existing ) {
	$total = it_cart_buddy_get_cart_subtotal();
	return apply_filters( 'it_cart_buddy_default_shopping_cart_get_cart_total', $total );
}

/**
 * Process checkout
 *
 * Formats data and hands it off to the appropriate tranaction method
 *
 * @since 0.3.7
 * @return void
*/
function it_cart_buddy_default_cart_purchase_cart() {

	// Verify products exist
	$products = it_cart_buddy_get_cart_products();
	if ( count( $products ) < 1 ) {
		do_action( 'it_cart_buddy_error-no_products_to_purchase' );
		it_cart_buddy_default_cart_notify_failed_transaction( 'no-products' );
		return false;
	}

	// Verify transaction method exists
	$method_var = it_cart_buddy_get_action_var( 'transaction_method' );
	$requested_transaction_method = empty( $_REQUEST[$method_var] ) ? false : $_REQUEST[$method_var];
	$enabled_addons = it_cart_buddy_get_enabled_addons( array( 'category' => 'transaction-methods' ) );
	if ( ! $requested_transaction_method || empty( $enabled_addons[$requested_transaction_method] ) ) {
		do_action( 'it_cart_buddy_error-bad_transaction_method_at_purchase', $requested_transaction_method );
		it_cart_buddy_default_cart_notify_failed_transaction( 'bad-transaction-method' );
		return false;
	}

	// Verify cart total is a positive number
	$cart_total = number_format( it_cart_buddy_get_cart_total(), 2);
	if ( $cart_total < 0.01 ) {
		do_action( 'it_cart_buddy_error-negative_cart_total_on_checkout', $cart_total );
		it_cart_buddy_default_cart_notify_failed_transaction( 'negative-cart-total' );
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
	add_action( 'it_cart_buddy_add_transaction_success-' . $requested_transaction_method, 'it_cart_buddy_default_cart_empty_shopping_cart' );
	add_action( 'it_cart_buddy_add_transaction_success-' . $requested_transaction_method, 'it_cart_buddy_default_cart_do_confirmation_redirect' );
	add_action( 'it_cart_buddy_add_transaction_failed-' . $requested_transaction_method, 'it_cart_buddy_default_cart_notify_failed_transaction' );

	// Do the transaction
	it_cart_buddy_do_transaction( $requested_transaction_method, $transaction_object );

	// If we made it this far, the transaction failed or the transaction-method add-on did not hook into success/fail actions
	it_cart_buddy_default_cart_notify_failed_transaction();
}

/**
 * Redirect to confirmation page after successfull transaction
 *
 * @since 0.3.7
 * @param integer $transaction_id the transaction id
 * @return void
*/
function it_cart_buddy_default_cart_do_confirmation_redirect( $transaction_id ) {
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
function it_cart_buddy_default_cart_notify_failed_transaction( $message=false ) {
	$cart_url = it_cart_buddy_get_page_url( 'checkout' );
	$message_var = it_cart_buddy_get_action_var( 'display_message' );
	$message = empty( $message ) ? 'failed-transaction' : $message;
	$url = add_query_arg( array( $message_var => $message ) );
	wp_redirect( $url );
	die();
}
