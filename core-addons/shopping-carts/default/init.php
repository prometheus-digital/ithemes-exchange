<?php
/**
 * Default Cart Buddy Shopping Cart
 *
 * @since 0.3.0
 * @package IT_Cart_Buddy
*/

if ( ! is_admin() ) {
	// Includes
	include( 'lib.php' );
	include( 'template-functions.php' );
	include( 'data-functions.php' );

	// Low Level Cart API Hooks for theme developers. See /api/carts/cart-template_tags.php
	add_filter( 'it_cart_buddy_get_cart_products-default-shopping-cart', 'it_cart_buddy_default_cart_get_products' );
	add_filter( 'it_cart_buddy_get_cart_product-default-shopping-cart', 'it_cart_buddy_default_cart_get_product', 10, 2 );
	add_filter( 'it_cart_buddy_get_cart_get_product_attribute-default-shopping-cart', 'it_cart_buddy_get_product_attribute', 10, 3 );
	add_filter( 'it_cart_buddy_get_cart_form_vars-default-shopping-cart', 'it_cart_buddy_default_cart_get_form_vars', 10, 2 );
	add_filter( 'it_cart_buddy_get_cart_total-default-shopping-cart', 'it_cart_buddy_default_cart_get_total' );

	// Cart Data processing hooks
	add_action( 'template_redirect', 'it_cart_buddy_default_cart_add_product_to_cart' );
	add_action( 'template_redirect', 'it_cart_buddy_default_cart_empty_cart' );

	// High level Cart API Hooks for theme developers 
	add_filter( 'it_cart_buddy_get_shopping_cart_html-default-shopping-cart', 'it_cart_buddy_default_cart_get_cart_html', 10, 2 );
	add_filter( 'it_cart_buddy_get_add_product_to_shopping_cart_html-default-shopping-cart', 'it_cart_buddy_default_cart_get_add_product_to_cart_html', 10, 3 );
} else {
	// Backend
}
