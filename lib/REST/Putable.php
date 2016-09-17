<?php
/**
 * Putable Route.
 *
 * @since   1.36.0
 * @license GPLv2
 */

namespace iThemes\Exchange\REST;

/**
 * Interface Putable
 *
 * @package iThemes\Exchange\REST
 */
interface Putable extends Route {

	/**
	 * Handle a PUT request.
	 *
	 * @since 1.36.0
	 *
	 * @param \WP_REST_Request $request
	 *
	 * @return \WP_REST_Response
	 */
	public function handle_put( \WP_REST_Request $request );

	/**
	 * Whether the user has permission to access this route.
	 *
	 * @since 1.36.0
	 *
	 * @param \WP_REST_Request      $request
	 * @param \IT_Exchange_Customer $user
	 *
	 * @return bool
	 */
	public function user_can_put( \WP_REST_Request $request, \IT_Exchange_Customer $user = null );
}