<?php
/**
 * Contains test for the sales API functions.
 *
 * @since   1.35
 * @license GPLv2
 */

/**
 * Class IT_Exchange_Sales_API_Test
 *
 * @group sales-api
 */
class IT_Exchange_Sales_API_Test extends IT_Exchange_UnitTestCase {

	public function test_is_product_sale_active_is_false_for_invalid_product() {
		$this->assertFalse( it_exchange_is_product_sale_active( 1 ) );
	}

	public function test_is_product_sale_active_is_false_if_not_has_sale_price() {

		$mock     = $this->getMockBuilder( 'IT_Exchange_Product' )->disableOriginalConstructor()->getMock();
		$mock->ID = 1; // we just need a dummy ID
		$mock->method( 'has_feature' )->with( 'sale-price' )->willReturn( false );

		$this->assertFalse( it_exchange_is_product_sale_active( $mock ) );
	}

	public function test_is_product_sale_active_is_false_if_base_price_same_sale_price() {

		$mock     = $this->getMockBuilder( 'IT_Exchange_Product' )->disableOriginalConstructor()->getMock();
		$mock->ID = 1; // we just need a dummy ID
		$mock->method( 'has_feature' )->with( 'sale-price' )->willReturn( true );
		$mock->method( 'get_feature' )->willReturnMap( array(
			array( 'base-price', array(), '5.00' ),
			array( 'sale-price', array(), '5.00' )
		) );

		$this->assertFalse( it_exchange_is_product_sale_active( $mock ) );
	}

	public function test_is_product_sale_active() {

		$mock     = $this->getMockBuilder( 'IT_Exchange_Product' )->disableOriginalConstructor()->getMock();
		$mock->ID = 1; // we just need a dummy ID
		$mock->method( 'has_feature' )->with( 'sale-price' )->willReturn( true );
		$mock->method( 'get_feature' )->willReturnMap( array(
			array( 'base-price', array(), '5.00' ),
			array( 'sale-price', array(), '2.50' )
		) );

		$this->assertTrue( it_exchange_is_product_sale_active( $mock ) );
	}
}
