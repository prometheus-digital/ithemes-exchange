<?php
/**
 * Test the coupon model.
 *
 * @since   1.35
 * @license GPLv2
 */


/**
 * Class IT_Exchange_Addons_Basic_Coupons_Model_Test
 *
 * @group addons/basic-coupons
 */
class IT_Exchange_Addons_Basic_Coupons_Model_Test extends IT_Exchange_UnitTestCase {
	/**
	 * @inheritDoc
	 */
	public function setUp() {
		parent::setUp();

		$GLOBALS['it_exchange']['session']->clear_session();
	}

	public function test_increment_usage() {

		/** @var IT_Exchange_Cart_Coupon $coupon */
		$coupon = $this->coupon_factory->create_and_get( array(
			'post_meta' => array(
				'_it-basic-limit-quantity' => true,
				'_it-basic-quantity'       => 2
			)
		) );

		$coupon->increment_usage( new stdClass() );

		$this->assertEquals( 1, $coupon->get_remaining_quantity() );
	}

	public function test_decrement_usage() {

		/** @var IT_Exchange_Cart_Coupon $coupon */
		$coupon = $this->coupon_factory->create_and_get( array(
			'post_meta' => array(
				'_it-basic-limit-quantity' => true,
				'_it-basic-quantity'       => 2
			)
		) );

		$coupon->decrement_usage( new stdClass() );

		$this->assertEquals( 3, $coupon->get_remaining_quantity() );
	}

	public function test_get_data_for_transaction_object() {

		/** @var IT_Exchange_Cart_Coupon $coupon */
		$coupon = $this->coupon_factory->create_and_get( array(
			'post_meta' => array(
				'_it-basic-amount-number' => 15,
				'_it-basic-amount-type'   => IT_Exchange_Cart_Coupon::TYPE_PERCENT,
				'_it-basic-start-date'    => '2016-02-29 11:15:30',
				'_it-basic-end-date'      => '2016-03-29 2:45:30',
			)
		) );

		$data = $coupon->get_data_for_transaction_object();

		$this->assertArrayHasKey( 'amount_number', $data, "Missing 'amount_number'" );
		$this->assertArrayHasKey( 'amount_type', $data, "Missing 'amount_type'" );
		$this->assertArrayHasKey( 'start_date', $data, "Missing 'start_date'" );
		$this->assertArrayHasKey( 'end_date', $data, "Missing 'end_date'" );

		$this->assertEquals( 15, $data['amount_number'], "Invalid 'amount_number'" );
		$this->assertEquals( IT_Exchange_Cart_Coupon::TYPE_PERCENT, $data['amount_type'], "Invalid 'amount_type'" );
		$this->assertEquals( '2016-02-29 11:15:30', $data['start_date'], "Invalid 'start_date'" );
		$this->assertEquals( '2016-03-29 02:45:30', $data['end_date'], "Invalid 'end_date'" );
	}

	public function test_validate_quantity() {

		$this->setExpectedException( 'Exception', '', IT_Exchange_Cart_Coupon::E_NO_QUANTITY );

		/** @var IT_Exchange_Cart_Coupon $coupon */
		$coupon = $this->coupon_factory->create_and_get( array(
			'post_meta' => array(
				'_it-basic-limit-quantity' => true,
				'_it-basic-quantity'       => 0
			)
		) );

		$coupon->validate();
	}

	public function test_validate_customer() {

		$this->setExpectedException( 'Exception', '', IT_Exchange_Cart_Coupon::E_INVALID_CUSTOMER );

		/** @var IT_Exchange_Cart_Coupon $coupon */
		$coupon = $this->coupon_factory->create_and_get( array(
			'post_meta' => array(
				'_it-basic-limit-customer' => true,
				'_it-basic-customer'       => 1
			)
		) );

		wp_set_current_user( 0 );
		$coupon->validate();
	}

	public function test_validate_products() {

		$this->setExpectedException( 'Exception', '', IT_Exchange_Cart_Coupon::E_INVALID_PRODUCTS );

		/** @var IT_Exchange_Cart_Coupon $coupon */
		$coupon = $this->coupon_factory->create_and_get( array(
			'post_meta' => array(
				'_it-basic-limit-product' => true
			)
		) );

		$cart_product = array( 'product_id' => 1 );

		WP_Mock::wpFunction( 'it_exchange_get_cart_products', array(
			'return' => array( $cart_product )
		) );

		WP_Mock::wpFunction( 'it_exchange_basic_coupons_valid_product_for_coupon', array(
			'return' => false
		) );

		$coupon->validate();
	}

	public function test_validate_products_empty_cart() {

		$this->setExpectedException( 'Exception', '', IT_Exchange_Cart_Coupon::E_INVALID_PRODUCTS );

		/** @var IT_Exchange_Cart_Coupon $coupon */
		$coupon = $this->coupon_factory->create_and_get( array(
			'post_meta' => array(
				'_it-basic-limit-product' => true
			)
		) );

		$coupon->validate();
	}

	public function test_validate_products_start_date() {

		/** @var IT_Exchange_Cart_Coupon $coupon */
		$coupon = $this->coupon_factory->create_and_get( array(
			'post_meta' => array(
				'_it-basic-start-date' => date( 'Y-m-d H:i:s', strtotime( '+1 year' ) ),
			)
		) );

		it_exchange_add_product_to_shopping_cart( $this->product_factory->create() );

		WP_Mock::wpFunction( 'it_exchange_basic_coupons_valid_product_for_coupon', array(
			'return' => true
		) );

		$this->setExpectedException( 'Exception', '', IT_Exchange_Cart_Coupon::E_INVALID_START );

		$coupon->validate();
	}

	public function test_validate_products_end_date() {

		/** @var IT_Exchange_Cart_Coupon $coupon */
		$coupon = $this->coupon_factory->create_and_get( array(
			'post_meta' => array(
				'_it-basic-end-date' => date( 'Y-m-d H:i:s', strtotime( '-1 year' ) ),
			)
		) );

		it_exchange_add_product_to_shopping_cart( $this->product_factory->create() );
		
		WP_Mock::wpFunction( 'it_exchange_basic_coupons_valid_product_for_coupon', array(
			'return' => true
		) );

		$this->setExpectedException( 'Exception', '', IT_Exchange_Cart_Coupon::E_INVALID_END );

		$coupon->validate();
	}
}
