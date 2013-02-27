<?php
/**
 * This file contains shortcodes registered by Cart Buddy for shopping cart add-ons to use.
 *
 * @since 0.3.7
 * @package IT_Cart_Buddy
*/
add_shortcode( 'cart_buddy_shopping_cart', 'it_cart_buddy_shortcode_get_cart_html' );
add_shortcode( 'cart_buddy_add_to_cart', 'it_cart_buddy_shortcode_add_to_cart_button' );

/**
 * This shortcode is intended to print a complete shopping cart form
 *
 * Shopping cart addons should hook to this with their cart HTML
 *
 * @since 0.3.7
 * @param array $args args passed from WP Shortcode API
 * @param string $content data passed from WP Shortcode API
 * @return string html for shopping cart
*/
function it_cart_buddy_shortcode_get_cart_html( $args, $content='' ) {
	$active_cart = it_cart_buddy_get_active_shopping_cart( 'slug' );
	return apply_filters( 'it_cart_buddy_get_shopping_cart_html-' . $active_cart, '', $args, $content );
}
