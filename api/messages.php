<?php
/**
 * Functions for defining, initiating, and displaying iThemes Exchange Errors and Notices
 *
 * Core types are 'notice' and 'error'
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
 * @param string $context arbitrary context. Allows you to display messages in specific areas for specific actions
*/
function it_exchange_add_message( $type, $message, $context='' ) {
	$message = array(
		'message' => $message,
		'context' => $context,
	);
	it_exchange_add_session_data( $type, $message );
	do_action( 'it_exchange_add_message', $type, $message, $context );
}

/**
 * Gets messages to Exchange session
 *
 * @since 0.4.0
 *
 * @param string $type Type of message you want displayed
 * @param boolean $clear Should we clear the messages before returning them
 * @param string $requested_context Are we looking for a specific context
 * @param boolean $message_only Do you want the message returned or the message and the context
 * @return mixed message string or message|context array
*/
function it_exchange_get_messages( $type, $clear=true, $requested_context=false, $message_only=true ) {
	$messages          = it_exchange_get_session_data( $type );
	$filtered_messages = array();

	// Parse messages. Some are arrays with context. Some are just strings.
	foreach( $messages as $key => $message_array ) {

		$include_message = true;

		// If its an array, the message is in the message key
		if ( is_array( $message_array ) ) {

			// Set message and context
			$message = $message_array['message'];
			$context = empty( $message['context'] ) ? '' : $message['context'];

			// If a context arg was passed, filter out all messages that don't have that context
			if ( ! empty( $requested_context ) ) {
				if ( empty( $context ) || $requested_context != $context )
					$include_message = false;
			}
		} else {
			// Set message and context
			$message = $message_array;
			$context = '';

			// If a context arg was passed, do not include this message
			if ( ! empty( $requested_context ) ) {
				if ( empty( $context ) || $requested_context != $context )
					$include_message = false;
			}
		}

		// Add the message to the filtered messages array if not false
		if ( $include_message )
			$filtered_messages[] = empty( $message_only ) ? array( 'message' => $message, 'context' => $context ) : $message;
	}

	// Clear the messages if set
	if ( $clear )
		it_exchange_clear_messages( $type, $context );

	$messages = empty( $filtered_messages ) ? false : $filtered_messages;

	return apply_filters( 'it_exchange_get_messages', $messages, $type, $clear, $requested_context, $message_only );
}

/**
 * Checks if messages are in the to Exchange session
 *
 * @since 0.4.0
 *
 * @param string $type Type of message you want displayed
 * @param string $context arbitrary context. Allows you check for messages for specific actions
*/
function it_exchange_has_messages( $type, $context=false ) {
	return (bool) apply_filters( 'it_exchange_has_messages', it_exchange_get_messages( $type, false, $context ), $type, $context );
}

/**
 * Checks if messages are in the to Exchange session
 *
 * @since 0.4.0
 *
 * @param string $type Type of message you want displayed
 * @param string $context allows you to only clear messages for a specific context
*/
function it_exchange_clear_messages( $type, $context=false ) {

	// If not context was passed, clear all messages for the passed type
	if ( empty( $context ) ) {
		it_exchange_clear_session_data( $type );
	} else {
		// If we're only clearing one type, get everything, clear everything and then put back what we want to keep
		$all_type_messages = it_exchange_get_messages( $type, false, false, false );

		// Clear them all
		it_exchange_clear_messages( $type );

		foreach( $all_type_messages as $key => $message_array ) {
			if ( $message_array['context'] != $context )
				it_exchange_add_message( $type, $message_array['message'], $message_array['context'] );
		}

	}
	do_action( 'it_exchange_clear_messages', $type, $context );
}
