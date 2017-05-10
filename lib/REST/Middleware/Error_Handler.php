<?php
/**
 * Error Handler.
 *
 * @since   2.0.0
 * @license GPLv2
 */

namespace iThemes\Exchange\REST\Middleware;

use iThemes\Exchange\REST\Request;

/**
 * Class Error_Handler
 *
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
		} catch ( \Throwable $e ) {
			$data = array( 'status' => \WP_Http::INTERNAL_SERVER_ERROR );

			if ( $this->debug ) {
				$data['trace'] = $e->getTraceAsString();
			}

			$this->log( $request, $e );

			return new \WP_Error(
				'rest_internal_server_error',
				$this->debug ? $e->getMessage() : __( 'Internal Server Error', 'it-l10n-ithemes-exchange' ),
				$data
			);
		} catch ( \Exception $e ) {
			$data = array( 'status' => \WP_Http::INTERNAL_SERVER_ERROR );

			if ( $this->debug ) {
				$data['trace'] = $e->getTraceAsString();
			}

			$this->log( $request, $e );

			return new \WP_Error(
				'rest_internal_server_error',
				$this->debug ? $e->getMessage() : __( 'Internal Server Error', 'it-l10n-ithemes-exchange' ),
				$data
			);
		}
	}

	/**
	 * Log an unexpected exception.
	 *
	 * @since 2.0.0
	 *
	 * @param Request               $request
	 * @param \Exception|\Throwable $e
	 */
	private function log( Request $request, $e ) {
		it_exchange_log( 'Unexpected exception while processing {method} REST request to {route} for scope {scope} with body {body}: {exception}', array(
			'route'     => $request->get_route(),
			'method'    => $request->get_method(),
			'scope'     => \iThemes\Exchange\REST\get_rest_manager()->get_auth_scope(),
			'body'      => $request->get_body(),
			'exception' => $e,
			'_group'    => 'REST',
		) );
	}
}
