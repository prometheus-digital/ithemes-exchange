<?php
/**
 * This file contains API methods used with Exchange frontend pages and product pages
 * @package IT_Exchange
 * @since 0.4.4
*/

/**
 * Returns a list of all registered IT Exchange pages (including the products slug)
 *
 * @since 0.4.4
 *
 * @param boolean $break_cache pages come from it_storage which caches options. Set this to true to not retreived cached pages
 * @return array
*/
function it_exchange_get_pages( $break_cache=false ) {
	if ( ! $pages = it_exchange_get_option( 'settings_pages', $break_cache ) )
		$pages = array();

	return apply_filters( 'it_exchange_get_pages', $pages, $break_cache );
}

/**
 * Get name for ghost page
 *
 * @since 0.4.0
 *
 * @param string $page page setting
 * @return string url
*/
function it_exchange_get_page_name( $page, $break_cache=false ) { 
	$pages     = it_exchange_get_pages( $break_cache );
	$page_name = empty( $pages[$page . '-name'] ) ? false : $pages[$page . '-name'];
	return apply_filters( 'it_exchange_get_page_name', $page_name, $page, $break_cache );
}

/**
 * Get editable slug for ghost page
 *
 * @since 0.4.4
 *
 * @param string $page page setting
 * @return string
*/
function it_exchange_get_page_slug( $page, $break_cache=false ) { 
	$pages     = it_exchange_get_pages( $break_cache );
	$page_slug = empty( $pages[$page . '-slug'] ) ? false : $pages[$page . '-slug'];
	return apply_filters( 'it_exchange_get_page_slug', $page_slug, $page, $break_cache );
}

/**
 * Is the page using a ghost page?
 *
 * @since 0.4.4
 *
 * @param string $page page setting
 * @return boolean
*/
function it_exchange_is_page_ghost_page( $page, $break_cache=false ) {
	$pages     = it_exchange_get_pages( $break_cache );
	$is_ghost  = ! empty( $pages[$page . '-ghost'] );
	return apply_filters( 'it_exchange_is_page_ghost_page', $is_ghost, $page, $break_cache );
}

/**
 * Is the the current page what we're looking for? 
 *
 * @since 0.4.0
 * @todo add filters/actions
 *
 * @param string $page the exchange page were checking for
 * @return boolean
*/
function it_exchange_is_page( $page ) {
	global $wpdb;
	// Get query var
	$query_var = get_query_var( $page );

	// Return true if set and not product
	if ( $query_var && 'product' != $page )
		return true;

	// Are we doing AJAX, if so, grab product ID from it.
	if ( ! empty( $_GET['it-exchange-sw-ajax'] ) && ! empty( $_GET['sw-product'] ) ) {
		return (boolean) it_exchange_get_product( $_GET['sw-product'] );
	} else {
		// Try to get the post from the slug
		$sql = $wpdb->prepare( 'SELECT ID FROM ' . $wpdb->posts . ' WHERE post_type = "it_exchange_prod" AND post_status = "publish" AND post_name = "%s"', $query_var );
		if ( $id = $wpdb->get_var( $sql ) )
			return true;
	}
	return false;
}

/**
 * Get permalink for ghost page
 *
 * @since 0.4.0
 * @todo add filters/actions
 *
 * @param string $page page setting
 * @return string url
*/
function it_exchange_get_page_url( $page, $clear_settings_cache=false ) {
	$pages = it_exchange_get_pages( $clear_settings_cache );
	$page_slug = $pages[$page . '-slug'];
	$page_name = $pages[$page . '-name'];
	$permalinks = (boolean) get_option( 'permalink_structure' );
	$base = trailingslashit( get_home_url() );

	// Allow add-ons to create their own ghost pages
	$add_on_ghost_pages = apply_filters( 'it_exchange_add_ghost_pages', array() );
	foreach( (array) $add_on_ghost_pages as $addon_page => $data ) {
		if ( $page == $addon_page && ! empty ( $data['url'] ) )
			return $data['url'];
	}

	// Process SuperWidget links
	if ( it_exchange_in_superwidget() && $page_slug != 'transaction' ) {
		// Get current URL without exchange query args
		$url = it_exchange_clean_query_args();
		return add_query_arg( 'ite-sw-state', $page_slug, $url );
	}

	// Store needs to be first
	if ( 'store' == $page ) {
		if ( $permalinks )
			return trailingslashit( $base . $page_slug );
		else
			return add_query_arg( array( $page_slug => 1 ), $base );
	}

	// Any URLS in store breadcrumb need to come next
	if ( in_array( $page, array( 'cart', 'checkout', 'confirmation', 'transaction' ) ) ) {
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
	if ( $account_name && '1' != $account_name && ( 'log-in' != $page && 'log-out' != $page ) ) {
		if ( $permalinks ) {
			$base = trailingslashit( $base . $account_name );
		} else {
			$base = remove_query_arg( $pages['account-slug'], $base );
			$base = add_query_arg( array( $pages['account-slug'] => $account_name ), $base );
		}
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
