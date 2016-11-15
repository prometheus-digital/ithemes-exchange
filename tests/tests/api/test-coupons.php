<?php
/**
 * Contains tests for the coupons API.
 *
 * @since   1.35
 * @license GPLv2
 */

/**
 * Class IT_Exchange_API_Coupons_Test
 *
 * @group coupons-api
 */
class IT_Exchange_API_Coupons_Test extends IT_Exchange_UnitTestCase {
	/**
	 * Do custom initialization.
	 */
	public function setUp() {
		parent::setUp();

		$GLOBALS['it_exchange']['coupon_types']      = array();
		$GLOBALS['it_exchange']['coupon_types_meta'] = array();
	}

	public function test_get_coupon() {

		$ID = $this->coupon_factory->create( array(
			'code' => 'MYCODE'
		) );

		$coupon = it_exchange_get_coupon( $ID );

		$this->assertInstanceOf( 'IT_Exchange_Coupon', $coupon );
		$this->assertEquals( 'MYCODE', $coupon->get_code() );
	}

	public function test_get_coupon_from_code() {

		add_filter( 'it_exchange_get_my-test_coupon_from_code', function ( $val, $code ) {
			return $code;
		}, 10, 2 );

		$this->assertEquals( 'MYCODE', it_exchange_get_coupon_from_code( 'MYCODE', 'my-test' ) );
	}

	public function test_get_coupons() {

		$ids = $this->coupon_factory->create_many( 3 );

		$coupons = it_exchange_get_coupons();
		$this->assertContainsOnlyInstancesOf( 'IT_Exchange_Coupon', $coupons );

		$ret_ids = array_map( function ( IT_Exchange_Coupon $coupon ) {
			return $coupon->get_ID();
		}, $coupons );

		$this->assertEqualSets( $ids, $ret_ids );
	}

	public function test_register_coupon_type() {

		it_exchange_register_coupon_type( 'my-type' );

		$this->assertTrue( it_exchange_supports_coupon_type( 'my-type' ) );
	}

	public function test_register_coupon_type_rejects_non_child_class() {

		$this->setExpectedException( 'Exception' );

		it_exchange_register_coupon_type( 'my-type', 'Basic_Object' );
	}

	public function test_get_coupon_types() {

		it_exchange_register_coupon_type( 'my-type' );
		$this->assertContains( 'my-type', it_exchange_get_coupon_types() );
	}

	public function test_get_coupon_type_class() {

		eval( 'class IT_Exchange_My_Coupon_Type extends IT_Exchange_Coupon {}' );

		it_exchange_register_coupon_type( 'my-type', 'IT_Exchange_My_Coupon_Type' );

		$this->assertEquals( 'IT_Exchange_My_Coupon_Type', it_exchange_get_coupon_type_class( 'my-type' ) );
	}

	public function test_get_applied_coupons() {

		it_exchange_register_coupon_type( 'my-type' );
		it_exchange_register_coupon_type( 'my-type2' );

		add_filter( 'it_exchange_get_applied_my-type_coupons', function () {
			return array( 'a' );
		} );

		add_filter( 'it_exchange_get_applied_my-type2_coupons', function () {
			return array( 'b' );
		} );

		$set = array( 'my-type' => array( 'a' ), 'my-type2' => array( 'b' ) );

		$this->assertEqualSets( $set, it_exchange_get_applied_coupons() );
	}

	public function test_accepting_coupon_type() {

		add_filter( 'it_exchange_accepting_my-type_coupons', function () {
			return true;
		} );

		$this->assertTrue( it_exchange_accepting_coupon_type( 'my-type' ) );
	}

	public function test_get_coupon_type_apply_field() {

		add_filter( 'it_exchange_apply_my-type_coupon_field', function () {
			return 'a';
		} );

		$this->assertEquals( 'a', it_exchange_get_coupon_type_apply_field( 'my-type' ) );
	}

	public function test_get_remove_coupon_html() {

		add_filter( 'it_exchange_remove_my-type_coupon_html', function ( $val, $code ) {
			return $code;
		}, 10, 2 );

		$this->assertEquals( 'MYCODE', it_exchange_get_remove_coupon_html( 'my-type', 'MYCODE' ) );
	}

	public function test_apply_coupon() {

		add_filter( 'it_exchange_apply_coupon_to_my-type', function () {
			return true;
		} );

		$this->assertTrue( it_exchange_apply_coupon( 'my-type', 'MYCODE' ) );
	}

	public function test_remove_coupon() {

		add_filter( 'it_exchange_remove_coupon_for_my-type', function () {
			return true;
		} );

		$this->assertTrue( it_exchange_remove_coupon( 'my-type', 'MYCODE' ) );
	}

	public function test_get_total_coupons_discount() {

		it_exchange_register_coupon_type( 'my-type' );
		it_exchange_register_coupon_type( 'my-type2' );

		add_filter( 'it_exchange_get_total_discount_for_my-type', function () {
			return '2.50';
		} );

		add_filter( 'it_exchange_get_total_discount_for_my-type2', function () {
			return '5.00';
		} );

		$total = it_exchange_get_total_coupons_discount( false, array( 'format_price' => false ) );
		$this->assertEquals( '7.50', $total );
	}
}
