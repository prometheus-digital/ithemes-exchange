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
	 * @var IT_Exchange_Test_Factory_For_Products
	 */
	protected $product_factory;

	/**
	 * @var IT_Exchange_Test_Factory_For_Downloads
	 */
	protected $download_factory;

	/**
	 * @var IT_Exchange_Test_Factory_For_Transactions
	 */
	protected $transaction_factory;

	/**
	 * @var IT_Exchange_Test_Factory_For_Basic_Coupons
	 */
	protected $coupon_factory;

	/**
	 * Do custom initialization.
	 */
	public function setUp() {
		parent::setUp();

		$null                      = null;
		$this->exchange_admin      = new IT_Exchange_Admin( $null );
		$this->product_factory     = new IT_Exchange_Test_Factory_For_Products( self::factory() );
		$this->download_factory    = new IT_Exchange_Test_Factory_For_Downloads( self::factory() );
		$this->transaction_factory = new IT_Exchange_Test_Factory_For_Transactions( self::factory() );
		$this->coupon_factory      = new IT_Exchange_Test_Factory_For_Basic_Coupons( self::factory() );

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

		unset( $this->download_factory );
		unset( $this->product_factory );
		unset( $this->transaction_factory );
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