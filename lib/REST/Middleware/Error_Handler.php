<?php
/**
 * Error Handler.
 *
 * @since   1.36.0
 * @license GPLv2
 */

namespace iThemes\Exchange\REST\Middleware;

use iThemes\Exchange\REST\Request;

/**
 * Class Error_Handler
 * @package iThemes\Exchange\REST\Middleware
 */
class Error_Handler implements Middleware {

	/** @var bool */
	private $debug;

	/**
	 * Error_Handler constructor.
	 *
	 * @param bool $debug
	 */
	public function __construct( $debug ) { $this->debug = (bool) $debug; }

	/**
	 * @inheritDoc
	 */
	public function handle( Request $request, Delegate $next ) {
		try {
			return $next->next( $request );
		}
		catch ( \Throwable $e ) {
			return new \WP_Error(
				'rest_internal_server_error',
				$this->debug ? $e->getMessage() : __( 'Internal Server Error', 'it-l10n-ithemes-exchange' )
			);
		}
		catch ( \Exception $e ) {
			return new \WP_Error(
				'rest_internal_server_error',
				$this->debug ? $e->getMessage() : __( 'Internal Server Error', 'it-l10n-ithemes-exchange' )
			);
		}
	}
}