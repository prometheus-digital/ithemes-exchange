<?php
/**
 * Test the cart routes.
 *
 * @since   2.0.0
 * @license GPLv2
 */

/**
 * Class Test_IT_Exchange_v1_Cart_Items_Route
 *
 * @group rest-api
 */
class Test_IT_Exchange_v1_Cart_Items_Route extends Test_IT_Exchange_REST_Route {

	public function test_route_registered() {

		$routes = $this->server->get_routes();

		$this->assertArrayHasKey( '/it_exchange/v1/carts/(?P<cart_id>\w+)/items/product', $routes );
		$this->assertArrayHasKey( '/it_exchange/v1/carts/(?P<cart_id>\w+)/items/product/(?P<item_id>[\w\-]+)', $routes );
		$this->assertArrayHasKey( '/it_exchange/v1/carts/(?P<cart_id>\w+)/items/coupon', $routes );
		$this->assertArrayHasKey( '/it_exchange/v1/carts/(?P<cart_id>\w+)/items/coupon/(?P<item_id>[\w\-]+)', $routes );
		$this->assertArrayHasKey( '/it_exchange/v1/carts/(?P<cart_id>\w+)/items/fee', $routes );
		$this->assertArrayHasKey( '/it_exchange/v1/carts/(?P<cart_id>\w+)/items/fee/(?P<item_id>[\w\-]+)', $routes );
	}

	public function test_get_product_collection() {

		$product  = self::product_factory()->create_and_get();
		$customer = it_exchange_get_customer( self::factory()->user->create( array( 'role' => 'subscriber' ) ) );
		$cart     = it_exchange_create_cart_and_session( $customer );

		$item = ITE_Cart_Product::create( $product );
		$cart->add_item( $item );

		$request = \iThemes\Exchange\REST\Request::from_path( "/it_exchange/v1/carts/{$cart->get_id()}/items/product" );

		$scope = new \iThemes\Exchange\REST\Auth\CustomerAuthScope( $customer );
		$this->manager->set_auth_scope( $scope );

		$response = $this->server->dispatch( $request );
		$data     = $response->get_data();

		$this->assertEquals( 200, $response->get_status() );
		$this->assertCount( 1, $data );

		$this->assertEquals( $item->get_id(), $data[0]['id'] );
	}

	public function test_create_product() {

		$product  = self::product_factory()->create_and_get();
		$customer = it_exchange_get_customer( self::factory()->user->create( array( 'role' => 'subscriber' ) ) );
		$cart     = it_exchange_create_cart_and_session( $customer );

		$request = \iThemes\Exchange\REST\Request::from_path( "/it_exchange/v1/carts/{$cart->get_id()}/items/product" );
		$request->set_method( 'POST' );
		$request->add_header( 'content-type', 'application/json' );
		$request->set_body( wp_json_encode( array(
			'product'  => $product->ID,
			'quantity' => 2,
		) ) );

		$scope = new \iThemes\Exchange\REST\Auth\CustomerAuthScope( $customer );
		$this->manager->set_auth_scope( $scope );

		$response = $this->server->dispatch( $request );
		$data     = $response->get_data();

		$this->assertEquals( 201, $response->get_status() );
		$this->assertNotEmpty( $data['id'] );
		$this->assertEquals( 2, $data['quantity']['selected'] );
		$cart = it_exchange_get_cart( $cart->get_id() );
		$this->assertInstanceOf( 'ITE_Cart_Product', $cart->get_item( 'product', $data['id'] ) );
	}

	public function test_delete_product_collection() {

		$product  = self::product_factory()->create_and_get();
		$customer = it_exchange_get_customer( self::factory()->user->create( array( 'role' => 'subscriber' ) ) );
		$cart     = it_exchange_create_cart_and_session( $customer );

		$item = ITE_Cart_Product::create( $product );
		$cart->add_item( $item );

		$request = \iThemes\Exchange\REST\Request::from_path( "/it_exchange/v1/carts/{$cart->get_id()}/items/product" );
		$request->set_method( 'DELETE' );

		$scope = new \iThemes\Exchange\REST\Auth\CustomerAuthScope( $customer );
		$this->manager->set_auth_scope( $scope );

		$response = $this->server->dispatch( $request );

		$this->assertEquals( 204, $response->get_status() );
		$this->assertEmpty( $response->get_data() );
		$this->assertCount( 0, it_exchange_get_cart( $cart->get_id() )->get_items( 'product' ) );
	}

	public function test_get_fee_collection() {

		$customer = it_exchange_get_customer( self::factory()->user->create( array( 'role' => 'subscriber' ) ) );
		$cart     = it_exchange_create_cart_and_session( $customer );

		$item = ITE_Fee_Line_Item::create( 'My Fee', 5.00 );
		$cart->add_item( $item );

		$request = \iThemes\Exchange\REST\Request::from_path( "/it_exchange/v1/carts/{$cart->get_id()}/items/fee" );

		$scope = new \iThemes\Exchange\REST\Auth\CustomerAuthScope( $customer );
		$this->manager->set_auth_scope( $scope );

		$response = $this->server->dispatch( $request );
		$data     = $response->get_data();

		$this->assertEquals( 200, $response->get_status() );
		$this->assertCount( 1, $data );

		$this->assertEquals( $item->get_id(), $data[0]['id'] );
		$this->assertEquals( 'My Fee', $data[0]['name'] );
	}

	public function test_create_fee_not_allowed() {

		$customer = it_exchange_get_customer( self::factory()->user->create( array( 'role' => 'subscriber' ) ) );
		$cart     = it_exchange_create_cart_and_session( $customer );

		$request = \iThemes\Exchange\REST\Request::from_path( "/it_exchange/v1/carts/{$cart->get_id()}/items/fee" );
		$request->set_method( 'POST' );
		$request->add_header( 'content-type', 'application/json' );
		$request->set_body( wp_json_encode( array(
			'amount'   => 5.00,
			'quantity' => 2,
			'name'     => 'My Fee'
		) ) );

		$scope = new \iThemes\Exchange\REST\Auth\CustomerAuthScope( $customer );
		$this->manager->set_auth_scope( $scope );

		$response = $this->server->dispatch( $request );
		$this->assertErrorResponse( 'it_exchange_rest_create_line_item_not_supported', $response, WP_Http::METHOD_NOT_ALLOWED );
	}

	public function test_delete_fee_collection_not_allowed() {

		$customer = it_exchange_get_customer( self::factory()->user->create( array( 'role' => 'subscriber' ) ) );
		$cart     = it_exchange_create_cart_and_session( $customer );

		$item = ITE_Fee_Line_Item::create( 'My Fee', 5.00 );
		$cart->add_item( $item );

		$request = \iThemes\Exchange\REST\Request::from_path( "/it_exchange/v1/carts/{$cart->get_id()}/items/fee" );
		$request->set_method( 'DELETE' );

		$scope = new \iThemes\Exchange\REST\Auth\CustomerAuthScope( $customer );
		$this->manager->set_auth_scope( $scope );

		$response = $this->server->dispatch( $request );
		$this->assertErrorResponse( 'it_exchange_rest_delete_line_item_not_supported', $response, WP_Http::METHOD_NOT_ALLOWED );

		$this->assertCount( 1, it_exchange_get_cart( $cart->get_id() )->get_items( 'fee' ) );
	}

	public function test_get_coupon_collection() {

		/** @var IT_Exchange_Coupon $coupon */
		$coupon   = self::coupon_factory()->create_and_get();
		$product  = self::product_factory()->create_and_get();
		$customer = it_exchange_get_customer( self::factory()->user->create( array( 'role' => 'subscriber' ) ) );
		$cart     = it_exchange_create_cart_and_session( $customer );

		$cart->add_item( ITE_Cart_Product::create( $product ) );
		$item = ITE_Coupon_Line_Item::create( $coupon );
		$cart->add_item( $item );

		$request = \iThemes\Exchange\REST\Request::from_path( "/it_exchange/v1/carts/{$cart->get_id()}/items/coupon" );

		$scope = new \iThemes\Exchange\REST\Auth\CustomerAuthScope( $customer );
		$this->manager->set_auth_scope( $scope );

		$response = $this->server->dispatch( $request );
		$data     = $response->get_data();

		$this->assertEquals( 200, $response->get_status() );
		$this->assertCount( 1, $data );

		$this->assertEquals( $item->get_id(), $data[0]['id'] );
		$this->assertEquals( - 1.00, $data[0]['total'] );
	}

	public function test_create_coupon() {

		/** @var IT_Exchange_Coupon $coupon */
		$coupon   = self::coupon_factory()->create_and_get();
		$product  = self::product_factory()->create_and_get();
		$customer = it_exchange_get_customer( self::factory()->user->create( array( 'role' => 'subscriber' ) ) );
		$cart     = it_exchange_create_cart_and_session( $customer );
		$cart->add_item( ITE_Cart_Product::create( $product ) );

		$request = \iThemes\Exchange\REST\Request::from_path( "/it_exchange/v1/carts/{$cart->get_id()}/items/coupon" );
		$request->set_method( 'POST' );
		$request->add_header( 'content-type', 'application/json' );
		$request->set_body( wp_json_encode( array(
			'coupon' => $coupon->get_code(),
		) ) );

		$scope = new \iThemes\Exchange\REST\Auth\CustomerAuthScope( $customer );
		$this->manager->set_auth_scope( $scope );

		$response = $this->server->dispatch( $request );
		$data     = $response->get_data();

		$this->assertEquals( 201, $response->get_status() );
		$this->assertNotEmpty( $data['id'] );
		$this->assertEquals( $coupon->get_code(), $data['coupon']['code'] );
		$this->assertEquals( $coupon->get_type(), $data['coupon']['type'] );
		$cart = it_exchange_get_cart( $cart->get_id() );
		$this->assertInstanceOf( 'ITE_Coupon_Line_Item', $cart->get_item( 'coupon', $data['id'] ) );
	}

	public function test_create_coupon_via_array() {

		/** @var IT_Exchange_Coupon $coupon */
		$coupon   = self::coupon_factory()->create_and_get();
		$product  = self::product_factory()->create_and_get();
		$customer = it_exchange_get_customer( self::factory()->user->create( array( 'role' => 'subscriber' ) ) );
		$cart     = it_exchange_create_cart_and_session( $customer );
		$cart->add_item( ITE_Cart_Product::create( $product ) );

		$request = \iThemes\Exchange\REST\Request::from_path( "/it_exchange/v1/carts/{$cart->get_id()}/items/coupon" );
		$request->set_method( 'POST' );
		$request->add_header( 'content-type', 'application/json' );
		$request->set_body( wp_json_encode( array(
			'coupon' => array(
				'code' => $coupon->get_code()
			),
		) ) );

		$scope = new \iThemes\Exchange\REST\Auth\CustomerAuthScope( $customer );
		$this->manager->set_auth_scope( $scope );

		$response = $this->server->dispatch( $request );
		$data     = $response->get_data();

		$this->assertEquals( 201, $response->get_status() );
		$this->assertNotEmpty( $data['id'] );
		$this->assertEquals( $coupon->get_code(), $data['coupon']['code'] );
		$this->assertEquals( $coupon->get_type(), $data['coupon']['type'] );
		$cart = it_exchange_get_cart( $cart->get_id() );
		$this->assertInstanceOf( 'ITE_Coupon_Line_Item', $cart->get_item( 'coupon', $data['id'] ) );
	}

	public function test_delete_coupon_collection() {

		$coupon   = self::coupon_factory()->create_and_get();
		$product  = self::product_factory()->create_and_get();
		$customer = it_exchange_get_customer( self::factory()->user->create( array( 'role' => 'subscriber' ) ) );
		$cart     = it_exchange_create_cart_and_session( $customer );
		$cart->add_item( ITE_Cart_Product::create( $product ) );

		$item = ITE_Coupon_Line_Item::create( $coupon );
		$cart->add_item( $item );

		$request = \iThemes\Exchange\REST\Request::from_path( "/it_exchange/v1/carts/{$cart->get_id()}/items/coupon" );
		$request->set_method( 'DELETE' );

		$scope = new \iThemes\Exchange\REST\Auth\CustomerAuthScope( $customer );
		$this->manager->set_auth_scope( $scope );

		$response = $this->server->dispatch( $request );

		$this->assertEquals( 204, $response->get_status() );
		$this->assertEmpty( $response->get_data() );
		$this->assertCount( 0, it_exchange_get_cart( $cart->get_id() )->get_items( 'coupon', true ) );
	}

	public function test_get_product_item() {

		$product  = self::product_factory()->create_and_get();
		$customer = it_exchange_get_customer( self::factory()->user->create( array( 'role' => 'subscriber' ) ) );
		$cart     = it_exchange_create_cart_and_session( $customer );

		$item = ITE_Cart_Product::create( $product );
		$cart->add_item( $item );

		$request = \iThemes\Exchange\REST\Request::from_path(
			"/it_exchange/v1/carts/{$cart->get_id()}/items/product/{$item->get_id()}"
		);

		$scope = new \iThemes\Exchange\REST\Auth\CustomerAuthScope( $customer );
		$this->manager->set_auth_scope( $scope );

		$response = $this->server->dispatch( $request );
		$data     = $response->get_data();

		$this->assertEquals( 200, $response->get_status() );
		$this->assertEquals( $item->get_id(), $data['id'] );
		$this->assertEquals( $product->ID, $data['product'] );
	}

	public function test_edit_product() {

		$product  = self::product_factory()->create_and_get();
		$customer = it_exchange_get_customer( self::factory()->user->create( array( 'role' => 'subscriber' ) ) );
		$cart     = it_exchange_create_cart_and_session( $customer );

		$item = ITE_Cart_Product::create( $product );
		$cart->add_item( $item );

		$request = \iThemes\Exchange\REST\Request::from_path(
			"/it_exchange/v1/carts/{$cart->get_id()}/items/product/{$item->get_id()}"
		);
		$request->set_method( 'PUT' );
		$request->add_header( 'content-type', 'application/json' );
		$request->set_body( wp_json_encode( array(
			'quantity' => 2,
			'product'  => $product->ID
		) ) );

		$scope = new \iThemes\Exchange\REST\Auth\CustomerAuthScope( $customer );
		$this->manager->set_auth_scope( $scope );

		$response = $this->server->dispatch( $request );
		$data     = $response->get_data();

		$this->assertEquals( 200, $response->get_status() );
		$this->assertEquals( 2, $data['quantity']['selected'] );

		$cart = it_exchange_get_cart( $cart->get_id() );
		$this->assertEquals( 2, $cart->get_item( 'product', $data['id'] )->get_quantity() );
	}

	public function test_delete_product_item() {

		$p1       = self::product_factory()->create_and_get();
		$p2       = self::product_factory()->create_and_get();
		$customer = it_exchange_get_customer( self::factory()->user->create( array( 'role' => 'subscriber' ) ) );
		$cart     = it_exchange_create_cart_and_session( $customer );

		$i1 = ITE_Cart_Product::create( $p1 );
		$i2 = ITE_Cart_Product::create( $p2 );
		$cart->add_item( $i1 );
		$cart->add_item( $i2 );

		$request = \iThemes\Exchange\REST\Request::from_path(
			"/it_exchange/v1/carts/{$cart->get_id()}/items/product/{$i1->get_id()}"
		);
		$request->set_method( 'DELETE' );

		$scope = new \iThemes\Exchange\REST\Auth\CustomerAuthScope( $customer );
		$this->manager->set_auth_scope( $scope );

		$response = $this->server->dispatch( $request );

		$this->assertEquals( 204, $response->get_status() );
		$this->assertEmpty( $response->get_data() );

		$fresh = it_exchange_get_cart( $cart->get_id() );
		$this->assertNull( $fresh->get_item( $i1->get_type(), $i1->get_id() ) );
		$this->assertNotNull( $fresh->get_item( $i2->get_type(), $i2->get_id() ) );
	}

	public function test_get_fee_item() {

		$customer = it_exchange_get_customer( self::factory()->user->create( array( 'role' => 'subscriber' ) ) );
		$cart     = it_exchange_create_cart_and_session( $customer );

		$item = ITE_Fee_Line_Item::create( 'My Fee', 5.00 );
		$cart->add_item( $item );

		$request = \iThemes\Exchange\REST\Request::from_path(
			"/it_exchange/v1/carts/{$cart->get_id()}/items/fee/{$item->get_id()}"
		);

		$scope = new \iThemes\Exchange\REST\Auth\CustomerAuthScope( $customer );
		$this->manager->set_auth_scope( $scope );

		$response = $this->server->dispatch( $request );
		$data     = $response->get_data();

		$this->assertEquals( 200, $response->get_status() );

		$this->assertEquals( $item->get_id(), $data['id'] );
		$this->assertEquals( 'My Fee', $data['name'] );
	}

	public function test_edit_fee_not_allowed() {

		$customer = it_exchange_get_customer( self::factory()->user->create( array( 'role' => 'subscriber' ) ) );
		$cart     = it_exchange_create_cart_and_session( $customer );

		$item = ITE_Fee_Line_Item::create( 'My Fee', 5.00 );
		$cart->add_item( $item );

		$request = \iThemes\Exchange\REST\Request::from_path(
			"/it_exchange/v1/carts/{$cart->get_id()}/items/fee/{$item->get_id()}"
		);
		$request->set_method( 'PUT' );
		$request->add_header( 'content-type', 'application/json' );
		$request->set_body( wp_json_encode( array(
			'quantity' => 2,
			'name'     => 'My New Fee'
		) ) );

		$scope = new \iThemes\Exchange\REST\Auth\CustomerAuthScope( $customer );
		$this->manager->set_auth_scope( $scope );

		$response = $this->server->dispatch( $request );
		$this->assertErrorResponse( 'it_exchange_rest_edit_line_item_not_supported', $response, WP_Http::METHOD_NOT_ALLOWED );

		$fresh = it_exchange_get_cart( $cart->get_id() );
		$this->assertEquals( 'My Fee', $fresh->get_item( 'fee', $item->get_id() )->get_name() );
		$this->assertEquals( 1, $fresh->get_item( 'fee', $item->get_id() )->get_quantity() );
	}

	public function test_delete_fee_item_not_allowed() {

		$customer = it_exchange_get_customer( self::factory()->user->create( array( 'role' => 'subscriber' ) ) );
		$cart     = it_exchange_create_cart_and_session( $customer );

		$item = ITE_Fee_Line_Item::create( 'My Fee', 5.00 );
		$cart->add_item( $item );

		$request = \iThemes\Exchange\REST\Request::from_path(
			"/it_exchange/v1/carts/{$cart->get_id()}/items/fee/{$item->get_id()}"
		);
		$request->set_method( 'DELETE' );

		$scope = new \iThemes\Exchange\REST\Auth\CustomerAuthScope( $customer );
		$this->manager->set_auth_scope( $scope );

		$response = $this->server->dispatch( $request );
		$this->assertErrorResponse( 'it_exchange_rest_delete_line_item_not_supported', $response, WP_Http::METHOD_NOT_ALLOWED );

		$this->assertNotNull( it_exchange_get_cart( $cart->get_id() )->get_item( 'fee', $item->get_id() ) );
	}

	public function test_get_coupon_item() {

		$coupon   = self::coupon_factory()->create_and_get();
		$product  = self::product_factory()->create_and_get();
		$customer = it_exchange_get_customer( self::factory()->user->create( array( 'role' => 'subscriber' ) ) );
		$cart     = it_exchange_create_cart_and_session( $customer );

		$cart->add_item( ITE_Cart_Product::create( $product ) );
		$item = ITE_Coupon_Line_Item::create( $coupon );
		$cart->add_item( $item );

		$request = \iThemes\Exchange\REST\Request::from_path(
			"/it_exchange/v1/carts/{$cart->get_id()}/items/coupon/{$item->get_id()}"
		);

		$scope = new \iThemes\Exchange\REST\Auth\CustomerAuthScope( $customer );
		$this->manager->set_auth_scope( $scope );

		$response = $this->server->dispatch( $request );
		$data     = $response->get_data();

		$this->assertEquals( 200, $response->get_status() );
		$this->assertEquals( $item->get_id(), $data['id'] );
		$this->assertEquals( - 1.00, $data['total'] );
	}

	public function test_edit_coupon_no_op() {

		/** @var IT_Exchange_Coupon $coupon */
		$coupon   = self::coupon_factory()->create_and_get();
		$product  = self::product_factory()->create_and_get();
		$customer = it_exchange_get_customer( self::factory()->user->create( array( 'role' => 'subscriber' ) ) );
		$cart     = it_exchange_create_cart_and_session( $customer );
		$cart->add_item( ITE_Cart_Product::create( $product ) );
		$item = ITE_Coupon_Line_Item::create( $coupon );
		$cart->add_item( $item );

		$request = \iThemes\Exchange\REST\Request::from_path(
			"/it_exchange/v1/carts/{$cart->get_id()}/items/coupon/{$item->get_id()}"
		);
		$request->set_method( 'PUT' );
		$request->add_header( 'content-type', 'application/json' );
		$request->set_body( wp_json_encode( array(
			'coupon'   => '5678',
			'quantity' => 2,
		) ) );

		$scope = new \iThemes\Exchange\REST\Auth\CustomerAuthScope( $customer );
		$this->manager->set_auth_scope( $scope );

		$response = $this->server->dispatch( $request );
		$data     = $response->get_data();

		$this->assertEquals( 200, $response->get_status() );
		$this->assertEquals( $coupon->get_code(), $data['coupon']['code'] );
		$this->assertEquals( $coupon->get_type(), $data['coupon']['type'] );
		$this->assertEquals( 1, $data['quantity']['selected'] );

		$fresh = it_exchange_get_cart( $cart->get_id() );
		$this->assertNotNull( $fresh->get_item( 'coupon', $data['id'] ) );
		$this->assertEquals( 1, $fresh->get_item( 'coupon', $data['id'] )->get_quantity() );
	}

	// We can't test that only the correct one is deleted, because only one coupon is allowed at once.
	public function test_delete_coupon_item() {

		$coupon   = self::coupon_factory()->create_and_get();
		$product  = self::product_factory()->create_and_get();
		$customer = it_exchange_get_customer( self::factory()->user->create( array( 'role' => 'subscriber' ) ) );
		$cart     = it_exchange_create_cart_and_session( $customer );
		$cart->add_item( ITE_Cart_Product::create( $product ) );

		$item = ITE_Coupon_Line_Item::create( $coupon );
		$cart->add_item( $item );

		$request = \iThemes\Exchange\REST\Request::from_path(
			"/it_exchange/v1/carts/{$cart->get_id()}/items/coupon/{$item->get_id()}"
		);
		$request->set_method( 'DELETE' );

		$scope = new \iThemes\Exchange\REST\Auth\CustomerAuthScope( $customer );
		$this->manager->set_auth_scope( $scope );

		$response = $this->server->dispatch( $request );

		$this->assertEquals( 204, $response->get_status() );
		$this->assertEmpty( $response->get_data() );
		$this->assertNull( it_exchange_get_cart( $cart->get_id() )->get_item( 'coupon', $item->get_id() ) );
	}

	public function test_item_not_found() {

		$customer = it_exchange_get_customer( self::factory()->user->create( array( 'role' => 'subscriber' ) ) );
		$cart     = it_exchange_create_cart_and_session( $customer );

		$request = \iThemes\Exchange\REST\Request::from_path(
			"/it_exchange/v1/carts/{$cart->get_id()}/items/coupon/garbage"
		);

		$scope = new \iThemes\Exchange\REST\Auth\CustomerAuthScope( $customer );
		$this->manager->set_auth_scope( $scope );

		$response = $this->server->dispatch( $request );

		$this->assertEquals( 404, $response->get_status() );
	}

	public function test_item_not_found_if_cart_mismatch() {

		$product  = self::product_factory()->create_and_get();
		$customer = it_exchange_get_customer( self::factory()->user->create( array( 'role' => 'subscriber' ) ) );
		$c1       = it_exchange_create_cart_and_session( $customer, false );
		$c2       = it_exchange_create_cart_and_session( $customer, false );
		$c2->add_item( $item = ITE_Cart_Product::create( $product ) );

		$request = \iThemes\Exchange\REST\Request::from_path(
			"/it_exchange/v1/carts/{$c1->get_id()}/items/coupon/{$item->get_id()}"
		);

		$scope = new \iThemes\Exchange\REST\Auth\CustomerAuthScope( $customer );
		$this->manager->set_auth_scope( $scope );

		$response = $this->server->dispatch( $request );

		$this->assertEquals( 404, $response->get_status() );
	}

	public function test_item_not_found_if_cart_deleted() {

		$product  = self::product_factory()->create_and_get();
		$customer = it_exchange_get_customer( self::factory()->user->create( array( 'role' => 'subscriber' ) ) );
		$cart     = it_exchange_create_cart_and_session( $customer, false );
		$cart->add_item( $item = ITE_Cart_Product::create( $product ) );

		$request = \iThemes\Exchange\REST\Request::from_path(
			"/it_exchange/v1/carts/{$cart->get_id()}/items/coupon/{$item->get_id()}"
		);

		$cart->destroy();

		$scope = new \iThemes\Exchange\REST\Auth\CustomerAuthScope( $customer );
		$this->manager->set_auth_scope( $scope );

		$response = $this->server->dispatch( $request );

		$this->assertEquals( 404, $response->get_status() );
	}
}