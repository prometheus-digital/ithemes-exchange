<?php
/**
 * Registers hooks
*/

/**
 * Returns an array of fields for customer registration
 *
 * @since 0.3.7
 * @return array
*/
function it_cart_buddy_default_customer_management_get_customer_profile_fields( $fields ) {
    $fields['first_name']  = array(
		'type'  => 'text_box',
		'var'   => 'cart_buddy_customer_first_name',
		'label' => __( 'First Name', 'LION' ),
	);
	$fields['last_name'] = array(
		'type'  => 'text_box',
		'var'   => 'cart_buddy_customer_last_name',
		'label' => __( 'Last Name', 'LION' ),
	);
	$fields['email'] = array(
		'type'  => 'text_box',
		'var'   => 'cart_buddy_customer_email',
		'label' => __( 'Email', 'LION' ),
	);
	return apply_filters( 'it_cart_buddy_default_customer_management_profile_fields', $fields );
}

/**
 * Returns an array of fields for customer Registration 
 *
 * @since 0.3.7
 * @return array
*/
function it_cart_buddy_default_customer_management_get_customer_registration_fields( $fields ) {
	$profile_fields = it_cart_buddy_get_customer_profile_fields();

    $fields['username']  = array(
		'type'  => 'text_box',
		'var'   => 'cart_buddy_customer_username',
		'label' => __( 'Username', 'LION' ),
	);
	$fields['password1'] = array(
		'type'  => 'password',
		'var'   => 'cart_buddy_customer_password1',
		'label' => __( 'Password', 'LION' ),
	);
	$fields['password2'] = array(
		'type'  => 'password',
		'var'   => 'cart_buddy_customer_password2',
		'label' => __( 'Re-type Password', 'LION' ),
	);

	$fields = array_merge( $profile_fields, $fields );
	return apply_filters( 'it_cart_buddy_default_customer_management_registration_fields', $fields );
}

/**
 * Returns a login form
 *
 * @since 0.3.7
 * @param string $existing_html value passed by WP filter API. Discarded here
 * @return string HTML for the form
*/
function it_cart_buddy_default_customer_management_get_customer_login_form( $existing_html='' ) {
	$args = array(
		'echo' => false,
		'form_id' => 'cart_buddy_login_form',
	);
	$form = wp_login_form( $args );
	return apply_filters( 'it_cart_buddy_default_customer_management_login_form', $form );
}
