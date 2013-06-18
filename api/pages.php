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

	return apply_filters( 'it_exchange_get_pages', $pages );
}
