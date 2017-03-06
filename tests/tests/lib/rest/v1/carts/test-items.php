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
}