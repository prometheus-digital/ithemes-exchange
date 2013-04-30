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

/**
 * Get permalink for ghost page
 *
 * @since 0.4.0
 *
 * @param string $page page setting
 * @return string url
*/
function  it_exchange_get_page_url( $page, $clear_settings_cache=false ) {
	$pages = it_exchange_get_option( 'exchange_settings_pages', $clear_settings_cache );
	$page_slug = $pages[$page . '-slug'];
	$page_name = $pages[$page . '-name'];
	$permalinks = (boolean) get_option( 'permalink_structure' );
	$base = trailingslashit( get_home_url() );

	if ( 'store' == $page ) {
		if ( $permalinks )
			return trailingslashit( $base . $page_slug );
		else
			return add_query_arg( array( $page_slug => 1 ), $base );
	}

	if ( in_array( $page, array( 'cart', 'checkout', 'confirmation' ) ) ) {
		if ( $permalinks )
			return trailingslashit( $base . $pages['store-slug'] . '/' . $page_slug );
		else
			return add_query_arg( array( $pages['store-slug'] => 1, $page_slug => 1 ), $base );
	}

	if ( $permalinks )
		$base = trailingslashit( $base . $pages['account-slug'] );
	else
		$base = add_query_arg( array( $pages['account-slug'] => 1 ), $base );

	$account_name = get_query_var( 'account' );
	if ( $account_name && '1' != $account_name ) {
		if ( $permalinks ) {
			$base = trailingslashit( $base . $account_name );
		} else {
			$base = remove_query_arg( $pages['account-slug'], $base );
			$base = add_query_arg( array( $pages['account-slug'] => $account_name ), $base );
		}
	}

	if ( 'profile-edit' == $page ) {
		if ( $permalinks )
			return trailingslashit( $base . $pages['profile-slug'] . '/' . $pages['profile-edit-slug'] );
		else
			return add_query_arg( array( $pages['profile-slug'] => 1,  $pages['profile-edit-slug'] => 1 ), $base );
	}

	if ( 'account' == $page ) {
		return $base;
	} else {
		if ( $permalinks )
			return trailingslashit( $base . $page_slug );
		else
			return add_query_arg( array( $page_slug => 1 ), $base );
	}
}
