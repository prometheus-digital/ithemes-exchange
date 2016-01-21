<?php
/**
 * Contains tests for customer API functions.
 *
 * @since   1.35
 * @license GPLv2
 */

/**
 * Class IT_Exchange_API_Customers_Test
 *
 * @group customers-api
 */
class IT_Exchange_API_Customers_Test extends IT_Exchange_UnitTestCase {

	public function test_get_customer() {

		$customer = it_exchange_get_customer( 1 );

		$this->assertEquals( 1, $customer->id );
		$this->assertTrue( $customer->is_wp_user() );
	}

	/**
	 * @depends test_get_customer
	 */
	public function test_get_current_customer() {
		wp_set_current_user( 1 );
		$this->assertEquals( 1, it_exchange_get_current_customer()->id );
		wp_set_current_user( 0 );
	}

	public function test_get_current_customer_id() {
		wp_set_current_user( 1 );
		$this->assertEquals( 1, it_exchange_get_current_customer_id() );
		wp_set_current_user( 0 );
	}

	public function test_get_customer_transactions() {

		$u1 = 1;
		$u2 = self::factory()->user->create();

		$t1 = it_exchange_add_transaction( 'test-method', 'test-method-id-1', 'pending', $u1, (object) array(
			'cart_id' => it_exchange_create_cart_id()
		) );
		$t2 = it_exchange_add_transaction( 'test-method', 'test-method-id-2', 'pending', $u2, (object) array(
			'cart_id' => it_exchange_create_cart_id()
		) );

		$transactions = it_exchange_get_customer_transactions( $u1 );
		$this->assertEquals( 1, count( $transactions ) );
		$this->assertInstanceOf( 'IT_Exchange_Transaction', reset( $transactions ) );
		$this->assertEquals( $t1, reset( $transactions )->ID );
	}

	public function test_customer_has_transaction() {

		$t1 = it_exchange_add_transaction( 'test-method', 'test-method-id', 'pending', 1, (object) array(
			'cart_id' => it_exchange_create_cart_id()
		) );

		$customer = new IT_Exchange_Customer( 1 );
		$this->assertTrue( $customer->has_transaction( $t1 ) );
	}

	public function test_get_customer_products() {

		$p1 = it_exchange_add_product( array(
			'type'  => 'simple-product-type',
			'title' => 'My Product'
		) );
		$p2 = it_exchange_add_product( array(
			'type'  => 'simple-product-type',
			'title' => 'My Product 2'
		) );
		$p3 = it_exchange_add_product( array(
			'type'  => 'simple-product-type',
			'title' => 'My Product 3',
		) );
		$p4 = it_exchange_add_product( array(
			'type'  => 'simple-product-type',
			'title' => 'My Product 4'
		) );

		$t1 = it_exchange_add_transaction( 'test-method', 'test-method-id-1', 'pending', 1, (object) array(
			'cart_id'  => it_exchange_create_cart_id(),
			'products' => array(
				$p1 . '-hash' => array(
					'product_id' => $p1
				),
				$p2 . '-hash' => array(
					'product_id' => $p2
				)
			)
		) );

		$t2 = it_exchange_add_transaction( 'test-method', 'test-method-id-2', 'pending', 1, (object) array(
			'cart_id'  => it_exchange_create_cart_id(),
			'products' => array(
				$p3 . 'hash' => array(
					'product_id' => $p3
				)
			)
		) );

		$products = it_exchange_get_customer_products( 1 );
		$this->assertEquals( 3, count( $products ) );

		$ids = array_map( function ( $product ) {
			return $product['product_id'];
		}, $products );

		$this->assertEqualSets( array( $p1, $p2, $p3 ), $ids );
	}

	public function test_save_customer_billing_address() {

		$address = array(
			'first-name' => 'John',
			'last-name'  => 'Doe'
		);

		it_exchange_save_customer_billing_address( $address, 1 );

		$this->assertEquals( $address, it_exchange_get_customer_billing_address( 1 ) );
	}
}