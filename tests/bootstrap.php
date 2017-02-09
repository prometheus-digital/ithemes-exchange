<?php
/**
 * Bootstrap Unit Tests
 *
 * @author Iron Bound Designs
 * @since  1.0
 */

/**
 * Determine where the WP test suite lives.
 *
 * Support for:
 * 1. `WP_DEVELOP_DIR` environment variable, which points to a checkout
 *   of the develop.svn.wordpress.org repository (this is recommended)
 * 2. `WP_TESTS_DIR` environment variable, which points to a checkout
 * 3. `WP_ROOT_DIR` environment variable, which points to a checkout
 * 4. Plugin installed inside of WordPress.org developer checkout
 * 5. Tests checked out to /tmp
 */
if ( false !== getenv( 'WP_DEVELOP_DIR' ) ) {
	$test_root = getenv( 'WP_DEVELOP_DIR' ) . '/tests/phpunit';
} else if ( false !== getenv( 'WP_TESTS_DIR' ) ) {
	$test_root = getenv( 'WP_TESTS_DIR' );
} else if ( false !== getenv( 'WP_ROOT_DIR' ) ) {
	$test_root = getenv( 'WP_ROOT_DIR' ) . '/tests/phpunit';
} else if ( file_exists( '../../../../tests/phpunit/includes/bootstrap.php' ) ) {
	$test_root = '../../../../tests/phpunit';
} else if ( file_exists( '/tmp/wordpress-tests-lib/includes/bootstrap.php' ) ) {
	$test_root = '/tmp/wordpress-tests-lib';
} else {
	die( 'No valid test root given.' . PHP_EOL );
}

require_once dirname( __FILE__ ) . '/../vendor/antecedent/patchwork/Patchwork.php';

require_once $test_root . '/includes/functions.php';

$_SERVER['REMOTE_ADDR'] = '127.0.0.1';

if ( ! defined( 'COOKIEHASH' ) ) {
	define( 'COOKIEHASH', md5( uniqid() ) );
}

function _manually_load_plugin() {
	require_once dirname( __FILE__ ) . '/../init.php';

	add_action(  'it_libraries_loaded', function() {
		$GLOBALS['it_exchange']['session'] = new IT_Exchange_In_Memory_Session( null );
	} );
}

tests_add_filter( 'muplugins_loaded', '_manually_load_plugin' );

tests_add_filter( 'it_exchange_register_addons', function () {

	tests_add_filter( 'it_exchange_get_enabled_addons', function ( $_, $options ) {
		$addons = array_filter( it_exchange_get_addons(), function ( $addon ) {
			return strpos( $addon['slug'], 'test' ) === false;
		} );

		if ( ! empty( $options['category'] ) ) {
			return it_exchange_filter_addons_by_category( $addons, $options['category'] );
		} else {
			return $addons;
		}
	}, 10, 2 );
} );

require $test_root . '/includes/bootstrap.php';

require dirname( __FILE__ ) . '/framework/coupon-factory.php';
require dirname( __FILE__ ) . '/framework/transaction-factory.php';
require dirname( __FILE__ ) . '/framework/product-factory.php';
require dirname( __FILE__ ) . '/framework/download-factory.php';
require dirname( __FILE__ ) . '/framework/test-case.php';

require_once dirname( __FILE__ ) . '/mocks/mock-product-type-class.php';

activate_plugin( 'ithemes-exchange/init.php' );

\WP_Mock::setUsePatchwork( true );
\WP_Mock::bootstrap();

