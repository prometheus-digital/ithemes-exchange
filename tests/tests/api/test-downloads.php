<?php
/**
 * Contains tests for the downloads API functions.
 *
 * @since   1.35
 * @license GPLv2
 */

/**
 * Class IT_Exchange_API_Downloads_Test
 *
 * @group downloads-api
 */
class IT_Exchange_API_Downloads_Test extends IT_Exchange_UnitTestCase {

	public function test_add_get_download_hash_data() {

		$product_id = $this->product_factory->create();
		$download   = $this->download_factory->create( array(
			'product' => $product_id
		) );

		$hash = it_exchange_create_unique_hash();

		$data = array(
			'transaction_id' => 10,
			'product_id'     => $product_id
		);

		$meta_id = it_exchange_add_download_hash_data( $download, $hash, $data );

		$this->assertNotFalse( get_metadata_by_mid( 'post', $meta_id ) );
		$this->assertEquals( $data, it_exchange_get_download_data( $download, $hash ) );
	}

	public function test_add_download_hash_data_rejects_dupe_hashes() {

		$product_id = $this->product_factory->create();
		$download   = $this->download_factory->create( array(
			'product' => $product_id
		) );

		$hash = it_exchange_create_unique_hash();

		$data = array(
			'transaction_id' => 10,
			'product_id'     => $product_id
		);

		$this->assertNotFalse( it_exchange_add_download_hash_data( $download, $hash, $data ) );
		$this->assertFalse( it_exchange_add_download_hash_data( $download, $hash, array() ) );
	}

	public function test_update_download_hash_data() {

		$product_id = $this->product_factory->create();
		$download   = $this->download_factory->create( array(
			'product' => $product_id
		) );

		$hash = it_exchange_create_unique_hash();

		$data = array(
			'transaction_id' => 10,
			'product_id'     => $product_id,
			'hash'           => $hash,
			'file_id'        => $download,
			'customer_id'    => 1
		);

		it_exchange_add_download_hash_data( $download, $hash, $data );

		$new_data            = $data;
		$new_data['another'] = 'value';

		it_exchange_update_download_hash_data( $hash, $new_data );

		$this->assertEquals( $new_data, it_exchange_get_download_data_from_hash( $hash ) );
	}

	public function test_get_download_info() {

		$product_id = $this->product_factory->create();
		$download   = $this->download_factory->create( array(
			'product' => $product_id
		) );

		// Exchange doesn't provide defaults or assert certain keys exist
		// if we checked keys we'd just be testing our factory
		$this->assertInternalType( 'array', it_exchange_get_download_info( $download ) );
	}

	public function test_get_download_data_always_returns_array() {

		$product_id = $this->product_factory->create();
		$download   = $this->download_factory->create( array(
			'product' => $product_id
		) );

		$this->assertInternalType( 'array', it_exchange_get_download_data( $download, 'fake' ) );
	}

	public function test_get_download_data_from_hash() {

		$product_id = $this->product_factory->create();
		$download   = $this->download_factory->create( array(
			'product' => $product_id
		) );

		$hash = it_exchange_create_unique_hash();

		$data = array(
			'transaction_id' => 10,
			'product_id'     => $product_id
		);

		$meta_id = it_exchange_add_download_hash_data( $download, $hash, $data );

		$this->assertNotFalse( get_metadata_by_mid( 'post', $meta_id ) );
		$this->assertEquals( $data, it_exchange_get_download_data_from_hash( $hash ) );
	}

	public function test_get_download_data_returns_false_for_invalid_hash() {
		$this->assertFalse( it_exchange_get_download_data_from_hash( 'i dont exist' ) );
	}

	public function test_get_download_hashes_for_transaction_product_returns_false_for_invalid_transaction() {
		$this->assertFalse( it_exchange_get_download_hashes_for_transaction_product( 1, array(), 1 ) );
	}

	public function test_get_download_hashes_for_transaction_product_returns_false_if_txn_prod_id_not_found() {

		$txn = it_exchange_add_transaction(
			'test-method', 'test-method-id', 'pending', 1,
			(object) array( 'cart_id' => it_exchange_create_cart_id() )
		);

		$this->assertFalse( it_exchange_get_download_hashes_for_transaction_product( $txn, array(), 1 ) );
		$this->assertFalse( it_exchange_get_download_hashes_for_transaction_product( $txn, array(
			'product_id' => false
		), 1 ) );
	}

	public function test_update_download_hashes_for_transaction_product() {

		$product  = $this->product_factory->create();
		$download = $this->download_factory->create( array( 'product' => $product ) );

		$cart = $this->cart();
		$cart->add_item( ITE_Cart_Product::create( it_exchange_get_product( $product ) ) );

		$txn = $this->transaction_factory->create( array(
			'cart' => $cart,
		) );

		$mock     = $this->getMockBuilder( 'IT_Exchange_Product' )->disableOriginalConstructor()->getMock();
		$mock->ID = $product;
		$mock->method( 'get_feature' )->willReturnMap( array(
			array( 'downloads', array( 'setting' => 'expires' ), true ),
			array( 'downloads', array( 'setting' => 'expire-int' ), 30 ),
			array( 'downloads', array( 'setting' => 'expire-units' ), 'days' ),
			array( 'downloads', array( 'setting' => 'limit' ), 5 )
		) );

		$now = time();

		$hash_array = it_exchange_update_download_hashes_for_transaction_product(
			$txn, $mock, $download
		);

		$this->assertInternalType( 'array', $hash_array );
		$hash = reset( $hash_array );
		$data = it_exchange_get_download_data_from_hash( $hash );

		$expected_data = array(
			'hash'           => $hash,
			'transaction_id' => $txn,
			'product_id'     => $product,
			'file_id'        => $download,
			'customer_id'    => 1,
			'expires'        => true,
			'expire_int'     => 30,
			'expire_units'   => 'days',
			'download_limit' => 5,
			'downloads'      => 0
		);

		foreach ( $expected_data as $key => $expected ) {
			$this->assertEquals( $expected, $data[ $key ], $key );
		}

		$this->assertEquals( $now + 30 * DAY_IN_SECONDS, $data['expire_time'], 'expire_time', 3 );
	}

	public function test_get_download_hashes_for_transaction_product_updates_if_none_exist() {

		$txn = $this->transaction_factory->create();

		WP_Mock::wpFunction(
			'it_exchange_update_download_hashes_for_transaction_product', array(
				'times'  => 1,
				'args'   => array( $txn, 2, 3 ),
				'return' => array( 'hash' )
			)
		);

		$hashes = it_exchange_get_download_hashes_for_transaction_product( $txn, array(
			'product_id' => 2
		), 3 );

		$this->assertEquals( array( 'hash' ), $hashes );
	}

	public function test_update_transaction_download_hash() {

		$txn  = $this->transaction_factory->create();
		$prod = $this->product_factory->create();
		$down = $this->download_factory->create( array( 'product' => $prod ) );

		$hash = it_exchange_create_unique_hash();

		it_exchange_update_transaction_download_hash_index( $txn, $prod, $down, $hash );

		$index = it_exchange_get_transaction_download_hash_index( $txn );

		$this->assertArrayHasKey( $prod, $index, 'Product array not set.' );
		$this->assertArrayHasKey( $down, $index[ $prod ], 'Download array not set.' );
		$this->assertContains( $hash, $index[ $prod ][ $down ], 'Hash not found.' );
	}

	public function test_delete_hash_from_transaction_hash_index() {

		$txn  = $this->transaction_factory->create();
		$prod = $this->product_factory->create();
		$down = $this->download_factory->create( array( 'product' => $prod ) );

		$hash = it_exchange_create_unique_hash();
		it_exchange_update_transaction_download_hash_index( $txn, $prod, $down, $hash );

		it_exchange_delete_hash_from_transaction_hash_index( $txn, $hash );
		$index = it_exchange_get_transaction_download_hash_index( $txn );

		$this->assertEmpty( $index[ $prod ][ $down ] );
	}

	public function test_clear_transaction_hash_index() {

		$txn  = $this->transaction_factory->create();
		$prod = $this->product_factory->create();
		$down = $this->download_factory->create( array( 'product' => $prod ) );

		$hash = it_exchange_create_unique_hash();

		it_exchange_update_transaction_download_hash_index( $txn, $prod, $down, $hash );
		it_exchange_clear_transaction_hash_index( $txn );

		$this->assertEmpty( it_exchange_get_transaction_download_hash_index( $txn ) );
	}

	public function test_increment_download_count() {

		$hash = it_exchange_create_unique_hash();
		$data = array(
			'downloads' => 1,
			'hash'      => $hash
		);

		WP_Mock::wpFunction( 'current_user_can', array(
			'return' => false
		) );

		$new              = $data;
		$new['downloads'] = 2;

		WP_Mock::wpFunction( 'it_exchange_update_download_hash_data', array(
			'times' => 1,
			'args'  => array( $hash, $new )
		) );

		it_exchange_increment_download_count( $data, false );
	}

	public function test_increment_download_count_doesnt_increment_for_admins() {

		WP_Mock::wpFunction( 'current_user_can', array(
			'return' => true
		) );

		$this->assertFalse( it_exchange_increment_download_count( array() ) );
	}

	public function test_increment_download_count_increments_admins_if_forced() {

		$hash = it_exchange_create_unique_hash();
		$data = array(
			'downloads' => 1,
			'hash'      => $hash
		);

		WP_Mock::wpFunction( 'current_user_can', array(
			'return' => true
		) );

		$new              = $data;
		$new['downloads'] = 2;

		WP_Mock::wpFunction( 'it_exchange_update_download_hash_data', array(
			'times' => 1,
			'args'  => array( $hash, $new )
		) );

		it_exchange_increment_download_count( $data, true );
	}

}