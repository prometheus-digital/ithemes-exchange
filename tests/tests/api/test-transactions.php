<?php
/**
 * Tests for the transactions API functions.
 *
 * @since   1.35
 * @license GPLv2
 */

/**
 * Class IT_Exchange_API_Transactions_Test
 *
 * @group transactions
 */
class IT_Exchange_API_Transactions_Test extends IT_Exchange_UnitTestCase {

	/**
	 * Get a transaction for testing.
	 *
	 * @param     stdClass object
	 * @param int $customer
	 *
	 * @return int
	 */
	protected function _get_txn( $object = null, $customer = 1 ) {

		if ( ! $object ) {
			$object = new stdClass();
		}

		if ( empty( $object->cart_id ) ) {
			$object->cart_id = 'test-cart-id';
		}

		return it_exchange_add_transaction( 'test-method', 'test-method-id', 'pending', $customer, $object );
	}

	public function test_get_transaction_method() {
		$this->assertEquals( 'test-method', it_exchange_get_transaction_method( $this->_get_txn() ) );
	}

	public function test_get_transaction_method_fallsback_to_query_var() {
		$_GET['transaction-method'] = 'fake-method';

		$this->assertEquals( 'fake-method', it_exchange_get_transaction_method() );

		unset( $_GET['transaction-method'] );
	}

	public function test_get_transaction_by_id() {
		$this->assertInstanceOf( 'IT_Exchange_Transaction', it_exchange_get_transaction( $this->_get_txn() ) );
	}

	public function test_get_transaction_by_post() {
		$this->assertInstanceOf( 'IT_Exchange_Transaction', it_exchange_get_transaction( get_post( $this->_get_txn() ) ) );
	}

	public function test_get_transaction_returns_false_for_invalid_post_type() {
		$this->assertFalse( it_exchange_get_transaction( get_post( 1 ) ) );
	}

	public function test_get_transaction_by_method_id() {
		$txn = $this->_get_txn();

		$this->assertEquals( $txn, it_exchange_get_transaction_by_method_id( 'test-method', 'test-method-id' )->ID );
	}

	public function test_get_transaction_by_cart_id() {
		$txn = $this->_get_txn();

		$this->assertEquals( $txn, it_exchange_get_transaction_by_cart_id( 'test-cart-id' )->ID );
	}

	public function test_expired_transient_transactions_are_deleted_upon_access() {

		$fn = function () {
			return 0;
		};

		add_filter( 'it_exchange_transient_transaction_expiry', $fn );

		it_exchange_update_transient_transaction( 'test-method', 'test-temp-id', false, new stdClass(), false );

		usleep( 750000 ); // yeah, life sucks

		WP_Mock::wpFunction( 'it_exchange_delete_transient_transaction', array(
			'args'  => array( 'test-method', 'test-temp-id' ),
			'times' => 1
		) );

		$this->assertFalse( it_exchange_get_transient_transaction( 'test-method', 'test-temp-id' ) );

		remove_filter( 'it_exchange_transient_transaction_expiry', $fn );
	}

	public function test_delete_transient_transaction() {

		it_exchange_update_transient_transaction( 'test-method', 'test-temp-id', false, new stdClass(), false );
		it_exchange_delete_transient_transaction( 'test-method', 'test-temp-id' );

		$this->assertFalse( it_exchange_get_transient_transaction( 'test-method', 'test-temp-id' ) );
	}

	public function test_add_transaction() {

		// this is verging on implementation details...
		$txn_object              = new stdClass();
		$txn_object->customer_ip = '127.0.0.1';
		$txn_object->cart_id     = it_exchange_create_cart_id();

		$meta = array(
			'_it_exchange_transaction_method'    => 'test-method',
			'_it_exchange_transaction_method_id' => 'test-method-id',
			'_it_exchange_transaction_status'    => 'pending',
			'_it_exchange_customer_id'           => 1,
			'_it_exchange_customer_ip'           => '127.0.0.1',
			'_it_exchange_cart_object'           => $txn_object,
			'_it_exchange_cart_id'               => $txn_object->cart_id
		);

		$txn = it_exchange_add_transaction( 'test-method', 'test-method-id', 'pending', 1, $txn_object );

		foreach ( $meta as $key => $value ) {
			$this->assertEquals( $value, get_post_meta( $txn, $key, true ), $key );
		}

		$this->assertNotFalse( get_post_meta( $txn, '_it_exchange_transaction_hash', true ) );
	}

	public function test_add_transaction_adds_txn_to_product() {

		$product = self::factory()->post->create( array(
			'post_type' => 'it_exchange_prod'
		) );

		$object = (object) array(
			'products' => array(
				"$product-test-product-hash" => array(
					'product_id' => $product
				)
			),
			'cart_id'  => it_exchange_create_cart_id()
		);

		$txn = it_exchange_add_transaction( 'test-method', 'test-method-id', 'pending', 1, $object );

		$product_purchases = get_post_meta( $product, '_it_exchange_transaction_id' );

		$this->assertNotFalse( array_search( $txn, $product_purchases ) );
	}

	public function test_add_transaction_adds_txn_to_user() {
		$object = (object) array(
			'cart_id' => it_exchange_create_cart_id()
		);

		$customer = $this->getMockBuilder( 'IT_Exchange_Customer' )->disableOriginalConstructor()->getMock();
		$customer->expects( $this->once() )->method( 'add_transaction_to_user' );
		$customer->method( 'is_wp_user' )->willReturn( true );

		it_exchange_add_transaction( 'test-method', 'test-method-id', 'pending', $customer, $object );
	}

	public function test_transaction_with_dupe_method_id_rejected() {
		$this->_get_txn();
		$this->assertFalse( $this->_get_txn() );
	}

	public function test_add_child_transaction() {

		$object = (object) array(
			'total' => '5.00'
		);

		$parent = $this->_get_txn();

		$meta = array(
			'_it_exchange_transaction_method'    => 'test-method',
			'_it_exchange_transaction_method_id' => 'test-method-child-id',
			'_it_exchange_transaction_status'    => 'pending',
			'_it_exchange_customer_id'           => 1,
			'_it_exchange_parent_tx_id'          => $parent,
			'_it_exchange_cart_object'           => $object
		);

		$child = it_exchange_add_child_transaction( 'test-method', 'test-method-child-id', 'pending', 1, $parent, $object );

		foreach ( $meta as $key => $value ) {
			$this->assertEquals( $value, get_post_meta( $child, $key, true ), $key );
		}

		$this->assertEquals( $parent, wp_get_post_parent_id( $child ) );
	}

	public function test_get_gateway_id_for_transaction() {
		$this->assertEquals( 'test-method-id', it_exchange_get_gateway_id_for_transaction( $this->_get_txn() ) );
	}

	public function test_get_transaction_id_from_hash() {

		$txn  = $this->_get_txn();
		$hash = it_exchange_get_transaction_hash( $txn );

		$this->assertEquals( $txn, it_exchange_get_transaction_id_from_hash( $hash ) );
	}

	public function test_update_transaction_status() {

		$txn = $this->getMockBuilder( 'IT_Exchange_Transaction' )->disableOriginalConstructor()->getMock();
		$txn->expects( $this->once() )->method( 'update_status' )->with( 'paid' );

		it_exchange_update_transaction_status( $txn, 'paid' );
	}

	public function test_get_transaction_status() {
		$this->assertEquals( 'pending', it_exchange_get_transaction_status( $this->_get_txn() ) );
	}

	public function test_get_status_options() {

		$action  = 'it_exchange_get_status_options_for_test-method_transaction';
		$options = array( 'test' );
		$fn      = function () use ( $options ) {
			return $options;
		};

		add_filter( $action, $fn );

		$this->assertEquals( $options, it_exchange_get_status_options_for_transaction( $this->_get_txn() ) );

		remove_filter( $action, $fn );
	}

	public function test_get_default_status() {

		$action = 'it_exchange_get_default_transaction_status_for_test-method';
		$status = 'test-status';
		$fn     = function () use ( $status ) {
			return $status;
		};

		add_filter( $action, $fn );

		$this->assertEquals( $status, it_exchange_get_default_transaction_status( $this->_get_txn() ) );

		remove_filter( $action, $fn );
	}

	public function test_get_transaction_instructions() {

		$action       = 'it_exchange_transaction_instructions_test-method';
		$instructions = 'The instructions';
		$fn           = function () use ( $instructions ) {
			return $instructions;
		};

		add_filter( $action, $fn );

		$this->assertEquals( $instructions, it_exchange_get_transaction_instructions( $this->_get_txn() ) );

		remove_filter( $action, $fn );
	}

	public function test_get_transaction_subtotal() {

		$txn = $this->_get_txn( (object) array(
			'sub_total' => '5.00'
		) );

		$this->assertEquals( '5.00', it_exchange_get_transaction_subtotal( $txn, false ) );
	}

	public function test_get_transaction_total() {

		$txn = $this->_get_txn( (object) array(
			'total' => '25.00'
		) );

		$this->assertEquals( '25.00', it_exchange_get_transaction_total( $txn, false, false ) );
	}

	public function test_get_transaction_currency() {

		$txn = $this->_get_txn( (object) array(
			'currency' => 'EUR'
		) );

		$this->assertEquals( 'EUR', it_exchange_get_transaction_currency( $txn ) );
	}

	public function test_get_transaction_currency_fallsback_to_global_setting() {
		$this->assertEquals( 'USD', it_exchange_get_transaction_currency( $this->_get_txn() ) );
	}

	public function test_get_transaction_coupons() {

		$coupons = array(
			'cart' => array(
				'id'   => 1,
				'code' => 'MYCODE'
			)
		);

		$txn = $this->_get_txn( (object) array(
			'coupons' => $coupons
		) );

		$this->assertEquals( $coupons, it_exchange_get_transaction_coupons( $txn ) );
	}

	public function test_get_transaction_coupons_total_discount() {

		$txn = $this->_get_txn( (object) array(
			'coupons_total_discount' => '24.99'
		) );

		$this->assertEquals( '24.99', it_exchange_get_transaction_coupons_total_discount( $txn, false ) );
	}

	public function test_add_refund_to_transaction() {

		$txn = $this->_get_txn();

		$amt  = '5.00';
		$time = current_time( 'mysql' );

		it_exchange_add_refund_to_transaction( $txn, $amt, $time );
		$refunds = it_exchange_get_transaction_refunds( $txn );

		$this->assertEquals( array(
			'amount'  => $amt,
			'date'    => $time,
			'options' => array()
		), $refunds[0] );
	}

	/**
	 * @depends test_add_refund_to_transaction
	 */
	public function test_has_transaction_refunds() {

		$txn = $this->_get_txn();

		$this->assertFalse( it_exchange_has_transaction_refunds( $txn ) );

		it_exchange_add_refund_to_transaction( $txn, '5.00' );

		$this->assertTrue( it_exchange_has_transaction_refunds( $txn ) );
	}

	/**
	 * @depends test_add_refund_to_transaction
	 */
	public function test_get_transaction_refunds_total() {

		$txn = $this->_get_txn();

		$this->assertEquals( 0, it_exchange_get_transaction_refunds_total( $txn, false ) );

		it_exchange_add_refund_to_transaction( $txn, '4.99' );
		it_exchange_add_refund_to_transaction( $txn, '3.25' );

		$this->assertEquals( '8.24', it_exchange_get_transaction_refunds_total( $txn, false ) );
	}

	/**
	 * @depends test_add_refund_to_transaction
	 */
	public function test_get_transaction_total_without_refunds() {

		$txn = $this->_get_txn( (object) array(
			'total' => '25.00'
		) );

		it_exchange_add_refund_to_transaction( $txn, '5.00' );

		$this->assertEquals( '20.00', it_exchange_get_transaction_total( $txn, false, true ) );
	}

	public function test_get_transaction_description() {

		$desc = 'My description';
		$txn  = $this->_get_txn( (object) array(
			'description' => $desc
		) );

		$this->assertEquals( $desc, it_exchange_get_transaction_description( $txn ) );
	}

	public function test_get_transaction_customer() {

		$customer = it_exchange_get_transaction_customer( $this->_get_txn() );

		$this->assertInstanceOf( 'IT_Exchange_Customer', $customer );
		$this->assertEquals( 1, $customer->id );
	}

	public function test_get_transaction_customer_display_name() {

		$name = get_user_by( 'id', 1 )->display_name;

		$this->assertEquals( $name, it_exchange_get_transaction_customer_display_name( $this->_get_txn() ) );
	}

	public function test_get_deleted_transaction_customer_display_name() {
		$this->assertEquals( 'Deleted Customer', it_exchange_get_transaction_customer_display_name( $this->_get_txn( null, 0 ) ) );
	}

	public function test_get_transaction_customer_id() {
		$this->assertEquals( 1, it_exchange_get_transaction_customer_id( $this->_get_txn() ) );
	}

	public function test_get_transaction_customer_email() {
		$this->assertEquals( WP_TESTS_EMAIL, it_exchange_get_transaction_customer_email( $this->_get_txn() ) );
	}

	public function test_get_transaction_customer_ip() {

		$txn = $this->_get_txn( (object) array(
			'customer_ip' => '127.0.0.1'
		) );

		$this->assertEquals( '127.0.0.1', it_exchange_get_transaction_customer_ip_address( $txn, false ) );
	}

	public function test_get_customer_admin_profile_url() {

		$url = it_exchange_get_transaction_customer_admin_profile_url( $this->_get_txn() );

		parse_str( parse_url( $url, PHP_URL_QUERY ), $args );

		$this->assertArrayHasKey( 'user_id', $args, 'Missing user ID' );
		$this->assertEquals( 1, $args['user_id'], 'Invalid user ID' );

		$this->assertArrayHasKey( 'it_exchange_customer_data', $args, 'Missing customer data flag.' );
		$this->assertEquals( 1, $args['it_exchange_customer_data'], 'Invalid customer data flag.' );

		$this->assertArrayHasKey( 'tab', $args, 'Missing tab.' );
		$this->assertEquals( 'transactions', $args['tab'], 'Invalid tab.' );
	}

	public function test_get_transaction_order_number() {

		$transaction     = $this->getMockBuilder( 'IT_Exchange_Transaction' )->disableOriginalConstructor()->getMock();
		$transaction->ID = 1;

		$this->assertEquals( '#000001', it_exchange_get_transaction_order_number( $transaction ) );
	}

	public function test_get_transaction_shipping_address() {

		$shipping = array(
			'first-name' => 'John',
			'last-name'  => 'Doe'
		);

		$txn = $this->_get_txn( (object) array(
			'shipping_address' => $shipping
		) );

		$this->assertEquals( $shipping, it_exchange_get_transaction_shipping_address( $txn ) );
	}

	public function test_get_transaction_billing_address() {

		$billing = array(
			'first-name' => 'John',
			'last-name'  => 'Doe'
		);

		$txn = $this->_get_txn( (object) array(
			'billing_address' => $billing
		) );

		$this->assertEquals( $billing, it_exchange_get_transaction_billing_address( $txn ) );
	}

	public function test_get_transaction_products() {

		$product = self::factory()->post->create( array(
			'post_type' => 'it_exchange_prod'
		) );

		$products = array(
			"$product-test-product-hash" => array(
				'product_id' => $product
			)
		);

		$txn = $this->_get_txn( (object) array(
			'products' => $products,
		) );

		$this->assertEquals( $products, it_exchange_get_transaction_products( $txn ) );
	}

	public function test_get_transaction_product() {

		$product = self::factory()->post->create( array(
			'post_type' => 'it_exchange_prod'
		) );

		$products = array(
			"$product-test-product-hash" => array(
				'product_id' => $product
			)
		);

		$txn = $this->_get_txn( (object) array(
			'products' => $products,
		) );

		$this->assertEquals( reset( $products ), it_exchange_get_transaction_product( $txn, "$product-test-product-hash" ) );
	}

	public function test_get_transaction_product_feature() {

		$product = self::factory()->post->create( array(
			'post_type' => 'it_exchange_prod'
		) );

		$product_data = array(
			'product_id'   => $product,
			'product_name' => 'My Name'
		);

		$this->assertEquals( 'My Name', it_exchange_get_transaction_product_feature( $product_data, 'product_name' ) );
		$this->assertEquals( 'My Name', it_exchange_get_transaction_product_feature( $product_data, 'title' ) );
		$this->assertEquals( 'My Name', it_exchange_get_transaction_product_feature( $product_data, 'name' ) );
		$this->assertEquals( $product, it_exchange_get_transaction_product_feature( $product_data, 'product_id' ) );
	}

	public function test_update_method_id() {

		$txn = $this->_get_txn();

		it_exchange_update_transaction_method_id( $txn, 'new-test-method-id' );

		$this->assertEquals( 'new-test-method-id', it_exchange_get_transaction_method_id( $txn ) );
	}


}