<?php
/**
 * Test the Refunds Route.
 *
 * @since   2.0.0
 * @license GPLv2
 */

/**
 * Class Test_IT_Exchange_v1_Transaction_Refunds_Route
 *
 * @group rest-api
 */
class Test_IT_Exchange_v1_Transaction_Refunds_Route extends Test_IT_Exchange_REST_Route {

	public function test_route_registered() {

		$routes = $this->server->get_routes();

		$this->assertArrayHasKey( '/it_exchange/v1/transactions/(?P<transaction_id>\d+)/refunds', $routes );
		$this->assertArrayHasKey( '/it_exchange/v1/transactions/(?P<transaction_id>\d+)/refunds/(?P<refund_id>\d+)', $routes );
	}

	public function test_collection_forbidden_for_public() {

		$customer = it_exchange_get_customer( self::factory()->user->create( array( 'role' => 'subscriber' ) ) );
		$txn      = self::transaction_factory()->create( array( 'customer' => $customer ) );
		$request  = \iThemes\Exchange\REST\Request::from_path( "/it_exchange/v1/transactions/{$txn}/refunds" );
		$this->assertNotNull( $request );

		$scope = new \iThemes\Exchange\REST\Auth\PublicAuthScope();
		$this->manager->set_auth_scope( $scope );

		$response = $this->server->dispatch( $request );
		$this->assertEquals( rest_authorization_required_code(), $response->get_status() );
	}

	public function test_collection_forbidden_for_guest() {

		$customer = it_exchange_get_customer( self::factory()->user->create( array( 'role' => 'subscriber' ) ) );
		$txn      = self::transaction_factory()->create( array( 'customer' => $customer ) );
		$request  = \iThemes\Exchange\REST\Request::from_path( "/it_exchange/v1/transactions/{$txn}/refunds" );
		$this->assertNotNull( $request );

		$scope = new \iThemes\Exchange\REST\Auth\GuestAuthScope( it_exchange_get_customer( 'guest@example.org' ) );
		$this->manager->set_auth_scope( $scope );

		$response = $this->server->dispatch( $request );
		$this->assertEquals( rest_authorization_required_code(), $response->get_status() );
	}

	public function test_collection_forbidden_for_customer() {

		$customer = it_exchange_get_customer( self::factory()->user->create( array( 'role' => 'subscriber' ) ) );
		$txn      = self::transaction_factory()->create( array( 'customer' => $customer ) );
		$request  = \iThemes\Exchange\REST\Request::from_path( "/it_exchange/v1/transactions/{$txn}/refunds" );
		$this->assertNotNull( $request );

		$scope = new \iThemes\Exchange\REST\Auth\CustomerAuthScope( $customer );
		$this->manager->set_auth_scope( $scope );

		$response = $this->server->dispatch( $request );
		$this->assertEquals( rest_authorization_required_code(), $response->get_status() );
	}

	public function test_collection_allowed_for_administrator() {

		$customer = it_exchange_get_customer( self::factory()->user->create( array( 'role' => 'subscriber' ) ) );
		$txn      = self::transaction_factory()->create( array( 'customer' => $customer ) );
		$request  = \iThemes\Exchange\REST\Request::from_path( "/it_exchange/v1/transactions/{$txn}/refunds" );
		$this->assertNotNull( $request );

		$scope = new \iThemes\Exchange\REST\Auth\CustomerAuthScope( it_exchange_get_customer( 1 ) );
		$this->manager->set_auth_scope( $scope );

		$response = $this->server->dispatch( $request );
		$this->assertEquals( 200, $response->get_status() );
	}

	public function test_collection_edit_context_forbidden_for_list_refunds_only() {

		$customer = it_exchange_get_customer( self::factory()->user->create( array( 'role' => 'subscriber' ) ) );
		$txn      = self::transaction_factory()->create( array( 'customer' => $customer ) );
		$request  = \iThemes\Exchange\REST\Request::from_path( "/it_exchange/v1/transactions/{$txn}/refunds" );
		$request->set_param( 'context', 'edit' );
		$this->assertNotNull( $request );

		$scope = $this->getMockBuilder( '\iThemes\Exchange\REST\Auth\AuthScope' )->setMethods( array( 'can' ) )->getMockForAbstractClass();
		$scope->expects( $this->at( 0 ) )->method( 'can' )->with( 'it_list_transaction_refunds' )->willReturn( true );
		$scope->expects( $this->at( 1 ) )->method( 'can' )->with( 'it_edit_refunds' )->willReturn( false );
		$this->manager->set_auth_scope( $scope );

		$response = $this->server->dispatch( $request );
		$this->assertErrorResponse( 'it_exchange_rest_forbidden_context', $response, rest_authorization_required_code() );
	}

	public function test_collection_edit_context_allowed_for_administrator() {

		$customer = it_exchange_get_customer( self::factory()->user->create( array( 'role' => 'subscriber' ) ) );
		$txn      = self::transaction_factory()->create( array( 'customer' => $customer ) );
		$request  = \iThemes\Exchange\REST\Request::from_path( "/it_exchange/v1/transactions/{$txn}/refunds" );
		$request->set_param( 'context', 'edit' );

		$scope = new \iThemes\Exchange\REST\Auth\CustomerAuthScope( it_exchange_get_customer( 1 ) );
		$this->manager->set_auth_scope( $scope );

		$response = $this->server->dispatch( $request );
		$this->assertEquals( 200, $response->get_status() );
	}

	public function test_collection() {

		$customer = it_exchange_get_customer( self::factory()->user->create( array( 'role' => 'subscriber' ) ) );
		$txn      = self::transaction_factory()->create( array( 'customer' => $customer ) );

		$r1 = ITE_Refund::create( array(
			'transaction' => $txn,
			'amount'      => 5.00,
			'gateway_id'  => 'test_id_1',
		) );
		$r2 = ITE_Refund::create( array(
			'transaction' => $txn,
			'amount'      => 3.00,
			'gateway_id'  => 'test_id_2',
		) );

		$request = \iThemes\Exchange\REST\Request::from_path( "/it_exchange/v1/transactions/{$txn}/refunds" );

		$scope = new \iThemes\Exchange\REST\Auth\CustomerAuthScope( it_exchange_get_customer( 1 ) );
		$this->manager->set_auth_scope( $scope );

		$response = $this->server->dispatch( $request );
		$this->assertEquals( 200, $response->get_status() );
		$data = $response->get_data();

		$this->assertCount( 2, $data );
		$this->assertEquals( $r1->get_ID(), $data[0]['id'] );
		$this->assertEquals( $r2->get_ID(), $data[1]['id'] );
	}

	public function test_create_forbidden_if_transaction_cannot_be_refunded() {

		$customer = it_exchange_get_customer( self::factory()->user->create( array( 'role' => 'subscriber' ) ) );
		$txn      = self::transaction_factory()->create( array( 'customer' => $customer ) );
		$request  = \iThemes\Exchange\REST\Request::from_path( "/it_exchange/v1/transactions/{$txn}/refunds" );
		$request->set_method( 'POST' );
		$request->add_header( 'content-type', 'application/json' );
		$request->set_body( wp_json_encode( array(
			'amount' => 5.00,
			'reason' => 'Requested',
		) ) );

		$scope = new \iThemes\Exchange\REST\Auth\CustomerAuthScope( it_exchange_get_customer( 1 ) );
		$this->manager->set_auth_scope( $scope );

		add_filter( 'it_exchange_transaction_can_be_refunded', '__return_false' );

		$response = $this->server->dispatch( $request );
		$this->assertErrorResponse( 'it_exchange_rest_invalid_transaction_for_refund', $response, 400 );
	}

	public function test_create_refund() {

		if ( ! $gateway = ITE_Gateways::get( 'test-gateway-live' ) ) {
			$gateway = new IT_Exchange_Test_Gateway_Live();
			ITE_Gateways::register( $gateway );
		}

		$handler = new IT_Exchange_Stub_Gateway_Request_Handler( function ( ITE_Gateway_Refund_Request $request ) {
			return ITE_Refund::create( array(
				'transaction' => $request->get_transaction()->get_ID(),
				'amount'      => $request->get_amount(),
				'gateway_id'  => 'test-id-1',
				'reason'      => $request->get_reason(),
			) );
		} );

		$gateway->handlers = array( $handler );

		$customer = it_exchange_get_customer( self::factory()->user->create( array( 'role' => 'subscriber' ) ) );
		$txn      = self::transaction_factory()->create( array(
			'customer' => $customer,
			'method'   => $gateway->get_slug(),
		) );
		$request  = \iThemes\Exchange\REST\Request::from_path( "/it_exchange/v1/transactions/{$txn}/refunds" );
		$request->set_method( 'POST' );
		$request->add_header( 'content-type', 'application/json' );
		$request->set_body( wp_json_encode( array(
			'amount' => 5.00,
			'reason' => 'Requested',
		) ) );

		$scope = new \iThemes\Exchange\REST\Auth\CustomerAuthScope( it_exchange_get_customer( 1 ) );
		$this->manager->set_auth_scope( $scope );

		add_filter( 'it_exchange_transaction_can_be_refunded', '__return_true' );

		$response = $this->server->dispatch( $request );
		$data     = $response->get_data();
		$this->assertEquals( 201, $response->get_status() );
		$this->assertNotEmpty( $data['id'] );
		$this->assertEquals( 5.00, $data['amount'] );
		$this->assertEquals( 'Requested', $data['reason'] );

		$refund = ITE_Refund::get( $data['id'] );
		$this->assertNotNull( $refund );
		$this->assertEquals( 5.00, $refund->amount );
		$this->assertEquals( 'Requested', $refund->reason );
		$this->assertEquals( $txn, $refund->transaction->get_ID() );
	}

	public function test_object_not_found_if_invalid_id() {

		$txn     = self::transaction_factory()->create();
		$request = \iThemes\Exchange\REST\Request::from_path( "/it_exchange/v1/transactions/{$txn}/refunds/500" );

		$scope = new \iThemes\Exchange\REST\Auth\CustomerAuthScope( it_exchange_get_customer( 1 ) );
		$this->manager->set_auth_scope( $scope );

		$response = $this->server->dispatch( $request );
		$this->assertEquals( 404, $response->get_status() );
	}

	public function test_object_not_found_if_transaction_deleted() {

		/** @var IT_Exchange_Transaction $txn */
		$txn    = self::transaction_factory()->create_and_get();
		$refund = ITE_Refund::create( array( 'transaction' => $txn, 'gateway_id' => 'test-id-1', 'amount' => 5.00 ) );

		$request = \iThemes\Exchange\REST\Request::from_path( "/it_exchange/v1/transactions/{$txn->get_ID()}/refunds/{$refund->ID}" );
		wp_delete_post( $txn->get_ID(), true );
		$txn->delete();

		$scope = new \iThemes\Exchange\REST\Auth\CustomerAuthScope( it_exchange_get_customer( 1 ) );
		$this->manager->set_auth_scope( $scope );

		$response = $this->server->dispatch( $request );
		$this->assertEquals( 404, $response->get_status() );
	}

	public function test_object_not_found_if_id_mismatch() {

		$t1     = self::transaction_factory()->create();
		$t2     = self::transaction_factory()->create();
		$refund = ITE_Refund::create( array( 'transaction' => $t1, 'gateway_id' => 'test-id-1', 'amount' => 5.00 ) );

		$request = \iThemes\Exchange\REST\Request::from_path( "/it_exchange/v1/transactions/{$t2}/refunds/{$refund->ID}" );

		$scope = new \iThemes\Exchange\REST\Auth\CustomerAuthScope( it_exchange_get_customer( 1 ) );
		$this->manager->set_auth_scope( $scope );

		$response = $this->server->dispatch( $request );
		$this->assertEquals( 404, $response->get_status() );
	}

	public function test_get_object() {

		$txn    = self::transaction_factory()->create();
		$refund = ITE_Refund::create( array( 'transaction' => $txn, 'gateway_id' => 'test-id-1', 'amount' => 5.00 ) );

		$request = \iThemes\Exchange\REST\Request::from_path( "/it_exchange/v1/transactions/{$txn}/refunds/{$refund->ID}" );
		$scope   = new \iThemes\Exchange\REST\Auth\CustomerAuthScope( it_exchange_get_customer( 1 ) );
		$this->manager->set_auth_scope( $scope );

		$response = $this->server->dispatch( $request );
		$this->assertEquals( 200, $response->get_status() );
		$this->assertEquals( $refund->ID, $response->data['id'] );
		$this->assertEquals( $refund->reason, $response->data['reason'] );
	}
}