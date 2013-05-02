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
	$notices = (boolean) it_exchange_get_notices() ? it_exchange_get_notices() : array();
	$notice = trim( $notice );
	if ( ! empty( $notice ) )
		$notices[] = $notice;
	$GLOBALS['it_exchange']['notices'] = $notices;
}

/**
 * Gets all notices from Exchange global
 *
 * @since 0.4.0
 *
 * @return array an array of strings
*/
function it_exchange_get_notices() {
	return empty( $GLOBALS['it_exchange']['notices'] ) ? array() : (array) $GLOBALS['it_exchange']['notices'];
}
