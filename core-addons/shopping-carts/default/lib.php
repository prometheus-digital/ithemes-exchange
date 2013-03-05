<?php
/**
 * Random utility functions for the default shopping cart add-on
 *
 * @since 0.3.7
 * @package IT_Cart_Buddy
*/

/**
 * Returns columns for the shopping cart HTML table
 *
 * @since 0.3.7
 * @return array column slugs / labels
*/
function it_cart_buddy_default_shopping_cart_get_table_columns() {
	$columns = array(
		'product-remove'   => '',
		'product-title'    => __( 'Product', 'LION' ),
		'product-cost'     => __( 'Price', 'LION' ),
		'product-quantity' => __( 'Quantity', 'LION' ),
		'product-subtotal' => __( 'Total', 'LION' ),
	);
	return apply_filters( 'it_cart_buddy_default_shopping_cart_get_table_columns', $columns );
}

/**
 * Redirect from checkout to cart if there are no items in the cart
 *
 * @since 0.3.7
 * @return void
*/
function it_cart_buddy_default_shopping_cart_redirect_checkout_to_cart() {
	$cart     = it_cart_buddy_get_page_url( 'cart' );
	$checkout = it_cart_buddy_get_page_id( 'checkout' );

	if ( ! is_page( $checkout ) )
		return;

	$products = it_cart_buddy_get_cart_products();
	if ( empty( $products ) ){
		wp_redirect( $cart );
		die();
	}
}
