<?php
/**
 * Main API call for CartBuddy
 *
 * @since 0.1
*/
function cartbuddy( $method, $options=array() ) {
	global $cartbuddy_api;

	// Init object if not done so already
	if ( ! is_object( $cartbuddy_api ) ) {
		require_once( 'main.php' );
		$cartbuddy_api = new IT_Cartbuddy_API();
	}
	
	$api_method = array( $cartbuddy_api, $method );

	// If method exists, call it. Otherise, return error
	if ( ! is_callable( $api_method ) )
		return new WP_Error( 'it-cb-invalid-api-call', __( 'Undefined CartBuddy API method call', 'LION' ) );

	return call_user_func( $api_method, $options );
}
do_action( 'it_cartbuddy-api_loaded' );
