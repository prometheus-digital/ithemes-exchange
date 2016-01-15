<?php
/**
 * Contains tests for the storage API functions.
 *
 * @since   1.35
 * @license GPLv2
 */

/**
 * Class IT_Exchange_API_Storage_Test
 *
 * @group storage-api
 */
class IT_Exchange_API_Storage_Test extends IT_Exchange_UnitTestCase {

	public function test_get_option_break_cache() {

		$old = it_exchange_get_option( 'my-test-key' );
		it_exchange_save_option( 'my-test-key', array(
			'my-data' => 'my-other-data'
		) );

		$this->assertEquals( $old, it_exchange_get_option( 'my-test-key' ), 'Cache broken incorrectly.' );

		$new = it_exchange_get_option( 'my-test-key', true );
		$this->assertArrayHasKey( 'my-data', $new, 'Cache not broken.' );
		$this->assertEquals( 'my-other-data', $new['my-data'], 'Wrong value saved' );
	}

	public function test_get_option_merge_defaults() {

		$defaults = array(
			'my-default' => 'value'
		);

		add_filter( 'it_storage_get_defaults_exchange_my-test-key2', function () use ( $defaults ) {
			return $defaults;
		} );

		$this->assertArrayNotHasKey( 'my-default', it_exchange_get_option( 'my-test-key2', true, false ) );
		$this->assertEquals( $defaults, it_exchange_get_option( 'my-test-key2', true ) );
	}

	public function test_clear_option_cache() {

		it_exchange_get_option( 'my-test-key' );
		it_exchange_save_option( 'my-test-key', array(
			'my-data' => 'my-other-data'
		) );

		it_exchange_clear_option_cache( 'my-test-key' );
		$new = it_exchange_get_option( 'my-test-key' );
		$this->assertArrayHasKey( 'my-data', $new, 'Cache not cleared.' );
		$this->assertEquals( 'my-other-data', $new['my-data'], 'Wrong value saved' );
	}
}