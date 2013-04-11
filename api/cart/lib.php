<?php
/**
 * Additional Functions
 * @since 0.3.7
 * @package IT_Exchange
*/

/**
 * Return the URL for a specific page
 *
 * @since 0.3.7
 * @return string URL
*/
function it_exchange_get_page_url( $page ) {
	$page_id = it_exchange_get_page_id( $page );
	return apply_filters( 'it_exchange_get_page_url', get_permalink( $page_id ), $page );
}

/**
 * Return the ID of a specific iThemes Exchange page as set in options
 *
 * @return integer the WordPress page id if it exists.
*/
function it_exchange_get_page_id( $page ) {
	$pages = it_exchange_get_option( 'exchange_settings_pages' );
	$id = empty( $pages[$page] ) ? false : (integer) $pages[$page];
	return apply_filters( 'it_exchange_get_page_id', $id, $page );;
}
