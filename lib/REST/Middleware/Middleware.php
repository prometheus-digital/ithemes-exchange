<?php
/**
 * Middleware Interface.
 *
 * @since   2.0.0
 * @license GPLv2
 */

namespace iThemes\Exchange\REST\Middleware;
use iThemes\Exchange\REST\Request;

/**
 * Interface Middleware
 * @package iThemes\Exchange\REST
 */
interface Middleware {

	/**
	 * Handle a REST request.
	 *
	 * @since 2.0.0
	 *
	 * @param \iThemes\Exchange\REST\Request             $request
	 * @param \iThemes\Exchange\REST\Middleware\Delegate $next
	 *
	 * @return \WP_REST_Response|\WP_Error
	 */
	public function handle( Request $request, Delegate $next );
}
