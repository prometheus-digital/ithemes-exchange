<?php
/**
 * Unit tests for the add-ons API functions.
 *
 * @since   1.35
 * @license GPLv2
 */

/**
 * Class IT_Exchange_API_Addons_Test
 *
 * @group addons-api
 */
class IT_Exchange_API_Addons_Test extends IT_Exchange_UnitTestCase {

	/**
	 * Unregister an add-on.
	 *
	 * @since 1.35
	 *
	 * @param string $slug
	 */
	protected function _unregister( $slug ) {
		unset( $GLOBALS['it_exchange']['add_ons']['registered'][ $slug ] );
	}

	public function test_register_addon() {

		$addon = array(
			'name' => 'Test Addon',
			'file' => __FILE__
		);

		it_exchange_register_addon( 'test-addon', $addon );

		$got = it_exchange_get_addon( 'test-addon' );

		$this->assertNotFalse( $got );

		$keys = array(
			'slug',
			'name',
			'author',
			'author_url',
			'description',
			'file',
			'options',
			'basename'
		);

		foreach ( $keys as $key ) {
			$this->assertArrayHasKey( $key, $got );
		}

		$this->_unregister( 'test-addon' );
	}

	/**
	 * @dataProvider _dp_register_addon_required_parameters
	 */
	public function test_register_addon_required_parmeters( $slug, $params ) {
		$this->assertWPError( it_exchange_register_addon( $slug, $params ) );
	}

	public function _dp_register_addon_required_parameters() {
		return array(
			array(
				'',
				array()
			),
			array(
				'test-addon',
				array()
			),
			array(
				'test-addon',
				array( 'name' => 'Test Addon' ),
			)
		);
	}

	public function test_register_addon_default_category() {

		$this->assertFalse( it_exchange_get_addon( 'test-addon' ) );

		it_exchange_register_addon( 'test-addon', array(
			'name' => 'Test Addon',
			'file' => __FILE__
		) );

		$addon = it_exchange_get_addon( 'test-addon' );
		$this->assertEquals( 'other', $addon['options']['category'] );

		$this->_unregister( 'test-addon' );
	}

	public function test_register_product_type_addon_with_class() {

		$error = it_exchange_register_addon( 'test-product-type', array(
			'name'    => 'Test Product Type',
			'file'    => __FILE__,
			'options' => array(
				'class'    => 'Basic_Object',
				'category' => 'product-type'
			)
		) );

		$this->assertWPError( $error );

		$this->_unregister( 'test-addon' );
	}

	public function test_register_addon_category() {

		it_exchange_register_addon_category( 'my-test-category', 'My Test Category', 'This is my test category' );

		$this->assertArrayHasKey( 'my-test-category', it_exchange_get_addon_categories() );
	}

	public function test_register_addon_category_required_parameters() {

		$this->assertWPError( it_exchange_register_addon_category( '', '', '' ) );
		$this->assertWPError( it_exchange_register_addon_category( 'my-test-category', '', '' ) );
		$this->assertNotWPError( it_exchange_register_addon_category( 'my-test-category2', 'Name', '' ) );
	}

	public function test_temporarily_load_addon() {

		it_exchange_register_addon( 'test-addon', array(
			'name' => 'Test Addon',
			'file' => dirname( __FILE__ ) . '/../../data/1.php'
		) );

		$this->assertFalse( function_exists( 'it_exchange_my_super_unique_function_name' ) );

		it_exchange_temporarily_load_addon( 'test-addon' );

		$this->assertTrue( function_exists( 'it_exchange_my_super_unique_function_name' ) );

		$this->_unregister( 'test-addon' );
	}

	public function test_temporarily_load_addons() {

		it_exchange_register_addon( 'test-addon2', array(
			'name' => 'Test Addon',
			'file' => dirname( __FILE__ ) . '/../../data/2.php'
		) );

		it_exchange_register_addon( 'test-addon3', array(
			'name' => 'Test Addon',
			'file' => dirname( __FILE__ ) . '/../../data/3.php'
		) );

		$this->assertFalse( function_exists( 'it_exchange_my_super_unique_function_name2' ) );
		$this->assertFalse( function_exists( 'it_exchange_my_super_unique_function_name3' ) );

		it_exchange_temporarily_load_addons( array(
			it_exchange_get_addon( 'test-addon2' ),
			it_exchange_get_addon( 'test-addon3' ),
		) );

		$this->assertTrue( function_exists( 'it_exchange_my_super_unique_function_name2' ) );
		$this->assertTrue( function_exists( 'it_exchange_my_super_unique_function_name3' ) );

		$this->_unregister( 'test-addon2' );
		$this->_unregister( 'test-addon3' );
	}

	public function test_filter_addons_by_category() {

		$shipping = it_exchange_filter_addons_by_category(
			it_exchange_get_addons(), array( 'shipping' )
		);

		$this->assertArrayHasKey( 'simple-shipping', $shipping );
		$this->assertArrayNotHasKey( 'taxes-simple', $shipping );
	}

	public function test_enable_disable_addons() {

		// disable bootstrap logic to load all add-ons
		remove_all_filters( 'it_exchange_get_enabled_addons' );

		$this->assertArrayNotHasKey( 'simple-shipping', it_exchange_get_enabled_addons() );
		it_exchange_enable_addon( 'simple-shipping' );
		$this->assertArrayHasKey( 'simple-shipping', it_exchange_get_enabled_addons( array(
			'break_cache' => true
		) ) );
		$this->assertTrue( it_exchange_is_addon_enabled( 'simple-shipping' ) );

		it_exchange_disable_addon( 'simple-shipping' );
		$this->assertArrayNotHasKey( 'simple-shipping', it_exchange_get_enabled_addons( array(
			'break_cache' => true
		) ) );
		$this->assertFalse( it_exchange_is_addon_enabled( 'simple-shipping' ) );
	}

	public function test_is_addon_registered() {
		$this->assertFalse( it_exchange_is_addon_registered( 'im not real' ) );
		$this->assertTrue( it_exchange_is_addon_registered( 'simple-shipping' ) );
	}

	public function test_addon_supports() {

		it_exchange_register_addon( 'test-addon', array(
			'name'    => 'Test Addon',
			'file'    => __FILE__,
			'options' => array(
				'supports' => array( 'feature-a' => true, 'feature-b' => false )
			)
		) );

		$this->assertTrue( it_exchange_addon_supports( 'test-addon', 'feature-a' ) );
		$this->assertFalse( it_exchange_addon_supports( 'test-addon', 'feature-b' ) );
		$this->assertFalse( it_exchange_addon_supports( 'test-addon', 'feature-c' ) );

		$this->_unregister( 'test-addon' );
	}

	public function test_add_addon_support() {

		it_exchange_register_addon( 'test-addon', array(
			'name'    => 'Test Addon',
			'file'    => __FILE__,
			'options' => array(
				'supports' => array( 'feature-a' => true, 'feature-b' => false )
			)
		) );
		it_exchange_add_addon_support( 'test-addon', 'feature-c' );

		$this->assertTrue( it_exchange_addon_supports( 'test-addon', 'feature-c' ) );
		$this->assertFalse( it_exchange_addon_supports( 'test-addon', 'feature-b' ) );

		it_exchange_add_addon_support( 'test-addon', 'feature-b' );
		$this->assertTrue( it_exchange_addon_supports( 'test-addon', 'feature-b' ) );

		$this->_unregister( 'test-addon' );
	}

	public function test_remove_addon_support() {

		it_exchange_register_addon( 'test-addon', array(
			'name'    => 'Test Addon',
			'file'    => __FILE__,
			'options' => array(
				'supports' => array( 'feature-a' => true, 'feature-b' => false )
			)
		) );

		it_exchange_remove_addon_support( 'test-addon', 'feature-a' );

		$this->assertFalse( it_exchange_addon_supports( 'test-addon', 'feature-a' ) );

		$this->_unregister( 'test-addon' );
	}

	public function test_is_core_addon() {

		$this->assertTrue( it_exchange_is_core_addon( 'simple-shipping' ) );

		it_exchange_register_addon( 'test-addon', array(
			'name' => 'Test Addon',
			'file' => WP_CONTENT_DIR . '/index.php'
		) );

		$this->assertFalse( it_exchange_is_core_addon( 'test-addon' ) );
	}
}