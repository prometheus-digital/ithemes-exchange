<?php
/**
 * Functions for defining, initiating, and displaying iThemes Exchange Notices 
 *
 * @since 0.3.7
 * @package IT_Exchange
*/

/**
 * Adds notices to Exchange Notices global for use by theme
 *
 * @since 0.4.0
 *
 * @param string $notice the message you want displayed
*/
function it_exchange_add_notice( $notice ) {
	$notices = it_exchange_has_notices() ? it_exchange_get_notices() : array();
	$notice = trim( $notice );
	if ( ! empty( $notice ) )
		$notices[] = $notice;
	it_exchange_update_session_data( 'notices', $errors );
}

/**
 * Gets all notices from Exchange global
 *
 * @since 0.4.0
 *
 * @return array an array of strings
*/
function it_exchange_get_notices() {
	$notices = it_exchange_get_session_data( 'notices' );
	$GLOBALS['it_exchange']['notices'] = empty( $notices ) ? array() : $notices;
	return $notices;
}

/**
 * Checkes if notices exist
 *
 * @since 0.4.0
 *
 * @return boolean
*/
function it_exchange_has_notices() {
	$notices = it_exchange_get_session_data( 'notices' );
	$GLOBALS['it_exchange']['notices'] = empty( $notices ) ? array() : $notices;
	return (bool) $notices;
}