<?php
/**
 * These are hooks that add-ons should use for form actions
 * @since 0.3.7
 * @package IT_Exchange
*/

/**
 * Returns a field name used in links and forms
 *
 * @since 0.4.0
 * @param string $var var being requested
 * @return string var used in links / forms for different actions
*/
function it_exchange_get_field_name( $var ) {
	$field_names = it_exchange_get_field_names();
	return empty( $field_names[$var] ) ? false : $field_names[$var];
}

/**
 * Returns an array of all field names registered with iThemes Exchange
 *
 * @since 0.4.0
 * @return array
*/
function it_exchange_get_field_names() {
	// required field names
	$required = array(
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
	//We don't want users to modify the core vars, but we should let them add new ones.
	return array_merge( $required, apply_filters( 'it_exchange_default_field_names', array() ) );
}

/**
 * Get permalink for ghost page
 *
 * @since 0.4.0
 *
 * @param string $page page setting
 * @return string url
*/
function it_exchange_get_page_url( $page, $clear_settings_cache=false ) {
	$pages = it_exchange_get_option( 'settings_pages', $clear_settings_cache );
	$page_slug = $pages[$page . '-slug'];
	$page_name = $pages[$page . '-name'];
	$permalinks = (boolean) get_option( 'permalink_structure' );
	$base = trailingslashit( get_home_url() );

	// Store needs to be first
	if ( 'store' == $page ) {
		if ( $permalinks )
			return trailingslashit( $base . $page_slug );
		else
			return add_query_arg( array( $page_slug => 1 ), $base );
	}

	// Any URLS in store breadcrumb need to come next
	if ( in_array( $page, array( 'cart', 'checkout', 'confirmation', 'reports' ) ) ) {
		if ( $permalinks )
			return trailingslashit( $base . $pages['store-slug'] . '/' . $page_slug );
		else
			return add_query_arg( array( $pages['store-slug'] => 1, $page_slug => 1 ), $base );
	}

	// Replace account value with name if user is logged in
	if ( $permalinks )
		$base = trailingslashit( $base . $pages['account-slug'] );
	else
		$base = add_query_arg( array( $pages['account-slug'] => 1 ), $base );

	$account_name = get_query_var( 'account' );
	if ( $account_name && '1' != $account_name && 'log-in' != $page ) {
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
