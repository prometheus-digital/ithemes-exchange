<?php
/**
 * Test the customer routes.
 *
 * @since   2.0.0
 * @license GPLv2
 */

/**
 * Class Test_IT_Exchange_v1_Customers_Route
 *
 * @group rest-api
 */
class Test_IT_Exchange_v1_Customers_Route extends Test_IT_Exchange_REST_Route {

	public function test_route_registered() {

		$routes = $this->server->get_routes();

		$this->assertArrayHasKey( '/it_exchange/v1/customers/(?P<customer_id>\d+)', $routes );
	}

	public function test_item_forbidden_for_public() {

		$request = \iThemes\Exchange\REST\Request::from_path( '/it_exchange/v1/customers/1' );

		$scope = new \iThemes\Exchange\REST\Auth\PublicAuthScope();
		$this->manager->set_auth_scope( $scope );

		$response = $this->server->dispatch( $request );
		$this->assertEquals( rest_authorization_required_code(), $response->get_status() );
	}

	public function test_item_forbidden_for_guest() {

		$request = \iThemes\Exchange\REST\Request::from_path( '/it_exchange/v1/customers/1' );

		$scope = new \iThemes\Exchange\REST\Auth\GuestAuthScope( it_exchange_get_customer( 'guest@example.org' ) );
		$this->manager->set_auth_scope( $scope );

		$response = $this->server->dispatch( $request );
		$this->assertEquals( rest_authorization_required_code(), $response->get_status() );
	}

	public function test_item_forbidden_for_different_customer() {

		$request = \iThemes\Exchange\REST\Request::from_path( '/it_exchange/v1/customers/1' );

		$customer = it_exchange_get_customer( self::factory()->user->create( array( 'role' => 'subscriber' ) ) );
		$scope    = new \iThemes\Exchange\REST\Auth\CustomerAuthScope( $customer );
		$this->manager->set_auth_scope( $scope );

		$response = $this->server->dispatch( $request );
		$this->assertEquals( rest_authorization_required_code(), $response->get_status() );
	}

	public function test_item_allow_for_self_customer() {

		$customer = it_exchange_get_customer( self::factory()->user->create( array( 'role' => 'subscriber' ) ) );
		$request  = \iThemes\Exchange\REST\Request::from_path( "/it_exchange/v1/customers/{$customer->get_ID()}" );

		$scope = new \iThemes\Exchange\REST\Auth\CustomerAuthScope( $customer );
		$this->manager->set_auth_scope( $scope );

		$response = $this->server->dispatch( $request );
		$this->assertEquals( 200, $response->get_status() );
	}

	public function test_item_allow_for_administrator() {

		$customer = it_exchange_get_customer( self::factory()->user->create( array( 'role' => 'subscriber' ) ) );
		$request  = \iThemes\Exchange\REST\Request::from_path( "/it_exchange/v1/customers/{$customer->get_ID()}" );

		$scope = new \iThemes\Exchange\REST\Auth\CustomerAuthScope( it_exchange_get_customer( 1 ) );
		$this->manager->set_auth_scope( $scope );

		$response = $this->server->dispatch( $request );
		$this->assertEquals( 200, $response->get_status() );
	}

	public function test_item_edit_context_allow_for_self_customer() {

		$customer = it_exchange_get_customer( self::factory()->user->create( array( 'role' => 'subscriber' ) ) );
		$request  = \iThemes\Exchange\REST\Request::from_path( "/it_exchange/v1/customers/{$customer->get_ID()}" );
		$request->set_param( 'context', 'edit' );

		$scope = new \iThemes\Exchange\REST\Auth\CustomerAuthScope( $customer );
		$this->manager->set_auth_scope( $scope );

		$response = $this->server->dispatch( $request );
		$this->assertEquals( 200, $response->get_status() );
	}

	public function test_item_edit_context_allow_for_administrator() {

		$customer = it_exchange_get_customer( self::factory()->user->create( array( 'role' => 'subscriber' ) ) );
		$request  = \iThemes\Exchange\REST\Request::from_path( "/it_exchange/v1/customers/{$customer->get_ID()}" );
		$request->set_param( 'context', 'edit' );

		$scope = new \iThemes\Exchange\REST\Auth\CustomerAuthScope( it_exchange_get_customer( 1 ) );
		$this->manager->set_auth_scope( $scope );

		$response = $this->server->dispatch( $request );
		$this->assertEquals( 200, $response->get_status() );
	}

	public function test_item_stats_context_frobidden_for_self_customer() {

		$customer = it_exchange_get_customer( self::factory()->user->create( array( 'role' => 'subscriber' ) ) );
		$request  = \iThemes\Exchange\REST\Request::from_path( "/it_exchange/v1/customers/{$customer->get_ID()}" );
		$request->set_param( 'context', 'stats' );

		$scope = new \iThemes\Exchange\REST\Auth\CustomerAuthScope( $customer );
		$this->manager->set_auth_scope( $scope );

		$response = $this->server->dispatch( $request );
		$this->assertEquals( rest_authorization_required_code(), $response->get_status() );
	}

	public function test_item_stats_context_allow_for_administrator() {

		$customer = it_exchange_get_customer( self::factory()->user->create( array( 'role' => 'subscriber' ) ) );
		$request  = \iThemes\Exchange\REST\Request::from_path( "/it_exchange/v1/customers/{$customer->get_ID()}" );
		$request->set_param( 'context', 'stats' );

		$scope = new \iThemes\Exchange\REST\Auth\CustomerAuthScope( it_exchange_get_customer( 1 ) );
		$this->manager->set_auth_scope( $scope );

		$response = $this->server->dispatch( $request );
		$this->assertEquals( 200, $response->get_status() );
	}

	public function test_invalid_item_id_404_for_administrator() {

		$request = \iThemes\Exchange\REST\Request::from_path( '/it_exchange/v1/customers/500' );

		$scope = new \iThemes\Exchange\REST\Auth\CustomerAuthScope( it_exchange_get_customer( 1 ) );
		$this->manager->set_auth_scope( $scope );

		$response = $this->server->dispatch( $request );
		$this->assertEquals( 404, $response->get_status() );
	}

	public function test_invalid_item_id_cannot_read_for_non_administrator() {

		$request = \iThemes\Exchange\REST\Request::from_path( '/it_exchange/v1/customers/500' );

		$customer = it_exchange_get_customer( self::factory()->user->create( array( 'role' => 'subscriber' ) ) );
		$scope    = new \iThemes\Exchange\REST\Auth\CustomerAuthScope( $customer );
		$this->manager->set_auth_scope( $scope );

		$response = $this->server->dispatch( $request );
		$this->assertErrorResponse( 'it_exchange_rest_cannot_view', $response, rest_authorization_required_code() );
	}

	public function test_set_address_by_id() {

		$customer = it_exchange_get_customer( self::factory()->user->create( array( 'role' => 'subscriber' ) ) );

		$address = ITE_Saved_Address::create( array(
			'customer'   => $customer,
			'first-name' => 'John',
			'last-name'  => 'Doe',
			'address1'   => '123 Main Street'
		) );
		$request = \iThemes\Exchange\REST\Request::from_path( "/it_exchange/v1/customers/{$customer->get_ID()}" );
		$request->set_method( 'PUT' );
		$request->add_header( 'content-type', 'application/json' );
		$request->set_body( wp_json_encode( array(
			'billing_address' => $address->get_pk()
		) ) );

		$scope = new \iThemes\Exchange\REST\Auth\CustomerAuthScope( $customer );
		$this->manager->set_auth_scope( $scope );

		$response = $this->server->dispatch( $request );
		$data     = $response->get_data();

		$this->assertEquals( 200, $response->get_status() );
		$this->assertEquals( $address->ID, $data['billing_address'] );
		$this->assertEquals( $address->ID, it_exchange_get_customer( $customer->get_ID() )->get_billing_address( true )->ID );
	}

	public function test_update_address_by_id() {

		$customer = it_exchange_get_customer( self::factory()->user->create( array( 'role' => 'subscriber' ) ) );

		$a1 = ITE_Saved_Address::create( array(
			'customer'   => $customer,
			'first-name' => 'John',
			'last-name'  => 'Doe',
			'address1'   => '123 Main Street'
		) );
		$a2 = ITE_Saved_Address::create( array(
			'customer'   => $customer,
			'first-name' => 'John',
			'last-name'  => 'Doe',
			'address1'   => '321 Main Street'
		) );
		$customer->set_billing_address( $a1 );

		$request = \iThemes\Exchange\REST\Request::from_path( "/it_exchange/v1/customers/{$customer->get_ID()}" );
		$request->set_method( 'PUT' );
		$request->add_header( 'content-type', 'application/json' );
		$request->set_body( wp_json_encode( array(
			'billing_address' => $a2->get_pk()
		) ) );

		$scope = new \iThemes\Exchange\REST\Auth\CustomerAuthScope( $customer );
		$this->manager->set_auth_scope( $scope );

		$response = $this->server->dispatch( $request );
		$data     = $response->get_data();

		$this->assertEquals( 200, $response->get_status() );
		$this->assertEquals( $a2->ID, $data['billing_address'] );
		$this->assertEquals( $a2->ID, it_exchange_get_customer( $customer->get_ID() )->get_billing_address( true )->ID );
	}

	public function test_set_address_by_array() {

		$customer = it_exchange_get_customer( self::factory()->user->create( array( 'role' => 'subscriber' ) ) );

		$request = \iThemes\Exchange\REST\Request::from_path( "/it_exchange/v1/customers/{$customer->get_ID()}" );
		$request->set_method( 'PUT' );
		$request->add_header( 'content-type', 'application/json' );
		$request->set_body( wp_json_encode( array(
			'billing_address' => array(
				'customer'   => $customer,
				'first-name' => 'John',
				'last-name'  => 'Doe',
				'address1'   => '123 Main Street'
			)
		) ) );

		$scope = new \iThemes\Exchange\REST\Auth\CustomerAuthScope( $customer );
		$this->manager->set_auth_scope( $scope );

		$response = $this->server->dispatch( $request );
		$data     = $response->get_data();

		$this->assertEquals( 200, $response->get_status() );
		$this->assertNotEmpty( $data['billing_address'] );

		$address = ITE_Saved_Address::get( $data['billing_address'] );

		$this->assertEquals( 'John', $address['first-name'] );
		$this->assertEquals( 'Doe', $address['last-name'] );
		$this->assertEquals( '123 Main Street', $address['address1'] );
	}
}