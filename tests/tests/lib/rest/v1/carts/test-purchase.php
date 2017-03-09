<?php
/**
 * Test the cart purchase route.
 *
 * @since   2.0.0
 * @license GPLv2
 */

/**
 * Class Test_IT_Exchange_v1_Cart_Purchase_Route
 *
 * @group rest-api
 */
class Test_IT_Exchange_v1_Cart_Purchase_Route extends Test_IT_Exchange_REST_Route {

	public function test_route_registered() {

		$routes = $this->server->get_routes();

		$this->assertArrayHasKey( '/it_exchange/v1/carts/(?P<cart_id>\w+)/purchase', $routes );
	}

	public function test_get_collection() {

		$customer = it_exchange_get_customer( self::factory()->user->create( array( 'role' => 'subscriber' ) ) );
		$cart     = it_exchange_create_cart_and_session( $customer );
		$cart->add_item( $item = ITE_Cart_Product::create( self::product_factory()->create_and_get() ) );

		$request = \iThemes\Exchange\REST\Request::from_path( "/it_exchange/v1/carts/{$cart->get_id()}/purchase" );
		$request->set_param( 'redirect_to', $redirect = add_query_arg( 'redirect', '1', site_url() ) );

		$scope = new \iThemes\Exchange\REST\Auth\CustomerAuthScope( $customer );
		$this->manager->set_auth_scope( $scope );

		$response = $this->server->dispatch( $request );
		$data     = $response->get_data();
		$this->assertEquals( 200, $response->get_status() );

		$offline = array(
			'id'     => 'offline-payments',
			'name'   => 'Offline Payments',
			'label'  => 'Pay with check',
			'nonce'  => wp_create_nonce( 'offline-payments-purchase' ),
			'method' => array(
				'method'  => 'REST',
				'accepts' => array(),
			),
		);

		$pps = array(
			'id'     => 'paypal-standard',
			'name'   => 'PayPal Standard',
			'label'  => 'Pay with PayPal',
			'nonce'  => wp_create_nonce( 'paypal-standard-purchase' ),
			'method' => array(
				'method'  => 'redirect',
				'accepts' => array(),
			),
		);

		$ppss = array(
			'id'     => 'paypal-standard-secure',
			'name'   => 'PayPal Standard - Secure',
			'label'  => 'Pay with PayPal',
			'nonce'  => wp_create_nonce( 'paypal-standard-secure-purchase' ),
			'method' => array(
				'method'  => 'redirect',
				'accepts' => array(),
			),
		);

		foreach ( $data as $method ) {
			if ( $method['id'] === 'offline-payments' ) {
				$this->assertArraySubset( $offline, $method );
			}

			if ( $method['id'] === 'paypal-standard' ) {
				$this->assertArraySubset( $pps, $method );
				$this->assertArrayHasKey( 'url', $method['method'] );
				$this->assertContains( '&redirect_to=' . rawurlencode( $redirect ), $method['method']['url'] );
			}

			if ( $method['id'] === 'paypal-standard-secure' ) {
				$this->assertArraySubset( $ppss, $method );
				$this->assertArrayHasKey( 'url', $method['method'] );
				$this->assertContains( '&redirect_to=' . rawurlencode( $redirect ), $method['method']['url'] );
			}
		}
	}

	public function test_get_collection_errors_on_invalid_redirect() {

		$customer = it_exchange_get_customer( self::factory()->user->create( array( 'role' => 'subscriber' ) ) );
		$cart     = it_exchange_create_cart_and_session( $customer );
		$cart->add_item( $item = ITE_Cart_Product::create( self::product_factory()->create_and_get() ) );

		$request = \iThemes\Exchange\REST\Request::from_path( "/it_exchange/v1/carts/{$cart->get_id()}/purchase" );
		$request->set_param( 'redirect_to', 'http://google.com' );

		$scope = new \iThemes\Exchange\REST\Auth\CustomerAuthScope( $customer );
		$this->manager->set_auth_scope( $scope );

		$response = $this->server->dispatch( $request );
		$this->assertEquals( 400, $response->get_status() );
	}

	public function test_post_errors_on_invalid_redirect() {

		$customer = it_exchange_get_customer( self::factory()->user->create( array( 'role' => 'subscriber' ) ) );
		$cart     = it_exchange_create_cart_and_session( $customer );
		$cart->add_item( $item = ITE_Cart_Product::create( self::product_factory()->create_and_get() ) );

		$request = \iThemes\Exchange\REST\Request::from_path( "/it_exchange/v1/carts/{$cart->get_id()}/purchase" );
		$request->set_method( 'POST' );
		$request->add_header( 'content-type', 'application/json' );
		$request->set_body( wp_json_encode( array(
			'redirect_to' => 'http://google.com',
			'nonce'       => 'asdfas',
			'id'          => 'offline-payments'
		) ) );

		$scope = new \iThemes\Exchange\REST\Auth\CustomerAuthScope( $customer );
		$this->manager->set_auth_scope( $scope );

		$response = $this->server->dispatch( $request );
		$this->assertEquals( 400, $response->get_status() );
	}

	public function test_multiple_sources_not_allowed() {

		$customer = it_exchange_get_customer( self::factory()->user->create( array( 'role' => 'subscriber' ) ) );
		$cart     = it_exchange_create_cart_and_session( $customer );
		$cart->add_item( $item = ITE_Cart_Product::create( self::product_factory()->create_and_get() ) );

		$token = ITE_Payment_Token_Card::create( array(
			'customer' => $customer,
			'gateway'  => 'test-gateway-live',
			'mode'     => 'live',
			'token'    => '1234'
		) )->get_ID();

		$request = \iThemes\Exchange\REST\Request::from_path( "/it_exchange/v1/carts/{$cart->get_id()}/purchase" );
		$request->set_method( 'POST' );
		$request->add_header( 'content-type', 'application/json' );
		$request->set_body( wp_json_encode( array(
			'id'             => 'offline-payments',
			'nonce'          => 'nonce',
			'card'           => array(
				'number' => '4242424242424242',
				'year'   => strval( intval( date( 'Y' ) ) + 4 ),
				'month'  => 10,
			),
			'token'          => $token,
			'one_time_token' => 'stripe_token',
			'tokenize'       => 'stripe_token',
		) ) );

		$scope = new \iThemes\Exchange\REST\Auth\CustomerAuthScope( $customer );
		$this->manager->set_auth_scope( $scope );

		$response = $this->server->dispatch( $request );
		$this->assertEquals( 400, $response->get_status() );
	}

	public function test_invalid_payment_token_rejected() {
		$customer = it_exchange_get_customer( self::factory()->user->create( array( 'role' => 'subscriber' ) ) );
		$cart     = it_exchange_create_cart_and_session( $customer );
		$cart->add_item( $item = ITE_Cart_Product::create( self::product_factory()->create_and_get() ) );

		$request = \iThemes\Exchange\REST\Request::from_path( "/it_exchange/v1/carts/{$cart->get_id()}/purchase" );
		$request->set_method( 'POST' );
		$request->add_header( 'content-type', 'application/json' );
		$request->set_body( wp_json_encode( array(
			'id'    => 'offline-payments',
			'nonce' => 'nonce',
			'token' => 2,
		) ) );

		$scope = new \iThemes\Exchange\REST\Auth\CustomerAuthScope( $customer );
		$this->manager->set_auth_scope( $scope );

		$response = $this->server->dispatch( $request );
		$data     = $response->get_data();
		$this->assertEquals( rest_authorization_required_code(), $response->get_status() );
		$this->assertArrayHasKey( 'data', $data );
		$this->assertArrayHasKey( 'params', $data['data'] );
		$this->assertArrayHasKey( 'token', $data['data']['params'] );
	}

	public function test_rest_purchase() {

		$customer = it_exchange_get_customer( self::factory()->user->create( array( 'role' => 'subscriber' ) ) );
		$cart     = it_exchange_create_cart_and_session( $customer );
		$cart->add_item( $item = ITE_Cart_Product::create( self::product_factory()->create_and_get() ) );

		$request = \iThemes\Exchange\REST\Request::from_path( "/it_exchange/v1/carts/{$cart->get_id()}/purchase" );
		$request->set_method( 'POST' );
		$request->add_header( 'content-type', 'application/json' );
		$request->set_body( wp_json_encode( array(
			'id'    => 'offline-payments',
			'nonce' => wp_create_nonce( 'offline-payments-purchase' ),
		) ) );

		$scope = new \iThemes\Exchange\REST\Auth\CustomerAuthScope( $customer );
		$this->manager->set_auth_scope( $scope );

		$response = $this->server->dispatch( $request );
		$headers  = $response->get_headers();
		$data     = $response->get_data();
		$this->assertEquals( 201, $response->get_status() );

		$this->assertArrayHasKey( 'id', $data );
		$this->assertNotEmpty( $data['id'] );

		$this->assertArrayHasKey( 'Location', $headers );
		$this->assertEquals( rest_url( "/it_exchange/v1/transactions/{$data['id']}" ), $headers['Location'] );

		$this->assertInstanceOf( 'IT_Exchange_Transaction', it_exchange_get_transaction( $data['id'] ) );
	}

	public function test_purchase_with_card() {

		if ( ! $gateway = ITE_Gateways::get( 'test-gateway-live' ) ) {
			$gateway = new IT_Exchange_Test_Gateway_Live();
			ITE_Gateways::register( $gateway );
		}

		$gateway->handlers = array(
			$handler = new IT_Exchange_Stub_Gateway_Request_Handler( '__return_null' )
		);

		$customer = it_exchange_get_customer( self::factory()->user->create( array( 'role' => 'subscriber' ) ) );
		$cart     = it_exchange_create_cart_and_session( $customer );
		$cart->add_item( $item = ITE_Cart_Product::create( self::product_factory()->create_and_get() ) );

		$request = \iThemes\Exchange\REST\Request::from_path( "/it_exchange/v1/carts/{$cart->get_id()}/purchase" );
		$request->set_method( 'POST' );
		$request->add_header( 'content-type', 'application/json' );
		$request->set_body( wp_json_encode( array(
			'id'    => 'test-gateway-live',
			'nonce' => wp_create_nonce( 'test-gateway-live-purchase' ),
			'card'  => array(
				'number' => '4242424242424242',
				'year'   => strval( intval( date( 'Y' ) ) + 4 ),
				'month'  => 10,
				'cvc'    => '123',
				'name'   => 'John Doe',
			),
		) ) );

		$scope = new \iThemes\Exchange\REST\Auth\CustomerAuthScope( $customer );
		$this->manager->set_auth_scope( $scope );

		$response = $this->server->dispatch( $request );

		/** @var ITE_Gateway_Purchase_Request $gateway_request */
		$gateway_request = $handler->get_request();

		$this->assertNotNull( $gateway_request );

		$this->assertNotNull( $gateway_request->get_card() );
		$this->assertEquals( '4242424242424242', $gateway_request->get_card()->get_number() );
		$this->assertEquals( strval( intval( date( 'Y' ) ) + 4 ), $gateway_request->get_card()->get_expiration_year() );
		$this->assertEquals( 10, $gateway_request->get_card()->get_expiration_month() );
		$this->assertEquals( '123', $gateway_request->get_card()->get_cvc() );
		$this->assertEquals( 'John Doe', $gateway_request->get_card()->get_holder_name() );
	}

	public function test_purchase_with_token() {

		if ( ! $gateway = ITE_Gateways::get( 'test-gateway-live' ) ) {
			$gateway = new IT_Exchange_Test_Gateway_Live();
			ITE_Gateways::register( $gateway );
		}

		$gateway->handlers = array(
			$handler = new IT_Exchange_Stub_Gateway_Request_Handler( '__return_null' )
		);

		$customer = it_exchange_get_customer( self::factory()->user->create( array( 'role' => 'subscriber' ) ) );
		$cart     = it_exchange_create_cart_and_session( $customer );
		$cart->add_item( $item = ITE_Cart_Product::create( self::product_factory()->create_and_get() ) );

		$token = ITE_Payment_Token_Card::create( array(
			'customer' => $customer,
			'gateway'  => 'test-gateway-live',
			'mode'     => 'live',
			'token'    => '1234'
		) )->get_ID();

		$request = \iThemes\Exchange\REST\Request::from_path( "/it_exchange/v1/carts/{$cart->get_id()}/purchase" );
		$request->set_method( 'POST' );
		$request->add_header( 'content-type', 'application/json' );
		$request->set_body( wp_json_encode( array(
			'id'    => 'test-gateway-live',
			'nonce' => wp_create_nonce( 'test-gateway-live-purchase' ),
			'token' => $token,
		) ) );

		$scope = new \iThemes\Exchange\REST\Auth\CustomerAuthScope( $customer );
		$this->manager->set_auth_scope( $scope );

		$response = $this->server->dispatch( $request );

		/** @var ITE_Gateway_Purchase_Request $gateway_request */
		$gateway_request = $handler->get_request();

		$this->assertNotNull( $gateway_request );
		$this->assertNotNull( $gateway_request->get_token() );
		$this->assertEquals( $token, $gateway_request->get_token()->get_ID() );
	}

	public function test_purchase_with_one_time_token() {

		if ( ! $gateway = ITE_Gateways::get( 'test-gateway-live' ) ) {
			$gateway = new IT_Exchange_Test_Gateway_Live();
			ITE_Gateways::register( $gateway );
		}

		$gateway->handlers = array(
			$handler = new IT_Exchange_Stub_Gateway_Request_Handler( '__return_null' )
		);

		$customer = it_exchange_get_customer( self::factory()->user->create( array( 'role' => 'subscriber' ) ) );
		$cart     = it_exchange_create_cart_and_session( $customer );
		$cart->add_item( $item = ITE_Cart_Product::create( self::product_factory()->create_and_get() ) );


		$request = \iThemes\Exchange\REST\Request::from_path( "/it_exchange/v1/carts/{$cart->get_id()}/purchase" );
		$request->set_method( 'POST' );
		$request->add_header( 'content-type', 'application/json' );
		$request->set_body( wp_json_encode( array(
			'id'             => 'test-gateway-live',
			'nonce'          => wp_create_nonce( 'test-gateway-live-purchase' ),
			'one_time_token' => 'card_token',
		) ) );

		$scope = new \iThemes\Exchange\REST\Auth\CustomerAuthScope( $customer );
		$this->manager->set_auth_scope( $scope );

		$response = $this->server->dispatch( $request );

		/** @var ITE_Gateway_Purchase_Request $gateway_request */
		$gateway_request = $handler->get_request();

		$this->assertNotNull( $gateway_request );
		$this->assertEquals( 'card_token', $gateway_request->get_one_time_token() );
	}

	public function test_purchase_with_tokenize_source() {

		if ( ! $gateway = ITE_Gateways::get( 'test-gateway-live' ) ) {
			$gateway = new IT_Exchange_Test_Gateway_Live();
			ITE_Gateways::register( $gateway );
		}

		$gateway->handlers = array(
			$handler = new IT_Exchange_Stub_Gateway_Request_Handler( '__return_null' )
		);

		$customer = it_exchange_get_customer( self::factory()->user->create( array( 'role' => 'subscriber' ) ) );
		$cart     = it_exchange_create_cart_and_session( $customer );
		$cart->add_item( $item = ITE_Cart_Product::create( self::product_factory()->create_and_get() ) );


		$request = \iThemes\Exchange\REST\Request::from_path( "/it_exchange/v1/carts/{$cart->get_id()}/purchase" );
		$request->set_method( 'POST' );
		$request->add_header( 'content-type', 'application/json' );
		$request->set_body( wp_json_encode( array(
			'id'       => 'test-gateway-live',
			'nonce'    => wp_create_nonce( 'test-gateway-live-purchase' ),
			'tokenize' => 'card_token',
		) ) );

		$scope = new \iThemes\Exchange\REST\Auth\CustomerAuthScope( $customer );
		$this->manager->set_auth_scope( $scope );

		$response = $this->server->dispatch( $request );

		/** @var ITE_Gateway_Purchase_Request $gateway_request */
		$gateway_request = $handler->get_request();

		$this->assertNotNull( $gateway_request );
		$this->assertNotNull( $gateway_request->get_tokenize() );
		$this->assertEquals( 'card_token', $gateway_request->get_tokenize()->get_source_to_tokenize() );
	}

	public function test_purchase_with_tokenize_card() {

		if ( ! $gateway = ITE_Gateways::get( 'test-gateway-live' ) ) {
			$gateway = new IT_Exchange_Test_Gateway_Live();
			ITE_Gateways::register( $gateway );
		}

		$gateway->handlers = array(
			$handler = new IT_Exchange_Stub_Gateway_Request_Handler( '__return_null' )
		);

		$customer = it_exchange_get_customer( self::factory()->user->create( array( 'role' => 'subscriber' ) ) );
		$cart     = it_exchange_create_cart_and_session( $customer );
		$cart->add_item( $item = ITE_Cart_Product::create( self::product_factory()->create_and_get() ) );

		$request = \iThemes\Exchange\REST\Request::from_path( "/it_exchange/v1/carts/{$cart->get_id()}/purchase" );
		$request->set_method( 'POST' );
		$request->add_header( 'content-type', 'application/json' );
		$request->set_body( wp_json_encode( array(
			'id'       => 'test-gateway-live',
			'nonce'    => wp_create_nonce( 'test-gateway-live-purchase' ),
			'tokenize' => array(
				'number' => '4242424242424242',
				'year'   => strval( intval( date( 'Y' ) ) + 4 ),
				'month'  => 10,
				'cvc'    => '123',
				'name'   => 'John Doe',
			),
		) ) );

		$scope = new \iThemes\Exchange\REST\Auth\CustomerAuthScope( $customer );
		$this->manager->set_auth_scope( $scope );

		$response = $this->server->dispatch( $request );

		/** @var ITE_Gateway_Purchase_Request $gateway_request */
		$gateway_request = $handler->get_request();

		$this->assertNotNull( $gateway_request );

		$this->assertNotNull( $gateway_request->get_tokenize() );
		$source = $gateway_request->get_tokenize()->get_source_to_tokenize();
		$this->assertEquals( '4242424242424242', $source->get_number() );
		$this->assertEquals( strval( intval( date( 'Y' ) ) + 4 ), $source->get_expiration_year() );
		$this->assertEquals( 10, $source->get_expiration_month() );
		$this->assertEquals( '123', $source->get_cvc() );
		$this->assertEquals( 'John Doe', $source->get_holder_name() );
	}
}