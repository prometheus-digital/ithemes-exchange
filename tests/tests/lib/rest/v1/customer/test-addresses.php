<?php
/**
 * Test the address routes.
 *
 * @since   2.0.0
 * @license GPLv2
 */

/**
 * Class Test_IT_Exchange_v1_Customer_Addresses_Route
 *
 * @group rest-api
 */
class Test_IT_Exchange_v1_Customer_Addresses_Route extends Test_IT_Exchange_REST_Route {

	public function test_route_registered() {

		$routes = $this->server->get_routes();

		$this->assertArrayHasKey( '/it_exchange/v1/customers/(?P<customer_id>\d+)/addresses', $routes );
		$this->assertArrayHasKey( '/it_exchange/v1/customers/(?P<customer_id>\d+)/addresses/(?P<address_id>\d+)', $routes );
	}

	public function test_collection_forbidden_for_public() {

		$customer = it_exchange_get_customer( self::factory()->user->create( array( 'role' => 'subscriber' ) ) );
		$request  = \iThemes\Exchange\REST\Request::from_path( "/it_exchange/v1/customers/{$customer->get_ID()}/addresses" );

		$scope = new \iThemes\Exchange\REST\Auth\PublicAuthScope();
		$this->manager->set_auth_scope( $scope );

		$response = $this->server->dispatch( $request );
		$this->assertEquals( rest_authorization_required_code(), $response->get_status() );
	}

	public function test_collection_forbidden_for_guest() {

		$customer = it_exchange_get_customer( self::factory()->user->create( array( 'role' => 'subscriber' ) ) );
		$request  = \iThemes\Exchange\REST\Request::from_path( "/it_exchange/v1/customers/{$customer->get_ID()}/addresses" );

		$scope = new \iThemes\Exchange\REST\Auth\GuestAuthScope( it_exchange_get_customer( 'guest@example.org' ) );
		$this->manager->set_auth_scope( $scope );

		$response = $this->server->dispatch( $request );
		$this->assertEquals( rest_authorization_required_code(), $response->get_status() );
	}

	public function test_collection_forbidden_for_different_customer() {

		$customer = it_exchange_get_customer( self::factory()->user->create( array( 'role' => 'subscriber' ) ) );
		$request  = \iThemes\Exchange\REST\Request::from_path( '/it_exchange/v1/customers/1/addresses' );

		$scope = new \iThemes\Exchange\REST\Auth\CustomerAuthScope( $customer );
		$this->manager->set_auth_scope( $scope );

		$response = $this->server->dispatch( $request );
		$this->assertEquals( rest_authorization_required_code(), $response->get_status() );
	}

	public function test_collection_allowed_for_same_customer() {

		$customer = it_exchange_get_customer( self::factory()->user->create( array( 'role' => 'subscriber' ) ) );
		$request  = \iThemes\Exchange\REST\Request::from_path( "/it_exchange/v1/customers/{$customer->get_ID()}/addresses" );

		$scope = new \iThemes\Exchange\REST\Auth\CustomerAuthScope( $customer );
		$this->manager->set_auth_scope( $scope );

		$response = $this->server->dispatch( $request );
		$this->assertEquals( 200, $response->get_status() );
	}

	public function test_collection_allowed_for_administrator() {

		$customer = it_exchange_get_customer( self::factory()->user->create( array( 'role' => 'subscriber' ) ) );
		$request  = \iThemes\Exchange\REST\Request::from_path( "/it_exchange/v1/customers/{$customer->get_ID()}/addresses" );

		$scope = new \iThemes\Exchange\REST\Auth\CustomerAuthScope( it_exchange_get_customer( 1 ) );
		$this->manager->set_auth_scope( $scope );

		$response = $this->server->dispatch( $request );
		$this->assertEquals( 200, $response->get_status() );
	}

	public function test_collection() {

		$customer = it_exchange_get_customer( self::factory()->user->create( array( 'role' => 'subscriber' ) ) );
		$customer->set_billing_address( new ITE_In_Memory_Address( array(
			'first-name' => 'Joe',
			'last-name'  => 'Doe',
			'address1'   => '123 Main Street',
		) ) );
		$customer->set_shipping_address( new ITE_In_Memory_Address( array(
			'first-name' => 'Joe',
			'last-name'  => 'Doe',
			'address1'   => '321 Main Street',
		) ) );

		$request = \iThemes\Exchange\REST\Request::from_path( "/it_exchange/v1/customers/{$customer->get_ID()}/addresses" );

		$auth = new \iThemes\Exchange\REST\Auth\CustomerAuthScope( $customer );
		$this->manager->set_auth_scope( $auth );

		$response = $this->server->dispatch( $request );
		$this->assertEquals( 200, $response->get_status() );
		$this->assertCount( 2, $response->get_data() );
		$this->assertEquals( '123 Main Street', $response->data[0]['address1'] );
		$this->assertEquals( '321 Main Street', $response->data[1]['address1'] );
	}

	public function test_create_duplicate() {

		$customer = it_exchange_get_customer( self::factory()->user->create( array( 'role' => 'subscriber' ) ) );
		$data     = array(
			'first-name' => 'Joe',
			'last-name'  => 'Doe',
			'address1'   => '123 Main Street',
		);
		$customer->set_billing_address( new ITE_In_Memory_Address( $data ) );
		$address = $customer->get_billing_address( true );

		$request = \iThemes\Exchange\REST\Request::from_path( "/it_exchange/v1/customers/{$customer->get_ID()}/addresses" );
		$request->set_method( 'POST' );
		$request->add_header( 'content-type', 'application/json' );
		$request->set_body( wp_json_encode( $data ) );

		$auth = new \iThemes\Exchange\REST\Auth\CustomerAuthScope( $customer );
		$this->manager->set_auth_scope( $auth );

		$response = $this->server->dispatch( $request );
		$headers  = $response->get_headers();
		$this->assertEquals( WP_Http::SEE_OTHER, $response->get_status() );
		$this->assertEquals( rest_url( "/it_exchange/v1/customers/{$customer->get_ID()}/addresses/{$address->ID}" ), $headers['Location'] );

		$this->assertCount( 1, $customer->get_addresses() );
	}

	public function test_create_new() {

		$customer = it_exchange_get_customer( self::factory()->user->create( array( 'role' => 'subscriber' ) ) );
		$data     = array(
			'first-name' => 'Joe',
			'last-name'  => 'Doe',
			'address1'   => '123 Main Street',
		);

		$request = \iThemes\Exchange\REST\Request::from_path( "/it_exchange/v1/customers/{$customer->get_ID()}/addresses" );
		$request->set_method( 'POST' );
		$request->add_header( 'content-type', 'application/json' );
		$request->set_body( wp_json_encode( $data ) );

		$auth = new \iThemes\Exchange\REST\Auth\CustomerAuthScope( $customer );
		$this->manager->set_auth_scope( $auth );

		$response = $this->server->dispatch( $request );
		$data     = $response->get_data();
		$this->assertEquals( WP_Http::CREATED, $response->get_status() );
		$this->assertNotEmpty( $data['id'] );
		$this->assertEquals( '123 Main Street', $data['address1'] );

		$address = ITE_Saved_Address::get( $data['id'] );
		$this->assertNotNull( $address->customer );
		$this->assertEquals( $customer->get_ID(), $address->customer->get_ID() );
		$this->assertEquals( '123 Main Street', $address['address1'] );
		$this->assertCount( 1, $customer->get_addresses() );
	}

	public function test_create_new_if_different_customer_has_same_address() {

		$c1 = it_exchange_get_customer( self::factory()->user->create( array( 'role' => 'subscriber' ) ) );
		$c2 = it_exchange_get_customer( self::factory()->user->create( array( 'role' => 'subscriber' ) ) );

		$data = array(
			'first-name' => 'Joe',
			'last-name'  => 'Doe',
			'address1'   => '123 Main Street',
		);

		$c2->set_billing_address( new ITE_In_Memory_Address( $data ) );

		$request = \iThemes\Exchange\REST\Request::from_path( "/it_exchange/v1/customers/{$c1->get_ID()}/addresses" );
		$request->set_method( 'POST' );
		$request->add_header( 'content-type', 'application/json' );
		$request->set_body( wp_json_encode( $data ) );

		$auth = new \iThemes\Exchange\REST\Auth\CustomerAuthScope( $c1 );
		$this->manager->set_auth_scope( $auth );

		$response = $this->server->dispatch( $request );
		$data     = $response->get_data();
		$this->assertEquals( WP_Http::CREATED, $response->get_status() );
		$this->assertNotEmpty( $data['id'] );
		$this->assertEquals( '123 Main Street', $data['address1'] );

		$address = ITE_Saved_Address::get( $data['id'] );
		$this->assertNotNull( $address->customer );
		$this->assertEquals( $c1->get_ID(), $address->customer->get_ID() );
		$this->assertEquals( '123 Main Street', $address['address1'] );
		$this->assertCount( 1, $c1->get_addresses() );

		$c2_address = $c2->get_billing_address( true );
		$this->assertNotEquals( $address->ID, $c2_address->ID );
	}

	public function test_object_not_found_if_invalid_id() {

		$customer = it_exchange_get_customer( self::factory()->user->create( array( 'role' => 'subscriber' ) ) );
		$request  = \iThemes\Exchange\REST\Request::from_path( "/it_exchange/v1/customers/{$customer->get_ID()}/addresses/500" );

		$scope = new \iThemes\Exchange\REST\Auth\CustomerAuthScope( $customer );
		$this->manager->set_auth_scope( $scope );

		$response = $this->server->dispatch( $request );
		$this->assertEquals( 404, $response->get_status() );
	}

	public function test_object_not_found_if_customer_deleted() {

		$customer = it_exchange_get_customer( self::factory()->user->create( array( 'role' => 'subscriber' ) ) );
		$address  = $customer->set_billing_address( new ITE_In_Memory_Address( array(
			'first-name' => 'Joe',
			'last-name'  => 'Doe',
			'address1'   => '123 Main Street'
		) ) );
		$request  = \iThemes\Exchange\REST\Request::from_path(
			"/it_exchange/v1/customers/{$customer->get_ID()}/addresses/{$address->ID}"
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
		$address = $c1->set_billing_address( new ITE_In_Memory_Address( array(
			'first-name' => 'Joe',
			'last-name'  => 'Doe',
			'address1'   => '123 Main Street'
		) ) );
		$request = \iThemes\Exchange\REST\Request::from_path(
			"/it_exchange/v1/customers/{$c2->get_ID()}/addresses/{$address->ID}"
		);

		$scope = new \iThemes\Exchange\REST\Auth\CustomerAuthScope( $c1 );
		$this->manager->set_auth_scope( $scope );

		$response = $this->server->dispatch( $request );
		$this->assertEquals( 404, $response->get_status() );
	}

	public function test_get_object() {

		$customer = it_exchange_get_customer( self::factory()->user->create( array( 'role' => 'subscriber' ) ) );
		$address  = $customer->set_billing_address( new ITE_In_Memory_Address( array(
			'first-name' => 'Joe',
			'last-name'  => 'Doe',
			'address1'   => '123 Main Street',
		) ) );

		$request = \iThemes\Exchange\REST\Request::from_path(
			"/it_exchange/v1/customers/{$customer->get_ID()}/addresses/{$address->ID}"
		);

		$auth = new \iThemes\Exchange\REST\Auth\CustomerAuthScope( $customer );
		$this->manager->set_auth_scope( $auth );

		$response = $this->server->dispatch( $request );
		$this->assertEquals( 200, $response->get_status() );
		$this->assertEquals( $address->ID, $response->data['id'] );
		$this->assertEquals( '123 Main Street', $response->data['address1'] );
	}

	public function test_delete_object() {

		$customer = it_exchange_get_customer( self::factory()->user->create( array( 'role' => 'subscriber' ) ) );
		$address  = $customer->set_billing_address( new ITE_In_Memory_Address( array(
			'first-name' => 'Joe',
			'last-name'  => 'Doe',
			'address1'   => '123 Main Street',
		) ) );

		$request = \iThemes\Exchange\REST\Request::from_path(
			"/it_exchange/v1/customers/{$customer->get_ID()}/addresses/{$address->ID}"
		);
		$request->set_method( 'DELETE' );

		$auth = new \iThemes\Exchange\REST\Auth\CustomerAuthScope( $customer );
		$this->manager->set_auth_scope( $auth );

		$response = $this->server->dispatch( $request );
		$this->assertEquals( WP_Http::NO_CONTENT, $response->get_status() );

		$address = ITE_Saved_Address::get( $address->ID );
		$this->assertNotNull( $address );
		$this->assertEquals( time(), $address->deleted_at->getTimestamp(), '', 2 );

		$request  = \iThemes\Exchange\REST\Request::from_path( "/it_exchange/v1/customers/{$customer->get_ID()}/addresses" );
		$response = $this->server->dispatch( $request );

		$this->assertEquals( 200, $response->get_status() );
		$this->assertCount( 0, $response->get_data() );
	}

	public function test_update_label() {

		$customer = it_exchange_get_customer( self::factory()->user->create( array( 'role' => 'subscriber' ) ) );
		$address  = $customer->set_billing_address( new ITE_In_Memory_Address( array(
			'first-name' => 'Joe',
			'last-name'  => 'Doe',
			'address1'   => '123 Main Street',
		) ) );

		$request = \iThemes\Exchange\REST\Request::from_path(
			"/it_exchange/v1/customers/{$customer->get_ID()}/addresses/{$address->ID}"
		);
		$request->set_method( 'PUT' );
		$request->add_header( 'content-type', 'application/json' );
		$request->set_body( wp_json_encode( array(
			'label' => 'My Address',
		) ) );

		$scope = new \iThemes\Exchange\REST\Auth\CustomerAuthScope( $customer );
		$this->manager->set_auth_scope( $scope );
		$response = $this->server->dispatch( $request );
		$data     = $response->get_data();

		$this->assertEquals( 200, $response->get_status() );
		$this->assertEquals( $address->ID, $data['id'] );
		$this->assertEquals( 'My Address', $data['label'] );

		$this->assertEquals( 'My Address', ITE_Saved_Address::get( $data['id'] )->label );
	}

	public function test_update() {

		$customer = it_exchange_get_customer( self::factory()->user->create( array( 'role' => 'subscriber' ) ) );
		$address  = $customer->set_billing_address( new ITE_In_Memory_Address( array(
			'first-name' => 'Joe',
			'last-name'  => 'Doe',
			'address1'   => '123 Main Street',
		) ) );

		$request = \iThemes\Exchange\REST\Request::from_path(
			"/it_exchange/v1/customers/{$customer->get_ID()}/addresses/{$address->ID}"
		);
		$request->set_method( 'PUT' );
		$request->add_header( 'content-type', 'application/json' );
		$request->set_body( wp_json_encode( array(
			'address2' => 'Apartment 5',
		) ) );

		$scope = new \iThemes\Exchange\REST\Auth\CustomerAuthScope( $customer );
		$this->manager->set_auth_scope( $scope );
		$response = $this->server->dispatch( $request );
		$data     = $response->get_data();

		$this->assertEquals( 200, $response->get_status() );
		$this->assertEquals( $address->ID, $data['id'] );
		$this->assertEquals( 'Apartment 5', $data['address2'] );

		$this->assertEquals( 'Apartment 5', ITE_Saved_Address::get( $data['id'] )->address2 );
	}

	public function test_update_creates_new_address_if_current_address_used() {

		$customer = it_exchange_get_customer( self::factory()->user->create( array( 'role' => 'subscriber' ) ) );
		$address  = $customer->set_billing_address( new ITE_In_Memory_Address( array(
			'first-name' => 'Joe',
			'last-name'  => 'Doe',
			'address1'   => '123 Main Street',
		) ) );

		self::transaction_factory()->create( array( 'customer' => $customer, 'billing_address' => $address ) );

		$request = \iThemes\Exchange\REST\Request::from_path(
			"/it_exchange/v1/customers/{$customer->get_ID()}/addresses/{$address->ID}"
		);
		$request->set_method( 'PUT' );
		$request->add_header( 'content-type', 'application/json' );
		$request->set_body( wp_json_encode( array(
			'address2' => 'Apartment 5',
		) ) );

		$scope = new \iThemes\Exchange\REST\Auth\CustomerAuthScope( $customer );
		$this->manager->set_auth_scope( $scope );
		$response = $this->server->dispatch( $request );
		$headers  = $response->get_headers();

		$customer           = it_exchange_get_customer( $customer->get_ID() );
		$original_refreshed = ITE_Saved_Address::get( $address->ID );

		$this->assertCount( 1, $customer->get_addresses() );
		$this->assertEmpty( $original_refreshed['address2'] );
		$this->assertEquals( time(), $original_refreshed->deleted_at->getTimestamp(), '', 3 );

		$this->assertEquals( WP_Http::SEE_OTHER, $response->get_status() );
		$this->assertEquals( 'Apartment 5', $customer->get_billing_address()->offsetGet( 'address2' ) );
		$this->assertEquals( rest_url(
			"/it_exchange/v1/customers/{$customer->get_ID()}/addresses/{$customer->get_billing_address( true )->ID}" ),
			$headers['Location']
		);
	}
}