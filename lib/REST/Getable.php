<?php
/**
 * Getable Route.
 *
 * @since   1.36.0
 * @license GPLv2
 */

namespace iThemes\Exchange\REST;

/**
 * Interface Getable
 *
 * @package iThemes\Exchange\REST
 */
interface Getable extends Route {

	/**
	 * Handle a GET request.
	 *
	 * @since 1.36.0
	 *
	 * @param \iThemes\Exchange\REST\Request $request
	 *
	 * @return \WP_REST_Response
	 */
	public function handle_get( Request $request );

	/**
	 * Whether the user has permission to access this route.
	 *
	 * @since 1.36.0
	 *
	 * @param \iThemes\Exchange\REST\Request $request
	 * @param \IT_Exchange_Customer          $user
	 *
	 * @return bool
	 */
	public function user_can_get( Request $request, \IT_Exchange_Customer $user = null );
}