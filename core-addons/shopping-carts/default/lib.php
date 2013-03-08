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

/**
 * Register error messages used with this add-on
 *
 * @since 0.3.7
 * @param array $messages existing messages
 * @return array
*/
function it_cart_buddy_default_shopping_cart_register_error_messages( $messages ) {
	$messages['bad-transaction-method'] = __( 'Please select a payment method', 'LION' );
	$messages['failed-transaction']     = __( 'There was an error processing your transaction. Please try again.', 'LION' );
	$messages['negative-cart-total']    = __( 'The cart total must be greater than 0 for you to checkout. Please try again.', 'LION' );
	$messages['no-products-in-cart']    = __( 'You cannot checkout without any items in your cart.', 'LION' );
	return $messages;
}
add_filter( 'it_cart_buddy_get_error_messages', 'it_cart_buddy_default_shopping_cart_register_error_messages' );

/**
 * Register alert messages used with this add-on
 *
 * @since 0.3.7
 * @param array $messages existing messages
 * @return array
*/
function it_cart_buddy_default_shopping_cart_register_alert_messages( $messages ) {
	$messages['cart-updated']          = __( 'Cart Updated.', 'LION' );
	$messages['product-removed']       = __( 'Product removed from cart.', 'LION' );
	$messages['product-added-to-cart'] = __( 'Product added to cart', 'LION' );
	return $messages;
}
add_filter( 'it_cart_buddy_get_alert_messages', 'it_cart_buddy_default_shopping_cart_register_alert_messages' );
