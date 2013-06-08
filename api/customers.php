<?php
/**
 * API functions to deal with customer data and actions
 *
 * @since 0.3.7
 * @package IT_Exchange
*/

/**
 * Registers a customer
 *
 * @since 0.3.7
 * @param array $customer_data array of customer data to be processed by the customer management add-on when creating a customer
 * @param array $args optional array of arguments. not used by all add-ons
 * @return mixed
*/
function it_exchange_register_customer( $customer_data, $args=array() ) {
	return do_action( 'it_exchange_register_customer', $customer_data, $args );
}

/**
 * Get a customer
 *
 * Will return customer data formated by the active customer management add-on
 *
 * @since 0.3.7
 * @param integer $customer_id id for the customer
 * @return mixed customer data
*/
function it_exchange_get_customer( $customer_id ) {
    // Grab the WP User
	$customer = new IT_Exchange_Customer( $customer_id );
	return apply_filters( 'it_exchange_get_customer', $customer, $customer_id );
}

/**
 * Get the currently logged in customer or return false
 *
 * @since 0.3.7
 * @return mixed customer data
*/
function it_exchange_get_current_customer() {
	if ( ! is_user_logged_in() )
		return false;

	$customer = it_exchange_get_customer( get_current_user_id() );
	return apply_filters( 'it_exchange_get_current_customer', $customer );
}

/**
 * Get the currently logged in customer ID or return false
 *
 * @since 0.4.0
 * @return mixed customer data
*/
function it_exchange_get_current_customer_id() {
	if ( ! is_user_logged_in() )
		return false;

	return get_current_user_id();
}

/**
 * Update a customer's data
 *
 * @since 0.3.7
 * @param integer $customer_id id for the customer
 * @param mixed $customer_data data to be updated
 * @param array $args optional array of arguments. not used by all add-ons
 * @return mixed
*/
function it_exchange_update_customer( $customer_id, $customer_data, $args ) {
	return add_action( 'it_exchange_update_customer', $customer_id, $customer_data, $args );
}

/**
 * Returns all customer transactions
 *
 * @since 0.4.0
 *
 * @param integer ID customer id
 * @return array
*/
function it_exchange_get_customer_transactions( $customer_id ) {
	if ( ! $customer = it_exchange_get_customer( $customer_id ) )
		return array();

	// Get transactions args
	$args = array(
		'numberposts' => -1,
		'customer_id' => $customer->id,
	);
	return it_exchange_get_transactions( $args );
}

/**
 * Returns all customer transactions
 *
 * @since 0.4.0
 *
 * @param integer ID transaction id
 * @param integer ID customer id
 * @return array
*/
function it_exchange_customer_has_transaction( $transaction_id, $customer_id = NULL ) {

	if ( is_null( $customer_id ) ) {

		$customer = it_exchange_get_current_customer();

	} else {

		if ( ! $customer = it_exchange_get_customer( $customer_id ) )
			return array();

	}

	// Get transactions args
	$args = array(
		'numberposts' => -1, 
		'customer_id' => $customer->id,
	);
	return $customer->has_transaction( $transaction_id );
}

/**
 * Returns all customer products purchased across various transactions
 *
 * @since 0.4.0
 *
 * @param integer $customer_id the WP id of the customer
 * @return array
*/
function it_exchange_get_customer_products( $customer_id ) {
	// All products are attached to a transaction
	$transactions = it_exchange_get_customer_transactions( $customer_id );

	// Loop through transactions and build array of products
	$products = array();
	foreach( $transactions as $transaction ) {

		// strip array values from each product to prevent ovewriting multiple purchases of same product
		$transaction_products = (array) array_values( it_exchange_get_transaction_products( $transaction ) );

		// Add transaction ID to each products array
		foreach( $transaction_products as $key => $data ) {
			$transaction_products[$key]['transaction_id'] = $transaction->ID;
		}

		// Merge with previously queried
		$products = array_merge( $products, $transaction_products );
	}

	// Return
	return $products;
}

/**
 * Handles $_REQUESTs and submits them to the profile for processing
 *
 * @since 0.4.0
 * @return void
*/
function handle_it_exchange_save_profile_action() {

	// Grab action and process it.
	if ( isset( $_REQUEST['it-exchange-save-profile'] ) ) {

		//WordPress builtin
		require_once(ABSPATH . 'wp-admin/includes/user.php');
		$customer = it_exchange_get_current_customer();
		$result = edit_user( $customer->id );
		
		if ( is_wp_error( $result ) ) {
			it_exchange_add_message( 'error', $result->get_error_message() );
		} else {
			it_exchange_add_message( 'notice', __( 'Successfully saved profile!', 'LION' ) );
		}
		
	}
	
}
add_action( 'template_redirect', 'handle_it_exchange_save_profile_action', 5 );

/**
 * Register's an exchange user
 *
 * @since 0.4.0
 * @param array $user_data optional. Overwrites POST data
 * @return mixed WP_Error or WP_User object
*/
function it_exchange_register_user( $user_data=array() ) {

	// Include WP file
	require_once( ABSPATH . 'wp-admin/includes/user.php' );

	// If any data was passed in through param, inject into POST variable
	foreach( $user_data as $key => $value ) {
		$_POST[$key] = $value;
	}

	// Register user via WP function
	return edit_user();
}

/**
 * Handles $_REQUESTs and submits them to the registration for processing
 *
 * @todo Move to to lib/customers
 * @since 0.4.0
 * @return void
*/
function handle_it_exchange_customer_registration_action() {

	// Grab action and process it.
	if ( isset( $_REQUEST['it-exchange-register-customer'] ) ) {

		$result = it_exchange_register_user();

		if ( is_wp_error( $result ) )
			return it_exchange_add_message( 'error', $result->get_error_message());

		$user_id = $result;

		//else

		$creds = array(
			'user_login'    => $_REQUEST['user_login'],
			'user_password' => $_REQUEST['pass1'],
		);

		$result = wp_signon( $creds );

		if ( is_wp_error( $result ) )
			return it_exchange_add_message( 'error', $result->get_error_message() );

		wp_new_user_notification( $user_id, $_REQUEST['pass1'] );

		$reg_page = it_exchange_get_page_url( 'registration' );
		// Set redirect to profile page if they were on the registration page
		$redirect = ( trailingslashit( $reg_page ) == trailingslashit( wp_get_referer() ) ) ? it_exchange_get_page_url( 'profile' ) : clean_it_exchange_query_args( array(), array( 'ite-sw-state' ) );
		wp_redirect( $redirect );
		die();

	}

}
add_action( 'template_redirect', 'handle_it_exchange_customer_registration_action', 5 );
