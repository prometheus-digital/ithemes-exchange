<?php
/**
 * Contains tests for the pages API functions.
 *
 * @since   1.35
 * @license GPLv2
 */

/**
 * Class IT_Exchange_API_Pages_Test
 *
 * @group pages-api
 */
class IT_Exchange_API_Pages_Test extends IT_Exchange_UnitTestCase {

	public function test_get_pages() {

		$pages = it_exchange_get_pages();

		$this->assertInternalType( 'array', $pages );
	}

	public function test_get_pages_caches_results() {

		$GLOBALS['it_exchange']['registered_pages'] = array();

		$pages = it_exchange_get_pages( true );

		$this->assertEquals( $pages, $GLOBALS['it_exchange']['registered_pages'] );
	}

	public function test_get_pages_does_not_overwrite_global_when_filtering() {

		$GLOBALS['it_exchange']['registered_pages'] = array();

		it_exchange_get_pages( true, array( 'my' => 'filter' ) );

		$this->assertEquals( array(), $GLOBALS['it_exchange']['registered_pages'] );
	}
}