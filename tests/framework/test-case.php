<?php
/**
 * Test Case.
 *
 * @since   1.34
 * @license GPLv2
 */

/**
 * Class IT_Exchange_UnitTestCase
 *
 * @property IT_Exchange_Test_Factory_For_Products      $product_factory
 * @property IT_Exchange_Test_Factory_For_Downloads     $download_factory
 * @property IT_Exchange_Test_Factory_For_Transactions  $transaction_factory
 * @property IT_Exchange_Test_Factory_For_Basic_Coupons $coupon_factory
 */
class IT_Exchange_UnitTestCase extends WP_UnitTestCase {

	/**
	 * @var IT_Exchange_Admin
	 */
	protected $exchange_admin;

	/**
	 * @var array
	 */
	protected $expected_hooks = array();

	/**
	 * Do custom initialization.
	 */
	public function setUp() {
		parent::setUp();

		$null                 = null;
		$this->exchange_admin = new IT_Exchange_Admin( $null );

		it_exchange_save_option( 'settings_general',
			$this->exchange_admin->set_general_settings_defaults( array() ) );
		it_exchange_get_option( 'settings_general', true );

		WP_Mock::setUp();

		add_filter( 'it_exchange_send_purchase_email_to_customer', '__return_false' );
		add_filter( 'it_exchange_send_purchase_email_to_admin', '__return_false' );
	}

	/**
	 * Expect a hook to be fired.
	 *
	 * @param string $hook
	 */
	public function expectHook( $hook ) {
		$fired                         = false;
		$this->expected_hooks[ $hook ] = &$fired;

		add_filter( $hook, function ( $_ = null ) use ( &$fired ) {
			$fired = true;

			return $_;
		} );
	}

	public function expectDeprecated() {
		parent::expectDeprecated();

		add_action( 'it_exchange_deprecated_meta_run', array( $this, 'deprecated_function_run' ) );
		add_filter( 'it_exchange_deprecated_meta_run_trigger_error', '__return_false' );
	}

	/**
	 * @return IT_Exchange_Test_Factory_For_Products|null
	 */
	static function product_factory() {
		static $factory = null;

		if ( ! $factory ) {
			$factory = new IT_Exchange_Test_Factory_For_Products( self::factory() );
		}

		return $factory;
	}

	/**
	 * @return IT_Exchange_Test_Factory_For_Downloads|null
	 */
	static function download_factory() {
		static $factory = null;

		if ( ! $factory ) {
			$factory = new IT_Exchange_Test_Factory_For_Downloads( self::factory() );
		}

		return $factory;
	}

	/**
	 * @return IT_Exchange_Test_Factory_For_Transactions|null
	 */
	static function transaction_factory() {
		static $factory = null;

		if ( ! $factory ) {
			$factory = new IT_Exchange_Test_Factory_For_Transactions( self::factory() );
		}

		return $factory;
	}

	/**
	 * @return IT_Exchange_Test_Factory_For_Basic_Coupons|null
	 */
	static function coupon_factory() {
		static $factory = null;

		if ( ! $factory ) {
			$factory = new IT_Exchange_Test_Factory_For_Basic_Coupons( self::factory() );
		}

		return $factory;
	}

	/**
	 * is utilized for reading data from inaccessible members.
	 *
	 * @param $name string
	 *
	 * @return mixed
	 * @link http://php.net/manual/en/language.oop5.overloading.php#language.oop5.overloading.members
	 */
	function __get( $name ) {
		switch ( $name ) {
			case 'product_factory':
				return self::product_factory();
			case 'download_factory':
				return self::download_factory();
			case 'transaction_factory':
				return self::transaction_factory();
			case 'coupon_factory':
				return self::coupon_factory();
		}

		return null;
	}

	/**
	 * Teardown the test case.
	 */
	function tearDown() {

		foreach ( $this->expected_hooks as $hook => $fired ) {
			if ( ! $fired ) {
				$this->fail( "Expected hook '$hook' was not fired." );
			}
		}

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