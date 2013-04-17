<?php
/**
 * These are hooks that add-ons should use for form actions
 * @since 0.3.7
 * @package IT_Exchange
*/

/**
 * Fires a WP action hook when any of the registered iThemes Exchange link / form action are set 
 *
 * @since 0.3.7
 * @reutn void
*/
function it_exchange_cart_actions() {
	
	// Fires whena a product is being added to product to a cart
	foreach( (array) it_exchange_get_action_vars() as $slug => $var ) {
		if ( isset( $_REQUEST[$var] ) ) {
			do_action( 'it_exchange_' . $slug, $_REQUEST[$var] );
		}
	}
}
add_action( 'template_redirect', 'it_exchange_cart_actions' );

/**
 * Returns an action var used in links and forms
 *
 * @since 0.3.7
 * @param string $var var being requested
 * @return string var used in links / forms for different actions
*/
function it_exchange_get_action_var( $var ) {
	$vars = it_exchange_get_action_vars();
	$value  = empty( $vars[$var] ) ? false : $vars[$var];
	return apply_filters( 'it_exchange_get_action_var', $value, $var );
}

/**
 * Returns an array of all action vars registered with iThemes Exchange
 *
 * @since 0.3.7
 * @return array
*/
function it_exchange_get_action_vars() {
	// Default vars
	$defaults = array(
		'add_product_to_cart'      => 'it-exchange-add-product-to-cart',
		'remove_product_from_cart' => 'it-exchange-remove-product-from-cart',
		'update_cart_action'       => 'it-exchange-update-cart-request',
		'empty_cart'               => 'it-exchange-empty-cart',
		'proceed_to_checkout'      => 'it-exchange-proceed-to-checkout',
		'view_cart'                => 'it-exchange-view-cart',
		'purchase_cart'            => 'it-exchange-purchase-cart',
		'alert_message'            => 'it-exchange-messages',
		'error_message'            => 'it-exchange-errors',
		'transaction_id'           => 'it-exchange-transaction-id',
		'transaction_method'       => 'it-exchange-transaction-method',
	);
	return apply_filters( 'it_exchange_get_action_vars', $defaults );
}
