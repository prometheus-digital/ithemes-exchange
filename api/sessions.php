<?php
/**
 * API functions pertaining to user sessions
 *
 * - IT_Cart_Buddy_Session object is stored in a global variable
 * - Sessions are only active on the frontend of the web site
 * - Cart Buddy inits the session and loads the data for you. Add-ons should not need to start the session
 *
 * @since 0.3.3
 * @package IT_Cart_Buddy
*/

/**
 * This grabs you a copy of the IT_Cart_Buddy_Session object
 *
 * @since 0.3.3
 * @return object  instance of IT_Cart_Buddy_Session
*/
function it_cart_buddy_session_get() {

	// No sessions in the admin
	if ( is_admin() )
		return;

	$session = empty( $GLOBALS['it_cart_buddy']['session'] ) ? false : $GLOBALS['it_cart_buddy']['session'];
	return $session;
}

/**
 * Returns session_data array from current session
 *
 * @since 0.3.3
 * @return array  an array of session_data stored in $_SESSION['it_cart_buddy']
*/
function it_cart_buddy_session_data_get() {
	$session = it_cart_buddy_session_get();
	return $session->get_data();
}

/**
 * Adds session data to the Cart Buddy Session.
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
function it_cart_buddy_session_data_add( $data, $key=false ) {
	$session = it_cart_buddy_session_get();
	$session->add_data( $data, $key );
	do_action( 'it_cart_buddy_session_data_add', $data, $key );
}

/**
 * Removes data from the session if the passed key exists
 *
 * @since 0.3.7
 * @param mixed $key array key for the data to be removed
 * @return boolean
*/
function it_cart_buddy_session_data_remove( $key ) {
	$session = it_cart_buddy_session_get();
	$result = $session->remove_data( $key );
	if ( $result ) {
		do_action( 'it_cart_buddy_session_data_remove', $key );
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
function it_cart_buddy_session_data_remove_all() {
	$session = it_cart_buddy_get_session();
	$result = $session->reset_data();
	if ( $result ) {
		do_action( 'it_cart_buddy_session_data_remove_all' );
		return true;
	}
	return false;
}

/**
 * Returns products array from current session
 *
 * @since 0.3.3
 * @return array  an array of products stored in $_SESSION['it_cart_buddy']
*/
function it_cart_buddy_session_products_get() {
	$session = it_cart_buddy_session_get();
	return $session->get_products();
}

/**
 * Adds a product to the Cart Buddy Session.
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
function it_cart_buddy_session_product_add( $product, $key=false ) {
	$session = it_cart_buddy_session_get();
	$session->add_product( $product, $key );
	do_action( 'it_cart_buddy_session_product_add', $product, $key );
}

/**
 * Removes a product from the session if the passed key exists
 *
 * @since 0.3.3
 * @param mixed $key array key for the product to be removed
 * @return boolean
*/
function it_cart_buddy_session_product_remove( $key ) {
	$session = it_cart_buddy_session_get();
	$result = $session->remove_product( $key );
	if ( $result ) {
		do_action( 'it_cart_buddy_session_product_remove', $key );
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
function it_cart_buddy_session_products_remove_all() {
	$session = it_cart_buddy_get_session();
	$result = $session->reset_products();
	if ( $result ) {
		do_action( 'it_cart_buddy_session_products_remove_all' );
		return true;
	}
	return false;
}
