<?php
/**
 * Functions related to managing and retrieving customer data
 *
 * The default customer management add-on uses the WP users table and usermeta
 *
 * @since 0.3.7
 * @package IT_Cart_Buddy
*/

/**
 * Returns a WP User object with additional Cart Buddy properties
 *
 * @since 0.3.7
 * @param interger $customer_id
*/
function it_cart_buddy_default_customer_management_get_customer( $existing_data, $customer_id ) {
	// Grab the WP User
	$wp_user        = new WP_User( $customer_id );

	// Return if error
	if ( is_wp_error( $wp_user ) )
		return false;

	// Dup id to lowercase just to be nice
	$customer['id'] = $customer['ID'] = $customer_id;

	// Init customer array with data object from WP_User object
	$wp_data  = get_object_vars( $wp_user->data );
	$customer = array_merge( $customer, $wp_data );

	// Default Memeber Managment fields
	$customer['first_name'] = get_user_meta( $customer_id, 'first_name', true );
	$customer['last_name']  = get_user_meta( $customer_id, 'last_name', true );

	// Add-ons shouldn't hook into this before we do... but just in case.
	$customer = ITUtility::merge_defaults( $existing_data, $customer );

	return $customer;
}

/**
 * Grabs the current customer if user is logged in
 *
 * @since 0.3.7
 * @return object
*/
function it_cart_buddy_default_customer_management_get_current_customer( $existing_data ) {
	if ( ! is_user_logged_in() )
		return false;

	// Get current users's ID
	$customer_id = get_current_user_id();
	return it_cart_buddy_get_customer( $customer_id );
}
