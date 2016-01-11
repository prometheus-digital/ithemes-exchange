<?php
/**
 * Test Case.
 *
 * @since   1.34
 * @license GPLv2
 */

/**
 * Class IT_Exchange_UnitTestCase
 */
class IT_Exchange_UnitTestCase extends WP_UnitTestCase {

	/**
	 * @var IT_Exchange_Admin
	 */
	protected $exchange_admin;

	/**
	 * Do custom initialization.
	 */
	public function setUp() {
		parent::setUp();

		it_exchange_temporarily_load_addons( array_keys( it_exchange_get_addons() ) );
		it_exchange_add_feature_support_to_product_type( 'recurring-payments', 'digital-downloads-product-type' );

		$null                 = null;
		$this->exchange_admin = new IT_Exchange_Admin( $null );

		it_exchange_save_option( 'settings_general',
			$this->exchange_admin->set_general_settings_defaults( array() ) );
		it_exchange_get_option( 'settings_general', true );

		WP_Mock::setUp();
	}

	/**
	 * Teardown the test case.
	 */
	function tearDown() {
		parent::tearDown();

		WP_Mock::tearDown();

		unset( $this->product_factory );
		unset( $this->key_factory );
		unset( $this->activation_factory );
		unset( $this->release_factory );
		unset( $this->update_factory );
	}

	/**
	 * Simulate going to an iThemes Exchange custom page.
	 *
	 * @since 1.0
	 *
	 * @param string $exchange_page
	 */
	public function go_to_exchange_page( $exchange_page ) {

		remove_all_filters( 'it_exchange_is_page' );

		add_filter( 'it_exchange_is_page', function ( $result, $page ) use ( $exchange_page ) {
			if ( $page == $exchange_page ) {
				$result = true;
			}

			return $result;
		}, 10, 2 );
	}
}