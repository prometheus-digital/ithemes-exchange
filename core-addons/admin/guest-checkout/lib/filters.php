<?php
/**
 * Enqueues Guest Checkout SW JS
 *
 * @since 1.6.0
 *
 * @return void
*/
function it_exchange_guest_checkout_enqueue_sw_js() {
	$file = ITUtility::get_url_from_file( dirname( __FILE__ ) . '/assets/js/super-widget.js' );
	wp_enqueue_script( 'it-exchange-guest-checkout-sw', $file, array( 'it-exchange-super-widget' ), false, true );
}
add_action( 'it_exchange_enqueue_super_widget_scripts', 'it_exchange_guest_checkout_enqueue_sw_js' );

/**
 * Enqueues the checkout page scripts
 *
 * @since 1.6.0
 *
 * @return void
*/
function it_exchange_guest_checkout_enqueue_checkout_scripts() {
	if ( ! it_exchange_is_page( 'checkout' ) )
		return;

	$file = ITUtility::get_url_from_file( dirname( __FILE__ ) . '/assets/js/checkout.js' );
	wp_enqueue_script( 'it-exchange-guest-checkout-checkout-page', $file, array( 'jquery' ), false, true );
}
add_action( 'wp_enqueue_scripts', 'it_exchange_guest_checkout_enqueue_checkout_scripts' );

/**
 * Init Guest Checkout Registration/Login via email
 *
 * @since 1.6.0
 *
 * @return void
*/
function it_exchange_guest_checkout_init_login() {
	if ( empty( $_POST['it-exchange-init-guest-checkout'] ) )
		return;

	// Vaidate email address
	if ( ! is_email( $_POST['email'] ) ) {
		it_exchange_add_message( 'error', __( 'Please use a properly formatted email address.', 'it-l10n-ithemes-exchange' ) );
		return;
	}

	$customer_email = $_POST['email'];

	it_exchange_init_guest_checkout_session( $customer_email );
}
add_action( 'template_redirect', 'it_exchange_guest_checkout_init_login' );

/**
 * Return true on has_transaction (for confirmation screen) if conditionals match
 *
 * Conditionals:
 * - We're doing a guest checkout
 * - Transaction was a guest checkout transaction
 * - Current guest has same email as one used in the transaction
 *
 * @since 1.6.0
 *
 * @param boolean $has_transaction the value coming in from the WP filter
 * @param integer $transaction_id  the transaction ID
 * @param mixed   $user_id         normally the WP user ID but could be something different if changed by an add-on
 *
 * @return bool
*/
function it_exchange_guest_checkout_guest_has_transaction( $has_transaction, $transaction_id, $user_id ) {

	_deprecated_function( __FUNCTION__, '2.0.0' );

	return $has_transaction;
}

/**
 * Continues the guest checkout session or ends it based on timeout
 *
 * @since 1.6.0
 *
 * @return void
*/
function it_exchange_handle_guest_checkout_session() {

	// Abandon if also initing. We have another function hooked to template_redirect for that.
	if ( ! empty( $_POST['it-exchange-init-guest-checkout'] ) )
		return;

	$guest_session = it_exchange_get_cart_data( 'guest-checkout' );
	$guest_session = empty( $guest_session ) ? false : reset( $guest_session );

	// IF we don't have a guest session, return
	if ( ! $guest_session ) {
		return;
	}

	it_exchange_guest_checkout_bump_session();
}
add_action( 'template_redirect', 'it_exchange_handle_guest_checkout_session', 9 );
add_action( 'it_exchange_super_widget_ajax_top', 'it_exchange_handle_guest_checkout_session', 9 );

/**
 * Save the billing address to the guest checkout session for BC.
 *
 * @since 2.0.0
 *
 * @param \ITE_Cart $cart
 */
function it_exchange_guest_checkout_deprecated_billing_address_shipping( ITE_Cart $cart ) {

	if ( $cart->is_current() && it_exchange_doing_guest_checkout() ) {
		it_exchange_update_cart_data( 'guest-billing-address', $cart->get_billing_address()->to_array() );
	}
}

add_action( 'it_exchange_set_cart_billing_address', 'ite_save_main_billing_address_on_current_update' );

/**
 * Save the shipping address to the guest checkout session for BC.
 *
 * @since 2.0.0
 *
 * @param \ITE_Cart $cart
 */
function it_exchange_guest_checkout_deprecated_shipping_address_shipping( ITE_Cart $cart ) {

	if ( $cart->is_current() && it_exchange_doing_guest_checkout() ) {
		it_exchange_update_cart_data( 'guest-shipping-address', $cart->get_shipping_address()->to_array() );
	}
}

add_action( 'it_exchange_set_cart_shipping_address', 'ite_save_main_shipping_address_on_current_update' );

/**
 * Returns the customer email for a guest transaction
 *
 * @since 1.6.0
 *
 * @param string $email the email passed through from the WP filter
 * @param mixed  $transaction the id or the object
 *
 * @return string
*/
function it_exchange_get_guest_checkout_transaction_email( $email, $transaction ) {
	_deprecated_function( __FUNCTION__, '2.0.0' );

	return $email;
}

/**
 * Returns the customer id for a guest transaction
 *
 * @since 1.6.0
 *
 * @param string $id          the id passed through from the WP filter
 * @param mixed  $transaction the id or the object
 *
 * @return int|string
*/
function it_exchange_get_guest_checkout_transaction_id( $id, $transaction ) {
	$transaction = it_exchange_get_transaction( $transaction );

	if ( ! $transaction->is_guest_purchase() ) {
		return $id;
	}

	return $transaction->customer_email;
}
add_filter( 'it_exchange_get_transaction_customer_id', 'it_exchange_get_guest_checkout_transaction_id', 10, 2 );

/**
 * Do not print link to customer details on payment transactions admin page
 *
 * @since 1.6.0
 *
 * @param boolean $display_link yes or no
 * @param WP_Post $wp_post      the wp post_type for the transaction
 *
 * @return boolean
*/
function it_exchange_hide_admin_customer_details_link_on_transaction_details_page( $display_link, $wp_post ) {

	if ( ! $transaction = it_exchange_get_transaction( $wp_post->ID ) ) {
		return $display_link;
	}

	if ( ! $transaction->is_guest_purchase() ) {
		return $display_link;
	}

	return false;
}
add_filter( 'it_exchange_transaction_detail_has_customer_profile', 'it_exchange_hide_admin_customer_details_link_on_transaction_details_page', 10, 2 );

/**
 * Flags the user as someone who registered as a guest
 *
 * @since 1.6.0
 *
 * @param  object $data        custoemr data
 * @param  int    $customer_id the wp customer_id
 * @return object
*/
function it_exchange_guest_checkout_set_customer_data( $data, $customer_id ) {

	_deprecated_function( __FUNCTION__, '2.0.0' );

	return $data;
}

/**
 * Flag transaction object as guest checkout
 *
 * @since 1.6.0
 *
 * @param object        $transaction_object The transaction object right before being added to database
 * @param ITE_Cart|null $cart
 *
 * @return object
*/
function it_exchange_flag_transaction_as_guest_checkout( $transaction_object, ITE_Cart $cart = null ) {

	if ( ! $cart || ! $cart->is_guest() ) {
		return $transaction_object;
	}

	$transaction_object->is_guest_checkout = true;

	return $transaction_object;
}
add_filter( 'it_exchange_generate_transaction_object', 'it_exchange_flag_transaction_as_guest_checkout', 10, 2 );

/**
 * Adds post meta to flag as guest checkout after its inserted into the DB
 *
 * So that we can filter it out of queries
 *
 * @since 1.6.0
 *
 * @param int           $transaction_id
 * @param ITE_Cart|null $cart
 *
 * @return void
*/
function it_exchange_flag_transaction_post_as_guest_checkout( $transaction_id, ITE_Cart $cart = null ) {
	$transaction = it_exchange_get_transaction( $transaction_id );
	error_log($cart && $cart->is_guest());

	if ( $cart && $cart->is_guest() ) {
		update_post_meta( $transaction_id, '_it-exchange-is-guest-checkout', true );

		if ( $cart && $cart->is_current() ) {
			it_exchange_set_guest_email_cookie( $transaction->get_customer_email() );
		}
	}
}
add_action( 'it_exchange_add_transaction_success', 'it_exchange_flag_transaction_post_as_guest_checkout', 0, 2 );

/**
 * Removes guest checkout transactions from User Purchases
 *
 * If a registerd user checkouts as a guest rather than logging in, the transaction
 * is still attached to them but we don't want to show it to them in their front end profile.
 *
 * @since 1.6.0
 *
 * @param array $args wp post args used for the post query
 *
 * @return array
*/
function it_exchange_guest_checkout_filter_frontend_purchases( $args ) {

	_deprecated_function( __FUNCTION__, '2.0.0' );

	return $args;
}

/**
 * Modifies the Transaction Customer data when dealing with a guest checkout
 *
 * @since 1.6.0
 *
 * @param IT_Exchange_Customer    $customer the customer object
 * @param IT_Exchange_Transaction $transaction
 *
 * @return IT_Exchange_Customer
*/
function it_exchange_guest_checkout_modify_transaction_customer( $customer, $transaction ) {

	_deprecated_function( __FUNCTION__, '2.0.0' );

	if ( ! $transaction->is_guest_purchase() ) {
		return $customer;
	}

	if ( ! $transaction->customer_email ) {
		return $customer;
	}

	$customer = it_exchange_guest_checkout_generate_guest_user_object( $transaction->customer_email, true );

	$customer->wp_user = new stdClass();
	$customer->wp_user->display_name = sprintf( __( 'Guest (%s)', 'it-l10n-ithemes-exchange' ), $customer->ID );

	return $customer;
}

/**
 * Modifies the Customer data when dealing with a guest checkout
 *
 * This modifies the feedback on the Checkout Page in the Logged-In purchse requirement
 *
 * @since 1.6.0
 *
 * @param IT_Exchange_Customer $customer the customer object
 *
 * @return IT_Exchange_Customer
*/
function it_exchange_guest_checkout_modify_customer( $customer ) {

	if ( ! it_exchange_doing_guest_checkout() ) {
		return $customer;
	}

	if ( ( is_admin() && ! defined( 'DOING_AJAX' ) ) || ( is_admin() && defined( 'DOING_AJAX' ) && ! DOING_AJAX ) ) {
		return $customer;
	}

	$email = it_exchange_get_cart_data( 'guest-checkout-user' );
	$email = is_array( $email ) ? reset( $email ) : $email;

	if ( ! $email ) {
		return $customer;
	}

	$customer = it_exchange_guest_checkout_generate_guest_user_object( $email, true );

	return $customer;
}

add_filter( 'it_exchange_get_current_customer', 'it_exchange_guest_checkout_modify_customer' );

/**
 * This modifies the loginout link generated by WP when we're doing Guest Checkout
 *
 * @since 1.6.0
 *
 * @param string $url      the html for the loginout link
 * @param string $redirect the URL we're redirecting to after logged out.
 * @return string
*/
function it_exchange_guest_checkout_modify_loginout_link( $url, $redirect ) {

	if ( ! it_exchange_doing_guest_checkout() )
		return $url;

	$url = add_query_arg( array( 'it-exchange-guest-logout' => 1 ), esc_url( $redirect ) );

	return $url;
}
add_filter( 'logout_url', 'it_exchange_guest_checkout_modify_loginout_link', 10, 2 );

/**
 * Logs out a guest checkout session
 *
 * @since 1.6.0
 *
 * @return void
*/
function it_exchange_logout_guest_checkout_session() {
	if ( ( it_exchange_is_page( 'logout' ) && it_exchange_doing_guest_checkout() ) || ! empty( $_REQUEST['it-exchange-guest-logout'] ) ) {
		it_exchange_kill_guest_checkout_session();
		wp_redirect( esc_url_raw( remove_query_arg( 'it-exchange-guest-logout' ) ) );
	}
}
add_action( 'template_redirect', 'it_exchange_logout_guest_checkout_session', 1 );

/**
 * Logout Guest user after hitting confirmation page.
 *
 * @since 1.10.6
 *
 * @return void
*/
function it_exchange_logout_guest_checkout_session_on_confirmation_page() {
	if ( it_exchange_is_page( 'confirmation' ) && it_exchange_doing_guest_checkout() ) {
		it_exchange_kill_guest_checkout_session();
	}
}
add_action( 'wp_footer', 'it_exchange_logout_guest_checkout_session_on_confirmation_page' );

/**
 * Allow downloads to be served regardless of the requirement to be logged in if user checkout out as a guest
 *
 * @since 1.6.0
 *
 * @param boolean  $setting the default setting
 * @param array    $hash_data the download has data
 *
 * @return boolean
*/
function it_exchange_allow_file_downloads_for_guest_checkout( $setting, $hash_data ) {
	if ( ! $transaction = it_exchange_get_transaction( $hash_data['transaction_id'] ) )
		return $setting;

	return $transaction->is_guest_purchase() ? false : $setting;
}
add_filter( 'it_exchange_require_user_login_for_download', 'it_exchange_allow_file_downloads_for_guest_checkout', 10, 2 );

/**
 * Clear guest session when an authentication attemp happens.
 *
 * @since 1.6.0
 *
 * @param  mixed $incoming Whatever is coming from WP hook API. We don't use it.
 *
 * @return WP_User|null
*/
function it_exchange_end_guest_checkout_on_login_attempt( $incoming ) {
	if ( it_exchange_doing_guest_checkout() ) {
		it_exchange_kill_guest_checkout_session();
	}

	return $incoming;
}
add_filter( 'authenticate', 'it_exchange_end_guest_checkout_on_login_attempt' );

/**
 * Proccesses Guest login via superwidget
 *
 * @since 1.6.0
 *
*/
function it_exchange_guest_checkout_process_ajax_login() {

	if ( empty( $_REQUEST['sw-action'] ) || 'guest-checkout' != $_REQUEST['sw-action'] || empty( $_POST['email'] ) ) {
		it_exchange_add_message( 'error', __( 'Please use a properly formatted email address.', 'it-l10n-ithemes-exchange' ) );
		die('0');
	}

	// Vaidate email address
	if ( ! is_email( $_POST['email'] ) ) {
		it_exchange_add_message( 'error', __( 'Please use a properly formatted email address.', 'it-l10n-ithemes-exchange' ) );
		die('0');
	}

	$customer_email = $_POST['email'];

	it_exchange_init_guest_checkout_session( $customer_email );
	die('1');
}
add_action( 'it_exchange_processing_super_widget_ajax_guest-checkout', 'it_exchange_guest_checkout_process_ajax_login' );

/**
 * Remove the download page link in the email if this was a guest checkout transaction
 *
 * @since 1.6.0
 *
 * @param  boolean  $boolean incoming from WP Filter
 * @param  int      $id      the transaction ID
 * @return boolean
*/
function it_exchange_guest_checkout_maybe_remove_download_page_link_from_email( $boolean, $id ) {

	if ( ! $transaction = it_exchange_get_transaction( $id ) ) {
		return $boolean;
	}

	if ( ! $transaction->is_guest_purchase() ) {
		return $boolean;
	}

	$settings = it_exchange_get_option( 'addon_digital_downloads', true );

	if ( empty( $settings['require-user-login'] ) ) {
		return $boolean;
	}

	return false;
}
add_filter( 'it_exchange_print_downlods_page_link_in_email', 'it_exchange_guest_checkout_maybe_remove_download_page_link_from_email', 10, 2 );

/**
 * Filter email for sending if its false and we're transaction was a guest checkout
 *
 * @since 1.7.12 
 *
 * @param string $to_email the email address we're sending it to
 * @param object $transaction the transaction object
 * @return string
*/
function it_exchange_guest_checkout_modify_confirmation_email_address( $to_email, $transaction ) {
	_deprecated_function( __FUNCTION__, '2.0.0' );

	return $to_email;
}
