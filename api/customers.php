<?php
/**
 * API functions to deal with customer data and actions
 *
 * @since 0.3.7
 * @package IT_Cart_Buddy
*/

/**
 * Registers a customer
 *
 * @since 0.3.7
 * @param array $customer_data array of customer data to be processed by the customer management add-on when creating a customer
 * @param array $args optional array of arguments. not used by all add-ons
 * @return mixed
*/
function it_cart_buddy_register_customer( $customer_data, $args=array() ) {
	return do_action( 'it_cart_buddy_register_customer', $customer_data, $args );
}

/**
 * Get a customer
 *
 * Will return customer data formated by the active customer management add-on
 *
 * @since 0.3.7
 * @param integer $customer_id id for the customer
 * @param array $args optional array of arguments. not used by all add-ons
 * @return mixed customer data
*/
function it_cart_buddy_get_customer( $customer_id, $args=array() ) {
	return apply_filters( 'it_cart_buddy_get_customer', false, $customer_id, $args );
}

/**
 * Get the currently logged in customer or return false
 *
 * Will return customer data formated by the active customer management add-on
 *
 * @since 0.3.7
 * @param array $args optional array of arguments. not used by all add-ons
 * @return mixed customer data
*/
function it_cart_buddy_get_current_customer( $args=array() ) {
	return apply_filters( 'it_cart_buddy_get_current_customer', false, $args );
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
function it_cart_buddy_update_customer( $customer_id, $customer_data, $args ) {
	return add_action( 'it_cart_buddy_update_customer', $customer_id, $customer_data, $args );
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
 * @param array $args optional array of arguments. not used by all add-ons
 * @return mixed return value is determined by the active customer management add-on
*/
function it_cart_buddy_get_customer_registration_fields( $args=array() ) {
	return apply_filters( 'it_cart_buddy_get_customer_registration_fields', array(), $args );
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
 * @param array $args optional array of arguments. not used by all add-ons
 * @return mixed return value is determined by the active customer management add-on
*/
function it_cart_buddy_get_customer_profile_fields( $args=array() ) {
	return apply_filters( 'it_cart_buddy_get_customer_profile_fields', array(), $args );
}

/**
 * Returns the customer login form
 *
 * @since 0.3.7
 * @param array $args optional array of arguments. not used by all add-ons
 * @return string HTML
*/
function it_cart_buddy_get_customer_login_form( $args=array() ) {
	return apply_filters( 'it_cart_buddy_get_customer_login_form', '', $args );
}
