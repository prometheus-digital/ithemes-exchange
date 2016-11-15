<?php
/**
 * Contains tests for the product features API functions.
 *
 * @since   1.35
 * @license GPLv2
 */

/**
 * Class IT_Exchange_API_Product_Features_Test
 *
 * This entire set of tests are hacky, beware.
 *
 * @group product-features-api
 */
class IT_Exchange_API_Product_Features_Test extends IT_Exchange_UnitTestCase {

	public function test_supports_feature() {
		add_filter( 'it_exchange_product_supports_feature_my-feature', function ( $val, $prod ) {

			if ( $prod !== 1 ) {
				PHPUnit_Framework_Assert::fail();
			}

			return true;
		}, 10, 2 );

		$this->assertTrue( it_exchange_product_supports_feature( 1, 'my-feature' ) );
	}

	public function test_has_feature() {

		add_filter( 'it_exchange_product_has_feature_my-feature', function ( $val, $prod ) {

			if ( $prod !== 1 ) {
				PHPUnit_Framework_Assert::fail();
			}

			return true;
		}, 10, 2 );

		$this->assertTrue( it_exchange_product_has_feature( 1, 'my-feature' ) );
	}

	public function test_update_feature() {

		// hack
		global $exchange_test_ran;
		$exchange_test_ran = false;

		add_action( 'it_exchange_update_product_feature_my-feature', function ( $prod, $feature ) {

			if ( $prod !== 1 || $feature !== 'new' ) {
				PHPUnit_Framework_Assert::fail();
			}

			$GLOBALS['exchange_test_ran'] = true;
		}, 10, 2 );

		it_exchange_update_product_feature( 1, 'my-feature', 'new' );

		if ( ! $exchange_test_ran ) {
			$this->fail();
		}
	}

	public function test_get_feature() {

		add_filter( 'it_exchange_get_product_feature_my-feature', function ( $val, $prod ) {

			if ( $prod !== 1 ) {
				PHPUnit_Framework_Assert::fail();
			}

			return true;
		}, 10, 2 );

		$this->assertTrue( it_exchange_get_product_feature( 1, 'my-feature' ) );
	}

	public function test_add_feature_support_to_product_type() {

		it_exchange_register_product_feature( 'my-feature' );
		it_exchange_add_feature_support_to_product_type( 'my-feature', 'simple-product-type' );

		$this->assertTrue( it_exchange_product_type_supports_feature( 'simple-product-type', 'my-feature' ) );

		it_exchange_remove_feature_support_for_product_type( 'my-feature', 'simple-product-type' );

		$this->assertFalse( it_exchange_product_type_supports_feature( 'simple-product-type', 'my-feature' ) );
	}

	public function test_product_type_supports_feature_from_addon() {

		it_exchange_register_product_feature( 'my-cool-feature' );

		it_exchange_register_addon( 'my-product-type', array(
			'name'     => 'My Product Type',
			'file'     => __FILE__,
			'options'  => array(
				'supports' => array(
					'my-cool-feature' => true
				)
			),
			'category' => 'product-type'
		) );

		$this->assertTrue( it_exchange_product_type_supports_feature(
			'my-product-type', 'my-cool-feature'
		) );
	}

}
