<?php
/**
 * This file contains shortcodes registered by Cart Buddy for shopping cart add-ons to use.
 *
 * @since 0.3.7
 * @package IT_Cart_Buddy
*/
add_shortcode( 'cart_buddy_shopping_cart', 'it_cart_buddy_shortcode_shopping_cart_html' );
add_shortcode( 'cart_buddy_add_product_to_cart', 'it_cart_buddy_shortcode_add_product_to_shopping_cart_html' );

/**
 * This shortcode is intended to print a complete shopping cart form
 *
 * Shopping cart addons should hook to this with their cart HTML
 *
 * @since 0.3.7
 * @param array $atts atts passed from WP Shortcode API
 * @param string $content data passed from WP Shortcode API
 * @return string html for shopping cart
*/
function it_cart_buddy_shortcode_shopping_cart_html( $atts, $content='' ) {
	return it_cart_buddy_get_shopping_cart_html( $atts, $content );
}

/**
 * This shortcode is intended to print an Add to Cart HTML block
 *
 * This is a wrapper to it_cart_buddy_get_add_product_to_shopping_cart_html()
 * Shopping cart add-ons do the heavy lifting
 *
 * @since 0.3.7
 * @param array $atts attributess passed from WP Shortcode API
 * @param string $content data passed from WP Shortcode API
 * @return string html for the 'Add to Shopping Cart' HTML
*/
function it_cart_buddy_shortcode_add_product_to_shopping_cart_html( $atts, $content='' ) {
    global $post;
    $defaults['product_id'] = empty( $post->ID ) ? 0: $post->ID;

    // Merge defaults with passed attributes
    $attributes = shortcode_atts( $defaults, $atts );

    // Confirm that the given product_id is a product post
    if ( $product = it_cart_buddy_get_product( $attributes['product_id'] ) )
		return it_cart_buddy_get_add_product_to_shopping_cart_html( $attributes['product_id'], $attributes, $content );
}
