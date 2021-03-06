<?php
/**
 * Contains tests for the misc API functions.
 *
 * @since   1.35
 * @license GPLv2
 */

/**
 * Class IT_Exchange_API_Misc_Test
 *
 * @group misc-api
 */
class IT_Exchange_API_Misc_Test extends IT_Exchange_UnitTestCase {

	public function _dp_convert_to_database_number_usd_before_comma_per() {
		return array(
			array( '$2.00', 200 ),
			array( '$2.50', 250 ),
			array( '$20.00', 2000 ),
			array( '$20.40', 2040 ),
			array( '$200.00', 20000 ),
			array( '$200.22', 20022 ),
			array( '$2,000.00', 200000 ),
			array( '$2,100.00', 210000 ),
			array( '$2,150.00', 215000 ),
			array( '$2,153.00', 215300 ),
			array( '$2,153.22', 215322 ),
			array( '$2,153.02', 215302 ),
			array( '$2,230,153.02', 223015302 ),
			array( 2230153.02, 223015302 ),
			array( '2230153.02', 223015302 ),
			array( '2230153', 223015300 ),
			array( 2230153, 223015300 )
		);
	}

	/**
	 * @dataProvider _dp_convert_to_database_number_usd_before_comma_per
	 */
	public function test_convert_to_database_number_usd_before_comma_per( $price, $expected ) {

		add_filter( 'it_exchange_get_option-settings_general', function () {
			return array(
				'default-currency'             => 'USD',
				'currency-symbol-position'     => 'before',
				'currency-thousands-separator' => ',',
				'currency-decimals-separator'  => '.',
			);
		} );

		$this->assertEquals( $expected, it_exchange_convert_to_database_number( $price ) );
	}

	public function _dp_convert_to_database_number_usd_after_comma_per() {
		return array(
			array( '2.00$', 200 ),
			array( '2.50$', 250 ),
			array( '20.00$', 2000 ),
			array( '20.40$', 2040 ),
			array( '200.00$', 20000 ),
			array( '200.22$', 20022 ),
			array( '2,000.00$', 200000 ),
			array( '2,100.00$', 210000 ),
			array( '2,150.00$', 215000 ),
			array( '2,153.00$', 215300 ),
			array( '2,153.22$', 215322 ),
			array( '2,153.02$', 215302 ),
			array( '2,230,153.02$', 223015302 ),
			array( 2230153.02, 223015302 ),
			array( '2230153.02', 223015302 ),
			array( '2230153', 223015300 ),
			array( 2230153, 223015300 )
		);
	}

	/**
	 * @dataProvider _dp_convert_to_database_number_usd_after_comma_per
	 */
	public function test_convert_to_database_number_usd_after_comma_per( $price, $expected ) {

		add_filter( 'it_exchange_get_option-settings_general', function () {
			return array(
				'default-currency'             => 'USD',
				'currency-symbol-position'     => 'after',
				'currency-thousands-separator' => ',',
				'currency-decimals-separator'  => '.',
			);
		} );

		$this->assertEquals( $expected, it_exchange_convert_to_database_number( $price ) );
	}

	public function _dp_convert_to_database_number_usd_before_per_comma() {
		return array(
			array( '$2,00', 200 ),
			array( '$2,50', 250 ),
			array( '$20,00', 2000 ),
			array( '$20,40', 2040 ),
			array( '$200,00', 20000 ),
			array( '$200,22', 20022 ),
			array( '$2.000,00', 200000 ),
			array( '$2.100,00', 210000 ),
			array( '$2.150,00', 215000 ),
			array( '$2.153,00', 215300 ),
			array( '$2.153,22', 215322 ),
			array( '$2.153,02', 215302 ),
			array( '$2.230.153,02', 223015302 ),
			array( '2230153,02', 223015302 ),
			array( '2230153', 223015300 ),
			array( 2230153, 223015300 )
		);
	}

	/**
	 * @dataProvider _dp_convert_to_database_number_usd_before_per_comma
	 */
	public function test_convert_to_database_number_usd_before_per_comma( $price, $expected ) {

		add_filter( 'it_exchange_get_option-settings_general', function () {
			return array(
				'default-currency'             => 'USD',
				'currency-symbol-position'     => 'before',
				'currency-thousands-separator' => '.',
				'currency-decimals-separator'  => ',',
			);
		} );

		$this->assertEquals( $expected, it_exchange_convert_to_database_number( $price ) );
	}

	public function _dp_convert_to_database_number_usd_after_per_comma() {
		return array(
			array( '2,00$', 200 ),
			array( '2,50$', 250 ),
			array( '20,00$', 2000 ),
			array( '20,40$', 2040 ),
			array( '200,00$', 20000 ),
			array( '200,22$', 20022 ),
			array( '2.000,00$', 200000 ),
			array( '2.100,00$', 210000 ),
			array( '2.150,00$', 215000 ),
			array( '2.153,00$', 215300 ),
			array( '2.153,22$', 215322 ),
			array( '2.153,02$', 215302 ),
			array( '2.230.153,02$', 223015302 ),
			array( '2230153,02', 223015302 ),
			array( '2230153', 223015300 ),
			array( 2230153, 223015300 )
		);
	}

	/**
	 * @dataProvider _dp_convert_to_database_number_usd_after_per_comma
	 */
	public function test_convert_to_database_number_usd_after_per_comma( $price, $expected ) {

		add_filter( 'it_exchange_get_option-settings_general', function () {
			return array(
				'default-currency'             => 'USD',
				'currency-symbol-position'     => 'after',
				'currency-thousands-separator' => '.',
				'currency-decimals-separator'  => ',',
			);
		} );

		$this->assertEquals( $expected, it_exchange_convert_to_database_number( $price ) );
	}

	public function _dp_convert_to_database_number_inr_before_comma_per() {
		return array(
			array( '₉2.00', 200 ),
			array( '₉2.50', 250 ),
			array( '₉20.00', 2000 ),
			array( '₉20.40', 2040 ),
			array( '₉200.00', 20000 ),
			array( '₉200.22', 20022 ),
			array( '₉2,000.00', 200000 ),
			array( '₉2,100.00', 210000 ),
			array( '₉2,150.00', 215000 ),
			array( '₉2,153.00', 215300 ),
			array( '₉2,153.22', 215322 ),
			array( '₉2,153.02', 215302 ),
			array( '₉2,230,153.02', 223015302 ),
			array( 2230153.02, 223015302 ),
			array( '2230153.02', 223015302 ),
			array( '2230153', 223015300 ),
			array( 2230153, 223015300 )
		);
	}

	/**
	 * @dataProvider _dp_convert_to_database_number_inr_before_comma_per
	 */
	public function test_convert_to_database_number_inr_before_comma_per( $price, $expected ) {

		add_filter( 'it_exchange_get_option-settings_general', function () {
			return array(
				'default-currency'             => 'inr',
				'currency-symbol-position'     => 'before',
				'currency-thousands-separator' => ',',
				'currency-decimals-separator'  => '.',
			);
		} );

		$this->assertEquals( $expected, it_exchange_convert_to_database_number( $price ) );
	}

	public function _dp_convert_to_database_number_inr_after_comma_per() {
		return array(
			array( '2.00₉', 200 ),
			array( '2.50₉', 250 ),
			array( '20.00₉', 2000 ),
			array( '20.40₉', 2040 ),
			array( '200.00₉', 20000 ),
			array( '200.22₉', 20022 ),
			array( '2,000.00₉', 200000 ),
			array( '2,100.00₉', 210000 ),
			array( '2,150.00₉', 215000 ),
			array( '2,153.00₉', 215300 ),
			array( '2,153.22₉', 215322 ),
			array( '2,153.02₉', 215302 ),
			array( '2,230,153.02₉', 223015302 ),
			array( 2230153.02, 223015302 ),
			array( '2230153.02', 223015302 ),
			array( '2230153', 223015300 ),
			array( 2230153, 223015300 )
		);
	}

	/**
	 * @dataProvider _dp_convert_to_database_number_inr_after_comma_per
	 */
	public function test_convert_to_database_number_inr_after_comma_per( $price, $expected ) {

		add_filter( 'it_exchange_get_option-settings_general', function () {
			return array(
				'default-currency'             => 'inr',
				'currency-symbol-position'     => 'after',
				'currency-thousands-separator' => ',',
				'currency-decimals-separator'  => '.',
			);
		} );

		$this->assertEquals( $expected, it_exchange_convert_to_database_number( $price ) );
	}

	public function _dp_convert_to_database_number_inr_before_per_comma() {
		return array(
			array( '₉2,00', 200 ),
			array( '₉2,50', 250 ),
			array( '₉20,00', 2000 ),
			array( '₉20,40', 2040 ),
			array( '₉200,00', 20000 ),
			array( '₉200,22', 20022 ),
			array( '₉2.000,00', 200000 ),
			array( '₉2.100,00', 210000 ),
			array( '₉2.150,00', 215000 ),
			array( '₉2.153,00', 215300 ),
			array( '₉2.153,22', 215322 ),
			array( '₉2.153,02', 215302 ),
			array( '₉2.230.153,02', 223015302 ),
			array( '2230153,02', 223015302 ),
			array( '2230153', 223015300 ),
			array( 2230153, 223015300 )
		);
	}

	/**
	 * @dataProvider _dp_convert_to_database_number_inr_before_per_comma
	 */
	public function test_convert_to_database_number_inr_before_per_comma( $price, $expected ) {

		add_filter( 'it_exchange_get_option-settings_general', function () {
			return array(
				'default-currency'             => 'inr',
				'currency-symbol-position'     => 'before',
				'currency-thousands-separator' => '.',
				'currency-decimals-separator'  => ',',
			);
		} );

		$this->assertEquals( $expected, it_exchange_convert_to_database_number( $price ) );
	}

	public function _dp_convert_to_database_number_inr_after_per_comma() {
		return array(
			array( '2,00₉', 200 ),
			array( '2,50₉', 250 ),
			array( '20,00₉', 2000 ),
			array( '20,40₉', 2040 ),
			array( '200,00₉', 20000 ),
			array( '200,22₉', 20022 ),
			array( '2.000,00₉', 200000 ),
			array( '2.100,00₉', 210000 ),
			array( '2.150,00₉', 215000 ),
			array( '2.153,00₉', 215300 ),
			array( '2.153,22₉', 215322 ),
			array( '2.153,02₉', 215302 ),
			array( '2.230.153,02₉', 223015302 ),
			array( '2230153,02', 223015302 ),
			array( '2230153', 223015300 ),
			array( 2230153, 223015300 )
		);
	}

	/**
	 * @dataProvider _dp_convert_to_database_number_inr_after_per_comma
	 */
	public function test_convert_to_database_number_inr_after_per_comma( $price, $expected ) {

		add_filter( 'it_exchange_get_option-settings_general', function () {
			return array(
				'default-currency'             => 'inr',
				'currency-symbol-position'     => 'after',
				'currency-thousands-separator' => '.',
				'currency-decimals-separator'  => ',',
			);
		} );

		$this->assertEquals( $expected, it_exchange_convert_to_database_number( $price ) );
	}

	public function _dp_convert_from_database_number() {
		return array(
			array( 1, 0.01 ),
			array( 002, 0.02 ),
			array( 10, 0.10 ),
			array( 100, 1.00 ),
			array( 102, 1.02 ),
			array( 123, 1.23 ),
			array( 1200, 12.00 ),
			array( 1234, 12.34 ),
			array( 12300, 123.00 ),
			array( 12345, 123.45 ),
			array( 12300, 123.00 ),
			array( 123400, 1234.00 ),
			array( 123456, 1234.56 ),
			array( 1234500, 12345.00 ),
			array( 1234567, 12345.67 )
		);
	}

	/**
	 * @dataProvider _dp_convert_from_database_number
	 */
	public function test_convert_from_database_number( $db_num, $expected ) {
		$this->assertEquals( $expected, it_exchange_convert_from_database_number( $db_num ) );
	}

	public function test_get_currency_symbol() {
		$this->assertEquals( 'Z$', it_exchange_get_currency_symbol( 'ZWR' ) );
	}

	public function test_get_currency_symbol_default_usd() {
		$this->assertEquals( '$', it_exchange_get_currency_symbol( 'fake' ) );
	}

	public function test_set_get_global() {

		it_exchange_set_global( 'test-key', 'test-value' );
		$this->assertEquals( 'test-value', it_exchange_get_global( 'test-key' ) );
	}

	public function test_register_purchase_requirement() {

		$props = array(
			'priority'               => 2,
			'requirement-met'        => '__return_false',
			'sw-template-part'       => 'my-template-part',
			'checkout-template-part' => 'my-template-part',
			'notification'           => 'My Notification'
		);

		it_exchange_register_purchase_requirement( 'my-pr', $props );

		$props['slug'] = 'my-pr';

		$reqs = it_exchange_get_purchase_requirements();

		$this->assertArrayHasKey( 'my-pr', $reqs );
		$this->assertEquals( $props, $reqs['my-pr'] );
	}

	public function test_unregister_purchase_requirements() {

		it_exchange_register_purchase_requirement( 'test-pr' );
		it_exchange_unregister_purchase_requirement( 'test-pr' );

		$this->assertArrayNotHasKey( 'test-pr', it_exchange_get_purchase_requirements() );
	}

	public function test_get_next_purchase_requirement() {

		$backup = $GLOBALS['it_exchange']['purchase-requirements'];

		$GLOBALS['it_exchange']['purchase-requirements'] = array();

		it_exchange_register_purchase_requirement( 'test1', array(
			'requirement-met' => '__return_false',
			'priority'        => 10
		) );

		it_exchange_register_purchase_requirement( 'test2', array(
			'requirement-met' => '__return_true',
			'priority'        => 2
		) );

		$next = it_exchange_get_next_purchase_requirement();
		$this->assertEquals( 'test1', $next['slug'] );

		it_exchange_unregister_purchase_requirement( 'test1' );
		it_exchange_unregister_purchase_requirement( 'test2' );
		$GLOBALS['it_exchange']['purchase-requirements'] = $backup;
	}

	public function test_get_all_purchase_requirement_checkout_element_template_parts() {

		$backup = $GLOBALS['it_exchange']['purchase-requirements'];

		$GLOBALS['it_exchange']['purchase-requirements'] = array();

		it_exchange_register_purchase_requirement( 'test1', array(
			'requirement-met'        => '__return_false',
			'priority'               => 10,
			'checkout-template-part' => 'test1-part'
		) );

		it_exchange_register_purchase_requirement( 'test2', array(
			'requirement-met'        => '__return_true',
			'priority'               => 2,
			'checkout-template-part' => 'test2-part'
		) );

		$parts = it_exchange_get_all_purchase_requirement_checkout_element_template_parts();
		$this->assertEquals( array( 'test2-part', 'test1-part' ), $parts );

		it_exchange_unregister_purchase_requirement( 'test1' );
		it_exchange_unregister_purchase_requirement( 'test2' );
		$GLOBALS['it_exchange']['purchase-requirements'] = $backup;
	}

	/**
	 * @depends test_get_next_purchase_requirement
	 */
	public function test_get_next_purchase_requirement_property() {

		$backup = $GLOBALS['it_exchange']['purchase-requirements'];

		$GLOBALS['it_exchange']['purchase-requirements'] = array();

		it_exchange_register_purchase_requirement( 'test1', array(
			'requirement-met' => '__return_false',
			'priority'        => 10,
			'notification'    => 'Test 1'

		) );

		it_exchange_register_purchase_requirement( 'test2', array(
			'requirement-met' => '__return_true',
			'priority'        => 2,
			'notification'    => 'Test 2'
		) );

		$prop = it_exchange_get_next_purchase_requirement_property( 'notification' );
		$this->assertEquals( 'Test 1', $prop );

		it_exchange_unregister_purchase_requirement( 'test1' );
		it_exchange_unregister_purchase_requirement( 'test2' );
		$GLOBALS['it_exchange']['purchase-requirements'] = $backup;
	}

	public function test_get_pending_purchase_requirements() {

		$backup = $GLOBALS['it_exchange']['purchase-requirements'];

		$GLOBALS['it_exchange']['purchase-requirements'] = array();

		it_exchange_register_purchase_requirement( 'test1', array(
			'requirement-met' => '__return_false',
			'priority'        => 10,
			'notification'    => 'Test 1'

		) );
		it_exchange_register_purchase_requirement( 'test2', array(
			'requirement-met' => '__return_true',
			'priority'        => 2,
		) );
		it_exchange_register_purchase_requirement( 'test3', array(
			'requirement-met' => '__return_false',
			'priority'        => 1,
		) );

		$pending = it_exchange_get_pending_purchase_requirements();
		$this->assertEquals( array( 'test3', 'test1' ), $pending );

		it_exchange_unregister_purchase_requirement( 'test1' );
		it_exchange_unregister_purchase_requirement( 'test2' );
		it_exchange_unregister_purchase_requirement( 'test3' );
		$GLOBALS['it_exchange']['purchase-requirements'] = $backup;
	}

	/**
	 * @dataProvider _dp_convert_country_code
	 *
	 * @param $code
	 * @param $converted
	 */
	public function test_convert_country_code( $code, $converted ) {
		$this->assertEquals( $converted, it_exchange_convert_country_code( $code ) );
	}

	public function _dp_convert_country_code() {
		return array(
			array( 'US', 'USA' ),
			array( 'USA', 'US' ),
			array( 'AU', 'AUS' ),
			array( 'AUS', 'AU' ),
			array( 'BAD', false ),
			array( 'ZZ', false ),
		);
	}
}