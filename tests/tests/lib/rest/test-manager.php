<?php
/**
 * Test the REST Manager class.
 *
 * @since   2.0.0
 * @license GPLv2
 */

/**
 * Class Test_IT_Exchange_REST_Manager
 *
 * @group rest-api
 */
class Test_IT_Exchange_REST_Manager extends Test_IT_Exchange_REST_Route {

	public function test_authentication_bails_if_already_authed() {
		$this->assertTrue( $this->manager->authenticate( true ) );
	}

	public function test_authenticate_bails_if_not_our_endpoint() {

		unset( $_SERVER['HTTP_AUTHORIZATION'] );
		$_SERVER['REQUEST_URI'] = trailingslashit( rest_get_url_prefix() ) . 'wp/v2/posts';

		// Random string just to check for pass through
		$this->assertEquals( 'bob', $this->manager->authenticate( 'bob' ) );
	}

	public function test_authenticate_no_op_when_authorization_header_not_set() {

		unset( $_SERVER['HTTP_AUTHORIZATION'] );
		$_SERVER['REQUEST_URI'] = trailingslashit( rest_get_url_prefix() ) . 'it_exchange/v1/carts';

		$this->assertEquals( 'bob', $this->manager->authenticate( 'bob' ) );
	}

	public function test_authentication_no_op_if_different_authorization_scheme() {

		$_SERVER['HTTP_AUTHORIZATION'] = 'Basic ' . base64_encode( 'username:password' );
		$_SERVER['REQUEST_URI']        = trailingslashit( rest_get_url_prefix() ) . 'it_exchange/v1/carts';

		$this->assertEquals( 'bob', $this->manager->authenticate( 'bob' ) );
	}

	public function test_authentication_error_if_guest_checkout_disabled() {

		$_SERVER['HTTP_AUTHORIZATION'] = 'ITHEMES-EXCHANGE-GUEST email="guest@example.org"';
		$_SERVER['REQUEST_URI']        = trailingslashit( rest_get_url_prefix() ) . 'it_exchange/v1/carts';

		add_filter( 'it_exchange_guest_checkout_enabled', '__return_false' );

		$error = $this->manager->authenticate( null );
		$this->assertErrorResponse( 'it_exchange_rest_guest_checkout_disabled', $error, 401 );
	}

	public function test_authentication_error_if_invalid_email() {

		$_SERVER['HTTP_AUTHORIZATION'] = 'ITHEMES-EXCHANGE-GUEST email="invalid"';
		$_SERVER['REQUEST_URI']        = trailingslashit( rest_get_url_prefix() ) . 'it_exchange/v1/carts';

		$error = $this->manager->authenticate( null );
		$this->assertErrorResponse( 'it_exchange_rest_authentication_failed', $error, 400 );
	}

	public function test_guest_authentication() {

		$_SERVER['HTTP_AUTHORIZATION'] = 'ITHEMES-EXCHANGE-GUEST email="guest@example.org"';
		$_SERVER['REQUEST_URI']        = trailingslashit( rest_get_url_prefix() ) . 'it_exchange/v1/carts';

		$this->assertTrue( $this->manager->authenticate( null ) );
		$this->assertInstanceOf( '\iThemes\Exchange\REST\Auth\GuestAuthScope', $this->manager->get_auth_scope() );

		$this->assertEquals( 'guest@example.org', wp_get_current_user()->ID );
		$this->assertEquals( 'guest@example.org', it_exchange_get_current_customer()->get_email() );
	}
}