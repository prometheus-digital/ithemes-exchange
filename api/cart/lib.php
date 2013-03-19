<?php
/**
 * Additional Functions
 * @since 0.3.7
 * @package IT_Cart_Buddy
*/

/**
 * Return the URL for a specific page
 *
 * @since 0.3.7
 * @return string URL
*/
function it_cart_buddy_get_page_url( $page ) {
	$page_id = it_cart_buddy_get_page_id( $page );
	return apply_filters( 'it_cart_buddy_get_page_url', get_permalink( $page_id ), $page );
}

/**
 * Return the ID of a specific cart buddy page as set in options
 *
 * @return integer the WordPress page id if it exists.
*/
function it_cart_buddy_get_page_id( $page ) {
	$pages = it_cart_buddy_get_option( 'cart_buddy_settings_pages' );
	$id = empty( $pages[$page] ) ? false : (integer) $pages[$page];
	return apply_filters( 'it_cart_buddy_get_page_id', $id, $page );;
}

/**
 * Redirect from checkout to cart if there are no items in the cart
 *
 * @since 0.3.7
 * @return void
*/
function it_cart_buddy_redirect_checkout_to_cart() {
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
add_action( 'template_redirect', 'it_cart_buddy_redirect_checkout_to_cart' );

/**
 * Register error messages used with this add-on
 *
 * @since 0.3.7
 * @param array $messages existing messages
 * @return array
*/
function it_cart_buddy_register_cart_error_messages( $messages ) { 
    $messages['bad-transaction-method'] = __( 'Please select a payment method', 'LION' );
    $messages['failed-transaction']     = __( 'There was an error processing your transaction. Please try again.', 'LION' );
    $messages['negative-cart-total']    = __( 'The cart total must be greater than 0 for you to checkout. Please try again.', 'LION' );
    $messages['no-products-in-cart']    = __( 'You cannot checkout without any items in your cart.', 'LION' );
	$messages['product-not-removed']    = __( 'Product not removed from cart. Please try again.', 'LION' );
	$messages['cart-not-emptied']       = __( 'There was an error emptying your cart. Please try again.', 'LION' );
	$messages['cart-not-updated']       = __( 'There was an error updating your cart. Please try again.', 'LION' );
    return $messages;
}
add_filter( 'it_cart_buddy_get_error_messages', 'it_cart_buddy_register_cart_error_messages' );

/**
 * Register alert messages used with this add-on
 *
 * @since 0.3.7
 * @param array $messages existing messages
 * @return array
*/
function it_cart_buddy_register_cart_alert_messages( $messages ) { 
    $messages['cart-updated']          = __( 'Cart Updated.', 'LION' );
    $messages['cart-emptied']          = __( 'Cart Emptied', 'LION' );
    $messages['product-removed']       = __( 'Product removed from cart.', 'LION' );
    $messages['product-added-to-cart'] = __( 'Product added to cart', 'LION' );
    return $messages;
}
add_filter( 'it_cart_buddy_get_alert_messages', 'it_cart_buddy_register_cart_alert_messages' );
