<?php
/**
 * Contains tests for the cart API functions
 *
 * @since   1.35
 * @license GPLv2
 */

/**
 * Class IT_Exchange_API_Cart_Test
 *
 * @group cart-api
 */
class IT_Exchange_API_Cart_Test extends IT_Exchange_UnitTestCase {
	/**
	 * Do custom initialization.
	 */
	public function setUp() {
		parent::setUp();

		$GLOBALS['it_exchange']['session'] = new IT_Exchange_Mock_Session();
	}

	public function test_add_get_cart_data() {

		it_exchange_update_cart_data( 'test-key', array( 'test' => 'value' ) );
		$data = it_exchange_get_cart_data( 'test-key' );
		$this->assertArrayHasKey( 'test', $data );
		$this->assertEquals( 'value', $data['test'] );
	}

	public function test_get_cart_data_returns_empty_array_for_invalid_key() {

		$data = it_exchange_get_cart_data( 'non-existent' );

		$this->assertEmpty( $data );
		$this->assertInternalType( 'array', $data );
	}

	public function test_remove_cart_data() {

		it_exchange_update_cart_data( 'test-key', array( 'data' ) );
		$this->assertNotEmpty( it_exchange_get_cart_data( 'test-key' ) );
		it_exchange_remove_cart_data( 'test-key' );
		$this->assertEmpty( it_exchange_get_cart_data( 'test-key' ) );
	}

	public function test_get_cart_products_always_returns_empty_cart() {

		$products = it_exchange_get_cart_products();

		$this->assertEmpty( $products );
		$this->assertInternalType( 'array', $products );
	}

	public function test_add_get_cart_products() {

		$ID = it_exchange_add_product( array(
			'type'  => 'simple-product-type',
			'title' => 'My Product'
		) );

		it_exchange_add_cart_product( $ID . '-hash', array(
			'product_id' => $ID
		) );

		$products = it_exchange_get_cart_products();
		$this->assertArrayHasKey( $ID . '-hash', $products );
		$this->assertArrayHasKey( 'product_id', $products[ $ID . '-hash' ] );
		$this->assertEquals( $ID, $products[ $ID . '-hash' ]['product_id'] );
	}

	public function test_update_cart_product() {

		$ID = it_exchange_add_product( array(
			'type'  => 'simple-product-type',
			'title' => 'My Product'
		) );

		it_exchange_add_cart_product( $ID . '-hash', array(
			'product_id' => $ID
		) );
		$new_data = array(
			'product_id' => $ID,
			'quantity'   => 2
		);
		it_exchange_update_cart_product( $ID . '-hash', $new_data );

		$products = it_exchange_get_cart_products();
		$this->assertEquals( $new_data, $products[ $ID . '-hash' ] );
	}

	public function test_update_cart_products_adds_product() {

		$ID = it_exchange_add_product( array(
			'type'  => 'simple-product-type',
			'title' => 'My Product'
		) );

		it_exchange_update_cart_product( $ID . '-hash', array(
			'product_id' => $ID
		) );

		$products = it_exchange_get_cart_products();
		$this->assertArrayHasKey( $ID . '-hash', $products );
		$this->assertEquals( $ID, $products[ $ID . '-hash' ]['product_id'] );
	}

	public function test_delete_cart_product() {

		$ID = it_exchange_add_product( array(
			'type'  => 'simple-product-type',
			'title' => 'My Product'
		) );

		it_exchange_add_cart_product( $ID . '-hash', array(
			'product_id' => $ID
		) );

		it_exchange_delete_cart_product( $ID . '-hash' );

		$this->assertEmpty( it_Exchange_get_cart_products() );
	}

	public function test_delete_cart_product_non_existent_product() {

		it_exchange_delete_cart_product( 'i-dont-exist' );

		$this->assertEmpty( it_exchange_get_cart_products() );
	}

	public function test_get_cart_product() {

		$ID = it_exchange_add_product( array(
			'type'  => 'simple-product-type',
			'title' => 'My Product'
		) );

		$product = array(
			'product_id' => $ID
		);

		it_exchange_add_cart_product( $ID . '-hash', $product );

		$this->assertEquals( $product, it_exchange_get_cart_product( $ID . '-hash' ) );
	}

	public function test_get_cart_product_returns_false_for_invalid_product() {
		$this->assertFalse( it_exchange_get_cart_product( 'i-dont-exist' ) );
	}

	public function test_is_current_product_in_cart() {

		$ID = it_exchange_add_product( array(
			'type'  => 'simple-product-type',
			'title' => 'My Product'
		) );

		WP_Mock::wpFunction( 'it_exchange_get_the_product_id', array(
			'return' => $ID
		) );

		$this->assertFalse( it_exchange_is_current_product_in_cart() );
		it_exchange_add_cart_product( $ID . '-hash', array(
			'product_id' => $ID
		) );
		$this->assertTrue( it_exchange_is_current_product_in_cart() );
	}

	public function test_is_current_product_in_cart_checks_sw_query_var() {

		$ID = it_exchange_add_product( array(
			'type'  => 'simple-product-type',
			'title' => 'My Product'
		) );
		it_exchange_add_cart_product( $ID . '-hash', array(
			'product_id' => $ID
		) );

		$_GET['sw-product'] = $ID;

		$this->assertTrue( it_exchange_is_current_product_in_cart() );

		unset( $_GET['sw-product'] );
	}

	public function test_is_product_in_cart() {

		$ID = it_exchange_add_product( array(
			'type'  => 'simple-product-type',
			'title' => 'My Product'
		) );

		$this->assertFalse( it_exchange_is_product_in_cart( $ID ) );
		it_exchange_add_cart_product( $ID . '-hash', array(
			'product_id' => $ID
		) );
		$this->assertTrue( it_exchange_is_product_in_cart( $ID ) );
	}

	public function test_add_product_to_shopping_cart() {

		$ID = it_exchange_add_product( array(
			'type'  => 'simple-product-type',
			'title' => 'My Product'
		) );

		$cart_product_id = it_exchange_add_product_to_shopping_cart( $ID, 1, true );

		$this->assertTrue( it_exchange_is_product_in_cart( $ID ) );

		$product = it_exchange_get_cart_product( $cart_product_id );


		$keys = array(
			'product_cart_id',
			'product_id',
			'itemized_data',
			'additional_data',
			'itemized_hash',
			'count'
		);

		foreach ( $keys as $key ) {
			$this->assertArrayHasKey( $key, $product );
		}

		$this->assertEquals( 1, $product['count'] );
	}

	public function test_add_product_to_shopping_cart_min_quantity_0() {

		$ID = it_exchange_add_product( array(
			'type'  => 'simple-product-type',
			'title' => 'My Product'
		) );

		$cart_product_id = it_exchange_add_product_to_shopping_cart( $ID, 0, true );

		$product = it_exchange_get_cart_product( $cart_product_id );

		$this->assertEquals( 1, $product['count'] );
	}

	public function test_add_product_to_shopping_cart_bumps_quantity() {

		$ID = it_exchange_add_product( array(
			'type'  => 'simple-product-type',
			'title' => 'My Product'
		) );

		$cart_product_id = it_exchange_add_product_to_shopping_cart( $ID, 1, true );
		$this->assertEquals( $cart_product_id, it_exchange_add_product_to_shopping_cart( $ID, 2, true ) );

		$product = it_exchange_get_cart_product( $cart_product_id );
		$this->assertEquals( 3, $product['count'] );
	}

	public function test_add_product_to_shopping_cart_multi_item_cart_enforced() {

		WP_Mock::wpFunction( 'it_exchange_is_multi_item_cart_allowed', array(
			'return' => false
		) );

		$p1 = it_exchange_add_product( array(
			'type'  => 'simple-product-type',
			'title' => 'My Product'
		) );

		$p2 = it_exchange_add_product( array(
			'type'  => 'simple-product-type',
			'title' => 'My Product 2'
		) );

		$cp1 = it_exchange_add_product_to_shopping_cart( $p1, 1, true );
		$cp2 = it_exchange_add_product_to_shopping_cart( $p2, 1, true );

		$this->assertFalse( it_exchange_get_cart_product( $cp1 ) );
		$this->assertNotEmpty( it_exchange_get_cart_product( $cp2 ) );
	}

	public function test_add_product_to_shopping_cart_bumps_quantity_if_multi_item_cart_disabled() {

		WP_Mock::wpFunction( 'it_exchange_is_multi_item_cart_allowed', array(
			'return' => false
		) );

		$p1 = it_exchange_add_product( array(
			'type'  => 'simple-product-type',
			'title' => 'My Product'
		) );

		$cart_product_id = it_exchange_add_product_to_shopping_cart( $p1, 1, true );
		$this->assertEquals( $cart_product_id, it_exchange_add_product_to_shopping_cart( $p1, 2, true ) );

		$product = it_exchange_get_cart_product( $cart_product_id );
		$this->assertEquals( 3, $product['count'] );
	}

	public function test_add_product_to_shopping_cart_quantity_capped_at_1_when_purchase_quantity_disabled() {

		$product = $this->getMockBuilder( 'IT_Exchange_Product' )->disableOriginalConstructor()->getMock();
		$product->method( 'supports_feature' )->with( 'purchase-quantity' )->willReturn( false );
		$product->ID = 1;

		$cart_product_id = it_exchange_add_product_to_shopping_cart( $product, 2, true );

		$data = it_exchange_get_cart_product( $cart_product_id );
		$this->assertEquals( 1, $data['count'] );
	}

	public function test_add_product_to_shopping_cart_quantity_capped_at_inventory() {

		$product = $this->getMockBuilder( 'IT_Exchange_Product' )->disableOriginalConstructor()->getMock();
		$product->method( 'supports_feature' )->willReturnMap( array(
			array( 'purchase-quantity', array(), true ),
			array( 'inventory', array(), true )
		) );
		$product->method( 'get_feature' )->willReturnMap( array(
			array( 'purchase-quantity', array(), '' ),
			array( 'inventory', array(), 2 )
		) );
		$product->ID = 1;

		$cart_product_id = it_exchange_add_product_to_shopping_cart( $product, 3, true );

		$data = it_exchange_get_cart_product( $cart_product_id );
		$this->assertEquals( 2, $data['count'] );
	}

	public function test_add_product_to_shopping_cart_quantity_capped_to_max_purchase_quantity() {

		$product = $this->getMockBuilder( 'IT_Exchange_Product' )->disableOriginalConstructor()->getMock();
		$product->method( 'supports_feature' )->willReturnMap( array(
			array( 'purchase-quantity', array(), true ),
			array( 'inventory', array(), true )
		) );
		$product->method( 'get_feature' )->willReturnMap( array(
			array( 'purchase-quantity', array(), 2 ),
			array( 'inventory', array(), 5 )
		) );
		$product->ID = 1;

		$cart_product_id = it_exchange_add_product_to_shopping_cart( $product, 3, true );

		$data = it_exchange_get_cart_product( $cart_product_id );
		$this->assertEquals( 2, $data['count'] );
	}

	public function test_add_product_to_shopping_cart_quantity_capped_to_inventory_with_quantity_greater_than_max() {

		$product = $this->getMockBuilder( 'IT_Exchange_Product' )->disableOriginalConstructor()->getMock();
		$product->method( 'supports_feature' )->willReturnMap( array(
			array( 'purchase-quantity', array(), true ),
			array( 'inventory', array(), true )
		) );
		$product->method( 'get_feature' )->willReturnMap( array(
			array( 'purchase-quantity', array(), 3 ),
			array( 'inventory', array(), 2 )
		) );
		$product->ID = 1;

		$cart_product_id = it_exchange_add_product_to_shopping_cart( $product, 5, true );

		$data = it_exchange_get_cart_product( $cart_product_id );
		$this->assertEquals( 2, $data['count'] );
	}

	public function test_add_product_to_shopping_cart_quantity_not_capped_if_inventory_disabled_and_no_max_quantity() {

		$product = $this->getMockBuilder( 'IT_Exchange_Product' )->disableOriginalConstructor()->getMock();
		$product->method( 'supports_feature' )->willReturnMap( array(
			array( 'purchase-quantity', array(), true ),
			array( 'inventory', array(), false )
		) );
		$product->method( 'get_feature' )->willReturnMap( array(
			array( 'purchase-quantity', array(), '' ),
			array( 'inventory', array(), '' )
		) );
		$product->ID = 1;

		$cart_product_id = it_exchange_add_product_to_shopping_cart( $product, 5, true );

		$data = it_exchange_get_cart_product( $cart_product_id );
		$this->assertEquals( 5, $data['count'] );
	}

	public function test_add_product_to_shopping_cart_quantity_capped_to_purchase_quantity_when_inventory_disabled() {

		$product = $this->getMockBuilder( 'IT_Exchange_Product' )->disableOriginalConstructor()->getMock();
		$product->method( 'supports_feature' )->willReturnMap( array(
			array( 'purchase-quantity', array(), true ),
			array( 'inventory', array(), false )
		) );
		$product->method( 'get_feature' )->willReturnMap( array(
			array( 'purchase-quantity', array(), 3 ),
			array( 'inventory', array(), 0 )
		) );
		$product->ID = 1;

		$cart_product_id = it_exchange_add_product_to_shopping_cart( $product, 5, true );

		$data = it_exchange_get_cart_product( $cart_product_id );
		$this->assertEquals( 3, $data['count'] );
	}

	public function test_update_cart_product_quantity_add_to_existing() {

		$ID = it_exchange_add_product( array(
			'type'  => 'simple-product-type',
			'title' => 'My Product'
		) );

		$cart_product_id = it_exchange_add_product_to_shopping_cart( $ID, 1, true );
		it_exchange_update_cart_product_quantity( $cart_product_id, 5, true );

		$product = it_exchange_get_cart_product( $cart_product_id );
		$this->assertEquals( 6, $product['count'] );
	}

	public function test_update_cart_product_quantity_overwrite() {

		$ID = it_exchange_add_product( array(
			'type'  => 'simple-product-type',
			'title' => 'My Product'
		) );

		$cart_product_id = it_exchange_add_product_to_shopping_cart( $ID, 1, true );
		it_exchange_update_cart_product_quantity( $cart_product_id, 5, false );

		$product = it_exchange_get_cart_product( $cart_product_id );
		$this->assertEquals( 5, $product['count'] );
	}

	public function test_update_cart_product_quantity_removes_product_if_quantity_less_than_1() {

		$ID = it_exchange_add_product( array(
			'type'  => 'simple-product-type',
			'title' => 'My Product'
		) );

		$cart_product_id = it_exchange_add_product_to_shopping_cart( $ID, 1, true );
		it_exchange_update_cart_product_quantity( $cart_product_id, 0, false );

		$this->assertEmpty( it_exchange_get_cart_product( $cart_product_id ) );
	}

	public function test_empty_shopping_cart() {

		$ID = it_exchange_add_product( array(
			'type'  => 'simple-product-type',
			'title' => 'My Product'
		) );

		it_exchange_add_product_to_shopping_cart( $ID );
		it_exchange_empty_shopping_cart();

		$this->assertEmpty( it_exchange_get_cart_products() );
	}

	public function test_get_cart_product_title() {

		$ID = it_exchange_add_product( array(
			'type'  => 'simple-product-type',
			'title' => 'My Product'
		) );

		$this->assertEquals( 'My Product', it_exchange_get_cart_product_title( array(
			'product_id' => $ID
		) ) );
	}

	public function test_get_cart_product_quantity() {

		$ID = it_exchange_add_product( array(
			'type'  => 'simple-product-type',
			'title' => 'My Product'
		) );

		$cart_id = it_exchange_add_product_to_shopping_cart( $ID, 2, true );

		$this->assertEquals( 2, it_exchange_get_cart_product_quantity( it_exchange_Get_cart_product( $cart_id ) ) );
	}

	public function test_get_cart_product_quantity_defaults_to_0() {
		$this->assertEquals( 0, it_exchange_get_cart_product_quantity( array() ) );
	}

	public function test_get_cart_product_quantity_by_product_id() {

		$ID = it_exchange_add_product( array(
			'type'  => 'simple-product-type',
			'title' => 'My Product'
		) );

		it_exchange_add_product_to_shopping_cart( $ID, 2 );

		$this->assertEquals( 2, it_exchange_get_cart_product_quantity_by_product_id( $ID ) );
	}

	public function test_get_cart_products_count() {

		$p1 = it_exchange_add_product( array(
			'type'  => 'simple-product-type',
			'title' => 'My Product'
		) );

		it_exchange_add_product_to_shopping_cart( $p1, 3 );

		$p2 = it_exchange_add_product( array(
			'type'  => 'simple-product-type',
			'title' => 'My Product'
		) );

		it_exchange_add_product_to_shopping_cart( $p2, 1 );

		$this->assertEquals( 2, it_exchange_get_cart_products_count() );
	}

	public function test_get_cart_products_count_true_count() {

		$p1 = it_exchange_add_product( array(
			'type'  => 'simple-product-type',
			'title' => 'My Product'
		) );

		it_exchange_add_product_to_shopping_cart( $p1, 3 );

		$p2 = it_exchange_add_product( array(
			'type'  => 'simple-product-type',
			'title' => 'My Product'
		) );

		it_exchange_add_product_to_shopping_cart( $p2, 1 );

		$this->assertEquals( 4, it_exchange_get_cart_products_count( true ) );
	}

	public function test_get_cart_products_with_feature() {

		$p1 = it_exchange_add_product( array(
			'type'        => 'simple-product-type',
			'title'       => 'My Product',
			'description' => 'Test'
		) );

		it_exchange_add_product_to_shopping_cart( $p1, 3 );

		$p2 = it_exchange_add_product( array(
			'type'  => 'simple-product-type',
			'title' => 'My Product'
		) );

		it_exchange_add_product_to_shopping_cart( $p2, 1 );

		$this->assertEquals( 3, it_exchange_get_cart_products_count( true, 'description' ) );
	}

	public function test_get_cart_weight() {

		$p1 = it_exchange_add_product( array(
			'type'      => 'physical-product-type',
			'title'     => 'My Product',
			'post_meta' => array(
				'_it_exchange_core_weight' => array(
					'weight' => 3
				)
			)
		) );

		it_exchange_add_product_to_shopping_cart( $p1, 3 );

		$p2 = it_exchange_add_product( array(
			'type'      => 'physical-product-type',
			'title'     => 'My Product 2',
			'post_meta' => array(
				'_it_exchange_core_weight' => array(
					'weight' => 2
				)
			)
		) );

		it_exchange_add_product_to_shopping_cart( $p2, 1 );

		$this->assertEquals( 11, it_exchange_get_cart_weight() );
	}

	public function test_get_cart_product_base_price() {

		$ID = it_exchange_add_product( array(
			'type'       => 'simple-product-type',
			'title'      => 'My Product',
			'base-price' => '5.00'
		) );

		$cart_id = it_exchange_add_product_to_shopping_cart( $ID, 1, true );
		$product = it_exchange_get_cart_product( $cart_id );

		$this->assertEquals( '5.00', it_exchange_get_cart_product_base_price( $product, false ) );
	}

	public function test_get_cart_product_subtotal() {

		$ID = it_exchange_add_product( array(
			'type'       => 'simple-product-type',
			'title'      => 'My Product',
			'base-price' => '5.00'
		) );

		$cart_id = it_exchange_add_product_to_shopping_cart( $ID, 2, true );
		$product = it_exchange_get_cart_product( $cart_id );

		$this->assertEquals( '10.00', it_exchange_get_cart_product_subtotal( $product, false ) );
	}

	public function test_get_cart_subtotal() {

		$p1 = it_exchange_add_product( array(
			'type'       => 'simple-product-type',
			'title'      => 'My Product',
			'base-price' => '5.00'
		) );

		it_exchange_add_product_to_shopping_cart( $p1, 3 );

		$p2 = it_exchange_add_product( array(
			'type'       => 'simple-product-type',
			'title'      => 'My Product',
			'base-price' => '7.50'
		) );

		it_exchange_add_product_to_shopping_cart( $p2, 1 );

		$this->assertEquals( '22.50', it_exchange_get_cart_subtotal( false ) );
	}

	public function test_get_cart_shipping_address_defaults() {

		wp_set_current_user( 1 );
		$user = wp_get_current_user();

		$shipping = it_exchange_get_cart_shipping_address();

		$keys = array(
			'first-name',
			'last-name',
			'company-name',
			'address1',
			'address2',
			'city',
			'state',
			'zip',
			'country',
			'email',
			'phone'
		);

		foreach ( $keys as $key ) {
			$this->assertArrayHasKey( $key, $shipping );
		}

		$this->assertEquals( $user->first_name, $shipping['first-name'] );
		$this->assertEquals( $user->last_name, $shipping['last-name'] );
		$this->assertEquals( $user->user_email, $shipping['email'] );

		wp_set_current_user( 0 );
	}

	public function test_get_cart_billing_address_defaults() {

		wp_set_current_user( 1 );
		$user = wp_get_current_user();

		$billing = it_exchange_get_cart_shipping_address();

		$keys = array(
			'first-name',
			'last-name',
			'company-name',
			'address1',
			'address2',
			'city',
			'state',
			'zip',
			'country',
			'email',
			'phone'
		);

		foreach ( $keys as $key ) {
			$this->assertArrayHasKey( $key, $billing );
		}

		$this->assertEquals( $user->first_name, $billing['first-name'] );
		$this->assertEquals( $user->last_name, $billing['last-name'] );
		$this->assertEquals( $user->user_email, $billing['email'] );

		wp_set_current_user( 0 );
	}

	public function test_get_update_cart_id() {

		$id = it_exchange_create_cart_id();
		it_exchange_update_cart_id( $id );

		$this->assertEquals( $id, it_exchange_get_cart_id() );
	}

	public function test_remove_cart_id() {

		$id = it_exchange_create_cart_id();
		it_exchange_update_cart_id( $id );
		it_exchange_remove_cart_id();

		$this->assertFalse( it_exchange_get_cart_id() );
	}
}