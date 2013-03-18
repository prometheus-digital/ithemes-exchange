<?php
/**
 * Functions for defining, initiating, and displaying Cart Buddy Alerts
 *
 * @since 0.3.7
 * @package IT_Cart_Buddy
*/

/**
 * Returns an array of alert message params to alert messages
 *
 * @since 0.3.7
 * @return array
*/
function it_cart_buddy_get_alert_messages() {
	$alerts = array();
	return apply_filters( 'it_cart_buddy_get_alert_messages', $alerts );
}

/**
 * Returns a specific alert message string based on the var paramater
 *
 * @since 0.3.7
 * @param array $alerts an array of slugs for the alert message being requested
 * @return string the alert message
*/
function it_cart_buddy_get_alert_message( $alerts=array() ) {
	// Retreive list of all registered alerts
	$all_alerts = it_cart_buddy_get_alert_messages();
	$all_alerts = empty( $all_alerts ) ? false : $all_alerts;

	// Grab the alert var
	$alert_var = it_cart_buddy_get_action_var( 'alert_message' );

	// Grab alerts from REQUEST params
	$request_vars = empty( $_REQUEST[$alert_var] ) ? array() : (array) $_REQUEST[$alert_var];

	// Cast alerts param to array
	$param_vars = (array) $alerts;

	// Merge any alerts from the REQUEST and the param arrays
	$alerts = ITUtility::merge_defaults( $request_vars, $param_vars );

	// Return false if not alerts were passed in via param or no alerts are registered
	if ( empty( $alerts ) || empty( $all_alerts ) )
		return false;

	// Init return value
	$alert_messages = array();

	// Loop through registered alerts and add messages to return value if they have been requested.
	foreach( $all_alerts as $slug => $alert ) {
		if ( in_array( $slug, $alerts ) )
			$alert_messages[$slug] = $all_alerts[$slug];
	}

	// Return messages if not empty
	if ( ! empty( $alert_messages ) )
		return $alert_messages;
	
	return false;
}

/**
 * Returns the HTML div and the conatining messages
 *
 * If there is a $_REQUEST with a registered alert message, that will be included.
 * If the alerts param isn't empty, those will be included as well.
 *
 * @since 0.3.7
 * @param array $alerts an array of alert message slugs
 * @return string HTML
*/
function it_cart_buddy_get_alerts_div( $alerts=array() ) {
	$alerts = it_cart_buddy_get_alert_message( $alerts );
	if ( ! $alerts )
		return false;

	$count = count( $alerts );

	if ( 1 === $count ) {
		$alert = array_values( $alerts );
		$alert_message = '<p class="cart-buddy-alerts">' . $alert[0] . '</p>';
	} else {
		$alert_message = '<ul class="cart-buddy-alerts">';
		foreach( $alerts as $alert ) {
			$alert_message .= '<li>' . $alert . '</li>';
		}
		$alert_message .= '</ul>';
	}
	$html = '<div class="cart-buddy-alert-message">' . $alert_message . '</div>';
	return $html;
}

/**
 * Echos the it_cart_buddy_get_alert_message_div() results
 *
 * @since 0.3.7
 * @param array $alerts an array of alert message slugs
 * @return string HTML
*/
function it_cart_buddy_alerts_div( $alerts=array() ) {
	echo it_cart_buddy_get_alerts_div( $alerts );
}
