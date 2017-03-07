<?php
/**
 * Test the Shipping Methods API endpoint.
 *
 * @since   2.0.0
 * @license GPLv2
 */


/**
 * Class Test_IT_Exchange_v1_Cart_Shipping_Route
 *
 * @group rest-api
 */
class Test_IT_Exchange_v1_Cart_Shipping_Route extends Test_IT_Exchange_REST_Route {

	/**
	 * @inheritDoc
	 */
	public function setUp() {
		parent::setUp();

		$options                              = it_exchange_get_option( 'simple-shipping', true );
		$options['enable-free-shipping']      = true;
		$options['enable-flat-rate-shipping'] = true;
		it_exchange_save_option( 'simple-shipping', $options, true );
	}

	public function test_route_registered() {

		$routes = $this->server->get_routes();

		$this->assertArrayHasKey( '/it_exchange/v1/carts/(?P<cart_id>\w+)/shipping', $routes );
	}

	public function test_single_shippable_product() {

		$product  = self::product_factory()->create_and_get( array(
			'type'     => 'physical-product-type',
			'shipping' => true
		) );
		$customer = it_exchange_get_customer( self::factory()->user->create( array( 'role' => 'subscriber' ) ) );
		$cart     = it_exchange_create_cart_and_session( $customer );

		$cart->add_item( $item = ITE_Cart_Product::create( $product ) );

		$request = \iThemes\Exchange\REST\Request::from_path( "/it_exchange/v1/carts/{$cart->get_id()}/shipping" );

		$scope = new \iThemes\Exchange\REST\Auth\CustomerAuthScope( $customer );
		$this->manager->set_auth_scope( $scope );

		$response = $this->server->dispatch( $request );
		$data     = $response->get_data();
		$this->assertEquals( 200, $response->get_status() );

		$this->assertArrayHasKey( 'cart_wide', $data );
		$this->assertArrayHasKey( 'per_item', $data );

		$this->assertInternalType( 'array', $data['cart_wide'] );
		$this->assertInternalType( 'array', $data['per_item'] );

		$this->assertCount( 2, $data['cart_wide'] );
		$this->assertCount( 0, $data['per_item'] );

		$expected = array(
			array(
				'id'       => 'exchange-flat-rate-shipping',
				'label'    => 'Standard Shipping (3-5 days)',
				'total'    => 5.00,
				'selected' => false,
			),
			array(
				'id'       => 'exchange-free-shipping',
				'label'    => 'Free Shipping (3-5 days)',
				'total'    => 0.0,
				'selected' => false,
			),
		);

		$this->assertEqualSets( $expected, $data['cart_wide'] );
	}

	public function test_multiple_shippable_products() {

		$p1       = self::product_factory()->create_and_get( array(
			'type'     => 'physical-product-type',
			'shipping' => true
		) );
		$p2       = self::product_factory()->create_and_get( array(
			'type'     => 'physical-product-type',
			'shipping' => true
		) );
		$customer = it_exchange_get_customer( self::factory()->user->create( array( 'role' => 'subscriber' ) ) );
		$cart     = it_exchange_create_cart_and_session( $customer );

		$cart->add_item( $i1 = ITE_Cart_Product::create( $p1 ) );
		$cart->add_item( $i2 = ITE_Cart_Product::create( $p2 ) );

		$request = \iThemes\Exchange\REST\Request::from_path( "/it_exchange/v1/carts/{$cart->get_id()}/shipping" );

		$scope = new \iThemes\Exchange\REST\Auth\CustomerAuthScope( $customer );
		$this->manager->set_auth_scope( $scope );

		$response = $this->server->dispatch( $request );
		$data     = $response->get_data();
		$this->assertEquals( 200, $response->get_status() );

		$this->assertArrayHasKey( 'cart_wide', $data );
		$this->assertArrayHasKey( 'per_item', $data );

		$this->assertInternalType( 'array', $data['cart_wide'] );
		$this->assertInternalType( 'array', $data['per_item'] );

		$this->assertCount( 3, $data['cart_wide'] );
		$this->assertCount( 2, $data['per_item'] );

		$expected_cart_wide = array(
			array(
				'id'       => 'exchange-flat-rate-shipping',
				'label'    => 'Standard Shipping (3-5 days)',
				'total'    => 10.00,
				'selected' => false,
			),
			array(
				'id'       => 'exchange-free-shipping',
				'label'    => 'Free Shipping (3-5 days)',
				'total'    => 0.0,
				'selected' => false,
			),
			array(
				'id'       => 'multiple-methods',
				'label'    => 'Multiple Methods',
				'total'    => null,
				'selected' => false,
			),
		);

		$this->assertEqualSets( $expected_cart_wide, $data['cart_wide'] );

		$expected_per_item = array(
			array(
				'item'    => array(
					'id'   => $i1->get_id(),
					'type' => 'product',
				),
				'methods' => array(
					array(
						'id'       => 'exchange-flat-rate-shipping',
						'label'    => 'Standard Shipping (3-5 days)',
						'total'    => 5.00,
						'selected' => false,
					),
					array(
						'id'       => 'exchange-free-shipping',
						'label'    => 'Free Shipping (3-5 days)',
						'total'    => 0.0,
						'selected' => false,
					),
				),
			),
			array(
				'item'    => array(
					'id'   => $i2->get_id(),
					'type' => 'product',
				),
				'methods' => array(
					array(
						'id'       => 'exchange-flat-rate-shipping',
						'label'    => 'Standard Shipping (3-5 days)',
						'total'    => 5.00,
						'selected' => false,
					),
					array(
						'id'       => 'exchange-free-shipping',
						'label'    => 'Free Shipping (3-5 days)',
						'total'    => 0.0,
						'selected' => false,
					),
				),
			),
		);

		$this->assertEqualSets( $expected_per_item, $data['per_item'] );
	}

	public function test_multiple_shippable_products_not_eligible_for_multiple_methods() {

		$p1       = self::product_factory()->create_and_get( array(
			'type'     => 'physical-product-type',
			'shipping' => true
		) );
		$p2       = self::product_factory()->create_and_get( array(
			'type'     => 'physical-product-type',
			'shipping' => true
		) );
		$customer = it_exchange_get_customer( self::factory()->user->create( array( 'role' => 'subscriber' ) ) );
		$cart     = it_exchange_create_cart_and_session( $customer );

		$cart->add_item( $i1 = ITE_Cart_Product::create( $p1 ) );
		$cart->add_item( $i2 = ITE_Cart_Product::create( $p2 ) );

		$request = \iThemes\Exchange\REST\Request::from_path( "/it_exchange/v1/carts/{$cart->get_id()}/shipping" );

		$scope = new \iThemes\Exchange\REST\Auth\CustomerAuthScope( $customer );
		$this->manager->set_auth_scope( $scope );

		add_filter( 'it_exchange_shipping_method_form_multiple_shipping_methods_allowed', '__return_false' );

		$response = $this->server->dispatch( $request );
		$data     = $response->get_data();
		$this->assertEquals( 200, $response->get_status() );

		$this->assertArrayHasKey( 'cart_wide', $data );
		$this->assertArrayHasKey( 'per_item', $data );

		$this->assertInternalType( 'array', $data['cart_wide'] );
		$this->assertInternalType( 'array', $data['per_item'] );

		$this->assertCount( 2, $data['cart_wide'] );
		$this->assertCount( 0, $data['per_item'] );

		$expected_cart_wide = array(
			array(
				'id'       => 'exchange-flat-rate-shipping',
				'label'    => 'Standard Shipping (3-5 days)',
				'total'    => 10.00,
				'selected' => false,
			),
			array(
				'id'       => 'exchange-free-shipping',
				'label'    => 'Free Shipping (3-5 days)',
				'total'    => 0.0,
				'selected' => false,
			),
		);

		$this->assertEqualSets( $expected_cart_wide, $data['cart_wide'] );
	}

	public function test_multiple_shippable_products_multiple_methods_forced() {

		$options                                           = it_exchange_get_option( 'shipping-general' );
		$options['products-can-override-shipping-methods'] = true;
		it_exchange_save_option( 'shipping-general', $options, true );

		$p1 = self::product_factory()->create_and_get( array(
			'type'     => 'physical-product-type',
			'shipping' => true,
		) );
		$p2 = self::product_factory()->create_and_get( array(
			'type'     => 'physical-product-type',
			'shipping' => true,
		) );

		$p1_feature = it_exchange_get_registered_shipping_feature( 'core-available-shipping-methods', $p1 );
		$p1_feature->update_value( array(
			'exchange-flat-rate-shipping' => true,
			'exchange-free-shipping'      => false,
			'enabled'                     => true,
		) );
		$p2_feature = it_exchange_get_registered_shipping_feature( 'core-available-shipping-methods', $p2 );
		$p2_feature->update_value( array(
			'exchange-flat-rate-shipping' => false,
			'exchange-free-shipping'      => true,
			'enabled'                     => true,
		) );

		$customer = it_exchange_get_customer( self::factory()->user->create( array( 'role' => 'subscriber' ) ) );
		$cart     = it_exchange_create_cart_and_session( $customer );

		$cart->add_item( $i1 = ITE_Cart_Product::create( $p1 ) );
		$cart->add_item( $i2 = ITE_Cart_Product::create( $p2 ) );

		$request = \iThemes\Exchange\REST\Request::from_path( "/it_exchange/v1/carts/{$cart->get_id()}/shipping" );

		$scope = new \iThemes\Exchange\REST\Auth\CustomerAuthScope( $customer );
		$this->manager->set_auth_scope( $scope );

		$response = $this->server->dispatch( $request );
		$data     = $response->get_data();

		$this->assertEquals( 200, $response->get_status() );
		$this->assertArrayHasKey( 'cart_wide', $data );
		$this->assertArrayHasKey( 'per_item', $data );

		$this->assertInternalType( 'array', $data['cart_wide'] );
		$this->assertInternalType( 'array', $data['per_item'] );

		$this->assertCount( 1, $data['cart_wide'] );
		$this->assertCount( 2, $data['per_item'] );

		$expected_cart_wide = array(
			array(
				'id'       => 'multiple-methods',
				'label'    => 'Multiple Methods',
				'total'    => null,
				'selected' => true,
			),
		);

		$this->assertEqualSets( $expected_cart_wide, $data['cart_wide'] );

		$expected_per_item = array(
			array(
				'item'    => array(
					'id'   => $i1->get_id(),
					'type' => 'product',
				),
				'methods' => array(
					array(
						'id'       => 'exchange-flat-rate-shipping',
						'label'    => 'Standard Shipping (3-5 days)',
						'total'    => 5.00,
						'selected' => true,
					),
				),
			),
			array(
				'item'    => array(
					'id'   => $i2->get_id(),
					'type' => 'product',
				),
				'methods' => array(
					array(
						'id'       => 'exchange-free-shipping',
						'label'    => 'Free Shipping (3-5 days)',
						'total'    => 0.0,
						'selected' => true,
					),
				),
			),
		);

		$this->assertEqualSets( $expected_per_item, $data['per_item'] );
	}

	public function test_select_method_for_single_shippable_product() {

		$product  = self::product_factory()->create_and_get( array(
			'type'     => 'physical-product-type',
			'shipping' => true
		) );
		$customer = it_exchange_get_customer( self::factory()->user->create( array( 'role' => 'subscriber' ) ) );
		$cart     = it_exchange_create_cart_and_session( $customer );

		$cart->add_item( $item = ITE_Cart_Product::create( $product ) );

		$cart_wide = array(
			array(
				'id'       => 'exchange-flat-rate-shipping',
				'label'    => 'Standard Shipping (3-5 days)',
				'total'    => 5.00,
				'selected' => false,
			),
			array(
				'id'       => 'exchange-free-shipping',
				'label'    => 'Free Shipping (3-5 days)',
				'total'    => 0.0,
				'selected' => true,
			),
		);

		$request = \iThemes\Exchange\REST\Request::from_path( "/it_exchange/v1/carts/{$cart->get_id()}/shipping" );
		$request->set_method( 'PUT' );
		$request->add_header( 'content-type', 'application/json' );
		$request->set_body( wp_json_encode( array(
			'cart_wide' => $cart_wide,
		) ) );

		$scope = new \iThemes\Exchange\REST\Auth\CustomerAuthScope( $customer );
		$this->manager->set_auth_scope( $scope );

		$response = $this->server->dispatch( $request );
		$data     = $response->get_data();
		$this->assertEquals( 200, $response->get_status() );

		$this->assertEqualSets( $cart_wide, $data['cart_wide'] );
		$this->assertEquals( 'exchange-free-shipping', it_exchange_get_cart( $cart->get_id() )->get_shipping_method()->slug );
	}

	public function test_select_single_method_for_multiple_shippable_products() {
		$p1       = self::product_factory()->create_and_get( array(
			'type'     => 'physical-product-type',
			'shipping' => true
		) );
		$p2       = self::product_factory()->create_and_get( array(
			'type'     => 'physical-product-type',
			'shipping' => true
		) );
		$customer = it_exchange_get_customer( self::factory()->user->create( array( 'role' => 'subscriber' ) ) );
		$cart     = it_exchange_create_cart_and_session( $customer );

		$cart->add_item( $i1 = ITE_Cart_Product::create( $p1 ) );
		$cart->add_item( $i2 = ITE_Cart_Product::create( $p2 ) );

		$cart_wide = array(
			array(
				'id'       => 'exchange-flat-rate-shipping',
				'label'    => 'Standard Shipping (3-5 days)',
				'total'    => 10.00,
				'selected' => true,
			),
			array(
				'id'       => 'exchange-free-shipping',
				'label'    => 'Free Shipping (3-5 days)',
				'total'    => 0.0,
				'selected' => false,
			),
			array(
				'id'       => 'multiple-methods',
				'label'    => 'Multiple Methods',
				'total'    => null,
				'selected' => false,
			),
		);
		$per_item  = array(
			array(
				'item'    => array(
					'id'   => $i1->get_id(),
					'type' => 'product',
				),
				'methods' => array(
					array(
						'id'       => 'exchange-flat-rate-shipping',
						'label'    => 'Standard Shipping (3-5 days)',
						'total'    => 5.00,
						'selected' => false,
					),
					array(
						'id'       => 'exchange-free-shipping',
						'label'    => 'Free Shipping (3-5 days)',
						'total'    => 0.0,
						'selected' => false,
					),
				),
			),
			array(
				'item'    => array(
					'id'   => $i2->get_id(),
					'type' => 'product',
				),
				'methods' => array(
					array(
						'id'       => 'exchange-flat-rate-shipping',
						'label'    => 'Standard Shipping (3-5 days)',
						'total'    => 5.00,
						'selected' => false,
					),
					array(
						'id'       => 'exchange-free-shipping',
						'label'    => 'Free Shipping (3-5 days)',
						'total'    => 0.0,
						'selected' => false,
					),
				),
			),
		);

		$request = \iThemes\Exchange\REST\Request::from_path( "/it_exchange/v1/carts/{$cart->get_id()}/shipping" );
		$request->set_method( 'PUT' );
		$request->add_header( 'content-type', 'application/json' );
		$request->set_body( wp_json_encode( array(
			'cart_wide' => $cart_wide,
			'per_item'  => $per_item,
		) ) );

		$scope = new \iThemes\Exchange\REST\Auth\CustomerAuthScope( $customer );
		$this->manager->set_auth_scope( $scope );

		$response = $this->server->dispatch( $request );
		$data     = $response->get_data();
		$this->assertEquals( 200, $response->get_status() );

		$this->assertEqualSets( $cart_wide, $data['cart_wide'] );
		$this->assertEqualSets( $per_item, $data['per_item'] );

		$this->assertEquals( 'exchange-flat-rate-shipping', it_exchange_get_cart( $cart->get_id() )->get_shipping_method()->slug );
	}

	public function test_select_multiple_methods_for_multiple_shippable_products() {
		$p1       = self::product_factory()->create_and_get( array(
			'type'     => 'physical-product-type',
			'shipping' => true
		) );
		$p2       = self::product_factory()->create_and_get( array(
			'type'     => 'physical-product-type',
			'shipping' => true
		) );
		$customer = it_exchange_get_customer( self::factory()->user->create( array( 'role' => 'subscriber' ) ) );
		$cart     = it_exchange_create_cart_and_session( $customer );

		$cart->add_item( $i1 = ITE_Cart_Product::create( $p1 ) );
		$cart->add_item( $i2 = ITE_Cart_Product::create( $p2 ) );

		$cart_wide = array(
			array(
				'id'       => 'exchange-flat-rate-shipping',
				'label'    => 'Standard Shipping (3-5 days)',
				'total'    => 10.00,
				'selected' => false,
			),
			array(
				'id'       => 'exchange-free-shipping',
				'label'    => 'Free Shipping (3-5 days)',
				'total'    => 0.0,
				'selected' => false,
			),
			array(
				'id'       => 'multiple-methods',
				'label'    => 'Multiple Methods',
				'total'    => null,
				'selected' => true,
			),
		);
		$per_item  = array(
			array(
				'item'    => array(
					'id'   => $i1->get_id(),
					'type' => 'product',
				),
				'methods' => array(
					array(
						'id'       => 'exchange-flat-rate-shipping',
						'label'    => 'Standard Shipping (3-5 days)',
						'total'    => 5.00,
						'selected' => true,
					),
					array(
						'id'       => 'exchange-free-shipping',
						'label'    => 'Free Shipping (3-5 days)',
						'total'    => 0.0,
						'selected' => false,
					),
				),
			),
			array(
				'item'    => array(
					'id'   => $i2->get_id(),
					'type' => 'product',
				),
				'methods' => array(
					array(
						'id'       => 'exchange-flat-rate-shipping',
						'label'    => 'Standard Shipping (3-5 days)',
						'total'    => 5.00,
						'selected' => false,
					),
					array(
						'id'       => 'exchange-free-shipping',
						'label'    => 'Free Shipping (3-5 days)',
						'total'    => 0.0,
						'selected' => true,
					),
				),
			),
		);

		$request = \iThemes\Exchange\REST\Request::from_path( "/it_exchange/v1/carts/{$cart->get_id()}/shipping" );
		$request->set_method( 'PUT' );
		$request->add_header( 'content-type', 'application/json' );
		$request->set_body( wp_json_encode( array(
			'cart_wide' => $cart_wide,
			'per_item'  => $per_item,
		) ) );

		$scope = new \iThemes\Exchange\REST\Auth\CustomerAuthScope( $customer );
		$this->manager->set_auth_scope( $scope );

		$response = $this->server->dispatch( $request );
		$data     = $response->get_data();
		$this->assertEquals( 200, $response->get_status() );

		$this->assertEqualSets( $cart_wide, $data['cart_wide'] );
		$this->assertEqualSets( $per_item, $data['per_item'] );

		$fresh = it_exchange_get_cart( $cart->get_id() );
		$this->assertEquals( 'multiple-methods', $fresh->get_shipping_method()->slug );
		$this->assertInternalType( 'object', $i1_method = $fresh->get_shipping_method( $fresh->refresh_item( $i1 ) ) );
		$this->assertInternalType( 'object', $i2_method = $fresh->get_shipping_method( $fresh->refresh_item( $i2 ) ) );
		$this->assertEquals( 'exchange-flat-rate-shipping', $i1_method->slug );
		$this->assertEquals( 'exchange-free-shipping', $i2_method->slug );
	}

	public function test_not_available_shipping_methods_rejected_for_cart_method() {

		$options                              = it_exchange_get_option( 'simple-shipping', true );
		$options['enable-free-shipping']      = false;
		$options['enable-flat-rate-shipping'] = true;
		it_exchange_save_option( 'simple-shipping', $options, true );

		$product  = self::product_factory()->create_and_get( array(
			'type'     => 'physical-product-type',
			'shipping' => true
		) );
		$customer = it_exchange_get_customer( self::factory()->user->create( array( 'role' => 'subscriber' ) ) );
		$cart     = it_exchange_create_cart_and_session( $customer );

		$cart->add_item( $item = ITE_Cart_Product::create( $product ) );

		$cart_wide = array(
			array(
				'id'       => 'exchange-flat-rate-shipping',
				'label'    => 'Standard Shipping (3-5 days)',
				'total'    => 5.00,
				'selected' => false,
			),
			array(
				'id'       => 'exchange-free-shipping',
				'label'    => 'Free Shipping (3-5 days)',
				'total'    => 0.0,
				'selected' => true,
			),
		);

		$request = \iThemes\Exchange\REST\Request::from_path( "/it_exchange/v1/carts/{$cart->get_id()}/shipping" );
		$request->set_method( 'PUT' );
		$request->add_header( 'content-type', 'application/json' );
		$request->set_body( wp_json_encode( array(
			'cart_wide' => $cart_wide,
		) ) );

		$scope = new \iThemes\Exchange\REST\Auth\CustomerAuthScope( $customer );
		$this->manager->set_auth_scope( $scope );

		$response = $this->server->dispatch( $request );
		$this->assertErrorResponse( 'it_exchange_rest_invalid_shipping_method', $response, 400 );
	}

	public function test_multiple_methods_shipping_method_rejected_for_cart_method() {

		$options                              = it_exchange_get_option( 'simple-shipping', true );
		$options['enable-free-shipping']      = false;
		$options['enable-flat-rate-shipping'] = true;
		it_exchange_save_option( 'simple-shipping', $options, true );

		$product  = self::product_factory()->create_and_get( array(
			'type'     => 'physical-product-type',
			'shipping' => true
		) );
		$customer = it_exchange_get_customer( self::factory()->user->create( array( 'role' => 'subscriber' ) ) );
		$cart     = it_exchange_create_cart_and_session( $customer );

		$cart->add_item( $item = ITE_Cart_Product::create( $product ) );

		$cart_wide = array(
			array(
				'id'       => 'exchange-flat-rate-shipping',
				'label'    => 'Standard Shipping (3-5 days)',
				'total'    => 5.00,
				'selected' => false,
			),
			array(
				'id'       => 'exchange-free-shipping',
				'label'    => 'Free Shipping (3-5 days)',
				'total'    => 0.0,
				'selected' => false,
			),
			array(
				'id'       => 'multiple-methods',
				'label'    => 'Multiple Methods',
				'total'    => null,
				'selected' => true,
			),
		);

		$request = \iThemes\Exchange\REST\Request::from_path( "/it_exchange/v1/carts/{$cart->get_id()}/shipping" );
		$request->set_method( 'PUT' );
		$request->add_header( 'content-type', 'application/json' );
		$request->set_body( wp_json_encode( array(
			'cart_wide' => $cart_wide,
		) ) );

		$scope = new \iThemes\Exchange\REST\Auth\CustomerAuthScope( $customer );
		$this->manager->set_auth_scope( $scope );

		$response = $this->server->dispatch( $request );
		$this->assertErrorResponse( 'it_exchange_rest_invalid_shipping_method', $response, 400 );
	}

	public function test_not_available_shipping_methods_rejected_for_cart_item() {

		$options                                           = it_exchange_get_option( 'shipping-general' );
		$options['products-can-override-shipping-methods'] = true;
		it_exchange_save_option( 'shipping-general', $options, true );

		$p1 = self::product_factory()->create_and_get( array(
			'type'     => 'physical-product-type',
			'shipping' => true,
		) );
		$p2 = self::product_factory()->create_and_get( array(
			'type'     => 'physical-product-type',
			'shipping' => true,
		) );

		$p1_feature = it_exchange_get_registered_shipping_feature( 'core-available-shipping-methods', $p1 );
		$p1_feature->update_value( array(
			'exchange-flat-rate-shipping' => true,
			'exchange-free-shipping'      => false,
			'enabled'                     => true,
		) );
		$p2_feature = it_exchange_get_registered_shipping_feature( 'core-available-shipping-methods', $p2 );
		$p2_feature->update_value( array(
			'exchange-flat-rate-shipping' => false,
			'exchange-free-shipping'      => true,
			'enabled'                     => true,
		) );

		$customer = it_exchange_get_customer( self::factory()->user->create( array( 'role' => 'subscriber' ) ) );
		$cart     = it_exchange_create_cart_and_session( $customer );

		$cart->add_item( $i1 = ITE_Cart_Product::create( $p1 ) );
		$cart->add_item( $i2 = ITE_Cart_Product::create( $p2 ) );

		$cart_wide = array(
			array(
				'id'       => 'exchange-flat-rate-shipping',
				'label'    => 'Standard Shipping (3-5 days)',
				'total'    => 10.00,
				'selected' => false,
			),
			array(
				'id'       => 'exchange-free-shipping',
				'label'    => 'Free Shipping (3-5 days)',
				'total'    => 0.0,
				'selected' => false,
			),
			array(
				'id'       => 'multiple-methods',
				'label'    => 'Multiple Methods',
				'total'    => null,
				'selected' => true,
			),
		);
		$per_item  = array(
			array(
				'item'    => array(
					'id'   => $i1->get_id(),
					'type' => 'product',
				),
				'methods' => array(
					array(
						'id'       => 'exchange-flat-rate-shipping',
						'label'    => 'Standard Shipping (3-5 days)',
						'total'    => 5.00,
						'selected' => false,
					),
					array(
						'id'       => 'exchange-free-shipping',
						'label'    => 'Free Shipping (3-5 days)',
						'total'    => 0.0,
						'selected' => true,
					),
				),
			),
			array(
				'item'    => array(
					'id'   => $i2->get_id(),
					'type' => 'product',
				),
				'methods' => array(
					array(
						'id'       => 'exchange-flat-rate-shipping',
						'label'    => 'Standard Shipping (3-5 days)',
						'total'    => 5.00,
						'selected' => true,
					),
					array(
						'id'       => 'exchange-free-shipping',
						'label'    => 'Free Shipping (3-5 days)',
						'total'    => 0.0,
						'selected' => false,
					),
				),
			),
		);

		$request = \iThemes\Exchange\REST\Request::from_path( "/it_exchange/v1/carts/{$cart->get_id()}/shipping" );
		$request->set_method( 'PUT' );
		$request->add_header( 'content-type', 'application/json' );
		$request->set_body( wp_json_encode( array(
			'cart_wide' => $cart_wide,
			'per_item'  => $per_item,
		) ) );

		$scope = new \iThemes\Exchange\REST\Auth\CustomerAuthScope( $customer );
		$this->manager->set_auth_scope( $scope );

		$response = $this->server->dispatch( $request );
		$this->assertErrorResponse( 'it_exchange_rest_invalid_shipping_method', $response, 400 );
	}
}