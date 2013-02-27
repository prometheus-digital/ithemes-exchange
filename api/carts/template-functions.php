<?php
/**
 * These functions print HTML elements for the cart
 *
 * @since 0.3.7
 * @package IT_Cart_Buddy
*/

/**
 * Generates an add to cart button / link
 *
 * @since 0.3.7
 * @return string HTML for the button
*/
function it_cart_buddy_get_add_to_cart_button( $product ) { 
    $active_cart = it_cart_buddy_get_active_shopping_cart( 'slug' );
    return apply_filters( 'it_cart_buddy_get_add_to_cart_button-' . $active_cart, '', $product );
}

/**
 * This function returns the HTML for the shopping cart
 *
 * @since 0.3.7
 * @return string html for the shopping cart
*/
function it_cart_buddy_get_shopping_cart_html() {
    $active_cart = it_cart_buddy_get_active_shopping_cart( 'slug' );
    return apply_filters( 'it_cart_buddy_get_shopping_cart_html-' . $active_cart, '' );
}
