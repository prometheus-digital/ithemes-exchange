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
	$errors = (boolean) it_exchange_get_errors() ? it_exchange_get_errors(): array();
	$error = trim( $error );
	if ( ! empty( $error ) )
		$errors[] = $error;
	$GLOBALS['it_exchange']['errors'] = $errors;
}

/**
 * Gets all errors from Exchange global
 *
 * @since 0.4.0
 *
 * @return array an array of strings
*/
function it_exchange_get_errors() {
	return empty( $GLOBALS['it_exchange']['errors'] ) ? array() : (array) $GLOBALS['it_exchange']['errors'];
}
