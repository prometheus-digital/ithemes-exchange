<?php
/**
 * These functions print HTML elements for the cart
 *
 * @since 0.3.7
 * @package IT_Cart_Buddy
*/

/**
 * This function returns the HTML for the shopping cart
 *
 * Theme developers may use this to print the shopping cart code.
 * Shopping cart add-on developers will hook to it for their carts.
 * It is also invoked via a shortcode
 *
 * @since 0.3.7
 * @param array $shortcode_args args passed from WP Shortcode API if function is being invoked by it.
 * @param string $shortcode_content content passed from WP Shortcode API if function is being invoked by it.
 * @return string html for the shopping cart
*/
function it_cart_buddy_get_shopping_cart_cart_html( $shortcode_args=array(), $shortcode_content='' ) {
    return apply_filters( 'it_cart_buddy_get_shopping_cart_cart_html', '', $shortcode_args, $shortcode_content );
}

/**
 * Generates an add to cart button
 *
 * Theme developers may use this to print the add_to_cart HTML
 * Shopping cart add-on developers will hook to it for their carts.
 * It is also invoked via a shortcode
 *
 * @since 0.3.7
 * @param mixed $product product ID
 * @param array $shortcode_args args passed from WP Shortcode API if function is being invoked by it.
 * @param string $shortcode_content content passed from WP Shortcode API if function is being invoked by it.
 * @return string HTML for the button
*/
function it_cart_buddy_get_add_product_to_shopping_cart_html( $product, $shortcode_args=array(), $shortcode_content=''  ) { 
    return apply_filters( 'it_cart_buddy_get_add_product_to_shopping_cart_html', '', $product, $shortcode_args, $shortcode_content );
}

/**
 * Prints the HTML for the empty cart action
 *
 * This prints HTML for a form element. It is assumed that it will be used inside the shopping cart form
 *
 * @since 0.3.7
 * @return string HTML
*/
function it_cart_buddy_get_empty_shopping_cart_html() {
	return apply_filters( 'it_cart_buddy_get_empty_shopping_cart_html', '' );
}

/**
 * Prints the HTML to remove a product from the cart
 *
 * @since 0.3.7
 * @param mixed $cart_product_id the id of the product in the cart
 * @reuturn string HTML
*/
function it_cart_buddy_get_remove_product_from_shopping_cart_html( $cart_product_id ) {
	return apply_filters( 'it_cart_buddy_get_remove_product_from_shopping_cart_html', '', $cart_product_id );
}

/**
 * Prints the HTML for the Update cart action
 *
 * @since 0.3.7
 * @return string HTML
*/
function it_cart_buddy_get_update_shopping_cart_html() {
	return apply_filters( 'it_cart_buddy_get_update_shopping_cart_html', '' );
}

/**
 * Prints the HTML for the checkout cart action
 *
 * @since 0.3.7
 * @return string HTML
*/
function it_cart_buddy_get_checkout_shopping_cart_html() {
	return apply_filters( 'it_cart_buddy_get_checkout_shopping_cart_html', '' );
}
