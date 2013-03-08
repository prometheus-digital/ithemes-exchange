<?php
/**
 * Functions for defining, initiating, and displaying Cart Buddy Errors
 *
 * @since 0.3.7
 * @package IT_Cart_Buddy
*/

/**
 * Returns an array of error message params to error messages
 *
 * @since 0.3.7
 * @return array
*/
function it_cart_buddy_get_error_messages() {
	$errors = array(
		'default' => __( 'There was an error processing your request. Please try again.', 'LION' ),
	);
	return apply_filters( 'it_cart_buddy_get_error_messages', $errors );
}

/**
 * Returns a specific error message string based on the var paramater
 *
 * @since 0.3.7
 * @param array $errors an array of slugs for the error message being requested
 * @return string the error message
*/
function it_cart_buddy_get_error_message( $errors=array() ) {
	// Retreive list of all registered errors
	$all_errors = it_cart_buddy_get_error_messages();
	$all_errors = empty( $all_errors ) ? false : $all_errors;

	// Grab the error var
	$error_var = it_cart_buddy_get_action_var( 'error_message' );

	// Grab errors from REQUEST params
	$request_vars = empty( $_REQUEST[$error_var] ) ? array() : (array) $_REQUEST[$error_var];

	// Cast errors param to array
	$param_vars = (array) $errors;

	// Merge any errors from the REQUEST and the param arrays
	$errors = ITUtility::merge_defaults( $request_vars, $param_vars );

	// Return false if not errors were passed in via param or no errors are registered
	if ( empty( $errors ) || empty( $all_errors ) )
		return false;

	// Init return value
	$error_messages = array();

	// Loop through registered errors and add messages to return value if they have been requested.
	foreach( $all_errors as $slug => $error ) {
		if ( in_array( $slug, $errors ) )
			$error_messages[$slug] = $all_errors[$slug];
	}

	// Return messages if not empty
	if ( ! empty( $error_messages ) )
		return $error_messages;
	
	return false;
}

/**
 * Returns the HTML div and the conatining messages
 *
 * If there is a $_REQUEST with a registered error message, that will be included.
 * If the errors param isn't empty, those will be included as well.
 *
 * @since 0.3.7
 * @param array $errors an array of error message slugs
 * @return string HTML
*/
function it_cart_buddy_get_errors_div( $errors=array() ) {
	$errors = it_cart_buddy_get_error_message( $errors );
	if ( ! $errors )
		return false;

	$count = count( $errors );

	if ( 1 === $count ) {
		$error = array_values( $errors );
		$error_message = '<p class="cart-buddy-errors">' . $error[0] . '</p>';
	} else {
		$error_message = '<ul class="cart-buddy-errors">';
		foreach( $errors as $error ) {
			$error_message .= '<li>' . $error . '</li>';
		}
		$error_message .= '</ul>';
	}
	$html = '<div class="cart-buddy-error-message">' . $error_message . '</div>';
	return $html;
}

/**
 * Echos the it_cart_buddy_get_error_message_div() results
 *
 * @since 0.3.7
 * @param array $errors an array of error message slugs
 * @return string HTML
*/
function it_cart_buddy_errors_div( $errors=array() ) {
	echo it_cart_buddy_get_errors_div( $errors );
}
