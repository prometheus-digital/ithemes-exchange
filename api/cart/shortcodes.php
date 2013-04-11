<?php
/**
 * This file contains shortcodes registered by iThemes Exchange for shopping cart add-ons to use.
 *
 * @since 0.3.7
 * @package IT_Exchange
*/
add_shortcode( 'it_exchange_shopping_cart', 'it_exchange_get_shopping_cart_html' );
add_shortcode( 'it_exchange_checkout', 'it_exchange_get_checkout_html' );
add_shortcode( 'it_exchange_add_product_to_cart', 'it_exchange_shortcode_add_product_to_shopping_cart_html' );

/**
 * This shortcode is intended to print an Add to Cart HTML block
 *
 * This is a wrapper to it_exchange_get_add_product_to_shopping_cart_html()
 * Shopping cart add-ons do the heavy lifting
 *
 * @since 0.3.7
 * @param array $atts attributess passed from WP Shortcode API
 * @param string $content data passed from WP Shortcode API
 * @return string html for the 'Add to Shopping Cart' HTML
*/
function it_exchange_shortcode_add_product_to_shopping_cart_html( $atts, $content='' ) {
    global $post;
    $defaults['product_id'] = empty( $post->ID ) ? 0: $post->ID;
	$defaults['title']      = __( 'Add to Cart', 'LION' );

    // Merge defaults with passed attributes
    $attributes = shortcode_atts( $defaults, $atts );

    // Confirm that the given product_id is a product post
    if ( $product = it_exchange_get_product( $attributes['product_id'] ) )
		return it_exchange_get_add_product_to_shopping_cart_html( $attributes );
}
