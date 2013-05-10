<?php
/**
 * API functions pertaining to user sessions
 *
 * - IT_Exchange_Session object is stored in a global variable
 * - Sessions are only active on the frontend of the web site
 * - iThemes Exchange inits the session and loads the data for you. Add-ons should not need to start the session
 *
 * @since 0.3.3
 * @package IT_Exchange
 * @todo fix all the comments!
*/

/**
 * This grabs you a copy of the IT_Exchange_Session object
 *
 * @since 0.4.0
 * @return object  instance of IT_Exchange_Session
*/
function it_exchange_get_session() {
	// No sessions in the admin
	if ( is_admin() )
		return;

	$session = empty( $GLOBALS['it_exchange']['session'] ) ? false : $GLOBALS['it_exchange']['session'];
	return $session;
}

/**
 * Returns session array from current session
 *
 * @since 0.4.0
 * @return array  an array of session_data stored in $_SESSION['it_exchange']
*/
function it_exchange_get_session_type( $type ) {
	$session = it_exchange_get_session();
	return $session->get( $type );
}

/**
 * Adds session data to the iThemes Exchange Session.
 *
 * This simply adds an item to the data array of the PHP Session.
 * Shopping cart plugins are responsible for managing the structure of the data
 * If a key is passed, it will be used as the key in the data array. Otherwise, the data array will just be
 * incremented. eg: ['data'][] = $data;
 *
 * @since 0.4.0
 * @param mixed $data data as passed by the shopping cart
 * @param mixed $key optional identifier for the data.
 * @return void 
*/
function it_exchange_set_session_type( $type, $key, $data=false ) {
	$session = it_exchange_get_session();
	$session->set( $type, $key, $data );
	do_action( 'it_exchange_set_session_' . $type, $data, $key );
}

/**
 * Adds session data to the iThemes Exchange Session.
 *
 * This simply adds an item to the data array of the PHP Session.
 * Shopping cart plugins are responsible for managing the structure of the data
 * If a key is passed, it will be used as the key in the data array. Otherwise, the data array will just be
 * incremented. eg: ['data'][] = $data;
 *
 * @since 0.4.0
 * @param mixed $data data as passed by the shopping cart
 * @param mixed $key optional identifier for the data.
 * @return void 
*/
function it_exchange_update_session_type( $type, $key, $data ) {
	$session = it_exchange_get_session();
	$session->update( $type, $key, $data );
	do_action( 'it_exchange_update_session_' . $type, $data, $key );
}

/**
 * Removes data from the session if the passed key exists
 *
 * @since 0.4.0
 * @param mixed $key array key for the data to be removed
 * @return boolean
*/
function it_exchange_unset_session_type( $type, $key ) {
	$session = it_exchange_get_session();
	$result = $session->unset_data( $type, $key );
	do_action( 'it_exchange_unset_session_' . $type, $key );
}

/**
 * Removes all data from the session
 *
 * @since 0.4.0
 * @return boolean
*/
function it_exchange_clear_session_type( $type ) {
	$session = it_exchange_get_session();
	$result = $session->clear( $type );
	do_action( 'it_exchange_clear_session_' . $type );
}
