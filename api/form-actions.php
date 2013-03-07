<?php
/**
 * These are hooks that add-ons should use for form actions
 * @since 0.3.7
 * @package IT_Cart_Buddy
*/

/**
 * Fires a WP action hook when any of the registered Cart Buddy link / form action are set 
 *
 * @since 0.3.7
 * @reutn void
*/
function it_cart_buddy_cart_actions() {
	
	// Fires whena a product is being added to product to a cart
	foreach( (array) it_cart_buddy_get_action_vars() as $slug => $var ) {
		if ( isset( $_REQUEST[$var] ) ) {
			do_action( $slug, $_REQUEST[$var] );
		}
	}
}
add_action( 'template_redirect', 'it_cart_buddy_cart_actions' );

/**
 * Returns an action var used in links and forms
 *
 * @since 0.3.7
 * @param string $var var being requested
 * @return string var used in links / forms for different actions
*/
function it_cart_buddy_get_action_var( $var ) {
	$vars = it_cart_buddy_get_action_vars();
	$value  = empty( $vars[$var] ) ? false : $vars[$var];
	return apply_filters( 'it_cart_buddy_get_action_var', $value, $var );
}

/**
 * Returns an array of all action vars registered with Cart Buddy
 *
 * @since 0.3.7
 * @return array
*/
function it_cart_buddy_get_action_vars() {
	// Default vars
	$defaults = array(
		'add_product_to_cart'      => 'it_cart_buddy_add_product_to_cart',
		'remove_product_from_cart' => 'it_cart_buddy_remove_product_from_cart',
		'update_cart'              => 'it_cart_buddy_update_cart',
		'empty_cart'               => 'it_cart_buddy_empty_cart',
		'proceed_to_checkout'      => 'it_cart_buddy_proceed_to_checkout',
		'view_cart'                => 'it_cart_buddy_view_cart',
		'purchase_cart'            => 'it_cart_buddy_purchase_cart',
		'display_message'          => 'it_cart_buddy_message',
		'display_errors'           => 'it_cart_buddy_errors',
		'transaction_id'           => 'it_cart_buddy_transaction_id',
		'transaction_method'       => 'it_cart_buddy_transaction_method',
	);
	return apply_filters( 'it_cart_buddy_get_action_vars', $defaults );
}
