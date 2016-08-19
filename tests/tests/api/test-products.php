<?php
/**
 * Contains tests for the products API functions.
 *
 * @since   1.34
 * @license GPLv2
 */

/**
 * Class IT_Exchange_API_Products_Test
 *
 * @group products-api
 */
class IT_Exchange_API_Products_Test extends IT_Exchange_UnitTestCase {

	public function test_is_product() {

		$product = self::factory()->post->create( array(
			'post_type' => 'it_exchange_prod'
		) );

		$this->assertTrue( it_exchange_is_product( $product ) );
	}

	public function test_is_product_returns_false_for_invalid_post_type() {
		$this->assertFalse( it_exchange_is_product( 1 ) );
	}

	public function test_get_product_type() {

		$product = it_exchange_add_product( array(
			'type'  => 'simple-product-type',
			'title' => 'My Product'
		) );

		$this->assertEquals( 'simple-product-type', it_exchange_get_product_type( $product ) );
	}

	public function test_get_product_type_fallsback_to_query_var() {

		$_GET['it-exchange-product-type'] = 'garbage-product-type';

		$this->assertEquals( 'garbage-product-type', it_exchange_get_product_type() );

		unset( $_GET['it-exchange-product-type'] );
	}

	public function test_get_product() {

		$ID = it_exchange_add_product( array(
			'type'  => 'simple-product-type',
			'title' => 'My Product'
		) );

		$product = it_exchange_get_product( $ID );

		$this->assertInstanceOf( 'IT_Exchange_Product', $product );
		$this->assertEquals( $ID, $product->ID );
	}

	public function test_get_product_returns_false_for_invalid_post_type() {
		$this->assertFalse( it_exchange_get_product( 1 ) );
	}

	public function test_get_product_uses_custom_class() {

		it_exchange_register_addon( 'test-product-type', array(
			'name'    => 'Test Product Type',
			'file'    => __FILE__,
			'options' => array(
				'class'    => 'IT_Exchange_Mock_Product_Type',
				'category' => 'product-type'
			)
		) );

		$ID = $this->product_factory->create(
			array(
				'type' => 'test-product-type'
			)
		);

		$this->assertInstanceOf( 'IT_Exchange_Mock_Product_Type', it_exchange_get_product( $ID ) );
	}

	public function test_get_products() {

		$ID = it_exchange_add_product( array(
			'type'          => 'simple-product-type',
			'title'         => 'My Product',
			'show_in_store' => true
		) );

		$products = it_exchange_get_products();

		$this->assertEquals( 1, count( $products ) );
		$this->assertContainsOnlyInstancesOf( 'IT_Exchange_Product', $products );
		$this->assertEquals( $ID, reset( $products )->ID );
	}

	public function test_get_products_hidden_products() {

		it_exchange_add_product( array(
			'type'          => 'simple-product-type',
			'title'         => 'My Product',
			'show_in_store' => false
		) );

		$this->assertEquals( 0, count( it_exchange_get_products() ), 'Hidden not excluded.' );
		$this->assertEquals( 1, count( it_exchange_get_products( array( 'show_hidden' => true ) ) ), 'Hidden excluded.' );
	}

	public function test_get_products_by_type() {

		$ID = it_exchange_add_product( array(
			'type'          => 'simple-product-type',
			'title'         => 'My Product',
			'show_in_store' => true
		) );
		it_exchange_add_product( array(
			'type'          => 'physical-product-type',
			'title'         => 'My Physical Product',
			'show_in_store' => true
		) );

		$products = it_exchange_get_products( array( 'product_type' => 'simple-product-type' ) );

		$this->assertEquals( 1, count( $products ) );
		$this->assertEquals( $ID, reset( $products )->ID );
	}

	public function test_get_products_deactivated_types_excluded() {

		it_exchange_add_product( array(
			'type'          => 'deactivated-product-type',
			'title'         => 'My Product',
			'show_in_store' => true
		) );

		$this->assertEquals( 0, count( it_exchange_get_products() ) );
	}

	public function test_set_the_product_id() {

		$ID = it_exchange_add_product( array(
			'title' => 'My Product',
			'type'  => 'simple-product-type'
		) );

		it_exchange_set_the_product_id( $ID );

		$this->assertEquals( $ID, it_exchange_get_the_product_id() );
	}

	public function test_set_the_product_id_with_invalid_product_makes_product_false() {

		it_exchange_set_the_product_id( it_exchange_add_product( array(
			'title' => 'My Product',
			'type'  => 'simple-product-type'
		) ) );

		it_exchange_set_the_product_id( 1 );

		$this->assertFalse( it_exchange_get_the_product_id() );
	}

	public function test_set_the_product() {

		$ID = it_exchange_add_product( array(
			'title' => 'My Product',
			'type'  => 'simple-product-type'
		) );

		it_exchange_set_product( $ID );

		$this->assertInstanceOf( 'IT_Exchange_Product', $GLOBALS['it_exchange']['product'] );
		$this->assertEquals( $ID, $GLOBALS['it_exchange']['product']->ID );
	}

	protected function _setup_get_transactions_for_product() {

		$GLOBALS['it_exchange']['session']->clear_session();

		$ID = it_exchange_add_product( array(
			'title' => 'My Product',
			'type'  => 'simple-product-type'
		) );

		$object = (object) array(
			'cart_id'  => it_exchange_create_cart_id(),
			'products' => array(
				$ID . '-product-hash' => array(
					'product_id' => $ID
				)
			)
		);

		add_filter( 'it_exchange_test-method_transaction_is_cleared_for_delivery', function ( $cleared, $transaction ) {

			$status = it_exchange_get_transaction_status( $transaction );

			if ( $status === 'paid' ) {
				return true;
			}

			return false;
		}, 10, 2 );

		$t1 = it_exchange_add_transaction( 'test-method', 'test-method-id-1', 'pending', 1, $object );

		$object->cart_id = it_exchange_create_cart_id();

		$t2 = it_exchange_add_transaction( 'test-method', 'test-method-id-2', 'paid', 1, $object );

		return array(
			'ID' => $ID,
			't1' => $t1,
			't2' => $t2
		);
	}

	public function test_get_transactions_for_product_only_cleared_for_delivery() {

		$setup = $this->_setup_get_transactions_for_product();
		$ID    = $setup['ID'];
		$t1    = $setup['t1'];
		$t2    = $setup['t2'];

		$transactions = it_exchange_get_transactions_for_product( $ID );

		$this->assertEquals( 1, count( $transactions ), 'Wrong transaction count' );
		$this->assertInstanceOf( 'IT_Exchange_Transaction', $transactions[0], 'Transaction objects not returned' );
		$this->assertEquals( $t2, $transactions[0]->ID );
	}

	public function test_get_transactions_for_product_all_clear_types() {

		$setup = $this->_setup_get_transactions_for_product();
		$ID    = $setup['ID'];
		$t1    = $setup['t1'];
		$t2    = $setup['t2'];

		$transactions = it_exchange_get_transactions_for_product( $ID, 'objects', false );
		$this->assertEquals( 2, count( $transactions ) );
		$this->assertContainsOnlyInstancesOf( 'IT_Exchange_Transaction', $transactions );
	}

	public function test_get_transactions_for_product_return_ids() {

		$setup = $this->_setup_get_transactions_for_product();
		$ID    = $setup['ID'];
		$t1    = $setup['t1'];
		$t2    = $setup['t2'];

		$transactions = it_exchange_get_transactions_for_product( $ID, 'ids', false );
		$this->assertEqualSets( array( $t1, $t2 ), $transactions );
	}
}