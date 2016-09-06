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
 * @group transactions-api
 */
class IT_Exchange_API_Transactions_Test extends IT_Exchange_UnitTestCase {
	/**
	 * @inheritDoc
	 */
	public function setUp() {
		parent::setUp();

		$GLOBALS['it_exchange']['session']->clear_session();
	}

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
		$this->assertEquals( 'test-method', it_exchange_get_transaction_method( self::transaction_factory()->create() ) );
	}

	public function test_get_transaction_method_fallsback_to_query_var() {
		$_GET['transaction-method'] = 'fake-method';

		$this->assertEquals( 'fake-method', it_exchange_get_transaction_method() );

		unset( $_GET['transaction-method'] );
	}

	public function test_get_transaction_by_id() {
		$this->assertInstanceOf( 'IT_Exchange_Transaction', it_exchange_get_transaction( self::transaction_factory()->create() ) );
	}

	public function test_get_transaction_by_post() {
		$this->assertInstanceOf( 'IT_Exchange_Transaction', it_exchange_get_transaction( get_post( self::transaction_factory()->create() ) ) );
	}

	public function test_get_transaction_returns_false_for_invalid_post_type() {
		$this->assertFalse( it_exchange_get_transaction( self::factory()->post->create_and_get() ) );
	}

	public function test_get_transaction_by_method_id() {
		$txn = self::transaction_factory()->create_and_get();

		$this->assertEquals( $txn->ID, it_exchange_get_transaction_by_method_id( 'test-method', $txn->method_id )->ID );
	}

	public function test_get_transaction_by_cart_id() {
		$txn = self::transaction_factory()->create_and_get();

		$this->assertEquals( $txn->ID, it_exchange_get_transaction_by_cart_id( $txn->cart_id )->ID );
	}

	public function test_expired_transient_transactions_are_deleted_upon_access() {

		$fn = function () {
			return 0;
		};

		add_filter( 'it_exchange_transient_transaction_expiry', $fn );

		it_exchange_update_transient_transaction( 'test-method', 'test-temp-id', false, new stdClass(), false );

		sleep( 1 );

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

		WP_Mock::wpFunction( 'it_exchange_get_ip', array( 'return' => '127.0.0.1' ) );

		// this is verging on implementation details...
		$txn = $this->transaction_factory->create_and_get( array( 'method_id' => 'test-method-id' ) );

		$meta = array(
			'_it_exchange_transaction_method'    => 'test-method',
			'_it_exchange_transaction_method_id' => 'test-method-id',
			'_it_exchange_transaction_status'    => 'pending',
			'_it_exchange_customer_id'           => 1,
			'_it_exchange_customer_ip'           => '127.0.0.1',
			'_it_exchange_cart_id'               => $txn->cart_id,
		);

		foreach ( $meta as $key => $value ) {
			$this->assertEquals( $value, get_post_meta( $txn->ID, $key, true ), $key );
		}

		$this->assertNotFalse( get_post_meta( $txn->ID, '_it_exchange_transaction_hash', true ) );
		$this->assertInstanceOf( 'stdClass', get_post_meta( $txn->ID, '_it_exchange_cart_object', true ) );
	}

	public function test_add_transaction_adds_txn_to_product() {

		$product = self::factory()->post->create( array(
			'post_type' => 'it_exchange_prod'
		) );

		$cart = $this->cart();
		$cart->add_item( ITE_Cart_Product::create( it_exchange_get_product( $product ) ) );

		$txn = it_exchange_add_transaction( 'test-method', 'test-method-id', 'pending', 1, $cart );

		$product_purchases = get_post_meta( $product, '_it_exchange_transaction_id' );

		$this->assertNotFalse( array_search( $txn, $product_purchases ) );
	}

	public function test_add_transaction_adds_txn_to_user() {

		$object = $this->cart( 1, true );

		$customer = $this->getMockBuilder( 'IT_Exchange_Customer' )->disableOriginalConstructor()->getMock();
		$customer->expects( $this->once() )->method( 'add_transaction_to_user' );
		$customer->method( 'is_wp_user' )->willReturn( true );
		$customer->id = 1;

		it_exchange_add_transaction( 'test-method', 'test-method-id', 'pending', $customer, $object );
	}

	public function test_transaction_with_dupe_method_id_rejected() {
		self::transaction_factory()->create( array( 'method_id' => 'test-method-id' ) );
		$this->assertFalse( self::transaction_factory()->create( array( 'method_id' => 'test-method-id' ) ) );
	}

	public function test_add_child_transaction() {

		$object = (object) array(
			'total' => '5.00'
		);

		$parent = self::transaction_factory()->create();

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

	/**
	 * @expectedDeprecated it_exchange_get_gateway_id_for_transaction
	 */
	public function test_get_gateway_id_for_transaction() {
		$this->assertEquals( 'test-method-id', it_exchange_get_gateway_id_for_transaction(
			self::transaction_factory()->create_and_get( array( 'method_id' => 'test-method-id' ) )
		) );
	}

	public function test_get_method_id_for_transaction() {
		$this->assertEquals( 'test-method-id', it_exchange_get_transaction_method_id(
			self::transaction_factory()->create_and_get( array( 'method_id' => 'test-method-id' ) )
		) );
	}

	public function test_get_transaction_id_from_hash() {

		$txn  = self::transaction_factory()->create_and_get( array( 'method_id' => 'test-method-id' ) );
		$hash = $txn->hash;

		$this->assertEquals( $txn->ID, it_exchange_get_transaction_id_from_hash( $hash ) );
	}

	public function test_update_transaction_status() {

		$txn = self::transaction_factory()->create();

		it_exchange_update_transaction_status( $txn, 'paid' );

		$this->assertEquals( 'paid', it_exchange_get_transaction_status( $txn ) );
	}

	public function test_get_transaction_status() {
		$this->assertEquals( 'pending', it_exchange_get_transaction_status( self::transaction_factory()->create() ) );
	}

	public function test_get_status_options() {

		$action  = 'it_exchange_get_status_options_for_test-method_transaction';
		$options = array( 'test' );
		$fn      = function () use ( $options ) {
			return $options;
		};

		add_filter( $action, $fn );

		$this->assertEquals( $options, it_exchange_get_status_options_for_transaction( self::transaction_factory()->create() ) );

		remove_filter( $action, $fn );
	}

	public function test_get_default_status() {

		$action = 'it_exchange_get_default_transaction_status_for_test-method';
		$status = 'test-status';
		$fn     = function () use ( $status ) {
			return $status;
		};

		add_filter( $action, $fn );

		$this->assertEquals( $status, it_exchange_get_default_transaction_status( self::transaction_factory()->create() ) );

		remove_filter( $action, $fn );
	}

	public function test_get_transaction_instructions() {

		$action       = 'it_exchange_transaction_instructions_test-method';
		$instructions = 'The instructions';
		$fn           = function () use ( $instructions ) {
			return $instructions;
		};

		add_filter( $action, $fn );

		$this->assertEquals( $instructions, it_exchange_get_transaction_instructions( self::transaction_factory()->create() ) );

		remove_filter( $action, $fn );
	}

	/**
	 * @expectedDeprecated it_exchange_add_transaction
	 */
	public function test_get_transaction_subtotal() {

		$txn = $this->_get_txn( (object) array(
			'sub_total' => '5.00'
		) );

		$this->assertEquals( '5.00', it_exchange_get_transaction_subtotal( $txn, false ) );
	}

	/**
	 * @expectedDeprecated it_exchange_add_transaction
	 */
	public function test_get_transaction_total() {

		$txn = $this->_get_txn( (object) array(
			'total' => '25.00'
		) );

		$this->assertEquals( '25.00', it_exchange_get_transaction_total( $txn, false, false ) );
	}

	/**
	 * @expectedDeprecated it_exchange_add_transaction
	 */
	public function test_get_transaction_currency() {

		$txn = $this->_get_txn( (object) array(
			'currency' => 'EUR'
		) );

		$this->assertEquals( 'EUR', it_exchange_get_transaction_currency( $txn ) );
	}

	/**
	 * @expectedDeprecated it_exchange_add_transaction
	 */
	public function test_get_transaction_currency_fallsback_to_global_setting() {
		$this->assertEquals( 'USD', it_exchange_get_transaction_currency( $this->_get_txn() ) );
	}

	/**
	 * @expectedDeprecated it_exchange_add_transaction
	 */
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

	/**
	 * @expectedDeprecated it_exchange_add_transaction
	 */
	public function test_get_transaction_coupons_total_discount() {

		$txn = $this->_get_txn( (object) array(
			'coupons_total_discount' => '24.99'
		) );

		$this->assertEquals( '24.99', it_exchange_get_transaction_coupons_total_discount( $txn, false ) );
	}

	public function test_add_refund_to_transaction() {

		$txn = self::transaction_factory()->create();

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

		$txn = self::transaction_factory()->create();

		$this->assertFalse( it_exchange_has_transaction_refunds( $txn ) );

		it_exchange_add_refund_to_transaction( $txn, '5.00' );

		$this->assertTrue( it_exchange_has_transaction_refunds( $txn ) );
	}

	/**
	 * @depends test_add_refund_to_transaction
	 */
	public function test_get_transaction_refunds_total() {

		$txn = self::transaction_factory()->create();

		$this->assertEquals( 0, it_exchange_get_transaction_refunds_total( $txn, false ) );

		it_exchange_add_refund_to_transaction( $txn, '4.99' );
		it_exchange_add_refund_to_transaction( $txn, '3.25' );

		$this->assertEquals( '8.24', it_exchange_get_transaction_refunds_total( $txn, false ) );
	}

	/**
	 * @depends test_add_refund_to_transaction
	 */
	public function test_get_transaction_total_without_refunds() {

		$cart = $this->cart();
		$cart->add_item( ITE_Cart_Product::create( self::product_factory()->create_and_get( array(
			'base-price' => 25.00
		) ) ) );

		$txn = self::transaction_factory()->create( array( 'cart' => $cart ) );

		it_exchange_add_refund_to_transaction( $txn, '5.00' );

		$this->assertEquals( '20.00', it_exchange_get_transaction_total( $txn, false, true ) );
	}

	public function test_get_transaction_description() {

		$desc = 'My description';

		WP_Mock::wpFunction( 'it_exchange_get_cart_description', array( 'return' => $desc ) );

		$txn = self::transaction_factory()->create();

		$this->assertEquals( $desc, it_exchange_get_transaction_description( $txn ) );
	}

	public function test_get_transaction_customer() {

		$customer = it_exchange_get_transaction_customer( self::transaction_factory()->create() );

		$this->assertInstanceOf( 'IT_Exchange_Customer', $customer );
		$this->assertEquals( 1, $customer->id );
	}

	public function test_get_transaction_customer_display_name() {

		$name = get_user_by( 'id', 1 )->display_name;

		$this->assertEquals( $name, it_exchange_get_transaction_customer_display_name( self::transaction_factory()->create() ) );
	}

	public function test_get_deleted_transaction_customer_display_name() {
		$customer    = self::factory()->user->create();
		$transaction = self::transaction_factory()->create( array( 'customer' => $customer ) );
		wp_delete_user( $customer );

		$this->assertEquals( 'Deleted Customer', it_exchange_get_transaction_customer_display_name( $transaction ) );
	}

	public function test_get_transaction_customer_id() {
		$this->assertEquals( 1, it_exchange_get_transaction_customer_id( self::transaction_factory()->create() ) );
	}

	public function test_get_transaction_customer_email() {
		$this->assertEquals( WP_TESTS_EMAIL, it_exchange_get_transaction_customer_email( self::transaction_factory()->create() ) );
	}

	public function test_get_transaction_customer_ip() {

		WP_Mock::wpFunction( 'it_exchange_get_ip', array( 'return' => '127.0.0.1' ) );

		$txn = self::transaction_factory()->create();

		$this->assertEquals( '127.0.0.1', it_exchange_get_transaction_customer_ip_address( $txn, false ) );
	}

	public function test_get_customer_admin_profile_url() {

		$url = it_exchange_get_transaction_customer_admin_profile_url( self::transaction_factory()->create() );

		parse_str( parse_url( $url, PHP_URL_QUERY ), $args );

		$this->assertArrayHasKey( 'user_id', $args, 'Missing user ID' );
		$this->assertEquals( 1, $args['user_id'], 'Invalid user ID' );

		$this->assertArrayHasKey( 'it_exchange_customer_data', $args, 'Missing customer data flag.' );
		$this->assertEquals( 1, $args['it_exchange_customer_data'], 'Invalid customer data flag.' );

		$this->assertArrayHasKey( 'tab', $args, 'Missing tab.' );
		$this->assertEquals( 'transactions', $args['tab'], 'Invalid tab.' );
	}

	public function test_get_transaction_order_number() {

		$transaction = $this->getMockBuilder( 'IT_Exchange_Transaction' )->disableOriginalConstructor()->setMethods( array( 'get_ID' ) )->getMock();
		$transaction->method( 'get_ID' )->willReturn( 1 );

		$this->assertEquals( '#000001', it_exchange_get_transaction_order_number( $transaction ) );
	}

	public function test_get_transaction_shipping_address() {

		add_filter( 'it_exchange_shipping_address_purchase_requirement_enabled', '__return_true' );

		IT_Exchange_Shipping::register_shipping_address_purchase_requirement();
		IT_Exchange_Shipping::register_shipping_method_purchase_requirement();

		$shipping = array(
			'first-name' => 'John',
			'last-name'  => 'Doe'
		);

		$txn = $this->transaction_factory->create( array( 'shipping_address' => $shipping ) );

		$saved = it_exchange_get_transaction_shipping_address( $txn );

		$this->assertEquals( 'John', $saved['first-name'] );
		$this->assertEquals( 'Doe', $saved['last-name'] );
	}

	public function test_get_transaction_billing_address() {

		$billing = array(
			'first-name' => 'John',
			'last-name'  => 'Doe'
		);

		$txn = $this->transaction_factory->create( array( 'billing_address' => $billing ) );

		$saved = it_exchange_get_transaction_billing_address( $txn );

		$this->assertEquals( 'John', $saved['first-name'] );
		$this->assertEquals( 'Doe', $saved['last-name'] );
	}

	public function test_get_transaction_products() {

		$product = self::product_factory()->create_and_get();
		$cart    = $this->cart();
		$cart->add_item( ITE_Cart_Product::create( $product ) );

		$txn       = self::transaction_factory()->create( array( 'cart' => $cart ) );
		$products  = it_exchange_get_transaction_products( $txn );
		$t_product = reset( $products );

		$this->assertInternalType( 'array', $products );
		$this->assertStringStartsWith( (string) $product->ID, key( $products ) );
		$this->assertEquals( $product->ID, $t_product['product_id'] );
		$this->assertEquals( $product->post_title, $t_product['product_name'] );
	}

	/**
	 * @depends test_get_transaction_products
	 */
	public function test_get_transaction_product() {

		$product = self::product_factory()->create_and_get();
		$cart    = $this->cart();
		$cart->add_item( ITE_Cart_Product::create( $product ) );

		$txn       = self::transaction_factory()->create( array( 'cart' => $cart ) );
		$products  = it_exchange_get_transaction_products( $txn );
		$t_product = it_exchange_get_transaction_product( $txn, key( $products ) );

		$this->assertEquals( $product->ID, $t_product['product_id'] );
		$this->assertEquals( $product->post_title, $t_product['product_name'] );
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

		$txn = self::transaction_factory()->create();

		it_exchange_update_transaction_method_id( $txn, 'new-test-method-id' );

		$this->assertEquals( 'new-test-method-id', it_exchange_get_transaction_method_id( $txn ) );
	}

	public function test_get_transactions_wp_args_conversion_method_id() {

		$product = self::product_factory()->create_and_get();

		$cart = $this->cart();
		$cart->add_item( ITE_Cart_Product::create( $product ) );
		$t1 = it_exchange_add_transaction( 'test-method', 'method-id-1', 'paid', $cart );

		$cart = $this->cart();
		$cart->add_item( ITE_Cart_Product::create( $product ) );
		$t2 = it_exchange_add_transaction( 'test-method', 'method-id-2', 'paid', $cart );

		$cart = $this->cart();
		$cart->add_item( ITE_Cart_Product::create( $product ) );
		$t3 = it_exchange_add_transaction( 'test-method', 'method-id-3', 'paid', $cart );

		$this->expectHook( 'it_exchange_transaction_query_after', 4 );

		$transactions = it_exchange_get_transactions( array(
			'meta_query' => array(
				array(
					'key'   => '_it_exchange_transaction_method_id',
					'value' => 'method-id-1',
				)
			)
		) );
		$this->assertEquals( array( $t1 ), array_map( array( $this, '_map_id' ), $transactions ) );

		$transactions = it_exchange_get_transactions( array(
			'meta_query' => array(
				array(
					'key'     => '_it_exchange_transaction_method_id',
					'value'   => array( 'method-id-1', 'method-id-2' ),
					'compare' => 'IN'
				)
			)
		) );
		$this->assertEqualSets( array( $t1, $t2 ), array_map( array( $this, '_map_id' ), $transactions ) );

		$transactions = it_exchange_get_transactions( array(
			'meta_query' => array(
				array(
					'key'     => '_it_exchange_transaction_method_id',
					'value'   => array( 'method-id-2', 'method-id-3' ),
					'compare' => 'NOT IN'
				)
			)
		) );
		$this->assertEquals( array( $t1 ), array_map( array( $this, '_map_id' ), $transactions ) );

		$transactions = it_exchange_get_transactions( array(
			'meta_query' => array(
				array(
					'key'     => '_it_exchange_transaction_method_id',
					'value'   => 'method-id-1',
					'compare' => '!='
				)
			)
		) );
		$this->assertEqualSets( array( $t2, $t3 ), array_map( array( $this, '_map_id' ), $transactions ) );
	}

	public function test_get_transactions_wp_args_conversion_method() {

		$product = self::product_factory()->create_and_get();

		$cart = $this->cart();
		$cart->add_item( ITE_Cart_Product::create( $product ) );
		$t1 = it_exchange_add_transaction( 'test-method-1', 'method-id', 'paid', $cart );

		$cart = $this->cart();
		$cart->add_item( ITE_Cart_Product::create( $product ) );
		$t2 = it_exchange_add_transaction( 'test-method-2', 'method-id', 'paid', $cart );

		$this->expectHook( 'it_exchange_transaction_query_after', 1 );

		$transactions = it_exchange_get_transactions( array(
			'meta_query' => array(
				array(
					'key'   => '_it_exchange_transaction_method',
					'value' => 'test-method-1',
				)
			)
		) );
		$this->assertEquals( array( $t1 ), array_map( array( $this, '_map_id' ), $transactions ) );
	}

	public function test_get_transactions_wp_args_conversion_status() {

		$product = self::product_factory()->create_and_get();

		$cart = $this->cart();
		$cart->add_item( ITE_Cart_Product::create( $product ) );
		$t1 = it_exchange_add_transaction( 'test-method', 'method-id-1', 'paid', $cart );

		$cart = $this->cart();
		$cart->add_item( ITE_Cart_Product::create( $product ) );
		$t2 = it_exchange_add_transaction( 'test-method', 'method-id-2', 'pending', $cart );

		$this->expectHook( 'it_exchange_transaction_query_after', 1 );

		$transactions = it_exchange_get_transactions( array(
			'meta_query' => array(
				array(
					'key'   => '_it_exchange_transaction_status',
					'value' => 'paid',
				)
			)
		) );
		$this->assertEquals( array( $t1 ), array_map( array( $this, '_map_id' ), $transactions ) );
	}

	public function test_get_transactions_wp_args_conversion_customer() {

		$product = self::product_factory()->create_and_get();

		$cart = $this->cart();
		$cart->add_item( ITE_Cart_Product::create( $product ) );
		$t1 = it_exchange_add_transaction( 'test-method', 'method-id-1', 'paid', $cart );

		$cart = $this->cart( self::factory()->user->create() );
		$cart->add_item( ITE_Cart_Product::create( $product ) );
		$t2 = it_exchange_add_transaction( 'test-method', 'method-id-2', 'paid', $cart );

		$this->expectHook( 'it_exchange_transaction_query_after', 1 );

		$transactions = it_exchange_get_transactions( array(
			'meta_query' => array(
				array(
					'key'   => '_it_exchange_customer_id',
					'value' => 1,
				)
			)
		) );
		$this->assertEquals( array( $t1 ), array_map( array( $this, '_map_id' ), $transactions ) );
	}

	public function test_get_transactions_wp_args_conversion_cart_id() {

		$product = self::product_factory()->create_and_get();

		$cart = $this->cart();
		$cart->add_item( ITE_Cart_Product::create( $product ) );
		$cart_id = $cart->get_id();
		$t1      = it_exchange_add_transaction( 'test-method', 'method-id-1', 'paid', $cart );

		$cart = $this->cart();
		$cart->add_item( ITE_Cart_Product::create( $product ) );
		$t2 = it_exchange_add_transaction( 'test-method', 'method-id-2', 'pending', $cart );

		$this->expectHook( 'it_exchange_transaction_query_after', 1 );

		$transactions = it_exchange_get_transactions( array(
			'meta_query' => array(
				array(
					'key'   => '_it_exchange_cart_id',
					'value' => $cart_id,
				)
			)
		) );
		$this->assertEquals( array( $t1 ), array_map( array( $this, '_map_id' ), $transactions ) );
	}

	public function test_get_transactions_wp_args_conversion_transaction_hash() {

		$product = self::product_factory()->create_and_get();

		$cart = $this->cart();
		$cart->add_item( ITE_Cart_Product::create( $product ) );
		$t1 = it_exchange_add_transaction( 'test-method', 'method-id-1', 'paid', $cart );

		$cart = $this->cart();
		$cart->add_item( ITE_Cart_Product::create( $product ) );
		$t2 = it_exchange_add_transaction( 'test-method', 'method-id-2', 'pending', $cart );

		$this->expectHook( 'it_exchange_transaction_query_after', 1 );

		$transactions = it_exchange_get_transactions( array(
			'meta_query' => array(
				array(
					'key'   => '_it_exchange_transaction_hash',
					'value' => it_exchange_get_transaction_hash( $t1 ),
				)
			)
		) );
		$this->assertEquals( array( $t1 ), array_map( array( $this, '_map_id' ), $transactions ) );
	}

	public function test_get_transactions_wp_args_conversion_multiple_meta_keys() {

		$product = self::product_factory()->create_and_get();

		$cart = $this->cart();
		$cart->add_item( ITE_Cart_Product::create( $product ) );
		$t1 = it_exchange_add_transaction( 'test-method', 'method-id-1', 'paid', $cart );

		$cart = $this->cart();
		$cart->add_item( ITE_Cart_Product::create( $product ) );
		$t2 = it_exchange_add_transaction( 'test-method', 'method-id-2', 'pending', $cart );

		$this->expectHook( 'it_exchange_transaction_query_after', 1 );

		$transactions = it_exchange_get_transactions( array(
			'meta_query' => array(
				array(
					'key'   => '_it_exchange_customer_id',
					'value' => 1,
				),
				array(
					'key'   => '_it_exchange_transaction_status',
					'value' => 'paid',
				)
			)
		) );
		$this->assertEquals( array( $t1 ), array_map( array( $this, '_map_id' ), $transactions ) );
	}

	public function test_get_transactions_wp_args_return_id() {

		$product = self::product_factory()->create_and_get();

		$cart = $this->cart();
		$cart->add_item( ITE_Cart_Product::create( $product ) );
		$t1 = it_exchange_add_transaction( 'test-method', 'method-id-1', 'paid', $cart );

		$cart = $this->cart();
		$cart->add_item( ITE_Cart_Product::create( $product ) );
		$t2 = it_exchange_add_transaction( 'test-method', 'method-id-2', 'paid', $cart );

		$this->expectHook( 'it_exchange_transaction_query_after', 1 );

		$transactions = it_exchange_get_transactions( array(
			'fields' => 'ids'
		) );
		$this->assertEquals( array( (string) $t1, (string) $t2 ), $transactions );
	}

	public function test_get_transactions_wp_args_return_id_to_parent() {

		$product = self::product_factory()->create_and_get();

		$cart = $this->cart();
		$cart->add_item( ITE_Cart_Product::create( $product ) );
		$t1 = it_exchange_add_transaction( 'test-method', 'method-id-1', 'paid', $cart );

		$t2 = it_exchange_add_child_transaction( 'test-method', 'method-id-2', 'paid', 1, $t1, new stdClass() );

		$this->expectHook( 'it_exchange_transaction_query_after', 1 );

		$transactions = it_exchange_get_transactions( array(
			'fields' => 'id=>parent'
		) );
		$this->assertEquals( array( $t1 => 0, $t2 => $t1 ), $transactions );
	}

	public function test_get_transactions_wp_args_order_date() {

		$product = self::product_factory()->create_and_get();

		$cart = $this->cart();
		$cart->add_item( ITE_Cart_Product::create( $product ) );
		$t1 = it_exchange_add_transaction( 'test-method', 'method-id-1', 'paid', $cart, null, array(
			'post_date_gmt' => '2015-03-15'
		) );

		$cart = $this->cart();
		$cart->add_item( ITE_Cart_Product::create( $product ) );
		$t2 = it_exchange_add_transaction( 'test-method', 'method-id-2', 'paid', $cart, null, array(
			'post_date_gmt' => '2015-02-15'
		) );

		$this->expectHook( 'it_exchange_transaction_query_after', 1 );

		$transactions = it_exchange_get_transactions( array(
			'date_query' => array( 'month' => '03', 'year' => '2015' )
		) );
		$this->assertEquals( array( $t1 ), array_map( array( $this, '_map_id' ), $transactions ) );
	}

	public function test_get_transactions_wp_args_order_by_date() {

		$product = self::product_factory()->create_and_get();

		$cart = $this->cart();
		$cart->add_item( ITE_Cart_Product::create( $product ) );
		$t1 = it_exchange_add_transaction( 'test-method', 'method-id-1', 'paid', $cart, null, array(
			'post_date_gmt' => '2015-02-15'
		) );

		$cart = $this->cart();
		$cart->add_item( ITE_Cart_Product::create( $product ) );
		$t2 = it_exchange_add_transaction( 'test-method', 'method-id-2', 'paid', $cart, null, array(
			'post_date_gmt' => '2015-03-15'
		) );

		$this->expectHook( 'it_exchange_transaction_query_after', 2 );

		$transactions = it_exchange_get_transactions( array(
			'orderby' => 'date'
		) );
		$this->assertEquals( array( $t2, $t1 ), array_map( array( $this, '_map_id' ), $transactions ) );

		$transactions = it_exchange_get_transactions( array(
			'orderby' => 'date',
			'order'   => 'ASC'
		) );
		$this->assertEquals( array( $t1, $t2 ), array_map( array( $this, '_map_id' ), $transactions ) );
	}

	public function test_get_transactions_wp_args_order_by_ID() {

		$product = self::product_factory()->create_and_get();

		$cart = $this->cart();
		$cart->add_item( ITE_Cart_Product::create( $product ) );
		$t1 = it_exchange_add_transaction( 'test-method', 'method-id-1', 'paid', $cart, null, array(
			'post_date_gmt' => '2015-03-15'
		) );

		$cart = $this->cart();
		$cart->add_item( ITE_Cart_Product::create( $product ) );
		$t2 = it_exchange_add_transaction( 'test-method', 'method-id-2', 'paid', $cart, null, array(
			'post_date_gmt' => '2015-02-15'
		) );

		$this->expectHook( 'it_exchange_transaction_query_after', 2 );

		$transactions = it_exchange_get_transactions( array(
			'orderby' => 'ID'
		) );
		$this->assertEquals( array( $t2, $t1 ), array_map( array( $this, '_map_id' ), $transactions ) );

		$transactions = it_exchange_get_transactions( array(
			'orderby' => 'ID',
			'order'   => 'ASC'
		) );
		$this->assertEquals( array( $t1, $t2 ), array_map( array( $this, '_map_id' ), $transactions ) );
	}

	public function test_get_transactions_wp_args_include_exclude() {

		$product = self::product_factory()->create_and_get();

		$cart = $this->cart();
		$cart->add_item( ITE_Cart_Product::create( $product ) );
		$t1 = it_exchange_add_transaction( 'test-method', 'method-id-1', 'paid', $cart );

		$cart = $this->cart();
		$cart->add_item( ITE_Cart_Product::create( $product ) );
		$t2 = it_exchange_add_transaction( 'test-method', 'method-id-2', 'paid', $cart );

		$this->expectHook( 'it_exchange_transaction_query_after', 2 );

		$transactions = it_exchange_get_transactions( array(
			'include' => $t1,
		) );
		$this->assertEquals( array( $t1 ), array_map( array( $this, '_map_id' ), $transactions ) );

		$transactions = it_exchange_get_transactions( array(
			'exclude' => $t1,
		) );
		$this->assertEquals( array( $t2 ), array_map( array( $this, '_map_id' ), $transactions ) );
	}

	public function test_get_transactions_wp_args_post_status() {

		$product = self::product_factory()->create_and_get();

		$cart = $this->cart();
		$cart->add_item( ITE_Cart_Product::create( $product ) );
		$t1 = it_exchange_add_transaction( 'test-method', 'method-id-1', 'paid', $cart, null, array( 'post_status' => 'private' ) );

		$cart = $this->cart();
		$cart->add_item( ITE_Cart_Product::create( $product ) );
		$t2 = it_exchange_add_transaction( 'test-method', 'method-id-2', 'paid', $cart );

		$this->expectHook( 'it_exchange_transaction_query_after', 1 );

		$transactions = it_exchange_get_transactions( array(
			'post_status' => 'private'
		) );
		$this->assertEquals( array( $t1 ), array_map( array( $this, '_map_id' ), $transactions ) );
	}

	public function test_get_transactions_wp_args_nopaging() {

		$product = self::product_factory()->create_and_get();

		$expected = array();

		for ( $i = 0; $i < 10; $i ++ ) {
			$cart = $this->cart();
			$cart->add_item( ITE_Cart_Product::create( $product ) );
			$expected[] = it_exchange_add_transaction( 'test-method', "method-id-{$i}", 'paid', $cart );
		}

		$this->expectHook( 'it_exchange_transaction_query_after', 1 );

		$transactions = it_exchange_get_transactions( array(
			'nopaging' => true,
			'orderby'  => 'ID',
			'order'    => 'ASC'
		) );
		$this->assertEquals( $expected, array_map( array( $this, '_map_id' ), $transactions ) );
	}

	public function test_get_transactions_wp_args_numberposts() {

		$product = self::product_factory()->create_and_get();

		$expected = array();

		for ( $i = 0; $i < 7; $i ++ ) {
			$cart = $this->cart();
			$cart->add_item( ITE_Cart_Product::create( $product ) );
			$expected[] = it_exchange_add_transaction( 'test-method', "method-id-{$i}", 'paid', $cart );
		}

		$this->expectHook( 'it_exchange_transaction_query_after', 1 );

		$transactions = it_exchange_get_transactions( array(
			'numberposts' => 5,
			'orderby'     => 'ID',
			'order'       => 'ASC'
		), $total );
		$this->assertEquals( array_slice( $expected, 0, 5 ), array_map( array( $this, '_map_id' ), $transactions ) );
		$this->assertEquals( 7, $total );
	}

	public function test_get_transactions_wp_args_pagination() {

		$product = self::product_factory()->create_and_get();

		$expected = array();

		for ( $i = 0; $i < 15; $i ++ ) {
			$cart = $this->cart();
			$cart->add_item( ITE_Cart_Product::create( $product ) );
			$expected[] = it_exchange_add_transaction( 'test-method', "method-id-{$i}", 'paid', $cart );
		}

		$this->expectHook( 'it_exchange_transaction_query_after', 1 );

		$transactions = it_exchange_get_transactions( array(
			'posts_per_page' => 5,
			'paged'          => 2,
			'orderby'        => 'ID',
			'order'          => 'ASC'
		), $total );
		$this->assertEquals( array_slice( $expected, 5, 5 ), array_map( array( $this, '_map_id' ), $transactions ) );
		$this->assertEquals( 15, $total );
	}

	public function test_get_transactions_wp_args_offset() {

		$product = self::product_factory()->create_and_get();

		$expected = array();

		for ( $i = 0; $i < 8; $i ++ ) {
			$cart = $this->cart();
			$cart->add_item( ITE_Cart_Product::create( $product ) );
			$expected[] = it_exchange_add_transaction( 'test-method', "method-id-{$i}", 'paid', $cart );
		}

		$this->expectHook( 'it_exchange_transaction_query_after', 1 );

		$transactions = it_exchange_get_transactions( array(
			'posts_per_page' => 5,
			'offset'         => 1,
			'orderby'        => 'ID',
			'order'          => 'ASC'
		), $total );
		$this->assertEquals( array_slice( $expected, 1, 5 ), array_map( array( $this, '_map_id' ), $transactions ) );
		$this->assertEquals( 8, $total );
	}

	public function test_get_transactions_wp_args_conversion_unsupported_meta() {

		$product = self::product_factory()->create_and_get();

		$cart = $this->cart();
		$cart->add_item( ITE_Cart_Product::create( $product ) );
		$t1 = it_exchange_add_transaction( 'test-method', 'method-id-1', 'paid', $cart );
		add_post_meta( $t1, 'custom_key', 'custom-value' );

		$cart = $this->cart();
		$cart->add_item( ITE_Cart_Product::create( $product ) );
		$t2 = it_exchange_add_transaction( 'test-method', 'method-id-2', 'paid', $cart );

		$this->expectHook( 'it_exchange_transaction_query_after', 1 );

		$transactions = it_exchange_get_transactions( array(
			'meta_query' => array(
				array(
					'key'   => 'custom_key',
					'value' => 'custom-value',
				)
			)
		) );
		$this->assertEquals( array( $t1 ), array_map( array( $this, '_map_id' ), $transactions ) );
	}

	public function test_get_transactions_wp_args_conversion_unsupported_meta_field_combination() {

		$product = self::product_factory()->create_and_get();

		$cart = $this->cart();
		$cart->add_item( ITE_Cart_Product::create( $product ) );
		$t1 = it_exchange_add_transaction( 'test-method', 'method-id-1', 'paid', $cart );
		add_post_meta( $t1, 'custom_key', 'custom-value' );

		$cart = $this->cart();
		$cart->add_item( ITE_Cart_Product::create( $product ) );
		$t2 = it_exchange_add_transaction( 'test-method', 'method-id-2', 'paid', $cart );
		add_post_meta( $t2, 'custom_key', 'custom-value' );

		$this->expectHook( 'it_exchange_transaction_query_after', 1 );

		$transactions = it_exchange_get_transactions( array(
			'meta_query' => array(
				array(
					'key'   => 'custom_key',
					'value' => 'custom-value',
				),
				array(
					'key'   => '_it_exchange_transaction_method_id',
					'value' => 'method-id-1'
				)
			)
		) );
		$this->assertEquals( array( $t1 ), array_map( array( $this, '_map_id' ), $transactions ) );
	}

	public function test_get_transactions_wp_args_conversion_unsupported_meta_field_or() {

		$product = self::product_factory()->create_and_get();

		$cart = $this->cart();
		$cart->add_item( ITE_Cart_Product::create( $product ) );
		$t1 = it_exchange_add_transaction( 'test-method', 'method-id-1', 'paid', $cart );
		add_post_meta( $t1, 'custom_key', 'custom-value' );

		$cart = $this->cart();
		$cart->add_item( ITE_Cart_Product::create( $product ) );
		$t2 = it_exchange_add_transaction( 'test-method', 'method-id-2', 'paid', $cart );
		add_post_meta( $t2, 'custom_key', 'custom-value' );

		$transactions = it_exchange_get_transactions( array(
			'meta_query' => array(
				'relation' => 'OR',
				array(
					'key'   => 'custom_key',
					'value' => 'custom-value',
				),
				array(
					'key'   => '_it_exchange_transaction_method_id',
					'value' => 'method-id-1'
				)
			)
		) );
		$this->assertEqualSets( array( $t1, $t2 ), array_map( array( $this, '_map_id' ), $transactions ) );
	}

	public function test_get_transactions_wp_args_conversion_unsupported_meta_field_nesting_and() {

		$product = self::product_factory()->create_and_get();

		$cart = $this->cart();
		$cart->add_item( ITE_Cart_Product::create( $product ) );
		$t1 = it_exchange_add_transaction( 'test-method', 'method-id-1', 'paid', $cart );
		add_post_meta( $t1, 'custom_key', 'custom-value' );

		$cart = $this->cart();
		$cart->add_item( ITE_Cart_Product::create( $product ) );
		$t2 = it_exchange_add_transaction( 'test-method', 'method-id-2', 'paid', $cart );
		add_post_meta( $t2, 'custom_other_key', 'custom-other-value' );

		$this->expectHook( 'it_exchange_transaction_query_after' );

		$transactions = it_exchange_get_transactions( array(
			'meta_query' => array(
				'relation' => 'AND',
				array(
					'relation' => 'OR',
					array(
						'key'   => 'custom_other_key',
						'value' => 'custom-other-value',
					),
					array(
						'key'   => 'custom_key',
						'value' => 'custom-value',
					),
				),
				array(
					'key'   => '_it_exchange_transaction_method',
					'value' => 'test-method'
				)
			)
		) );
		$this->assertEqualSets( array( $t1, $t2 ), array_map( array( $this, '_map_id' ), $transactions ) );
	}

	public function _map_id( $transaction ) {
		return $transaction->ID;
	}
}