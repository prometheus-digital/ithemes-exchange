<?php
/**
 * Default Cart Buddy Shopping Cart actions
 *
 * @since 0.3.0
 * @package IT_Cart_Buddy
*/

/**
 * Adds a product to the shopping cart based on the cart_buddy_add_to_cart query arg
 *
 * @since 0.3.7
 * return void
*/
function it_cart_buddy_default_cart_add_product_to_cart() {
	$product_id = empty( $_REQUEST['cart_buddy_add_to_cart'] ) ? false : $_REQUEST['cart_buddy_add_to_cart'];
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
function it_cart_buddy_default_cart_empty_cart() {
	if ( ! empty( $_REQUEST['cart_buddy_empty_cart'] ) )
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
function it_cart_buddy_default_cart_remove_product_from_cart( $product_id=false ) {
	if ( ! $product_id ) {
		$product_id = empty( $_REQUEST['cart_buddy_remove_product_from_cart'] ) ? false : $_REQUEST['cart_buddy_remove_product_from_cart'];
	}

	// Remove from the Session
	if ( $product_id ) {
		it_cart_buddy_remove_session_product( $product_id );
		do_action( 'it_cart_buddy_default_cart-removed_product_from_cart', $product_id );
	}
}
