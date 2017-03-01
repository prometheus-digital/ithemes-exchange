<?php
/**
 * iThemes Exchange Info endpoint.
 *
 * @since   2.0.0
 * @license GPLv2
 */

namespace iThemes\Exchange\REST\Route\v1;

use iThemes\Exchange\REST\Auth\AuthScope;
use iThemes\Exchange\REST\Getable;
use iThemes\Exchange\REST\Request;
use iThemes\Exchange\REST\Route\Base;

/**
 * Class Info
 *
 * @package iThemes\Exchange\REST\Route\v1
 */
class Info extends Base implements Getable {
	/**
	 * @inheritDoc
	 */
	public function handle_get( Request $request ) {

		$general = it_exchange_get_option( 'settings_general' );

		return new \WP_REST_Response( array(
			'version'             => \IT_Exchange::VERSION,
			'company'             => $general['company-name'],
			'currency'            => $general['default-currency'],
			'symbol_position'     => $general['currency-symbol-position'],
			'thousands_separator' => $general['currency-thousands-separator'],
			'decimals_separator'  => $general['currency-decimals-separator'],
			'guest_checkout'      => function_exists( 'it_exchange_handle_guest_checkout_session' )
		) );
	}

	/**
	 * @inheritDoc
	 */
	public function user_can_get( Request $request, AuthScope $scope ) { return true; }

	/**
	 * @inheritDoc
	 */
	public function get_version() { return 1; }

	/**
	 * @inheritDoc
	 */
	public function get_path() { return 'info/'; }

	/**
	 * @inheritDoc
	 */
	public function get_query_args() { return array(); }

	/**
	 * @inheritDoc
	 */
	public function get_schema() {
		return array();
	}
}