<?php
/**
 * Test the token routes.
 *
 * @since   2.0.0
 * @license GPLv2
 */

/**
 * Class Test_IT_Exchange_v1_Customer_Tokens_Route
 *
 * @group rest-api
 */
class Test_IT_Exchange_v1_Customer_Tokens_Route extends Test_IT_Exchange_REST_Route {

	public function test_route_registered() {

		$routes = $this->server->get_routes();

		$this->assertArrayHasKey( '/it_exchange/v1/customers/(?P<customer_id>\d+)/tokens', $routes );
		$this->assertArrayHasKey( '/it_exchange/v1/customers/(?P<customer_id>\d+)/tokens/(?P<token_id>\d+)', $routes );
	}

	public function test_collection_forbidden_for_public() {

		$customer = it_exchange_get_customer( self::factory()->user->create( array( 'role' => 'subscriber' ) ) );
		$request  = \iThemes\Exchange\REST\Request::from_path( "/it_exchange/v1/customers/{$customer->get_ID()}/tokens" );

		$scope = new \iThemes\Exchange\REST\Auth\PublicAuthScope();
		$this->manager->set_auth_scope( $scope );

		$response = $this->server->dispatch( $request );
		$this->assertEquals( rest_authorization_required_code(), $response->get_status() );
	}

	public function test_collection_forbidden_for_guest() {

		$customer = it_exchange_get_customer( self::factory()->user->create( array( 'role' => 'subscriber' ) ) );
		$request  = \iThemes\Exchange\REST\Request::from_path( "/it_exchange/v1/customers/{$customer->get_ID()}/tokens" );

		$scope = new \iThemes\Exchange\REST\Auth\GuestAuthScope( it_exchange_get_customer( 'guest@example.org' ) );
		$this->manager->set_auth_scope( $scope );

		$response = $this->server->dispatch( $request );
		$this->assertEquals( rest_authorization_required_code(), $response->get_status() );
	}

	public function test_collection_forbidden_for_different_customer() {

		$customer = it_exchange_get_customer( self::factory()->user->create( array( 'role' => 'subscriber' ) ) );
		$request  = \iThemes\Exchange\REST\Request::from_path( '/it_exchange/v1/customers/1/tokens' );

		$scope = new \iThemes\Exchange\REST\Auth\CustomerAuthScope( $customer );
		$this->manager->set_auth_scope( $scope );

		$response = $this->server->dispatch( $request );
		$this->assertEquals( rest_authorization_required_code(), $response->get_status() );
	}

	public function test_collection_allowed_for_same_customer() {

		$customer = it_exchange_get_customer( self::factory()->user->create( array( 'role' => 'subscriber' ) ) );
		$request  = \iThemes\Exchange\REST\Request::from_path( "/it_exchange/v1/customers/{$customer->get_ID()}/tokens" );

		$scope = new \iThemes\Exchange\REST\Auth\CustomerAuthScope( $customer );
		$this->manager->set_auth_scope( $scope );

		$response = $this->server->dispatch( $request );
		$this->assertEquals( 200, $response->get_status() );
	}

	public function test_collection_allowed_for_administrator() {

		$customer = it_exchange_get_customer( self::factory()->user->create( array( 'role' => 'subscriber' ) ) );
		$request  = \iThemes\Exchange\REST\Request::from_path( "/it_exchange/v1/customers/{$customer->get_ID()}/tokens" );

		$scope = new \iThemes\Exchange\REST\Auth\CustomerAuthScope( it_exchange_get_customer( 1 ) );
		$this->manager->set_auth_scope( $scope );

		$response = $this->server->dispatch( $request );
		$this->assertEquals( 200, $response->get_status() );
	}

	public function test_collection_edit_context_forbidden_for_list_tokens_only() {

		$customer = it_exchange_get_customer( self::factory()->user->create( array( 'role' => 'subscriber' ) ) );
		$request  = \iThemes\Exchange\REST\Request::from_path( "/it_exchange/v1/customers/{$customer->get_ID()}/tokens" );
		$request->set_param( 'context', 'edit' );

		$scope = $this->getMockBuilder( '\iThemes\Exchange\REST\Auth\AuthScope' )->setMethods( array( 'can' ) )->getMockForAbstractClass();
		$scope->expects( $this->at( 0 ) )->method( 'can' )->with( 'it_list_payment_tokens' )->willReturn( true );
		$scope->expects( $this->at( 1 ) )->method( 'can' )->with( 'it_edit_customer_payment_tokens' )->willReturn( false );
		$this->manager->set_auth_scope( $scope );

		$response = $this->server->dispatch( $request );
		$this->assertErrorResponse( 'it_exchange_rest_forbidden_context', $response, rest_authorization_required_code() );
	}

	public function test_collection_edit_context_allowed_for_administrator() {

		$customer = it_exchange_get_customer( self::factory()->user->create( array( 'role' => 'subscriber' ) ) );
		$request  = \iThemes\Exchange\REST\Request::from_path( "/it_exchange/v1/customers/{$customer->get_ID()}/tokens" );
		$request->set_param( 'context', 'edit' );

		$scope = new \iThemes\Exchange\REST\Auth\CustomerAuthScope( it_exchange_get_customer( 1 ) );
		$this->manager->set_auth_scope( $scope );

		$response = $this->server->dispatch( $request );
		$this->assertEquals( 200, $response->get_status() );
	}

	public function test_collection() {

		ITE_Gateways::register( new IT_Exchange_Test_Gateway_Live() );
		$customer = it_exchange_get_customer( self::factory()->user->create( array( 'role' => 'subscriber' ) ) );

		$t1 = ITE_Payment_Token_Card::create( array(
			'customer' => $customer,
			'token'    => 'test-source-1',
			'gateway'  => 'test-gateway-live'
		) );
		$t2 = ITE_Payment_Token_Card::create( array(
			'customer' => $customer,
			'token'    => 'test-source-2',
			'gateway'  => 'test-gateway-live'
		) );

		$request = \iThemes\Exchange\REST\Request::from_path( "/it_exchange/v1/customers/{$customer->get_ID()}/tokens" );

		$scope = new \iThemes\Exchange\REST\Auth\CustomerAuthScope( it_exchange_get_customer( 1 ) );
		$this->manager->set_auth_scope( $scope );

		$response = $this->server->dispatch( $request );
		$this->assertEquals( 200, $response->get_status() );
		$data = $response->get_data();

		$this->assertCount( 2, $data );
		$this->assertEquals( $t1->get_ID(), $data[0]['id'] );
		$this->assertEquals( $t2->get_ID(), $data[1]['id'] );
	}

	public function test_collection_filter_by_gateway() {
		if ( ! $live = ITE_Gateways::get( 'test-gateway-live' ) ) {
			$live = new IT_Exchange_Test_Gateway_Live();
			ITE_Gateways::register( $live );
		}

		if ( ! $sandbox = ITE_Gateways::get( 'test-gateway-sandbox' ) ) {
			$sandbox = new IT_Exchange_Test_Gateway_Sandbox();
			ITE_Gateways::register( $sandbox );
		}

		$live->handlers = $sandbox->handlers = array( new IT_Exchange_Stub_Gateway_Request_Handler() );

		$this->manager->_reset();
		$this->initialize_server();

		$customer = it_exchange_get_customer( self::factory()->user->create( array( 'role' => 'subscriber' ) ) );

		$live_token    = ITE_Payment_Token_Card::create( array(
			'customer' => $customer,
			'token'    => 'test-source-1',
			'gateway'  => $live->get_slug(),
			'mode'     => 'live',
		) );
		$sandbox_token = ITE_Payment_Token_Card::create( array(
			'customer' => $customer,
			'token'    => 'test-source-2',
			'gateway'  => $sandbox->get_slug(),
			'mode'     => 'sandbox',
		) );

		$request = \iThemes\Exchange\REST\Request::from_path( "/it_exchange/v1/customers/{$customer->get_ID()}/tokens" );
		$request->set_param( 'gateway', $live->get_slug() );

		$scope = new \iThemes\Exchange\REST\Auth\CustomerAuthScope( $customer );
		$this->manager->set_auth_scope( $scope );

		$response = $this->server->dispatch( $request );
		$this->assertEquals( 200, $response->get_status() );
		$this->assertCount( 1, $response->get_data() );
		$this->assertEquals( $live_token->get_ID(), $response->data[0]['id'] );

		$request = \iThemes\Exchange\REST\Request::from_path( "/it_exchange/v1/customers/{$customer->get_ID()}/tokens" );
		$request->set_param( 'gateway', $sandbox->get_slug() );

		$scope = new \iThemes\Exchange\REST\Auth\CustomerAuthScope( $customer );
		$this->manager->set_auth_scope( $scope );

		$response = $this->server->dispatch( $request );
		$this->assertEquals( 200, $response->get_status() );
		$this->assertCount( 1, $response->get_data() );
		$this->assertEquals( $sandbox_token->get_ID(), $response->data[0]['id'] );
	}

	public function test_create_token() {

		if ( ! $gateway = ITE_Gateways::get( 'test-gateway-live' ) ) {
			$gateway = new IT_Exchange_Test_Gateway_Live();
			ITE_Gateways::register( $gateway );
		}

		$handler = new IT_Exchange_Stub_Gateway_Request_Handler( function ( ITE_Gateway_Tokenize_Request $request ) {
			static $i = 0;
			$i ++;

			return ITE_Payment_Token_Card::create( array(
				'customer' => $request->get_customer(),
				'label'    => $request->get_label(),
				'token'    => "test-id-{$i}",
				'gateway'  => 'test-gateway-live',
				'mode'     => 'live',
			) );
		} );

		$gateway->handlers = array( $handler );

		$this->manager->_reset();
		$this->initialize_server();

		$customer = it_exchange_get_customer( self::factory()->user->create( array( 'role' => 'subscriber' ) ) );
		$request  = \iThemes\Exchange\REST\Request::from_path( "/it_exchange/v1/customers/{$customer->get_ID()}/tokens" );
		$request->set_method( 'POST' );
		$request->add_header( 'content-type', 'application/json' );
		$request->set_body( wp_json_encode( array(
			'label'   => 'Custom Label',
			'source'  => 'my-source',
			'gateway' => 'test-gateway-live',
		) ) );

		$scope = new \iThemes\Exchange\REST\Auth\CustomerAuthScope( it_exchange_get_customer( 1 ) );
		$this->manager->set_auth_scope( $scope );

		$response = $this->server->dispatch( $request );
		$data     = $response->get_data();
		$this->assertEquals( 201, $response->get_status() );
		$this->assertNotEmpty( $data['id'] );
		$this->assertEquals( 'Custom Label', $data['label']['raw'] );

		$token = ITE_Payment_Token::get( $data['id'] );
		$this->assertNotNull( $token );
		$this->assertEquals( 'Custom Label', $token->label );

		/** @var ITE_Gateway_Tokenize_Request $tokenize */
		$tokenize = $handler->get_request();

		$this->assertEquals( 'my-source', $tokenize->get_source_to_tokenize() );
	}

	public function test_object_not_found_if_invalid_id() {

		$customer = it_exchange_get_customer( self::factory()->user->create( array( 'role' => 'subscriber' ) ) );
		$request  = \iThemes\Exchange\REST\Request::from_path( "/it_exchange/v1/customers/{$customer->get_ID()}/tokens/500" );

		$scope = new \iThemes\Exchange\REST\Auth\CustomerAuthScope( $customer );
		$this->manager->set_auth_scope( $scope );

		$response = $this->server->dispatch( $request );
		$this->assertEquals( 404, $response->get_status() );
	}

	public function test_object_not_found_if_customer_deleted() {

		$customer = it_exchange_get_customer( self::factory()->user->create( array( 'role' => 'subscriber' ) ) );
		$token    = ITE_Payment_Token_Card::create( array(
			'customer' => $customer,
			'token'    => 'test-token',
			'mode'     => 'live',
			'gateway'  => 'test-gateway-live',
		) );
		$request  = \iThemes\Exchange\REST\Request::from_path(
			"/it_exchange/v1/customers/{$customer->get_ID()}/tokens/{$token->get_ID()}"
		);

		wp_delete_user( $customer->get_ID() );

		$scope = new \iThemes\Exchange\REST\Auth\CustomerAuthScope( $customer );
		$this->manager->set_auth_scope( $scope );

		$response = $this->server->dispatch( $request );
		$this->assertEquals( 404, $response->get_status() );
	}

	public function test_object_not_found_if_id_mismatch() {

		$c1      = it_exchange_get_customer( self::factory()->user->create( array( 'role' => 'subscriber' ) ) );
		$c2      = it_exchange_get_customer( self::factory()->user->create( array( 'role' => 'subscriber' ) ) );
		$token   = ITE_Payment_Token_Card::create( array(
			'customer' => $c1,
			'token'    => 'test-token',
			'mode'     => 'live',
			'gateway'  => 'test-gateway-live',
		) );
		$request = \iThemes\Exchange\REST\Request::from_path(
			"/it_exchange/v1/customers/{$c2->get_ID()}/tokens/{$token->get_ID()}"
		);

		$scope = new \iThemes\Exchange\REST\Auth\CustomerAuthScope( $c1 );
		$this->manager->set_auth_scope( $scope );

		$response = $this->server->dispatch( $request );
		$this->assertEquals( 404, $response->get_status() );
	}

	public function test_get_object() {

		$customer = it_exchange_get_customer( self::factory()->user->create( array( 'role' => 'subscriber' ) ) );
		$token    = ITE_Payment_Token_Card::create( array(
			'customer' => $customer,
			'token'    => 'test-token',
			'mode'     => 'live',
			'gateway'  => 'test-gateway-live',
			'label'    => 'My Label',
		) );
		$request  = \iThemes\Exchange\REST\Request::from_path(
			"/it_exchange/v1/customers/{$customer->get_ID()}/tokens/{$token->get_ID()}"
		);
		$request->set_param( 'context', 'edit' );

		$scope = new \iThemes\Exchange\REST\Auth\CustomerAuthScope( $customer );
		$this->manager->set_auth_scope( $scope );

		$response = $this->server->dispatch( $request );
		$this->assertEquals( 200, $response->get_status() );
		$this->assertEquals( $token->ID, $response->data['id'] );
		$this->assertEquals( 'My Label', $response->data['label']['raw'] );
		$this->assertEquals( 'My Label', $response->data['label']['rendered'] );
	}

	public function test_delete_object() {

		$customer = it_exchange_get_customer( self::factory()->user->create( array( 'role' => 'subscriber' ) ) );
		$token    = ITE_Payment_Token_Card::create( array(
			'customer' => $customer,
			'token'    => 'test-token',
			'mode'     => 'live',
			'gateway'  => 'test-gateway-live',
			'label'    => 'My Label',
		) );
		$request  = \iThemes\Exchange\REST\Request::from_path(
			"/it_exchange/v1/customers/{$customer->get_ID()}/tokens/{$token->get_ID()}"
		);
		$request->set_method( 'DELETE' );

		$scope = new \iThemes\Exchange\REST\Auth\CustomerAuthScope( $customer );
		$this->manager->set_auth_scope( $scope );

		$response = $this->server->dispatch( $request );
		$this->assertEquals( WP_Http::NO_CONTENT, $response->get_status() );

		$deleted = ITE_Payment_Token::get( $token->get_ID() );
		$this->assertNotNull( $deleted );
		$this->assertEquals( time(), $deleted->deleted->getTimestamp(), '', 2 );
	}

	public function test_update_label() {

		if ( ! $live = ITE_Gateways::get( 'test-gateway-live' ) ) {
			$live = new IT_Exchange_Test_Gateway_Live();
			ITE_Gateways::register( $live );
		}

		$live->handlers = array( new IT_Exchange_Stub_Gateway_Request_Handler() );

		$this->manager->_reset();
		$this->initialize_server();

		$customer = it_exchange_get_customer( self::factory()->user->create( array( 'role' => 'subscriber' ) ) );
		$token    = ITE_Payment_Token_Card::create( array(
			'customer' => $customer,
			'token'    => 'test-token',
			'mode'     => 'live',
			'gateway'  => 'test-gateway-live',
			'label'    => 'My Label',
		) );
		$request  = \iThemes\Exchange\REST\Request::from_path(
			"/it_exchange/v1/customers/{$customer->get_ID()}/tokens/{$token->get_ID()}"
		);
		$request->set_method( 'PUT' );
		$request->add_header( 'content-type', 'application/json' );
		$request->set_body( wp_json_encode( array(
			'label'   => 'My New Label',
			'gateway' => 'test-gateway-live',
		) ) );

		$scope = new \iThemes\Exchange\REST\Auth\CustomerAuthScope( $customer );
		$this->manager->set_auth_scope( $scope );

		$response = $this->server->dispatch( $request );
		$this->assertEquals( 200, $response->get_status() );
		$this->assertEquals( 'My New Label', $response->data['label']['raw'] );
		$this->assertEquals( 'My New Label', $response->data['label']['rendered'] );

		$this->assertEquals( 'My New Label', ITE_Payment_Token::get( $token->ID )->label );
	}

	public function test_update_make_primary() {

		if ( ! $live = ITE_Gateways::get( 'test-gateway-live' ) ) {
			$live = new IT_Exchange_Test_Gateway_Live();
			ITE_Gateways::register( $live );
		}

		$live->handlers = array( new IT_Exchange_Stub_Gateway_Request_Handler() );

		$this->manager->_reset();
		$this->initialize_server();

		$customer = it_exchange_get_customer( self::factory()->user->create( array( 'role' => 'subscriber' ) ) );
		$t1       = ITE_Payment_Token_Card::create( array(
			'customer' => $customer,
			'token'    => 'test-token-1',
			'mode'     => 'live',
			'gateway'  => 'test-gateway-live',
			'primary'  => true,
		) );
		$t2       = ITE_Payment_Token_Card::create( array(
			'customer' => $customer,
			'token'    => 'test-token-2',
			'mode'     => 'live',
			'gateway'  => 'test-gateway-live',
		) );

		$request = \iThemes\Exchange\REST\Request::from_path(
			"/it_exchange/v1/customers/{$customer->get_ID()}/tokens/{$t2->get_ID()}"
		);
		$request->set_method( 'PUT' );
		$request->add_header( 'content-type', 'application/json' );
		$request->set_body( wp_json_encode( array(
			'primary' => true,
			'gateway' => 'test-gateway-live',
		) ) );

		$scope = new \iThemes\Exchange\REST\Auth\CustomerAuthScope( $customer );
		$this->manager->set_auth_scope( $scope );

		$response = $this->server->dispatch( $request );
		$this->assertEquals( 200, $response->get_status() );

		$t1 = ITE_Payment_Token::get( $t1->get_ID() );
		$t2 = ITE_Payment_Token::get( $t2->get_ID() );

		$this->assertFalse( $t1->primary );
		$this->assertTrue( $t2->primary );
	}

	public function test_update_make_non_primary() {

		if ( ! $live = ITE_Gateways::get( 'test-gateway-live' ) ) {
			$live = new IT_Exchange_Test_Gateway_Live();
			ITE_Gateways::register( $live );
		}

		$live->handlers = array( new IT_Exchange_Stub_Gateway_Request_Handler() );

		$this->manager->_reset();
		$this->initialize_server();

		$customer = it_exchange_get_customer( self::factory()->user->create( array( 'role' => 'subscriber' ) ) );
		$t1       = ITE_Payment_Token_Card::create( array(
			'customer' => $customer,
			'token'    => 'test-token-1',
			'mode'     => 'live',
			'gateway'  => 'test-gateway-live',
			'primary'  => true,
		) );
		$t2       = ITE_Payment_Token_Card::create( array(
			'customer' => $customer,
			'token'    => 'test-token-2',
			'mode'     => 'live',
			'gateway'  => 'test-gateway-live',
		) );

		$request = \iThemes\Exchange\REST\Request::from_path(
			"/it_exchange/v1/customers/{$customer->get_ID()}/tokens/{$t1->get_ID()}"
		);
		$request->set_method( 'PUT' );
		$request->add_header( 'content-type', 'application/json' );
		$request->set_body( wp_json_encode( array(
			'primary' => false,
			'gateway' => 'test-gateway-live',
		) ) );

		$scope = new \iThemes\Exchange\REST\Auth\CustomerAuthScope( $customer );
		$this->manager->set_auth_scope( $scope );

		$response = $this->server->dispatch( $request );
		$this->assertEquals( 200, $response->get_status() );

		$t1 = ITE_Payment_Token::get( $t1->get_ID() );
		$t2 = ITE_Payment_Token::get( $t2->get_ID() );

		$this->assertFalse( $t1->primary );
		$this->assertTrue( $t2->primary );
	}

	public function test_update_expiration() {

		if ( ! $gateway = ITE_Gateways::get( 'test-gateway-live' ) ) {
			$gateway = new IT_Exchange_Test_Gateway_Live();
			ITE_Gateways::register( $gateway );
		}

		$handler = new IT_Exchange_Stub_Gateway_Request_Handler( function ( ITE_Gateway_Update_Payment_Token_Request $request ) {

			/** @var ITE_Payment_Token_Card $token */
			$token = $request->get_token();
			$token->set_expiration( $request->get_expiration_month(), $request->get_expiration_year() );

			return $token;
		} );

		$gateway->handlers = array( $handler );

		$this->manager->_reset();
		$this->initialize_server();

		$customer = it_exchange_get_customer( self::factory()->user->create( array( 'role' => 'subscriber' ) ) );
		$token    = ITE_Payment_Token_Card::create( array(
			'customer' => $customer,
			'token'    => 'test-token',
			'mode'     => 'live',
			'gateway'  => 'test-gateway-live',
			'label'    => 'My Label',
		) );

		$year = ( (int) date( 'Y' ) ) + 2;

		$request = \iThemes\Exchange\REST\Request::from_path(
			"/it_exchange/v1/customers/{$customer->get_ID()}/tokens/{$token->get_ID()}"
		);
		$request->set_method( 'PUT' );
		$request->add_header( 'content-type', 'application/json' );
		$request->set_body( wp_json_encode( array(
			'gateway'    => 'test-gateway-live',
			'expiration' => array(
				'month' => 2,
				'year'  => $year,
			)
		) ) );

		$scope = new \iThemes\Exchange\REST\Auth\CustomerAuthScope( it_exchange_get_customer( 1 ) );
		$this->manager->set_auth_scope( $scope );

		$response = $this->server->dispatch( $request );
		$data     = $response->get_data();
		$this->assertEquals( 200, $response->get_status() );
		$this->assertEquals( 2, $data['expiration']['month'] );
		$this->assertEquals( ( (int) date( 'Y' ) ) + 2, $data['expiration']['year'] );

		/** @var ITE_Payment_Token_Card $token */
		$token = ITE_Payment_Token::get( $data['id'] );
		$this->assertNotNull( $token );
		$this->assertEquals( '2', $token->get_expiration_month() );
		$this->assertEquals( (string) $year, $token->get_expiration_year() );

		/** @var ITE_Gateway_Update_Payment_Token_Request $update */
		$update = $handler->get_request();

		$this->assertEquals( 2, $update->get_expiration_month() );
		$this->assertEquals( $year, $update->get_expiration_year() );
	}
}