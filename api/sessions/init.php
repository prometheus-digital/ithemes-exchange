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
*/

/**
 * This grabs you a copy of the IT_Exchange_Session object
 *
 * @since 0.3.3
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
 * Returns session_data array from current session
 *
 * @since 0.3.3
 * @return array  an array of session_data stored in $_SESSION['it_exchange']
*/
function it_exchange_get_session_data() {
	$session = it_exchange_get_session();
	return $session->get_data();
}

/**
 * Adds session data to the iThemes Exchange Session.
 *
 * This simply adds an item to the data array of the PHP Session.
 * Shopping cart plugins are responsible for managing the structure of the data
 * If a key is passed, it will be used as the key in the data array. Otherwise, the data array will just be
 * incremented. eg: ['data'][] = $data;
 *
 * @since 0.3.7
 * @param mixed $data data as passed by the shopping cart
 * @param mixed $key optional identifier for the data.
 * @return void 
*/
function it_exchange_add_session_data( $data, $key=false ) {
	$session = it_exchange_get_session();
	$session->add_data( $data, $key );
	do_action( 'it_exchange_add_session_data', $data, $key );
}

/**
 * Updates session data by key
 *
 * @since 0.3.7
 * @param mixed $key key for the data
 * @param mixed $data updated data
 * @return void
*/
function it_exchange_update_session_data( $key, $data ) {
	$session = it_exchange_get_session();
	$session->update_data( $key, $data );
	do_action( 'it_exchange_update_session_data', $key, $data );
}

/**
 * Removes data from the session if the passed key exists
 *
 * @since 0.3.7
 * @param mixed $key array key for the data to be removed
 * @return boolean
*/
function it_exchange_remove_session_data( $key ) {
	$session = it_exchange_get_session();
	$result = $session->remove_data( $key );
	if ( $result ) {
		do_action( 'it_exchange_get_session_data', $key );
		return true;
	}
	return false;
}

/**
 * Removes all data from the session
 *
 * @since 0.3.7
 * @return boolean
*/
function it_exchange_clear_session_data() {
	$session = it_exchange_get_session();
	$result = $session->clear_data();
	if ( $result ) {
		do_action( 'it_exchange_clear_session_data' );
		return true;
	}
	return false;
}

/**
 * Returns products array from current session
 *
 * @since 0.3.3
 * @return array  an array of products stored in $_SESSION['it_exchange']
*/
function it_exchange_get_session_products() {
	$session = it_exchange_get_session();
	return $session->get_products();
}

/**
 * Adds a product to the iThemes Exchange Session.
 *
 * This simply adds an item to the products array of the PHP Session.
 * Shopping cart plugins are responsible for managing the structure of the products
 * If a key is passed, it will be used as the key in the products array. Otherwise, the products array will just be
 * incremented. eg: ['products'][] = $product;
 *
 * @since 0.3.3
 * @param mixed $product product data as passed by the shopping cart
 * @param mixed $key optional identifier for the product.
 * @return void 
*/
function it_exchange_add_session_product( $product, $key=false ) {
	$session = it_exchange_get_session();
	$session->add_product( $product, $key );
	do_action( 'it_exchange_add_session_product', $product, $key );
}

/**
 * Updates a session product
 *
 * @since 0.3.7
 * @param mixed $session_product_key key for the product in the cart
 * @param mixed $product_data updated product data
 * @return void
*/
function it_exchange_update_session_product( $key, $product ) {
	$session = it_exchange_get_session();
	$session->update_product( $key, $product );
	do_action( 'it_exchange_update_session_product', $key, $product );
}

/**
 * Removes a product from the session if the passed key exists
 *
 * @since 0.3.3
 * @param mixed $key array key for the product to be removed
 * @return boolean
*/
function it_exchange_remove_session_product( $key ) {
	$session = it_exchange_get_session();
	$result = $session->remove_product( $key );
	if ( $result ) {
		do_action( 'it_exchange_remove_session_product', $key );
		return true;
	}
	return false;
}

/**
 * Removes all products from the session
 *
 * @since 0.3.3
 * @return boolean
*/
function it_exchange_clear_session_products() {
	$session = it_exchange_get_session();
	$result = $session->clear_products();
	if ( $result ) {
		do_action( 'it_exchange_clear_session_products' );
		return true;
	}
	return false;
}
