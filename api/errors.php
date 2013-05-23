<?php
/**
 * Functions for defining, initiating, and displaying iThemes Exchange Errors
 *
 * @since 0.3.7
 * @package IT_Exchange
*/

/**
 * Adds errors to Exchange Errors global for use by theme
 *
 * @since 0.4.0
 *
 * @param string $error the message you want displayed
*/
function it_exchange_add_error( $error ) {
	$errors = it_exchange_has_errors() ? it_exchange_get_errors(): array();
	$error = trim( $error );
	if ( ! empty( $error ) )
		$errors[] = $error;
	it_exchange_update_session_data( 'errors', $errors );
}

/**
 * Gets all errors from Exchange global
 *
 * @since 0.4.0
 *
 * @return array an array of strings
*/
function it_exchange_get_errors() {
	$errors = it_exchange_get_session_data( 'errors' );
	$GLOBALS['it_exchange']['errors'] = empty( $errors ) ? array() : $errors;
	return $errors;
}

/**
 * Checkes if errors exist
 *
 * @since 0.4.0
 *
 * @return boolean
*/
function it_exchange_has_errors() {
	$errors = it_exchange_get_session_data( 'errors' );
	$GLOBALS['it_exchange']['errors'] = empty( $errors ) ? array() : $errors;
	return (bool) $errors;
}
