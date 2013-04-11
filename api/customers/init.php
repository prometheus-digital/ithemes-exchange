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

	// Get current users's ID
	$customer_id = get_current_user_id();
	$customer = it_exchange_get_customer( $customer_id );
	return apply_filters( 'it_exchange_get_current_customer', $customer );
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
 * Returns an array of form fields for customer registration 
 *
 * Add-ons hooking onto this need to return an array with the following schema so that 
 * functions relying on this data may process it correctly:
 *
 * Add-ons calling this function should use ITForm to generate the form fields.
 *
 * $fields['password'] = array(
 *    'var'   => 'password',
 *    'type'  => 'password',
 *    'label' => 'Password:',
 * );
 *
 *
 * @since 0.3.7
 * @return array
*/
function it_exchange_get_customer_registration_fields() {
	$profile_fields = it_exchange_get_customer_profile_fields();

	$fields['username']  = array(
		'type'  => 'text_box',
		'var'   => 'user_login',
		'label' => __( 'Username', 'LION' ),
	);
	$fields['password1'] = array(
		'type'  => 'password',
		'var'   => 'password1',
		'label' => __( 'Password', 'LION' ),
	);
	$fields['password2'] = array(
		'type'  => 'password',
		'var'   => 'password2',
		'label' => __( 'Re-type Password', 'LION' ),
	);

	$fields = array_merge( $profile_fields, $fields );
	return apply_filters( 'it_exchange_get_customer_registration_fields', $fields );
}

/**
 * Returns an array of form fields for customer profile
 *
 * Add-ons hooking onto this need to return an array with the following schema so that 
 * functions relying on this data may process it correctly:
 *
 * Add-ons calling this function should use ITForm to generate the form fields.
 *
 * $fields['first_name'] = array(
 *    'var'   => 'first_name',
 *    'type'  => 'text_box',
 *    'label' => 'First Name:',
 * );
 *
 * @since 0.3.7
 * @return array 
*/
function it_exchange_get_customer_profile_fields() {
	$fields['first_name']  = array(
		'type'  => 'text_box',
		'var'   => 'first_name',
		'label' => __( 'First Name', 'LION' ),
	);  
	$fields['last_name'] = array(
		'type'  => 'text_box',
		'var'   => 'last_name',
		'label' => __( 'Last Name', 'LION' ),
	);  
	$fields['email'] = array(
		'type'  => 'text_box',
		'var'   => 'user_email',
		'label' => __( 'Email', 'LION' ),
	);  
	return apply_filters( 'it_exchange_get_customer_profile_fields', $fields );
}

/**
 * Returns the customer login form
 *
 * @since 0.3.7
 * @return string HTML
*/
function it_exchange_get_customer_login_form() {
	$args = array(
		'echo' => false,
		'form_id' => 'exchange_login_form',
	);
	$form = wp_login_form( $args );
	return apply_filters( 'it_exchange_get_customer_login_form', $form );
}
