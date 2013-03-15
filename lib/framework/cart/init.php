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
	include( 'checkout-template-functions.php' );

	// Utility hooks
	add_action( 'it_cart_buddy_template_redirect', 'it_cart_buddy_default_shopping_cart_redirect_checkout_to_cart' );

	// Cart Data processing hooks
	add_action( 'it_cart_buddy_remove_product_from_cart', 'it_cart_buddy_default_cart_remove_product_from_shopping_cart', 9 );

	// High level Cart API Hooks for theme developers 
	add_filter( 'it_cart_buddy_get_shopping_cart_cart_html', 'it_cart_buddy_default_cart_get_cart_html', 9, 2 );
	add_filter( 'it_cart_buddy_get_add_product_to_shopping_cart_html', 'it_cart_buddy_default_cart_get_add_product_to_cart_html', 9, 3 );
	add_filter( 'it_cart_buddy_get_remove_product_from_shopping_cart_html', 'it_cart_buddy_default_cart_get_remove_product_from_shopping_cart_html', 9, 2 );
	add_filter( 'it_cart_buddy_get_empty_shopping_cart_html', 'it_cart_buddy_default_cart_get_empty_cart_button', 9 );
	add_filter( 'it_cart_buddy_get_update_shopping_cart_html', 'it_cart_buddy_default_cart_get_update_cart_button', 9 );
	add_filter( 'it_cart_buddy_get_checkout_shopping_cart_html', 'it_cart_buddy_default_cart_get_checkout_cart_button', 9 );

	// High level Checkout API hooks for theme developers
	add_filter( 'it_cart_buddy_get_cart_checkout_page_html', 'it_cart_buddy_default_cart_get_checkout_html', 9, 2 );
	add_filter( 'it_cart_buddy_get_cart_checkout_form_open_html', 'it_cart_buddy_default_cart_get_checkout_form_open_html', 9, 2 );
	add_filter( 'it_cart_buddy_get_cart_checkout_customer_form_fields', 'it_cart_buddy_default_cart_get_checkout_customer_form_fields', 9, 2 );
	add_filter( 'it_cart_buddy_get_cart_checkout_order_summary', 'it_cart_buddy_default_cart_get_checkout_order_summary', 9, 2 );
	add_filter( 'it_cart_buddy_get_cart_checkout_place_order', 'it_cart_buddy_default_cart_get_checkout_place_order', 9, 2 );
	add_filter( 'it_cart_buddy_get_cart_checkout_form_close_html', 'it_cart_buddy_default_cart_get_checkout_form_close_html', 9, 2 );
	add_filter( 'it_cart_buddy_get_cart_checkout_order_button', 'it_cart_buddy_default_cart_get_checkout_order_button', 9, 2 );
} else {
	// Backend
}
