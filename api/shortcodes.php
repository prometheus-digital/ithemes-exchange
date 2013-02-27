<?php
/**
 * Register shortcodes. Most of these functions call other public API functions
 *
 * @since 0.3.7
 * @package IT_Cart_Buddy
*/

/**
 * Prints the add to cart button or link (dependant on active cart add-on)
 *
 * If no product_id is passed, it will attempt to find the current product from global $post var
 * If not product ID is passed and no global $post producut-type is available, it returns nothing.
 * eg: [cart_buddy_add_to_cart product_id=3] or [cart_buddy_add_to_cart]
 *
 * @since 0.3.7
 * @param array $attributes shortcode attributes
 * @return string HTML for add_to_cart
*/
function it_cart_buddy_shortcode_add_to_cart_button( $attributes ) {
	global $post;
	$defaults['product_id'] = empty( $post->ID ) ? 0: $post->ID;

	// Merge defaults with passed attributes
	$attributes = shortcode_atts( $defaults, $attributes );

	// Confirm that the given product_id is a product post
	if ( $product = it_cart_buddy_get_product( $attributes['product_id'] ) ) {
		if ( ! empty($product->ID ) )
			return it_cart_buddy_get_add_to_cart_button( $product->ID );
	}
}

/**
 * Prints the HTML for the shopping cart
 *
 * This is a passthrough to it_cart_buddy_get_shopping_cart_html found in api/carts.php
 *
 * @since 0.3.7
 * @param array $attributes shortcode attributes
 * @return string HTML or nothing if an active cart is not found
*/
function it_cart_buddy_shortcode_get_shopping_cart_html( $attributes ) {
	return it_cart_buddy_get_shopping_cart_html();
}
