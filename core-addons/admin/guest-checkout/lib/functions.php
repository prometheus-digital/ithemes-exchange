<?php
/**
 * Resets the session activity to the current timestamp
 *
 * @since 1.6.0
 *
 * @return void
*/
function it_exchange_guest_checkout_bump_session() {

	if ( ! it_exchange_is_guest_checkout_enabled() ) {
		return;
	}

	$now            = time();
	$customer_email = it_exchange_get_cart_data( 'guest-checkout-user' );
	$customer_email = is_array( $customer_email ) ? reset( $customer_email ) : $customer_email;

	if ( ! $customer_email ) {
		return;
	}

	it_exchange_update_cart_data( 'guest-checkout', $now );
	it_exchange_get_current_cart()->set_guest( new IT_Exchange_Guest_Customer( $customer_email ) );

	if (
		it_exchange_is_page( 'checkout' ) || it_exchange_is_page( 'transaction' ) || it_exchange_is_page( 'confirmation' ) ||
		! empty( $_GET['ite-checkout-refresh'] ) || it_exchange_in_superwidget()
	) {
		$GLOBALS['current_user'] = it_exchange_guest_checkout_generate_guest_user_object( $customer_email );
	}
}

/**
 * Generates a fake WP_User for Guest Chekcout
 *
 * @since 1.6.0
 *
 * @param string $email
 * @param bool   $return_exchange_customer
 *
 * @return IT_Exchange_Customer|object
*/
function it_exchange_guest_checkout_generate_guest_user_object( $email, $return_exchange_customer=false ) {

	if ( $return_exchange_customer ) {
		return new IT_Exchange_Guest_Customer( $email );
	}

	$user     = new WP_User();
	$user->ID = $email;

	$data               = new stdClass();
	$data->ID           = $email;
	$data->user_login   = false;
	$data->user_pass    = false;
	$data->user_email   = $email;
	$data->display_name = $email;
	$data->email        = $email;
	$data->is_guest     = true;
	$user->data         = $data;

	return $user;
}

/**
 * Kills a guest checkout session by removing vars from the session global
 *
 * @since 1.6.0
 *
 * @return void
*/
function it_exchange_kill_guest_checkout_session() {
	it_exchange_remove_cart_data( 'guest-checkout' );
	it_exchange_remove_cart_data( 'guest-checkout-user' );
	it_exchange_remove_cart_data( 'guest-billing-address' );
	it_exchange_remove_cart_data( 'guest-shipping-address' );
	do_action( 'it_exchange_kill_guest_checkout_session' );
}

/**
 * Init a guest session
 *
 * @since 1.6.0
 *
 * @param string $customer_email the customer's email
 * @return boolean
*/
function it_exchange_init_guest_checkout_session( $customer_email ) {
	if ( empty( $customer_email ) || ! is_email( $customer_email ) )
		return false;

	// Set the user ID in the cart session
	it_exchange_update_cart_data( 'guest-checkout-user', $customer_email );

	// Bump the timeout var
	it_exchange_guest_checkout_bump_session();

	it_exchange_log( 'Guest checkout session initialized for {email}', ITE_Log_Levels::DEBUG, array(
		'email'  => $customer_email,
		'_group' => 'session',
	) );

	do_action( 'it_exchange_init_guest_checkout', $customer_email );
}

/**
 * Set the cookie flagging the current guest email.
 *
 * This is used so that the customer can view the confirmation page again if they refresh.
 *
 * @since 2.0.0
 *
 * @param string $email
 */
function it_exchange_set_guest_email_cookie( $email ) {
	@setcookie( 'it-exchange-guest-email', $email, time() + HOUR_IN_SECONDS, COOKIEPATH, COOKIE_DOMAIN, '', true );
}