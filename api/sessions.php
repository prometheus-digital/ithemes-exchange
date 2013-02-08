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
function it_cart_buddy_get_session() {

	// No sessions in the admin
	if ( is_admin() )
		return;

	$session = empty( $GLOBALS['it_cart_buddy']['session'] ) ? false : $GLOBALS['it_cart_buddy']['session'];
	return $session;
}

/**
 * Returns products array from current session
 *
 * @since 0.3.3
 * @return array  an array of products stored in $_SESSION['it_cart_buddy']
*/
function it_cart_buddy_get_session_products() {
	$session = it_cart_buddy_get_session();
	return $session->get_products();
}

/**
 * Returns session_data array from current session
 *
 * @since 0.3.3
 * @return array  an array of session_data stored in $_SESSION['it_cart_buddy']
*/
function it_cart_buddy_get_session_data() {
	$session = it_cart_buddy_get_session();
	return $session->get_data();
}

/**
 * Adds a product to the Cart Buddy Session.
 *
 * This will be added to the PHP Session as well as the global object used by cart add-ons
 *
 * @since 0.3.3
 * @param array $product product
 * @return array an array of all the products for the session
*/
function it_cart_buddy_add_product_to_session( $product ) {
	$session = it_cart_buddy_get_session();
	return $session->add_product( $product );
}

/**
 * Removes a product from the session if it exists
 *
 * @since 0.3.3
 * @param mixed product_key array key for the product to be removed
 * @return array an array of all the products for the session
*/
function it_cart_buddy_remove_product_from_session( $product_key ) {
	$session = it_cart_buddy_get_session();
	return $session->remove_product( $product_key );
}

/**
 * Removes all products from the session
 *
 * @since 0.3.3
 * @return array an array of all the products for the session
*/
function it_cart_buddy_remove_all_products_from_session() {
	$session = it_cart_buddy_get_session();
	return $session->reset_products();
}
