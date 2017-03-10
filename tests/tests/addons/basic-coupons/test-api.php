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

		$GLOBALS['it_exchange']['session']->clear_session();
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
			'args'   => array( 'cart', '*' ),
			'return' => array( $coupon )
		) );

		$this->assertFalse( it_exchange_basic_coupons_accepting_cart_coupons() );
	}

	public function test_apply_to_cart() {

		$product = $this->product_factory->create();
		it_exchange_add_product_to_shopping_cart( $product );

		/** @var IT_Exchange_Cart_Coupon $coupon */
		$coupon = $this->coupon_factory->create_and_get();

		$this->assertTrue( it_exchange_apply_coupon( 'cart', $coupon->get_code() ) );

		$applied = it_exchange_basic_coupons_applied_cart_coupons();

		$this->assertInternalType( 'array', $applied );

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

		$p1 = $this->product_factory->create( array(
			'base-price' => '5.00'
		) );
		$p2 = $this->product_factory->create( array(
			'base-price' => '15.00'
		) );

		$coupon = $this->coupon_factory->create_and_get( array(
			'code'      => 'CODE',
			'post_meta' => array(
				'_it-basic-apply-discount' => IT_Exchange_Cart_Coupon::APPLY_CART,
				'_it-basic-amount-type'    => IT_Exchange_Cart_Coupon::TYPE_FLAT,
				'_it-basic-amount-number'  => it_exchange_convert_to_database_number( 2.50 ),
			)
		) );

		it_exchange_add_product_to_shopping_cart( $p1, 1 );
		it_exchange_add_product_to_shopping_cart( $p2, 2 );

		$this->assertTrue( it_exchange_apply_coupon( 'cart', 'CODE' ) );

		$this->assertEquals( '2.50', it_exchange_basic_coupons_get_total_discount_for_cart( false, array(
			'format_price' => false
		) ) );

		update_post_meta( $coupon->ID, '_it-basic-limit-product', true );
		update_post_meta( $coupon->ID, '_it-basic-excluded-products', array( $p2 ) );

		$this->assertEquals( '2.50', it_exchange_basic_coupons_get_total_discount_for_cart( false, array(
			'format_price' => false
		) ) );
	}

	public function test_get_total_discount_for_cart__percent_and_cart() {

		$p1 = $this->product_factory->create( array(
			'base-price' => '5.00'
		) );
		$p2 = $this->product_factory->create( array(
			'base-price' => '15.00'
		) );

		it_exchange_add_product_to_shopping_cart( $p1, 1 );
		it_exchange_add_product_to_shopping_cart( $p2, 2 );

		$coupon = $this->coupon_factory->create_and_get( array(
			'code'      => 'CODE',
			'post_meta' => array(
				'_it-basic-apply-discount' => IT_Exchange_Cart_Coupon::APPLY_CART,
				'_it-basic-amount-type'    => IT_Exchange_Cart_Coupon::TYPE_PERCENT,
				'_it-basic-amount-number'  => it_exchange_convert_to_database_number( 10.00 ),
			)
		) );

		$this->assertTrue( it_exchange_apply_coupon( 'cart', 'CODE' ) );

		$this->assertEquals( '3.50', it_exchange_basic_coupons_get_total_discount_for_cart( false, array(
			'format_price' => false
		) ) );

		update_post_meta( $coupon->ID, '_it-basic-limit-product', true );
		update_post_meta( $coupon->ID, '_it-basic-excluded-products', array( $p2 ) );

		$this->assertEquals( '0.50', it_exchange_basic_coupons_get_total_discount_for_cart( false, array(
			'format_price' => false
		) ) );
	}

	public function test_get_total_discount_for_cart__flat_and_product() {

		$p1 = $this->product_factory->create( array(
			'base-price' => '5.00'
		) );
		$p2 = $this->product_factory->create( array(
			'base-price' => '15.00'
		) );

		it_exchange_add_product_to_shopping_cart( $p1, 1 );
		it_exchange_add_product_to_shopping_cart( $p2, 2 );

		$coupon = $this->coupon_factory->create_and_get( array(
			'code'      => 'CODE',
			'post_meta' => array(
				'_it-basic-apply-discount' => IT_Exchange_Cart_Coupon::APPLY_PRODUCT,
				'_it-basic-amount-type'    => IT_Exchange_Cart_Coupon::TYPE_FLAT,
				'_it-basic-amount-number'  => it_exchange_convert_to_database_number( 2.50 ),
			)
		) );

		$this->assertTrue( it_exchange_apply_coupon( 'cart', 'CODE' ) );

		$this->assertEquals( '7.50', it_exchange_basic_coupons_get_total_discount_for_cart( false, array(
			'format_price' => false
		) ) );

		update_post_meta( $coupon->ID, '_it-basic-limit-product', true );
		update_post_meta( $coupon->ID, '_it-basic-excluded-products', array( $p2 ) );

		$this->assertEquals( '2.50', it_exchange_basic_coupons_get_total_discount_for_cart( false, array(
			'format_price' => false
		) ) );
	}

	public function test_get_total_discount_for_cart__percent_and_product() {

		$p1 = $this->product_factory->create( array(
			'base-price' => '5.00'
		) );
		$p2 = $this->product_factory->create( array(
			'base-price' => '15.00'
		) );

		it_exchange_add_product_to_shopping_cart( $p1, 1 );
		it_exchange_add_product_to_shopping_cart( $p2, 2 );

		$coupon = $this->coupon_factory->create_and_get( array(
			'code'      => 'CODE',
			'post_meta' => array(
				'_it-basic-apply-discount' => IT_Exchange_Cart_Coupon::APPLY_PRODUCT,
				'_it-basic-amount-type'    => IT_Exchange_Cart_Coupon::TYPE_PERCENT,
				'_it-basic-amount-number'  => it_exchange_convert_to_database_number( 10.00 ),
			)
		) );

		$this->assertTrue( it_exchange_apply_coupon( 'cart', 'CODE' ) );

		$this->assertEquals( '3.50', it_exchange_basic_coupons_get_total_discount_for_cart( false, array(
			'format_price' => false
		) ) );

		update_post_meta( $coupon->ID, '_it-basic-limit-product', true );
		update_post_meta( $coupon->ID, '_it-basic-excluded-products', array( $p2 ) );

		$this->assertEquals( '0.50', it_exchange_basic_coupons_get_total_discount_for_cart( false, array(
			'format_price' => false
		) ) );
	}

	public function test_valid_product_for_coupon_is_true_if_not_product_limited() {

		$coupon = $this->getMockBuilder( 'IT_Exchange_Cart_Coupon' )->disableOriginalConstructor()->getMock();
		$coupon->method( 'is_product_limited' )->willReturn( false );

		$this->assertTrue( it_exchange_basic_coupons_valid_product_for_coupon( array(), $coupon ) );
	}

	public function test_valid_product_for_coupon_sale_items() {

		$coupon = $this->getMockBuilder( 'IT_Exchange_Cart_Coupon' )->disableOriginalConstructor()->getMock();
		$coupon->method( 'is_sale_item_excluded' )->willReturn( true );

		WP_Mock::wpFunction( 'it_exchange_is_product_sale_active', array(
			'return' => true
		) );

		$this->assertFalse( it_exchange_basic_coupons_valid_product_for_coupon( array( 'product_id' => 1 ), $coupon ) );
	}

	public function test_valid_product_for_coupon_product_categories() {

		wp_set_current_user( 1 );

		$term1 = self::factory()->term->create_and_get( array(
			'taxonomy' => 'it_exchange_category',
			'name'     => 'Shirts'
		) );
		$term2 = self::factory()->term->create_and_get( array(
			'taxonomy' => 'it_exchange_category',
			'name'     => 'Pants'
		) );

		$p1 = $this->product_factory->create( array(
			'tax_input' => array(
				'it_exchange_category' => array( $term1->term_id )
			)
		) );
		$p2 = $this->product_factory->create( array(
			'tax_input' => array(
				'it_exchange_category' => array( $term2->term_id )
			)
		) );

		$coupon = $this->getMockBuilder( 'IT_Exchange_Cart_Coupon' )->disableOriginalConstructor()->getMock();
		$coupon->method( 'is_product_limited' )->willReturn( true );
		$coupon->method( 'get_product_categories' )->willReturn( array( $term1 ) );
		$coupon->method( 'get_limited_products' )->willReturn( array() );
		$coupon->method( 'get_excluded_products' )->willReturn( array() );

		$this->assertTrue( it_exchange_basic_coupons_valid_product_for_coupon( array( 'product_id' => $p1 ), $coupon ) );
		$this->assertFalse( it_exchange_basic_coupons_valid_product_for_coupon( array( 'product_id' => $p2 ), $coupon ) );

		wp_set_current_user( 0 );
	}

	public function test_valid_product_for_coupon_limited_products() {

		$p1 = $this->product_factory->create_and_get();
		$p2 = $this->product_factory->create_and_get();

		$coupon = $this->getMockBuilder( 'IT_Exchange_Cart_Coupon' )->disableOriginalConstructor()->getMock();
		$coupon->method( 'is_product_limited' )->willReturn( true );
		$coupon->method( 'get_product_categories' )->willReturn( array() );
		$coupon->method( 'get_limited_products' )->willReturn( array( $p1 ) );
		$coupon->method( 'get_excluded_products' )->willReturn( array() );

		$this->assertTrue( it_exchange_basic_coupons_valid_product_for_coupon( array( 'product_id' => $p1->ID ), $coupon ) );
		$this->assertFalse( it_exchange_basic_coupons_valid_product_for_coupon( array( 'product_id' => $p2->ID ), $coupon ) );
	}

	public function test_valid_product_for_coupon_limited_products_and_categories() {

		wp_set_current_user( 1 );

		$term1 = self::factory()->term->create_and_get( array(
			'taxonomy' => 'it_exchange_category',
			'name'     => 'Shirts'
		) );
		$term2 = self::factory()->term->create_and_get( array(
			'taxonomy' => 'it_exchange_category',
			'name'     => 'Pants'
		) );

		$p1 = $this->product_factory->create_and_get( array(
			'tax_input' => array(
				'it_exchange_category' => array( $term1->term_id )
			)
		) );
		$p2 = $this->product_factory->create_and_get( array(
			'tax_input' => array(
				'it_exchange_category' => array( $term2->term_id )
			)
		) );

		$coupon = $this->getMockBuilder( 'IT_Exchange_Cart_Coupon' )->disableOriginalConstructor()->getMock();
		$coupon->method( 'is_product_limited' )->willReturn( true );
		$coupon->method( 'get_product_categories' )->willReturn( array( $term1 ) );
		$coupon->method( 'get_limited_products' )->willReturn( array( $p2 ) );
		$coupon->method( 'get_excluded_products' )->willReturn( array() );

		$this->assertTrue( it_exchange_basic_coupons_valid_product_for_coupon( array( 'product_id' => $p1->ID ), $coupon ) );
		$this->assertTrue( it_exchange_basic_coupons_valid_product_for_coupon( array( 'product_id' => $p2->ID ), $coupon ) );

		wp_set_current_user( 0 );
	}

	public function test_valid_product_for_coupon_excluded_products() {

		$p1 = $this->product_factory->create_and_get();
		$p2 = $this->product_factory->create_and_get();

		$coupon = $this->getMockBuilder( 'IT_Exchange_Cart_Coupon' )->disableOriginalConstructor()->getMock();
		$coupon->method( 'is_product_limited' )->willReturn( true );
		$coupon->method( 'get_product_categories' )->willReturn( array() );
		$coupon->method( 'get_limited_products' )->willReturn( array() );
		$coupon->method( 'get_excluded_products' )->willReturn( array( $p2 ) );

		$this->assertTrue( it_exchange_basic_coupons_valid_product_for_coupon( array( 'product_id' => $p1->ID ), $coupon ) );
		$this->assertFalse( it_exchange_basic_coupons_valid_product_for_coupon( array( 'product_id' => $p2->ID ), $coupon ) );
	}

	public function test_valid_product_for_coupon_limited_products_categories_and_excluded() {

		wp_set_current_user( 1 );

		$term1 = self::factory()->term->create_and_get( array(
			'taxonomy' => 'it_exchange_category',
			'name'     => 'Shirts'
		) );
		$term2 = self::factory()->term->create_and_get( array(
			'taxonomy' => 'it_exchange_category',
			'name'     => 'Pants'
		) );

		$p1 = $this->product_factory->create_and_get( array(
			'tax_input' => array(
				'it_exchange_category' => array( $term1->term_id )
			)
		) );
		$p2 = $this->product_factory->create_and_get( array(
			'tax_input' => array(
				'it_exchange_category' => array( $term1->term_id )
			)
		) );
		$p3 = $this->product_factory->create_and_get( array(
			'tax_input' => array(
				'it_exchange_category' => array( $term2->term_id )
			)
		) );

		$coupon = $this->getMockBuilder( 'IT_Exchange_Cart_Coupon' )->disableOriginalConstructor()->getMock();
		$coupon->method( 'is_product_limited' )->willReturn( true );
		$coupon->method( 'get_product_categories' )->willReturn( array( $term1 ) );
		$coupon->method( 'get_limited_products' )->willReturn( array( $p3 ) );
		$coupon->method( 'get_excluded_products' )->willReturn( array( $p2 ) );

		$this->assertTrue( it_exchange_basic_coupons_valid_product_for_coupon( array( 'product_id' => $p1->ID ), $coupon ) );
		$this->assertFalse( it_exchange_basic_coupons_valid_product_for_coupon( array( 'product_id' => $p2->ID ), $coupon ) );
		$this->assertTrue( it_exchange_basic_coupons_valid_product_for_coupon( array( 'product_id' => $p3->ID ), $coupon ) );

		wp_set_current_user( 0 );
	}

	public function test_remove_coupon_from_cart() {

		/** @var IT_Exchange_Cart_Coupon $coupon */
		$coupon = $this->coupon_factory->create_and_get();

		$p1 = $this->product_factory->create();
		it_exchange_add_product_to_shopping_cart( $p1 );

		it_exchange_basic_coupons_apply_to_cart( false, array( 'code' => $coupon->get_code() ) );
		it_exchange_basic_coupons_remove_coupon_from_cart( false, array( 'code' => $coupon->get_code() ) );

		$this->assertFalse( it_exchange_basic_coupons_applied_cart_coupons() );
	}

	public function test_get_discount_label_flat() {

		$coupon = $this->getMockBuilder( 'IT_Exchange_Cart_Coupon' )->disableOriginalConstructor()->getMock();
		$coupon->method( 'get_amount_type' )->willReturn( IT_Exchange_Cart_Coupon::TYPE_FLAT );
		$coupon->method( 'get_amount_number' )->willReturn( 5 );

		WP_Mock::wpFunction( 'it_exchange_format_price', array(
			'args'   => 5,
			'return' => '$5.00'
		) );

		$this->assertEquals( '$5.00', it_exchange_basic_coupons_get_discount_label( false, array( 'coupon' => $coupon ) ) );
	}

	public function test_get_discount_label_percent() {

		$coupon = $this->getMockBuilder( 'IT_Exchange_Cart_Coupon' )->disableOriginalConstructor()->getMock();
		$coupon->method( 'get_amount_type' )->willReturn( IT_Exchange_Cart_Coupon::TYPE_PERCENT );
		$coupon->method( 'get_amount_number' )->willReturn( 5 );

		$this->assertEquals( '5%', it_exchange_basic_coupons_get_discount_label( false, array( 'coupon' => $coupon ) ) );
	}

}
