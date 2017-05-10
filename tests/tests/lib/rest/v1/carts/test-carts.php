<?php
/**
 * Test the cart routes.
 *
 * @since   2.0.0
 * @license GPLv2
 */

/**
 * Class Test_IT_Exchange_v1_Carts_Route
 *
 * @group rest-api
 */
class Test_IT_Exchange_v1_Carts_Route extends Test_IT_Exchange_REST_Route {

	protected $meta_keys = array();

	/**
	 * @inheritDoc
	 */
	public function tearDown() {
		parent::tearDown();

		foreach ( $this->meta_keys as $key ) {
			ITE_Cart_Meta_Registry::remove( $key );
		}
	}

	public function test_route_registered() {

		$routes = $this->server->get_routes();

		$this->assertArrayHasKey( '/it_exchange/v1/carts', $routes );
		$this->assertArrayHasKey( '/it_exchange/v1/carts/(?P<cart_id>\w+)', $routes );
	}

	public function test_create_forbidden_for_public() {

		$request = \iThemes\Exchange\REST\Request::from_path( '/it_exchange/v1/carts' );
		$request->set_method( 'POST' );

		$this->manager->set_auth_scope( new \iThemes\Exchange\REST\Auth\PublicAuthScope() );

		$response = $this->server->dispatch( $request );
		$this->assertEquals( rest_authorization_required_code(), $response->get_status() );
	}

	public function test_create_for_guest() {

		$request = \iThemes\Exchange\REST\Request::from_path( '/it_exchange/v1/carts' );
		$request->set_method( 'POST' );

		$customer = it_exchange_get_customer( 'guest@example.org' );
		$scope    = new \iThemes\Exchange\REST\Auth\GuestAuthScope( $customer );
		$this->manager->set_auth_scope( $scope );
		add_filter( 'it_exchange_get_current_customer', function () use ( $customer ) { return $customer; } );

		$response = $this->server->dispatch( $request );
		$data     = $response->get_data();
		$headers  = $response->get_headers();
		$this->assertEquals( WP_Http::CREATED, $response->get_status() );
		$this->assertNotEmpty( $data['id'] );
		$this->assertArrayHasKey( 'Location', $headers );
		$this->assertEquals( rest_url( "/it_exchange/v1/carts/{$data['id']}" ), $headers['Location'] );

		$cart = it_exchange_get_cart( $data['id'] );
		$this->assertNotNull( $cart );
		$this->assertTrue( $cart->is_guest() );
		$this->assertTrue( $cart->is_main() );
		$this->assertEquals( 'guest@example.org', $cart->get_customer()->get_email() );
	}

	public function test_create_main() {

		$customer = it_exchange_get_customer( 1 );
		wp_set_current_user( 1 );

		$request = \iThemes\Exchange\REST\Request::from_path( '/it_exchange/v1/carts' );
		$request->set_method( 'POST' );

		$scope = new \iThemes\Exchange\REST\Auth\CustomerAuthScope( $customer );
		$this->manager->set_auth_scope( $scope );

		$response = $this->server->dispatch( $request );
		$data     = $response->get_data();
		$headers  = $response->get_headers();
		$this->assertEquals( WP_Http::CREATED, $response->get_status() );
		$this->assertNotEmpty( $data['id'] );
		$this->assertArrayHasKey( 'Location', $headers );
		$this->assertEquals( rest_url( "/it_exchange/v1/carts/{$data['id']}" ), $headers['Location'] );

		$cart = it_exchange_get_cart( $data['id'] );
		$this->assertNotNull( $cart );
		$this->assertTrue( $cart->is_main() );
		$this->assertEquals( 1, $cart->get_customer()->get_ID() );
	}

	public function test_create_main_reuses_existing_cart() {

		$customer = it_exchange_get_customer( 1 );
		wp_set_current_user( 1 );

		$cart = it_exchange_create_cart_and_session( $customer );

		$request = \iThemes\Exchange\REST\Request::from_path( '/it_exchange/v1/carts' );
		$request->set_method( 'POST' );

		$scope = new \iThemes\Exchange\REST\Auth\CustomerAuthScope( $customer );
		$this->manager->set_auth_scope( $scope );

		$response = $this->server->dispatch( $request );
		$headers  = $response->get_headers();
		$this->assertEquals( WP_Http::SEE_OTHER, $response->get_status() );
		$this->assertArrayHasKey( 'Location', $headers );
		$this->assertEquals( rest_url( "/it_exchange/v1/carts/{$cart->get_id()}" ), $headers['Location'] );

		$this->assertTrue( $cart->is_main() );
		$this->assertEquals( 1, $cart->get_customer()->get_ID() );
	}

	public function test_create_main_reuses_existing_cart_and_sets_expires_at() {

		$customer = it_exchange_get_customer( 1 );
		wp_set_current_user( 1 );

		$expires_at     = new DateTime( '+ 1 day' );
		$cart           = it_exchange_create_cart_and_session( $customer, true, $expires_at );
		$new_expires_at = new DateTime( '+ 1 hour' );

		$request = \iThemes\Exchange\REST\Request::from_path( '/it_exchange/v1/carts' );
		$request->set_method( 'POST' );
		$request->add_header( 'content-type', 'application/json' );
		$request->set_body( wp_json_encode( array(
			'expires_at' => $new_expires_at->format( DateTime::ATOM )
		) ) );

		$scope = new \iThemes\Exchange\REST\Auth\CustomerAuthScope( $customer );
		$this->manager->set_auth_scope( $scope );

		$response = $this->server->dispatch( $request );
		$headers  = $response->get_headers();
		$this->assertEquals( WP_Http::SEE_OTHER, $response->get_status() );
		$this->assertArrayHasKey( 'Location', $headers );
		$this->assertEquals( rest_url( "/it_exchange/v1/carts/{$cart->get_id()}" ), $headers['Location'] );

		$this->assertTrue( $cart->is_main() );
		$this->assertEquals( 1, $cart->get_customer()->get_ID() );

		$session = ITE_Session_Model::from_cart_id( $cart->get_id() );
		$this->assertNotNull( $session );
		$this->assertEquals( $new_expires_at->getTimestamp(), $session->expires_at->getTimestamp(), '', 1 );
	}

	public function test_create_non_main() {

		$customer = it_exchange_get_customer( 1 );
		wp_set_current_user( 1 );

		$request = \iThemes\Exchange\REST\Request::from_path( '/it_exchange/v1/carts' );
		$request->set_method( 'POST' );
		$request->add_header( 'content-type', 'application/json' );
		$request->set_body( wp_json_encode( array(
			'is_main' => false,
		) ) );

		$scope = new \iThemes\Exchange\REST\Auth\CustomerAuthScope( $customer );
		$this->manager->set_auth_scope( $scope );

		$response = $this->server->dispatch( $request );
		$data     = $response->get_data();
		$headers  = $response->get_headers();
		$this->assertEquals( WP_Http::CREATED, $response->get_status() );
		$this->assertNotEmpty( $data['id'] );
		$this->assertArrayHasKey( 'Location', $headers );
		$this->assertEquals( rest_url( "/it_exchange/v1/carts/{$data['id']}" ), $headers['Location'] );

		$cart = it_exchange_get_cart( $data['id'] );
		$this->assertNotNull( $cart );
		$this->assertFalse( $cart->is_main() );
		$this->assertEquals( 1, $cart->get_customer()->get_ID() );
	}

	public function test_not_found() {

		$request = \iThemes\Exchange\REST\Request::from_path( '/it_exchange/v1/carts/garbage' );

		$scope = new \iThemes\Exchange\REST\Auth\CustomerAuthScope( it_exchange_get_customer( 1 ) );
		$this->manager->set_auth_scope( $scope );

		$this->assertErrorResponse( 'it_exchange_rest_not_found', $this->server->dispatch( $request ), 404 );
	}

	public function test_get_object() {

		$this->register_meta();

		$product  = self::product_factory()->create_and_get( array(
			'type'     => 'physical-product-type',
			'shipping' => true
		) );
		$customer = it_exchange_get_customer( 1 );
		$cart     = it_exchange_create_cart_and_session( $customer );
		$cart->add_item( ITE_Cart_Product::create( $product ) );

		$billing  = array(
			'first-name' => 'John',
			'last-name'  => 'Doe',
			'address1'   => '321 Main Street',
		);
		$shipping = array(
			'first-name' => 'John',
			'last-name'  => 'Doe',
			'address1'   => '123 Main Street',
		);

		$cart->set_billing_address( new ITE_In_Memory_Address( $billing ) );
		$cart->set_shipping_address( new ITE_In_Memory_Address( $shipping ) );
		$this->assertNotNull( $cart->get_shipping_address() );

		$cart->set_meta( '_hidden', 'Ghost' );
		$cart->set_meta( '_visible', 'See me' );
		$cart->set_meta( '_editable', 'Edit me' );

		$request = \iThemes\Exchange\REST\Request::from_path( "/it_exchange/v1/carts/{$cart->get_id()}" );

		$scope = new \iThemes\Exchange\REST\Auth\CustomerAuthScope( $customer );
		$this->manager->set_auth_scope( $scope );

		$response = $this->server->dispatch( $request );
		$data     = $response->get_data();
		$this->assertEquals( 200, $response->get_status() );
		$this->assertTrue( $data['is_main'] );
		$this->assertArrayHasKey( 'total', $data );
		$this->assertArrayHasKey( 'subtotal', $data );
		$this->assertEquals( $cart->get_total(), $data['total'] );
		$this->assertEquals( $cart->get_subtotal(), $data['subtotal'] );

		$this->assertArrayHasKey( 'items', $data );
		$this->assertCount( 1, $data['items'] );

		$this->assertArrayHasKey( 'billing_address', $data );
		$this->assertArrayHasKey( 'shipping_address', $data );
		$this->assertInternalType( 'array', $data['billing_address'] );
		$this->assertInternalType( 'array', $data['shipping_address'] );
		$this->assertEquals( '321 Main Street', $data['billing_address']['address1'] );
		$this->assertEquals( '123 Main Street', $data['shipping_address']['address1'] );

		$this->assertArrayHasKey( 'expires_at', $data );
		$this->assertEquals( $cart->expires_at()->getTimestamp(), strtotime( $data['expires_at'] ) );

		$this->assertArrayNotHasKey( '_hidden', $data['meta'] );
		$this->assertArrayHasKey( '_visible', $data['meta'] );
		$this->assertArrayHasKey( '_editable', $data['meta'] );

		$this->assertEquals( 'See me', $data['meta']['_visible'] );
		$this->assertEquals( 'Edit me', $data['meta']['_editable'] );
	}

	public function test_get_object_for_guest() {

		$customer = it_exchange_get_customer( 'guest@example.org' );
		$cart     = it_exchange_create_cart_and_session( $customer );

		$request = \iThemes\Exchange\REST\Request::from_path( "/it_exchange/v1/carts/{$cart->get_id()}" );

		$scope = new \iThemes\Exchange\REST\Auth\GuestAuthScope( $customer );
		$this->manager->set_auth_scope( $scope );

		$response = $this->server->dispatch( $request );
		$this->assertEquals( 200, $response->get_status() );
		$this->assertNotEmpty( $response->get_data() );

		$data = $response->get_data();
		$this->assertEquals( 'guest@example.org', $data['customer'] );
	}

	public function test_update_cart_address_from_array() {

		$product  = self::product_factory()->create_and_get( array(
			'type'     => 'physical-product-type',
			'shipping' => true
		) );
		$customer = it_exchange_get_customer( 1 );
		$cart     = it_exchange_create_cart_and_session( $customer );
		$cart->add_item( ITE_Cart_Product::create( $product ) );

		$billing  = array(
			'first-name' => 'John',
			'last-name'  => 'Doe',
			'address1'   => '321 Main Street',
		);
		$shipping = array(
			'first-name' => 'John',
			'last-name'  => 'Doe',
			'address1'   => '123 Main Street',
		);

		$request = \iThemes\Exchange\REST\Request::from_path( "/it_exchange/v1/carts/{$cart->get_id()}" );
		$request->set_method( 'PUT' );
		$request->add_header( 'content-type', 'application/json' );
		$request->set_body( wp_json_encode( array(
			'billing_address'  => $billing,
			'shipping_address' => $shipping,
		) ) );

		$scope = new \iThemes\Exchange\REST\Auth\CustomerAuthScope( $customer );
		$this->manager->set_auth_scope( $scope );

		$response = $this->server->dispatch( $request );
		$data     = $response->get_data();

		$this->assertArrayHasKey( 'billing_address', $data );
		$this->assertArrayHasKey( 'shipping_address', $data );
		$this->assertInternalType( 'array', $data['billing_address'] );
		$this->assertInternalType( 'array', $data['shipping_address'] );
		$this->assertEquals( '321 Main Street', $data['billing_address']['address1'] );
		$this->assertEquals( '123 Main Street', $data['shipping_address']['address1'] );
	}

	public function test_update_cart_address_from_id() {

		$product  = self::product_factory()->create_and_get( array(
			'type'     => 'physical-product-type',
			'shipping' => true
		) );
		$customer = it_exchange_get_customer( 1 );
		$cart     = it_exchange_create_cart_and_session( $customer );
		$cart->add_item( ITE_Cart_Product::create( $product ) );

		$billing  = ITE_Saved_Address::create( array(
			'first-name' => 'John',
			'last-name'  => 'Doe',
			'address1'   => '321 Main Street',
			'customer'   => $customer,
		) );
		$shipping = ITE_Saved_Address::create( array(
			'first-name' => 'John',
			'last-name'  => 'Doe',
			'address1'   => '123 Main Street',
			'customer'   => $customer,
		) );

		$request = \iThemes\Exchange\REST\Request::from_path( "/it_exchange/v1/carts/{$cart->get_id()}" );
		$request->set_method( 'PUT' );
		$request->add_header( 'content-type', 'application/json' );
		$request->set_body( wp_json_encode( array(
			'billing_address'  => $billing->ID,
			'shipping_address' => $shipping->ID,
		) ) );

		$scope = new \iThemes\Exchange\REST\Auth\CustomerAuthScope( $customer );
		$this->manager->set_auth_scope( $scope );

		$response = $this->server->dispatch( $request );
		$data     = $response->get_data();

		$this->assertArrayHasKey( 'billing_address', $data );
		$this->assertArrayHasKey( 'shipping_address', $data );
		$this->assertInternalType( 'array', $data['billing_address'] );
		$this->assertInternalType( 'array', $data['shipping_address'] );
		$this->assertEquals( '321 Main Street', $data['billing_address']['address1'] );
		$this->assertEquals( '123 Main Street', $data['shipping_address']['address1'] );
	}

	public function test_create_meta() {

		$this->register_meta();

		$product  = self::product_factory()->create_and_get();
		$customer = it_exchange_get_customer( 1 );
		$cart     = it_exchange_create_cart_and_session( $customer );
		$cart->add_item( ITE_Cart_Product::create( $product ) );

		$request = \iThemes\Exchange\REST\Request::from_path( "/it_exchange/v1/carts/{$cart->get_id()}" );
		$request->set_method( 'PUT' );
		$request->add_header( 'content-type', 'application/json' );
		$request->set_body( wp_json_encode( array(
			'meta' => array(
				'_editable' => 'Hello'
			),
		) ) );

		$scope = new \iThemes\Exchange\REST\Auth\CustomerAuthScope( $customer );
		$this->manager->set_auth_scope( $scope );

		$response = $this->server->dispatch( $request );
		$this->assertEquals( 200, $response->get_status() );
		$data = $response->get_data();
		$cart = it_exchange_get_cart( $cart->get_id() );

		$this->assertArrayHasKey( '_editable', $data['meta'] );
		$this->assertTrue( $cart->has_meta( '_editable' ) );
		$this->assertEquals( 'Hello', $cart->get_meta( '_editable' ) );
	}

	public function test_update_meta() {

		$this->register_meta();

		$product  = self::product_factory()->create_and_get();
		$customer = it_exchange_get_customer( 1 );
		$cart     = it_exchange_create_cart_and_session( $customer );
		$cart->add_item( ITE_Cart_Product::create( $product ) );
		$cart->set_meta( '_editable', 'Hello' );

		$request = \iThemes\Exchange\REST\Request::from_path( "/it_exchange/v1/carts/{$cart->get_id()}" );
		$request->set_method( 'PUT' );
		$request->add_header( 'content-type', 'application/json' );
		$request->set_body( wp_json_encode( array(
			'meta' => array(
				'_editable' => 'There'
			),
		) ) );

		$scope = new \iThemes\Exchange\REST\Auth\CustomerAuthScope( $customer );
		$this->manager->set_auth_scope( $scope );

		$response = $this->server->dispatch( $request );
		$this->assertEquals( 200, $response->get_status() );
		$data = $response->get_data();
		$cart = it_exchange_get_cart( $cart->get_id() );

		$this->assertArrayHasKey( '_editable', $data['meta'] );
		$this->assertTrue( $cart->has_meta( '_editable' ) );
		$this->assertEquals( 'There', $cart->get_meta( '_editable' ) );
	}

	public function test_delete_meta() {

		$this->register_meta();

		$product  = self::product_factory()->create_and_get();
		$customer = it_exchange_get_customer( 1 );
		$cart     = it_exchange_create_cart_and_session( $customer );
		$cart->add_item( ITE_Cart_Product::create( $product ) );
		$cart->set_meta( '_editable', 'Hello' );

		$request = \iThemes\Exchange\REST\Request::from_path( "/it_exchange/v1/carts/{$cart->get_id()}" );
		$request->set_method( 'PUT' );
		$request->add_header( 'content-type', 'application/json' );
		$request->set_body( wp_json_encode( array(
			'meta' => array(
				'_editable' => null
			),
		) ) );

		$scope = new \iThemes\Exchange\REST\Auth\CustomerAuthScope( $customer );
		$this->manager->set_auth_scope( $scope );

		$response = $this->server->dispatch( $request );
		$this->assertEquals( 200, $response->get_status() );
		$data = $response->get_data();
		$cart = it_exchange_get_cart( $cart->get_id() );

		$this->assertArrayNotHasKey( '_editable', (array) $data['meta'] );
		$this->assertFalse( $cart->has_meta( '_editable' ) );
	}

	public function test_cannot_update_non_editable() {

		$this->register_meta();

		$product  = self::product_factory()->create_and_get();
		$customer = it_exchange_get_customer( 1 );
		$cart     = it_exchange_create_cart_and_session( $customer );
		$cart->add_item( ITE_Cart_Product::create( $product ) );

		$request = \iThemes\Exchange\REST\Request::from_path( "/it_exchange/v1/carts/{$cart->get_id()}" );
		$request->set_method( 'PUT' );
		$request->add_header( 'content-type', 'application/json' );
		$request->set_body( wp_json_encode( array(
			'meta' => array(
				'_visible' => 'Hello'
			),
		) ) );

		$scope = new \iThemes\Exchange\REST\Auth\CustomerAuthScope( $customer );
		$this->manager->set_auth_scope( $scope );

		$response = $this->server->dispatch( $request );
		$data     = $response->get_data();
		$cart     = it_exchange_get_cart( $cart->get_id() );

		$this->assertArrayNotHasKey( '_visible', (array) $data['meta'] );
		$this->assertFalse( $cart->has_meta( '_visible' ) );
	}

	public function test_invalid_meta_rejected_number() {

		$this->register_meta();

		$product  = self::product_factory()->create_and_get();
		$customer = it_exchange_get_customer( 1 );
		$cart     = it_exchange_create_cart_and_session( $customer );
		$cart->add_item( ITE_Cart_Product::create( $product ) );

		$request = \iThemes\Exchange\REST\Request::from_path( "/it_exchange/v1/carts/{$cart->get_id()}" );
		$request->set_method( 'PUT' );
		$request->add_header( 'content-type', 'application/json' );
		$request->set_body( wp_json_encode( array(
			'meta' => array(
				'_number' => 'Hey'
			),
		) ) );

		$scope = new \iThemes\Exchange\REST\Auth\CustomerAuthScope( $customer );
		$this->manager->set_auth_scope( $scope );

		$response = $this->server->dispatch( $request );
		$this->assertEquals( 400, $response->get_status() );
		$cart = it_exchange_get_cart( $cart->get_id() );
		$this->assertFalse( $cart->has_meta( '_number' ) );
	}

	public function test_invalid_meta_rejected_array() {

		$this->register_meta();

		$product  = self::product_factory()->create_and_get();
		$customer = it_exchange_get_customer( 1 );
		$cart     = it_exchange_create_cart_and_session( $customer );
		$cart->add_item( ITE_Cart_Product::create( $product ) );

		$request = \iThemes\Exchange\REST\Request::from_path( "/it_exchange/v1/carts/{$cart->get_id()}" );
		$request->set_method( 'PUT' );
		$request->add_header( 'content-type', 'application/json' );
		$request->set_body( wp_json_encode( array(
			'meta' => array(
				'_array' => 'Joy'
			),
		) ) );

		$scope = new \iThemes\Exchange\REST\Auth\CustomerAuthScope( $customer );
		$this->manager->set_auth_scope( $scope );

		$response = $this->server->dispatch( $request );
		$this->assertEquals( 400, $response->get_status() );
		$cart = it_exchange_get_cart( $cart->get_id() );
		$this->assertFalse( $cart->has_meta( '_array' ) );

		$request = \iThemes\Exchange\REST\Request::from_path( "/it_exchange/v1/carts/{$cart->get_id()}" );
		$request->set_method( 'PUT' );
		$request->add_header( 'content-type', 'application/json' );
		$request->set_body( wp_json_encode( array(
			'meta' => array(
				'_array' => array(
					'joy',
					'to',
					'the',
					'WordPress'
				)
			),
		) ) );

		$scope = new \iThemes\Exchange\REST\Auth\CustomerAuthScope( $customer );
		$this->manager->set_auth_scope( $scope );

		$response = $this->server->dispatch( $request );
		$this->assertEquals( 400, $response->get_status() );
		$cart = it_exchange_get_cart( $cart->get_id() );
		$this->assertFalse( $cart->has_meta( '_array' ) );
	}

	public function test_delete_cart() {

		$customer = it_exchange_get_customer( 1 );
		$cart     = it_exchange_create_cart_and_session( $customer );
		$request  = \iThemes\Exchange\REST\Request::from_path( "/it_exchange/v1/carts/{$cart->get_id()}" );
		$request->set_method( 'DELETE' );

		$scope = new \iThemes\Exchange\REST\Auth\CustomerAuthScope( $customer );
		$this->manager->set_auth_scope( $scope );

		$response = $this->server->dispatch( $request );
		$this->assertEquals( WP_Http::NO_CONTENT, $response->get_status() );
		$this->assertEmpty( $response->get_data() );

		$this->assertNull( it_exchange_get_cart( $cart->get_id() ) );
	}

	protected function register_meta() {

		$this->meta_keys[] = '_hidden';
		$this->meta_keys[] = '_visible';
		$this->meta_keys[] = '_editable';
		$this->meta_keys[] = '_number';
		$this->meta_keys[] = '_array';

		ITE_Cart_Meta_Registry::register( new ITE_Cart_Meta( '_hidden', array() ) );
		ITE_Cart_Meta_Registry::register( new ITE_Cart_Meta( '_visible', array(
			'show_in_rest' => true,
			'schema'       => array(
				'type' => 'string',
			),
		) ) );
		ITE_Cart_Meta_Registry::register( new ITE_Cart_Meta( '_editable', array(
			'show_in_rest'     => true,
			'editable_in_rest' => true,
			'schema'           => array(
				'oneOf' => array(
					array( 'type' => 'string' ),
					array( 'type' => 'null' )
				)
			),
		) ) );
		ITE_Cart_Meta_Registry::register( new ITE_Cart_Meta( '_number', array(
			'show_in_rest'     => true,
			'editable_in_rest' => true,
			'schema'           => array(
				'oneOf' => array(
					array( 'type' => 'number' ),
					array( 'type' => 'null' )
				)
			),
		) ) );
		ITE_Cart_Meta_Registry::register( new ITE_Cart_Meta( '_array', array(
			'show_in_rest'     => true,
			'editable_in_rest' => true,
			'schema'           => array(
				'oneOf' => array(
					array(
						'type'  => 'array',
						'items' => array( 'type' => 'number' ),
					),
					array( 'type' => 'null' )
				),
			),
		) ) );

		$this->manager->_reset();
		$this->initialize_server();
	}
}