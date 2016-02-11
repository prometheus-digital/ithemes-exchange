<?php
/**
 * Contains tests for the APi functions for coupons.
 *
 * @since   1.35
 * @license GPLv2
 */

/**
 * Class IT_Exchange_Addons_Basic_Coupons_API_Test
 *
 * @group addons/basic-coupons
 */
class IT_Exchange_Addons_Basic_Coupons_API_Test extends IT_Exchange_UnitTestCase {

	/**
	 * Do custom initialization.
	 */
	public function setUp() {
		parent::setUp();

		$GLOBALS['it_exchange']['session'] = new IT_Exchange_Session();
	}

	public function test_coupon_type_registered() {
		$this->assertTrue( it_exchange_supports_coupon_type( 'cart' ) );
		$this->assertEquals( 'IT_Exchange_Cart_Coupon', it_exchange_get_coupon_type_class( 'cart' ) );
	}

	public function test_get_cart_coupon_from_code() {

		$code = 'MYTESTCODE';

		$ID = $this->coupon_factory->create( array(
			'code' => $code
		) );

		$coupon = it_exchange_get_cart_coupon_from_code( null, $code );

		$this->assertEquals( $code, $coupon->get_code() );
		$this->assertEquals( $ID, $coupon->get_ID() );
	}

	public function test_get_cart_coupon_from_code_id_is_cached() {

		$code = 'MYTESTCODE';

		$ID = $this->coupon_factory->create( array(
			'code' => $code
		) );

		it_exchange_get_cart_coupon_from_code( null, $code );

		$this->assertEquals( $ID, wp_cache_get( 'it-exchange-cart-coupon', $code ) );
	}

	public function test_applied_coupons_returns_false_if_no_coupons_applied() {
		$this->assertFalse( it_exchange_basic_coupons_applied_cart_coupons() );
	}

	public function test_accepting_cart_coupons() {
		$this->assertTrue( it_exchange_basic_coupons_accepting_cart_coupons() );

		$coupon = $this->coupon_factory->create_and_get();

		WP_Mock::wpFunction( 'it_exchange_get_applied_coupons', array(
			'args'   => array( 'cart' ),
			'return' => array( $coupon )
		) );

		$this->assertFalse( it_exchange_basic_coupons_accepting_cart_coupons() );
	}

	public function test_apply_to_cart() {

		$product = $this->product_factory->create();
		it_exchange_add_product_to_shopping_cart( $product );

		/** @var IT_Exchange_Cart_Coupon $coupon */
		$coupon = $this->coupon_factory->create_and_get();

		$this->assertTrue( it_exchange_basic_coupons_apply_to_cart( false, array( 'code' => $coupon->get_code() ) ) );

		$applied = it_exchange_basic_coupons_applied_cart_coupons();

		foreach ( $applied as $applied_coupon ) {
			if ( $applied_coupon->get_code() === $coupon->get_code() ) {
				return;
			}
		}

		$this->fail( 'Coupon not found in applied coupons.' );
	}

	public function test_apply_to_cart_non_existent_coupon() {
		$this->assertFalse( it_exchange_basic_coupons_apply_to_cart( false ) );
	}

	public function test_get_total_discount_for_cart__flat_and_cart() {

		$coupon = $this->getMockBuilder( 'IT_Exchange_Cart_Coupon' )->disableOriginalConstructor()->getMock();
		$coupon->method( 'get_application_method' )->willReturn( IT_Exchange_Cart_Coupon::APPLY_CART );
		$coupon->method( 'get_amount_type' )->willReturn( IT_Exchange_Cart_Coupon::TYPE_FLAT );
		$coupon->method( 'get_amount_number' )->willReturn( 2.50 );

		$p1 = $this->product_factory->create( array(
			'base-price' => '5.00'
		) );
		$p2 = $this->product_factory->create( array(
			'base-price' => '15.00'
		) );

		it_exchange_add_product_to_shopping_cart( $p1, 1 );
		it_exchange_add_product_to_shopping_cart( $p2, 2 );

		WP_Mock::wpFunction( 'it_exchange_get_applied_coupons', array(
			'args'   => 'cart',
			'return' => array( $coupon )
		) );

		$this->assertEquals( '2.50', it_exchange_basic_coupons_get_total_discount_for_cart( false, array(
			'format_price' => false
		) ) );

		WP_Mock::wpFunction( 'it_exchange_basic_coupons_valid_product_for_coupon', array(
			'return_in_order' => array( true, false )
		) );

		$this->assertEquals( '2.50', it_exchange_basic_coupons_get_total_discount_for_cart( false, array(
			'format_price' => false
		) ) );
	}

	public function test_get_total_discount_for_cart__percent_and_cart() {

		$coupon = $this->getMockBuilder( 'IT_Exchange_Cart_Coupon' )->disableOriginalConstructor()->getMock();
		$coupon->method( 'get_application_method' )->willReturn( IT_Exchange_Cart_Coupon::APPLY_CART );
		$coupon->method( 'get_amount_type' )->willReturn( IT_Exchange_Cart_Coupon::TYPE_PERCENT );
		$coupon->method( 'get_amount_number' )->willReturn( 10 );

		$p1 = $this->product_factory->create( array(
			'base-price' => '5.00'
		) );
		$p2 = $this->product_factory->create( array(
			'base-price' => '15.00'
		) );

		it_exchange_add_product_to_shopping_cart( $p1, 1 );
		it_exchange_add_product_to_shopping_cart( $p2, 2 );

		WP_Mock::wpFunction( 'it_exchange_get_applied_coupons', array(
			'args'   => 'cart',
			'return' => array( $coupon )
		) );

		$this->assertEquals( '3.50', it_exchange_basic_coupons_get_total_discount_for_cart( false, array(
			'format_price' => false
		) ) );

		WP_Mock::wpFunction( 'it_exchange_basic_coupons_valid_product_for_coupon', array(
			'return_in_order' => array( true, false )
		) );

		$this->assertEquals( '3.50', it_exchange_basic_coupons_get_total_discount_for_cart( false, array(
			'format_price' => false
		) ) );
	}

	public function test_get_total_discount_for_cart__flat_and_product() {

		$coupon = $this->getMockBuilder( 'IT_Exchange_Cart_Coupon' )->disableOriginalConstructor()->getMock();
		$coupon->method( 'get_application_method' )->willReturn( IT_Exchange_Cart_Coupon::APPLY_PRODUCT );
		$coupon->method( 'get_amount_type' )->willReturn( IT_Exchange_Cart_Coupon::TYPE_FLAT );
		$coupon->method( 'get_amount_number' )->willReturn( 2.50 );

		$p1 = $this->product_factory->create( array(
			'base-price' => '5.00'
		) );
		$p2 = $this->product_factory->create( array(
			'base-price' => '15.00'
		) );

		it_exchange_add_product_to_shopping_cart( $p1, 1 );
		it_exchange_add_product_to_shopping_cart( $p2, 2 );

		WP_Mock::wpFunction( 'it_exchange_get_applied_coupons', array(
			'args'   => 'cart',
			'return' => array( $coupon )
		) );

		$this->assertEquals( '7.50', it_exchange_basic_coupons_get_total_discount_for_cart( false, array(
			'format_price' => false
		) ) );

		WP_Mock::wpFunction( 'it_exchange_basic_coupons_valid_product_for_coupon', array(
			'return_in_order' => array( true, false )
		) );

		$this->assertEquals( '2.50', it_exchange_basic_coupons_get_total_discount_for_cart( false, array(
			'format_price' => false
		) ) );
	}

	public function test_get_total_discount_for_cart__percent_and_product() {

		$coupon = $this->getMockBuilder( 'IT_Exchange_Cart_Coupon' )->disableOriginalConstructor()->getMock();
		$coupon->method( 'get_application_method' )->willReturn( IT_Exchange_Cart_Coupon::APPLY_PRODUCT );
		$coupon->method( 'get_amount_type' )->willReturn( IT_Exchange_Cart_Coupon::TYPE_PERCENT );
		$coupon->method( 'get_amount_number' )->willReturn( 10 );

		$p1 = $this->product_factory->create( array(
			'base-price' => '5.00'
		) );
		$p2 = $this->product_factory->create( array(
			'base-price' => '15.00'
		) );

		it_exchange_add_product_to_shopping_cart( $p1, 1 );
		it_exchange_add_product_to_shopping_cart( $p2, 2 );

		WP_Mock::wpFunction( 'it_exchange_get_applied_coupons', array(
			'args'   => 'cart',
			'return' => array( $coupon )
		) );

		$this->assertEquals( '3.50', it_exchange_basic_coupons_get_total_discount_for_cart( false, array(
			'format_price' => false
		) ) );

		WP_Mock::wpFunction( 'it_exchange_basic_coupons_valid_product_for_coupon', array(
			'return_in_order' => array( true, false )
		) );

		$this->assertEquals( '0.50', it_exchange_basic_coupons_get_total_discount_for_cart( false, array(
			'format_price' => false
		) ) );
	}
}