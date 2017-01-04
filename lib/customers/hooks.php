<?php
/**
 * Customer Hooks.
 *
 * @since 2.0.0
 * @license GPLv2
 */

/**
 * Handles $_REQUESTs and submits them to the registration for processing
 *
 * @since 0.4.0
 * @return void
 */
function handle_it_exchange_customer_registration_action() {

	// Grab action and process it.
	if ( isset( $_POST['it-exchange-register-customer'] ) ) {
		global $wp;

		do_action( 'before_handle_it_exchange_customer_registration_action' );

		$user_id = it_exchange_register_user();

		if ( is_wp_error( $user_id ) ) {
			it_exchange_add_message( 'error', $user_id->get_error_message() );

			return;
		}

		$creds = array(
			'user_login'    => $_POST['user_login'],
			'user_password' => $_POST['pass1'],
		);

		$user = wp_signon( $creds );

		if ( is_wp_error( $user ) ) {
			it_exchange_add_message( 'error', $user->get_error_message() );

			return;
		}

		$registration_url = trailingslashit( it_exchange_get_page_url( 'registration' ) );
		$checkout_url     = trailingslashit( it_exchange_get_page_url( 'checkout' ) );
		$current_home_url = trailingslashit( home_url( $wp->request ) );
		$current_site_url = trailingslashit( site_url( $wp->request ) );
		$referrer         = trailingslashit( wp_get_referer() );

		// Redirect or clear query args
		$redirect_hook_slug = false;

		if ( in_array( $referrer, array( $registration_url, $checkout_url ) )
		     || in_array( $current_home_url, array( $registration_url, $checkout_url ) )
		     || in_array( $current_site_url, array( $registration_url, $checkout_url ) )
		) {
			// If on the reg page, check for redirect cookie.
			$login_redirect = it_exchange_get_session_data( 'login_redirect' );
			if ( ! empty( $login_redirect ) ) {
				$redirect           = reset( $login_redirect );
				$redirect_hook_slug = 'registration-to-variable-return-url';
				it_exchange_clear_session_data( 'login_redirect' );
			} else {
				if ( it_exchange_is_page( 'registration' ) ) {
					$redirect           = it_exchange_get_page_url( 'profile' );
					$redirect_hook_slug = 'registration-success-from-registration';
				}
				if ( it_exchange_is_page( 'checkout' ) ) {
					$redirect           = it_exchange_get_page_url( 'checkout' );
					$redirect_hook_slug = 'registration-success-from-checkout';
				}
			}
		} else {
			// Then were in the superwidget
			$redirect = it_exchange_clean_query_args( array(), array( 'ite-sw-state' ) );
		}

		do_action( 'handle_it_exchange_customer_registration_action' );
		do_action( 'after_handle_it_exchange_customer_registration_action' );

		it_exchange_redirect( $redirect, $redirect_hook_slug );
		die();
	}
}

add_action( 'template_redirect', 'handle_it_exchange_customer_registration_action', 5 );

/**
 * Provide back-compat for the _it_exchange_transaction_id customer user meta.
 *
 * @since 2.0.0
 *
 * @param null|mixed $value
 * @param int        $user_id
 * @param string     $meta_key
 * @param bool       $single
 *
 * @return array
 */
function it_exchange_bc_customer_transactions( $value, $user_id, $meta_key, $single ) {

	if ( $meta_key !== '_it_exchange_transaction_id' ) {
		return $value;
	}

	$transaction_ids = IT_Exchange_Transaction::query()
		->select_single( 'ID' )
		->where( 'customer_id', '=', $user_id )
		->results()
		->toArray();

	return $transaction_ids;
}

add_filter( 'get_user_metadata', 'it_exchange_bc_customer_transactions', 10, 4 );