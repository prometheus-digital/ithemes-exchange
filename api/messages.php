<?php
/**
 * Functions for defining, initiating, and displaying iThemes Exchange Errors and Notices
 *
 * @since 0.4.0
 * @package IT_Exchange
*/

/**
 * Adds messages to Exchange session
 *
 * @since 0.4.0
 *
 * @param string $type Type of message you want displayed
 * @param string $message the message you want displayed
*/
function it_exchange_add_message( $type, $message ) {
	it_exchange_update_session_data( $type, $message );
}

/**
 * Gets messages to Exchange session
 *
 * @since 0.4.0
 *
 * @param string $type Type of message you want displayed
*/
function it_exchange_get_messages( $type ) {
	return it_exchange_get_session_data( $type );
}

/**
 * Checks if messages are in the to Exchange session
 *
 * @since 0.4.0
 *
 * @param string $type Type of message you want displayed
*/
function it_exchange_has_messages( $type ) {
	return (bool) it_exchange_get_session_data( $type );
}

/**
 * Checks if messages are in the to Exchange session
 *
 * @since 0.4.0
 *
 * @param string $type Type of message you want displayed
*/
function it_exchange_clear_messages( $type ) {
	return (bool) it_exchange_clear_session_data( $type );
}