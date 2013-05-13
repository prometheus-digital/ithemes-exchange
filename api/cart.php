<?php
/**
 * This file contains functions intended for theme developers to interact with the active shopping cart plugin
 *
 * The active shopping cart plugin should add the needed hooks below within its codebase.
 *
 * @since 0.3.7
 * @package IT_Exchange
*/

/**
 * Returns an array of all data in the cart
 *
 * @since 0.3.7
 * @return array
*/
function it_exchange_get_cart_data() {
	$data = it_exchange_get_session_data();
	return apply_filters( 'it_exchange_get_cart_data', $data );
}

/**
 * Returns an array of all products in the cart
 *
 * @since 0.3.7
 * @return array
*/
function it_exchange_get_cart_products() {
    $products = it_exchange_get_session_products();
	return ( empty( $products ) || ! array( $products ) ) ? array() : $products;
}

/**
 * Returns a specific product from the cart.
 *
 * The returned data is not an iThemes Exchange Product object. It is a cart-product
 *
 * @since 0.3.7
 * @param mixed $id id for the cart's product data
 * @return mixed
*/
function it_exchange_get_cart_product( $id ) {
    if ( ! $products = it_exchange_get_cart_products() )
        return false;

    if ( empty( $products[$id] ) )
        return false;

	return apply_filters( 'it_exchange_get_cart_product', $products[$id], $id );
}

/**
 * Returns columns for the shopping cart table
 *
 * @since 0.3.8
 * @return array column slugs / labels
*/
function it_exchange_get_cart_table_columns() {
    $columns = array(
        'product-remove'   => '', 
        'product-title'    => __( 'Product', 'LION' ),
        'product-cost'     => __( 'Price', 'LION' ),
        'product-quantity' => __( 'Quantity', 'LION' ),
        'product-subtotal' => __( 'Total', 'LION' ),
    );  
    return apply_filters( 'it_exchange_get_cart_table_columns', $columns );
}

/**
 * Adds a product to the shopping cart based on the product_id
 *
 * @since 0.3.7
 * @param string $product_id a valid wp post id with an iThemes Exchange product post_typp
 * @param int $quantity (optional) how many?
 * return boolean 
*/
function it_exchange_add_product_to_shopping_cart( $product_id, $quantity=1 ) {

	if ( ! $product_id )
		return;

	if ( ! $product = it_exchange_get_product( $product_id ) )
		return;

	/**
	 * The default shopping cart organizes products in the cart by product_id and a hash of 'itemized_data'.
	 * Any data like product variants or pricing mods that should separate products in the cart can be passed through this filter.
	*/
	$itemized_data = apply_filters( 'it_exchange_add_itemized_data_to_cart_product', array(), $product_id );

	if ( ! is_serialized( $itemized_data ) )
		$itemized_data = maybe_serialize( $itemized_data );
	$itemized_hash = md5( $itemized_data );

	/**
	 * Any data that needs to be stored in the cart for this product but that should not trigger a new itemized row in the cart
	*/
	$additional_data = apply_filters( 'it_exchange_add_additional_data_to_cart_product', array(), $product_id );
	if ( ! is_serialized( $additional_data ) )
		$additional_data = maybe_serialize( $additional_data );

	// If product is in cart already, bump the quanity. Otherwise, add it to the cart
	$session_products = it_exchange_get_session_products();

	if ( ! empty ($session_products[$product_id . '-' . $itemized_hash] ) ) {
		$product = $session_products[$product_id . '-' . $itemized_hash];

		// If we don't support purchase quanity, quanity will always be 1
		if ( it_exchange_product_supports_feature( $product_id, 'purchase-quantity' ) ) {
			// Get max quantity setting
			$max_purchase_quantity = it_exchange_get_product_feature( $product_id, 'purchase-quantity' );

			// If we support it but don't have it, quantity is unlimited
			if ( ! $max_purchase_quantity )
				$product['count'] = $product['count'] + $quantity;
			else
				$product['count'] = ( ( $product['count'] + $quantity ) > $max_purchase_quantity ) ? $max_purchase_quantity : $quantity + $product['count'];
		} else {
			$product['count'] = 1;
		}
		// Update session data
		it_exchange_update_session_product( $product_id . '-' . $itemized_hash, $product );
		do_action( 'it_exchange_cart_prouduct_count_updated', $product_id );
		return true;
	} else {

		// If we don't support purchase quanity, quanity will always be 1
		if ( it_exchange_product_supports_feature( $product_id, 'purchase-quantity' ) ) {
			// Get max quantity setting
			$max_purchase_quantity = it_exchange_get_product_feature( $product_id, 'purchase-quantity' ); 
			$count = ( $quantity > $max_purchase_quantity ) ? $max_purchase_quantity : $quantity;
		} else {
			$count = 1;
		}

		$product = array(
			'product_cart_id' => $product_id . '-' . $itemized_hash,
			'product_id'      => $product_id,
			'itemized_data'   => $itemized_data,
			'additional_data' => $additional_data,
			'itemized_hash'   => $itemized_hash,
			'count'           => $count,
		);

		it_exchange_add_session_product( $product, $product_id . '-' . $itemized_hash );
		do_action( 'it_exchange_product_added_to_cart', $product_id );
		return true;
	}
	return false;
}

/**
 * Empties the cart
 *
 * @since 0.3.7
 * @return boolean
*/
function it_exchange_empty_shopping_cart() {
	if ( it_exchange_clear_session_products() ) {
		do_action( 'it_exchange_cart_emptied' );
		return true;
	}
	return false;
}

/**
 * Removes a product from the cart
 *
 * @since 0.3.7
 * @param integer $product_id the shopping_cart_product_id (different from the DB product id)
 * @return boolean
*/
function it_exchange_remove_product_from_shopping_cart( $product_id ) {

	if ( it_exchange_remove_session_product( $product_id ) ) {
		do_action( 'it_exchange_product_removed_from_cart', $product_id );
		return true;
	}
	return false;
}

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
function it_exchange_update_shopping_cart() {
	do_action( 'it_exchange_update_cart' );
	do_action( 'it_exchange_cart_updated' );
	return true;
}

/**
 * Returns the title for a cart product
 *
 * Other add-ons may need to modify the DB title to reflect variants / etc
 *
 * @since 0.3.7
 * @param array $product cart product
 * @return string product title
*/
function it_exchange_get_cart_product_title( $product ) {
    if ( ! $db_product = it_exchange_get_product( $product['product_id'] ) )
        return false;

    $title = get_the_title( $db_product->ID );
    return apply_filters( 'it_exchange_get_cart_product_title', $title, $product );
}

/**
 * Returns the quantity for a cart product
 *
 * @since 0.3.7
 * @param array $product cart product
 * @return integer quantity 
*/
function it_exchange_get_cart_product_quantity( $product ) {
    $count = empty( $product['count'] ) ? 0 : $product['count'];
    return apply_filters( 'it_exchange_get_cart_product_quantity', $count, $product );
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
function it_exchange_get_cart_product_base_price( $product, $format=true ) {
    if ( ! $db_product = it_exchange_get_product( $product['product_id'] ) )
        return false;

    // Get the price from the DB
    $db_base_price = it_exchange_get_product_feature( $db_product->ID, 'base-price' );

	if ( $format )
		$db_base_price = it_exchange_format_price( $db_base_price );

    return apply_filters( 'it_exchange_get_cart_product_base_price', $db_base_price, $product );
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
function it_exchange_get_cart_product_subtotal( $product, $format=true ) {
	$base_price = it_exchange_get_cart_product_base_price( $product, false );
    $subtotal_price = apply_filters( 'it_exchange_get_cart_product_subtotal', $base_price * $product['count'], $product );

	if ( $format )
		$subtotal_price = it_exchange_format_price( $subtotal_price );

    return $subtotal_price;
}

/**
 * Returns the cart subtotal
 *
 * @since 0.3.7
 * @return mixed subtotal of cart
*/
function it_exchange_get_cart_subtotal( $format=true ) {
    $subtotal = 0;
    if ( ! $products = it_exchange_get_cart_products() )
        return 0;

    foreach( (array) $products as $product ) {
        $subtotal += it_exchange_get_cart_product_subtotal( $product, false );
    }
    $subtotal = apply_filters( 'it_exchange_get_cart_subtotal', $subtotal );

	if ( $format )
		$subtotal = it_exchange_format_price( $subtotal );

	return $subtotal;
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
function it_exchange_get_cart_total( $format=true ) {
    $total = apply_filters( 'it_exchange_get_cart_total', it_exchange_get_cart_subtotal() );

	if ( $format )
		$total = it_exchange_format_price( $total );

	return $total;
}

/**
 * Redirect to confirmation page after successfull transaction
 *
 * @since 0.3.7
 * @param integer $transaction_id the transaction id
 * @return void
*/
function it_exchange_do_confirmation_redirect( $transaction_id ) {
        $confirmation_url = it_exchange_get_page_url( 'confirmation' );
        $transaction_var  = it_exchange_get_field_name( 'transaction_id' );
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
function it_exchange_notify_failed_transaction( $message=false ) {
    $cart_url = it_exchange_get_page_url( 'checkout' );
    $message_var = it_exchange_get_field_name( 'error_message' );
    $message = empty( $message ) ? 'failed-transaction' : $message;
    $url = add_query_arg( array( $message_var => $message ) );
    wp_redirect( $url );
    die();
}

/**
 * Return the ID of a specific iThemes Exchange page as set in options
 *
 * @return integer the WordPress page id if it exists.
*/
function it_exchange_get_page_id( $page ) {
	$pages = it_exchange_get_option( 'settings_pages' );
	$id = empty( $pages[$page] ) ? false : (integer) $pages[$page];
	return apply_filters( 'it_exchange_get_page_id', $id, $page );;
}
